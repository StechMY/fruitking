<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentStock extends Model
{
    use HasFactory,DefaultDatetimeFormat;

    protected $fillable = [
        'agent_id',
        'fruit_id',
        'stock_pack',
        'status'
    ];

    public function fruit()
    {
        return $this->belongsTo(Fruit::class);
    }

    public function record()
    {
        return $this->hasMany(AgentStockRecord::class,'agentstock_id');
    }
}
