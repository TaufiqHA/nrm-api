<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login when accessing admin lyrics', function () {
    $response = $this->get('/admin/lyrics');

    $response->assertRedirect('/login');
});

test('regular user is forbidden from accessing admin lyrics', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->get('/admin/lyrics');

    $response->assertStatus(403);
});

test('admin can view lyric management list', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['songcategoryname' => 'Piano + Lirik']);

    Song::factory()->create([
        'songtitle' => 'Beribu Lidah Patutlah',
        'songsinger' => '4',
        'songcategory' => $category->songcategoryid,
        'songlyric' => "Ayat 1\nBeribu lidah patutlah",
    ]);

    Song::factory()->create([
        'songtitle' => 'Ya Khalik Semesta',
        'songsinger' => '5',
        'songcategory' => $category->songcategoryid,
        'songlyric' => null,
    ]);

    $response = $this->actingAs($admin)->get('/admin/lyrics');

    $response->assertStatus(200);
    $response->assertSee('Lirik Lagu');
    $response->assertSee('Tambah Ayat');
    $response->assertSee('Daftar Ayat');
    $response->assertSee('Beribu Lidah Patutlah');
    $response->assertSee('Ya Khalik Semesta');
    $response->assertSee('Ada');
    $response->assertSee('Belum ada');
    $response->assertSee('Piano + Lirik');
});

test('admin can filter songs by lyric status', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Song::factory()->create([
        'songtitle' => 'Lagu Ada Lirik',
        'songcategory' => $category->songcategoryid,
        'songlyric' => 'Ayat 1',
    ]);
    Song::factory()->create([
        'songtitle' => 'Lagu Tanpa Lirik',
        'songcategory' => $category->songcategoryid,
        'songlyric' => null,
    ]);

    $filled = $this->actingAs($admin)->get('/admin/lyrics?status=filled');
    $filled->assertStatus(200);
    $filled->assertSee('Lagu Ada Lirik');
    $filled->assertDontSee('Lagu Tanpa Lirik');

    $empty = $this->actingAs($admin)->get('/admin/lyrics?status=empty');
    $empty->assertStatus(200);
    $empty->assertSee('Lagu Tanpa Lirik');
    $empty->assertDontSee('Lagu Ada Lirik');
});

test('admin can update a song lyric', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $song = Song::factory()->create([
        'songtitle' => 'Marilah Orang Berdosa',
        'songcategory' => $category->songcategoryid,
        'songlyric' => null,
    ]);

    $lyric = "Ayat 1\nMarilah orang berdosa datang kepada Tuhan\nDatang seperti adanya\n\nReff\nYesus memberi ampunan";

    $response = $this->actingAs($admin)->put("/admin/lyrics/{$song->songid}", [
        'songlyric' => $lyric,
    ]);

    $response->assertRedirect('/admin/lyrics');
    $response->assertSessionHas('success');

    expect($song->fresh()->songlyric)->toBe($lyric);
});

test('admin can clear a song lyric', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $song = Song::factory()->create([
        'songcategory' => $category->songcategoryid,
        'songlyric' => 'Ayat 1',
    ]);

    $response = $this->actingAs($admin)->put("/admin/lyrics/{$song->songid}", [
        'songlyric' => '',
    ]);

    $response->assertRedirect('/admin/lyrics');
    expect($song->fresh()->songlyric)->toBeNull();
});

test('lyric list is ordered by song number instead of song id', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Song::factory()->create([
        'songtitle' => 'Lagu Sepuluh',
        'songsinger' => '10',
        'songcategory' => $category->songcategoryid,
    ]);
    Song::factory()->create([
        'songtitle' => 'Lagu Dua',
        'songsinger' => '2',
        'songcategory' => $category->songcategoryid,
    ]);
    Song::factory()->create([
        'songtitle' => 'Lagu Satu',
        'songsinger' => '1',
        'songcategory' => $category->songcategoryid,
    ]);

    $response = $this->actingAs($admin)->get('/admin/lyrics');

    $response->assertStatus(200);
    $response->assertSeeInOrder(['Lagu Satu', 'Lagu Dua', 'Lagu Sepuluh']);
});
