<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\SortableTrait;

class RestaurantDatatbaseDetails extends Model
{
    use HasFactory, HasUuids, SoftDeletes, SortableTrait;

    protected $fillable = [
        'restaurant_id',

        'connection',
        'host',
        'port',
        'database',
        'username',
        'password',
        'active',

        'name',
        'default_cmd',
        'is_valid',

        'order_column',

        'updated_by_user_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'port' => 'integer',
        'active' => 'boolean',
        'order_column' => 'integer',
        'is_valid' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'created_by_user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'updated_by_user_id');
    }
}
