---
paths:
  - 'app/Http/Controllers/Auth/**'
---

# Auth

## Never authenticate a user on creation
Creating a user must NEVER call Auth::login()/attempt() on that user. RegisteredUserController::store creates the user and fires the Registered event but does not log them in — new users sign in manually. This prevents an admin's session from being hijacked by a user created from the dashboard. RegistrationTest and FullFlowSessionTest assert assertGuest() after /register.
