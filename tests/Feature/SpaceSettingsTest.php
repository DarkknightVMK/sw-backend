<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Feature tests for the owner-only Space Settings API (SpaceSettingsController).
 *
 * These drive the real HTTP stack: the swHeader middleware resolves the SWSID
 * header into an authenticated user, and the controller enforces ownership via
 * avatar_spaces.user_id === Auth::id(). Fixtures are inserted directly with
 * DB::table (FK checks disabled) rather than model factories, because this
 * legacy schema has many NOT-NULL columns and the shipped UserFactory doesn't
 * match the users table.
 *
 * NOTE: authored without a local PHP runtime — run with
 *   php artisan test --filter=SpaceSettingsTest
 * on a configured testing DB. RefreshDatabase runs the full migration set,
 * which includes the two space-settings migrations (theme/decor + user_id).
 */
class SpaceSettingsTest extends TestCase
{
    use RefreshDatabase;

    private string $ownerSwsid = 'owner-swsid-001';
    private string $strangerSwsid = 'stranger-swsid-002';

    private int $ownerUserId = 1001;
    private int $ownerAvatarId = 1001;      // avatars.avatar_id is a FK to users.id
    private int $strangerUserId = 2002;
    private int $strangerAvatarId = 2002;
    private int $memberAvatarId = 3003;
    private int $spaceId = 5001;
    private int $modelId = 9001;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();

        $this->makeUser($this->ownerUserId, 'owner@test.dev');
        $this->makeUser($this->strangerUserId, 'stranger@test.dev');

        $this->makeAvatar($this->ownerAvatarId, $this->ownerUserId, 'Owner', 'One');
        $this->makeAvatar($this->strangerAvatarId, $this->strangerUserId, 'Stranger', 'Two');
        $this->makeAvatar($this->memberAvatarId, $this->strangerUserId, 'Member', 'Three');

        $this->makeSpaceModel($this->modelId);
        // Owner of the space = ownerUserId (user_id); its avatar link = ownerAvatarId.
        $this->makeSpace($this->spaceId, $this->ownerUserId, $this->ownerAvatarId, $this->modelId);

        $this->makeSession($this->ownerSwsid, $this->ownerUserId);
        $this->makeSession($this->strangerSwsid, $this->strangerUserId);

