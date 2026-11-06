<?php

use App\Enums\UserRole;
use App\Jobs\UploadMusicToDrive;
use App\Models\Category;
use App\Models\Music;
use App\Models\Song;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

test('guest is redirected to login when accessing admin music', function () {
    $response = $this->get('/admin/music');

    $response->assertRedirect('/login');
});

test('regular user is forbidden from accessing admin music', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)->get('/admin/music')->assertStatus(403);
});

test('admin can view songs from the catalog in the music list', function () {
    $admin = User::factory()->admin()->create();

    $song = Song::factory()->create([
        'songtitle' => 'Nyanyian Pagi',
        'songsinger' => '12',
    ]);

    Music::create([
        'songid' => $song->songid,
        'musictitle' => $song->songtitle,
        'musicartist' => $song->songsinger,
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin)->get('/admin/music');

    $response->assertStatus(200);
    $response->assertSee('Kelola Katalog Lagu');
    $response->assertSee('Nyanyian Pagi');
    $response->assertSee('onclick="openSongModal(this)"', false);
    $response->assertSee('Hapus File');
});

test('songs without a music file are still listed', function () {
    $admin = User::factory()->admin()->create();

    Song::factory()->create(['songtitle' => 'Belum Ada Musik']);

    $response = $this->actingAs($admin)->get('/admin/music');

    $response->assertStatus(200);
    $response->assertSee('Belum Ada Musik');
    $response->assertSee('Belum ada file');
    $response->assertSee('Upload');
});

test('admin music page marks existing drive files as ready', function () {
    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create([
        'songtitle' => 'Sudah di Drive',
        'songsinger' => '3',
    ]);

    $music = Music::create([
        'songid' => $song->songid,
        'musictitle' => $song->songtitle,
        'musicartist' => $song->songsinger,
        'musicfileid' => 'drive-1',
        'musicfilename' => 'lagu.mp3',
        'is_active' => true,
        'sort_order' => 1,
        'upload_status' => 'failed',
        'upload_error' => 'Worker berhenti sebelum menyimpan status.',
    ]);

    $response = $this->actingAs($admin)->get('/admin/music');

    $response->assertOk()->assertSee('Siap');

    expect($music->refresh()->upload_status)->toBe('ready')
        ->and($music->upload_error)->toBeNull();
});

test('admin music page keeps an in-progress upload marked as uploading', function () {
    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create(['songtitle' => 'Sedang Unggah']);

    $music = Music::create([
        'songid' => $song->songid,
        'musictitle' => $song->songtitle,
        'musicfileid' => 'drive-old',
        'is_active' => true,
        'sort_order' => 1,
        'upload_status' => 'pending',
        'pending_file_path' => 'music/pending/baru.mp3',
    ]);

    $response = $this->actingAs($admin)->get('/admin/music');

    $response->assertOk()->assertSee('Mengunggah');

    expect($music->refresh()->upload_status)->toBe('pending');
});

test('legacy music without a song relation is not shown in the music list', function () {
    $admin = User::factory()->admin()->create();

    Music::create([
        'musictitle' => 'Musik Lama',
        'musicfileid' => 'drive-old',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin)->get('/admin/music');

    $response->assertStatus(200);
    $response->assertDontSee('Musik Tanpa Katalog Lagu');
    $response->assertDontSee('Musik Lama');
    $response->assertDontSee('Tambah Musik');
});

test('admin can add standalone music without a catalog song', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/music', [
        'musictitle' => 'Musik Mandiri',
        'is_active' => '1',
        'file' => UploadedFile::fake()->create('mandiri.mp3', 1024, 'audio/mpeg'),
    ]);

    $response->assertRedirect(route('admin.music.index'));

    $music = Music::first();

    expect($music)->not->toBeNull()
        ->and($music->songid)->toBeNull()
        ->and($music->musictitle)->toBe('Musik Mandiri')
        ->and($music->upload_status)->toBe('pending')
        ->and($music->pending_file_path)->not->toBeNull();

    Queue::assertPushed(UploadMusicToDrive::class);
});

