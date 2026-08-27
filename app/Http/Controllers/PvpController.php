<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Avatar;
use App\Models\Avatars;
use App\Models\avatarWearing;

class PvpController extends Controller
{
    // Preserve both known Space Jump identifiers across the desktop and Ben's
    // live catalogue. Every other space remains opt-in through its config row.
    private const DEFAULT_PVP_SPACE_IDS = [9999, 10031];
    private const ARENA_XP_TYPE = 5;
    private const PRIMARY_XP_TYPE = 8;
    // Damage is a property of the selected weapon/spell. The client sends only
    // its key; the server owns the values so a forged damage number is ignored.
    private const WEAPON_DAMAGE = [
        'peashooter' => 10, 'shockball' => 15, 'nbazooka' => 40,
        'fireball' => 25, 'stun' => 8, 'icecube' => 12,
        'confuser' => 6, 'starwand' => 20, 'wstaff' => 35,
    ];
    private const DEFAULT_ALLOWED_WEAPONS = ['peashooter', 'shockball', 'stun', 'icecube', 'confuser'];
    private const ATTRIBUTE_KEYS = ['health', 'speed', 'frost', 'arcane', 'nature', 'fire', 'shadow'];
    private const DEFAULT_RESPAWN_SECONDS = 10;

    public function getConfig($spaceId)
    {
        $row = DB::table('pvp_match_configs')->where('space_id', $spaceId)->first();
        return response()->json([
            'enabled' => $row ? (bool) $row->enabled : in_array((int) $spaceId, self::DEFAULT_PVP_SPACE_IDS, true),
            'config' => $row ? json_decode($row->config, true) : null,
        ]);
    }

    public function setEnabled(Request $request, $spaceId)
    {
        $this->requireAdmin();
        if ($this->settingsLocked($spaceId)) {
            return response()->json(['error' => 'PvP settings are locked while a match is in progress'], 409);
        }
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $existing = DB::table('pvp_match_configs')->where('space_id', $spaceId)->first();
        DB::table('pvp_match_configs')->updateOrInsert(
            ['space_id' => $spaceId],
            [
                'enabled' => $data['enabled'],
                'config' => $existing->config ?? '{}',
                'updated_by' => $this->myAvatarId(),
                'updated_at' => now(),
                'created_at' => $existing->created_at ?? now(),
            ]
        );
        return response()->json(['enabled' => (bool) $data['enabled']]);
    }

    public function saveConfig(Request $request, $spaceId)
    {
        $this->requireAdmin();
        if ($this->settingsLocked($spaceId)) {
            return response()->json(['error' => 'PvP settings are locked while a match is in progress'], 409);
        }
        $config = $request->input('config');
        if (!is_array($config)) return response()->json(['error' => 'config object required'], 422);
        $existing = DB::table('pvp_match_configs')->where('space_id', $spaceId)->first();
        DB::table('pvp_match_configs')->updateOrInsert(
            ['space_id' => $spaceId],
            [
                'enabled' => $existing ? (bool) $existing->enabled : in_array((int) $spaceId, self::DEFAULT_PVP_SPACE_IDS, true),
                'config' => json_encode($config),
                'updated_by' => $this->myAvatarId(),
                'updated_at' => now(),
                'created_at' => $existing->created_at ?? now(),
            ]
        );
        return response()->json(['message' => 'saved']);
    }

    public function updates(Request $request)
    {
        $spaceId = $request->query('spaceId');
        return response()->json([
            'enabled' => $spaceId ? $this->enabled($spaceId) : false,
            'match' => $this->snapshot($spaceId),
            'config' => $spaceId ? $this->config($spaceId) : null,
        ]);
    }

