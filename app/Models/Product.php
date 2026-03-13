<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'name',
        'detail',
        'image',
        'size',
        'color',
        'price',
        'status',
        'category',
    ];

    public function sizes()
    {
        return $this->belongsToMany(Size::class);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }


    protected $casts = [
        'size' => 'array',
        'color' => 'array',
        'category' => 'array',
    ];
}