test('standalone music upload rejects non mp3 files', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/music', [
        'musictitle' => 'Bukan MP3',
        'file' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('file');
    expect(Music::count())->toBe(0);
});

test('admin can upload a music file for an existing song in the background', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create([
        'songtitle' => 'Lagu Baru',
        'songsinger' => '21',
    ]);

    $response = $this->actingAs($admin)->put('/admin/music/songs/'.$song->songid, [
        'is_active' => '1',
        'file' => UploadedFile::fake()->create('lagu.mp3', 1024, 'audio/mpeg'),
    ]);

    $response->assertRedirect(route('admin.music.index'));

    $music = Music::first();

    expect($music)->not->toBeNull()
        ->and($music->songid)->toBe($song->songid)
        ->and($music->musictitle)->toBe('Lagu Baru')
        ->and($music->musicartist)->toBe('21')
        ->and($music->upload_status)->toBe('pending')
        ->and($music->musicfileid)->toBeNull()
        ->and($music->pending_file_path)->not->toBeNull();

    Storage::disk('local')->assertExists($music->pending_file_path);
    Queue::assertPushed(UploadMusicToDrive::class, function (UploadMusicToDrive $job) use ($music) {
        return $job->musicId === $music->musicid;
    });
});

test('json upload for a song returns queued status without redirecting', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create([
        'songtitle' => 'Lagu Latar Belakang',
        'songsinger' => '22',
    ]);

    $response = $this->actingAs($admin)
        ->withHeaders(['Accept' => 'application/json'])
        ->put('/admin/music/songs/'.$song->songid, [
            'is_active' => '1',
            'file' => UploadedFile::fake()->create('lagu.mp3', 1024, 'audio/mpeg'),
        ]);

    $music = Music::first();

    $response->assertOk()
        ->assertJsonPath('upload_status', 'pending')
        ->assertJsonPath('music_id', $music->musicid)
        ->assertJsonPath('message', 'File musik lagu "Lagu Latar Belakang" sedang diunggah ke Google Drive di latar belakang.');

    expect($music->refresh()->upload_status)->toBe('pending');

    Storage::disk('local')->assertExists($music->pending_file_path);
    Queue::assertPushed(UploadMusicToDrive::class);
});

test('json upload without a required file returns validation errors', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create();

    $response = $this->actingAs($admin)
        ->withHeaders(['Accept' => 'application/json'])
        ->put('/admin/music/songs/'.$song->songid, [
            'is_active' => '1',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect(Music::count())->toBe(0);
    Queue::assertNothingPushed();
});

test('replacing a song music file queues a background upload without duplicating the record', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create(['songtitle' => 'Judul Lama']);
    $music = Music::create([
        'songid' => $song->songid,
        'musictitle' => 'Judul Lama',
        'musicfileid' => 'drive-old',
        'is_active' => true,
        'sort_order' => 1,
        'upload_status' => 'ready',
    ]);

    $response = $this->actingAs($admin)->put('/admin/music/songs/'.$song->songid, [
        'is_active' => '1',
        'file' => UploadedFile::fake()->create('lagu-baru.mp3', 1024, 'audio/mpeg'),
    ]);

    $response->assertRedirect(route('admin.music.index'));

    $music->refresh();

    expect(Music::count())->toBe(1)
        ->and($music->upload_status)->toBe('pending')
        ->and($music->musicfileid)->toBe('drive-old')
        ->and($music->pending_file_path)->not->toBeNull();

    Queue::assertPushed(UploadMusicToDrive::class);
});

test('upload without active checkbox stores song music as inactive', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create();

    $this->actingAs($admin)->put('/admin/music/songs/'.$song->songid, [
        'file' => UploadedFile::fake()->create('lagu.mp3', 1024, 'audio/mpeg'),
    ]);

    expect(Music::first()->is_active)->toBeFalse();
});

