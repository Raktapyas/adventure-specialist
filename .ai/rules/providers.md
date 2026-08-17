---
paths:
  - app/Providers/AppServiceProvider.php
---

# Providers

## Gate bypass only for master admin (user ID 1)
Gate::before returns true only when $user->getKey() === 1 (the master admin). Every other user — including is_admin=1 legacy admins and spatie role holders — must strictly pass Filament Shield policy/permission checks. When tests need an admin with full access they must create the user with id 1 (User::factory()->create(['id' => 1, 'is_admin' => true])) and reuse that single instance within a test.
