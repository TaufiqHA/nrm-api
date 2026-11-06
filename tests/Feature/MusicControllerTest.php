<?php

use App\Models\Music;
use App\Models\Song;
use App\Services\GoogleDriveService;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

test('music endpoint only returns active music', function () {
    Music::create([
        'musictitle' => 'Aktif',
        'musicartist' => 'Artis',
        'musicfileid' => 'drive-aktif',
        'is_active' => true,
        'sort_order' => 1,
    ]);
    Music::create([
        'musictitle' => 'Nonaktif',
        'musicfileid' => 'drive-nonaktif',
        'is_active' => false,
        'sort_order' => 2,
    ]);

    $response = $this->getJson('/api/music');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.musictitle', 'Aktif')
        ->assertJsonPath('data.0.stream_url', url('/api/music/1/stream'));
});

test('music endpoint excludes music that is still uploading', function () {
    Music::create([
        'musictitle' => 'Sedang Unggah',
        'musicfileid' => null,
        'is_active' => true,
        'sort_order' => 1,
        'upload_status' => 'pending',
    ]);

    $this->getJson('/api/music')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('music endpoint can search by title or artist', function () {
    Music::create([
        'musictitle' => 'Pujian Pagi',
        'musicartist' => 'Tim Musik',
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);
    Music::create([
        'musictitle' => 'Sore',
        'musicartist' => 'Lain',
        'musicfileid' => 'drive-2',
        'is_active' => true,
        'sort_order' => 2,
    ]);

    $this->getJson('/api/music?search=Tim Musik')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.musictitle', 'Pujian Pagi');
});

test('music endpoint returns uploaded music linked to a catalog song', function () {
    $song = Song::factory()->create([
        'songtitle' => 'Pujian Sore',
        'songsinger' => '7',
    ]);

    Music::create([
        'songid' => $song->songid,
        'musictitle' => $song->songtitle,
        'musicartist' => $song->songsinger,
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->getJson('/api/music')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.songid', $song->songid)
        ->assertJsonPath('data.0.musictitle', 'Pujian Sore')
        ->assertJsonPath('data.0.musicartist', '7');
});

test('stream endpoint proxies drive content with range support', function () {
    $music = Music::create([
        'musictitle' => 'Stream',
        'musicfileid' => 'drive-1',
        'musicfilename' => 'stream.mp3',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->mock(GoogleDriveService::class, function (MockInterface $mock) {
        $mock->shouldReceive('openStream')
            ->once()
            ->with('drive-1', 'bytes=0-3')
            ->andReturn([
                'status' => 206,
                'headers' => [
                    'Content-Type' => 'audio/mpeg',
                    'Content-Length' => '4',
                    'Content-Range' => 'bytes 0-3/100',
                    'Accept-Ranges' => 'bytes',
                ],
                'body' => Utils::streamFor('ID3x'),
            ]);
    });

    $response = $this->withHeaders(['Range' => 'bytes=0-3'])
        ->get('/api/music/'.$music->musicid.'/stream');

    $response->assertStatus(206)
        ->assertHeader('Content-Type', 'audio/mpeg')
        ->assertHeader('Content-Range', 'bytes 0-3/100');

    expect($response->streamedContent())->toBe('ID3x');
});

test('stream endpoint returns 404 when drive file is missing', function () {
    $music = Music::create([
        'musictitle' => 'Hilang',
        'musicfileid' => 'drive-hilang',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->mock(GoogleDriveService::class, function (MockInterface $mock) {
        $mock->shouldReceive('openStream')
            ->once()
            ->andThrow(new RuntimeException('File musik tidak ditemukan di Google Drive.'));
    });

    $this->get('/api/music/'.$music->musicid.'/stream')->assertStatus(404);
});
