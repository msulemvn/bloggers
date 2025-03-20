<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

require __DIR__ . '/home.php';
require __DIR__ . '/dashboard.php';
require __DIR__ . '/permissions.php';
require __DIR__ . '/tags.php';
require __DIR__ . '/posts.php';
require __DIR__ . '/comments.php';
require __DIR__ . '/users.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
