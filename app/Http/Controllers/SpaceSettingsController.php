<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\avatarSpaces;
use App\Models\Avatars;
use App\Models\spaceMember;
use App\Models\spaceRoles;
use App\Models\spaceBans;
use App\Models\onlineUsers;

/**
 * REST surface for the in-world Space Settings editor (owner-only).
 *
 * Everything here is gated on ownership: a space is owned by the user whose
 * id sits in avatar_spaces.user_id. The `swHeader` middleware has already
 * resolved the caller into Auth::user() from their SWSID, so Auth::id() is
 * the authoritative "who is asking". The client hides the settings icon for
 * non-owners, but that's UX only — assertOwner() is the real boundary and
 * every mutating endpoint calls it first.
 *
 * Data model (all pre-existing tables; see the 2022_07_* migrations):
 *   - avatar_spaces  — the space row (name/desc/theme/decor/access/password)
 *   - space_members  — membership; each row points at a space_roles row
 *   - space_roles    — global role definitions (access/officer/edit flags)
 *   - space_bans     — the boot/block list (avatar + reason + optional expiry)
 */
class SpaceSettingsController extends Controller
{
    /**
     * Access-control codes stored in avatar_spaces.accessControl. Only the two
     * the settings UI exposes are whitelisted on write; anything else is a 422.
     * 'O' = open to everyone, 'M' = members only (space_members is the allow-list).
     */
    private const ACCESS_CODES = ['O', 'M'];

    // ── Read: space meta ────────────────────────────────────────────────
    // Public-ish: any authenticated caller can read a space's meta (the header
    // + info popover use it). `isOwner` tells the client whether to surface the
    // settings affordance; the server still re-checks on every write.
    public function meta(Request $request, $id)
    {
        $space = avatarSpaces::find($id);
        if (!$space) {
            return response()->json(['error' => 'Space not found'], 404);
        }

        return response()->json($this->metaPayload($space));
    }

    // ── Write: general + theme/decor + security ─────────────────────────
    // One PATCH covers every scalar field the editor can change. Only keys
    // actually present in the body are touched, so the client can send a
    // partial payload (e.g. just {desc}) without clobbering the rest.
    public function update(Request $request, $id)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $data = $request->validate([
            'name'          => ['sometimes', 'string', 'max:120'],
            'desc'          => ['sometimes', 'nullable', 'string', 'max:2000'],
            'theme'         => ['sometimes', 'nullable', 'string', 'max:64'],
            'wallpaper'     => ['sometimes', 'nullable', 'string', 'max:64'],
            'flooring'      => ['sometimes', 'nullable', 'string', 'max:64'],
            'accessControl' => ['sometimes', 'string', 'in:O,M'],
            'password'      => ['sometimes', 'nullable', 'string', 'max:100'],
            'alias'         => ['sometimes', 'nullable', 'string', 'max:64'],
        ]);

        if (array_key_exists('name', $data)) {
            $name = trim($data['name']);
            if ($name === '') {
                return response()->json(['error' => 'Name cannot be empty'], 422);
            }
            $space->name = $name;
        }
        foreach (['desc', 'theme', 'wallpaper', 'flooring'] as $field) {
            if (array_key_exists($field, $data)) {
                $val = is_string($data[$field]) ? trim($data[$field]) : $data[$field];
                $space->{$field} = ($val === '') ? null : $val;
            }
        }
        if (array_key_exists('accessControl', $data)) {
            $space->accessControl = $data['accessControl'];
        }
        // Password: a present-but-empty value CLEARS the password (public), a
        // non-empty value sets a fresh bcrypt hash. Absent key leaves it as-is
        // so a rename never wipes the password. Mirrors admin/space.php.
        if (array_key_exists('password', $data)) {
            $pw = $data['password'];
            $space->password = ($pw === null || $pw === '') ? null : Hash::make($pw);
        }
        if (array_key_exists('alias', $data)) {
            $aliasResult = $this->normalizeAlias($data['alias'], $space->id);
            if ($aliasResult instanceof \Illuminate\Http\JsonResponse) return $aliasResult;
            $space->alias = $aliasResult;
        }

        $space->save();

