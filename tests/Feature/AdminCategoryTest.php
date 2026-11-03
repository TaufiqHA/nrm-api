<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guest is redirected to login when accessing admin categories', function () {
    $response = $this->get('/admin/categories');

    $response->assertRedirect('/login');
});

test('regular user is forbidden from accessing admin categories', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user)->get('/admin/categories');

    $response->assertStatus(403);
});

test('admin can view categories list', function () {
    $admin = User::factory()->admin()->create();

    Category::factory()->create([
        'songcategoryname' => 'Pop Music',
    ]);
    Category::factory()->create([
        'songcategoryname' => 'Rock & Roll',
    ]);

    $response = $this->actingAs($admin)->get('/admin/categories');

    $response->assertStatus(200);
    $response->assertSee('Kategori Lagu');
    $response->assertSee('Pop Music');
    $response->assertSee('Rock & Roll');
    $response->assertSee('Tambah Kategori');
});

test('admin can search categories by name', function () {
    $admin = User::factory()->admin()->create();

    Category::factory()->create(['songcategoryname' => 'Dangdut Koplo']);
    Category::factory()->create(['songcategoryname' => 'Jazz Classic']);

    $response = $this->actingAs($admin)->get('/admin/categories?search=Dangdut');

    $response->assertStatus(200);
    $response->assertSee('Dangdut Koplo');
    $response->assertDontSee('Jazz Classic');
});

test('admin can store a new category', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/categories', [
        'songcategoryname' => 'Electronic Dance Music',
    ]);

    $response->assertRedirect('/admin/categories');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('categories', [
        'songcategoryname' => 'Electronic Dance Music',
    ]);
});

test('category creation fails with duplicate name', function () {
    $admin = User::factory()->admin()->create();

    Category::factory()->create([
        'songcategoryname' => 'Existing Category',
    ]);

    $response = $this->actingAs($admin)->from('/admin/categories')->post('/admin/categories', [
        'songcategoryname' => 'Existing Category',
    ]);

    $response->assertRedirect('/admin/categories');
    $response->assertSessionHasErrors(['songcategoryname']);
});

test('admin can update an existing category', function () {
    $admin = User::factory()->admin()->create();

    $category = Category::factory()->create([
        'songcategoryname' => 'Old Category Name',
    ]);

    $response = $this->actingAs($admin)->put("/admin/categories/{$category->songcategoryid}", [
        'songcategoryname' => 'New Category Name',
    ]);

    $response->assertRedirect('/admin/categories');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('categories', [
        'songcategoryid' => $category->songcategoryid,
        'songcategoryname' => 'New Category Name',
    ]);
});

test('admin can delete a category without songs', function () {
    $admin = User::factory()->admin()->create();

    $category = Category::factory()->create([
        'songcategoryname' => 'Category To Delete',
    ]);

    $response = $this->actingAs($admin)->delete("/admin/categories/{$category->songcategoryid}");

    $response->assertRedirect('/admin/categories');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('categories', [
        'songcategoryid' => $category->songcategoryid,
    ]);
});

test('admin cannot delete a category that has associated songs', function () {
    $admin = User::factory()->admin()->create();

    $category = Category::factory()->create();

    Song::factory()->create([
        'songcategory' => $category->songcategoryid,
    ]);

    $response = $this->actingAs($admin)->from('/admin/categories')->delete("/admin/categories/{$category->songcategoryid}");

    $response->assertRedirect('/admin/categories');
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('categories', [
        'songcategoryid' => $category->songcategoryid,
    ]);
});

test('new category is placed at the bottom of the order', function () {
    $admin = User::factory()->admin()->create();

    Category::factory()->create(['songcategoryname' => 'First']);
    Category::factory()->create(['songcategoryname' => 'Second']);

    $this->actingAs($admin)->post('/admin/categories', [
        'songcategoryname' => 'Third',
    ]);

    $third = Category::where('songcategoryname', 'Third')->first();

    expect($third)->not->toBeNull()
        ->and($third->sort_order)->toBe(3);
});

test('admin can move a category up and down', function () {
    $admin = User::factory()->admin()->create();

    $first = Category::factory()->create(['songcategoryname' => 'Alpha']);
    $second = Category::factory()->create(['songcategoryname' => 'Beta']);
    $third = Category::factory()->create(['songcategoryname' => 'Gamma']);

    $this->actingAs($admin)
        ->post("/admin/categories/{$third->songcategoryid}/move", ['direction' => 'up'])
        ->assertRedirect();

    expect($first->fresh()->sort_order)->toBe(1)
        ->and($third->fresh()->sort_order)->toBe(2)
        ->and($second->fresh()->sort_order)->toBe(3);

    $this->actingAs($admin)
        ->post("/admin/categories/{$third->songcategoryid}/move", ['direction' => 'up'])
        ->assertRedirect();

    expect($third->fresh()->sort_order)->toBe(1)
        ->and($first->fresh()->sort_order)->toBe(2);
});

