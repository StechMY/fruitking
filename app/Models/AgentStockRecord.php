<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentStockRecord extends Model
{
    use HasFactory,DefaultDatetimeFormat;

    protected $fillable = [
        'agentstock_id',
        'stock_before',
        'quantity',
        'stock_after',
        'remarks'
    ];

    public function agentstock()
    {
        return $this->belongsTo(agentstock::class, 'agentstock_id');
    }
}
