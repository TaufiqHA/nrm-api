<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Nada;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access admin nadas endpoints', function () {
    $nada = Nada::factory()->create();

    $this->getJson('/api/admin/nadas')->assertStatus(401);
    $this->postJson('/api/admin/nadas', ['nada' => 'Test'])->assertStatus(401);
    $this->getJson("/api/admin/nadas/{$nada->id}")->assertStatus(401);
    $this->putJson("/api/admin/nadas/{$nada->id}", ['nada' => 'Updated'])->assertStatus(401);
    $this->deleteJson("/api/admin/nadas/{$nada->id}")->assertStatus(401);
});

test('non-admin user receives 403 forbidden on admin nadas endpoints', function () {
    $regularUser = User::factory()->create([
        'role' => UserRole::User,
    ]);
    Sanctum::actingAs($regularUser);

    $nada = Nada::factory()->create();

    $this->getJson('/api/admin/nadas')
        ->assertStatus(403)
        ->assertJson(['message' => 'Forbidden. Admin access required.']);

    $this->postJson('/api/admin/nadas', ['nada' => 'Test'])->assertStatus(403);
    $this->getJson("/api/admin/nadas/{$nada->id}")->assertStatus(403);
    $this->putJson("/api/admin/nadas/{$nada->id}", ['nada' => 'Updated'])->assertStatus(403);
    $this->deleteJson("/api/admin/nadas/{$nada->id}")->assertStatus(403);
});

test('admin can list all nadas', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    Nada::factory()->create(['nada' => 'Pria']);
    Nada::factory()->create(['nada' => 'Wanita']);

    $response = $this->getJson('/api/admin/nadas');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nada',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('admin can search nadas by query', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    Nada::factory()->create(['nada' => 'Pria Rendah']);
    Nada::factory()->create(['nada' => 'Wanita Tinggi']);

    $response = $this->getJson('/api/admin/nadas?search=Pria');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nada', 'Pria Rendah');
});

test('admin can view a specific nada', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $nada = Nada::factory()->create(['nada' => 'Duet']);

    $response = $this->getJson("/api/admin/nadas/{$nada->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $nada->id,
                'nada' => 'Duet',
            ],
        ]);
});

test('admin can create a new nada', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/admin/nadas', [
        'nada' => 'Anak-anak',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Nada created successfully',
            'data' => [
                'nada' => 'Anak-anak',
            ],
        ]);

    $this->assertDatabaseHas('nadas', [
        'nada' => 'Anak-anak',
    ]);
});

test('create nada validates required and unique rules', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    Nada::factory()->create(['nada' => 'Pria']);

    $this->postJson('/api/admin/nadas', [
        'nada' => '',
    ])->assertStatus(422)->assertJsonValidationErrors(['nada']);

    $this->postJson('/api/admin/nadas', [
        'nada' => 'Pria',
    ])->assertStatus(422)->assertJsonValidationErrors(['nada']);
});

test('admin can update a nada', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $nada = Nada::factory()->create(['nada' => 'Original']);

    $response = $this->putJson("/api/admin/nadas/{$nada->id}", [
        'nada' => 'Modified',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Nada updated successfully',
            'data' => [
                'id' => $nada->id,
                'nada' => 'Modified',
            ],
        ]);

    $this->assertDatabaseHas('nadas', [
        'id' => $nada->id,
        'nada' => 'Modified',
    ]);
});

test('admin can delete a nada', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $nada = Nada::factory()->create(['nada' => 'Duet']);

    $response = $this->deleteJson("/api/admin/nadas/{$nada->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Nada deleted successfully']);

    $this->assertDatabaseMissing('nadas', [
        'id' => $nada->id,
    ]);
});

test('admin cannot delete a nada if still used by a song', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $category = Category::factory()->create();
    $nada = Nada::factory()->create(['nada' => 'Pria']);

    Song::factory()->create([
        'songcategory' => $category->songcategoryid,
        'songnada' => 'Pria',
    ]);

    $response = $this->deleteJson("/api/admin/nadas/{$nada->id}");

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'Nada cannot be deleted because it is still used by one or more songs.',
        ]);

    $this->assertDatabaseHas('nadas', [
        'id' => $nada->id,
    ]);
});
