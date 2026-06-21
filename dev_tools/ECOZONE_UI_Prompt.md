# ECOZONE Attendance System — Enterprise UI/UX Prompt

> **For use with:** Claude, Cursor, GitHub Copilot, v0.dev, or any AI coding tool.
> Paste this entire document as your prompt to generate the full enterprise-level UI.

---

## Role

You are a senior UI/UX designer and frontend engineer at an enterprise software studio. Redesign the ECOZONE Biometric Attendance System to feel like a Fortune 500 security product — on the same visual tier as Palantir, CrowdStrike, or Verkada dashboards.

The system is used by barangay admins and enterprise HR officers. It must communicate authority, precision, and trust at a glance. Every pixel should feel intentional.

---

## Color System

Use exactly these tokens as CSS custom properties. No hardcoded hex values in components.

```css
/* Brand */
--color-emerald-500: #10b981;   /* primary action, active states */
--color-emerald-400: #34d399;   /* hover glow, highlights */
--color-emerald-900: #064e3b;   /* deep accent backgrounds */
--color-emerald-950: #022c22;   /* darkest emerald surface */

/* Backgrounds */
--color-bg-base:     #0b0f0e;   /* main app background */
--color-bg-surface:  #111815;   /* cards, panels */
--color-bg-elevated: #182420;   /* modals, dropdowns */
--color-bg-overlay:  #1e2d28;   /* hover states, bento cells */

/* Text */
--color-text-primary:   #f0fdf8;  /* headings, labels */
--color-text-secondary: #6ee7b7;  /* muted emerald text */
--color-text-muted:     #4b5563;  /* disabled, placeholder */

/* Borders */
--color-border-default: #1f2e29;
--color-border-active:  #10b981;
--color-border-glow:    rgba(16, 185, 129, 0.35);

/* Status */
--color-success: #10b981;
--color-warning: #f59e0b;
--color-danger:  #ef4444;
--color-info:    #3b82f6;
```

---

## Typography

```
Display / headings:  "Inter" — weight 600–700, letter-spacing -0.03em
Body / UI labels:    "Inter" — weight 400–500
Monospace / data:    "JetBrains Mono" — used for timestamps, fingerprint IDs,
                     hex codes, and all status readouts

Type scale:
  --text-xs:   11px   badges, captions
  --text-sm:   13px   table cells, labels
  --text-base: 15px   body
  --text-lg:   18px   card titles
  --text-xl:   24px   section headers
  --text-2xl:  32px   dashboard hero stat
  --text-3xl:  48px   live clock display
```

Load both fonts from Google Fonts:
`https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap`

---

## Layout — Bento Grid System

Use CSS Grid bento layout across all pages. Cards snap to a 12-column grid. Use intentional size variation to encode importance — no equal-sized tiles.

- The fingerprint scanner zone takes **2×2 or 3×2 cells** (dominant, hero)
- Live attendance feed takes a **tall 1×3 vertical column** on the right
- Stat cards (Present, Absent, Late) are **compact 1×1 cells**
- The 10-finger enrollment map takes a **full-width 4×2 bento cell**

**Card base style:**
```css
border-radius: 16px;
border: 1px solid var(--color-border-default);
background: var(--color-bg-surface);
padding: 24px;
transition: border-color 200ms ease;

/* Hover state */
&:hover {
  border-color: var(--color-border-glow);
}
```

---

## Animations & Motion

> All animations must use `transform` and `opacity` only — never animate `width`, `height`, `top`, or `left` (they cause layout repaints and drop below 60fps).
> Always wrap decorative animations in `@media (prefers-reduced-motion: reduce)`.

### 1. Page Load Sequence — staggered orchestration

```
Navbar:       translateY(-20px) + opacity 0  →  0 + opacity 1
              400ms ease-out

Bento cards:  translateY(24px) + opacity 0  →  0 + opacity 1
              500ms ease-out, 60ms stagger between each card

Hero scanner: scale(0.97) + opacity 0  →  scale(1) + opacity 1
              600ms ease-out, enters last

Stat numbers: count up from 0 → real value on load
              800ms ease-out using requestAnimationFrame (not setInterval)
```

### 2. Fingerprint Scanner Animation — signature element

This is the most important animation in the system. Make it exceptional.

