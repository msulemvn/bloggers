<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Post;
use App\Models\Tag;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagResource;
use Symfony\Component\HttpFoundation\Response as symfonyResponse;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Contracts\PictureServiceInterface;

class PostController extends Controller
{

    public function __construct(protected PictureServiceInterface $pictureService)
    {
        $this->pictureService = $pictureService;
    }

    public function index(): Response
    {
        $posts = Post::with('tags')->paginate();
        $tags = TagResource::collection(Tag::all());
        $posts = PostResource::collection($posts);
        return Inertia::render("Posts", compact("posts", "tags"));
    }

    public function store(StorePostRequest $request)
    {
        $newFileName = '';
        if ($request->hasFile('feature_image')) {
            $newFileName = $this->pictureService->upload(
                $request->file('feature_image'),
                $request->file('feature_image')->getClientOriginalName()
            );
        }

        $data = $request->validated();
        $data['feature_image'] = $newFileName ?? "";
        $post = Post::create($data);
        if ($request->has('tags')) {
            $data['tags'] = json_decode($data['tags']);
            $post->tags()->sync($data['tags']);
        }

        logActivity(request: $request, description: "User created a new post", showable: true);
        return apiResponse(message: "Post added successfully", data: PostResource::make($post), statusCode: symfonyResponse::HTTP_CREATED);
    }

    public function show(Post $post): Response
    {
        $post->load('tags');
        return Inertia::render("posts/show", [
            "post" => $post
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->fill($request->validated());
        $newFileName = '';

        if ($request->hasFile('feature_image')) {
            $newFileName = $this->pictureService->upload(
                $request->file('feature_image'),
                $request->file('feature_image')->getClientOriginalName()
            );
            $post->feature_image = $newFileName;
        }

        if ($request->has('tags')) {
            $request['tags'] = json_decode($request['tags']);
            $post->tags()->sync($request['tags']);
        }

        $post->save();
        $post->tags()->sync($request->tags);

        logActivity(request: $request, description: "User updated a post", showable: true);
        return apiResponse(message: "Post updated successfully", data: PostResource::make($post));
    }

    public function destroy(Request $request, Post $post)
    {
        if (!$post) {
            return apiResponse(errors: ["id" => ["Post not found"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
        }
        $post->tags()->sync([]);
        $post->delete();

        logActivity(request: $request, description: "User deleted a post", showable: true);
        return apiResponse(message: "Post deleted successfully", statusCode: symfonyResponse::HTTP_NO_CONTENT);
    }
}