        return response()->json($this->metaPayload($space->fresh()));
    }

    // ── Members ─────────────────────────────────────────────────────────
    public function members(Request $request, $id)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $rows = spaceMember::where('space_id', $space->id)->get()->map(function ($m) {
            return $this->memberPayload($m);
        })->values();

        return response()->json(['data' => $rows]);
    }

    // Global role catalogue for the role picker. Seeds a sensible default set
    // the first time it's asked for (the space_roles table ships empty — there
    // is no seeder), so the UI always has at least Member/Officer to assign.
    public function roles(Request $request, $id)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $this->ensureDefaultRoles();

        $roles = spaceRoles::orderBy('id')->get()->map(function ($r) {
            return [
                'id'      => (int) $r->id,
                'desc'    => $r->desc,
                'access'  => $this->flag($r->access_flag),
                'officer' => $this->flag($r->officer_flag),
                'edit'    => $this->flag($r->edit_flag),
            ];
        })->values();

        return response()->json(['data' => $roles]);
    }

    public function addMember(Request $request, $id)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $data = $request->validate([
            'avatarId' => ['required', 'integer'],
            'roleId'   => ['required', 'integer'],
        ]);

        $avatar = Avatars::where('avatar_id', $data['avatarId'])->first();
        if (!$avatar) {
            return response()->json(['error' => 'Avatar not found'], 404);
        }
        if ((int) $avatar->avatar_id === (int) $space->avatar_id) {
            return response()->json(['error' => 'The owner is always a member'], 422);
        }
        if (!spaceRoles::where('id', $data['roleId'])->exists()) {
            return response()->json(['error' => 'Role not found'], 422);
        }
        if (spaceMember::where('space_id', $space->id)->where('avatar_id', $data['avatarId'])->exists()) {
            return response()->json(['error' => 'Already a member'], 409);
        }

        $member = spaceMember::create([
            'space_id'      => $space->id,
            'spacerole_id'  => $data['roleId'],
            'avatar_id'     => $data['avatarId'],
        ]);

        return response()->json($this->memberPayload($member), 201);
    }

    public function updateMember(Request $request, $id, $memberId)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $data = $request->validate([
            'roleId' => ['required', 'integer'],
        ]);

        $member = spaceMember::where('space_id', $space->id)->where('id', $memberId)->first();
        if (!$member) {
            return response()->json(['error' => 'Member not found'], 404);
        }
        if (!spaceRoles::where('id', $data['roleId'])->exists()) {
            return response()->json(['error' => 'Role not found'], 422);
        }

        $member->spacerole_id = $data['roleId'];
        $member->save();

        return response()->json($this->memberPayload($member->fresh()));
    }

    public function removeMember(Request $request, $id, $memberId)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $member = spaceMember::where('space_id', $space->id)->where('id', $memberId)->first();
        if (!$member) {
            return response()->json(['error' => 'Member not found'], 404);
        }
        $member->delete();

        return response()->json(['ok' => true]);
    }

    // ── Boot / bans ─────────────────────────────────────────────────────
    public function bans(Request $request, $id)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $rows = spaceBans::where('space_id', $space->id)->get()->map(function ($b) {
            return $this->banPayload($b);
        })->values();

        return response()->json(['data' => $rows]);
    }

    public function addBan(Request $request, $id)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $data = $request->validate([
            'avatarId' => ['required', 'integer'],
            'reason'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'expires'  => ['sometimes', 'nullable', 'date'],
        ]);

        $avatar = Avatars::where('avatar_id', $data['avatarId'])->first();
        if (!$avatar) {
            return response()->json(['error' => 'Avatar not found'], 404);
        }
        if ((int) $avatar->avatar_id === (int) $space->avatar_id) {
            return response()->json(['error' => "You can't ban the owner"], 422);
        }
        if (spaceBans::where('space_id', $space->id)->where('avatar_id', $data['avatarId'])->exists()) {
            return response()->json(['error' => 'Already banned'], 409);
        }

        // Banning also revokes membership — a booted user shouldn't keep their
        // members-only access. Silent no-op when they weren't a member.
        spaceMember::where('space_id', $space->id)->where('avatar_id', $data['avatarId'])->delete();

        $ban = spaceBans::create([
            'space_id'  => $space->id,
            'avatar_id' => $data['avatarId'],
            'reason'    => $data['reason'] ?? null,
            'expires'   => $data['expires'] ?? null,
        ]);

        return response()->json($this->banPayload($ban), 201);
    }

    public function removeBan(Request $request, $id, $banId)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $ban = spaceBans::where('space_id', $space->id)->where('id', $banId)->first();
        if (!$ban) {
            return response()->json(['error' => 'Ban not found'], 404);
        }
        $ban->delete();

        return response()->json(['ok' => true]);
    }

    // ── Avatar lookup (add-member / add-ban type-ahead) ─────────────────
    // Space-scoped so the picker can flag who's already a member/banned and
    // never offer the owner. Matches on first/last/full name, min 2 chars.
    public function avatarSearch(Request $request, $id)
    {
        $space = $this->assertOwner($id);
        if ($space instanceof \Illuminate\Http\JsonResponse) return $space;

        $q = trim((string) $request->query('q', ''));
        if (Str::length($q) < 2) {
            return response()->json(['data' => []]);
        }

        $memberIds = spaceMember::where('space_id', $space->id)->pluck('avatar_id')->map(fn ($v) => (int) $v)->all();
        $bannedIds = spaceBans::where('space_id', $space->id)->pluck('avatar_id')->map(fn ($v) => (int) $v)->all();

        $matches = Avatars::where(function ($query) use ($q) {
                $query->where('firstName', 'like', "%{$q}%")
                      ->orWhere('lastName', 'like', "%{$q}%")
                      ->orWhere('fullName', 'like', "%{$q}%");
            })
            ->limit(12)
            ->get()
            ->map(function ($a) use ($space, $memberIds, $bannedIds) {
                $aid = (int) $a->avatar_id;
                return [
                    'id'       => $aid,
                    'name'     => $this->avatarName($a),
                    'thumb'    => $a->thumbUrl,
                    'isOwner'  => $aid === (int) $space->avatar_id,
                    'isMember' => in_array($aid, $memberIds, true),
                    'isBanned' => in_array($aid, $bannedIds, true),
                ];
            })
            ->values();

        return response()->json(['data' => $matches]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    /**
     * Loads the space and enforces ownership. Returns the avatarSpaces model on
     * success, or a JsonResponse (404/403) the caller must return as-is. Every
     * mutating endpoint funnels through here — it is the security boundary.
     */
    private function assertOwner($id)
    {
        $space = avatarSpaces::find($id);
        if (!$space) {
            return response()->json(['error' => 'Space not found'], 404);
        }
        if ((int) $space->user_id !== (int) Auth::id()) {
            return response()->json(['error' => 'Not your space'], 403);
        }
        return $space;
    }

    private function metaPayload(avatarSpaces $space): array
    {
        $ownerAvatar = $space->avatar_id ? Avatars::where('avatar_id', $space->avatar_id)->first() : null;
        $visitorCount = onlineUsers::where('space_id', $space->id)
            ->where('online', true)->where('local', false)->count();

        return [
            'id'            => (int) $space->id,
            'name'          => $space->name ?? '',
            'desc'          => $space->desc ?? '',
            'theme'         => $space->theme,
            'wallpaper'     => $space->wallpaper,
            'flooring'      => $space->flooring,
            'accessControl' => $space->accessControl ?? 'O',
            'hasPassword'   => !empty($space->password),
            'alias'         => $space->alias,
            // ownerId is the owner's AVATAR id — the client gates the settings
            // icon by comparing it to the local user's default avatar. Server
            // still enforces via user_id, so this is display/UX only.
            'ownerId'       => $space->avatar_id ? (int) $space->avatar_id : null,
            'ownerName'     => $ownerAvatar ? $this->avatarName($ownerAvatar) : null,
            'isOwner'       => (int) $space->user_id === (int) Auth::id(),
            'visitorCount'  => $visitorCount,
        ];
    }

    private function memberPayload(spaceMember $member): array
    {
        $avatar = Avatars::where('avatar_id', $member->avatar_id)->first();
        $role   = spaceRoles::where('id', $member->spacerole_id)->first();
        $online = onlineUsers::where('avatar_id', $member->avatar_id)
            ->where('online', true)->where('local', false)->exists();

        return [
            'id'        => (int) $member->id,
            'avatarId'  => (int) $member->avatar_id,
            'name'      => $avatar ? $this->avatarName($avatar) : 'Unknown',
            'thumb'     => $avatar->thumbUrl ?? null,
            'roleId'    => $member->spacerole_id ? (int) $member->spacerole_id : null,
            'roleDesc'  => $role->desc ?? null,
            'online'    => $online,
        ];
    }

    private function banPayload(spaceBans $ban): array
    {
        $avatar = Avatars::where('avatar_id', $ban->avatar_id)->first();
        return [
            'id'        => (int) $ban->id,
            'avatarId'  => (int) $ban->avatar_id,
            'name'      => $avatar ? $this->avatarName($avatar) : 'Unknown',
            'thumb'     => $avatar->thumbUrl ?? null,
            'reason'    => $ban->reason,
            'expires'   => $ban->expires,
        ];
    }

    private function avatarName(Avatars $a): string
    {
        $name = trim(($a->firstName ?? '') . ' ' . ($a->lastName ?? ''));
        return $name !== '' ? $name : ($a->fullName ?? 'Unknown');
    }

    // space_roles flags are stored as 'Y'/'N' strings; expose as bools.
    private function flag($v): bool
    {
        return in_array(strtoupper((string) $v), ['Y', '1', 'TRUE'], true);
    }

    /**
     * Seeds the two baseline roles if the global space_roles table is empty.
     * Idempotent — only inserts when nothing exists, so repeated calls are free.
     */
    private function ensureDefaultRoles(): void
    {
        if (spaceRoles::exists()) return;

        spaceRoles::create(['desc' => 'Member',  'access_flag' => 'Y', 'officer_flag' => 'N', 'edit_flag' => 'N']);
        spaceRoles::create(['desc' => 'Officer', 'access_flag' => 'Y', 'officer_flag' => 'Y', 'edit_flag' => 'Y']);
    }

    /**
     * Normalizes a vanity alias: lowercased, [a-z0-9-] only, and unique across
     * spaces. Empty/blank clears the alias (returns null). Returns a JsonResponse
     * on a uniqueness clash so the caller can bail with a 422.
     */
    private function normalizeAlias($alias, $spaceId)
    {
        if ($alias === null || trim($alias) === '') {
            return null;
        }
        $clean = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $alias));
        if ($clean === '') {
            return null;
        }
        $clash = avatarSpaces::where('alias', $clean)->where('id', '!=', $spaceId)->exists();
        if ($clash) {
            return response()->json(['error' => 'That alias is already taken'], 422);
        }
        return $clean;
    }
}
