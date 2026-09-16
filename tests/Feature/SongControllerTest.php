<?php

use App\Models\Category;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('guest can list all songs with category details', function () {
    $pop = Category::factory()->create(['songcategoryname' => 'Pop']);
    $rock = Category::factory()->create(['songcategoryname' => 'Rock']);

    Song::factory()->create([
        'songtitle' => 'Song A',
        'songcategory' => $pop->songcategoryid,
    ]);
    Song::factory()->create([
        'songtitle' => 'Song B',
        'songcategory' => $rock->songcategoryid,
    ]);

    $response = $this->getJson('/api/songs');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'songid',
                    'songtitle',
                    'songsinger',
                    'songurl',
                    'songcategory',
                    'songnada',
                    'songduration',
                    'created_at',
                    'updated_at',
                    'category' => [
                        'songcategoryid',
                        'songcategoryname',
                    ],
                ],
            ],
        ])
        ->assertJsonCount(2, 'data');
});

test('guest can search songs by title or singer', function () {
    $category = Category::factory()->create();

    Song::factory()->create([
        'songtitle' => 'Separuh Nafas',
        'songsinger' => 'Dewa 19',
        'songcategory' => $category->songcategoryid,
    ]);
    Song::factory()->create([
        'songtitle' => 'Kemesraan',
        'songsinger' => 'Iwan Fals',
        'songcategory' => $category->songcategoryid,
    ]);

    // Search by title
    $responseTitle = $this->getJson('/api/songs?search=Separuh');
    $responseTitle->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.songtitle', 'Separuh Nafas');

    // Search by singer
    $responseSinger = $this->getJson('/api/songs?search=Iwan');
    $responseSinger->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.songsinger', 'Iwan Fals');
});

test('guest can filter songs by category', function () {
    $pop = Category::factory()->create(['songcategoryname' => 'Pop']);
    $rock = Category::factory()->create(['songcategoryname' => 'Rock']);

    Song::factory()->create([
        'songtitle' => 'Pop Song',
        'songcategory' => $pop->songcategoryid,
    ]);
    Song::factory()->create([
        'songtitle' => 'Rock Song',
        'songcategory' => $rock->songcategoryid,
    ]);

    $response = $this->getJson("/api/songs?songcategory={$pop->songcategoryid}");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.songtitle', 'Pop Song');

    $responseByCategoryId = $this->getJson("/api/songs?category_id={$rock->songcategoryid}");
    $responseByCategoryId->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.songtitle', 'Rock Song');
});

test('guest can paginate songs', function () {
    $category = Category::factory()->create();
    Song::factory()->count(5)->create(['songcategory' => $category->songcategoryid]);

    $response = $this->getJson('/api/songs?page=1&per_page=2');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'current_page',
            'data',
            'total',
            'per_page',
        ])
        ->assertJsonPath('per_page', 2)
        ->assertJsonPath('total', 5)
        ->assertJsonCount(2, 'data');
});

test('guest can view a specific song', function () {
    $category = Category::factory()->create(['songcategoryname' => 'Dangdut']);
    $song = Song::factory()->create([
        'songtitle' => 'Kopi Dangdut',
        'songsinger' => 'Fahmi Shahab',
        'songurl' => 'https://example.com/kopi.mp4',
        'songcategory' => $category->songcategoryid,
        'songnada' => 'Am',
        'songduration' => '4:20',
    ]);

    $response = $this->getJson("/api/songs/{$song->songid}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'songid' => $song->songid,
                'songtitle' => 'Kopi Dangdut',
                'songsinger' => 'Fahmi Shahab',
                'songurl' => 'https://example.com/kopi.mp4',
                'songcategory' => $category->songcategoryid,
                'songnada' => 'Am',
                'songduration' => '4:20',
                'category' => [
                    'songcategoryid' => $category->songcategoryid,
                    'songcategoryname' => 'Dangdut',
                ],
            ],
        ]);
});

test('returns 404 if song is not found', function () {
    $response = $this->getJson('/api/songs/999');

    $response->assertStatus(404);
});

test('unauthenticated user cannot create a song', function () {
    $category = Category::factory()->create();

    $response = $this->postJson('/api/songs', [
        'songtitle' => 'Test Song',
        'songsinger' => 'Test Singer',
        'songurl' => 'https://example.com/test.mp4',
        'songcategory' => $category->songcategoryid,
    ]);

    $response->assertStatus(401);
});

test('authenticated user can create a song', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = Category::factory()->create(['songcategoryname' => 'Pop']);

    $payload = [
        'songtitle' => 'Hati-Hati di Jalan',
        'songsinger' => 'Tulus',
        'songurl' => 'https://example.com/tulus.mp4',
        'songcategory' => $category->songcategoryid,
        'songnada' => 'C',
        'songduration' => '4:02',
    ];

    $response = $this->postJson('/api/songs', $payload);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'songid',
                'songtitle',
                'songsinger',
                'songurl',
                'songcategory',
                'songnada',
                'songduration',
                'category',
            ],
        ])
        ->assertJson([
            'message' => 'Song created successfully',
            'data' => [
                'songtitle' => 'Hati-Hati di Jalan',
                'songsinger' => 'Tulus',
                'songurl' => 'https://example.com/tulus.mp4',
                'songcategory' => $category->songcategoryid,
                'songnada' => 'C',
                'songduration' => '4:02',
            ],
        ]);

    $this->assertDatabaseHas('tb_songs', [
        'songtitle' => 'Hati-Hati di Jalan',
        'songsinger' => 'Tulus',
        'songcategory' => $category->songcategoryid,
    ]);
});

test('song creation validates required fields', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/songs', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['songtitle', 'songsinger', 'songurl', 'songcategory']);
});

test('song creation validates existing category', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/songs', [
        'songtitle' => 'Test Song',
        'songsinger' => 'Test Singer',
        'songurl' => 'https://example.com/test.mp4',
        'songcategory' => 9999,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['songcategory']);
});

test('unauthenticated user cannot update a song', function () {
    $song = Song::factory()->create();

    $response = $this->putJson("/api/songs/{$song->songid}", [
        'songtitle' => 'Updated Title',
    ]);

    $response->assertStatus(401);
});

test('authenticated user can update a song', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $song = Song::factory()->create([
        'songtitle' => 'Original Title',
    ]);

    $response = $this->putJson("/api/songs/{$song->songid}", [
        'songtitle' => 'Updated Title',
        'songnada' => 'G',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Song updated successfully',
            'data' => [
                'songid' => $song->songid,
                'songtitle' => 'Updated Title',
                'songnada' => 'G',
            ],
        ]);

    $this->assertDatabaseHas('tb_songs', [
        'songid' => $song->songid,
        'songtitle' => 'Updated Title',
        'songnada' => 'G',
    ]);
});

test('unauthenticated user cannot delete a song', function () {
    $song = Song::factory()->create();

    $response = $this->deleteJson("/api/songs/{$song->songid}");

    $response->assertStatus(401);
});

test('authenticated user can delete a song', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $song = Song::factory()->create();

    $response = $this->deleteJson("/api/songs/{$song->songid}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Song deleted successfully',
        ]);

    $this->assertDatabaseMissing('tb_songs', [
        'songid' => $song->songid,
    ]);
});
