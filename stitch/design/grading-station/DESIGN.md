# Design System Specification: The Academic Prestige Framework

## 1. Overview & Creative North Star: "The Distinguished Scholar"
The "Distinguished Scholar" is our Creative North Star. For FLC UMJ, we are moving away from the "toy-like" aesthetics of casual gamification and instead leaning into a high-end, editorial experience. This system balances the authority of a prestigious university with the dopamine-driven engagement of modern gaming.

To break the "template" look, this design system utilizes **Intentional Asymmetry** and **Floating Depth**. We do not use rigid, boxy grids. Instead, we use overlapping elements—such as a title "breaking" the container of a card—and significant negative space to create a layout that feels curated, not generated. It is an "Academic Journal meets Premium Fintech" aesthetic.

---

## 2. Color Strategy & Tonal Architecture
The palette is built on a foundation of "University Blue," but it is applied through a lens of sophisticated layering rather than flat fills.

### The "No-Line" Rule
**Explicit Instruction:** Designers are prohibited from using 1px solid borders to section content. Boundaries must be defined solely through:
1.  **Background Color Shifts:** A `surface-container-low` section sitting on a `background` surface.
2.  **Tonal Transitions:** Utilizing subtle shifts in the Material shades to imply a change in context.

### Surface Hierarchy & Nesting
Treat the UI as physical layers of fine stationery.
*   **Base:** `background` (#f7f9fb)
*   **Sectioning:** `surface-container-low` (#f2f4f6) for large content areas.
*   **Focus:** `surface-container-lowest` (#ffffff) for primary interactive cards.
*   **Interaction:** `surface-bright` (#f7f9fb) for hover states.

### The "Glass & Gradient" Rule
To elevate the "University Blue," avoid flat hex codes for large areas. Use **Signature Textures**:
*   **Primary Hero:** A linear gradient from `primary` (#2b4bb9) to `primary_container` (#4865d3) at a 135-degree angle.
*   **Gamification Accents:** Use `secondary` (Emerald) and `tertiary` (Gold) sparingly as "jewel tones" against the neutral backdrop.
*   **Glassmorphism:** For floating navigation or achievement modals, use `surface` colors at 80% opacity with a `20px` backdrop-blur.

---

## 3. Typography: The Editorial Scale
We pair **Manrope** (Display/Headlines) with **Public Sans** (Body/Labels) to create a "Modern Academic" feel—authoritative yet highly readable.

*   **Display (Manrope):** Use for "Level Up" moments and high-impact stats. The large scale and tight letter-spacing create an editorial, magazine-like impact.
*   **Headline (Manrope):** Set in Semi-Bold. These should lead the eye through the learning modules.
*   **Title (Public Sans):** Medium weight. Used for course titles and card headers to maintain a clean, scholarly air.
*   **Body (Public Sans):** Regular weight. Designed for long-form reading of course materials.
*   **Labels (Public Sans):** All-caps with 0.05em letter spacing for metadata (e.g., "30 MINS REMAINING").

---

## 4. Elevation & Depth: Tonal Layering
We achieve hierarchy through **Tonal Layering** rather than structural lines or heavy shadows.

*   **The Layering Principle:** Place a `surface-container-lowest` (#ffffff) card on top of a `surface-container-low` (#f2f4f6) background. This creates a "soft lift" that feels natural and premium.
*   **Ambient Shadows:** For "floating" gamification elements (like a badge pop-up), use an extra-diffused shadow:
    *   `box-shadow: 0 20px 40px -10px rgba(25, 28, 30, 0.06);` (Using a tint of `on_surface`).
*   **The "Ghost Border" Fallback:** If accessibility requires a stroke, use `outline_variant` (#c3c6d7) at **15% opacity**. Never use a 100% opaque border.
*   **Glassmorphism:** Floating action buttons or headers should use a semi-transparent `surface` color to allow the "University Blue" gradients to bleed through, softening the interface.

---

## 5. Components

### Buttons & Interaction
*   **Primary:** Linear gradient (`primary` to `primary_container`), `rounded-xl`, with a subtle inner-glow on the top edge.
*   **Secondary:** `surface-container-highest` background with `on_primary_fixed_variant` text. No border.
*   **Tertiary:** Transparent background, `primary` text, underlined only on hover.

### Chips (Badges & Tags)
*   **Achievement Chips:** Use `secondary_container` (#6cf8bb) with `on_secondary_container` (#00714d) text for "Completed" states. Use `tertiary_container` for "Gold/Pro" levels.
*   **Shape:** Always `rounded-full` to contrast against the `rounded-xl` of the cards.

### Cards & Lists
*   **The No-Divider Rule:** Forbid the use of horizontal rules (`<hr>`). Separate list items using `12px` of vertical whitespace or by alternating background colors between `surface-container-low` and `surface-container-lowest`.
*   **Course Cards:** Use `rounded-2xl`. Image headers should have a subtle `20%` University Blue overlay to ensure "Academic" branding consistency.

### Input Fields
*   **Style:** Minimalist. No bottom line, no full border. Use a solid `surface-container-high` background with `rounded-lg`. On focus, transition to a `2px` "Ghost Border" using the `primary` color.

### Specialized Gamification Components
*   **Progress Orbs:** Instead of flat bars, use "Progress Halos" around user avatars using `secondary_fixed`.
*   **Level Brackets:** Use asymmetrical layouts where the "Level Number" is oversized (Display-LG) and partially overlaps the edge of its container.

---

## 6. Do's and Don'ts

### Do:
*   **Do** use extreme whitespace (e.g., 64px+ between major sections) to convey "Premium" quality.
*   **Do** use `rounded-2xl` (1.5rem) for main containers to soften the academic tone.
*   **Do** use "University Blue" as a shadow tint for primary buttons to make them "glow" rather than "drop."

### Don't:
*   **Don't** use pure black (#000000) for text. Always use `on_surface` (#191c1e).
*   **Don't** use 1px dividers. If you need a separator, use a 4px wide vertical "color pillar" in `primary` on the left side of the content.
*   **Don't** crowd the interface. If a screen feels full, move 20% of the content to a sub-page or disclosure chevron.
*   **Don't** use "standard" system fonts. Stick strictly to the Manrope/Public Sans pairing to maintain the custom editorial feel.