        Schema::enableForeignKeyConstraints();
    }

    // ── Meta + auth ─────────────────────────────────────────────────────

    public function test_meta_reports_owner_for_the_owner(): void
    {
        $this->ownerHeaders()
            ->getJson("/api/spaces/{$this->spaceId}/meta")
            ->assertStatus(200)
            ->assertJson([
                'id'      => $this->spaceId,
                'isOwner' => true,
                'ownerId' => $this->ownerAvatarId,
            ]);
    }

    public function test_meta_reports_non_owner_for_a_stranger(): void
    {
        $this->strangerHeaders()
            ->getJson("/api/spaces/{$this->spaceId}/meta")
            ->assertStatus(200)
            ->assertJson(['isOwner' => false]);
    }

    public function test_bad_swsid_is_unauthorized(): void
    {
        $this->withHeaders(['SWSID' => 'not-a-real-session'])
            ->getJson("/api/spaces/{$this->spaceId}/meta")
            ->assertStatus(401);
    }

    // ── Update (general + theme/decor + security) ───────────────────────

    public function test_owner_can_update_name_desc_and_theme(): void
    {
        $this->ownerHeaders()
            ->patchJson("/api/spaces/{$this->spaceId}", [
                'name'  => 'Renamed Space',
                'desc'  => 'A better description',
                'theme' => 'neon',
            ])
            ->assertStatus(200)
            ->assertJson(['name' => 'Renamed Space', 'desc' => 'A better description', 'theme' => 'neon']);

        $this->assertDatabaseHas('avatar_spaces', [
            'id' => $this->spaceId, 'name' => 'Renamed Space', 'theme' => 'neon',
        ]);
    }

    public function test_stranger_cannot_update(): void
    {
        $this->strangerHeaders()
            ->patchJson("/api/spaces/{$this->spaceId}", ['name' => 'Hijacked'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('avatar_spaces', ['id' => $this->spaceId, 'name' => 'Hijacked']);
    }

    public function test_password_can_be_set_then_cleared(): void
    {
        $this->ownerHeaders()
            ->patchJson("/api/spaces/{$this->spaceId}", ['password' => 'secret123'])
            ->assertStatus(200)
            ->assertJson(['hasPassword' => true]);

        // Empty string clears it.
        $this->ownerHeaders()
            ->patchJson("/api/spaces/{$this->spaceId}", ['password' => ''])
            ->assertStatus(200)
            ->assertJson(['hasPassword' => false]);
    }

    public function test_access_control_is_validated(): void
    {
        // 'M' (members only) is allowed…
        $this->ownerHeaders()
            ->patchJson("/api/spaces/{$this->spaceId}", ['accessControl' => 'M'])
            ->assertStatus(200)
            ->assertJson(['accessControl' => 'M']);

        // …an unknown code is rejected.
        $this->ownerHeaders()
            ->patchJson("/api/spaces/{$this->spaceId}", ['accessControl' => 'ZZZ'])
            ->assertStatus(422);
    }

    // ── Roles + members ─────────────────────────────────────────────────

    public function test_roles_are_auto_seeded(): void
    {
        $this->ownerHeaders()
            ->getJson("/api/spaces/{$this->spaceId}/roles")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'desc', 'access', 'officer', 'edit']]]);

        $this->assertGreaterThanOrEqual(1, DB::table('space_roles')->count());
    }

    public function test_member_add_role_change_and_remove(): void
    {
        // Seed roles.
        $roles = $this->ownerHeaders()->getJson("/api/spaces/{$this->spaceId}/roles")->json('data');
        $roleId = $roles[0]['id'];
        $otherRoleId = $roles[1]['id'] ?? $roleId;

        // Add
        $add = $this->ownerHeaders()->postJson("/api/spaces/{$this->spaceId}/members", [
            'avatarId' => $this->memberAvatarId,
            'roleId'   => $roleId,
        ])->assertStatus(201)->json();
        $memberId = $add['id'];
        $this->assertDatabaseHas('space_members', [
            'space_id' => $this->spaceId, 'avatar_id' => $this->memberAvatarId, 'spacerole_id' => $roleId,
        ]);

        // Change role
        $this->ownerHeaders()->patchJson("/api/spaces/{$this->spaceId}/members/{$memberId}", [
            'roleId' => $otherRoleId,
        ])->assertStatus(200)->assertJson(['roleId' => $otherRoleId]);

        // Remove
        $this->ownerHeaders()->deleteJson("/api/spaces/{$this->spaceId}/members/{$memberId}")
            ->assertStatus(200);
        $this->assertDatabaseMissing('space_members', ['id' => $memberId]);
    }

    public function test_cannot_add_owner_as_member(): void
    {
        $roles = $this->ownerHeaders()->getJson("/api/spaces/{$this->spaceId}/roles")->json('data');
        $this->ownerHeaders()->postJson("/api/spaces/{$this->spaceId}/members", [
            'avatarId' => $this->ownerAvatarId,
            'roleId'   => $roles[0]['id'],
        ])->assertStatus(422);
    }

    // ── Bans ────────────────────────────────────────────────────────────

    public function test_banning_a_member_revokes_membership(): void
    {
        $roles = $this->ownerHeaders()->getJson("/api/spaces/{$this->spaceId}/roles")->json('data');

        // Make them a member first.
        $this->ownerHeaders()->postJson("/api/spaces/{$this->spaceId}/members", [
            'avatarId' => $this->memberAvatarId,
            'roleId'   => $roles[0]['id'],
        ])->assertStatus(201);

        // Ban them.
        $ban = $this->ownerHeaders()->postJson("/api/spaces/{$this->spaceId}/bans", [
            'avatarId' => $this->memberAvatarId,
            'reason'   => 'rule breaking',
        ])->assertStatus(201)->json();

        // Membership is revoked, ban recorded.
        $this->assertDatabaseMissing('space_members', [
            'space_id' => $this->spaceId, 'avatar_id' => $this->memberAvatarId,
        ]);
        $this->assertDatabaseHas('space_bans', [
            'space_id' => $this->spaceId, 'avatar_id' => $this->memberAvatarId,
        ]);

        // Unban.
        $this->ownerHeaders()->deleteJson("/api/spaces/{$this->spaceId}/bans/{$ban['id']}")
            ->assertStatus(200);
        $this->assertDatabaseMissing('space_bans', ['id' => $ban['id']]);
    }

    public function test_cannot_ban_the_owner(): void
    {
        $this->ownerHeaders()->postJson("/api/spaces/{$this->spaceId}/bans", [
            'avatarId' => $this->ownerAvatarId,
        ])->assertStatus(422);
    }

    // ── Avatar search ───────────────────────────────────────────────────

    public function test_avatar_search_requires_two_chars(): void
    {
        $this->ownerHeaders()
            ->getJson("/api/spaces/{$this->spaceId}/avatar-search?q=a")
            ->assertStatus(200)
            ->assertExactJson(['data' => []]);
    }

    public function test_avatar_search_matches_by_name(): void
    {
        $this->ownerHeaders()
            ->getJson("/api/spaces/{$this->spaceId}/avatar-search?q=Member")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $this->memberAvatarId]);
    }

    // ── Fixture helpers ─────────────────────────────────────────────────

    private function ownerHeaders()
    {
        return $this->withHeaders(['SWSID' => $this->ownerSwsid, 'Accept' => 'application/json']);
    }

    private function strangerHeaders()
    {
        return $this->withHeaders(['SWSID' => $this->strangerSwsid, 'Accept' => 'application/json']);
    }

    private function makeUser(int $id, string $email): void
    {
        DB::table('users')->insert([
            'id'           => $id,
            'firstName'    => 'Test',
            'lastName'     => 'User',
            'sex'          => 'M',
            'dob_month'    => '1',
            'dob_day'      => '1',
            'dob_year'     => '2000',
            'email'        => $email,
            'goldBalance'  => 0,
            'tokenBalance' => 0,
            'citizenLevel' => 1,
            'password'     => bcrypt('password'),
            'defaultAvatar' => (string) $id,
        ]);
    }

    private function makeAvatar(int $avatarId, int $userId, string $first, string $last): void
    {
        DB::table('avatars')->insert([
            'avatar_id'     => $avatarId,
            'firstName'     => $first,
            'lastName'      => $last,
            'gender'        => 'M',
            'nameInstance'  => 1,
            'fullName'      => "$first $last",
            'takePet'       => 0,
            'dateCreated'   => now(),
            'config'        => '{}',
            'defaultAvatar' => $avatarId,
            'thumbUrl'      => null,
        ]);
    }

    private function makeSpaceModel(int $modelId): void
    {
        DB::table('space_models')->insert([
            'model_id'      => $modelId,
            'model_details' => 'Test Model',
            'model_source'  => 'test.swf',
            'model_desc'    => 'A test space model',
        ]);
    }

    private function makeSpace(int $id, int $ownerUserId, int $ownerAvatarId, int $modelId): void
    {
        DB::table('avatar_spaces')->insert([
            'id'            => $id,
            'avatar_id'     => $ownerAvatarId,
            'user_id'       => $ownerUserId,
            'modelId'       => $modelId,
            'name'          => 'Original Name',
            'desc'          => 'Original desc',
            'accessControl' => 'O',
        ]);
    }

    private function makeSession(string $swsid, int $userId): void
    {
        DB::table('sessions')->insert([
            'SWSID'      => $swsid,
            'user_id'    => $userId,
            'expires_at' => now()->addDay(),
        ]);
    }
}
