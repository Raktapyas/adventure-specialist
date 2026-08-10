# Phase 2A — Reference Design Analysis Report

**Reference:** `techno-main` (Techno – IT Solutions & Technology, WordPress + Elementor)
**Target:** Adventure Specialist Travel (Laravel 13 + Tailwind v4 + Alpine)
**Status:** Inspection-only. No files were modified during analysis.

---

## 1. Reference framework

WordPress export. Not a JS-framework SPA — a server-rendered homepage (`index.html`, 3083
lines) built with **Elementor** (sections/columns/widgets, 12-col grid) plus the "DreamIT
Solution" custom Elementor extension (flip-box, section-title, team, counter, case-study,
testimonial widgets). Styling is classic multi-file CSS with CSS custom properties;
behavior is jQuery + GSAP.

## 2. Dependencies (reference vs. current project)

| Concern | Reference (techno-main) | Adventure Specialist (current) |
|---|---|---|
| Slider | **Revolution Slider sr7** v6.7.27 (full-width bg slider, layered text) | None (static hero image) |
| Scroll lib | **GSAP 3.13.0** + ScrollTrigger + SplitText (local files present) | IntersectionObserver (`.reveal`) in `app.js` |
| Carousels | Owl Carousel 2 + Slick (jQuery) | None |
| Menu | Max Mega Menu (`hover_intent`, `fade_up` 200ms) | Alpine `@mouseenter/@mouseleave` dropdowns |
| Popups | venobox (YouTube modal), Magnific Popup | None |
| Counters | jquery.counterup + waypoints/appear | None |
| Loader | **LoftLoader** (`pl-beating` curtain loader) | None |
| Forms | Contact Form 7 + Mailchimp (mc4wp) | Laravel POST form (`contact.store`) |
| Icons | flaticon + Font Awesome 5 + Themify | inline SVG |
| Chart | Chart.js 3.6.0 (loaded, homepage usage unclear) | None |

## 3. Design system

- Radius: 5px (buttons, cards, team, case-study) / 8px (blog cards). Current project uses
  `rounded-sm` (≈2px).
- Shadows: home service flip-cards `0 5px 20px rgba(0,0,0,.1)`; sticky header
  `0 0 3px rgba(0,0,0,.10)`; blog cards `1px solid rgba(29,33,36,.12)` border.
- Section rhythm: Elementor sections 100–120px vertical padding; a signature
  `margin-top:-121px` lets content **overlap the hero** (post-9.css `81f1998`).
- Section heading block (`section-title.style1`): uppercase eyebrow
  (16px/700, letter-spacing 5px, primary color) + two-line title (39px/800) with accent
  `span` + animated divider bar (`bar-main`: 5px×90px rounded `#aec6ef` with a 10px dot
  drifting across, 3s linear loop).

## 4. Typography

- **Mulish** (Google Fonts) — the site font, weights 300/400/500/600/700/800/900. Body
  default from `tpt` config: Mulish 500/600/800. Elementor local stack Roboto + Roboto
  Slab used in widget defaults.
- Current project uses **Instrument Sans** (sans) + **Fraunces** (serif). A redesign would
  swap to Mulish as the primary face; the serif accent role can be dropped or kept.

## 5. Color palette

- CSS vars inline in `index.html`: `--dream-color-primary:#0c5adb` (blue), `#232323`
  (dark text), `#fff`.
- Button gradient `#2475FC → #1129B9` (hover overlay `#171717`), secondary/accent
  gradient `#46FEC0 → #CAFF5C`.
- Team image overlay `rgba(12,90,219,.85)`; blog category pill solid primary.
- Current palette is warm editorial (`pine/paper/ink/moss/bronze`) — **entirely
  different** from reference's corporate blue. Palette decision is a Phase 2B choice;
  reference blue `#0c5adb` is the anchor.

## 6. Layout / grid

- Elementor 12-col grid: `elementor-col-25` (4-col rows of flip-boxes/icon-boxes),
  `elementor-col-33` (team), `col-50` (about). `.elementor-container` max-width 1320px,
  inner Bootstrap `.container` 1200px. Gaps via `elementor-column-gap-default`.
- Current project: Tailwind `max-w-7xl` + 12-col `lg:grid-cols-12`. Compatible; a 4-across
  service grid maps to `lg:grid-cols-4`.

## 7. Header / nav behavior

- Transparent-over-hero → white sticky on scroll (`scroll-to-fixed-fixed` adds shadow).
  Top contact strip above nav (phone/email/Facebook), hidden on scroll.
- Desktop: 100px row, links 16px/600 `#232323`, hover/current → primary blue,
  `margin:35px 18px`; mega menu **fade_up 200ms** on hover-intent.
- Mobile (≤768): slide-right panel with animated hamburger (`toggle-animated`),
  meanmenu-driven.
- Current navbar is already fixed + scrolled-state + mobile drawer — structure matches;
  needs the mega-menu fade-up feel and contact strip polish.

## 8. Hero behavior

