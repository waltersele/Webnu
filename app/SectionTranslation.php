<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SectionTranslation extends Model
{
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_AI = 'ai';
    public const SOURCE_AI_EDITED = 'ai_edited';

    protected $fillable = ['section_id', 'locale', 'name', 'source'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
