<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Nada;
use App\Models\Song;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the temporary admin dashboard page.
     */
    public function index(): View
    {
        $stats = [
            'total_songs' => Song::count(),
            'total_categories' => Category::count(),
            'total_nadas' => Nada::count(),
            'total_users' => User::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
