<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    use HasFactory,HasUuids;
	protected $table = 'donors';
	protected $guarded = [''];

	protected $casts = [
		'id' => 'string',
		'created_at' => 'datetime:d-M-Y H:i:s',
		'updated_at' => 'datetime:d-M-Y H:i:s',
	];

}
