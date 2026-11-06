<?php

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guest is redirected to login when accessing admin ads page', function () {
    $response = $this->get('/admin/ads');

    $response->assertRedirect('/login');
});

test('regular user is forbidden from accessing admin ads page', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->get('/admin/ads');

    $response->assertStatus(403);
});

test('admin can view ads page even if no settings record exists yet', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/ads');

    $response->assertStatus(200);
    $response->assertSee('Konfigurasi Iklan');
    $response->assertSee('Iklan Utama');
    $response->assertSee('Iklan Bawah');
    $response->assertSee('Simpan Iklan');
    $response->assertSee('type="file"', false);
    $response->assertSee('enctype="multipart/form-data"', false);
    $response->assertSee('Ad Unit ID AdMob');
    $response->assertSee('name="applicationads1active"', false);
    $response->assertSee('name="applicationads2active"', false);
    $response->assertSee('name="applicationadshomeactive"', false);
    $response->assertSee('name="applicationadslyricactive"', false);
    $response->assertSee('name="applicationadslyrictopactive"', false);
    $response->assertSee('name="applicationadslyricbottomactive"', false);
    $response->assertSee('name="applicationadsbottomactive"', false);
    $response->assertSee('name="applicationads1clickurl"', false);
    $response->assertSee('name="applicationads2clickurl"', false);
    $response->assertSee('name="applicationadshomeclickurl"', false);
    $response->assertSee('name="applicationadslyricclickurl"', false);
    $response->assertSee('name="applicationadslyrictopclickurl"', false);
    $response->assertSee('name="applicationadslyricbottomclickurl"', false);
    $response->assertSee('name="applicationadsbottomclickurl"', false);
    $response->assertSee('Slot 3 — Halaman Utama');
    $response->assertSee('Slot 4 — Pop-up Halaman Lirik Only');
    $response->assertSee('Iklan Halaman Lirik');
    $response->assertSee('Halaman Lirik — Atas');
    $response->assertSee('Halaman Lirik — Bawah');
    $response->assertDontSee('name="applicationadsactive"', false);
});

test('admin can view ads page with existing banner preview', function () {
    $admin = User::factory()->admin()->create();
    Setting::factory()->create([
        'applicationads1' => 'https://example.com/banner-top.jpg',
        'applicationads2' => 'banners/popup.jpg',
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottom' => 'banners/bottom.jpg',
        'applicationadsbottomactive' => 'N',
    ]);

    $response = $this->actingAs($admin)->get('/admin/ads');

    $response->assertStatus(200);
    $response->assertSee('https://example.com/banner-top.jpg');
    $response->assertSee('popup.jpg');
    $response->assertSee('bottom.jpg');
});

test('admin can save ads and upload banner files when record does not exist initially', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    expect(Setting::count())->toBe(0);

    $ad1 = UploadedFile::fake()->image('banner1.jpg', 600, 200);
    $ad2 = UploadedFile::fake()->image('banner2.png', 400, 400);
    $adBottom = UploadedFile::fake()->image('banner-bottom.webp', 800, 100);

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1' => $ad1,
        'applicationads2' => $ad2,
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottom' => $adBottom,
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertRedirect('/admin/ads');
    $response->assertSessionHas('success', 'Konfigurasi iklan berhasil disimpan.');

    expect(Setting::count())->toBe(1);
    $setting = Setting::first();
    expect($setting->applicationadsactive)->toBe('Y')
        ->and($setting->applicationadsbottomactive)->toBe('Y')
        ->and($setting->applicationads1)->not->toBeNull()
        ->and($setting->applicationads2)->not->toBeNull()
        ->and($setting->applicationadsbottom)->not->toBeNull();

    Storage::disk('public')->assertExists($setting->applicationads1);
    Storage::disk('public')->assertExists($setting->applicationads2);
    Storage::disk('public')->assertExists($setting->applicationadsbottom);
});

test('admin can save different active status for each ad slot', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'N',
        'applicationadsbottomactive' => 'N',
    ]);

    $response->assertRedirect('/admin/ads');
    $response->assertSessionHasNoErrors();

    $setting = Setting::first();
    expect($setting->applicationads1active)->toBe('Y')
        ->and($setting->applicationads2active)->toBe('N')
        ->and($setting->applicationadsbottomactive)->toBe('N')
        ->and($setting->applicationadsactive)->toBe('Y');
});

