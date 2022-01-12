<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'process_date',
        'currency',
        'description',
        'user_id',
        'amount',
    ];

    public function category()
    {
        return $this->hasOne(Category::class);
    }

}


