<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaCode extends Model
{
    use HasFactory,DefaultDatetimeFormat;

    protected $fillable = [
        'name',
        'code',
    ];
}
