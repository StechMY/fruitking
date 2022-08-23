<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRecord extends Model
{
    use HasFactory,DefaultDatetimeFormat;

    protected $fillable = [
        'from_id',
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

    public function from()
    {
        return $this->belongsTo(Administrator::class, 'from_id');
    }
}