```
IDLE STATE
  Soft pulsing emerald ring around the scanner oval:
  box-shadow: 0 0 0 0 rgba(16,185,129,0.4) → 0 0 0 20px rgba(16,185,129,0)
  Loop every 2s with ease-in-out
  Keyframe name: scannerPulse

SCANNING STATE
  Ring pulses faster (1s loop)
  An animated horizontal scan line sweeps top → bottom inside the scanner area
  Implemented as a linear-gradient moving with CSS animation, 1.2s, loops 3 times

SUCCESS STATE
  Ring flashes bright emerald (#10b981)
  Card does a subtle scale(1.05) bounce, then returns to scale(1)
  Green checkmark icon fades in: scale(0 → 1) + opacity(0 → 1), 300ms
  Status text types itself character by character

DUPLICATE / REJECT STATE
  Card shakes horizontally: translateX(-8px → 8px → 0), 3 cycles, 400ms total
  Ring flashes red (#ef4444)
  Status text appears in --color-danger

All state transitions: 300ms ease-in-out
```

### 3. 10-Finger Enrollment Map

```
Show both hands as SVG illustrations (left and right outlines).
Each finger is a clickable SVG path with 4 states:

  Pending:   gray fill, no animation
  Active:    emerald pulsing fill + scale(1.1) + glow drop-shadow
  Enrolled:  solid emerald fill + checkmark badge overlay
  Skipped:   muted amber fill + dash icon overlay

On finger completion:
  Emerald wash fills from fingertip → base using clip-path animation, 600ms

Progress bar below both hands:
  Shows enrolled count (e.g. "7 / 10 fingers enrolled")
  Bar width transitions smoothly as each finger is completed
```

### 4. Attendance Table — live rows

```
New rows entering the table:
  translateX(40px) + opacity 0  →  translateX(0) + opacity 1
  300ms ease-out
  Never re-animate existing rows on data refresh

TIME IN badge:    emerald pill, soft glow pulse (1 cycle only on entry)
TIME OUT badge:   blue pill, same entry animation
Late entries:     amber badge, subtle warning icon shake (1 cycle on entry)

Row hover:
  background → var(--color-bg-overlay), 150ms
  emerald left border accent: scaleY(0 → 1), 150ms
```

### 5. Stat Cards

```
On load: numbers count up using requestAnimationFrame for smooth 60fps animation
         Duration: 800ms

Icon float animation (subtle, infinite):
  translateY(0px) → translateY(-4px) → translateY(0px)
  3s ease-in-out, infinite
  Staggered per card: card 1 = 0s delay, card 2 = 1s, card 3 = 2s
```

### 6. Password Gate (admin.html / holidays.html)

```
Overlay:      full-screen, backdrop-filter: blur(8px), rgba(0,0,0,0.7)

Wrong password:
  Lock icon rotates 20deg and springs back, 300ms
  Input field: border flashes red + shake animation

Correct password:
  Overlay scales up (scale 1 → 1.05) and fades out (opacity 1 → 0), 400ms
  Page content reveals beneath it
  Input field: border turns emerald + unlock icon animates in
```

### 7. Navbar — shared across all pages

```
Style:
  position: sticky, top: 0
  backdrop-filter: blur(16px)
  background: rgba(11,15,14,0.85)
  border-bottom: 1px solid var(--color-border-default)

Page load:    translateY(-20px) → 0, opacity 0 → 1, 400ms ease-out

Active link indicator:
  A small emerald underline that slides between nav links
  Implemented with position: absolute + left/width transition
  300ms cubic-bezier(0.4, 0, 0.2, 1)
  Never jumps — always slides

Link hover:   background pill fades in, 150ms ease
Scroll > 50px: navbar height compresses 64px → 52px, shadow appears, 300ms

Mobile:
  Hamburger icon → animates into X on open
  Mobile menu expands with max-height transition, 350ms ease-in-out
```

### 8. Toast Notifications

```
Enter: translateX(120%) → translateX(0)
       350ms cubic-bezier(0.34, 1.56, 0.64, 1)   ← slight spring overshoot

Auto-dismiss after 4s
Progress bar drains left → right under the toast during those 4s

Exit: translateX(120%), 250ms ease-in

Colors:
  Success  →  emerald (#10b981 bg tint, #10b981 border)
  Error    →  red     (#ef4444 bg tint, #ef4444 border)
  Warning  →  amber   (#f59e0b bg tint, #f59e0b border)
  Info     →  blue    (#3b82f6 bg tint, #3b82f6 border)
```

