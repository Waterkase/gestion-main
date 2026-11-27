<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Clickbar\Magellan\Cast\GeometryCast;

class geopoligono extends Model
{ 

    protected $table = 'geopoligono';

    protected $fillable = [
        'comucircu',
        'geom',
    ];

    protected $casts = [
        'geom' => GeometryCast::class,
        
    ];

}
