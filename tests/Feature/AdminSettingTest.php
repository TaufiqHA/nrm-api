<?php

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
    $response->assertSee('Konfigurasi Iklan &amp; Banner', false);
    $response->assertSee('Simpan Pengaturan');
    $response->assertSee('type="file"', false);
    $response->assertSee('enctype="multipart/form-data"', false);
});

test('admin can view settings page with existing settings data and banner preview', function () {
    $admin = User::factory()->admin()->create();
    $setting = Setting::factory()->create([
        'applicationcompany' => 'PT Suara Emas',
        'applicationname' => 'Karaoke Mantap',
        'applicationads1' => 'https://example.com/banner-top.jpg',
        'applicationads2' => 'banners/popup.jpg',
        'applicationadsactive' => 'Y',
        'applicationadsbottom' => 'banners/bottom.jpg',
        'applicationadsbottomactive' => 'N',
    ]);

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertSee('PT Suara Emas');
    $response->assertSee('Karaoke Mantap');
    $response->assertSee('https://example.com/banner-top.jpg');
    $response->assertSee('popup.jpg');
    $response->assertSee('bottom.jpg');
});

test('admin can save settings and upload banner files when record does not exist initially', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    expect(Setting::count())->toBe(0);

    $ad1 = UploadedFile::fake()->image('banner1.jpg', 600, 200);
    $ad2 = UploadedFile::fake()->image('banner2.png', 400, 400);
    $adBottom = UploadedFile::fake()->image('banner-bottom.webp', 800, 100);

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'PT Karaoke Digital',
        'applicationname' => 'Karaoke Pro v2',
        'applicationads1' => $ad1,
        'applicationads2' => $ad2,
        'applicationadsactive' => 'Y',
        'applicationadsbottom' => $adBottom,
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertRedirect('/admin/settings');
    $response->assertSessionHas('success', 'Pengaturan aplikasi berhasil disimpan.');

    expect(Setting::count())->toBe(1);
    $setting = Setting::first();
    expect($setting->applicationcompany)->toBe('PT Karaoke Digital')
        ->and($setting->applicationname)->toBe('Karaoke Pro v2')
        ->and($setting->applicationadsactive)->toBe('Y')
        ->and($setting->applicationadsbottomactive)->toBe('Y')
        ->and($setting->applicationads1)->not->toBeNull()
        ->and($setting->applicationads2)->not->toBeNull()
        ->and($setting->applicationadsbottom)->not->toBeNull();

    Storage::disk('public')->assertExists($setting->applicationads1);
    Storage::disk('public')->assertExists($setting->applicationads2);
    Storage::disk('public')->assertExists($setting->applicationadsbottom);
});

test('admin can upload new banner file and delete old file', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $oldFile = UploadedFile::fake()->image('old-banner.jpg');
    $oldPath = $oldFile->store('banners', 'public');

    $setting = Setting::factory()->create([
        'applicationcompany' => 'Nama Lama',
        'applicationname' => 'App Lama',
        'applicationads1' => $oldPath,
    ]);

    Storage::disk('public')->assertExists($oldPath);

    $newFile = UploadedFile::fake()->image('new-banner.jpg');

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'Nama Baru',
        'applicationname' => 'App Baru',
        'applicationads1' => $newFile,
        'applicationadsactive' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertRedirect('/admin/settings');
    $setting->refresh();

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($setting->applicationads1);
});

test('admin can retain existing banner if no new file is uploaded', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $bannerFile = UploadedFile::fake()->image('keep-banner.jpg');
    $bannerPath = $bannerFile->store('banners', 'public');

    $setting = Setting::factory()->create([
        'applicationcompany' => 'Nama Perusahaan',
        'applicationname' => 'Nama App',
        'applicationads1' => $bannerPath,
    ]);

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'Nama Perusahaan Diubah',
        'applicationname' => 'Nama App Diubah',
        'applicationadsactive' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertRedirect('/admin/settings');
    $setting->refresh();

    expect($setting->applicationcompany)->toBe('Nama Perusahaan Diubah')
        ->and($setting->applicationads1)->toBe($bannerPath);

    Storage::disk('public')->assertExists($bannerPath);
});

test('admin can remove banner by selecting delete checkbox', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $bannerFile = UploadedFile::fake()->image('banner-to-delete.jpg');
    $bannerPath = $bannerFile->store('banners', 'public');

    $setting = Setting::factory()->create([
        'applicationcompany' => 'Nama Perusahaan',
        'applicationname' => 'Nama App',
        'applicationads1' => $bannerPath,
    ]);

    Storage::disk('public')->assertExists($bannerPath);

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'Nama Perusahaan',
        'applicationname' => 'Nama App',
        'applicationadsactive' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'delete_applicationads1' => '1',
    ]);

    $response->assertRedirect('/admin/settings');
    $setting->refresh();

    expect($setting->applicationads1)->toBeNull();
    Storage::disk('public')->assertMissing($bannerPath);
});

test('validation fails when uploaded file is not an image or exceeds 2MB', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $nonImage = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
    $oversizedImage = UploadedFile::fake()->image('huge.jpg')->size(3000); // 3MB

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'PT Karaoke',
        'applicationname' => 'Karaoke Pro',
        'applicationads1' => $nonImage,
        'applicationads2' => $oversizedImage,
        'applicationadsactive' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertSessionHasErrors([
        'applicationads1',
        'applicationads2',
    ]);
});