---

## Component Specifications

### Buttons

```css
/* Primary */
background: var(--color-emerald-500);
color: white;
border-radius: 8px;
padding: 10px 20px;
font-size: 14px;
font-weight: 500;
border: none;
cursor: pointer;
transition: background 150ms ease, box-shadow 150ms ease, transform 150ms ease;

&:hover {
  background: var(--color-emerald-400);
  box-shadow: 0 0 16px rgba(16, 185, 129, 0.4);
}
&:active { transform: scale(0.97); }

/* Secondary */
background: transparent;
border: 1px solid var(--color-emerald-500);
color: var(--color-emerald-400);
&:hover { background: rgba(16, 185, 129, 0.08); }

/* Danger */
background: transparent;
border: 1px solid var(--color-danger);
color: var(--color-danger);
&:hover { background: rgba(239, 68, 68, 0.08); }
```

### Status Badges / Pills

```css
height: 22px;
font-size: 11px;
font-family: var(--font-mono);
font-weight: 500;
border-radius: 100px;
letter-spacing: 0.04em;
text-transform: uppercase;
padding: 0 10px;

/* Color pattern: 15% opacity background + full opacity text + matching border */
/* Example — Time In (success): */
background: rgba(16, 185, 129, 0.15);
color: var(--color-success);
border: 1px solid rgba(16, 185, 129, 0.3);
```

### Input Fields

```css
background: var(--color-bg-elevated);
border: 1px solid var(--color-border-default);
border-radius: 8px;
padding: 10px 14px;
color: var(--color-text-primary);
font-size: 14px;
font-family: var(--font-sans);
outline: none;
transition: border-color 150ms ease, box-shadow 150ms ease;

&:focus {
  border-color: var(--color-emerald-500);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

&::placeholder { color: var(--color-text-muted); }
```

### Data Table

```css
/* Header */
thead th {
  background: var(--color-bg-elevated);
  color: var(--color-text-secondary);
  font-size: 11px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 12px 16px;
}

/* Rows */
tbody td {
  border-bottom: 1px solid var(--color-border-default);
  padding: 14px 16px;
  font-size: 14px;
  color: var(--color-text-primary);
  transition: background 150ms ease;
}

/* Use JetBrains Mono for timestamp and ID columns */
td.timestamp, td.id {
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  color: var(--color-text-secondary);
}
```

### Modals

```css
/* Backdrop */
position: fixed;
inset: 0;
background: rgba(0, 0, 0, 0.6);
backdrop-filter: blur(4px);
display: flex;
align-items: center;
justify-content: center;
z-index: 200;

/* Panel */
background: var(--color-bg-elevated);
border: 1px solid var(--color-border-default);
border-radius: 16px;
max-width: 480px;
width: 100%;
padding: 28px;

/* Entry animation */
transform: scale(0.95);
opacity: 0;
→ scale(1) + opacity 1, 300ms ease-out

/* Exit animation */
→ scale(0.95) + opacity 0, 200ms ease-in
```

---

## Page-by-Page Instructions

### `index.html` — Dashboard (primary control center)

```
LAYOUT (bento grid, 12 columns):

┌─────────────────────┬──────────────┐
│  STAT   STAT  STAT  │  LIVE FEED   │
├──────────┬──────────┤  (scrollable │
│          │  10-FIN- │   tall col)  │
│ SCANNER  │  GER MAP │              │
│  HERO    │          │              │
│  CELL    │          │              │
└──────────┴──────────┴──────────────┘

Scanner hero cell: dark oval, animated scan beam, emerald glow ring,
live status readout in JetBrains Mono.
This card IS the identity of the system — make it dramatic.

Stat cards: Present Today, Absent, Late — numbers animate up on load.
Each stat has a relevant icon with a floating animation.

10-finger SVG map: both hand outlines, each finger is a colored state path.
Progress bar beneath it.

Live attendance feed right column: auto-refreshes every 10 seconds.
New rows slide in from the right. Shows last 20 records.
```

### `attendance.html` — Full Attendance Log

