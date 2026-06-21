# 🎨 ECOZONE Premium UI Redesign — Master Prompt

> **Copy everything below and paste it into your AI coding assistant to trigger the full redesign.**

---

## PROMPT START

You are redesigning the **ECOZONE Fingerprint Attendance System** — a PHP/MySQL web application with a vanilla HTML/CSS/JS frontend. The system uses a **Digital Persona 4500** USB fingerprint scanner for biometric enrollment and verification.

The current design uses a clean "Bento" layout with emerald accents, but it looks **generic and flat**. I want you to transform it into a **world-class, premium, cinematic** interface that would rival products like Linear, Vercel, Raycast, or Apple's product pages — while keeping the exact same functionality.

---

### 🏗️ PROJECT STRUCTURE

The project has these key files — **you must redesign ALL of them**:

| File | Purpose |
|------|---------|
| `landing.html` | Marketing/product landing page. Hero section, features grid, how-it-works, hardware showcase, stats, CTA, footer. |
| `index.html` | **Public Kiosk** — A single-purpose page where employees scan their fingerprint to clock in/out. No registration, no admin features. |
| `admin.html` | **Secure Admin Panel** — Password-protected (`ecozone2026`). Contains 6 tabbed sections: Enrollment, Holiday Management, Manual Attendance, Employee Roles, Override History, Attendance Logs. |
| `assets/css/style.css` | The master stylesheet. All design tokens, variables, and component styles live here. |
| `assets/js/navbar.js` | Navbar scroll/hamburger logic (keep functional, restyle if needed). |

### 🖼️ AVAILABLE ASSETS

- `assets/images/logo.jpg` — The ZAMBOECOZONE circular logo.
- `assets/images/cinematic_scanner.png` — A cinematic render of the fingerprint scanner.
- `digital-persona-fingerprint-scanner__1_-removebg-preview.png` — A **transparent PNG cutout** of the actual Digital Persona 4500 device (silver body, blue glowing sensor, USB cable). **This is the hero product image — use it prominently.**

---

### 🎯 DESIGN SYSTEM REQUIREMENTS

