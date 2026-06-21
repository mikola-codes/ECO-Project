# 🎨 UI/UX Design Prompt — Biometric Fingerprint Scanning System

---

## PROJECT BRIEF

**Product:** Biometric Fingerprint Scanning System (B2B/enterprise security)
**Page Type:** Landing Page + Navbar
**Audience:** Security officers, enterprise IT buyers, government agencies
**Page's Single Job:** Convert visitors into demo-request leads by making fingerprint biometrics feel precise, trustworthy, and approachable — clean, not clinical.
**Inspiration Style:** HopeRise by Phenomenon Labs — large editorial type, generous whitespace, modern warmth. Translated here into a light-mode, bento-grid system with an emerald identity.

---

## TOKEN DESIGN SYSTEM

### Color Palette
| Role | Name | Hex |
|------|------|-----|
| Primary Background | Soft Ash | `#f3f4f6` |
| Card Surfaces | Pure White | `#ffffff` |
| Input Backgrounds | Ghost White | `#f9fafb` |
| Primary Accent / Buttons | Emerald Green | `#10b981` |
| Text — Headings | Near Black | `#111111` |
| Text — Muted / Subtitles | Warm Grey | `#6b7280` |
| Borders & Dividers | Feather Grey | `#e5e7eb` |
| Accent Hover (darkened) | Deep Emerald | `#059669` |

> **Palette rationale:** Soft ash page + pure white cards creates depth without darkness. Emerald is the single accent color — it reads as "verified," "safe," and "biometric green" without feeling clinical. This is the inverse of the dark-mode default that every competitor uses. Light = transparency, trust, nothing to hide.

---

## TYPOGRAPHY

| Role | Family | Weight / Style |
|------|--------|---------------|
| All type | **Plus Jakarta Sans** | — |
| Headings (H1–H3) | Plus Jakarta Sans | 700–800, `#111111`, -0.02em letter-spacing |
| Body / Supporting | Plus Jakarta Sans | 500, `#6b7280`, 1.6 line-height |
| Labels / Badges | Plus Jakarta Sans | 600, uppercase, 0.08em tracking, small caps feel |

> **Type rationale:** Single-family system for cohesion. Plus Jakarta Sans has enough personality in its bold weights to feel editorial (HopeRise-adjacent) while staying highly legible at small sizes. Weight alone drives hierarchy — no need for a second face.

---

## UI ELEMENT RULES

### Cards
```
background: #ffffff
border-radius: 32px
border: 1px solid #e5e7eb
box-shadow: 0 4px 24px rgba(0,0,0,0.03)
padding: 32px–40px (generous, never tight)
```
Hover state: shadow lifts to `0 8px 40px rgba(0,0,0,0.07)`, border transitions to `#10b981` at 40% opacity.

### Buttons — Primary
```
background: #10b981
color: #ffffff
border-radius: 100px  ← pill-shaped, always
padding: 14px 28px
font-weight: 600
font-size: 15px
hover: background #059669, transform: translateY(-1px)
```

### Buttons — Secondary / Ghost
```
background: transparent
border: 1.5px solid #e5e7eb
color: #111111
border-radius: 100px
hover: border-color #10b981, color #10b981
```

### Form Inputs
```
background: #f9fafb
border: 1px solid #e5e7eb
border-radius: 6px
padding: 12px 16px
font-size: 14px
color: #111111
focus: border-color #10b981, box-shadow 0 0 0 3px rgba(16,185,129,0.12)
placeholder-color: #9ca3af
```

### Bento Grid Layout
- All content sections organized in a **bento-style grid**: mixed-size white cards arranged on the ash `#f3f4f6` background — the background color acts as the "gap" between cards.
- Grid gaps: `20px` to `24px` consistently.
- Cards span varying column widths (1-col, 2-col, 3-col) to create visual rhythm and hierarchy.
- Never use full-bleed sections except the hero and CTA block.

---

## LAYOUT CONCEPT

