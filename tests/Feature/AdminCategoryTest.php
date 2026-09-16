<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
