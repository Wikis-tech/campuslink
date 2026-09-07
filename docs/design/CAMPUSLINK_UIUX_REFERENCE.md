# Campus Link Phase 4.7 UI/UX Reference

This document records the design principles used for the Phase 4.7 redesign of every authenticated Campus Link experience. The public landing page is intentionally excluded.

## Sources used

1. UI UX Pro Max by Next Level Builder (`nextlevelbuilder/ui-ux-pro-max-skill`), MIT licensed.
2. Campus Market Nigeria — discovery-first campus marketplace, categories, verified vendors and direct WhatsApp connection.
3. Rakata — campus-local marketplace where listings are tied to the selected institution/campus.
4. iMart.ng — student/vendor marketplace focused on trusted listings, campus context and quick discovery.
5. EdeEki — student-first marketplace with stronger lifestyle imagery and campus-specific trust.
6. Fresha — image-led service discovery, strong provider profiles, ratings and location context.

## Product decision

Campus Link must not use the same visual model for every role.

### Student
Consumer marketplace first. The primary job is to help a student find a trusted person/service quickly.

Priority order:
1. Greeting + school context
2. Search
3. Popular service categories
4. Featured / trusted vendors
5. Saved and recently useful vendors
6. Verification state
7. Personal activity as secondary information

Avoid making the student homepage feel like an enterprise analytics dashboard.

### Vendor
Business workspace. Prioritise visibility, trust status, enquiries, saves, reviews, profile quality, portfolio and performance.

### Admin
Operations workspace. Dense information, queues, filters, tables, charts and moderation status are appropriate here.

## Visual system

- Campus navy and green remain brand anchors, but large blue surfaces should be rare on the student side.
- Warm neutral backgrounds and white content surfaces should dominate light mode.
- Dark mode uses deep slate/navy surfaces rather than pure black.
- Real vendor imagery should be preferred to decorative gradients whenever available.
- Rounded corners use a restrained radius scale; avoid making every block a floating pill/card.
- Use section rhythm, dividers, rails, imagery and typography before adding another card.
- Use Lucide icons rather than emojis for interface actions.
- Keep search highly visible throughout the student experience.

## Motion

- Motion should explain state or improve perceived quality, not decorate every element.
- Prefer opacity and transform animation.
- Hover movement should remain subtle (roughly 1–4px).
- Use slower ambient background motion only in hero/identity areas.
- Respect `prefers-reduced-motion`.
- Avoid infinite bouncing, excessive parallax and high-frequency animated gradients.

## Responsive targets

Test at minimum:
- 375px
- 768px
- 1024px
- 1440px

Student marketplace content should move from multi-column desktop grids to horizontal rails / two-column tablet / one-column mobile layouts without clipping labels or actions.

## Accessibility

- Maintain visible keyboard focus.
- Keep text contrast at WCAG-readable levels.
- Do not rely on colour alone for verification or status meaning.
- Let labels, chips and badges wrap rather than clip.
- Preserve semantic buttons/links/forms.

## Attribution

UI UX Pro Max is Copyright (c) 2024 Next Level Builder and distributed under the MIT License. Campus Link uses adapted design guidance only; the upstream repository is not bundled wholesale into the application.
