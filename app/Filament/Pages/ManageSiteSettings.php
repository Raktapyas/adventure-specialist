<?php

namespace App\Filament\Pages;

use App\Filament\Components\MediaPicker;
use App\Models\Destination;
use App\Models\Media;
use App\Models\Page as PageModel;
use App\Models\Service;
use App\Models\SiteSetting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;

class ManageSiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Administration';

    protected static string $view = 'filament.pages.manage-site-settings';

    protected static ?string $title = 'Site Settings';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::current()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Homepage Stats Strip')
                    ->description('The animated counters shown on the homepage. Leave a row\'s value and label empty to hide it.')
                    ->schema([
                        Repeater::make('stats')
                            ->label('Stat rows')
                            ->columns(3)
                            ->schema([
                                TextInput::make('value')
                                    ->numeric()
                                    ->label('Value'),
                                TextInput::make('suffix')
                                    ->label('Suffix')
                                    ->maxLength(10)
                                    ->helperText('e.g. + or m+'),
                                TextInput::make('label')
                                    ->label('Label')
                                    ->maxLength(60),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('CTA Strip')
                    ->description('The dark call-to-action band at the bottom of the homepage. Empty fields are hidden.')
                    ->schema([
                        TextInput::make('cta_eyebrow')
                            ->label('Eyebrow')
                            ->maxLength(80),
                        TextInput::make('cta_title')
                            ->label('Heading')
                            ->maxLength(160),
                        TextInput::make('cta_button_label')
                            ->label('Button label')
                            ->maxLength(40),
                        TextInput::make('cta_button_url')
                            ->label('Button URL')
                            ->maxLength(255)
                            ->helperText('Path starting with /, e.g. /contact/#enquiry')
                            ->rules(['starts_with:/']),
                        Select::make('cta_image')
                            ->label('Background image (from Media Library)')
                            ->searchable()
                            ->preload()
                            ->allowHtml()
                            ->getSearchResultsUsing(function (string $search): array {
                                return Media::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(fn (Media $media): array => [
                                        $media->path => self::mediaOptionLabel($media),
                                    ])
                                    ->all();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $media = Media::where('path', $value)->first();

                                return $media ? self::mediaOptionLabel($media) : e($value);
                            })
                            // Validation state is a plain string here; normalize to a host-relative path.
                            ->mutateStateForValidationUsing(fn ($state): ?string => Media::normalizePath($state))
                            ->rules([
                                'nullable',
                                'string',
                                'max:255',
                                'starts_with:/',
                                'not_regex:/\/\//',
                                'not_regex:/\.\./',
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Homepage Headings')
                    ->description('Manage all 5 homepage section headers with one save. Delete "AST Services" and type "Activities" in the Title input, likewise for Destinations, Packages, Gallery and Why Choose.')
                    ->icon('heroicon-o-rectangle-stack')
                    ->columns(2)
                    ->schema([
                        TextInput::make('homepage_services_eyebrow')
                            ->label('Services — Eyebrow')
                            ->maxLength(40)
                            ->placeholder('What we do')
                            ->helperText('Small caps above title'),
                        TextInput::make('homepage_services_title')
                            ->label('Services — Title')
                            ->maxLength(60)
                            ->placeholder('AST Services')
                            ->helperText('Delete and type "Activities" to rename. Leave empty for default.'),
                        Textarea::make('homepage_services_lede')
                            ->label('Services — Lede')
                            ->rows(2)
                            ->maxLength(180)
                            ->placeholder('Culture, adventure and wildlife — arranged for groups and individuals across the Himalaya.')
                            ->columnSpanFull(),
                        TextInput::make('homepage_services_button_label')
                            ->label('Services — Button label')
                            ->maxLength(40)
                            ->placeholder('View all services')
                            ->helperText('Blue button at right. Delete and type "View all activities". Leave empty for default.'),
                        Select::make('homepage_services_button_quick_pick')
                            ->label('Services — Button URL (quick pick)')
                            ->placeholder('Search pages, services, destinations…')
                            ->helperText('Search existing Page/Service/Destination to autofill URL below, or type custom URL.')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                if (! Schema::hasTable('pages') && ! Schema::hasTable('services') && ! Schema::hasTable('destinations')) {
                                    return [];
                                }
                                $results = [];
                                try {
                                    if (Schema::hasTable('pages')) {
                                        $pages = PageModel::query()
                                            ->where('is_published', true)
                                            ->where(function ($q) use ($search): void {
                                                $q->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
                                            })->orderBy('title')->limit(10)->get();
                                        foreach ($pages as $page) {
                                            $path = $page->getPath();
                                            $results[$path] = 'Page: '.$page->title.' — '.$path;
                                        }
                                    }
                                } catch (\Throwable $e) {
                                }
                                try {
                                    if (Schema::hasTable('services')) {
                                        $services = Service::query()
                                            ->where('is_published', true)
                                            ->where(function ($q) use ($search): void {
                                                $q->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
                                            })->orderBy('title')->limit(10)->get();
                                        foreach ($services as $service) {
                                            $path = $service->getPath();
                                            $results[$path] = 'Service: '.$service->title.' — '.$path;
                                        }
                                    }
                                } catch (\Throwable $e) {
                                }
                                try {
                                    if (Schema::hasTable('destinations')) {
                                        $destinations = Destination::query()
                                            ->where('is_published', true)
                                            ->where(function ($q) use ($search): void {
                                                $q->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
                                            })->orderBy('title')->limit(10)->get();
                                        foreach ($destinations as $destination) {
                                            $path = $destination->getPath();
                                            $results[$path] = 'Destination: '.$destination->title.' — '.$path;
                                        }
                                    }
                                } catch (\Throwable $e) {
                                }

                                return array_slice($results, 0, 20, true);
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => $value ? e($value) : null)
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if (filled($state)) {
                                    $set('homepage_services_button_url', $state);
                                }
                            })
                            ->dehydrated(false),
                        TextInput::make('homepage_services_button_url')
                            ->label('Services — Button URL')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-m-link')
                            ->placeholder('/ast-services/ or /activities/ or https://…')
                            ->helperText('Update link path to match activities if slug changes. Leave empty for /ast-services/.')
                            ->rules(['nullable', 'string', 'max:255', 'starts_with:/,http://,https://'])
                            ->columnSpanFull(),
                        Toggle::make('homepage_services_visible')
                            ->label('Show Services section')
                            ->helperText('Toggle off to hide entire section')
                            ->inline(false)
                            ->default(true),

                        TextInput::make('homepage_destinations_eyebrow')
                            ->label('Destinations — Eyebrow')
                            ->maxLength(40)
                            ->placeholder('Where we go'),
                        TextInput::make('homepage_destinations_title')
                            ->label('Destinations — Title')
                            ->maxLength(60)
                            ->placeholder('Destinations'),
                        Textarea::make('homepage_destinations_lede')
                            ->label('Destinations — Lede')
                            ->rows(2)
                            ->maxLength(180)
                            ->placeholder('From the Kathmandu Valley to the roof of the world — five countries, one standard of care.')
                            ->columnSpanFull(),
                        Toggle::make('homepage_destinations_visible')
                            ->label('Show Destinations section')
                            ->inline(false)
                            ->default(true),

                        TextInput::make('homepage_packages_eyebrow')
                            ->label('Packages — Eyebrow')
                            ->maxLength(40)
                            ->placeholder('Signature programs'),
                        TextInput::make('homepage_packages_title')
                            ->label('Packages — Title')
                            ->maxLength(60)
                            ->placeholder('AST Special Package Program'),
                        Textarea::make('homepage_packages_lede')
                            ->label('Packages — Lede')
                            ->rows(2)
                            ->maxLength(180)
                            ->placeholder('Optional lede line (leave empty to hide)')
                            ->columnSpanFull(),
                        Toggle::make('homepage_packages_visible')
                            ->label('Show Packages section')
                            ->inline(false)
                            ->default(true),

                        TextInput::make('homepage_gallery_eyebrow')
                            ->label('Gallery — Eyebrow')
                            ->maxLength(40)
                            ->placeholder('Moments'),
                        TextInput::make('homepage_gallery_title')
                            ->label('Gallery — Title')
                            ->maxLength(60)
                            ->placeholder('AST Photo Gallery'),
                        Textarea::make('homepage_gallery_lede')
                            ->label('Gallery — Lede')
                            ->rows(2)
                            ->maxLength(180)
                            ->placeholder('Optional lede')
                            ->columnSpanFull(),
                        Toggle::make('homepage_gallery_visible')
                            ->label('Show Gallery section')
                            ->inline(false)
                            ->default(true),

                        TextInput::make('homepage_why_eyebrow')
                            ->label('Why Choose — Eyebrow')
                            ->maxLength(40)
                            ->placeholder('About us'),
                        TextInput::make('homepage_why_title')
                            ->label('Why Choose — Title')
                            ->maxLength(60)
                            ->placeholder('Why Choose AST?'),
                        Textarea::make('homepage_why_lede')
                            ->label('Why Choose — Lede')
                            ->rows(3)
                            ->maxLength(200)
                            ->placeholder('Adventure Specialist Travel is very concerned about your comfort…')
                            ->columnSpanFull(),
                        Toggle::make('homepage_why_visible')
                            ->label('Show Why Choose section')
                            ->inline(false)
                            ->default(true),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Footer Headings')
                    ->description('Column titles in the footer. Delete "TREKKING & ACTIVITIES" and type "ACTIVITIES" to rename. Items underneath pull automatically from Services and Destinations (first 6, hidden when 0).')
                    ->icon('heroicon-o-map')
                    ->columns(1)
                    ->schema([
                        TextInput::make('footer_services_title')
                            ->label('Trekking column — Title')
                            ->maxLength(40)
                            ->placeholder('TREKKING & ACTIVITIES')
                            ->helperText('Current shows "TREKKING & ACTIVITIES". Delete and type "ACTIVITIES". Leave empty for default.'),
                        TextInput::make('footer_destinations_title')
                            ->label('Destinations column — Title')
                            ->maxLength(40)
                            ->placeholder('DESTINATIONS')
                            ->helperText('Leave empty for default.'),
                        TextInput::make('footer_contact_title')
                            ->label('Contact column — Title')
                            ->maxLength(40)
                            ->placeholder('Contact Us')
                            ->helperText('Leave empty for default "Contact Us".'),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Branding')
                    ->description('Main navigation logo. Leave empty to use bundled public/images. White logo is for transparent hero (optional).')
                    ->schema([
                        MediaPicker::make('logo', 'Main logo (colored)')
                            ->helperText('Used in navbar (left, always visible). Falls back to /images/logo.png')
                            ->nullable()
                            ->rules(['nullable', 'string', 'max:255', 'starts_with:/']),
                        MediaPicker::make('logo_white', 'White logo (for transparent hero)')
                            ->helperText('Optional — white variant for dark hero background. Falls back to /images/logo-white.png')
                            ->nullable()
                            ->rules(['nullable', 'string', 'max:255', 'starts_with:/']),
                    ])
                    ->collapsible(),

                Section::make('Footer Contact Block')
                    ->description('Shown in the footer on every page. Empty fields are hidden.')
                    ->schema([
                        TextInput::make('contact_company')
                            ->label('Company name')
                            ->maxLength(120),
                        TextInput::make('contact_address')
                            ->label('Address')
                            ->maxLength(160),
                        TextInput::make('contact_phone_primary')
                            ->label('Primary phone')
                            ->maxLength(40),
                        TextInput::make('contact_phone_secondary')
                            ->label('Secondary phone')
                            ->maxLength(40),
                        TextInput::make('contact_phone_owner')
                            ->label('Secondary phone owner')
                            ->maxLength(80)
                            ->helperText('Name shown after the secondary number'),
                        TextInput::make('contact_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(120),
                        Textarea::make('contact_hours')
                            ->label('Opening hours')
                            ->rows(2)
                            ->helperText('One line per entry'),
                        TextInput::make('contact_facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::current()->update($data);

        Notification::make()
            ->title('Site settings saved')
            ->success()
            ->send();
    }

    private static function mediaOptionLabel(Media $media): string
    {
        $url = filled($media->url()) ? url($media->url()) : url('/images/placeholder.png');

        return '<div class="flex items-center gap-3">'
            .'<img src="'.e($url).'" alt="'.e($media->name).'" class="h-10 w-10 rounded object-cover">'
            .'<span>'.e($media->name).'</span>'
            .'</div>';
    }
}
