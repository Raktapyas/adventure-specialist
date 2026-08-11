<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlRedirect extends Model
{
    protected $table = 'redirects';

    protected $fillable = [
        'model_type',
        'model_id',
        'old_path',
        'new_path',
    ];

    /**
     * Resolve the content model that owns this historical URL, if it still exists.
     */
    public function target(): ?Model
    {
        $class = static::CLASSES[$this->model_type] ?? null;

        return $class ? $class::find($this->model_id) : null;
    }

    /**
     * @var array<string, class-string<Model>>
     */
    public const CLASSES = [
        'page' => Page::class,
        'service' => Service::class,
        'destination' => Destination::class,
        'package' => Package::class,
    ];
}
