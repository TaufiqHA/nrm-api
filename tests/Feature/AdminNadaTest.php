<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Nada;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login when storing a nada', function () {
    $response = $this->post('/admin/nadas', [
        'nada' => 'Duet',
    ]);

    $response->assertRedirect('/login');
});

test('regular user is forbidden from storing a nada', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->post('/admin/nadas', [
        'nada' => 'Duet',
    ]);

    $response->assertStatus(403);
});

test('admin can store a new nada', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->from('/admin/songs')
        ->post('/admin/nadas', [
            'nada' => 'Duet',
        ]);

    $response->assertRedirect('/admin/songs');
    $this->assertDatabaseHas('nadas', [
        'nada' => 'Duet',
    ]);
});

test('store nada validates required and unique', function () {
    $admin = User::factory()->admin()->create();
    Nada::factory()->create(['nada' => 'Pria']);

    $response = $this->actingAs($admin)->post('/admin/nadas', [
        'nada' => '',
    ]);
    $response->assertSessionHasErrors(['nada']);

    $duplicateResponse = $this->actingAs($admin)->post('/admin/nadas', [
        'nada' => 'Pria',
    ]);
    $duplicateResponse->assertSessionHasErrors(['nada']);
});

test('admin can update a nada', function () {
    $admin = User::factory()->admin()->create();
    $nada = Nada::factory()->create(['nada' => 'Old Nada']);

    $response = $this->actingAs($admin)
        ->from('/admin/songs')
        ->put("/admin/nadas/{$nada->id}", [
            'nada' => 'Updated Nada',
        ]);

    $response->assertRedirect('/admin/songs');
    $this->assertDatabaseHas('nadas', [
        'id' => $nada->id,
        'nada' => 'Updated Nada',
    ]);
});

test('admin can delete a nada', function () {
    $admin = User::factory()->admin()->create();
    $nada = Nada::factory()->create(['nada' => 'Duet']);

    $response = $this->actingAs($admin)
        ->from('/admin/songs')
        ->delete("/admin/nadas/{$nada->id}");

    $response->assertRedirect('/admin/songs');
    $this->assertDatabaseMissing('nadas', [
        'id' => $nada->id,
    ]);
});

test('admin cannot delete a nada if used by songs', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $nada = Nada::factory()->create(['nada' => 'Pria']);

    Song::factory()->create([
        'songcategory' => $category->songcategoryid,
        'songnada' => 'Pria',
    ]);

    $response = $this->actingAs($admin)->delete("/admin/nadas/{$nada->id}");

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('nadas', [
        'id' => $nada->id,
    ]);
});
