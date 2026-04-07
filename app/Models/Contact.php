<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    //
    use HasFactory;

    // Allow these fields to be saved directly from the form
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
    ];

    // Optional: Setup the relationship so you can easily grab the user data
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
