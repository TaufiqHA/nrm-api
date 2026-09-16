<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Nada;
use App\Models\Setting;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => UserRole::Admin,
                'password' => bcrypt('password'),
            ]
        );

        User::firstOrCreate(
            ['username' => 'testuser'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => UserRole::User,
                'password' => bcrypt('password'),
            ]
        );

        // Seed Nadas
        foreach (['Pria', 'Wanita', '-'] as $nadaName) {
            Nada::firstOrCreate(['nada' => $nadaName]);
        }

        // Seed Setting
        Setting::firstOrCreate(
            ['applicationcompany' => 'NRM'],
            [
                'applicationname' => 'Nyanyian Rohani Methodist',
                'applicationadsactive' => 'N',
                'applicationadsbottomactive' => 'N',
            ]
        );

        // Seed Categories
        $nrmCategory = Category::firstOrCreate(['songcategoryname' => 'NRM']);
        $popCategory = Category::firstOrCreate(['songcategoryname' => 'Pop']);
        $batakCategory = Category::firstOrCreate(['songcategoryname' => 'Lagu Batak']);

        // Seed Songs
        Song::firstOrCreate(
            ['songtitle' => 'NRM 001'],
            [
                'songsinger' => 'NRM',
                'songurl' => 'https://youtu.be/m_WRuWRUOGU?si=MIVrbLSNpH_Bi4XV',
                'songcategory' => $nrmCategory->songcategoryid,
                'songnada' => '-',
                'songduration' => '3:30',
            ]
        );

        Song::firstOrCreate(
            ['songtitle' => 'Dear God'],
            [
                'songsinger' => 'Avenged Sevenfold',
                'songurl' => 'https://youtu.be/mzX0rhF8buo?si=FbWAlO56_nSBUvOp',
                'songcategory' => $popCategory->songcategoryid,
                'songnada' => 'Pria',
                'songduration' => '4:00',
            ]
        );

        Song::firstOrCreate(
            ['songtitle' => 'Mardua Holong'],
            [
                'songsinger' => 'Cipt Saut Barasa',
                'songurl' => 'https://youtu.be/B5FxCH2ZK-E?si=j__lD1dqKqgOrBkO',
                'songcategory' => $batakCategory->songcategoryid,
                'songnada' => 'Pria',
                'songduration' => '4:15',
            ]
        );
    }
}
