<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'stats',
        'cta_eyebrow',
        'cta_title',
        'cta_button_label',
        'cta_button_url',
        'cta_image',
        'logo',
        'logo_white',
        'contact_company',
        'contact_address',
        'contact_phone_primary',
        'contact_phone_secondary',
        'contact_phone_owner',
        'contact_email',
        'contact_hours',
        'contact_facebook_url',
    ];

    protected $casts = [
        'stats' => 'array',
    ];

    /**
     * The single settings row, created on first access.
     */
    public static function current(): self
    {
        return static::query()->first() ?? static::create();
    }

    /**
     * Normalized stats rows for the homepage strip. Rows missing both a
     * value and a label are dropped; an entirely empty configuration falls
     * back to the seeded defaults.
     *
     * @return array<int, array{value: int, suffix: string, label: string}>
     */
    public function statsRows(): array
    {
        $rows = collect($this->stats ?? [])
            ->filter(fn (array $row): bool => filled($row['value'] ?? null) || filled($row['label'] ?? null))
            ->map(fn (array $row): array => [
                'value' => (int) ($row['value'] ?? 0),
                'suffix' => (string) ($row['suffix'] ?? ''),
                'label' => (string) ($row['label'] ?? ''),
            ])
            ->values();

        return $rows->isNotEmpty() ? $rows->all() : $this->defaultStats();
    }

    /**
     * CTA strip fields for home.blade.php — empty values mean "hide".
     *
     * @return array{eyebrow: ?string, title: ?string, button_label: ?string, button_url: ?string, image: ?string}
     */
    public function ctaBlock(): array
    {
        return [
            'eyebrow' => $this->cta_eyebrow,
            'title' => $this->cta_title,
            'button_label' => $this->cta_button_label,
            'button_url' => $this->cta_button_url,
            'image' => $this->cta_image,
        ];
    }

    /**
     * Footer contact block fields.
     *
     * @return array<string, ?string>
     */
    public function contactBlock(): array
    {
        return [
            'company' => $this->contact_company,
            'address' => $this->contact_address,
            'phone_primary' => $this->contact_phone_primary,
            'phone_secondary' => $this->contact_phone_secondary,
            'phone_owner' => $this->contact_phone_owner,
            'email' => $this->contact_email,
            'hours' => $this->contact_hours,
            'facebook_url' => $this->contact_facebook_url,
        ];
    }

    /**
     * Branding block for navbar logo. Falls back to bundled public/images.
     *
     * @return array{logo: string, logo_white: string}
     */
    public function brandingBlock(): array
    {
        return [
            'logo' => $this->logo ?: '/images/logo.png',
            'logo_white' => $this->logo_white ?: '/images/logo-white.png',
        ];
    }

    private function defaultStats(): array
    {
        return [
            ['value' => 2013, 'suffix' => '', 'label' => 'Established Year'],
            ['value' => 1200, 'suffix' => '+', 'label' => 'Total Trekking'],
            ['value' => 1600, 'suffix' => '+', 'label' => 'Happy Trekkers'],
        ];
    }
}
