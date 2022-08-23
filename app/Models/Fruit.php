<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fruit extends Model
{
    use HasFactory,DefaultDatetimeFormat;

    protected $fillable = [
        'name',
        'ori_price',
        'sales_price',
        'commission_price',
        'image',
        'stock',
        'status',
    ];

    public function record()
    {
        return $this->hasMany(StockRecord::class);
    }
}
