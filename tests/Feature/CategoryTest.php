<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('guest can list all categories', function () {
    Category::factory()->create(['songcategoryname' => 'Pop']);
    Category::factory()->create(['songcategoryname' => 'Rock']);

    $response = $this->getJson('/api/categories');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'songcategoryid',
                    'songcategoryname',
                    'created_at',
                    'updated_at',
                ],
            ],
        ])
        ->assertJsonCount(2, 'data');
});

test('guest can search categories by name', function () {
    Category::factory()->create(['songcategoryname' => 'Pop Indonesia']);
    Category::factory()->create(['songcategoryname' => 'Rock Barat']);

    $response = $this->getJson('/api/categories?search=Pop');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.songcategoryname', 'Pop Indonesia');
});

test('guest can view a specific category', function () {
    $category = Category::factory()->create(['songcategoryname' => 'Dangdut']);

    $response = $this->getJson("/api/categories/{$category->songcategoryid}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'songcategoryid' => $category->songcategoryid,
                'songcategoryname' => 'Dangdut',
            ],
        ]);
});

test('returns 404 if category is not found', function () {
    $response = $this->getJson('/api/categories/999');

    $response->assertStatus(404);
});

test('unauthenticated user cannot create a category', function () {
    $response = $this->postJson('/api/categories', [
        'songcategoryname' => 'Jazz',
    ]);

    $response->assertStatus(401);
});

test('authenticated user can create a category', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/categories', [
        'songcategoryname' => 'Jazz',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'songcategoryid',
                'songcategoryname',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'message' => 'Category created successfully',
            'data' => [
                'songcategoryname' => 'Jazz',
            ],
        ]);

    $this->assertDatabaseHas('categories', [
        'songcategoryname' => 'Jazz',
    ]);
});

test('category creation validates required fields', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/categories', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['songcategoryname']);
});

test('category creation validates unique category name', function () {
    Category::factory()->create(['songcategoryname' => 'Reggae']);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/categories', [
        'songcategoryname' => 'Reggae',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['songcategoryname']);
});

test('unauthenticated user cannot update a category', function () {
    $category = Category::factory()->create(['songcategoryname' => 'Blues']);

    $response = $this->putJson("/api/categories/{$category->songcategoryid}", [
        'songcategoryname' => 'Blues Rock',
    ]);

    $response->assertStatus(401);
});

test('authenticated user can update a category', function () {
    $category = Category::factory()->create(['songcategoryname' => 'Blues']);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/categories/{$category->songcategoryid}", [
        'songcategoryname' => 'Blues Rock',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Category updated successfully',
            'data' => [
                'songcategoryid' => $category->songcategoryid,
                'songcategoryname' => 'Blues Rock',
            ],
        ]);

    $this->assertDatabaseHas('categories', [
        'songcategoryid' => $category->songcategoryid,
        'songcategoryname' => 'Blues Rock',
    ]);
});

test('category update allows keeping the same name', function () {
    $category = Category::factory()->create(['songcategoryname' => 'Blues']);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/categories/{$category->songcategoryid}", [
        'songcategoryname' => 'Blues',
    ]);

    $response->assertStatus(200);
});

test('category update rejects duplicate name from another category', function () {
    Category::factory()->create(['songcategoryname' => 'Pop']);
    $rock = Category::factory()->create(['songcategoryname' => 'Rock']);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/categories/{$rock->songcategoryid}", [
        'songcategoryname' => 'Pop',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['songcategoryname']);
});

test('unauthenticated user cannot delete a category', function () {
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/categories/{$category->songcategoryid}");

    $response->assertStatus(401);
});

test('authenticated user can delete a category', function () {
    $category = Category::factory()->create(['songcategoryname' => 'Disco']);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/categories/{$category->songcategoryid}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Category deleted successfully',
        ]);

    $this->assertDatabaseMissing('categories', [
        'songcategoryid' => $category->songcategoryid,
    ]);
});