    public function control(Request $request, $spaceId)
    {
        if (!$this->enabled($spaceId)) return response()->json(['error' => 'PvP is disabled in this space'], 409);
        $me = $this->myAvatarId();
        $action = $request->input('action');
        $match = $this->currentOrNewMatch($spaceId);

        switch ($action) {
            case 'load': break;
            case 'start':
                $this->requireAdmin();
                $config = $this->config($spaceId);
                // The controller is admin-only, so Start is the explicit
                // override for the configured automatic readiness count.
                $countdown = max(0, (int) ($config['durationCountdown'] ?? 5));
                DB::table('pvp_matches')->where('id', $match->id)->update([
                    'status' => $countdown ? 'countdown' : 'active',
                    // During countdown this is the instant combat becomes live.
                    'started_at' => now()->addSeconds($countdown), 'updated_at' => now(),
                ]);
                DB::table('pvp_match_players')->where('match_id', $match->id)->update([
                    'score' => 0, 'kos' => 0, 'state' => 'alive', 'updated_at' => now(),
                ]);
                $participants = DB::table('pvp_match_players')->where('match_id', $match->id)->get();
                foreach ($participants as $participant) {
                    DB::table('pvp_match_players')->where('id', $participant->id)->update([
                        'health' => $this->startingHealthForAvatar($spaceId, $participant->avatar_id, $config),
                    ]);
                }
                break;
            case 'end':
                $this->requireAdmin();
                DB::table('pvp_matches')->where('id', $match->id)->update([
                    'status' => 'ended',
                    'winner' => $this->winnerForMatch($match->id),
                    'ended_at' => now(),
                    'updated_at' => now(),
                ]);
                break;
            case 'joinTeam':
                $countdownFinished = $match->status === 'countdown' && $match->started_at
                    && now()->greaterThanOrEqualTo(\Carbon\Carbon::parse($match->started_at));
                if (!in_array($match->status, ['lobby', 'countdown'], true) || $countdownFinished) {
                    return response()->json(['error' => 'Teams are locked once combat begins'], 409);
                }
                $team = $request->input('team');
                if (!in_array($team, ['A', 'B'], true)) return response()->json(['error' => 'invalid team'], 422);
                $teamSize = max(1, (int) ($this->config($spaceId)['teamSize'] ?? 4));
                $onTeam = DB::table('pvp_match_players')->where('match_id', $match->id)->where('team', $team)->count();
                $alreadyThere = DB::table('pvp_match_players')->where('match_id', $match->id)->where('avatar_id', $me)->where('team', $team)->exists();
                if (!$alreadyThere && $onTeam >= $teamSize) return response()->json(['error' => 'That team is full'], 409);
                DB::table('pvp_match_players')->updateOrInsert(
                    ['match_id' => $match->id, 'avatar_id' => $me],
                    ['team' => $team, 'state' => 'alive', 'health' => $this->startingHealthForAvatar($spaceId, $me), 'updated_at' => now(), 'created_at' => now()]
                );
                break;
            case 'leaveTeam':
                DB::table('pvp_match_players')->where('match_id', $match->id)->where('avatar_id', $me)->delete();
                break;
            case 'hit':
                if ($match->status !== 'active') return response()->json(['error' => 'Combat is not active'], 409);
                $targetId = $request->input('targetAvatarId');
                if ((string) $targetId === (string) $me) {
                    return response()->json(['error' => 'You cannot attack yourself in a PvP match'], 422);
                }
                $attacker = DB::table('pvp_match_players')->where('match_id', $match->id)->where('avatar_id', $me)->first();
                $target = DB::table('pvp_match_players')->where('match_id', $match->id)->where('avatar_id', $targetId)->first();
                if (!$attacker || !$target || !$attacker->team || !$target->team || $attacker->team === $target->team) {
                    return response()->json(['error' => 'Only assigned opponents can be hit'], 422);
                }
                if ($attacker->state !== 'alive') {
                    return response()->json(['error' => 'You cannot shoot during your defeat timeout'], 409);
                }
                if ($target->state !== 'alive') return response()->json(['match' => $this->snapshot($spaceId)]);
                $config = $this->config($spaceId);
                $weapon = strtolower(trim((string) $request->input('weapon', '')));
                $allowedWeapons = is_array($config['allowedWeapons'] ?? null)
                    ? $config['allowedWeapons']
                    : self::DEFAULT_ALLOWED_WEAPONS;
                if (!array_key_exists($weapon, self::WEAPON_DAMAGE) || !in_array($weapon, $allowedWeapons, true)) {
                    return response()->json(['error' => 'That weapon or spell is not allowed in this space'], 422);
                }
                $health = max(0, (int) $target->health - self::WEAPON_DAMAGE[$weapon]);
                $eliminated = $health === 0;
                // Optimistic state/health guards make this an atomic transition:
                // simultaneous projectiles cannot defeat or score from the same
                // health value more than once.
                $changed = DB::table('pvp_match_players')->where('id', $target->id)
                    ->where('state', 'alive')->where('health', $target->health)->update([
                    'health' => $health, 'state' => $eliminated ? 'eliminated' : 'alive',
                    'eliminated_at' => $eliminated ? now() : null, 'updated_at' => now(),
                ]);
                if (!$changed) break;
                if ($eliminated) {
                    $points = max(1, (int) ($config['pointsPerElimination'] ?? 1));
                    DB::table('pvp_match_players')->where('id', $attacker->id)->increment('score', $points);
                    DB::table('pvp_match_players')->where('id', $attacker->id)->increment('kos');
                    $announcement = [
                        'id' => uniqid('pvp_', true), 'type' => 'elimination',
                        'scorerId' => $me, 'scorerName' => $this->avatarName($me),
                        'defeatedId' => $targetId, 'defeatedName' => $this->avatarName($targetId),
                        'points' => $points,
                    ];
                    DB::table('pvp_matches')->where('id', $match->id)->update(['last_event' => json_encode($announcement), 'updated_at' => now()]);
                }
                $teamTotals = DB::table('pvp_match_players')->where('match_id', $match->id)
                    ->select('team', DB::raw('sum(score) as total'))->groupBy('team')->pluck('total', 'team');
                $winningTeam = collect(['A', 'B'])->first(fn ($team) => (int) ($teamTotals[$team] ?? 0) >= max(1, (int) ($config['pointsToWin'] ?? 3)));
                if ($winningTeam) DB::table('pvp_matches')->where('id', $match->id)->update(['status' => 'ended', 'winner' => $winningTeam, 'ended_at' => now(), 'updated_at' => now()]);
                break;
            case 'showScores': case 'hideScores': case 'showRules': case 'hideRules': break;
            default: return response()->json(['error' => 'unknown action'], 422);
        }
        return response()->json(['match' => $this->snapshot($spaceId)]);
    }

