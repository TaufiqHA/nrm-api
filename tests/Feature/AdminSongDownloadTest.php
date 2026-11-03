<?php

use App\Enums\UserRole;
use App\Jobs\DownloadSongMedia;
use App\Models\Category;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

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
});

test('updating a song only queues a download when the url changes', function () {
    Queue::fake();

    $admin = songDownloadAdmin();
    $category = Category::factory()->create();
    $song = Song::factory()->create([
        'songcategory' => $category->songcategoryid,
        'songurl' => 'https://www.youtube.com/watch?v=aaaaaaaaaaa',
    ]);

    $payload = [
        'songtitle' => $song->songtitle,
        'songsinger' => $song->songsinger,
        'songcategory' => $category->songcategoryid,
        'songnada' => 'pria',
        'songduration' => $song->songduration,
        'songurl' => $song->songurl,
    ];

    $this->actingAs($admin)->put("/admin/songs/{$song->songid}", $payload)
        ->assertRedirect('/admin/songs');

    Queue::assertNotPushed(DownloadSongMedia::class);

    $payload['songurl'] = 'https://youtu.be/bbbbbbbbbbb';

    $this->actingAs($admin)->put("/admin/songs/{$song->songid}", $payload)
        ->assertRedirect('/admin/songs');

    Queue::assertPushed(DownloadSongMedia::class, function (DownloadSongMedia $job) use ($song) {
        return $job->songId === $song->songid
            && $job->url === 'https://youtu.be/bbbbbbbbbbb';
    });
});

test('admin can trigger a fresh download for a youtube song', function () {
    Queue::fake();

    $admin = songDownloadAdmin();
    $song = Song::factory()->create([
        'songurl' => 'https://youtu.be/ccccccccccc',
        'download_status' => 'failed',
        'download_error' => 'Gagal sebelumnya',
    ]);

    $this->actingAs($admin)->post("/admin/songs/{$song->songid}/download")
        ->assertRedirect('/admin/songs')
        ->assertSessionHas('success');

    Queue::assertPushed(DownloadSongMedia::class);
    expect($song->fresh()->download_status)->toBe('pending')
        ->and($song->fresh()->download_error)->toBeNull();
});

test('admin cannot trigger a download for a non-youtube song', function () {
    Queue::fake();

    $admin = songDownloadAdmin();
    $song = Song::factory()->create([
        'songurl' => 'https://example.com/audio/lagu.mp3',
    ]);

    $this->actingAs($admin)->post("/admin/songs/{$song->songid}/download")
        ->assertRedirect('/admin/songs')
        ->assertSessionHas('error');

    Queue::assertNotPushed(DownloadSongMedia::class);
});

test('download status endpoint returns only the requested songs', function () {
    $admin = songDownloadAdmin();
    $category = Category::factory()->create();

    $first = Song::factory()->create([
        'songcategory' => $category->songcategoryid,
        'download_status' => 'processing',
        'download_progress' => 45,
    ]);
    $second = Song::factory()->create([
        'songcategory' => $category->songcategoryid,
        'download_status' => 'completed',
        'download_progress' => 100,
    ]);

    $response = $this->actingAs($admin)->getJson("/admin/songs/download-status?ids[]={$first->songid}");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.songid', $first->songid)
        ->assertJsonPath('data.0.download_status', 'processing')
        ->assertJsonPath('data.0.download_progress', 45)
        ->assertJsonMissing(['songid' => $second->songid]);
});

test('regular user cannot access download endpoints', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $song = Song::factory()->create();

    $this->actingAs($user)->post("/admin/songs/{$song->songid}/download")->assertForbidden();
    $this->actingAs($user)->getJson('/admin/songs/download-status')->assertForbidden();
});
