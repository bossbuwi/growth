<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\System;

class Machine extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function systems()
    {
        return $this->hasMany(System::class);
    }
}
