<?php

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login when accessing admin settings', function () {
    $response = $this->get('/admin/settings');

    $response->assertRedirect('/login');
});

test('regular user is forbidden from accessing admin settings', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->get('/admin/settings');

    $response->assertStatus(403);
});

test('admin can view settings page even if no settings record exists yet', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertSee('Pengaturan Aplikasi');
    $response->assertSee('Identitas &amp; Informasi Aplikasi', false);
    $response->assertSee('Simpan Pengaturan');
});

test('admin can view settings page with existing identity data', function () {
    $admin = User::factory()->admin()->create();
    Setting::factory()->create([
        'applicationcompany' => 'PT Suara Emas',
        'applicationname' => 'Karaoke Mantap',
    ]);

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertSee('PT Suara Emas');
    $response->assertSee('Karaoke Mantap');
});

test('admin can save identity settings when record does not exist initially', function () {
    $admin = User::factory()->admin()->create();

    expect(Setting::count())->toBe(0);

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'PT Karaoke Digital',
        'applicationname' => 'Karaoke Pro v2',
    ]);

    $response->assertRedirect('/admin/settings');
    $response->assertSessionHas('success', 'Pengaturan aplikasi berhasil disimpan.');

    expect(Setting::count())->toBe(1);
    $setting = Setting::first();
    expect($setting->applicationcompany)->toBe('PT Karaoke Digital')
        ->and($setting->applicationname)->toBe('Karaoke Pro v2');
});

test('saving identity settings keeps existing ad configuration untouched', function () {
    $admin = User::factory()->admin()->create();
    Setting::factory()->create([
        'applicationcompany' => 'Nama Lama',
        'applicationname' => 'App Lama',
        'applicationads1' => 'banners/keep.jpg',
        'applicationads1type' => 'admob_native',
        'applicationads1unit' => 'ca-app-pub-1234567890123456/1111111111',
        'applicationadsactive' => 'N',
    ]);

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'Nama Baru',
        'applicationname' => 'App Baru',
    ]);

    $response->assertRedirect('/admin/settings');

    $setting = Setting::first();
    expect($setting->applicationcompany)->toBe('Nama Baru')
        ->and($setting->applicationname)->toBe('App Baru')
        ->and($setting->applicationads1)->toBe('banners/keep.jpg')
        ->and($setting->applicationads1type)->toBe('admob_native')
        ->and($setting->applicationads1unit)->toBe('ca-app-pub-1234567890123456/1111111111')
        ->and($setting->applicationadsactive)->toBe('N');
});

test('validation fails when company or app name is missing', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/settings', []);

    $response->assertSessionHasErrors([
        'applicationcompany',
        'applicationname',
    ]);
});
