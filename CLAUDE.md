# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A single full-screen landing page (the "Splash 3 – Horizont" splash) for the **Initiative Deutschlandtakt** verein, built to be **embedded into an existing WordPress page**, not served standalone. The WordPress page in question is `page-id-992`, running under the *Catch Box* theme. There is no build system, no package manager, no tests — the deliverable is the two source files plus the brand assets.

- `landing.html` — the markup + an inline scaling script. This is an HTML *fragment* (no `<html>`/`<head>`/`<body>`); WordPress provides the document shell.
- `landing.css` — all styling, including the IDT design-token system.
- `assets/` — brand logos (`logo-idt-transparent.png` on cream, `logo-idt-inverse.png` for the dark footer).

There is no command to build/lint/test. To preview, the two placeholders below must be resolved first (see Deploy contract), then the markup needs to be placed inside an element so the `.page-id-992 .lp-root` selectors match.

## Two hard constraints — read before editing

### 1. Everything is scoped to `.page-id-992 .lp-root`
Because the page is injected into a live WordPress theme, **no style may leak into the host page**, and the host theme must not bleed into the splash. Every rule in `landing.css` is prefixed with `.page-id-992 .lp-root` — including the reset (`box-sizing`), the element defaults (what would normally be `body`, `h1`…`h6`, `a`, `p`), and the CSS custom properties (what would normally live on `:root`). When adding any rule, keep this prefix. Do **not** reintroduce global selectors (`*`, `html`, `body`, `:root`) or browser resets — they were deliberately removed.

The one intentional exception: `@font-face` is global. It is "scoped" only by its family name (`InterVariable`), which is safe because it adds a font rather than restyling the host.

The inline `<script>` mirrors this discipline: it is wrapped in an IIFE and only queries inside `.lp-root` so nothing leaks into WordPress's global JS scope.

### 2. `__ASSET__` is a deploy-time placeholder
Asset URLs are written as `__ASSET__/logo-idt-transparent.png`. `__ASSET__` is a literal token meant to be string-replaced with the real asset base URL at deploy/embed time. Keep using `__ASSET__/<file>` for any new asset reference — do not hardcode a real path. (The Inter font currently loads from the rsms.me CDN; the comment in `landing.css` documents how to self-host it by swapping the `src` URLs to `__ASSET__/InterVariable*.woff2`.)

## The fixed-stage layout model

The visual is a fixed **1280×800** "canvas" (`.canvas`), centered in a full-viewport `.stage`. The inline script in `landing.html` computes `scale = min(vw/1280, vh/800)` on load and resize, sets it as the `--s` CSS variable on the canvas (`transform: scale(var(--s))`), and matches the footer width to the scaled stage. **Consequence:** all positions inside the canvas — the diagonal bars, the logo, the link pills — are absolute pixel coordinates in 1280×800 space. If you move or resize an element, you are working in that fixed coordinate system, and the bar geometry comments in the CSS (e.g. "Kopien der unteren Schicht ziehen 465 ab") explain the hand-computed offsets for the horizon split at `top: 465px`.

The diagonal "horizon" graphic is built from two clipped `.layer`s (top = white bars on cream, bottom = violet bars on teal `--idt-ink`), each containing the same three `.bar` spans rotated 41° but offset so they line up across the horizon cut.

## Design tokens

`landing.css` section 2 defines the full IDT brand system as custom properties on `.page-id-992 .lp-root`: brand core colors (with a CMYK/print reference set kept only as documentation), a neutral ramp, semantic aliases (`--surface`, `--text-*`, `--accent-*`), type scale, spacing (4px base), radii, shadows, motion, and z-index. Prefer these variables over literal values when extending the page.

## Language

Source comments and UI copy are in **German**. Match that when editing copy or adding comments.
