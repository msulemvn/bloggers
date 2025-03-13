<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseRequest;

class StorePostRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug'],
            'user_id' => ['required', 'exists:users,id'],
            'content' => ['required', 'string'],
            'feature_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_published' => ['sometimes', 'boolean'],
            'status' => ['required', 'in:approved,disapproved'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title is required.',
            'slug.required' => 'The slug is required.',
            'slug.unique' => 'This slug already exists.',
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'content.required' => 'The content is required.',
            'feature_image.image' => 'The file must be an image.',
            'feature_image.mimes' => 'The image must be in jpeg, png, jpg, or gif format.',
            'feature_image.max' => 'The image must not be larger than 2MB.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be either "approved" or "disapproved".',
        ];
    }
}
