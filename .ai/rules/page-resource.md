---
paths:
  - 'app/Filament/Resources/PageResource/**'
---

# Page Resource

## Normalize media paths before Filament validation
Filament v3.3.54 has no mutateFormDataBeforeValidate hook; validation runs on raw state before mutateFormDataBeforeSave. To replicate legacy prepareForValidation normalization (Media::normalizePath turning absolute URLs into host-relative paths), use the field-level `->mutateStateForValidationUsing(fn ($state) => Media::normalizePath($state))` on the cover_image field (this runs during validation preparation), and also normalize in mutateFormDataBeforeSave. Do NOT use a beforeValidate() hook to set component state — it does not reliably feed validation. Otherwise starts_with:/ rejects absolute URLs.

## Audit all tests for legacy route references
When migrating a legacy feature to Filament, grep ALL tests for the legacy route name (e.g. `admin.pages`) — not just the obvious CRUD test files. AdminSeederSafetyTest::test_raw_html_round_trips_through_crud also used post('/admin/pages') and was missed in the initial audit, causing a full-suite failure. Run the full suite after migration, not just the targeted files.