#### Color Palette (Non-Generic)
Do NOT use plain emerald green (#10b981) as the only accent. Create a **rich, multi-tone palette**:

| Token | Light Mode | Dark Mode | Usage |
|-------|-----------|-----------|-------|
| `--bg-primary` | `#fafaf9` (warm off-white) | `#09090b` (true dark) | Page background |
| `--surface` | `rgba(255,255,255,0.7)` | `rgba(24,24,27,0.6)` | Card backgrounds (glassmorphism) |
| `--surface-elevated` | `rgba(255,255,255,0.9)` | `rgba(39,39,42,0.8)` | Elevated panels, modals |
| `--brand-primary` | `#059669` (deeper emerald) | `#34d399` (bright mint) | Primary actions, accents |
| `--brand-glow` | `rgba(5,150,105,0.15)` | `rgba(52,211,153,0.12)` | Glow halos, focus rings |
| `--accent-blue` | `#2563eb` | `#60a5fa` | Secondary accent (links, info badges) |
| `--accent-amber` | `#d97706` | `#fbbf24` | Warning states, special holidays |
| `--gradient-hero` | `linear-gradient(135deg, #059669 0%, #2563eb 50%, #7c3aed 100%)` | same | Hero gradient, CTA sections |
| `--border` | `rgba(0,0,0,0.06)` | `rgba(255,255,255,0.06)` | Subtle glass borders |
| `--shadow-ambient` | `0 1px 2px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.04)` | `0 1px 2px rgba(0,0,0,0.4), 0 8px 32px rgba(0,0,0,0.3)` | Multi-layer depth |

#### Typography
- **Font Family:** `'Plus Jakarta Sans'` (already loaded, weights 400–800).
- **Hero Headings:** 56–72px, weight 800, `letter-spacing: -0.04em`, `line-height: 1.0`.
- **Section Headings:** 32–40px, weight 800, `letter-spacing: -0.03em`.
- **Body Text:** 15–16px, weight 500, `line-height: 1.7`.
- **Captions/Labels:** 12–13px, weight 700, uppercase, `letter-spacing: 0.08em`.

#### Glassmorphism
ALL cards (`.section-card`, `.feat-card`, `.stat-bento`, `.login-card`, `.modal-box`, `.hand-card`) must use:
```css
background: var(--surface);
backdrop-filter: blur(20px) saturate(180%);
-webkit-backdrop-filter: blur(20px) saturate(180%);
border: 1px solid var(--border);
box-shadow: var(--shadow-ambient);
```

#### Radius System
- Cards: `24px`
- Buttons: `14px` (NOT pill/100px — use a modern squircle feel)
- Inputs: `12px`
- Small elements (badges, pills): `8px`
- Avatars/icons: `50%`

---

### ✨ ANIMATION REQUIREMENTS

#### 1. Page Load — Staggered Fade-In
Every major element should animate in on page load using CSS:
```css
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: translateY(0); }
}
.animate-in {
  animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}
/* Stagger delays */
.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }
/* ... up to delay-8 */
```
Apply these classes to hero text, cards, stats, feature items, etc.

#### 2. Scroll-Triggered Reveals
Use `IntersectionObserver` to add a `.revealed` class when elements scroll into view. Elements below the fold should start hidden and animate in.

#### 3. Hover Micro-Interactions
- **Cards:** On hover, translate up 6px, increase shadow depth, and add a subtle emerald border glow.
- **Buttons:** On hover, scale to 1.02, add a colored glow shadow matching the button color.
- **Table rows:** Smooth background color transition on hover.
- **Tabs:** Active tab gets a colored bottom bar that slides with a spring animation.

#### 4. Fingerprint Scanner Animation (Kiosk Page)
The scanner icon on `index.html` should have a **continuous idle animation** — a slow, breathing glow pulse around the fingerprint SVG. When "Scan Fingerprint" is clicked:
1. The glow intensifies (brighter, wider).
2. A conic-gradient sweep rotates around the icon (like a radar).
3. On success: the glow turns solid green, a checkmark fades in, and confetti particles or a subtle ripple effect plays.
4. On error: the glow turns red, icon shakes.

#### 5. Device Showcase (Landing Page)
The **Digital Persona 4500** device image (`digital-persona-fingerprint-scanner__1_-removebg-preview.png`) must be showcased cinematically:
- Display it floating with a subtle CSS `transform: perspective(1000px) rotateY(-8deg) rotateX(5deg)`.
- Add a soft colored ambient glow behind it (like a radial gradient halo).
- On scroll, it should **parallax** slightly (use `transform: translateY(calc(var(--scroll) * -0.1))` with a scroll listener updating `--scroll`).
- Below the device image, show a spec card grid with the hardware details:
  - **Sensor Type:** Optical
  - **Resolution:** 500 DPI
  - **Interface:** USB 2.0
  - **Dimensions:** 65mm × 36mm × 15.7mm
  - **OS Support:** Windows, Linux, macOS
  - **Certifications:** FBI PIV, ISO 19794-2

#### 6. Admin Panel Tab Transitions
When switching tabs in `admin.html`, the outgoing tab content should fade/slide out to the left, and the incoming tab content should fade/slide in from the right. Use CSS transitions (not JS animation libraries).

---

### 📄 PAGE-BY-PAGE INSTRUCTIONS

#### `landing.html` — Product Landing Page
1. **Hero Section:**
   - Left side: Headline ("Identity Secured. At the Speed of a Touch."), subtitle, two CTA buttons (primary gradient button + ghost outline button), three stat counters.
   - Right side: The **actual device image** (transparent PNG), floating with perspective transform and ambient glow.
   - Background: Subtle animated gradient mesh or noise texture overlay.

2. **About ZAMBOECOZONE Section:**
   - Keep the existing content about Zamboanga City Special Economic Zone.
   - Style as a full-width feature card with the ZAMBOECOZONE logo.

3. **Features Grid (Bento Layout):**
   - 4 feature cards in a 2-column grid with one spanning 2 columns.
   - Each card: glass background, icon with colored background circle, title with badge pill, description.
   - Cards animate in on scroll with staggered delays.

4. **How It Works — 4 Steps:**
   - Horizontal timeline with numbered steps (Scan → Extract → Match → Authorize).
   - Steps connected by a dashed line.
   - Each step card elevates on hover.

5. **Hardware Showcase Section:**
   - Two-column layout: Left = device image (the transparent PNG) with ambient glow. Right = ordered list explaining how the scanner works (optical imaging, minutiae extraction, encryption).
   - Below: Full-width cinematic render image (`cinematic_scanner.png`).

6. **Stats Section:**
   - 3 large stat cards (99.97% accuracy, 0.3s match, ISO certified).
   - Numbers should animate/count up when scrolled into view.

7. **CTA Section:**
   - Full-width card with gradient background (`--gradient-hero`).
   - Bold headline, subtitle, two buttons.
   - Decorative fingerprint watermark SVG in the corner at low opacity.

8. **Footer:**
   - Glass card with 4-column grid: Brand info, Product links, Company links, Legal links.
   - Bottom bar with copyright and certification badges.

#### `index.html` — Public Kiosk (Verification Only)
1. **Navbar:** Minimal — logo, "Home" and "Dashboard" links, live clock, theme toggle, "🔒 Admin Login" CTA.
2. **Holiday Banner:** Conditional banner at top if today is a holiday (fetched from API).
3. **Verification Card:** 
   - Center of the page, large glass card.
   - Animated fingerprint SVG icon with breathing glow.
   - "Place any finger on the scanner and click the button" instruction text.
   - Large "Scan Fingerprint" button.
   - Result message area showing employee ID, name, TIME IN/OUT badge, and timestamp.
4. **Footer:** Simple copyright line.

#### `admin.html` — Admin Panel
1. **Login Gate:** 
   - Full-height centered glass card.
   - Left: "Secure Admin Access" heading, password input (large, centered), unlock button, error message area.
   - Right: Decorative fingerprint SVG animation.

2. **Admin Dashboard (after login):**
   - **Tab Bar:** 6 tabs in a modern segmented control pill strip:
     - 👆 Enrollment | 🏖️ Holiday Management | 📋 Manual Attendance | 👤 Employee Roles | 📜 Override History | 📊 Attendance Logs
   - Tab switching should animate content transitions.

3. **Tab: Enrollment**
   - Step 1: Nickname input with icon and description.
   - Step 2: 10-finger scanning interface. Two "hand cards" (Left Hand / Right Hand) each with 5 finger circle slots. Active finger pulses with glow. Completed fingers show filled green. Skipped fingers show dashed border.
   - Progress bar and counter.
   - Scan / Skip / Back buttons.

4. **Tab: Holiday Management**
   - Add Holiday form (name, date, type dropdown).
   - Holiday table with colored type badges, edit/delete actions.
   - Inline edit mode for holidays.

5. **Tab: Manual Attendance**
   - Form: Employee dropdown, date, duty status dropdown, time in/out, reason textarea.
   - Records table with duty status badges, delete action.

6. **Tab: Employee Roles**
   - Table listing all employees with role badges (REGULAR, FIELD, DRIVER, FLEXIBLE).
   - Inline role change via dropdown.

7. **Tab: Override History**
   - Read-only table of all manual attendance entries with timestamps.

8. **Tab: Attendance Logs**
   - Filter bar: date picker + nickname search + clear button.
   - Full attendance records table with ID, nickname, date/time, status badge, holiday info, duty info.

9. **Confirm Delete Modal:**
   - Glass overlay with centered modal.
   - Warning text, Cancel + Delete buttons.

#### `assets/css/style.css` — Master Stylesheet
- Define ALL design tokens as CSS custom properties in `:root` and `[data-theme="dark"]`.
- Include the glassmorphism, animation, and typography utilities described above.
- Ensure all components are responsive (mobile breakpoint at 768px, tablet at 992px).
- Include smooth transitions on theme toggle (background, text, borders should all smoothly crossfade).
- Add a subtle background pattern or noise texture to `body` for depth.

---

### 🚫 THINGS TO AVOID
- Do NOT use TailwindCSS. Use vanilla CSS only.
- Do NOT use React, Vue, or any JS framework. Keep vanilla JS.
- Do NOT change any API endpoints, PHP backend logic, or database schema.
- Do NOT remove any existing functionality — the JavaScript logic for enrollment, verification, holidays, manual attendance, roles, and attendance logs must remain fully working.
- Do NOT use placeholder images. Use the actual device images provided.
- Do NOT use generic flat designs. Every surface should have depth through glassmorphism, shadows, and subtle gradients.
- Do NOT use pill-shaped (`border-radius: 100px`) buttons except for small badge pills. Use modern squircle radius (`14px`).

---

### ✅ DELIVERABLES
Rewrite the following files completely with the premium design applied:
1. `assets/css/style.css` — Complete new design system.
2. `landing.html` — Premium landing page with device showcase and scroll animations.
3. `index.html` — Premium kiosk page with animated scanner.
4. `admin.html` — Premium admin panel with tab transitions and glassmorphism.

Ensure all existing JavaScript functionality is preserved exactly as-is. Only the HTML structure and CSS should change.

## PROMPT END
