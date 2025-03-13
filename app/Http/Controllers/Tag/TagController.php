<?php

namespace App\Http\Controllers\Tag;

use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tag;
use App\Http\Resources\TagResource;
use Symfony\Component\HttpFoundation\Response as symfonyResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use Illuminate\Http\Request;
use App\DTO\Tag\StoreTagDTO;

class TagController extends Controller
{
    public function index(): Response
    {
        $tags = Tag::OrderBy('id', 'DESC')->paginate();
        $tags = TagResource::collection($tags);
        return Inertia::render("Tags", compact("tags"));
    }

    public function store(StoreTagRequest $request)
    {
        $data = $request->validated();
        $TagDTO = new StoreTagDTO(
            $data["title"]
        );
        $tag = Tag::create($TagDTO->toArray());

        logActivity(request: $request, description: "User created a new tag", showable: true);
        return apiResponse(message: "Tag added successfully", data: TagResource::make($tag), statusCode: symfonyResponse::HTTP_CREATED);
    }

    public function update(UpdateTagRequest $request, $id)
    {
        try {

            $tag = Tag::findOrFail($id);
            $tag->fill($request->validated());
            $tag->save();

            logActivity(request: $request, description: "User updated a tag", showable: true);
            return apiResponse(message: "Tag updated successfully", data: TagResource::make($tag));
        } catch (ModelNotFoundException $e) {
            return apiResponse(errors: ["id" => ["No query results for tag"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $tag = Tag::find($id);
            if (!$tag) {
                return apiResponse(errors: ["id" => ["Tag not found"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
            }
            $tag->delete();

            logActivity(request: $request, description: "User deleted a tag", showable: true);
            return apiResponse(message: "Tag deleted successfully", statusCode: symfonyResponse::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return apiResponse(data: ["id" => ["No query results for tag"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
        }
    }
}
