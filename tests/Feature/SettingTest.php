<?php

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('guest can get settings when empty', function () {
    $response = $this->getJson('/api/settings');

    $response->assertStatus(200)
        ->assertJson([
            'data' => null,
        ]);
});

test('guest can get settings when configured', function () {
    $setting = Setting::factory()->create([
        'applicationcompany' => 'PT Karaoke Digital',
        'applicationname' => 'Karaoke Family',
        'applicationadsactive' => 'Y',
        'applicationadsbottomactive' => 'N',
    ]);

    $response = $this->getJson('/api/settings');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'applicationid' => $setting->applicationid,
                'applicationcompany' => 'PT Karaoke Digital',
                'applicationname' => 'Karaoke Family',
                'applicationadsactive' => 'Y',
                'applicationadsbottomactive' => 'N',
            ],
        ]);
});

test('unauthenticated user cannot access admin settings endpoints', function () {
    $setting = Setting::factory()->create();

    $this->getJson('/api/admin/settings')->assertStatus(401);
    $this->postJson('/api/admin/settings', [])->assertStatus(401);
    $this->putJson('/api/admin/settings', [])->assertStatus(401);
    $this->getJson("/api/admin/settings/{$setting->applicationid}")->assertStatus(401);
    $this->putJson("/api/admin/settings/{$setting->applicationid}", [])->assertStatus(401);
    $this->deleteJson("/api/admin/settings/{$setting->applicationid}")->assertStatus(401);
});

test('regular user receives 403 forbidden on admin settings endpoints', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    Sanctum::actingAs($user);

    $setting = Setting::factory()->create();

    $this->getJson('/api/admin/settings')
        ->assertStatus(403)
        ->assertJson(['message' => 'Forbidden. Admin access required.']);

    $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'Test',
        'applicationname' => 'Test App',
    ])->assertStatus(403);

    $this->putJson('/api/admin/settings', [
        'applicationcompany' => 'Test',
    ])->assertStatus(403);

    $this->getJson("/api/admin/settings/{$setting->applicationid}")->assertStatus(403);
    $this->putJson("/api/admin/settings/{$setting->applicationid}", ['applicationname' => 'New'])->assertStatus(403);
    $this->deleteJson("/api/admin/settings/{$setting->applicationid}")->assertStatus(403);
});

test('admin can create initial settings via post', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'Media Prima Nusantara',
        'applicationname' => 'Karaoke Pro Station',
        'applicationads1' => 'https://example.com/banner1.jpg',
        'applicationads1active' => 'Y',
        'applicationads1clickurl' => 'https://example.com/click-1',
        'applicationads2' => 'https://example.com/banner2.jpg',
        'applicationads2active' => 'N',
        'applicationads2clickurl' => 'https://example.com/click-2',
        'applicationadsactive' => 'Y',
        'applicationadsbottom' => 'https://example.com/bottom.jpg',
        'applicationadsbottomactive' => 'Y',
        'applicationadsbottomclickurl' => 'https://example.com/click-bottom',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'applicationid',
                'applicationcompany',
                'applicationname',
                'applicationads1',
                'applicationads1active',
                'applicationads1clickurl',
                'applicationads2',
                'applicationads2active',
                'applicationads2clickurl',
                'applicationadsactive',
                'applicationadsbottom',
                'applicationadsbottomactive',
                'applicationadsbottomclickurl',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'message' => 'Settings created successfully',
            'data' => [
                'applicationcompany' => 'Media Prima Nusantara',
                'applicationname' => 'Karaoke Pro Station',
                'applicationads1active' => 'Y',
                'applicationads1clickurl' => 'https://example.com/click-1',
                'applicationads2active' => 'N',
                'applicationads2clickurl' => 'https://example.com/click-2',
                'applicationadsactive' => 'Y',
                'applicationadsbottomactive' => 'Y',
                'applicationadsbottomclickurl' => 'https://example.com/click-bottom',
            ],
        ]);

    $this->assertDatabaseHas('tb_application', [
        'applicationcompany' => 'Media Prima Nusantara',
        'applicationname' => 'Karaoke Pro Station',
        'applicationads1active' => 'Y',
        'applicationads1clickurl' => 'https://example.com/click-1',
        'applicationads2active' => 'N',
        'applicationads2clickurl' => 'https://example.com/click-2',
        'applicationadsactive' => 'Y',
        'applicationadsbottomclickurl' => 'https://example.com/click-bottom',
    ]);
});

test('admin settings api maps legacy active status to both main slots', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'PT ABC',
        'applicationname' => 'Karaoke App',
        'applicationadsactive' => 'N',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'applicationads1active' => 'N',
                'applicationads2active' => 'N',
                'applicationadsactive' => 'N',
            ],
        ]);
});