test('legacy main ad status is inactive when both main slots are inactive', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'N',
        'applicationads2active' => 'N',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertRedirect('/admin/ads');
    $response->assertSessionHasNoErrors();

    $setting = Setting::first();
    expect($setting->applicationadsactive)->toBe('N')
        ->and($setting->applicationadsbottomactive)->toBe('Y');
});

test('admin save rejects invalid per-slot active status', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'INVALID',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertSessionHasErrors(['applicationads1active']);
});

test('admin can save click url for each image banner', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads1clickurl' => 'https://example.com/slot-1',
        'applicationads2active' => 'Y',
        'applicationads2clickurl' => 'https://example.com/slot-2',
        'applicationadsbottomactive' => 'Y',
        'applicationadsbottomclickurl' => 'https://example.com/footer',
    ]);

    $response->assertRedirect('/admin/ads');
    $response->assertSessionHasNoErrors();

    $setting = Setting::first();
    expect($setting->applicationads1clickurl)->toBe('https://example.com/slot-1')
        ->and($setting->applicationads2clickurl)->toBe('https://example.com/slot-2')
        ->and($setting->applicationadsbottomclickurl)->toBe('https://example.com/footer');
});

test('admin can save ad configuration for home and lyric pages', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $homeBanner = UploadedFile::fake()->image('home-banner.jpg', 600, 200);

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'applicationadshome' => $homeBanner,
        'applicationadshomeactive' => 'Y',
        'applicationadshomeclickurl' => 'https://example.com/home',
        'applicationadslyrictype' => 'admob_banner',
        'applicationadslyricunit' => 'ca-app-pub-1234567890123456/4444444444',
        'applicationadslyricactive' => 'N',
    ]);

    $response->assertRedirect('/admin/ads');
    $response->assertSessionHasNoErrors();

    $setting = Setting::first();
    expect($setting->applicationadshome)->not->toBeNull()
        ->and($setting->applicationadshomeactive)->toBe('Y')
        ->and($setting->applicationadshomeclickurl)->toBe('https://example.com/home')
        ->and($setting->applicationadslyrictype)->toBe('admob_banner')
        ->and($setting->applicationadslyricunit)->toBe('ca-app-pub-1234567890123456/4444444444')
        ->and($setting->applicationadslyricactive)->toBe('N');

    Storage::disk('public')->assertExists($setting->applicationadshome);
});

test('admin can save separate lyric page top and bottom slots', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $topBanner = UploadedFile::fake()->image('lyric-top.jpg', 600, 200);
    $bottomBanner = UploadedFile::fake()->image('lyric-bottom.jpg', 600, 200);

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'applicationadslyrictop' => $topBanner,
        'applicationadslyrictopactive' => 'Y',
        'applicationadslyrictopclickurl' => 'https://example.com/lyric-top',
        'applicationadslyricbottomtype' => 'admob_banner',
        'applicationadslyricbottomunit' => 'ca-app-pub-1234567890123456/5555555555',
        'applicationadslyricbottomactive' => 'N',
        'applicationadslyricbottomclickurl' => 'https://example.com/lyric-bottom',
    ]);

    $response->assertRedirect('/admin/ads');
    $response->assertSessionHasNoErrors();

    $setting = Setting::first();
    expect($setting->applicationadslyrictop)->not->toBeNull()
        ->and($setting->applicationadslyrictopactive)->toBe('Y')
        ->and($setting->applicationadslyrictopclickurl)->toBe('https://example.com/lyric-top')
        ->and($setting->applicationadslyricbottomtype)->toBe('admob_banner')
        ->and($setting->applicationadslyricbottomunit)->toBe('ca-app-pub-1234567890123456/5555555555')
        ->and($setting->applicationadslyricbottomactive)->toBe('N')
        ->and($setting->applicationadslyricbottomclickurl)->toBe('https://example.com/lyric-bottom');

    Storage::disk('public')->assertExists($setting->applicationadslyrictop);
});