test('upload rejects non mp3 files', function () {
    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create();

    $response = $this->actingAs($admin)->put('/admin/music/songs/'.$song->songid, [
        'file' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('file');
    expect(Music::count())->toBe(0);
});

test('upload requires a file when the song has no music yet', function () {
    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create();

    $response = $this->actingAs($admin)->put('/admin/music/songs/'.$song->songid, [
        'is_active' => '1',
    ]);

    $response->assertSessionHasErrors('file');
    expect(Music::count())->toBe(0);
});

test('admin can update legacy music metadata', function () {
    $admin = User::factory()->admin()->create();
    $music = Music::create([
        'musictitle' => 'Judul Lama',
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin)->put('/admin/music/'.$music->musicid, [
        'musictitle' => 'Judul Baru',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.music.index'));

    $music->refresh();

    expect($music->musictitle)->toBe('Judul Baru')
        ->and($music->musicfileid)->toBe('drive-1')
        ->and($music->songid)->toBeNull();
});

test('admin can link legacy music to a catalog song', function () {
    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create([
        'songtitle' => 'Lagu Tujuan',
        'songsinger' => '33',
    ]);
    $music = Music::create([
        'musictitle' => 'Judul Lama',
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin)->put('/admin/music/'.$music->musicid, [
        'musictitle' => 'Judul Lama',
        'songid' => $song->songid,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.music.index'));

    $music->refresh();

    expect($music->songid)->toBe($song->songid)
        ->and($music->musictitle)->toBe('Lagu Tujuan')
        ->and($music->musicartist)->toBe('33');
});

test('linking music to a song that already has music is rejected', function () {
    $admin = User::factory()->admin()->create();
    $song = Song::factory()->create();

    Music::create([
        'songid' => $song->songid,
        'musictitle' => $song->songtitle,
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $legacy = Music::create([
        'musictitle' => 'Musik Lama',
        'musicfileid' => 'drive-2',
        'is_active' => true,
        'sort_order' => 2,
    ]);

    $response = $this->actingAs($admin)->put('/admin/music/'.$legacy->musicid, [
        'musictitle' => 'Musik Lama',
        'songid' => $song->songid,
        'is_active' => '1',
    ]);

    $response->assertSessionHasErrors('songid');
    expect($legacy->refresh()->songid)->toBeNull();
});

test('updating a song title syncs the linked music metadata', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $song = Song::factory()->create([
        'songtitle' => 'Judul Awal',
        'songsinger' => '10',
        'songcategory' => $category->songcategoryid,
    ]);
    $music = Music::create([
        'songid' => $song->songid,
        'musictitle' => 'Judul Awal',
        'musicartist' => '10',
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin)->put('/admin/songs/'.$song->songid, [
        'songtitle' => 'Judul Baru',
        'songsinger' => '11',
        'songcategory' => $category->songcategoryid,
        'songurl' => 'https://example.com/lagu.mp3',
    ]);

    $response->assertRedirect(route('admin.songs.index'));

    expect($music->refresh()->musictitle)->toBe('Judul Baru')
        ->and($music->musicartist)->toBe('11');
});

test('admin can delete music and its drive file', function () {
    $this->mock(GoogleDriveService::class, function (MockInterface $mock) {
        $mock->shouldReceive('delete')->once()->with('drive-1')->andReturnNull();
    });

    $admin = User::factory()->admin()->create();
    $music = Music::create([
        'musictitle' => 'Akan Dihapus',
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin)->delete('/admin/music/'.$music->musicid);

    $response->assertRedirect(route('admin.music.index'));
    expect(Music::count())->toBe(0);
});

test('admin can toggle music status', function () {
    $admin = User::factory()->admin()->create();
    $music = Music::create([
        'musictitle' => 'Status',
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->post('/admin/music/'.$music->musicid.'/status', ['is_active' => '0'])
        ->assertRedirect();

    expect($music->refresh()->is_active)->toBeFalse();
});
