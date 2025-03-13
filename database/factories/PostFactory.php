<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{

    public function definition(): array
    {
        $title = $this->faker->sentence;
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'user_id' => 2,
            'content' => $this->faker->paragraphs(3, true),
            'feature_image' => $this->faker->imageUrl(),
            'is_published' => $this->faker->boolean,
            'status' => $this->faker->randomElement(['approved', 'disapproved']),
        ];
    }
}
