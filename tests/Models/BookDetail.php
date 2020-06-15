<?php

declare(strict_types=1);

namespace Laqu\Test\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookDetail extends EloquentModel
{
    use SoftDeletes;

    protected $fillable = ['isbn', 'published_date', 'price'];

    public $timestamps = false;

    protected $table = 'bookdetails';
}
