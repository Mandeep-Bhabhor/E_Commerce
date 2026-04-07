<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderFooterSetting extends Model
{
    protected $fillable = [
        'header_logo',
        'footer_logo',
        'email',
        'phone',
    ];

    protected $casts = [
        'header_logo' => 'array',
        'footer_logo' => 'array',
        'email' => 'array',
        'phone' => 'array',
    ];
}   