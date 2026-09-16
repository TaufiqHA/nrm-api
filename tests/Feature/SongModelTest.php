<?php

use App\Models\Category;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('tb_songs table has expected columns', function () {
    expect(Schema::hasTable('tb_songs'))->toBeTrue();

    $expectedColumns = [
        'songid',
        'songtitle',
        'songsinger',
        'songurl',
        'songcategory',
        'songnada',
        'songduration',
        'created_at',
        'updated_at',
    ];

    foreach ($expectedColumns as $column) {
        expect(Schema::hasColumn('tb_songs', $column))->toBeTrue();
    }
});

test('song can be created using factory and belongs to category', function () {
    $category = Category::factory()->create([
        'songcategoryname' => 'Dangdut',
    ]);

    $song = Song::factory()->create([
        'songtitle' => 'Kopi Dangdut',
        'songsinger' => 'Fahmi Shahab',
        'songurl' => 'https://example.com/songs/kopi-dangdut.mp4',
        'songcategory' => $category->songcategoryid,
        'songnada' => 'Am',
        'songduration' => '4:20',
    ]);

    expect($song->songid)->toBeGreaterThan(0)
        ->and($song->songtitle)->toBe('Kopi Dangdut')
        ->and($song->songsinger)->toBe('Fahmi Shahab')
        ->and($song->songurl)->toBe('https://example.com/songs/kopi-dangdut.mp4')
        ->and($song->songcategory)->toBe($category->songcategoryid)
        ->and($song->songnada)->toBe('Am')
        ->and($song->songduration)->toBe('4:20')
        ->and($song->category->songcategoryid)->toBe($category->songcategoryid);

    expect($category->songs)->toHaveCount(1)
        ->and($category->songs->first()->songid)->toBe($song->songid);
});

test('deleting a category cascades delete to its songs', function () {
    $category = Category::factory()->create();
    $song = Song::factory()->create([
        'songcategory' => $category->songcategoryid,
    ]);

    $this->assertDatabaseHas('tb_songs', [
        'songid' => $song->songid,
    ]);

    $category->delete();

    $this->assertDatabaseMissing('tb_songs', [
        'songid' => $song->songid,
    ]);
});
