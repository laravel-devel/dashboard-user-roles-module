<?php

namespace Modules\DevelUserRoles\Tests\Feature;

use Devel\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Devel\Database\Seeders\DevelDatabaseSeeder;
use Devel\Models\Auth\Role;
use Modules\DevelDashboard\Database\Seeders\DevelDashboardDatabaseSeeder;

class CrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DevelDatabaseSeeder::class);
        $this->seed(DevelDashboardDatabaseSeeder::class);

        $this->userModel = config('auth.providers.users.model');

        $this->root = $this->userModel::find(1);
    }

    /** @test */
    public function roots_can_view_user_role_lists()
    {
        $response = $this->actingAs($this->root)
            ->get(route('dashboard.develuserroles.roles.get'))
            ->assertStatus(200);

        $data = $response->json();

        $this->assertEquals(Role::count(), $data['total']);
    }

    /** @test */
    public function roots_can_create_user_roles()
    {
        $data = [
            'key' => 'test',
            'name' => 'Test',
            'permissions' => ['admin_dashboard.access'],
        ];

        $this->assertDatabaseMissing('devel_user_roles', ['key' => $data['key']]);

        $this->actingAs($this->root)
            ->postJson(route('dashboard.develuserroles.roles.store'), $data)
            ->assertStatus(201);

        $this->assertDatabaseHas('devel_user_roles', ['key' => $data['key']]);

        $role = Role::where('key', $data['key'])->first();

        $this->assertTrue($role->permissions->contains($data['permissions'][0]));
    }

    /** @test */
    public function roots_can_view_user_roles()
    {
        $role = factory(Role::class)->create();

        $this->actingAs($this->root)
            ->get(route('dashboard.develuserroles.roles.edit', $role->key))
            ->assertStatus(200);
    }

    /** @test */
    public function roots_can_edit_user_roles()
    {
        $role = factory(Role::class)->create();

        $data = [
            'key' => 'test',
            'name' => 'Test',
            'permissions' => ['admin_dashboard.access'],
        ];

        $this->actingAs($this->root)
            ->post(route('dashboard.develuserroles.roles.update', $role->key), $data)
            ->assertStatus(200);

        $role = $role->fresh();

        $this->assertEquals([
            'name' => $data['name'],
        ], [
            'name' => $role['name'],
        ]);

        $this->assertTrue($role->permissions->contains($data['permissions'][0]));
    }

    /** @test */
    public function existing_roles_key_cannot_be_changed()
    {
        $role = factory(Role::class)->create();

        $data = [
            'key' => 'test',
            'name' => 'Test',
        ];

        $this->actingAs($this->root)
            ->post(route('dashboard.develuserroles.roles.update', $role->key), $data)
            ->assertStatus(200);

        $this->assertFalse(Role::whereKey($data['key'])->exists());
        $this->assertTrue(Role::whereKey($role->key)->exists());
    }

    /** @test */
    public function roots_can_delete_user_roles()
    {
        $role = factory(Role::class)->create();

        $this->actingAs($this->root)
            ->delete(route('dashboard.develuserroles.roles.destroy', $role->key))
            ->assertStatus(200);

        $this->assertDatabaseMissing('devel_user_roles', ['key' => $role->key]);
    }

    /** @test */
    public function the_default_role_cannot_be_deleted()
    {
        $defaultRole = Role::where('default', true)->first();

        $this->actingAs($this->root)
            ->delete(route('dashboard.develuserroles.roles.destroy', $defaultRole->key))
            ->assertStatus(409);

        $this->assertDatabaseHas('devel_user_roles', ['key' => $defaultRole->key]);
    }

    /** @test */
    public function there_can_be_only_one_default_role()
    {
        $defaultRole = Role::where('default', true)->first();

        // Create a new role
        $data = [
            'key' => 'test',
            'name' => 'Test',
            'permissions' => ['admin_dashboard.access'],
            'default' => true,
        ];

        $this->actingAs($this->root)
            ->postJson(route('dashboard.develuserroles.roles.store'), $data)
            ->assertStatus(201);

        // Make sure the new role is the default one and the original one is not
        $newRole = Role::where('key', $data['key'])->first();

        $this->assertEquals(true, $newRole->default);
        $this->assertEquals(false, $defaultRole->fresh()->default);
    }

    /** @test */
    public function there_has_to_be_a_default_role()
    {
        // Get the default role
        $defaultRole = Role::where('default', true)->first();

        // Try making it non-default
        $data = [
            'key' => $defaultRole->key,
            'name' => $defaultRole->name,
        ];

        $this->actingAs($this->root)
            ->post(route('dashboard.develuserroles.roles.update', $defaultRole->key), $data)
            ->assertStatus(200);

        // Make sure the role is still default
        $this->assertEquals(1, $defaultRole->fresh()->default);
    }
}
