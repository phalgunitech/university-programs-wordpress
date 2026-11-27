# Task Understanding

The goal is to turn the supplied University of Aberdeen Mumbai frontend into a WordPress site and add a complete Programs module. The homepage should preserve the supplied visual system and responsive behavior. Programmes will be managed as a custom post type and grouped with Undergraduate/Postgraduate taxonomy terms. Editors will enter academic year, duration, and repeatable content sections through a custom React-compatible admin panel. Visitors will browse filtered listings and detailed programme pages through clean `/programmes` routes. Important links, cards, filters, and buttons will send validated click events to a WordPress REST endpoint and a protected uploads log.

## Planned work

1. Frontend audit and theme conversion – 1 hour.
2. CPT, taxonomy, metadata, and admin editor – 2 hours.
3. Routing, cards, archive/detail templates – 2 hours.
4. Interactivity and tracking – 1 hour.
5. Sample content, documentation, and testing – 2 hours.

## Architecture

The theme owns appearance and templates. The plugin owns data, admin behavior, routing, tracking, and seed tooling. This keeps the Programs feature portable across themes.

## Assumptions and risks

The supplied frontend is a homepage reference and does not include the complete programme screens, so the programme routes reuse the existing palette and card components. Email delivery and CRM integration were not specified; the enquiry UI is therefore kept lightweight. Final repository branches, pull requests, video, preview hosting, and exports must be completed in the recruiter’s Git/hosting environment.
