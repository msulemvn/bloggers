<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;

Route::get('/', HomeController::class)->name('home');
Route::get('dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::get('permissions', [PermissionController::class, 'index'])->middleware(['auth', 'verified'])->name('permissions');

require __DIR__ . '/tags.php';
require __DIR__ . '/posts.php';
require __DIR__ . '/comments.php';
require __DIR__ . '/users.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
