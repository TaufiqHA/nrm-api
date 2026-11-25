<?php

use App\Enums\UserRole;
use App\Models\Category;
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

test('admin can toggle music and lyric menu visibility', function () {
    $admin = User::factory()->admin()->create();
    Setting::factory()->create([
        'applicationmusikactive' => 'Y',
        'applicationlyricactive' => 'Y',
    ]);

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'PT Suara Emas',
        'applicationname' => 'Karaoke Mantap',
        'applicationmusikactive' => 'N',
        'applicationlyricactive' => 'N',
    ]);

    $response->assertRedirect('/admin/settings');

    $setting = Setting::first();
    expect($setting->applicationmusikactive)->toBe('N')
        ->and($setting->applicationlyricactive)->toBe('N');
});

test('sidebar hides music and lyric menus when disabled', function () {
    $admin = User::factory()->admin()->create();
    Setting::factory()->create([
        'applicationmusikactive' => 'N',
        'applicationlyricactive' => 'N',
    ]);

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertDontSee(route('admin.music.index'), false);
    $response->assertDontSee(route('admin.lyrics.index'), false);
});

test('sidebar shows music and lyric menus when active', function () {
    $admin = User::factory()->admin()->create();
    Setting::factory()->create([
        'applicationmusikactive' => 'Y',
        'applicationlyricactive' => 'Y',
    ]);

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertSee(route('admin.music.index'), false);
    $response->assertSee(route('admin.lyrics.index'), false);
});

test('admin can rename menus inline from settings', function () {
    $admin = User::factory()->admin()->create();
    Setting::factory()->create();

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'PT Suara Emas',
        'applicationname' => 'Karaoke Mantap',
        'applicationmusiklabel' => 'Musik Karaoke',
        'applicationlyriclabel' => 'Lirik Karaoke',
        'applicationmenuindonesialabel' => 'Lagu Indonesia',
    ]);

    $response->assertRedirect('/admin/settings');
    $response->assertSessionHasNoErrors();

    $setting = Setting::first();
    expect($setting->applicationmusiklabel)->toBe('Musik Karaoke')
        ->and($setting->applicationlyriclabel)->toBe('Lirik Karaoke')
        ->and($setting->applicationmenuindonesialabel)->toBe('Lagu Indonesia');
});

test('settings page shows editable menu labels', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertSee('name="applicationmusiklabel"', false);
    $response->assertSee('name="applicationlyriclabel"', false);
    $response->assertSee('name="applicationmenuindonesialabel"', false);
    $response->assertSee('editMenuLabel', false);
});

test('validation fails when company or app name is missing', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/settings', []);

    $response->assertSessionHasErrors([
        'applicationcompany',
        'applicationname',
    ]);
});

test('settings page shows Indonesia menu visibility toggle', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertSee('Menu Indonesia');
    $response->assertSee('name="applicationmenuindonesiaactive"', false);
});

test('admin can toggle Indonesia menu visibility from settings', function () {
    $admin = User::factory()->admin()->create();
    Setting::factory()->create([
        'applicationmenuindonesiaactive' => 'Y',
    ]);
    $category = Category::factory()->create([
        'songcategoryname' => 'Indonesia',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'PT Suara Emas',
        'applicationname' => 'Karaoke Mantap',
        'applicationmenuindonesiaactive' => 'N',
    ]);

    $response->assertRedirect('/admin/settings');
    $response->assertSessionHasNoErrors();

    expect(Setting::first()->applicationmenuindonesiaactive)->toBe('N');

    // Data kategori lama tidak ikut berubah saat menu diubah.
    expect($category->fresh()->is_active)->toBeTrue();
});

test('settings page shows non-menu categories as category visibility', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create([
        'songcategoryid' => 2,
        'songcategoryname' => 'Kategori Lain',
    ]);
    $menuCategory = Category::factory()->create([
        'songcategoryid' => 1,
        'songcategoryname' => 'Indonesia',
    ]);

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertSee('Visibilitas Kategori');
    $response->assertSee('Kategori Lain');
    $response->assertSee(
        'name="category_visibility['.$category->songcategoryid.']"',
        false,
    );
    $response->assertDontSee(
        'name="category_visibility['.$menuCategory->songcategoryid.']"',
        false,
    );
});

test('menu category is hidden from category visibility based on id', function () {
    $admin = User::factory()->admin()->create();
    // Nama berbeda tetap disembunyikan karena id-nya id menu (1).
    $category = Category::factory()->create([
        'songcategoryid' => 1,
        'songcategoryname' => 'Nama Kategori Bebas',
    ]);

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertDontSee('name="category_visibility['.$category->songcategoryid.']"', false);
});

test('admin can toggle non-menu category visibility from settings', function () {
    $admin = User::factory()->admin()->create();
    Setting::factory()->create();
    $category = Category::factory()->create([
        'songcategoryid' => 2,
        'songcategoryname' => 'Kategori Sembunyi',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put('/admin/settings', [
        'applicationcompany' => 'PT Suara Emas',
        'applicationname' => 'Karaoke Mantap',
        'category_visibility' => [
            $category->songcategoryid => 'N',
        ],
    ]);

    $response->assertRedirect('/admin/settings');
    $response->assertSessionHasNoErrors();

    expect($category->fresh()->is_active)->toBeFalse();
});
