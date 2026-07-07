# CETAR DESIGN SYSTEM — Build Rules

A premium SaaS style for the Bimbel Cetar CBT platform. Paste this whole file into your AI agent's system prompt / rules to reproduce the exact look.

---

## Stack
- **HTML5 + Tailwind CSS (CDN) + Alpine.js + Lucide icons**
- **Fonts:** Inter (all UI). JetBrains Mono (timers & numbers ONLY).
- Use Alpine (`x-data`) for dropdowns, tabs, and modals — never full page reloads.

## Brand feel
Premium SaaS, **maximum whitespace**, clean and borderless. Orange = every action + active state; slate/navy = structure & text; status colors are used literally (exam grid, results).

---

## Color tokens

### Primary · Orange (actions, active states, selection, links)
| Token | Hex |
|---|---|
| `primary.light` | `#FBA94C` |
| `primary` (base) | `#F5872A` |
| `primary.dark` | `#D9741A` |

### Secondary · Slate / Navy (headings, dark chrome, timer bg)
| Token | Hex |
|---|---|
| `secondary.light` | `#3A5575` |
| `secondary` (base) | `#22384D` |
| `secondary.dark` | `#16232F` |

### Status (used literally)
| Token | Hex | Meaning |
|---|---|---|
| `ok` | `#27A35A` | answered / pass |
| `warn` | `#F2C200` (dark `#C9A200`) | doubt / ragu-ragu |
| `bad` | `#E94B3C` | fail / danger |
| `gridgrey` | `#94A3AB` | unanswered |

### Neutrals & surfaces
| Token | Hex | Use |
|---|---|---|
| `surface` | `#FFFFFF` | cards, chrome |
| `surface.soft` | `#F4F6F5` | page background, inputs |
| `surface.tint` | `#FBF9F6` | nested card fill |
| `ink` | `#2B2B2B` | body text |
| `ink.muted` | `#7B8794` | helper text |
| `ink.faint` | `#9AA7AE` | placeholders, eyebrows |

### Gradients
- `.brand-grad` → `linear-gradient(100deg, #F5872A 0%, #FBB823 100%)` — ALL primary buttons & icon chips.
- `.banner-grad` → `linear-gradient(105deg, #FBE6C2 0%, #FCEFDD 55%, #FDF6EC 100%)` — warm peach hero/summary wash.

---

## Shape & elevation
- **Radius:** `rounded-lg` (controls) · `rounded-xl` (cards, buttons, inputs) · `rounded-2xl` (panels, modals) · `rounded-full` (pills, avatars).
- **Shadow:** `shadow-card` at rest → `shadow-hover` on hover. **No hard borders** — separate with `border-black/5` or shadow only.
  - `card` = `0 1px 2px rgba(16,24,40,.04), 0 1px 3px rgba(16,24,40,.06)`
  - `hover` = `0 8px 24px rgba(16,24,40,.10), 0 2px 6px rgba(16,24,40,.06)`
- **Motion:** `transition-all` + `hover:-translate-y-0.5` on cards & primary buttons (~200–300ms). Icon chips (`bg-primary/10 text-primary`) flip to `.brand-grad text-white` on `group-hover`.

## Typography
- Headings: `font-extrabold tracking-tight text-secondary`.
- Body: `text-[15px] leading-relaxed text-ink/90`.
- Eyebrows: `text-[11px] font-semibold uppercase tracking-wider text-ink-faint`.
- Timer / numbers: `font-mono font-extrabold tabular-nums`.

---

## Key patterns

**Primary button**
```html
<button class="inline-flex items-center gap-2 brand-grad text-white font-bold text-sm px-5 py-3 rounded-xl shadow-card hover:shadow-hover hover:-translate-y-0.5 transition-all">…</button>
```

**Secondary button**
```html
<button class="bg-secondary text-white font-bold text-sm px-5 py-3 rounded-xl shadow-sm hover:bg-secondary-light hover:shadow-md transition-all">…</button>
```

**Card**
```html
<div class="bg-white rounded-xl shadow-card hover:shadow-hover hover:-translate-y-0.5 transition-all p-5">…</div>
```

**Active sidebar item** — `bg-primary/10 text-primary-dark font-semibold` with a `brand-grad` icon chip. Idle items: `text-secondary hover:bg-surface-soft`, chip fills to `brand-grad` on `group-hover`.

**Answer option** — hidden radio (`sr-only`) inside a `<label>`; checked → `.brand-grad` background + white text via CSS sibling selector:
```css
.opt input:checked + .opt-body { border-color:#F5872A; background:linear-gradient(100deg,#F5872A,#FB9D3A); }
.opt input:checked + .opt-body .opt-key  { background:#fff; color:#D9741A; }
.opt input:checked + .opt-body .opt-text { color:#fff; }
```

**Exam grid cell states** — `ok` = answered · `warn` = doubt · `ring-2 ring-primary ring-offset-2` = active question · `gridgrey` = unanswered.

**Sticky timer** — `font-mono` on a `bg-secondary` pill; pulse red (`ring-2 ring-bad`) under 5 minutes.

**Alpine note** — give a toggle button `@click.stop` when it's paired with an `@click.outside` handler on the panel, or the opening click closes it immediately.

---

## Don't
- Hard 1px borders everywhere, heavy drop shadows, tight spacing.
- Non-Inter fonts (except JetBrains Mono for numbers).
- Decorative gradients outside `brand-grad` / `banner-grad`.
- Emoji as UI icons — use Lucide.

---

## Drop-in Tailwind config
```html
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">

<script>
tailwind.config = {
  theme: { extend: {
    fontFamily: {
      sans: ['Inter','ui-sans-serif','system-ui','sans-serif'],
      mono: ['JetBrains Mono','ui-monospace','monospace']
    },
    colors: {
      primary:   { light:'#FBA94C', DEFAULT:'#F5872A', dark:'#D9741A' },
      secondary: { light:'#3A5575', DEFAULT:'#22384D', dark:'#16232F' },
      ink:       { DEFAULT:'#2B2B2B', muted:'#7B8794', faint:'#9AA7AE' },
      surface:   { DEFAULT:'#FFFFFF', soft:'#F4F6F5', tint:'#FBF9F6' },
      ok:        { DEFAULT:'#27A35A', soft:'#E3F4EA' },
      bad:       { DEFAULT:'#E94B3C', soft:'#FBEAEA' },
      warn:      { DEFAULT:'#F2C200', soft:'#FCF3CF', dark:'#C9A200' },
      gridgrey:  '#94A3AB'
    },
    boxShadow: {
      card:  '0 1px 2px rgba(16,24,40,.04), 0 1px 3px rgba(16,24,40,.06)',
      hover: '0 8px 24px rgba(16,24,40,.10), 0 2px 6px rgba(16,24,40,.06)'
    }
  }}
}
</script>

<style>
  .brand-grad  { background-image: linear-gradient(100deg,#F5872A 0%,#FBB823 100%); }
  .banner-grad { background-image: linear-gradient(105deg,#FBE6C2 0%,#FCEFDD 55%,#FDF6EC 100%); }
</style>
```
