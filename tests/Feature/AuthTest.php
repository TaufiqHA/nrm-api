<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can login using valid username and password', function () {
    $user = User::factory()->create([
        'username' => 'johndoe',
        'password' => 'secret123',
        'role' => UserRole::User,
    ]);

    $response = $this->postJson('/api/login', [
        'username' => 'johndoe',
        'password' => 'secret123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
            'user' => [
                'id',
                'name',
                'username',
                'email',
                'role',
            ],
        ])
        ->assertJson([
            'message' => 'Login successful',
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'username' => 'johndoe',
                'role' => 'user',
            ],
        ]);
});

test('user cannot login with invalid password', function () {
    User::factory()->create([
        'username' => 'johndoe',
        'password' => 'secret123',
    ]);

    $response = $this->postJson('/api/login', [
        'username' => 'johndoe',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});

test('user cannot login with non-existent username', function () {
    $response = $this->postJson('/api/login', [
        'username' => 'nonexistent',
        'password' => 'secret123',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});

test('login requires username and password', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'password']);
});

test('authenticated user can view profile via me endpoint', function () {
    $user = User::factory()->create([
        'username' => 'johndoe',
        'role' => UserRole::Admin,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/me');

    $response->assertStatus(200)
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'username' => 'johndoe',
                'role' => 'admin',
            ],
        ]);
});

test('unauthenticated user cannot view profile via me endpoint', function () {
    $response = $this->getJson('/api/me');

    $response->assertStatus(401);
});

test('authenticated user can logout and revoke token', function () {
    $user = User::factory()->create([
        'username' => 'johndoe',
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully logged out',
        ]);

    expect($user->tokens()->count())->toBe(0);
});

test('user role helpers identify admin and user correctly', function () {
    $admin = User::factory()->admin()->create();
    $regularUser = User::factory()->create();

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isUser())->toBeFalse()
        ->and($regularUser->isAdmin())->toBeFalse()
        ->and($regularUser->isUser())->toBeTrue();
});

test('authenticated user can update profile data', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'username' => 'olduser',
        'email' => 'old@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/profile', [
        'name' => 'New Name',
        'username' => 'newuser',
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => 'New Name',
                'username' => 'newuser',
                'email' => 'new@example.com',
            ],
        ]);

    $freshUser = $user->fresh();
    expect($freshUser->name)->toBe('New Name')
        ->and($freshUser->username)->toBe('newuser')
        ->and($freshUser->email)->toBe('new@example.com');
});

test('profile update fails if username or email is already taken by another user', function () {
    User::factory()->create([
        'username' => 'existinguser',
        'email' => 'existing@example.com',
    ]);

    $user = User::factory()->create([
        'username' => 'myuser',
        'email' => 'my@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/profile', [
        'name' => 'My Name',
        'username' => 'existinguser',
        'email' => 'existing@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username', 'email']);
});

test('authenticated user can update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword123'),
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/profile/password', [
        'current_password' => 'oldpassword123',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Password updated successfully',
        ]);

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

test('password update fails with invalid current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correctpassword'),
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/profile/password', [
        'current_password' => 'wrongpassword',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});
