<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUniqueIds;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class RestaurantSSHDetails extends Model implements Sortable
{
    use HasFactory, SoftDeletes, HasUuids, SortableTrait;

    protected $fillable = [
        'restaurant_id',
        'ssh_host',
        'ssh_username',
        'ssh_password',
        'ssh_private_key',
        'ssh_port',
        'ssh_active',
        'order_column',
        'updated_by_user_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'ssh_port' => 'integer',
        'ssh_active' => 'boolean',
        'order_column' => 'integer',
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