    private function enabled($spaceId): bool
    {
        $value = DB::table('pvp_match_configs')->where('space_id', $spaceId)->value('enabled');
        return $value === null ? in_array((int) $spaceId, self::DEFAULT_PVP_SPACE_IDS, true) : (bool) $value;
    }

    private function settingsLocked($spaceId): bool
    {
        return DB::table('pvp_matches')->where('space_id', $spaceId)
            ->whereIn('status', ['countdown', 'active'])->exists();
    }

    private function currentOrNewMatch($spaceId)
    {
        $match = DB::table('pvp_matches')->where('space_id', $spaceId)->whereIn('status', ['lobby', 'countdown', 'active'])->orderByDesc('id')->first();
        if (!$match) {
            $id = DB::table('pvp_matches')->insertGetId(['space_id' => $spaceId, 'genre' => 'spacejump', 'status' => 'lobby', 'mode' => 'teams', 'created_at' => now(), 'updated_at' => now()]);
            $match = DB::table('pvp_matches')->where('id', $id)->first();
        }
        return $match;
    }

    private function snapshot($spaceId)
    {
        if (!$spaceId || !$this->enabled($spaceId)) return null;
        $match = DB::table('pvp_matches')->where('space_id', $spaceId)->orderByDesc('id')->first();
        if (!$match) return null;
        if ($match->status === 'countdown' && $match->started_at && now()->greaterThanOrEqualTo($match->started_at)) {
            DB::table('pvp_matches')->where('id', $match->id)->update(['status' => 'active', 'updated_at' => now()]);
            $match->status = 'active';
        }
        if ($match->status === 'active' && $match->started_at) {
            $duration = max(1, (int) ($this->config($spaceId)['duration'] ?? 600));
            if (now()->greaterThanOrEqualTo(\Carbon\Carbon::parse($match->started_at)->addSeconds($duration))) {
                $match->winner = $this->winnerForMatch($match->id);
                DB::table('pvp_matches')->where('id', $match->id)->update([
                    'status' => 'ended', 'winner' => $match->winner,
                    'ended_at' => now(), 'updated_at' => now(),
                ]);
                $match->status = 'ended';
            }
        }
        if ($match->status === 'ended') {
            $this->finalizeMatch($match->id, $spaceId);
            $match = DB::table('pvp_matches')->where('id', $match->id)->first();
        }
        $config = $this->config($spaceId);
        $respawnSeconds = max(1, (int) ($config['respawnDuration'] ?? self::DEFAULT_RESPAWN_SECONDS));
        if ($match->status === 'active') {
            $expired = DB::table('pvp_match_players')->where('match_id', $match->id)
                ->where('state', 'eliminated')->whereNotNull('eliminated_at')
                ->where('eliminated_at', '<=', now()->subSeconds($respawnSeconds))->get();
            foreach ($expired as $player) {
                DB::table('pvp_match_players')->where('id', $player->id)->where('state', 'eliminated')->update([
                    'state' => 'alive',
                    'health' => $this->startingHealthForAvatar($spaceId, $player->avatar_id, $config),
                    'eliminated_at' => null,
                    'updated_at' => now(),
                ]);
            }
        }
        $playerRows = DB::table('pvp_match_players')->where('match_id', $match->id)->get();
        $avatars = Avatars::whereIn('avatar_id', $playerRows->pluck('avatar_id'))
            ->get()->keyBy(fn ($avatar) => (string) $avatar->avatar_id);
        $players = $playerRows->map(function ($p) use ($avatars, $config, $respawnSeconds) {
            $avatar = $avatars->get((string) $p->avatar_id);
            $attributeModel = $this->attributeModel($p->avatar_id, $config);
            $attributes = $attributeModel['effective'];
            return [
                'avatarId' => $p->avatar_id,
                'name' => $avatar ? $this->avatarDisplayName($avatar) : 'Unknown player',
                'thumbUrl' => $avatar->thumbUrl ?? null,
                'team' => $p->team, 'state' => $p->state, 'health' => $p->health,
                'maxHealth' => max(1, (int) ($config['startingHealth'] ?? 100) + max(0, (int) $attributes['health'])),
                'attributes' => $attributes,
                'score' => $p->score, 'kos' => $p->kos,
                'respawnAt' => $p->state === 'eliminated' && $p->eliminated_at
                    ? \Carbon\Carbon::parse($p->eliminated_at)->addSeconds($respawnSeconds)->toIso8601String()
                    : null,
            ];
        })->values();
        $scores = [];
        foreach ($players as $player) $scores[$player['avatarId']] = $player['score'];
        $teamScores = ['A' => 0, 'B' => 0];
        foreach ($players as $player) if (isset($teamScores[$player['team']])) $teamScores[$player['team']] += (int) $player['score'];
        $mine = $players->first(fn ($player) => (string) $player['avatarId'] === (string) $this->myAvatarId());
        $myAttributeModel = $mine ? $this->attributeModel($mine['avatarId'], $config) : null;
        $startsAt = $match->started_at ? \Carbon\Carbon::parse($match->started_at) : null;
        $duration = max(1, (int) ($config['duration'] ?? 600));
        return [
            'id' => $match->id, 'status' => $match->status, 'mode' => $match->mode,
            'players' => $players, 'scores' => $scores, 'teamScores' => $teamScores,
            'winner' => $match->winner,
            'startsAt' => $startsAt?->toIso8601String(),
            'endsAt' => $startsAt?->copy()->addSeconds($duration)->toIso8601String(),
            'endedAt' => $match->ended_at ? \Carbon\Carbon::parse($match->ended_at)->toIso8601String() : null,
            'serverNow' => now()->toIso8601String(),
            'announcement' => $match->last_event ? json_decode($match->last_event, true) : null,
            // The completed roster remains in `players` for the results popup,
            // but nobody remains actively assigned after the match ends.
            'myTeam' => $match->status === 'ended' ? null : ($mine['team'] ?? null),
            'myAttributeModel' => $myAttributeModel,
        ];
    }

