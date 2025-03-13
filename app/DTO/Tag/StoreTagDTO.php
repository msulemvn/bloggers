<?php

namespace App\DTO\Tag;

use App\DTO\BaseDTO;

class StoreTagDTO extends BaseDTO
{
    public function __construct(
        public string $title,
    ) {}
}
