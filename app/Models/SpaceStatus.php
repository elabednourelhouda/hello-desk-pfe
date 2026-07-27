<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceStatus extends Model
{
    protected $fillable = [
        'name',
        'code',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Fallback color used whenever `color` is null/empty — should only
     * ever happen for rows created before the color column existed and
     * not yet re-saved through the admin form.
     */
    private const FALLBACK_COLOR = '#6b7280'; // gray-500

    /**
     * Space::status is a plain string column storing this status's
     * `code` (there is no real foreign key on spaces) — so, same as
     * ContactType::prospectVisits(), this relation is defined by
     * matching `spaces.status` to `space_statuses.code` rather than
     * the usual id/*_id pair.
     *
     * Note: this will never include spaces currently showing as
     * "Réservé" on the map — that display status is computed from
     * active reservations, never stored as `spaces.status`.
     */
    public function spaces()
    {
        return $this->hasMany(Space::class, 'status', 'code');
    }

    /**
     * The status's color as "r, g, b", ready to drop into an rgba()
     * CSS function. Centralized here (rather than repeated per-blade
     * match() statements) because the color is now an arbitrary,
     * admin-picked hex value — Tailwind's build-time class scanner
     * can never know it ahead of time, so we can't rely on utility
     * classes like `bg-[#xxxxxx]` for it. Inline `style` attributes
     * built from this method are the only reliable way to render an
     * admin-configurable color after `npm run build`.
     */
    public function rgbTriplet(): string
    {
        $hex = ltrim($this->color ?: self::FALLBACK_COLOR, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = ltrim(self::FALLBACK_COLOR, '#');
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r}, {$g}, {$b}";
    }

    /**
     * rgba(...) string at the given opacity, e.g. for a tile's tinted
     * background (low alpha) versus its solid border/dot (alpha 1).
     */
    public function toRgba(float $alpha = 1): string
    {
        return "rgba({$this->rgbTriplet()}, {$alpha})";
    }

    /**
     * Whether text sitting on top of the solid color needs to be dark
     * or light to stay readable, using the standard perceptual
     * luminance approximation (ITU-R BT.601). Admins can pick any hex
     * value, including light ones (e.g. a pale yellow "En pause"), so
     * we can't assume white text is always safe the way the old
     * hardcoded `text-emerald-800` etc. classes did.
     */
    public function needsDarkText(): bool
    {
        [$r, $g, $b] = array_map('intval', explode(', ', $this->rgbTriplet()));

        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.6;
    }

    /**
     * Convenience bundle of inline styles for a status "pill"/badge:
     * tinted background, solid border/dot color, and readable text.
     * Used identically by the map legend, the map tiles, and the
     * spaces list/show pages, so the four map blade files no longer
     * each need their own copy of this logic.
     */
    public function badgeStyle(): string
    {
        $textColor = $this->needsDarkText() ? '#1f2937' : '#ffffff'; // gray-800 / white

        return sprintf(
            'background-color: %s; border-color: %s; color: %s;',
            $this->toRgba(0.14),
            $this->toRgba(1),
            $textColor
        );
    }
}
