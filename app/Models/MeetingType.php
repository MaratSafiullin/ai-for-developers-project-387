<?php

namespace App\Models;

use Database\Factories\MeetingTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A call template with a name, an optional description and a duration.
 * Created by the owner and never changed or deleted afterwards.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $duration_minutes
 */
#[Fillable(['name', 'description', 'duration_minutes'])]
class MeetingType extends Model
{
    /** @use HasFactory<MeetingTypeFactory> */
    use HasFactory;

    /**
     * The largest id a request may refer to: the contract types meeting type
     * ids as int32, so larger values are rejected before any query.
     */
    public const int MAX_ID = 2147483647;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
        ];
    }
}
