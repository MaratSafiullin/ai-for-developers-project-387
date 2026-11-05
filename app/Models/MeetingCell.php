<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A 15-minute cell occupied by a meeting. The unique index on `starts_at`
 * forbids overlapping meetings (ADR 0002). An implementation detail, not a
 * domain term.
 *
 * @property int $id
 * @property int $meeting_id
 * @property CarbonImmutable $starts_at
 */
#[Fillable(['starts_at'])]
class MeetingCell extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
        ];
    }
}