```
LAYOUT:

┌─────────────────────────────────┐
│  LIVE CLOCK (monospace, large)  │  ← updates every second
├───────┬─────────────────────────┤
│ FILTER│  FULL-WIDTH DATA TABLE  │
│ PANEL │  (sticky header)        │
│       │                         │
└───────┴─────────────────────────┘

Left sidebar: filter by date, by status (Time In / Time Out / Late),
by employee name. Filters animate the table rows out and in.

Top right: Export CSV button with download icon.
Table columns: Employee Name | Finger Used | Status | Time | Date
```

### `admin.html` — Employee Management

```
Protected by full-screen password gate overlay (blur backdrop).

Employee management table:
  Columns: Name | Enrolled Fingers | Role | Last Seen | Actions

Enrolled Fingers: shown as 10 small dots (● = enrolled, ○ = not enrolled)
rendered in a single row per employee — at a glance you see coverage.

Role badge: pill component (Admin / Staff / Guest)

Actions: Edit icon and Delete icon per row.
Delete opens a confirmation modal with danger styling.
Edit opens a slide-in drawer panel from the right (not a modal).
```

### `holidays.html` — Holiday Configuration

```
Protected by password gate.

Calendar grid view showing the current month.
Holidays are highlighted in emerald with a label.
Non-holiday days are dark and minimal.

Add holiday: opens a slide-in drawer from the right (300ms ease-out).
  Drawer contains: date picker, holiday name input, type selector (Regular / Special).

Month navigation: prev/next buttons animate the calendar with a slide
  (translateX(40px) → 0 when going forward, translateX(-40px) → 0 going back).
```

---

## Technical Rules

```
CSS
  All colors via custom properties — zero hardcoded hex values in components
  Use CSS Grid for all page layouts
  Use Flexbox only for alignment within cells
  border-radius: 16px for cards, 8px for inputs/buttons, 100px for pills

ANIMATIONS
  Only animate: transform, opacity, box-shadow
  Never animate: width, height, top, left, margin, padding
  Always include reduced motion support:

  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation-duration: 0.01ms !important;
      transition-duration: 0.01ms !important;
    }
  }

JAVASCRIPT
  Vanilla JS only — no jQuery
  Use IntersectionObserver for scroll-triggered card reveal animations
  Use requestAnimationFrame for all counting / number animations
  Auto-refresh attendance table every 10s using setInterval + fetch

BOOTSTRAP 5
  Use Bootstrap 5 for grid utilities and modal scaffolding only
  Override ALL Bootstrap default colors with your CSS custom properties
  Do not use Bootstrap's default blue/gray/white palette anywhere

FONTS
  Inter + JetBrains Mono loaded from Google Fonts
  Set font-family on :root, not on body (avoids specificity issues)

NAVBAR
  Identical markup on every page — treat it as a shared component
  Sticky, frosted glass, consistent animations across all 4 pages
  Active page link always highlighted with the sliding emerald indicator

CONSISTENCY
  Every card, badge, button, and input is styled from the same token system
  No one-off colors or font sizes outside the defined scale
  Page transitions keep the navbar static — only content below it changes
```

---

## Quick Reference — Animation Timing Cheatsheet

| Element | Duration | Easing |
|---|---|---|
| Page load nav slide | 400ms | ease-out |
| Bento card stagger | 500ms + 60ms delay | ease-out |
| Scanner ring pulse | 2000ms loop | ease-in-out |
| Scanner state change | 300ms | ease-in-out |
| Finger fill animation | 600ms | ease-out |
| Table row enter | 300ms | ease-out |
| Toast enter | 350ms | cubic-bezier(0.34,1.56,0.64,1) |
| Toast exit | 250ms | ease-in |
| Modal enter | 300ms | ease-out |
| Modal exit | 200ms | ease-in |
| Navbar compress on scroll | 300ms | ease |
| Active link indicator slide | 300ms | cubic-bezier(0.4,0,0.2,1) |
| Stat number count-up | 800ms | ease-out |
| Theme / color transitions | 300ms | ease |

---

*ECOZONE Attendance System — UI/UX Prompt v1.0*
*Stack: HTML5 · CSS3 · Vanilla JS · Bootstrap 5 (utilities only) · PHP backend · DigitalPersona U.are.U SDK*
