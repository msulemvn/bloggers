<?php

namespace App\Http\Controllers\User;

use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response as symfonyResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\Request;
use App\DTO\User\StoreUserDTO;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        $users = User::where("id", "!=", Auth::id())->orderBy('id', 'DESC')->paginate();
        $users = UserResource::collection($users);
        return Inertia::render("Users", compact("users"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $userDTO = new StoreUserDTO($request->validated());
        $user = User::create($userDTO->toArray());

        logActivity(request: $request, description: "User created a new user", showable: true);
        return apiResponse(message: "User added successfully", data: UserResource::make($user), statusCode: symfonyResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function show(string $id): Response
    {
        return Inertia::render("users/show", [
            "user" => User::findOrFail($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {

            $user = User::findOrFail($id);
            $user->fill($request->validated());
            $user->save();

            logActivity(request: $request, description: "User updated a user", showable: true);
            return apiResponse(message: "User updated successfully", data: UserResource::make($user));
        } catch (ModelNotFoundException $e) {
            return apiResponse(errors: ["id" => ["No query results for model"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return apiResponse(errors: ["id" => ["User not found"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
            }
            $user->delete();

            logActivity(request: $request, description: "User deleted a user", showable: true);
            return apiResponse(message: "User deleted successfully", statusCode: symfonyResponse::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return apiResponse(data: ["id" => ["No query results for model"]], statusCode: symfonyResponse::HTTP_NOT_FOUND);
        }
    }
}