test('admin save requires ad unit id for lyric page top and bottom admob slots', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'applicationadslyrictoptype' => 'admob_native',
        'applicationadslyrictopunit' => '',
        'applicationadslyricbottomtype' => 'admob_banner',
        'applicationadslyricbottomunit' => '',
    ]);

    $response->assertSessionHasErrors([
        'applicationadslyrictopunit',
        'applicationadslyricbottomunit',
    ]);
});

test('admin save requires ad unit id for home and lyric admob slots', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'applicationadshometype' => 'admob_native',
        'applicationadshomeunit' => '',
        'applicationadslyrictype' => 'admob_interstitial',
        'applicationadslyricunit' => '',
    ]);

    $response->assertSessionHasErrors([
        'applicationadshomeunit',
        'applicationadslyricunit',
    ]);
});

test('admin save rejects invalid banner click url', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads1clickurl' => 'bukan-url',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertSessionHasErrors(['applicationads1clickurl']);
});

test('deleting banner also clears its click url', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $bannerPath = UploadedFile::fake()->image('banner-with-link.jpg')->store('banners', 'public');

    Setting::factory()->create([
        'applicationads1' => $bannerPath,
        'applicationads1clickurl' => 'https://example.com/promo',
    ]);

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'delete_applicationads1' => '1',
    ]);

    $response->assertRedirect('/admin/ads');

    $setting = Setting::first();
    expect($setting->applicationads1)->toBeNull()
        ->and($setting->applicationads1clickurl)->toBeNull();
});

test('admin can upload new banner file and delete old file', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $oldFile = UploadedFile::fake()->image('old-banner.jpg');
    $oldPath = $oldFile->store('banners', 'public');

    $setting = Setting::factory()->create([
        'applicationads1' => $oldPath,
    ]);

    Storage::disk('public')->assertExists($oldPath);

    $newFile = UploadedFile::fake()->image('new-banner.jpg');

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1' => $newFile,
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertRedirect('/admin/ads');
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
        'applicationads1' => $bannerPath,
    ]);

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertRedirect('/admin/ads');
    $setting->refresh();

    expect($setting->applicationads1)->toBe($bannerPath);

    Storage::disk('public')->assertExists($bannerPath);
});

test('admin can remove banner by selecting delete checkbox', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $bannerFile = UploadedFile::fake()->image('banner-to-delete.jpg');
    $bannerPath = $bannerFile->store('banners', 'public');

    $setting = Setting::factory()->create([
        'applicationads1' => $bannerPath,
    ]);

    Storage::disk('public')->assertExists($bannerPath);

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'delete_applicationads1' => '1',
    ]);

    $response->assertRedirect('/admin/ads');
    $setting->refresh();

    expect($setting->applicationads1)->toBeNull();
    Storage::disk('public')->assertMissing($bannerPath);
});

test('validation fails when uploaded file is not an image or exceeds 2MB', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $nonImage = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
    $oversizedImage = UploadedFile::fake()->image('huge.jpg')->size(3000); // 3MB

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1' => $nonImage,
        'applicationads2' => $oversizedImage,
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertSessionHasErrors([
        'applicationads1',
        'applicationads2',
    ]);
});

test('admin can save admob ad type with ad unit id without uploading image', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1type' => 'admob_native',
        'applicationads1unit' => 'ca-app-pub-1234567890123456/1111111111',
        'applicationads2type' => 'admob_banner',
        'applicationads2unit' => 'ca-app-pub-1234567890123456/3333333333',
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomtype' => 'admob_interstitial',
        'applicationadsbottomunit' => 'ca-app-pub-1234567890123456/2222222222',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertRedirect('/admin/ads');
    $response->assertSessionHasNoErrors();

    $setting = Setting::first();
    expect($setting->applicationads1type)->toBe('admob_native')
        ->and($setting->applicationads1unit)->toBe('ca-app-pub-1234567890123456/1111111111')
        ->and($setting->applicationads2type)->toBe('admob_banner')
        ->and($setting->applicationads2unit)->toBe('ca-app-pub-1234567890123456/3333333333')
        ->and($setting->applicationadsbottomtype)->toBe('admob_interstitial')
        ->and($setting->applicationadsbottomunit)->toBe('ca-app-pub-1234567890123456/2222222222');
});

test('admin save requires ad unit id when ad type is admob', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1type' => 'admob_banner',
        'applicationads1unit' => '',
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertSessionHasErrors(['applicationads1unit']);
});

