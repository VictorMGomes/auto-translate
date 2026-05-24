<?php

declare(strict_types=1);

namespace Victormgomes\AutoTranslate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class AutoTranslation extends Model
{
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'field',
        'locale',
        'content',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
