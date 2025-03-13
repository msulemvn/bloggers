<?php

namespace App\DTO\Post;

use App\DTO\BaseDTO;

class StorePostDTO extends BaseDTO
{
    public function __construct(
        public string $title,
        public int $user_id,
        public bool $is_published,
        public string $status,
        public string $feature_image = "",
    ) {}
}
