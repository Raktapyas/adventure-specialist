---
paths:
  - 'app/Filament/Resources/**/Pages/*.php'
  - 'app/Filament/Resources/*/Pages/Create*.php'
---

# Pages

## Create/Edit pages redirect to index after save
Every Create and Edit page class (Destination, GalleryImage, Inquiry, Package, Page, Service) overrides getRedirectUrl() to return $this->getResource()::getUrl('index'), so saving/creating always lands on the resource's List page. Keep this override when adding new Create/Edit pages.

## Sort order auto-assigns next value on create
All ordered resources (page, service, destination, package, gallery_image, hero_slide) use the AutoAssignsSortOrder concern in their Create pages: when sort_order is left blank, mutateFormDataBeforeCreate sets it to (max(sort_order) ?? -1)+1 (0 when empty). The form field stays nullable with placeholder "Auto — next available" and helper "Leave blank to auto-assign next position." — explicit 0 or any value is preserved. No separate sort_order migration; existing rows untouched.
