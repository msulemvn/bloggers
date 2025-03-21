<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Post;
use App\Http\Resources\PostResource;

class HomeController extends Controller
{
    public function __invoke()
    {
        $posts = PostResource::collection(Post::where('is_published', true)->latest()->paginate());
        return Inertia::render('Home', compact('posts'));
    }
}
