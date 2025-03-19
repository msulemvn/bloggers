<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;

Route::get('dashboard', function () {
    $count = [
        "users" => App\Models\User::count(),
        "posts" => Auth::user()->hasRole('admin')
            ? App\Models\Post::count()
            : App\Models\Post::currentUserPost()->count(),
        "tags" => App\Models\Tag::count(),
    ];

    return Inertia::render('Dashboard', compact('count'));
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('permissions', function (Request $request) {
    $access = $request->user()->roles->flatMap->permissions->pluck('name')->unique();;
    return Inertia::render('Permissions', compact('access'));
})->middleware(['auth', 'verified'])->name('permissions');

require __DIR__ . '/tags.php';
require __DIR__ . '/posts.php';
require __DIR__ . '/comments.php';
require __DIR__ . '/users.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
