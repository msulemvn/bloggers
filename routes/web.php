<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('posts', function () {
    return Inertia::render('Posts');
})->middleware(['auth', 'verified'])->name('posts');

Route::get('tags', function () {
    return Inertia::render('Tags');
})->middleware(['auth', 'verified'])->name('tags');

Route::get('permissions', function (Request $request) {
    $permissions = $request->user()->roles->flatMap->permissions->pluck('name')->unique();;
    return Inertia::render('Permissions', compact('permissions'));
})->middleware(['auth', 'verified'])->name('permissions');

require __DIR__ . '/tags.php';
require __DIR__ . '/posts.php';
require __DIR__ . '/users.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