test('moving a category beyond the boundary keeps the order', function () {
    $admin = User::factory()->admin()->create();

    $first = Category::factory()->create(['songcategoryname' => 'Alpha']);
    $second = Category::factory()->create(['songcategoryname' => 'Beta']);

    $this->actingAs($admin)
        ->post("/admin/categories/{$first->songcategoryid}/move", ['direction' => 'up'])
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->actingAs($admin)
        ->post("/admin/categories/{$second->songcategoryid}/move", ['direction' => 'down'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($first->fresh()->sort_order)->toBe(1)
        ->and($second->fresh()->sort_order)->toBe(2);
});

test('admin can store a category with a builtin icon', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/categories', [
        'songcategoryname' => 'Piano Rohani',
        'icon_source' => 'builtin',
        'icon' => 'piano',
    ]);

    $response->assertRedirect('/admin/categories');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('categories', [
        'songcategoryname' => 'Piano Rohani',
        'icon' => 'piano',
        'icon_image' => null,
    ]);
});

test('category creation rejects an unknown icon key', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->from('/admin/categories')->post('/admin/categories', [
        'songcategoryname' => 'Kategori Aneh',
        'icon_source' => 'builtin',
        'icon' => 'icon-tidak-ada',
    ]);

    $response->assertRedirect('/admin/categories');
    $response->assertSessionHasErrors(['icon']);
});

test('admin can upload a custom icon for a category', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/categories', [
        'songcategoryname' => 'Kategori Custom',
        'icon_source' => 'image',
        'icon_file' => UploadedFile::fake()->image('ikon.png', 64, 64),
    ]);

    $response->assertRedirect('/admin/categories');
    $response->assertSessionHas('success');

    $category = Category::where('songcategoryname', 'Kategori Custom')->first();

    expect($category)->not->toBeNull()
        ->and($category->icon)->toBeNull()
        ->and($category->icon_image)->toStartWith('category-icons/');

    Storage::disk('public')->assertExists($category->icon_image);
    expect($category->icon_url)->not->toBeNull();
});

test('replacing or clearing a custom icon removes the old file', function () {
    Storage::fake('public');

    Storage::disk('public')->put('category-icons/lama.png', 'old');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create([
        'songcategoryname' => 'Kategori Lama',
        'icon_image' => 'category-icons/lama.png',
    ]);

    $this->actingAs($admin)->put("/admin/categories/{$category->songcategoryid}", [
        'songcategoryname' => 'Kategori Lama',
        'icon_source' => 'image',
        'icon_file' => UploadedFile::fake()->image('baru.png', 64, 64),
    ])->assertRedirect('/admin/categories');

    Storage::disk('public')->assertMissing('category-icons/lama.png');

    $updated = $category->fresh();
    expect($updated->icon_image)->not->toBe('category-icons/lama.png');
    Storage::disk('public')->assertExists($updated->icon_image);

    $this->actingAs($admin)->put("/admin/categories/{$category->songcategoryid}", [
        'songcategoryname' => 'Kategori Lama',
        'icon_source' => 'auto',
    ])->assertRedirect('/admin/categories');

    $cleared = $category->fresh();
    expect($cleared->icon)->toBeNull()
        ->and($cleared->icon_image)->toBeNull();
    Storage::disk('public')->assertMissing($updated->icon_image);
});

test('deleting a category removes its custom icon file', function () {
    Storage::fake('public');

    Storage::disk('public')->put('category-icons/hapus.png', 'old');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create([
        'songcategoryname' => 'Kategori Hapus',
        'icon_image' => 'category-icons/hapus.png',
    ]);

    $this->actingAs($admin)
        ->delete("/admin/categories/{$category->songcategoryid}")
        ->assertRedirect('/admin/categories');

    Storage::disk('public')->assertMissing('category-icons/hapus.png');
});

test('admin can remove an uploaded icon without changing the source', function () {
    Storage::fake('public');

    Storage::disk('public')->put('category-icons/hapus-saja.png', 'old');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create([
        'songcategoryname' => 'Kategori Ikon',
        'icon_image' => 'category-icons/hapus-saja.png',
    ]);

    $this->actingAs($admin)->put("/admin/categories/{$category->songcategoryid}", [
        'songcategoryname' => 'Kategori Ikon',
        'icon_source' => 'image',
        'remove_icon' => '1',
    ])->assertRedirect('/admin/categories');

    $updated = $category->fresh();
    expect($updated->icon_image)->toBeNull()
        ->and($updated->icon)->toBeNull();

    Storage::disk('public')->assertMissing('category-icons/hapus-saja.png');
});

test('admin can change a category status with the toggle switch', function () {
    $admin = User::factory()->admin()->create();

    $category = Category::factory()->create([
        'songcategoryname' => 'Kategori Status',
    ]);

    $this->actingAs($admin)
        ->post("/admin/categories/{$category->songcategoryid}/status", ['is_active' => '0'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($category->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->post("/admin/categories/{$category->songcategoryid}/status", ['is_active' => '1'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($category->fresh()->is_active)->toBeTrue();
});

test('category status update requires a boolean value', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin)
        ->from('/admin/categories')
        ->post("/admin/categories/{$category->songcategoryid}/status", ['is_active' => 'invalid'])
        ->assertRedirect('/admin/categories')
        ->assertSessionHasErrors(['is_active']);

    expect($category->fresh()->is_active)->toBeTrue();
});

test('guest cannot change a category status', function () {
    $category = Category::factory()->create();

    $this->post("/admin/categories/{$category->songcategoryid}/status", ['is_active' => '0'])
        ->assertRedirect('/login');

    expect($category->fresh()->is_active)->toBeTrue();
});
