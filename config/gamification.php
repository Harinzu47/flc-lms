<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Rank Proximity Threshold
    |--------------------------------------------------------------------------
    |
    | The maximum XP gap between a member and the member directly above them
    | on the leaderboard that will trigger a rank progress notification.
    | Set to 0 to disable rank proximity notifications entirely.
    |
    */

    'rank_proximity_threshold' => (int) env('RANK_PROXIMITY_THRESHOLD', 50),

    /*
    |--------------------------------------------------------------------------
    | Rank Proximity Minimum Gap Change
    |--------------------------------------------------------------------------
    |
    | The minimum decrease in XP gap (compared to the last notification) required
    | before a new rank progress notification is sent.  Prevents spamming the
    | user with a notification on every single XP award while they remain within
    | the proximity threshold.
    |
    */

    'rank_proximity_min_gap_change' => (int) env('RANK_PROXIMITY_MIN_GAP_CHANGE', 10),

];
