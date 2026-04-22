<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'file_url',
        'type',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * XP log entries that reference this material as a reward source.
     * Used to efficiently check whether a user has already earned XP for reading.
     */
    public function xpLogs(): HasMany
    {
        return $this->hasMany(XpLog::class, 'reference_id');
    }
}
