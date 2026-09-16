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
        'applicationads2' => 'https://example.com/banner2.jpg',
        'applicationadsactive' => 'Y',
        'applicationadsbottom' => 'https://example.com/bottom.jpg',
        'applicationadsbottomactive' => 'Y',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'applicationid',
                'applicationcompany',
                'applicationname',
                'applicationads1',
                'applicationads2',
                'applicationadsactive',
                'applicationadsbottom',
                'applicationadsbottomactive',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'message' => 'Settings created successfully',
            'data' => [
                'applicationcompany' => 'Media Prima Nusantara',
                'applicationname' => 'Karaoke Pro Station',
                'applicationadsactive' => 'Y',
                'applicationadsbottomactive' => 'Y',
            ],
        ]);

    $this->assertDatabaseHas('tb_application', [
        'applicationcompany' => 'Media Prima Nusantara',
        'applicationname' => 'Karaoke Pro Station',
        'applicationadsactive' => 'Y',
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
        'applicationadsactive' => 'INVALID',
        'applicationadsbottomactive' => 'INVALID',
    ]);

    $responseInvalidEnum->assertStatus(422)
        ->assertJsonValidationErrors(['applicationadsactive', 'applicationadsbottomactive']);
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