test('admin settings api syncs legacy status from per-slot statuses', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    Setting::factory()->create();

    $response = $this->putJson('/api/admin/settings', [
        'applicationads1active' => 'N',
        'applicationads2active' => 'N',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'applicationads1active' => 'N',
                'applicationads2active' => 'N',
                'applicationadsactive' => 'N',
            ],
        ]);
});

test('admin can update existing settings via put', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    Setting::factory()->create([
        'applicationcompany' => 'Old Company',
        'applicationname' => 'Old App',
    ]);

    $response = $this->putJson('/api/admin/settings', [
        'applicationcompany' => 'Updated Company',
        'applicationname' => 'Updated App',
        'applicationadsactive' => 'N',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Settings updated successfully',
            'data' => [
                'applicationcompany' => 'Updated Company',
                'applicationname' => 'Updated App',
                'applicationadsactive' => 'N',
            ],
        ]);

    $this->assertDatabaseHas('tb_application', [
        'applicationcompany' => 'Updated Company',
        'applicationname' => 'Updated App',
        'applicationadsactive' => 'N',
    ]);
});

test('settings creation validates required fields and enum values', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', []);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['applicationcompany', 'applicationname']);

    $responseInvalidEnum = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'PT ABC',
        'applicationname' => 'Karaoke App',
        'applicationads1active' => 'INVALID',
        'applicationads2active' => 'INVALID',
        'applicationadsactive' => 'INVALID',
        'applicationadsbottomactive' => 'INVALID',
    ]);

    $responseInvalidEnum->assertStatus(422)
        ->assertJsonValidationErrors([
            'applicationads1active',
            'applicationads2active',
            'applicationadsactive',
            'applicationadsbottomactive',
        ]);
});

test('admin can view specific setting by id', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $setting = Setting::factory()->create([
        'applicationname' => 'Specific App',
    ]);

    $response = $this->getJson("/api/admin/settings/{$setting->applicationid}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'applicationid' => $setting->applicationid,
                'applicationname' => 'Specific App',
            ],
        ]);
});

test('admin can update specific setting by id', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $setting = Setting::factory()->create([
        'applicationcompany' => 'Initial Company',
    ]);

    $response = $this->putJson("/api/admin/settings/{$setting->applicationid}", [
        'applicationcompany' => 'Modified Company',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Settings updated successfully',
            'data' => [
                'applicationid' => $setting->applicationid,
                'applicationcompany' => 'Modified Company',
            ],
        ]);

    $this->assertDatabaseHas('tb_application', [
        'applicationid' => $setting->applicationid,
        'applicationcompany' => 'Modified Company',
    ]);
});

test('admin can delete setting by id', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $setting = Setting::factory()->create();

    $response = $this->deleteJson("/api/admin/settings/{$setting->applicationid}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Settings deleted successfully',
        ]);

    $this->assertDatabaseMissing('tb_application', [
        'applicationid' => $setting->applicationid,
    ]);
});

test('admin can create settings with admob ad configuration via api', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'Media Prima Nusantara',
        'applicationname' => 'Karaoke AdMob',
        'applicationads1type' => 'admob_native',
        'applicationads1unit' => 'ca-app-pub-1234567890123456/1111111111',
        'applicationads2type' => 'image',
        'applicationadsactive' => 'Y',
        'applicationadsbottomtype' => 'admob_app_open',
        'applicationadsbottomunit' => 'ca-app-pub-1234567890123456/2222222222',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'applicationads1type' => 'admob_native',
                'applicationads1unit' => 'ca-app-pub-1234567890123456/1111111111',
                'applicationadsbottomtype' => 'admob_app_open',
            ],
        ]);

    $this->assertDatabaseHas('tb_application', [
        'applicationads1type' => 'admob_native',
        'applicationadsbottomtype' => 'admob_app_open',
        'applicationadsbottomunit' => 'ca-app-pub-1234567890123456/2222222222',
    ]);
});