```
┌─────────────────────────────────────┐
│  NAVBAR (sticky, white bg, border) │
│  [Logo + icon]  [Links]  [CTA pill]│
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  HERO (white card, full-width)     │
│  ┌────────────────┐ ┌────────────┐ │
│  │  H1 Headline   │ │ Fingerprint│ │
│  │  Subtext       │ │ Scan Ring  │ │
│  │  [CTA Pills]   │ │ (animated) │ │
│  │  [3 Stats]     │ │            │ │
│  └────────────────┘ └────────────┘ │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  TRUST BAR (ash bg, no card)       │
│  "Trusted by:" + muted logos       │
└─────────────────────────────────────┘

┌─── BENTO FEATURES GRID ───────────┐
│  ┌──────────┐  ┌──────────────┐  │
│  │ Feature  │  │  Feature 02  │  │
│  │   01     │  │ (wide card)  │  │
│  └──────────┘  └──────────────┘  │
│  ┌──────────────────┐  ┌──────┐  │
│  │  Feature 03      │  │  04  │  │
│  │  (wide)          │  │      │  │
│  └──────────────────┘  └──────┘  │
└───────────────────────────────────┘

┌─────────────────────────────────────┐
│  HOW IT WORKS (4-step bento row)   │
│  [Scan]→[Extract]→[Match]→[Auth]   │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  STATS BENTO (3 white cards)       │
│  [99.97%] [0.3s] [ISO 19794]       │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  CTA SECTION (emerald card)        │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  FOOTER (white card, ash bg)       │
└─────────────────────────────────────┘
```

---

## SECTION DESIGN SPECS

### 1. NAVBAR
- **Background:** `#ffffff`, `border-bottom: 1px solid #e5e7eb`
- **Sticky:** Yes. On scroll, add `box-shadow: 0 2px 12px rgba(0,0,0,0.05)`
- **Logo:** Small emerald fingerprint SVG icon + wordmark in Plus Jakarta Sans 700, `#111111`
- **Nav Links:** `Features · How It Works · Security · Pricing` — Plus Jakarta Sans 500, 14px, `#6b7280`; hover color `#111111`
- **CTA Button:** "Request Demo" — pill-shaped, emerald fill, white text
- **Max width:** `1200px` centered, generous horizontal padding (`24px` mobile, `48px` desktop)

---

### 2. HERO SECTION
- **Container:** Large white card (`border-radius: 32px`), `margin: 24px`, generous inner padding `64px`
- **Layout:** 55% text left / 45% visual right on desktop; stacks vertically on mobile
- **Headline (H1):**
  > *"Identity Secured.*
  > *At the Speed of a Touch."*
  — Plus Jakarta Sans 800, 56–72px, `#111111`, -0.03em tracking
- **Subheadline:** "Enterprise fingerprint authentication. Sub-second matching. Zero compromise." — Plus Jakarta Sans 500, 18px, `#6b7280`
- **CTA Row:** Two pills — `Request Demo` (emerald fill) + `View How It Works` (ghost outlined)
- **Stats Row:** 3 inline metrics separated by `#e5e7eb` vertical dividers
  - `99.97%` accuracy · `0.3s` match · `ISO 19794` certified
  — Numbers in Plus Jakarta Sans 700 `#111111`, labels in 500 `#6b7280`

**Hero Visual — SIGNATURE ELEMENT:**
- Circular SVG fingerprint scan ring on white/ash background
- Ridge lines in `#10b981` at 15% opacity, building to full emerald on the active arc
- Rotating emerald scan line sweeping 360° (CSS animation, 3s loop)
- After sweep completes: emerald "✓ VERIFIED" badge fades in center (loops every 4s)
- No glow — clean, precise, flat. Matches the light-mode aesthetic exactly.

---

### 3. TRUST BAR
- Sits directly on ash `#f3f4f6` background — no card wrapper
- Label: `TRUSTED BY SECURITY TEAMS WORLDWIDE` — Plus Jakarta Sans 600, 11px, uppercase, `#9ca3af`, 0.1em tracking
- Logos: grayscale, `opacity: 0.4`, spaced evenly in a horizontal row

---

### 4. BENTO FEATURES GRID
- **Layout:** Asymmetric 3-column grid on `#f3f4f6` background
- **Cards:** Pure white `#ffffff`, `border-radius: 32px`, `border: 1px solid #e5e7eb`
- **Icon:** Small emerald-filled rounded square icon (24px) per card
- **Card anatomy:** Icon → Heading (700, `#111111`) → Body (500, `#6b7280`) → optional `badge` tag in Plus Jakarta Sans 600, emerald text, emerald tint bg
- **Features:**
  1. **Liveness Detection** — Defeats spoof attacks with 3D depth sensing `[ANTI-SPOOF]`
  2. **Offline Matching** — Full on-device processing, no cloud required `[LOCAL ONLY]`
  3. **Multi-Factor Fusion** — Layer fingerprint + PIN or card `[MFA READY]`
  4. **AES-256 Encryption** — Templates never leave the chip unencrypted `[ENCRYPTED]`

