<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseRequest;

class UpdatePostRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:posts,slug,' . $this->post],
            'user_id' => ['sometimes', 'exists:users,id'],
            'content' => ['sometimes', 'string'],
            'feature_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_published' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:approved,disapproved'],
        ];
    }

    public function messages()
    {
        return [
            'slug.unique' => 'This slug already exists.',
            'user_id.exists' => 'The selected user does not exist.',
            'feature_image.image' => 'The file must be an image.',
            'feature_image.mimes' => 'The image must be in jpeg, png, jpg, or gif format.',
            'feature_image.max' => 'The image must not be larger than 2MB.',
            'status.in' => 'The status must be either "approved" or "disapproved".',
        ];
    }
}
