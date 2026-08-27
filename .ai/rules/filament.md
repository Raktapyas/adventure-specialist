---
paths:
  - 'app/Filament/**/*.php'
---

# Filament

## MediaPicker is the single library select; hero syncs usage
App\Filament\Components\MediaPicker::make() is the shared "choose from Media Library" select for Hero Slides (image_path) and Gallery (image_url); value = media.path, keep normalizePath + starts_with rules. Image option-label markup must stay byte-identical (tests assert exact HTML); videos render an svg play badge instead of an <img> thumb. Hero create/edit/delete now sync/purge media_usages with field 'image_path' (same contract as Gallery 'image_url'). Hero/Gallery tables use the ViewColumn media-thumb blade with ->with('media') eager loading (media() is a path-keyed hasOne); that view must read $getRecord() — Filament passes methods as closures, not $record.
