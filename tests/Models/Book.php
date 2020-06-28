<?php

declare(strict_types=1);

namespace Laqu\Test\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Book extends EloquentModel
{
    protected $fillable = ['name', 'author_id'];

    public $timestamps = true;

    protected $table = 'books';

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
