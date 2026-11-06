<?php

use App\Enums\UserRole;
use App\Jobs\UploadMusicToDrive;
use App\Models\Music;
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

test('admin can view music list', function () {
    $admin = User::factory()->admin()->create();

    Music::create([
        'musictitle' => 'Nyanyian Pagi',
        'musicartist' => 'NRM',
        'musicfileid' => 'drive-1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin)->get('/admin/music');

    $response->assertStatus(200);
    $response->assertSee('Tambah Musik');
    $response->assertSee('Nyanyian Pagi');
    $response->assertSee('onclick="openEditModal(', false);
    $response->assertSee('onclick="openDeleteModal(', false);
});

test('admin can upload a music file in the background', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/music', [
        'musictitle' => 'Lagu Baru',
        'is_active' => '1',
        'file' => UploadedFile::fake()->create('lagu.mp3', 1024, 'audio/mpeg'),
    ]);

    $response->assertRedirect(route('admin.music.index'));

    $music = Music::first();

    expect($music)->not->toBeNull()
        ->and($music->upload_status)->toBe('pending')
        ->and($music->musicfileid)->toBeNull()
        ->and($music->pending_file_path)->not->toBeNull();

    Storage::disk('local')->assertExists($music->pending_file_path);
    Queue::assertPushed(UploadMusicToDrive::class, function (UploadMusicToDrive $job) use ($music) {
        return $job->musicId === $music->musicid;
    });
});

test('replacing a music file queues a background upload', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $music = Music::create([
        'musictitle' => 'Judul Lama',
        'musicfileid' => 'drive-old',
        'is_active' => true,
        'sort_order' => 1,
        'upload_status' => 'ready',
    ]);

    $response = $this->actingAs($admin)->put('/admin/music/'.$music->musicid, [
        'musictitle' => 'Judul Baru',
        'is_active' => '1',
        'file' => UploadedFile::fake()->create('lagu-baru.mp3', 1024, 'audio/mpeg'),
    ]);

    $response->assertRedirect(route('admin.music.index'));

    $music->refresh();

    expect($music->musictitle)->toBe('Judul Baru')
        ->and($music->upload_status)->toBe('pending')
        ->and($music->musicfileid)->toBe('drive-old')
        ->and($music->pending_file_path)->not->toBeNull();

    Queue::assertPushed(UploadMusicToDrive::class);
});

test('upload without active checkbox stores music as inactive', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/music', [
        'musictitle' => 'Belum Tampil',
        'file' => UploadedFile::fake()->create('lagu.mp3', 1024, 'audio/mpeg'),
    ]);

    expect(Music::first()->is_active)->toBeFalse();
});

test('upload rejects non mp3 files', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/music', [
        'musictitle' => 'Bukan MP3',
        'file' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('file');
    expect(Music::count())->toBe(0);
});

test('admin can update music metadata', function () {
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
        ->and($music->musicfileid)->toBe('drive-1');
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
