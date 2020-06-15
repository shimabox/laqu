<?php

declare(strict_types=1);

namespace Laqu\Test\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Author extends EloquentModel
{
    protected $fillable = ['name'];

    public $timestamps = false;

    protected $table = 'authors';
}
