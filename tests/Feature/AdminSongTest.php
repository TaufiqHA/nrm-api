<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login when accessing admin songs', function () {
    $response = $this->get('/admin/songs');

    $response->assertRedirect('/login');
});

test('regular user is forbidden from accessing admin songs', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->get('/admin/songs');

    $response->assertStatus(403);
});

test('admin can view songs catalog list', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['songcategoryname' => 'Pop Indo']);

    Song::factory()->create([
        'songtitle' => 'Kemesraan',
        'songsinger' => 'Iwan Fals',
        'songnada' => 'pria',
        'songcategory' => $category->songcategoryid,
    ]);

    $response = $this->actingAs($admin)->get('/admin/songs');

    $response->assertStatus(200);
    $response->assertSee('Katalog Lagu');
    $response->assertSee('Kemesraan');
    $response->assertSee('Iwan Fals');
    $response->assertSee('Pop Indo');
    $response->assertSee('Judul Lagu & Pencipta');
    $response->assertSee('Pria');
    $response->assertSee('Tambah Lagu');
    $response->assertSee('Tambah Nada');
    $response->assertSee('URL Lagu');
});

test('admin can search songs by title or singer', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Song::factory()->create([
        'songtitle' => 'Separuh Aku',
        'songsinger' => 'Noah',
        'songcategory' => $category->songcategoryid,
    ]);
    Song::factory()->create([
        'songtitle' => 'Dan',
        'songsinger' => 'Sheila On 7',
        'songcategory' => $category->songcategoryid,
    ]);

    // Search by title
    $responseTitle = $this->actingAs($admin)->get('/admin/songs?search=Separuh');
    $responseTitle->assertStatus(200);
    $responseTitle->assertSee('Separuh Aku');
    $responseTitle->assertDontSee('Sheila On 7');

    // Search by singer
    $responseSinger = $this->actingAs($admin)->get('/admin/songs?search=Sheila');
    $responseSinger->assertStatus(200);
    $responseSinger->assertSee('Sheila On 7');
    $responseSinger->assertDontSee('Separuh Aku');
});

test('admin can filter songs by category', function () {
    $admin = User::factory()->admin()->create();

    $pop = Category::factory()->create(['songcategoryname' => 'Pop Music']);
    $rock = Category::factory()->create(['songcategoryname' => 'Rock Music']);

    Song::factory()->create([
        'songtitle' => 'Pop Song 1',
        'songcategory' => $pop->songcategoryid,
    ]);
    Song::factory()->create([
        'songtitle' => 'Rock Song 1',
        'songcategory' => $rock->songcategoryid,
    ]);

    $response = $this->actingAs($admin)->get("/admin/songs?category={$pop->songcategoryid}");

    $response->assertStatus(200);
    $response->assertSee('Pop Song 1');
    $response->assertDontSee('Rock Song 1');
});

test('admin can store a new song', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/songs', [
        'songtitle' => 'Hampa',
        'songsinger' => 'Ari Lasso',
        'songcategory' => $category->songcategoryid,
        'songnada' => 'pria',
        'songduration' => '04:30',
        'songurl' => 'https://example.com/audio/hampa.mp3',
    ]);

    $response->assertRedirect('/admin/songs');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('tb_songs', [
        'songtitle' => 'Hampa',
        'songsinger' => 'Ari Lasso',
        'songcategory' => $category->songcategoryid,
        'songnada' => 'pria',
    ]);
});

test('song creation fails when required fields are missing', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->from('/admin/songs')->post('/admin/songs', [
        'songtitle' => '',
        'songsinger' => '',
        'songcategory' => '',
        'songurl' => '',
    ]);

    $response->assertRedirect('/admin/songs');
    $response->assertSessionHasErrors(['songtitle', 'songsinger', 'songcategory', 'songurl']);
    $response->assertSessionHasErrors([
        'songsinger' => 'Pencipta wajib diisi.',
        'songurl' => 'URL lagu wajib diisi.',
    ]);
});

test('song creation fails when songnada is not in pria, wanita, or -', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->from('/admin/songs')->post('/admin/songs', [
        'songtitle' => 'Invalid Nada Song',
        'songsinger' => 'Singer',
        'songcategory' => $category->songcategoryid,
        'songurl' => 'https://example.com/song.mp3',
        'songnada' => 'Am',
    ]);

    $response->assertRedirect('/admin/songs');
    $response->assertSessionHasErrors(['songnada']);
});

test('admin can update an existing song', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $song = Song::factory()->create([
        'songcategory' => $category->songcategoryid,
        'songtitle' => 'Original Title',
    ]);

    $response = $this->actingAs($admin)->put("/admin/songs/{$song->songid}", [
        'songtitle' => 'Updated Song Title',
        'songsinger' => $song->songsinger,
        'songcategory' => $category->songcategoryid,
        'songnada' => 'wanita',
        'songduration' => '03:50',
        'songurl' => 'https://example.com/updated.mp3',
    ]);

    $response->assertRedirect('/admin/songs');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('tb_songs', [
        'songid' => $song->songid,
        'songtitle' => 'Updated Song Title',
        'songnada' => 'wanita',
    ]);
});

test('admin can delete a song', function () {
    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/songs/{$song->songid}");

    $response->assertRedirect('/admin/songs');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('tb_songs', [
        'songid' => $song->songid,
    ]);
});