- Full-width Revolution Slider, `fullWidth:true, fullHeight:false`, responsive height grid
  `[900,900,768,584,720]`, container width `[1240,1240,1024,778,480]`.
- 3 slides; each = bg image (slider11.jpg / slider21.png) + staggered `sr7-txt` layers
  (`Total IT Solution`, `Best IT solution agency for your Business`, `How IT Works`,
  `IT Services`) + ripple video play button (venobox YouTube modal, `data-autoplay`).
- Slide transitions (zoom / rotating blur) + layer entrance choreography are driven by the
  **remote sr7 runtime** (`sr7.js`/`tptools.js` CDN) not in the export — only the generated
  HTML/CSS and `SR7.PMH` config are local. Layer keyframes inferable: opacity/translate/
  scale entrances, Back easing, ~0.6–1s.
- Current hero is a static full-height image + fade-up text. A redesign should replicate
  the layered text entrance + full-bleed image, ideally with a lightweight
  IntersectionObserver/GSAP-equivalent stagger rather than a slider library.

## 9. Component inventory (homepage sections, top→bottom)

1. Hero slider (Revolution) + ripple video
2. IT services — **flip-box grid** (4-up)
3. About + image + video popup (slick thumbnail)
4. Services heading (large title) + 3-up cards
5. Team member (3-up, social slide-in)
6. Features / **case-study cards** (owl carousel, 2-up) — absolute card rising from image
   bottom
7. Call Us CTA + **animated counters** (15/1280/12…)
8. Happy Clients testimonials (owl, 3-up, quote + avatar)
9. Get Quote — CF7 form (Name/Email/Subject/Message grid)
10. Latest Article — blog cards (3-up: category pill, title, meta author/date)
11. Newsletter subscribe + **footer** (4 cols: brand+social, Quick Links, Popular Post,
    contact)

## 10. Animation inventory (verified from code)

**Scroll-triggered (theme.js + GSAP):**
- `.text-anime-3` section headings → SplitText chars, start opacity 0 + x:50 → to
  x:0/rotateX:0/opacity:1, `duration:1`, `Back.easeOut`, `stagger:0.02`, trigger `top 90%`.
- `.title-anim` → SplitText words/lines, `from` lines `{rotationX:-80,
  transformOrigin:"top center -50", opacity:0}`, stagger 0.1, trigger `top 90%`.
- `.reveal` images → image-mask slide (container `xPercent:-100`, img `xPercent:100`,
  1s Power2.out, `toggleActions play`).
- `.fade` → y:10→0 + opacity, `power2.out`, 1s, trigger `top 90%`, `toggleActions play
  none none reverse`.
- Counters → jQuery animate 2000ms swing, fired once when `.counter` enters viewport
  (inline scroll handler).

**On-load (Elementor):** `elementor-invisible` + `data-settings._animation`
(fadeInUp / fadeInDown / fadeInLeft / slideInLeft) with per-element `_animation_delay`.

**Ambient loops:** `@keyframes ripple-white` (1s linear infinite box-shadow ripple on
video button); `alltuchtopdown` (translateY −20px, 2.5s alternate) on floating shapes;
`rotation` 20s linear (ai-animation.css); LoftLoader beat animation.

**Hover:** buttons clip-path circle wipe `0%→100%`, `#171717`,
`cubic-bezier(0,.96,.58,1.1)`, 0.8s (reverse ~4s); flip-box 3D flip `.6s
cubic-bezier(.2,.85,.4,1.275)`; team lifts −10px + blue overlay + social rail slides in;
case-study card rises `top:70%→50%` + fade; image zoom via transform.

## 11. Scroll behavior

- GSAP ScrollTrigger for all text/image reveals (`top 90%` start).
- Elementor IntersectionObserver lazy-load backgrounds (`rootMargin 200px`).
- Sticky header transitions on scroll; counters fire on viewport entry.
- Current project already has `scroll-smooth`, a `.reveal` IO pattern in `app.js`
  (`threshold 0.12, rootMargin -40px`) — this is the natural slot to host a redesign's
  scroll reveals.

## 12. Hover behavior

Catalogued above (buttons, cards, team, case-study, blog image zoom `scale(1.1)`). Note:
no magnetic buttons, no cursor effects, no parallax beyond a `parallax.min` file (usage
unconfirmed).

## 13. Responsive behavior

- Breakpoints in responsive.css: `min-width` pairs 320–575, 576–767, 768–991, 992–1169,
  1170–1365, 1200–1820 + header-specific 1025–1169.
- Hero height steps [900,900,768,584,720]; carousels collapse to 1 item (0–767),
  2 items (768–991), 3 items (992+).
- Mega menu → slide-right drawer ≤768; nav height shrinks; section paddings reduce.

## 14. Reference assets that matter

- `assets/css/widgets-style.css` (16,392 lines) — flip-box, section-title, button, team,
  counter, case-study, testimonial, blog CSS (the source of truth for card/button/section
  visuals).
- `uploads/elementor/css/post-9.css` — homepage section overrides incl. the hero-overlap
  `-121px`.
