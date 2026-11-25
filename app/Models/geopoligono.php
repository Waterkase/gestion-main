<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Clickbar\Magellan\Database\Eloquent\HasPostgisColumns;

class geopoligono extends Model
{
use HasPostgisColumns;

    protected $table = 'geopoligono';

    protected $fillable = [
        'ComuCircu',
        'geom',
    ];

    protected array $postgisColumns = [
        'geom' => [
            'type' => 'geometry',
            'srid' => 4326,
        ],
    ];

}
