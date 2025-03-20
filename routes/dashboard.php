<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
