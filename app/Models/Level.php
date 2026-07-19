<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'min_xp',
        'icon_url',
    ];

    /**
     * Get the virtual xp_threshold attribute (alias of min_xp).
     */
    public function getXpThresholdAttribute(): int
    {
        return $this->min_xp;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * A level has many users who have attained it.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
