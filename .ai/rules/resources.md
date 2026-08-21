---
paths:
  - app/Filament/Resources/PageResource.php
  - 'app/Filament/Resources/**'
  - app/Filament/Resources/GalleryImageResource.php
  - app/Filament/Resources/ServiceResource.php
---

# Resources

## cover_image normalization runs after validation in Filament v3.3.54
In Filament v3.3.54 there is no mutateFormDataBeforeValidate hook; CreateRecord::create()/EditRecord::save() validate BEFORE mutateFormDataBeforeSave. So cover_image normalization (Media::normalizePath) in subtask 02's mutateFormDataBeforeSave runs AFTER the starts_with:/ and not_regex rules see the raw value. The form field carries a comment noting this; do not move normalization into the field's rules.

## is_published is nullable, not required
is_published must be nullable (not required) with default(false), matching legacy StorePageRequest/UpdatePageRequest where it was ['boolean'] and defaulted to false when absent. Making it required breaks tests that create pages without passing is_published.

## FileUpload cover_image Option C wiring
FileUpload's afterStateHydrated is a SINGLE closure — calling ->afterStateHydrated() overwrites BaseFileUpload's default hydration (which maps state to [uuid=>file] and filters files missing from the disk). To preview web-path values (/storage/...), override afterStateHydrated to strip the /storage/ prefix AND replicate the uuid mapping, but do NOT filter by disk existence or legacy /assets/... values get wiped on save. Do NOT add mutateStateForValidationUsing(Media::normalizePath) or mediaPathRules (startsWith:/ etc.) to a FileUpload field — its validation state is an array, not a string, so trim()/startsWith crash or always fail. Translate disk-relative state to /storage/... web paths in mutateFormDataBeforeCreate/mutateFormDataBeforeSave instead.

## ImageColumn::make('url') conflicts with Media::url() method
In Filament v3, ImageColumn::make('url') on the Media model throws "App\Models\Media::url must return a relationship instance" because Eloquent resolves the column name as a relationship (Media::url() returns a string). Fix: use ->getStateUsing(fn (Media $record) => filled($record->url()) ? url($record->url()) : null) so the state is an absolute URL and getImageUrl() returns it as-is (host-relative paths like /assets/... would otherwise be mangled into /storage/... paths).

## GalleryImage image_url is host-relative; don't prepend storage/
gallery_images.image_url stores a HOST-RELATIVE web path (e.g. /assets/images/foo.jpg or /storage/foo.jpg), not a disk-relative path. When rendering the ImageColumn thumbnail, resolve with url($record->image_url) — do NOT prepend 'storage/' (that would produce /storage//assets/... broken URLs). The form field already normalizes to host-relative via Media::normalizePath + starts_with:/ validation.

## GalleryImage image_url Select: value=path, keep normalize+rules
The image_url Select pulls from the Media table but must keep the legacy TextInput validation behavior: ->mutateStateForValidationUsing(Media::normalizePath) + rules (starts_with:/, not_regex://, not_regex:/../). Option VALUE is the media path (pluck('name','path')), label is the name — never pluck('name','name') or image_url would store a filename instead of a host-relative path. getOptionLabelUsing resolves name from path with ?? $value fallback for legacy rows without a Media match.

## Slug auto-generates from title via afterStateUpdated
All resources with a slug field (Destination, Package, Page, Service) auto-generate the slug from the title using the title field's ->live(onBlur: true) + ->afterStateUpdated hook with Str::slug. Use the "smart" pattern: only overwrite the slug when it still equals Str::slug($old title) — this preserves manually customized slugs (important because slug changes create redirects).

## GalleryImage picker uses allowHtml thumbnails
The image_url Select uses ->allowHtml() with a private static mediaOptionLabel() helper that renders a thumbnail <img> + escaped name. Always escape (e()) names/paths in the HTML labels — allowHtml() skips escaping. In tests, getFormSelectSearchResults returns options transformed to [['label'=>..., 'value'=>..., 'disabled'=>false]] (not a key=>label map).

## Service icon is a curated heroicon name stored in services.icon
services.icon stores a heroicon name (e.g. heroicon-o-paper-airplane) chosen from ServiceResource::iconOptions(), NOT an image path. Render it in the card with {{ svg($service->icon ?: 'heroicon-o-photo', 'h-5 w-5') }}. The BladeUI Svg object is Htmlable — never cast to string; use the svg() helper or @svg directive so e()/Blade renders toHtml(). Keep iconOptions() in sync with the card's default heroicon-o-photo fallback.
