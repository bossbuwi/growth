<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\System;


class Rule extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function system()
    {
        return $this->belongsTo(System::class);
    }
}
