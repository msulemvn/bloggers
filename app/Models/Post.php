<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory, HasSlug;
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'feature_image',
    ];

    protected static function booted()
    {
        static::creating(function ($post) {
            $post->user_id = $post->user_id ?? Auth::id();
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getFeatureImageAttribute($value)
    {
        return $value ? asset('storage/pictures/' . $value) : null;
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }
}
