---
paths:
  - app/View/Composers/NavComposer.php
---

# Composers

## Top-level pages auto-render as navbar dropdowns
Navbar sections are auto-generated: published children of 'about' render under the About Us dropdown; every OTHER published top-level page gets its own dropdown (desktop) + drawer entry (mobile), ordered by sort_order. Excluded from auto-sections: slug 'about' (is About Us) and 'managing-director' (lives under /contact/). New pages created in Filament appear in nav automatically once published — no code changes needed.
