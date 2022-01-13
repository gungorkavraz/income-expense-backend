<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

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
        return $this->belongsTo(Category::class);
    }

}


