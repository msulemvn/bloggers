<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Tag;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagResource;
use Symfony\Component\HttpFoundation\Response as symfonyResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\DTO\Post\StorePostDTO;
use App\Services\PictureService;

class PostController extends Controller
{
    protected $pictureService;

    public function __construct(PictureService $pictureService)
    {
        $this->pictureService = $pictureService;
    }

    public function index(): Response
    {
        $posts = Post::with('tags')->where("user_id", "=", Auth::id())->orderBy('id', 'DESC')->paginate();
        $tags = Tag::all();
        $tags = TagResource::collection($tags);
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
        $data['feature_image'] = $newFileName;
        $PostDTO = new StorePostDTO(
            $data["title"],
            $data["content"],
            $data["feature_image"] ?? ""
        );
        $post = Post::create($PostDTO->toArray());
        if ($request->has('tags')) {
            $data['tags'] = json_decode($data['tags']);
            $post->tags()->sync($data['tags']);
        }

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
