# 🏔️ ADVENTURE SPECIALIST TRAVEL

> *"You climbed my mountains for the view. You stayed for the empire."*

This is not a website. This is the **command center of a Himalayan travel dynasty** —
every trail, every summit, every wide-eyed tourist who ever whispered *"take me there"*
passes through systems built here.

Built to dominate. Tested 275 times before it's allowed to see daylight.

---

## 💀 The Empire (What This Is)

A full-stack **Laravel + Filament** operation with two faces:

**The Public Face** — what the tourists see:
- Cinematic hero slider, driven by the database — swap the imagery without touching code
- About Us dominion with nested sub-pages and auto-generated navigation
- Services & Destinations hierarchies (Nepal gets its own subtree — obviously)
- Special Packages with duration badges and inquiry funnels
- A gallery with a full-screen lightbox (they came for photos; they stay for the rest)
- Contact form → inquiries land in *my* dashboard, throttled against spam-rats

**The War Room** (`/admin`) — what my lieutenants use:
- Filament panel managing **everything**: pages, services, destinations, packages,
  gallery, media library, hero slides, inquiries, site settings
- **Role hierarchy**: `super_admin` (me — unlimited power) and `sub-admin`
  (staff managers — content only, keys to nothing dangerous)
- Media library with usage tracking — every image knows where it's deployed
- URL history system — change a slug and old links get redirected automatically.
  *Nothing escapes. Nothing breaks.*

---

## ⚔️ The Arsenal

| Weapon | Purpose |
|--------|---------|
| PHP 8.5 / Laravel 13 | The skeleton |
| Filament 3 | The war room UI |
| MySQL 8.4 | The vault |
| Tailwind CSS + Alpine.js | The public face's good looks |
| Vite | Asset forge |
| PHPUnit | 275 tests. Zero mercy. |

---


## 🔐 The Secrets (Environment)

Every variable is documented in `.env.example`. The ones that matter most:

| Secret | Why you need it |
|--------|-----------------|
| `APP_KEY` | Encrypts everything worth encrypting |
| `DB_*` | The vault connection |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Creates the first overlord on fresh soil |
| `MAIL_*` | So password-reset ravens actually fly |

---

## 🥊 The Gauntlet (Testing)

```bash
./vendor/bin/sail php artisan test --compact          # the full gauntlet
./vendor/bin/sail php artisan test --compact --filter=AdminPageCrudTest
```

275 tests. 1200+ assertions. Content rules, permission matrices, URL redirects,
media tracking, query-count regressions — all enforced. Break something and the
gauntlet will find you.

---


## 📜 Final Words

*Most travel agencies have a website.*
*This one has a fortress.*

— *The mountain always wins.*

 ...raktapyas...
