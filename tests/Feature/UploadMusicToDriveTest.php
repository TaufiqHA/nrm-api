<?php

use App\Jobs\UploadMusicToDrive;
use App\Models\Music;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

test('job uploads pending file and marks music as ready', function () {
    Storage::fake('local');
    Storage::disk('local')->put('music/pending/lagu.mp3', 'ID3data');

    $music = Music::create([
        'musictitle' => 'Judul Baru',
        'musicfileid' => 'drive-old',
        'musicfilename' => 'lama.mp3',
        'musicmime' => 'audio/mpeg',
        'is_active' => true,
        'sort_order' => 1,
        'upload_status' => 'pending',
        'pending_file_path' => 'music/pending/lagu.mp3',
    ]);

    $this->mock(GoogleDriveService::class, function (MockInterface $mock) {
        $mock->shouldReceive('upload')
            ->once()
            ->andReturn([
                'id' => 'drive-new',
                'name' => 'Judul Baru.mp3',
                'mimeType' => 'audio/mpeg',
            ]);
        $mock->shouldReceive('delete')->once()->with('drive-old')->andReturnNull();
    });

    (new UploadMusicToDrive($music->musicid))->handle(app(GoogleDriveService::class));

    $music->refresh();

    expect($music->upload_status)->toBe('ready')
        ->and($music->musicfileid)->toBe('drive-new')
        ->and($music->musicfilename)->toBe('Judul Baru.mp3')
        ->and($music->pending_file_path)->toBeNull()
        ->and($music->uploaded_at)->not->toBeNull();

    Storage::disk('local')->assertMissing('music/pending/lagu.mp3');
});

test('job failure marks music as failed and removes temporary file', function () {
    Storage::fake('local');
    Storage::disk('local')->put('music/pending/lagu.mp3', 'ID3data');

    $music = Music::create([
        'musictitle' => 'Gagal',
        'is_active' => true,
        'sort_order' => 1,
        'upload_status' => 'processing',
        'pending_file_path' => 'music/pending/lagu.mp3',
    ]);

    (new UploadMusicToDrive($music->musicid))->failed(new RuntimeException('Drive down'));

    $music->refresh();

    expect($music->upload_status)->toBe('failed')
        ->and($music->upload_error)->toBe('Drive down')
        ->and($music->pending_file_path)->toBeNull();

    Storage::disk('local')->assertMissing('music/pending/lagu.mp3');
});

test('job skips music that is no longer pending', function () {
    $music = Music::create([
        'musictitle' => 'Sudah Siap',
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
        'upload_status' => 'ready',
    ]);

    $this->mock(GoogleDriveService::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('upload');
    });

    (new UploadMusicToDrive($music->musicid))->handle(app(GoogleDriveService::class));

    expect($music->refresh()->musicfileid)->toBe('drive-1');
});
