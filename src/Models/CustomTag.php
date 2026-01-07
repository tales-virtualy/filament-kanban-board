<?php

namespace FilamentKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomTag extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'badge_color',
        'text_color',
        'order_column',
    ];

    public function getTable()
    {
        return config('kanban.tables.tags', parent::getTable());
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }

            if (empty($tag->type)) {
                $tag->type = 'Global';
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name')) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}
