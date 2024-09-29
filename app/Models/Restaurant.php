<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Restaurant extends Model implements Sortable
{
    use HasFactory, SoftDeletes, HasUuids, SortableTrait;

    protected $fillable = [
        'name',
        'description',
        'domain',
        'logo',

        'installation_token',

        'featured',
        'visible',
        'verified',

        'status',
        'status_msg',
        'online_order_status',
        'online_order_msg',
        'reservation_status',
        'reservation_msg',
        'shutdown_status',
        'shutdown_msg',

        'order_column',

        'other_details',

        'updated_by_user_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'visible' => 'boolean',
        'verified' => 'boolean',

        'status' => 'boolean',
        'online_order_status' => 'boolean',
        'reservation_status' => 'boolean',
        'shutdown_status' => 'boolean',

        'order_column' => 'integer',

        'other_details' => 'array',
    ];

    public function ftp(): HasOne
    {
        return $this->hasOne(related: RestaurantFTPDetails::class);
    }

    public function ssh(): HasOne
    {
        return $this->hasOne(related: RestaurantSSHDetails::class);
    }

    public function db(): HasOne
    {
        return $this->hasOne(related: RestaurantDatatbaseDetails::class);
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
