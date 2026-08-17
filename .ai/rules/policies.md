---
paths:
  - 'app/Policies/*.php'
---

# Policies

## Merged view_any into view; never regenerate Shield policies
view_any and view are merged into a single 'view' permission token. Shield policies' viewAny() must check 'view_{resource}' (e.g. view_page), never 'view_any_{resource}'. config/filament-shield.php permission_prefixes.resource is ['view','create','update','delete']. Do NOT run shield:generate --all — it would regenerate policies from the stub and emit broken '{{ ViewAny }}' placeholders (the stub still references ViewAny/DeleteAny/etc. which no longer exist in the prefix list).
