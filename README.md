# University Mumbai WordPress Practical

This submission integrates the supplied static University Website frontend with a custom WordPress Programs module.

## Structure

- `wp-content/themes/university-mumbai` – theme templates and supplied visual assets.
- `wp-content/plugins/programs-module` – Programs CPT, taxonomy, metadata, React-compatible admin editor, routing, seeder, tracking REST endpoint, and frontend tracking script.

## Setup

1. Copy `wp-content` into a WordPress installation.
2. Activate **University Mumbai** and **University Programs Module**.
3. Visit Settings → Permalinks and save once if needed.
4. Add programs manually, or visit `/wp-admin/edit.php?post_type=program&upm_seed=1&_wpnonce=...` after generating a nonce for the seeded data utility.
5. Build the admin source with `npm install && npm run build` inside `wp-content/plugins/programs-module` when Node tooling is available. The checked-in runtime admin script means the editor remains available in the provided package.

## Routes

- `/programmes`
- `/programmes/undergraduate`
- `/programmes/postgraduate`
- `/programmes/{program-slug}`

## Tracking

Tracked elements use the shared `upm_track_attr()` PHP helper. Events are sent to `POST /wp-json/programs/v1/track` and written as JSON Lines to `wp-content/uploads/logs/data-track.log` with file locking. The endpoint strictly accepts click events with `link` or `button` labels, rejects oversized and unknown payloads, rate-limits requests, and does not record personal data.

## Decisions and limitations

The supplied homepage styles and image assets are retained. Programme listing/detail pages use the same palette and card language, and use native WordPress templates plus vanilla event delegation. A production deployment could additionally add a REST nonce for authenticated tracking, a real enquiry submission handler, and a server-side WXR export containing uploaded media.

## AI disclosure

OpenAI Codex was used to assist with scaffolding, implementation, and documentation. All generated code should be reviewed and tested in the target WordPress environment.
