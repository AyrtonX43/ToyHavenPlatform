<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'image',
        'icon',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_category_preferences', 'category_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Get the display icon for this category (Bootstrap Icon class).
     * Uses icon column if set, otherwise maps from slug/name. Always returns an icon.
     */
    public function getDisplayIcon(): string
    {
        if (!empty($this->icon)) {
            return 'bi-' . ltrim($this->icon, 'bi-');
        }

        $slug = strtolower($this->slug ?? '');
        $name = strtolower($this->name ?? '');
        $searchText = $slug . ' ' . $name;

        // Exact slug match (most specific)
        $iconMap = [
            'action-figures' => 'bi-person-standing',
            'action-figure' => 'bi-person-standing',
            'board-games' => 'bi-dice-5',
            'board-game' => 'bi-dice-5',
            'puzzles' => 'bi-puzzle',
            'puzzle' => 'bi-puzzle',
            'dolls' => 'bi-person-standing-dress',
            'doll' => 'bi-person-standing-dress',
            'plush' => 'bi-heart',
            'stuffed-animals' => 'bi-heart',
            'stuffed-animal' => 'bi-heart',
            'educational' => 'bi-book',
            'education' => 'bi-book',
            'learning' => 'bi-book',
            'outdoor' => 'bi-sun',
            'outdoors' => 'bi-sun',
            'sports' => 'bi-trophy',
            'sport' => 'bi-trophy',
            'arts-crafts' => 'bi-brush',
            'arts-and-crafts' => 'bi-brush',
            'crafts' => 'bi-brush',
            'vehicles' => 'bi-truck',
            'vehicle' => 'bi-truck',
            'building-blocks' => 'bi-bricks',
            'blocks' => 'bi-bricks',
            'lego' => 'bi-bricks',
            'construct' => 'bi-bricks',
            'collectibles' => 'bi-gem',
            'collectible' => 'bi-gem',
            'electronics' => 'bi-cpu',
            'electronic' => 'bi-cpu',
            'video-games' => 'bi-controller',
            'video-game' => 'bi-controller',
            'games' => 'bi-controller',
            'game' => 'bi-controller',
            'robotic' => 'bi-robot',
            'robots' => 'bi-robot',
            'robot' => 'bi-robot',
            'musical' => 'bi-music-note-beamed',
            'music' => 'bi-music-note-beamed',
            'instruments' => 'bi-music-note-beamed',
            'instrument' => 'bi-music-note-beamed',
            'science' => 'bi-beaker',
            'stem' => 'bi-beaker',
            'diecast' => 'bi-car-front',
            'die-cast' => 'bi-car-front',
            'vehicles-toys' => 'bi-car-front',
            'remote-control' => 'bi-car-front',
            'figures' => 'bi-person-standing',
            'trading-cards' => 'bi-card-image',
            'cards' => 'bi-card-image',
            'books' => 'bi-book',
            'books-toys' => 'bi-book',
            'pretend-play' => 'bi-house-door',
            'role-play' => 'bi-house-door',
            'imaginative' => 'bi-stars',
            'baby' => 'bi-heart',
            'infant' => 'bi-heart',
            'toddler' => 'bi-person',
            'bath-toys' => 'bi-droplet',
            'water-toys' => 'bi-droplet',
        ];

        if (isset($iconMap[$slug])) {
            return $iconMap[$slug];
        }

        // Keyword match (order matters - more specific phrases first)
        $keywordMap = [
            'action figure' => 'bi-person-standing',
            'board game' => 'bi-dice-5',
            'puzzle' => 'bi-puzzle',
            'doll' => 'bi-person-standing-dress',
            'plush' => 'bi-heart',
            'stuffed animal' => 'bi-heart',
            'stuffed' => 'bi-heart',
            'teddy' => 'bi-heart',
            'educational' => 'bi-book',
            'learning' => 'bi-book',
            'outdoor' => 'bi-sun',
            'sport' => 'bi-trophy',
            'arts and crafts' => 'bi-brush',
            'arts & crafts' => 'bi-brush',
            'arts-craft' => 'bi-brush',
            'craft' => 'bi-brush',
            'vehicle' => 'bi-truck',
            'diecast' => 'bi-car-front',
            'die cast' => 'bi-car-front',
            'remote control' => 'bi-car-front',
            ' rc ' => 'bi-car-front',
            ' rc-' => 'bi-car-front',
            'rc car' => 'bi-car-front',
            'rc vehicle' => 'bi-car-front',
            'building block' => 'bi-bricks',
            'lego' => 'bi-bricks',
            'block' => 'bi-bricks',
            'collectible' => 'bi-gem',
            'electronic' => 'bi-cpu',
            'video game' => 'bi-controller',
            'arcade' => 'bi-controller',
            'robot' => 'bi-robot',
            'music' => 'bi-music-note-beamed',
            'instrument' => 'bi-music-note-beamed',
            'science' => 'bi-beaker',
            'stem' => 'bi-beaker',
            'trading card' => 'bi-card-image',
            'pretend' => 'bi-house-door',
            'role play' => 'bi-house-door',
            'baby' => 'bi-heart',
            'infant' => 'bi-heart',
            'toddler' => 'bi-person',
            'bath toy' => 'bi-droplet',
            'water toy' => 'bi-droplet',
        ];

        foreach ($keywordMap as $keyword => $icon) {
            if (str_contains($searchText, $keyword)) {
                return $icon;
            }
        }

        return 'bi-toy';
    }

    /**
     * Get Flaticon animated icon config for this category (for welcome/profile selection).
     * Returns null if no mapping exists.
     */
    public function getAnimatedIconConfig(): ?array
    {
        $map = config('category_animated_icons', []);
        $name = trim($this->name ?? '');
        $slug = $this->slug ?? '';

        if (isset($map[$name])) {
            return $map[$name];
        }
        $nameLower = strtolower($name);
        foreach ($map as $key => $config) {
            $keySlug = \Illuminate\Support\Str::slug($key);
            $keyLower = strtolower($key);
            if ($keySlug === $slug || str_contains($slug, $keySlug) || str_contains($keySlug, $slug)) {
                return $config;
            }
            if (str_contains($keyLower, $nameLower) || str_contains($nameLower, $keyLower)) {
                return $config;
            }
        }
        return null;
    }

    /**
     * Flaticon CDN PNG URL for the animated icon (static fallback / preview).
     */
    public function getAnimatedIconPngUrl(): ?string
    {
        $config = $this->getAnimatedIconConfig();
        if (!$config || empty($config['id']) || empty($config['pack'])) {
            return null;
        }
        return 'https://cdn-icons-png.flaticon.com/128/' . $config['pack'] . '/' . $config['id'] . '.png';
    }
}
