<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access admin users endpoints', function () {
    $user = User::factory()->create();

    $this->getJson('/api/admin/users')->assertStatus(401);
    $this->postJson('/api/admin/users', [])->assertStatus(401);
    $this->getJson("/api/admin/users/{$user->id}")->assertStatus(401);
    $this->putJson("/api/admin/users/{$user->id}", [])->assertStatus(401);
    $this->deleteJson("/api/admin/users/{$user->id}")->assertStatus(401);
});

test('non-admin user receives 403 forbidden on admin users endpoints', function () {
    $regularUser = User::factory()->create([
        'role' => UserRole::User,
    ]);
    Sanctum::actingAs($regularUser);

    $targetUser = User::factory()->create();

    $this->getJson('/api/admin/users')
        ->assertStatus(403)
        ->assertJson(['message' => 'Forbidden. Admin access required.']);

    $this->postJson('/api/admin/users', [
        'name' => 'New User',
        'username' => 'newuser',
        'email' => 'new@example.com',
        'password' => 'secret123',
        'role' => 'user',
    ])->assertStatus(403);

    $this->getJson("/api/admin/users/{$targetUser->id}")->assertStatus(403);
    $this->putJson("/api/admin/users/{$targetUser->id}", ['name' => 'Updated'])->assertStatus(403);
    $this->deleteJson("/api/admin/users/{$targetUser->id}")->assertStatus(403);
});

test('admin can list all users', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    User::factory()->count(3)->create();

    $response = $this->getJson('/api/admin/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'username',
                    'email',
                    'role',
                    'created_at',
                    'updated_at',
                ],
            ],
        ])
        ->assertJsonCount(4, 'data');
});

test('admin can search users by name, username, or email', function () {
    $admin = User::factory()->admin()->create(['username' => 'adminboss']);
    Sanctum::actingAs($admin);

    User::factory()->create([
        'name' => 'Michael Jackson',
        'username' => 'mjking',
        'email' => 'michael@example.com',
    ]);
    User::factory()->create([
        'name' => 'Freddie Mercury',
        'username' => 'queenfreddie',
        'email' => 'freddie@example.com',
    ]);

    $responseByName = $this->getJson('/api/admin/users?search=Michael');
    $responseByName->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Michael Jackson');

    $responseByUsername = $this->getJson('/api/admin/users?search=queenfreddie');
    $responseByUsername->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.username', 'queenfreddie');

    $responseByEmail = $this->getJson('/api/admin/users?search=michael@example.com');
    $responseByEmail->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.email', 'michael@example.com');
});

test('admin can filter users by role', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    User::factory()->admin()->create();
    User::factory()->count(3)->create(['role' => UserRole::User]);

    $responseAdmin = $this->getJson('/api/admin/users?role=admin');
    $responseAdmin->assertStatus(200)
        ->assertJsonCount(2, 'data');

    $responseUser = $this->getJson('/api/admin/users?role=user');
    $responseUser->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('admin can paginate users', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    User::factory()->count(5)->create();

    $response = $this->getJson('/api/admin/users?page=1&per_page=2');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => ['id', 'name', 'username', 'email', 'role'],
            ],
            'total',
            'per_page',
        ])
        ->assertJsonPath('per_page', 2)
        ->assertJsonCount(2, 'data');
});

test('admin can view a specific user', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $target = User::factory()->create([
        'name' => 'Target User',
        'username' => 'targetuser',
        'email' => 'target@example.com',
        'role' => UserRole::User,
    ]);

    $response = $this->getJson("/api/admin/users/{$target->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $target->id,
                'name' => 'Target User',
                'username' => 'targetuser',
                'email' => 'target@example.com',
                'role' => 'user',
            ],
        ]);
});

test('returns 404 when user is not found', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/users/9999');

    $response->assertStatus(404);
});

test('admin can create a new user', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/users', [
        'name' => 'Alice Admin',
        'username' => 'aliceadmin',
        'email' => 'alice@example.com',
        'password' => 'password123',
        'role' => 'admin',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'username',
                'email',
                'role',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'message' => 'User created successfully',
            'data' => [
                'name' => 'Alice Admin',
                'username' => 'aliceadmin',
                'email' => 'alice@example.com',
                'role' => 'admin',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'username' => 'aliceadmin',
        'email' => 'alice@example.com',
        'role' => 'admin',
    ]);

    $createdUser = User::where('username', 'aliceadmin')->first();
    expect(Hash::check('password123', $createdUser->password))->toBeTrue();
});

test('user creation validates required and unique fields', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    User::factory()->create([
        'username' => 'existinguser',
        'email' => 'existing@example.com',
    ]);

    $response = $this->postJson('/api/admin/users', []);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'username', 'email', 'password', 'role']);

    $responseDuplicate = $this->postJson('/api/admin/users', [
        'name' => 'New User',
        'username' => 'existinguser',
        'email' => 'existing@example.com',
        'password' => 'pass1234',
        'role' => 'invalid_role',
    ]);

    $responseDuplicate->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'email', 'role']);

    $responseShortPassword = $this->postJson('/api/admin/users', [
        'name' => 'New User',
        'username' => 'newuser',
        'email' => 'new@example.com',
        'password' => 'short',
        'role' => 'user',
    ]);

    $responseShortPassword->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('admin can update user without changing password', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $user = User::factory()->create([
        'name' => 'Old Name',
        'username' => 'oldusername',
        'email' => 'old@example.com',
        'password' => 'originalpassword',
        'role' => UserRole::User,
    ]);

    $response = $this->putJson("/api/admin/users/{$user->id}", [
        'name' => 'Updated Name',
        'username' => 'updatedusername',
        'email' => 'updated@example.com',
        'role' => 'admin',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'User updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => 'Updated Name',
                'username' => 'updatedusername',
                'email' => 'updated@example.com',
                'role' => 'admin',
            ],
        ]);

    $fresh = $user->fresh();
    expect(Hash::check('originalpassword', $fresh->password))->toBeTrue();
});

test('admin can update user password', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $user = User::factory()->create([
        'password' => 'oldpassword123',
    ]);

    $response = $this->putJson("/api/admin/users/{$user->id}", [
        'password' => 'newsupersecret',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'User updated successfully',
        ]);

    $fresh = $user->fresh();
    expect(Hash::check('newsupersecret', $fresh->password))->toBeTrue();
});

test('user update validates unique constraints except self', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $user1 = User::factory()->create([
        'username' => 'userone',
        'email' => 'userone@example.com',
    ]);
    $user2 = User::factory()->create([
        'username' => 'usertwo',
        'email' => 'usertwo@example.com',
    ]);

    // Updating self with same username/email succeeds
    $responseSelf = $this->putJson("/api/admin/users/{$user1->id}", [
        'username' => 'userone',
        'email' => 'userone@example.com',
    ]);
    $responseSelf->assertStatus(200);

    // Updating to user2's username/email fails
    $responseDuplicate = $this->putJson("/api/admin/users/{$user1->id}", [
        'username' => 'usertwo',
        'email' => 'usertwo@example.com',
    ]);
    $responseDuplicate->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'email']);
});

test('admin cannot delete their own account', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/admin/users/{$admin->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'You cannot delete your own account',
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

test('admin can delete another user', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $target = User::factory()->create();
    $target->createToken('test_token');

    $response = $this->deleteJson("/api/admin/users/{$target->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'User deleted successfully',
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $target->id,
    ]);
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $target->id,
    ]);
});
