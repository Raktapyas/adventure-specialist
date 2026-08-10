<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $guarded = [];

    public function getPath(): string
    {
        return '/special-package/'.$this->slug.'/';
    }

    public function publicUrl(): string
    {
        return $this->getPath();
    }
}