---

### 5. HOW IT WORKS
- **Layout:** 4 equal white bento cards in a row, connected by small emerald `→` arrows in the gap
- Each card: step number (Plus Jakarta Sans 800, `#10b981`, large), icon, title, 1-sentence description
- Steps: **Scan → Extract → Encode → Authorize**
- Dashed emerald connector line between cards (CSS border-dashed, `#10b981` at 30%)

---

### 6. STATS / SOCIAL PROOF
- 3 white bento cards on ash background, each showcasing one key stat
- Large number: Plus Jakarta Sans 800, 48px, `#111111`
- Unit/label: Plus Jakarta Sans 600, `#10b981`
- Supporting sentence: 500 weight, `#6b7280`
- Optional: 4th wide card with a short testimonial quote — `border-left: 3px solid #10b981`

---

### 7. CTA SECTION
- Single large bento card with **emerald background** (`#10b981`), `border-radius: 32px`
- Headline in Plus Jakarta Sans 800, pure white
- Subtext in white at 80% opacity
- Two buttons: `Request Demo` (white fill, emerald text) + `View Docs` (white outline, white text)
- Optional: faint fingerprint SVG watermark in white at 6% opacity, bottom-right corner

---

### 8. FOOTER
- White card on ash background, `border-radius: 32px`
- 4-column layout: Logo+tagline · Product links · Company links · Contact
- Bottom divider: `1px solid #e5e7eb`
- Copyright row: Plus Jakarta Sans 500, 13px, `#6b7280`
- Compliance badges inline: `ISO 19794` `AES-256` `GDPR` — small pill badges, emerald tint bg

---

## SIGNATURE ELEMENT

> **The Emerald Scan Ring on a Clean White Card.** The hero fingerprint animation uses emerald — not cyan, not blue — because emerald is the system's single accent color. The ridge lines, sweep arc, and verified badge all exist within the same white card world as the rest of the UI. It doesn't feel like a "dark-mode tech demo dropped into a light page." It is native to this design. That coherence is what separates it.

---

## MOTION GUIDE

| Element | Animation | Trigger |
|--------|-----------|---------|
| Hero scan ring | Emerald arc sweep, 3s loop | On load |
| Verified badge | Fade in after sweep, 0.8s | After sweep |
| Hero text | Fade up, 40ms stagger per line | On load |
| Feature cards | Fade + subtle lift (12px → 0) | Scroll enter |
| Stats numbers | Count-up (0 → final) | Scroll enter |
| Process step cards | Sequential fade-in L→R, 80ms gap | Scroll enter |

**All animations:** `ease-out` easing, 300–500ms duration.
**`prefers-reduced-motion`:** All animations cut to instant `opacity` only.

---

## WRITING VOICE

- **Plain. Precise. Human.** Security doesn't need to be cold.
- Active verbs: *"Scans. Matches. Authorizes."*
- Avoid: "next-gen," "seamless," "cutting-edge," "robust"
- Data beats adjectives: *"0.3-second match time"* not *"blazing-fast performance"*
- Badge labels always in ALL CAPS: `[AES-256]` `[ISO 19794-2]` `[OFFLINE]`

---

## DESIGN PRINCIPLES REMINDER

1. **Ash is the canvas** — `#f3f4f6` page background is never a section background; it's the grid gap. Cards live on it, not in it.
2. **Emerald is earned** — use only for: primary buttons, scan ring, stat units, badge text, CTA card bg, connector arrows. Never as a card background outside the CTA.
3. **32px radius = the brand** — every card, every bento cell. Never use sharp corners or small radius on cards.
4. **Shadows stay ambient** — `0 4px 24px rgba(0,0,0,0.03)` is the floor. Hover = `0 8px 40px rgba(0,0,0,0.07)`. Never heavy drop shadows.
5. **Padding is respect** — minimum 32px inside every card. 40px preferred. Never let content touch a card edge.
6. **One font, two weights** — Plus Jakarta Sans 800 for headings, 500 for body. That contrast carries the entire hierarchy.

---

*Use this prompt to generate the full HTML/CSS/JS implementation. Build as if presenting to a Fortune 500 IT security director who demands clarity, precision, and zero visual noise.*
