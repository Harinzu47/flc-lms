<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'level_id',
        'total_xp',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'total_xp'          => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * A user belongs to a level (their current rank).
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * A user has many task submissions.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * A user has many XP log entries (append-only audit trail).
     */
    public function xpLogs(): HasMany
    {
        return $this->hasMany(XpLog::class);
    }

    /**
     * A user has unlocked many badges (via the user_badges pivot table).
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('unlocked_at');
    }

    // -------------------------------------------------------------------------
    // Gamification Helpers
    // -------------------------------------------------------------------------

    /**
     * The highest Level tier the user has reached based on their total XP.
     * Falls back to a synthetic "Novice" object if no levels exist in the DB.
     *
     * Queries the Level table directly so the result always reflects the
     * current total_xp, not the cached level_id FK value.
     */
    public function currentLevel(): ?Level
    {
        return Level::query()
            ->where('min_xp', '<=', $this->total_xp)
            ->orderByDesc('min_xp')
            ->first();
    }

    /**
     * The next Level tier above the user's current XP.
     * Returns null if the user is already at the maximum level.
     */
    public function nextLevel(): ?Level
    {
        return Level::query()
            ->where('min_xp', '>', $this->total_xp)
            ->orderBy('min_xp')
            ->first();
    }

    /**
     * Percentage of XP earned between the current and next level thresholds.
     * Returns 100 if the user is at the max level (no next level exists).
     * Returns 0 if no levels have been seeded yet.
     */
    public function progressPercentage(): int
    {
        $current = $this->currentLevel();
        $next    = $this->nextLevel();

        // Max level reached — bar is full
        if ($current !== null && $next === null) {
            return 100;
        }

        // No levels seeded or below first threshold
        if ($current === null) {
            if ($next === null) {
                return 0;
            }

            // Progress from 0 toward first level
            $percentage = ($this->total_xp / $next->min_xp) * 100;
            return (int) min(100, max(0, round($percentage)));
        }

        $range = $next->min_xp - $current->min_xp;

        if ($range <= 0) {
            return 100;
        }

        $earned     = $this->total_xp - $current->min_xp;
        $percentage = ($earned / $range) * 100;

        return (int) min(100, max(0, round($percentage)));
    }
}
