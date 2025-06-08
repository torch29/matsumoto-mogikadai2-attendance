<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestCorrection extends Model
{
    use HasFactory;

    public function rest()
    {
        return $this->belongsTo(Rest::class);
    }

    protected $fillable = [
        'rest_id',
        'corrected_rest_start',
        'corrected_rest_end',
        'note',
        'approve_status',
    ];
}