    /**
     * Award the configured Arena XP once. Participant rows are retained as the
     * immutable final scoreboard; a new lobby uses a new match id, so players
     * still have to join that new match explicitly.
     */
    private function finalizeMatch($matchId, $spaceId): void
    {
        DB::transaction(function () use ($matchId, $spaceId) {
            $match = DB::table('pvp_matches')->where('id', $matchId)->lockForUpdate()->first();
            if (!$match || $match->status !== 'ended' || $match->finalized_at) return;

            $avatarIds = DB::table('pvp_match_players')->where('match_id', $matchId)
                ->pluck('avatar_id')->unique()->values();
            $xp = max(0, (int) ($this->config($spaceId)['xpPerMatch'] ?? 15));

            if ($xp > 0) {
                foreach ($avatarIds as $avatarId) {
                    DB::table('xp')->where('avatar_id', $avatarId)->where('xpleveltype', self::ARENA_XP_TYPE)
                        ->update([
                            'xp' => DB::raw('xp + ' . $xp),
                            'xpGiven' => DB::raw('COALESCE(xpGiven, 0) + ' . $xp),
                            'updates' => true,
                            'updated_at' => now(),
                        ]);
                    $primaryXp = DB::table('xp')->where('avatar_id', $avatarId)
                        ->where('xpleveltype', '!=', self::PRIMARY_XP_TYPE)->sum('xp');
                    DB::table('xp')->where('avatar_id', $avatarId)->where('xpleveltype', self::PRIMARY_XP_TYPE)
                        ->update(['xp' => $primaryXp, 'updated_at' => now()]);
                }
            }

            DB::table('pvp_matches')->where('id', $matchId)->update(['finalized_at' => now(), 'updated_at' => now()]);
        });
    }

