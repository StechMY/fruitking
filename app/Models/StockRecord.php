<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRecord extends Model
{
    use HasFactory,DefaultDatetimeFormat;

    protected $fillable = [
        'fruit_id',
        'stock_before',
        'quantity',
        'stock_after',
        'remarks',
    ];

    public function fruit()
    {
        return $this->belongsTo(Fruit::class, 'fruit_id');
    }
}
