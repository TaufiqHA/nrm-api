<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guest is redirected to login when accessing admin profile', function () {
    $response = $this->get('/admin/profile');

    $response->assertRedirect('/login');
});

test('regular user is forbidden from accessing admin profile', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->get('/admin/profile');

    $response->assertStatus(403);
});

test('admin can view profile page with their information', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'Admin Karaoke',
        'username' => 'adminkaraoke',
        'email' => 'adminkaraoke@example.com',
    ]);

    $response = $this->actingAs($admin)->get('/admin/profile');

    $response->assertStatus(200);
    $response->assertSee('Profil Saya');
    $response->assertSee('Informasi Pribadi & Kontak');
    $response->assertSee('Keamanan & Kata Sandi');
    $response->assertSee('Admin Karaoke');
    $response->assertSee('adminkaraoke');
    $response->assertSee('adminkaraoke@example.com');
    $response->assertDontSee('ID Pengguna');
    $response->assertDontSee('Terdaftar Sejak');
});

test('admin can update profile data', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'Nama Lama',
        'username' => 'usernamelama',
        'email' => 'lama@example.com',
    ]);

    $response = $this->actingAs($admin)->patch('/admin/profile', [
        'name' => 'Nama Baru',
        'username' => 'usernamebaru',
        'email' => 'baru@example.com',
    ]);

    $response->assertRedirect('/admin/profile');
    $response->assertSessionHas('success_profile');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'name' => 'Nama Baru',
        'username' => 'usernamebaru',
        'email' => 'baru@example.com',
    ]);
});

test('profile update succeeds when keeping same username and email', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'Nama Asli',
        'username' => 'usernameasli',
        'email' => 'asli@example.com',
    ]);

    $response = $this->actingAs($admin)->patch('/admin/profile', [
        'name' => 'Nama Terganti Saja',
        'username' => 'usernameasli',
        'email' => 'asli@example.com',
    ]);

    $response->assertRedirect('/admin/profile');
    $response->assertSessionHas('success_profile');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'name' => 'Nama Terganti Saja',
        'username' => 'usernameasli',
        'email' => 'asli@example.com',
    ]);
});

test('profile update fails if username or email is already taken by another user', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create([
        'username' => 'otheruser',
        'email' => 'other@example.com',
    ]);

    $response = $this->actingAs($admin)->from('/admin/profile')->patch('/admin/profile', [
        'name' => 'Test Name',
        'username' => 'otheruser',
        'email' => 'other@example.com',
    ]);

    $response->assertRedirect('/admin/profile');
    $response->assertSessionHasErrors(['username', 'email']);
});

test('admin can update password with valid current password', function () {
    $admin = User::factory()->admin()->create([
        'password' => Hash::make('oldpassword123'),
    ]);

    $response = $this->actingAs($admin)->put('/admin/profile/password', [
        'current_password' => 'oldpassword123',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertRedirect('/admin/profile');
    $response->assertSessionHas('success_password');

    $admin->refresh();
    expect(Hash::check('newpassword123', $admin->password))->toBeTrue();
});

test('password update fails when current password is invalid', function () {
    $admin = User::factory()->admin()->create([
        'password' => Hash::make('correctpassword123'),
    ]);

    $response = $this->actingAs($admin)->from('/admin/profile')->put('/admin/profile/password', [
        'current_password' => 'wrongpassword123',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertRedirect('/admin/profile');
    $response->assertSessionHasErrors(['current_password']);
});

test('password update fails when confirmation mismatch or password too short', function () {
    $admin = User::factory()->admin()->create([
        'password' => Hash::make('currentpassword123'),
    ]);

    $response = $this->actingAs($admin)->from('/admin/profile')->put('/admin/profile/password', [
        'current_password' => 'currentpassword123',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);

    $response->assertRedirect('/admin/profile');
    $response->assertSessionHasErrors(['password']);
});
