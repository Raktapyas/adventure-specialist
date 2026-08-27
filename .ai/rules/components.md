---
paths:
  - app/Filament/Components/MediaPicker.php
---

# Components

## MediaPicker browses without typing via options() closure
Non-relationship selects with only getSearchResultsUsing() never show results on open (hasDynamicOptions==false) so preload() is a no-op and the dropdown stays on "Start typing to search...". To make the Media library browsable, MediaPicker exposes an options() closure returning the latest 50 media (thumbnail HTML via optionLabel). That makes hasDynamicOptions true, so Choices.js fetches via getOptionsUsing() on dropdown open — users see a thumbnail list immediately; typing still filters via getSearchResultsUsing.
