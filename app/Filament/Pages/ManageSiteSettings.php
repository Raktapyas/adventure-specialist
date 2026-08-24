<?php

namespace App\Filament\Pages;

use App\Models\Media;
use App\Models\SiteSetting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

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
