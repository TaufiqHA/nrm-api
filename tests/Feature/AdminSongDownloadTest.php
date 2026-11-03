<?php

use App\Enums\UserRole;
use App\Jobs\DownloadSongMedia;
use App\Models\Category;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function songDownloadAdmin(): User
{
    return User::factory()->admin()->create();
}

test('storing a song with a youtube link queues the media download', function () {
    Queue::fake();

    $admin = songDownloadAdmin();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/songs', [
        'songtitle' => 'Lagu YouTube',
        'songsinger' => 'Penyanyi',
        'songcategory' => $category->songcategoryid,
        'songnada' => 'pria',
        'songurl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    $response->assertRedirect('/admin/songs');

    $song = Song::where('songtitle', 'Lagu YouTube')->firstOrFail();

    Queue::assertPushed(DownloadSongMedia::class, function (DownloadSongMedia $job) use ($song) {
        return $job->songId === $song->songid
            && $job->url === 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    });

    $this->assertDatabaseHas('tb_songs', [
        'songid' => $song->songid,
        'songyoutubeurl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'download_status' => 'pending',
        'download_progress' => 0,
    ]);
});

test('storing a song with a non-youtube link does not queue a download', function () {
    Queue::fake();

    $admin = songDownloadAdmin();
    $category = Category::factory()->create();

    $this->actingAs($admin)->post('/admin/songs', [
        'songtitle' => 'Lagu Lokal',
        'songsinger' => 'Penyanyi',
        'songcategory' => $category->songcategoryid,
        'songurl' => 'https://example.com/audio/lagu.mp3',
    ])->assertRedirect('/admin/songs');

    Queue::assertNotPushed(DownloadSongMedia::class);

    $song = Song::where('songtitle', 'Lagu Lokal')->firstOrFail();

    expect($song->songyoutubeurl)->toBeNull()
        ->and($song->songurl)->toBe('https://example.com/audio/lagu.mp3');
});

test('updating a song without changing the youtube link keeps the local url', function () {
    Queue::fake();

    $admin = songDownloadAdmin();
    $category = Category::factory()->create();
    $song = Song::factory()->create([
        'songcategory' => $category->songcategoryid,
        'songurl' => 'http://localhost:8000/storage/songs/1/audio.mp3',
        'songyoutubeurl' => 'https://www.youtube.com/watch?v=aaaaaaaaaaa',
        'download_status' => 'completed',
    ]);

    $this->actingAs($admin)->put("/admin/songs/{$song->songid}", [
        'songtitle' => 'Judul Baru',
        'songsinger' => $song->songsinger,
        'songcategory' => $category->songcategoryid,
        'songnada' => 'pria',
        'songduration' => $song->songduration,
        'songurl' => 'https://www.youtube.com/watch?v=aaaaaaaaaaa',
    ])->assertRedirect('/admin/songs');

    Queue::assertNotPushed(DownloadSongMedia::class);

    $song->refresh();

    expect($song->songurl)->toBe('http://localhost:8000/storage/songs/1/audio.mp3')
        ->and($song->songyoutubeurl)->toBe('https://www.youtube.com/watch?v=aaaaaaaaaaa')
        ->and($song->songtitle)->toBe('Judul Baru');
});

test('updating to a different youtube link queues a new download', function () {
    Queue::fake();

    $admin = songDownloadAdmin();
    $category = Category::factory()->create();
    $song = Song::factory()->create([
        'songcategory' => $category->songcategoryid,
        'songurl' => 'http://localhost:8000/storage/songs/1/audio.mp3',
        'songyoutubeurl' => 'https://www.youtube.com/watch?v=aaaaaaaaaaa',
        'download_status' => 'completed',
    ]);

    $this->actingAs($admin)->put("/admin/songs/{$song->songid}", [
        'songtitle' => $song->songtitle,
        'songsinger' => $song->songsinger,
        'songcategory' => $category->songcategoryid,
        'songnada' => 'pria',
        'songduration' => $song->songduration,
        'songurl' => 'https://youtu.be/bbbbbbbbbbb',
    ])->assertRedirect('/admin/songs');

    Queue::assertPushed(DownloadSongMedia::class, function (DownloadSongMedia $job) use ($song) {
        return $job->songId === $song->songid
            && $job->url === 'https://youtu.be/bbbbbbbbbbb';
    });

    $song->refresh();

    expect($song->songyoutubeurl)->toBe('https://youtu.be/bbbbbbbbbbb')
        ->and($song->download_status)->toBe('pending');
});

