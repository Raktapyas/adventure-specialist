<?php

namespace App\Models;

use Database\Factories\NavigationItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    /** @use HasFactory<NavigationItemFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'label',
        'url',
        'type',
        'sort_order',
        'is_visible',
        'open_in_new_tab',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'open_in_new_tab' => 'boolean',
        ];
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    /**
     * All descendants (direct + indirect) IDs for cycle prevention.
     *
     * @return array<int>
     */
    public function descendantIds(): array
    {
        return $this->children()->pluck('id')->flatMap(function (int $id) {
            return array_merge([$id], static::find($id)?->descendantIds() ?? []);
        })->all();
    }

    /**
     * Depth of this item in the tree (0 = top-level).
     */
    public function chainDepth(): int
    {
        $depth = 0;

        for ($current = $this->parent; $current; $current = $current->parent) {
            $depth++;
        }

        return $depth;
    }

    public function isDropdown(): bool
    {
        if ($this->type === 'dropdown') {
            return true;
        }

        // Auto-dropdown when has visible children or grandchildren — ensures 3-level vertical flyout for any parent
        $children = $this->relationLoaded('children') ? $this->children : $this->children()->get();

        if ($children->isNotEmpty()) {
            return true;
        }

        // Check grandchildren without extra query if already eager loaded
        foreach ($children as $child) {
            $grand = $child->relationLoaded('children') ? $child->children : $child->children()->get();
            if ($grand->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolved href for frontend. Dropdown parents prevent page jump.
     */
    public function resolvedUrl(): string
    {
        if ($this->isDropdown()) {
            return 'javascript:void(0)';
        }

        return $this->url ?: '#';
    }

    /**
     * Whether this node has any visible children.
     */
    public function hasVisibleChildren(): bool
    {
        $children = $this->relationLoaded('children') ? $this->children : $this->children()->get();

        return $children->contains(fn (self $child) => $child->is_visible);
    }
}