- `uploads/maxmegamenu/style.css` — nav behavior.
- `theme-d0ff6cad32.js` — the entire animation engine (SplitText reveals, counters).
- `ScrollTrigger.min`/`SplitText`/`gsap` files — the animation runtime.
- Not usable directly: Revolution Slider runtime (CDN), Elementor + WordPress markup —
  **visual patterns only**.

## 15. Current Adventure Specialist architecture (relevant facts)

- Laravel 13, PHP 8.5.8, Vite 8, **Tailwind CSS v4** (`@theme` tokens in `app.css`),
  Alpine 3.15.
- Views: `layouts/app`, components `navbar, footer, hero, service-card, package-card,
  section-heading`; pages `home, contact, gallery, managing-director, show`; sections
  services/destinations/packages.
- `app.js` has the `.reveal` IntersectionObserver + reduced-motion guard (already matches
  the reference's accessibility posture).
- Routes/canonical hierarchy, `NavComposer`, models `getPath()/resolvePath()`, SQLite seed
  data in `database/data/*.json`. Phase 1 URL work verified (83/83 direct 200s, slashless
  301s, no loops).

## 16. Components to create (for redesign)

1. `flip-card` (flip-box service card) — 3D flip front/back.
2. `countup` (animated stat) — Alpine or IO-driven counter.
3. `testimonial-card` + carousel (Alpine-based, no jQuery).
4. `case-study-card` (overlay card rising on hover).
5. `team-card` (social slide-in overlay).
6. `video-modal` (YouTube/venobox-equivalent via Alpine + iframe).
7. `icon-box` (icon + heading + text rows).
8. `blog-card` (category pill, meta, zoom image).
9. `section-heading-v2` (animated 2-line title + divider bar + eyebrow).
10. `dreamit-button` (gradient + clip-path wipe) or a Tailwind-compatible `.btn-wipe`
    class.
11. `quote-form` (Get Quote CF7-equivalent → reuse `contact.store`).
12. Hero v2 (layered text entrance + full-bleed bg + optional video trigger).
13. Page loader equivalent (optional; LoftLoader curtain).
14. `counters-cta` band (Call Us + counters).

## 17. Reusable existing components

- `navbar` (fixed/scroll-state/mobile drawer already match reference structure — add
  fade-up mega-menu feel).
- `footer` (4-column layout already matches reference column intent).
- `hero` (slot-based; extend for layered entrances).
- `section-heading`, `service-card`, `package-card` (redesign tokens, keep API/props).
- `app.js` `.reveal` IO — extend with stagger/delay + reduced-motion guard (already
  present).
- Routes/controllers/contact form — **unchanged** per Phase 2 mandate.

## 18. Dependencies possibly required (report only — do NOT install)

- **@gsap/react or gsap** — only if the exact SplitText char-choreography is required;
  otherwise IO+CSS can replicate most effects. (GSAP + ScrollTrigger + SplitText are the
  reference's core; the current project has zero animation deps beyond Alpine.)
- A slider/carousel: **Swiper** (self-contained, tree-shakeable) is the modern drop-in
  for Owl/Slick/RevSlider; or hand-rolled Alpine carousels to keep the dep footprint at
  zero.
- No icon library required (inline SVG in use); Font Awesome optional.
- **Nothing else.** No Lenis/Splide/Three/Spline/Framer in reference.

## 19. Risks

- **Hero slider fidelity:** sr7 runtime not in export — exact layer timing/easing is
  inferred. Recommendation: rebuild as CSS/IO entrance choreography, not a slider
  dependency.
- **Dependency creep:** reference is jQuery-era; replicating via GSAP is a single clean
  dep, but the current stack is dependency-light — prefer Alpine/IO + CSS where possible.
- **Theme mismatch:** reference is a corporate tech blue theme; AST is a travel brand.
  Palette/typography must be re-sited into the AST brand, not copied verbatim.
- **Accessibility:** reference relies on ScrollTrigger reveals with no reduced-motion
  fallback; current project already has the correct guard — preserve it.
- **Scope:** flip-boxes/carousels must not regress the verified 83-URL/redirect behavior —
  all changes stay in Blade/CSS/JS.

## 20. Proposed implementation order (for Phase 2B, pending approval)

1. Design tokens: swap fonts (Mulish), extend `@theme` palette (blue anchor),
   radius/shadow tokens in `app.css`.
2. `dreamit-button` + section-heading v2 (bar divider) — the two most reused primitives.
3. Hero v2 (layered entrance) + ripple video modal.
4. Flip-card service grid; case-study + team + testimonial + blog cards.
5. Counters CTA band + quote form restyle (wire to existing `contact.store`).
6. Navbar/footer polish (fade-up mega menu, contact strip).
7. Scroll reveals pass (staggered `.reveal` via existing IO, GSAP only if required).
8. Responsive sweep at the reference breakpoints; rebuild + verify no URL regressions;
   lint/typecheck.

---

**STOP.** This is the Phase 2A deliverable. No files were modified during analysis.
Awaiting review to approve the design direction (especially palette/typography + the hero
approach + the GSAP-or-not decision) before any Phase 2B implementation.
