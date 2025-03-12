<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('users', function () {
    return Inertia::render('Users');
})->middleware(['auth', 'verified'])->name('users')->middleware('role:admin');

Route::get('posts', function () {
    return Inertia::render('Posts');
})->middleware(['auth', 'verified'])->name('posts');

Route::get('tags', function () {
    return Inertia::render('Tags');
})->middleware(['auth', 'verified'])->name('tags');

Route::get('permissions', function () {
    return Inertia::render('Permissions');
})->middleware(['auth', 'verified'])->name('permissions');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
