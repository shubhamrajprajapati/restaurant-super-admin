<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChildRestaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'installation_token',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }
}