test('admin save rejects unknown ad type', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1type' => 'unknown_type',
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertSessionHasErrors(['applicationads1type']);
});

test('admin ads page shows the music page slots', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/ads');

    $response->assertStatus(200);
    $response->assertSee('Iklan Halaman BUKU MERAH (Musik)');
    $response->assertSee('Halaman Musik — Atas');
    $response->assertSee('Halaman Musik — Pop-up');
    $response->assertSee('Halaman Musik — Bawah');
    $response->assertSee('name="applicationadsmusiktopactive"', false);
    $response->assertSee('name="applicationadsmusikactive"', false);
    $response->assertSee('name="applicationadsmusikbottomactive"', false);
    $response->assertSee('name="applicationadsmusiktopclickurl"', false);
    $response->assertSee('name="applicationadsmusikclickurl"', false);
    $response->assertSee('name="applicationadsmusikbottomclickurl"', false);
});

test('admin can save music page top popup and bottom slots', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $topBanner = UploadedFile::fake()->image('musik-top.jpg', 600, 200);
    $bottomBanner = UploadedFile::fake()->image('musik-bottom.jpg', 600, 200);

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'applicationadsmusiktop' => $topBanner,
        'applicationadsmusiktopactive' => 'Y',
        'applicationadsmusiktopclickurl' => 'https://example.com/musik-top',
        'applicationadsmusiktype' => 'admob_banner',
        'applicationadsmusikunit' => 'ca-app-pub-1234567890123456/6666666666',
        'applicationadsmusikactive' => 'N',
        'applicationadsmusikbottom' => $bottomBanner,
        'applicationadsmusikbottomactive' => 'Y',
        'applicationadsmusikbottomclickurl' => 'https://example.com/musik-bottom',
    ]);

    $response->assertRedirect('/admin/ads');
    $response->assertSessionHasNoErrors();

    $setting = Setting::first();
    expect($setting->applicationadsmusiktop)->not->toBeNull()
        ->and($setting->applicationadsmusiktopactive)->toBe('Y')
        ->and($setting->applicationadsmusiktopclickurl)->toBe('https://example.com/musik-top')
        ->and($setting->applicationadsmusiktype)->toBe('admob_banner')
        ->and($setting->applicationadsmusikunit)->toBe('ca-app-pub-1234567890123456/6666666666')
        ->and($setting->applicationadsmusikactive)->toBe('N')
        ->and($setting->applicationadsmusikbottom)->not->toBeNull()
        ->and($setting->applicationadsmusikbottomactive)->toBe('Y')
        ->and($setting->applicationadsmusikbottomclickurl)->toBe('https://example.com/musik-bottom');

    Storage::disk('public')->assertExists($setting->applicationadsmusiktop);
    Storage::disk('public')->assertExists($setting->applicationadsmusikbottom);
});

test('admin save requires ad unit id for music page admob slots', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'applicationadsmusiktoptype' => 'admob_native',
        'applicationadsmusiktopunit' => '',
        'applicationadsmusiktype' => 'admob_banner',
        'applicationadsmusikunit' => '',
        'applicationadsmusikbottomtype' => 'admob_interstitial',
        'applicationadsmusikbottomunit' => '',
    ]);

    $response->assertSessionHasErrors([
        'applicationadsmusiktopunit',
        'applicationadsmusikunit',
        'applicationadsmusikbottomunit',
    ]);
});

test('deleting music page banner also clears its click url', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $bannerPath = UploadedFile::fake()->image('musik-banner.jpg')->store('banners', 'public');

    Setting::factory()->create([
        'applicationadsmusiktop' => $bannerPath,
        'applicationadsmusiktopclickurl' => 'https://example.com/musik-promo',
    ]);

    $response = $this->actingAs($admin)->put('/admin/ads', [
        'applicationads1active' => 'Y',
        'applicationads2active' => 'Y',
        'applicationadsbottomactive' => 'Y',
        'delete_applicationadsmusiktop' => '1',
    ]);

    $response->assertRedirect('/admin/ads');

    $setting = Setting::first();
    expect($setting->applicationadsmusiktop)->toBeNull()
        ->and($setting->applicationadsmusiktopclickurl)->toBeNull();

    Storage::disk('public')->assertMissing($bannerPath);
});
