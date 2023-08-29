<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeMy($query)
    {
        return $query->where('user_id', auth()->user()->id);
    }
}
