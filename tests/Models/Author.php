<?php

declare(strict_types=1);

namespace Laqu\Test\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Author extends EloquentModel
{
    protected $fillable = ['name'];

    public $timestamps = true;

    protected $table = 'authors';

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
