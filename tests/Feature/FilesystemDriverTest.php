<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->diskRoot = storage_path('framework/testing/disks/local-'.Str::uuid());

    config(['filesystems.disks.local.root' => $this->diskRoot]);
    Storage::forgetDisk('local');
});

afterEach(function () {
    File::deleteDirectory($this->diskRoot);
});

test('local disks use the finfo-free driver', function () {
    expect(config('filesystems.disks.local.driver'))->toBe('local_no_finfo')
        ->and(config('filesystems.disks.public.driver'))->toBe('local_no_finfo');
});

test('local disk can write read and delete files without the fileinfo extension', function () {
    $disk = Storage::disk('local');
    $path = 'music/pending/lagu.mp3';

    expect($disk->put($path, 'ID3data'))->toBeTrue()
        ->and($disk->exists($path))->toBeTrue()
        ->and($disk->get($path))->toBe('ID3data')
        ->and($disk->mimeType($path))->toBe('audio/mpeg');

    expect($disk->delete($path))->toBeTrue()
        ->and($disk->exists($path))->toBeFalse();
});
