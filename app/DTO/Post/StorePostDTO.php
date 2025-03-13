<?php

namespace App\DTO\Post;

use App\DTO\BaseDTO;

class StorePostDTO extends BaseDTO
{
    public function __construct(
        public string $title,
        public string $content,
        public string $feature_image = "",
    ) {}
}
