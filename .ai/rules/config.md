---
paths:
  - config/filament-shield.php
---

# Config

## RoleResource excluded from Shield permissions; prune with permissions:prune-role
RoleResource is excluded from Shield permission generation (exclude.resources = ['RoleResource']). Role permissions (view_role, create_role, etc.) must never exist in the permissions table. Run `php artisan permissions:prune-role` to delete any that reappear. The role edit form's "Role" section is gone because FilamentShield::getResources() rejects the excluded resource.
