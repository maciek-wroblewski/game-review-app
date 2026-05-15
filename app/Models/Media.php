<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'file_path', 'mime_type', 'file_name'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}