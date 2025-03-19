<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Str;


class CommentRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'id' => ['nullable', 'integer'],
            'commentable_id' => ['required', 'integer'],
            'commentable_type' => ['required', function ($attribute, $value, $fail) {
                $modelClass = "\\App\\Models\\" . Str::studly($value);
                if (!class_exists($modelClass)) {
                    $fail("The provided commentable type is invalid.");
                }
            }],
            'body' => ['required', 'string'],
            'parent_comment_id' => ['nullable', 'exists:comments,id'],
            'status' => ['nullable', 'in:approved,rejected,pending'],
        ];
    }
}
