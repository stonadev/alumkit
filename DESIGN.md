---
name: Heritage & Horizon
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#44474e'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#74777f'
  outline-variant: '#c4c6cf'
  surface-tint: '#465f88'
  primary: '#000a1e'
  on-primary: '#ffffff'
  primary-container: '#002147'
  on-primary-container: '#708ab5'
  inverse-primary: '#aec7f6'
  secondary: '#775a19'
  on-secondary: '#ffffff'
  secondary-container: '#fed488'
  on-secondary-container: '#785a1a'
  tertiary: '#020b1b'
  on-tertiary: '#ffffff'
  tertiary-container: '#172233'
  on-tertiary-container: '#7e899e'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d6e3ff'
  primary-fixed-dim: '#aec7f6'
  on-primary-fixed: '#001b3d'
  on-primary-fixed-variant: '#2d476f'
  secondary-fixed: '#ffdea5'
  secondary-fixed-dim: '#e9c176'
  on-secondary-fixed: '#261900'
  on-secondary-fixed-variant: '#5d4201'
  tertiary-fixed: '#d8e3fa'
  tertiary-fixed-dim: '#bcc7dd'
  on-tertiary-fixed: '#111c2c'
  on-tertiary-fixed-variant: '#3c475a'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  display-lg:
    fontFamily: Source Serif 4
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Source Serif 4
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Source Serif 4
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
  headline-md:
    fontFamily: Source Serif 4
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Hanken Grotesk
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Hanken Grotesk
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-caps:
    fontFamily: Hanken Grotesk
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.1em
  button:
    fontFamily: Hanken Grotesk
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  container-max: 1280px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 48px
  stack-sm: 8px
  stack-md: 24px
  stack-lg: 48px
---

## Brand & Style

The design system is built upon the pillars of **Academic Prestige**, **Lifelong Community**, and **Professional Advancement**. It targets a multi-generational audience—from recent graduates seeking mentorship to established donors and emeritus faculty.

The visual style is **Modern Corporate with Editorial influence**. It avoids the stuffiness of traditional institutional design by utilizing expansive white space, precise grid alignment, and high-quality photography. The interface should feel like a premium digital publication: authoritative, stable, and intellectually stimulating. We use "Tonal Layering" combined with subtle "Glassmorphism" on high-level navigation to bridge the gap between traditional values and modern technology.

## Colors

The palette is anchored in tradition but optimized for digital accessibility.

- **Primary (Oxford Navy):** Used for primary navigation, headings, and core brand elements to convey stability and authority.
- **Secondary (Academy Gold):** Reserved for high-priority actions (CTAs), achievement indicators, and premium membership signifiers. This should be used sparingly to maintain its impact.
- **Tertiary (Slate Blue):** Used for secondary UI elements, iconography, and metadata to provide a softer contrast than pure black.
- **Neutral (Parchment White):** A slightly warm off-white background prevents eye strain and evokes the feel of premium stationery.

**Functional Colors:**
- **Success:** Deep Emerald (#166534) for donation confirmations and event registrations.
- **Surface-1:** Pure white (#FFFFFF) for cards and interactive containers.
- **Surface-2:** Soft Slate (#F1F5F9) for background sectioning.

## Typography

This design system utilizes a sophisticated typographic pairing to balance heritage with utility.

- **Source Serif 4** provides the "Editorial" voice. Use it for all major headings and quotes. It should always be set with slightly tighter letter spacing in large formats to maintain a premium feel.
- **Hanken Grotesk** serves as the functional workhorse. Its sharp, contemporary geometry ensures that data-heavy networking directories and job boards remain highly legible and professional.

**Usage Rules:**
- Use **Label-Caps** for category tags (e.g., "CLASS OF 1998") and overlines.
- Ensure all body text maintains a minimum contrast ratio of 4.5:1 against the background.
- For long-form alumni stories, use `body-lg` to improve readability and create a "magazine" feel.

## Layout & Spacing

The design system employs a **Fixed Content Grid** on desktop to maintain a sense of order and prestige, while transitioning to a **Fluid System** on mobile for maximum accessibility.

- **Desktop (1280px+):** 12-column grid with a 24px gutter. Large 48px margins create a "framed" effect, suggesting importance.
- **Tablet (768px - 1024px):** 8-column grid with 24px gutters.
- **Mobile (<768px):** 4-column grid with 16px gutters and margins.

**Spacing Philosophy:** Use generous vertical rhythm (`stack-lg`) between major sections to allow the content to breathe. Use tight internal spacing (`stack-sm`) for related meta-data within cards to keep professional profiles compact.

## Elevation & Depth

Hierarchy is established through **Tonal Elevation** and **Refined Shadows**.

- **Level 0 (Base):** Neutral Parchment (#F8FAFC).
- **Level 1 (Cards):** Pure White (#FFFFFF) with a very soft, diffused shadow (0px 4px 20px rgba(0, 33, 71, 0.05)). The shadow should have a slight Navy tint to keep it integrated with the brand palette.
- **Level 2 (Dropdowns/Modals):** Pure White with a more defined shadow and a 1px border of Slate (#E2E8F0) to ensure separation.
- **Interactive Depth:** When hovering over "Networking Cards," the shadow should slightly deepen, and the element should lift by 2px to provide tactile feedback. 

Avoid heavy blurs or unrealistic light sources. Depth should feel like layers of high-quality paper stacked cleanly.

## Shapes

The shape language is **Conservative and Structured**. 

- **Soft (0.25rem):** Standard for buttons, input fields, and small UI components. This provides a hint of modernity without appearing overly casual or "bubbly."
- **Rounded-lg (0.5rem):** Used for container cards and image frames to soften the overall layout.
- **Sharp (0px):** Vertical accents (e.g., color bars on the side of active navigation items) should remain sharp to emphasize the "Institutional" grid.

Imagery should predominantly use rectangular crops. Avoid circular avatars for alumni profiles; instead, use slightly rounded squares (0.5rem) to maintain a professional, LinkedIn-adjacent aesthetic.

## Components

**Buttons:**
- **Primary:** Oxford Navy background, white text. No gradient. High-contrast and authoritative.
- **Secondary:** Transparent background with an Oxford Navy 1px border.
- **Tertiary:** Academy Gold text with an underline on hover, used for "Read More" links in stories.

**Networking Cards:**
- Contain a name (Headline-MD), Class Year (Label-Caps), and a "Connect" primary button. Use Level 1 Elevation.

**Input Fields:**
- Use a 1px Slate border. On focus, the border changes to Oxford Navy with a subtle 2px outer glow in Academy Gold.

**Status Chips:**
- Used for "Donor Levels" or "Industry Tags." Use background colors from the Slate palette with Navy text to keep them subtle and professional.

**The "Heritage" Accent:**
- A 4px vertical border in Academy Gold should be applied to the left side of "Featured News" or "President's Message" to immediately draw the eye to high-prestige content.