test('download all only queues youtube songs that are not completed yet', function () {
    Queue::fake();
    Storage::fake('public');

    $admin = songDownloadAdmin();

    $failed = Song::factory()->create([
        'songyoutubeurl' => 'https://youtu.be/failed00001',
        'download_status' => 'failed',
    ]);
    $neverDownloaded = Song::factory()->create([
        'songyoutubeurl' => 'https://youtu.be/never000001',
        'download_status' => null,
    ]);
    $completed = Song::factory()->create([
        'songyoutubeurl' => 'https://youtu.be/completed01',
        'download_status' => 'completed',
    ]);
    Storage::disk('public')->put("songs/{$completed->songid}/audio.mp3", 'audio');

    $processing = Song::factory()->create([
        'songyoutubeurl' => 'https://youtu.be/processing1',
        'download_status' => 'processing',
    ]);
    $local = Song::factory()->create([
        'songurl' => 'https://example.com/audio/lagu.mp3',
        'songyoutubeurl' => null,
    ]);

    $this->actingAs($admin)->post('/admin/songs/download-all')
        ->assertRedirect('/admin/songs')
        ->assertSessionHas('success');

    Queue::assertPushed(DownloadSongMedia::class, 2);
    Queue::assertPushed(DownloadSongMedia::class, fn (DownloadSongMedia $job) => $job->songId === $failed->songid);
    Queue::assertPushed(DownloadSongMedia::class, fn (DownloadSongMedia $job) => $job->songId === $neverDownloaded->songid);
    Queue::assertNotPushed(DownloadSongMedia::class, fn (DownloadSongMedia $job) => $job->songId === $completed->songid);
    Queue::assertNotPushed(DownloadSongMedia::class, fn (DownloadSongMedia $job) => $job->songId === $processing->songid);
    Queue::assertNotPushed(DownloadSongMedia::class, fn (DownloadSongMedia $job) => $job->songId === $local->songid);
});

test('download all reports when every song already has a local file', function () {
    Queue::fake();
    Storage::fake('public');

    $admin = songDownloadAdmin();
    $song = Song::factory()->create([
        'songyoutubeurl' => 'https://youtu.be/completed01',
        'download_status' => 'completed',
    ]);
    Storage::disk('public')->put("songs/{$song->songid}/audio.mp3", 'audio');

    $this->actingAs($admin)->post('/admin/songs/download-all')
        ->assertRedirect('/admin/songs')
        ->assertSessionHas('success', 'Semua lagu sudah memiliki file lokal. Gunakan ?force=1 untuk mengunduh ulang.');

    Queue::assertNotPushed(DownloadSongMedia::class);
});

test('download all re-downloads a completed song when its local file is missing', function () {
    Queue::fake();
    Storage::fake('public');

    $admin = songDownloadAdmin();
    $song = Song::factory()->create([
        'songyoutubeurl' => 'https://youtu.be/completed02',
        'download_status' => 'completed',
    ]);

    $this->actingAs($admin)->post('/admin/songs/download-all')
        ->assertRedirect('/admin/songs')
        ->assertSessionHas('success', '1 lagu berhasil dimasukkan ke antrean unduhan.');

    Queue::assertPushed(DownloadSongMedia::class, fn (DownloadSongMedia $job) => $job->songId === $song->songid);
});

test('download all with force re-queues songs that already have a local file', function () {
    Queue::fake();
    Storage::fake('public');

    $admin = songDownloadAdmin();
    $song = Song::factory()->create([
        'songyoutubeurl' => 'https://youtu.be/force000001',
        'download_status' => 'completed',
    ]);
    Storage::disk('public')->put("songs/{$song->songid}/audio.mp3", 'audio');

    $this->actingAs($admin)->post('/admin/songs/download-all?force=1')
        ->assertRedirect('/admin/songs')
        ->assertSessionHas('success', '1 lagu berhasil dimasukkan ke antrean unduhan.');

    Queue::assertPushed(DownloadSongMedia::class, fn (DownloadSongMedia $job) => $job->songId === $song->songid);
});

test('song list displays the youtube url instead of the local file url', function () {
    $admin = songDownloadAdmin();
    $song = Song::factory()->create([
        'songurl' => 'http://localhost:8000/storage/songs/1/audio.mp3',
        'songyoutubeurl' => 'https://www.youtube.com/watch?v=aaaaaaaaaaa',
    ]);

    $this->actingAs($admin)->get('/admin/songs')
        ->assertOk()
        ->assertSee('https://www.youtube.com/watch?v=aaaaaaaaaaa')
        ->assertDontSee('storage/songs/1/audio.mp3');
});

test('download all can be triggered from the browser with a get request', function () {
    Queue::fake();
    Storage::fake('public');

    $admin = songDownloadAdmin();
    Song::factory()->create([
        'songyoutubeurl' => 'https://youtu.be/never000001',
        'download_status' => null,
    ]);

    $this->actingAs($admin)->get('/admin/songs/download-all')
        ->assertRedirect('/admin/songs')
        ->assertSessionHas('success');

    Queue::assertPushed(DownloadSongMedia::class, 1);
});

test('regular user cannot trigger download all', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)->post('/admin/songs/download-all')->assertForbidden();
});
