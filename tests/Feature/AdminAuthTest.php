<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can view admin login page', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Admin');
    $response->assertSee('Username atau Email');
});

test('admin can login with valid username and is redirected to dashboard', function () {
    $admin = User::factory()->admin()->create([
        'username' => 'superadmin',
        'password' => 'password123',
    ]);

    $response = $this->post('/login', [
        'username' => 'superadmin',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/admin/dashboard');
    $this->assertAuthenticatedAs($admin);
});

test('admin can login with valid email and is redirected to dashboard', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@karaoke.test',
        'password' => 'password123',
    ]);

    $response = $this->post('/login', [
        'username' => 'admin@karaoke.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/admin/dashboard');
    $this->assertAuthenticatedAs($admin);
});

test('regular user cannot login via admin login', function () {
    User::factory()->create([
        'username' => 'regularuser',
        'password' => 'password123',
        'role' => UserRole::User,
    ]);

    $response = $this->from('/login')->post('/login', [
        'username' => 'regularuser',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors(['username']);
    $this->assertGuest();
});

test('login fails with invalid password', function () {
    User::factory()->admin()->create([
        'username' => 'adminuser',
        'password' => 'password123',
    ]);

    $response = $this->from('/login')->post('/login', [
        'username' => 'adminuser',
        'password' => 'wrongpassword',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors(['username']);
    $this->assertGuest();
});

test('unauthenticated user is redirected to login when visiting admin dashboard', function () {
    $response = $this->get('/admin/dashboard');

    $response->assertRedirect('/login');
});

test('authenticated regular user cannot access admin dashboard', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertStatus(403);
});

test('authenticated admin can access dashboard and view statistics', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'John Admin',
    ]);

    Category::factory()->count(3)->create();
    Song::factory()->count(5)->create();

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertStatus(200);
    $response->assertSee('John Admin');
    $response->assertSee('Overview Dashboard');
    $response->assertSee('Total Koleksi Lagu');
    $response->assertSee('Kategori / Genre');
    $response->assertSee('Total Pengguna');
    $response->assertSee('Keluar (Logout)');
});

test('authenticated admin visiting login page is redirected to dashboard', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/login');

    $response->assertRedirect('/admin/dashboard');
});

test('root route redirects authenticated admin to dashboard and redirects guest to login', function () {
    // Guest
    $guestResponse = $this->get('/');
    $guestResponse->assertRedirect('/login');

    // Admin
    $admin = User::factory()->admin()->create();
    $adminResponse = $this->actingAs($admin)->get('/');
    $adminResponse->assertRedirect('/admin/dashboard');
});

test('admin can logout successfully', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/logout');

    $response->assertRedirect('/login');
    $response->assertSessionHas('status');
    $this->assertGuest();
});