test('admin can save home and lyric page ads via api', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'Media Prima Nusantara',
        'applicationname' => 'Karaoke Pages',
        'applicationadshome' => 'https://example.com/home.jpg',
        'applicationadshometype' => 'image',
        'applicationadshomeactive' => 'Y',
        'applicationadshomeclickurl' => 'https://example.com/home',
        'applicationadslyric' => '',
        'applicationadslyrictype' => 'admob_banner',
        'applicationadslyricunit' => 'ca-app-pub-1234567890123456/4444444444',
        'applicationadslyricactive' => 'N',
        'applicationadslyrictop' => 'https://example.com/lyric-top.jpg',
        'applicationadslyrictoptype' => 'image',
        'applicationadslyrictopactive' => 'Y',
        'applicationadslyrictopclickurl' => 'https://example.com/lyric-top',
        'applicationadslyricbottomtype' => 'admob_banner',
        'applicationadslyricbottomunit' => 'ca-app-pub-1234567890123456/5555555555',
        'applicationadslyricbottomactive' => 'N',
        'applicationadslyricbottomclickurl' => 'https://example.com/lyric-bottom',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'applicationadshome' => 'https://example.com/home.jpg',
                'applicationadshomeactive' => 'Y',
                'applicationadshomeclickurl' => 'https://example.com/home',
                'applicationadslyrictype' => 'admob_banner',
                'applicationadslyricunit' => 'ca-app-pub-1234567890123456/4444444444',
                'applicationadslyricactive' => 'N',
                'applicationadslyrictop' => 'https://example.com/lyric-top.jpg',
                'applicationadslyrictopactive' => 'Y',
                'applicationadslyricbottomtype' => 'admob_banner',
                'applicationadslyricbottomunit' => 'ca-app-pub-1234567890123456/5555555555',
                'applicationadslyricbottomactive' => 'N',
            ],
        ]);

    $this->assertDatabaseHas('tb_application', [
        'applicationadshomeactive' => 'Y',
        'applicationadshomeclickurl' => 'https://example.com/home',
        'applicationadslyrictype' => 'admob_banner',
        'applicationadslyricactive' => 'N',
        'applicationadslyrictopactive' => 'Y',
        'applicationadslyricbottomtype' => 'admob_banner',
        'applicationadslyricbottomactive' => 'N',
    ]);
});

test('admin settings api rejects invalid lyric page top and bottom ad values', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'PT ABC',
        'applicationname' => 'Karaoke App',
        'applicationadslyrictopclickurl' => 'bukan-url',
        'applicationadslyricbottomactive' => 'INVALID',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'applicationadslyrictopclickurl',
            'applicationadslyricbottomactive',
        ]);
});

test('admin settings api rejects invalid home and lyric page ad values', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'PT ABC',
        'applicationname' => 'Karaoke App',
        'applicationadshomeclickurl' => 'bukan-url',
        'applicationadslyricactive' => 'INVALID',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'applicationadshomeclickurl',
            'applicationadslyricactive',
        ]);
});

test('admin settings api rejects invalid banner click url', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    Setting::factory()->create();

    $response = $this->putJson('/api/admin/settings', [
        'applicationads1clickurl' => 'bukan-url',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['applicationads1clickurl']);
});

test('admin settings api rejects invalid ad type', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'PT ABC',
        'applicationname' => 'Karaoke App',
        'applicationads1type' => 'not_a_type',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['applicationads1type']);
});

test('admin can save music page ads via api', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'Media Prima Nusantara',
        'applicationname' => 'Karaoke Music Page',
        'applicationadsmusiktop' => 'https://example.com/musik-top.jpg',
        'applicationadsmusiktoptype' => 'image',
        'applicationadsmusiktopactive' => 'Y',
        'applicationadsmusiktopclickurl' => 'https://example.com/musik-top',
        'applicationadsmusiktype' => 'admob_banner',
        'applicationadsmusikunit' => 'ca-app-pub-1234567890123456/6666666666',
        'applicationadsmusikactive' => 'N',
        'applicationadsmusikbottomtype' => 'admob_banner',
        'applicationadsmusikbottomunit' => 'ca-app-pub-1234567890123456/7777777777',
        'applicationadsmusikbottomactive' => 'Y',
        'applicationadsmusikbottomclickurl' => 'https://example.com/musik-bottom',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'applicationadsmusiktop' => 'https://example.com/musik-top.jpg',
                'applicationadsmusiktopactive' => 'Y',
                'applicationadsmusiktopclickurl' => 'https://example.com/musik-top',
                'applicationadsmusiktype' => 'admob_banner',
                'applicationadsmusikunit' => 'ca-app-pub-1234567890123456/6666666666',
                'applicationadsmusikactive' => 'N',
                'applicationadsmusikbottomtype' => 'admob_banner',
                'applicationadsmusikbottomunit' => 'ca-app-pub-1234567890123456/7777777777',
                'applicationadsmusikbottomactive' => 'Y',
                'applicationadsmusikbottomclickurl' => 'https://example.com/musik-bottom',
            ],
        ]);

    $this->assertDatabaseHas('tb_application', [
        'applicationadsmusiktopactive' => 'Y',
        'applicationadsmusikactive' => 'N',
        'applicationadsmusikbottomtype' => 'admob_banner',
        'applicationadsmusikbottomactive' => 'Y',
    ]);
});

test('admin settings api rejects invalid music page ad values', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/settings', [
        'applicationcompany' => 'PT ABC',
        'applicationname' => 'Karaoke App',
        'applicationadsmusiktopclickurl' => 'bukan-url',
        'applicationadsmusikactive' => 'INVALID',
        'applicationadsmusikbottomtype' => 'not_a_type',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'applicationadsmusiktopclickurl',
            'applicationadsmusikactive',
            'applicationadsmusikbottomtype',
        ]);
});
