<?php

namespace CMSCore\Models;

use CMSCore\Enums\MenuType;
use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Kalnoy\Nestedset\NodeTrait;

/**
 * @property MenuType $type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Menu> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Menu> $descendants
 * @property-read int|null $descendants_count
 * @property-read string|null $url
 * @property-read Menu|null $parent
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu root()
 *
 * @mixin \Eloquent
 */
class Menu extends TenantModel
{
    // use NodeTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => MenuType::class,
    ];

    /**
     * Parent menu
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Direct children ordered
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get ordered children recursively.
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Scope a query to only include root menus.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id')->orderBy('order');
    }

    /**
     * Scope a query to only include menus ordered by nested set.
     */
    // public function scopeOrdered($query)
    // {
    //     return $query->orderBy('lft');
    // }

    /**
     * Get all ancestors of this menu.
     */
    // public function ancestors()
    // {
    //     return $this->where('lft', '<', $this->lft)
    //         ->where('rgt', '>', $this->rgt)
    //         ->ordered()
    //         ->get();
    // }

    /**
     * Check if this menu is a descendant of another menu.
     */
    // public function isDescendantOf(Menu $menu): bool
    // {
    //     return $this->lft > $menu->lft && $this->rgt < $menu->rgt;
    // }

    /**
     * Check if this menu is an ancestor of another menu.
     */
    // public function isAncestorOf(Menu $menu): bool
    // {
    //     return $this->lft < $menu->lft && $this->rgt > $menu->rgt;
    // }

    /**
     * Get the URL for the menu item.
     */
    public function getUrlAttribute(): ?string
    {
        return match ($this->type) {
            MenuType::INTERNAL_LINK => $this->link,
            MenuType::EXTERNAL_LINK => $this->link,
            default => $this->link,
        };
    }
}