    /** Highest team score wins at the time limit; equal totals are a draw. */
    private function winnerForMatch($matchId): ?string
    {
        $totals = DB::table('pvp_match_players')->where('match_id', $matchId)
            ->select('team', DB::raw('sum(score) as total'))->groupBy('team')->pluck('total', 'team');
        $a = (int) ($totals['A'] ?? 0);
        $b = (int) ($totals['B'] ?? 0);
        if ($a === $b) return null;
        return $a > $b ? 'A' : 'B';
    }

    private function config($spaceId): array
    {
        $json = DB::table('pvp_match_configs')->where('space_id', $spaceId)->value('config');
        return $json ? (json_decode($json, true) ?: []) : [];
    }

    private function startingHealthForAvatar($spaceId, $avatarId, ?array $config = null): int
    {
        $config ??= $this->config($spaceId);
        $attributes = $this->effectiveAttributes($avatarId, $config);
        return max(1, (int) ($config['startingHealth'] ?? 100) + max(0, (int) $attributes['health']));
    }

    /**
     * PvP effective attributes = capped citizen-level allocation + the full
     * worn-item bonus. Wearables deliberately remain able to exceed the cap.
     */
    private function effectiveAttributes($avatarId, array $config): array
    {
        return $this->attributeModel($avatarId, $config)['effective'];
    }

    private function attributeModel($avatarId, array $config): array
    {
        $base = array_fill_keys(self::ATTRIBUTE_KEYS, 0);
        $bonus = array_fill_keys(self::ATTRIBUTE_KEYS, 0);
        $avatar = Avatar::find($avatarId);
        $assigned = $avatar && is_array($avatar->assigned_attributes) ? $avatar->assigned_attributes : [];
        foreach (self::ATTRIBUTE_KEYS as $key) $base[$key] = max(0, (int) ($assigned[$key] ?? 0));

        $rows = avatarWearing::where('avatar_id', $avatarId)
            ->with('model:items.model_id,items.action_attributes,items.action_attributes_secondary')
            ->get();
        foreach ($rows as $row) {
            if (!$row->model) continue;
            $this->sumAttributeCsv($row->model->action_attributes, $bonus);
            $this->sumAttributeCsv($row->model->action_attributes_secondary, $bonus);
        }

        $limits = is_array($config['shieldLimits'] ?? null) ? $config['shieldLimits'] : [];
        $cappedBase = [];
        $normal = [];
        $effective = [];
        foreach (self::ATTRIBUTE_KEYS as $key) {
            $cap = max(0, (int) ($limits[$key] ?? 100));
            $cappedBase[$key] = min($base[$key], $cap);
            $normal[$key] = max(0, $base[$key] + $bonus[$key]);
            $effective[$key] = max(0, $cappedBase[$key] + $bonus[$key]);
        }
        return ['base' => $base, 'cappedBase' => $cappedBase, 'wear' => $bonus, 'normal' => $normal, 'effective' => $effective];
    }

    private function sumAttributeCsv(?string $csv, array &$totals): void
    {
        if (!$csv) return;
        foreach (explode(',', $csv) as $token) {
            if (!preg_match('/^([a-z]+)([+-]?\d+)$/i', trim($token), $match)) continue;
            $key = strtolower($match[1]);
            if (array_key_exists($key, $totals)) $totals[$key] += (int) $match[2];
        }
    }

    private function myAvatarId()
    {
        return Auth::user()->defaultAvatar;
    }

    private function avatarName($avatarId): string
    {
        $avatar = Avatars::where('avatar_id', $avatarId)->first();
        if (!$avatar) return 'Unknown player';
        return $this->avatarDisplayName($avatar);
    }

    private function avatarDisplayName($avatar): string
    {
        $name = trim(($avatar->firstName ?? '') . ' ' . ($avatar->lastName ?? ''));
        return $name !== '' ? $name : ($avatar->fullName ?? 'Unknown player');
    }

    private function requireAdmin(): void
    {
        $user = Auth::user();
        $groups = preg_split('/\s*,\s*/', (string) ($user->secondaryGroupIds ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $admin = in_array($user->is_superuser ?? null, [true, 1, '1'], true)
            || in_array((int) ($user->primaryGroupId ?? 0), [1, 2, 13], true)
            || in_array(13, array_map('intval', $groups), true);
        abort_unless($admin, 403, 'Administrator access is required.');
    }
}
