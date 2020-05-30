<?php

declare(strict_types=1);

namespace LaravelQueryAssertion\Test\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class User extends EloquentModel
{
    protected $fillable = ['email'];

    public $timestamps = false;

    protected $table = 'users';
}
