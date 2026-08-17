---
paths:
  - 'app/Filament/Resources/**/Pages/*.php'
---

# Pages

## Create/Edit pages redirect to index after save
Every Create and Edit page class (Destination, GalleryImage, Inquiry, Package, Page, Service) overrides getRedirectUrl() to return $this->getResource()::getUrl('index'), so saving/creating always lands on the resource's List page. Keep this override when adding new Create/Edit pages.
