<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Http\Resources\PostResource;
use Symfony\Component\HttpFoundation\Response as symfonyResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\DTO\Post\StorePostDTO;

class PostController extends Controller
{
    public function index(): Response
    {
        $posts = Post::where("id", "=", Auth::id())->orderBy('id', 'DESC')->paginate();
        $posts = PostResource::collection($posts);
        return Inertia::render("Posts", compact("posts"));
    }

    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $PostDTO = new StorePostDTO(
            $data["title"],
            $data["user_id"],
            $data["is_published"],
            $data["status"],
            $data["feature_image"]
        );
        $post = Post::create($PostDTO->toArray());

        logActivity(request: $request, description: "User created a new post", showable: true);
        return apiResponse(message: "Post added successfully", data: PostResource::make($post), statusCode: symfonyResponse::HTTP_CREATED);
    }

    public function show(Post $post): Response
    {
        return Inertia::render("posts/show", [
            "post" => $post
        ]);
    }

    public function update(UpdatePostRequest $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->fill($request->validated());
            $post->save();

            logActivity(request: $request, description: "User updated a post", showable: true);
            return apiResponse(message: "Post updated successfully", data: PostResource::make($post));
        } catch (ModelNotFoundException $e) {
            return apiResponse(errors: ["id" => ["No query results for post"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return apiResponse(errors: ["id" => ["Post not found"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
            }
            $post->delete();

            logActivity(request: $request, description: "User deleted a post", showable: true);
            return apiResponse(message: "Post deleted successfully", statusCode: symfonyResponse::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return apiResponse(data: ["id" => ["No query results for post"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
        }
    }
}
