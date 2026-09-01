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
        'homepage_services_eyebrow',
        'homepage_services_title',
        'homepage_services_lede',
        'homepage_services_visible',
        'homepage_destinations_eyebrow',
        'homepage_destinations_title',
        'homepage_destinations_lede',
        'homepage_destinations_visible',
        'homepage_packages_eyebrow',
        'homepage_packages_title',
        'homepage_packages_lede',
        'homepage_packages_visible',
        'homepage_gallery_eyebrow',
        'homepage_gallery_title',
        'homepage_gallery_lede',
        'homepage_gallery_visible',
        'homepage_why_eyebrow',
        'homepage_why_title',
        'homepage_why_lede',
        'homepage_why_visible',
        'footer_services_title',
        'footer_destinations_title',
        'footer_contact_title',
        'homepage_services_button_label',
        'homepage_services_button_url',
        'navigation_flyout_style',
    ];

    protected $casts = [
        'stats' => 'array',
        'homepage_services_visible' => 'boolean',
        'homepage_destinations_visible' => 'boolean',
        'homepage_packages_visible' => 'boolean',
        'homepage_gallery_visible' => 'boolean',
        'homepage_why_visible' => 'boolean',
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

    /**
     * Homepage headings blocks with fallbacks. Each block: eyebrow/title/lede/visible + services button_label/button_url.
     *
     * @return array{eyebrow: string, title: string, lede: string, visible: bool, button_label: string, button_url: string}
     */
    public function homepageBlock(string $key): array
    {
        $defaults = [
            'services' => [
                'eyebrow' => 'What we do',
                'title' => 'AST Services',
                'lede' => 'Culture, adventure and wildlife — arranged for groups and individuals across the Himalaya.',
                'visible' => true,
                'button_label' => 'View all services',
                'button_url' => '/ast-services/',
            ],
            'destinations' => [
                'eyebrow' => 'Where we go',
                'title' => 'Destinations',
                'lede' => 'From the Kathmandu Valley to the roof of the world — five countries, one standard of care.',
                'visible' => true,
            ],
            'packages' => [
                'eyebrow' => 'Signature programs',
                'title' => 'AST Special Package Program',
                'lede' => '',
                'visible' => true,
            ],
            'gallery' => [
                'eyebrow' => 'Moments',
                'title' => 'AST Photo Gallery',
                'lede' => '',
                'visible' => true,
            ],
            'why' => [
                'eyebrow' => 'About us',
                'title' => 'Why Choose AST?',
                'lede' => 'Adventure Specialist Travel is very concerned about your comfort, your safety and the quality of your time in the mountains.',
                'visible' => true,
            ],
        ];

        $def = $defaults[$key] ?? $defaults['services'];

        $hasButton = array_key_exists('button_label', $def);

        $base = [
            'eyebrow' => filled($this->{"homepage_{$key}_eyebrow"}) ? (string) $this->{"homepage_{$key}_eyebrow"} : $def['eyebrow'],
            'title' => filled($this->{"homepage_{$key}_title"}) ? (string) $this->{"homepage_{$key}_title"} : $def['title'],
            'lede' => filled($this->{"homepage_{$key}_lede"}) ? (string) $this->{"homepage_{$key}_lede"} : $def['lede'],
            'visible' => $this->{"homepage_{$key}_visible"} !== null ? (bool) $this->{"homepage_{$key}_visible"} : (bool) $def['visible'],
        ];

        if ($hasButton) {
            $base['button_label'] = filled($this->homepage_services_button_label) ? (string) $this->homepage_services_button_label : $def['button_label'];
            $base['button_url'] = filled($this->homepage_services_button_url) ? (string) $this->homepage_services_button_url : $def['button_url'];
        }

        return $base;
    }

    /**
     * All homepage headings keyed by block.
     *
     * @return array<string, array{eyebrow: string, title: string, lede: string, visible: bool}>
     */
    public function homepageHeadings(): array
    {
        return [
            'services' => $this->homepageBlock('services'),
            'destinations' => $this->homepageBlock('destinations'),
            'packages' => $this->homepageBlock('packages'),
            'gallery' => $this->homepageBlock('gallery'),
            'why' => $this->homepageBlock('why'),
        ];
    }

    /**
     * Footer headings block.
     *
     * @return array{services: string, destinations: string, contact: string}
     */
    public function footerHeadings(): array
    {
        return [
            'services' => filled($this->footer_services_title) ? (string) $this->footer_services_title : 'TREKKING & ACTIVITIES',
            'destinations' => filled($this->footer_destinations_title) ? (string) $this->footer_destinations_title : 'DESTINATIONS',
            'contact' => filled($this->footer_contact_title) ? (string) $this->footer_contact_title : 'Contact Us',
        ];
    }

    public function navFlyoutStyle(): string
    {
        return ($this->navigation_flyout_style ?? 'classic') === 'image' ? 'image' : 'classic';
    }
}
