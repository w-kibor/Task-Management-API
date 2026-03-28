<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'due_date',
        'priority',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date:Y-m-d',
    ];
}
