# Plan – Datebook

Datebook is a WordPress plugin for upcoming events: events with a start, an end, a venue and a description, shown as a list that drops past events, and a map to a venue that loads only on request. Built with [wp-app-framework](https://github.com/webcodingcat/wp-app-framework). Goals: generic (any theme, any client), GDPR-friendly, easy to edit.

This file records the decisions and the steps. Tick a step when its commits are in; change a decision only together with the code.

## Decisions

### Data

- **Events** – post type `datebook_event`, not public (no pages of their own, no archive), edited on the classic edit screen: the title, and the meta "Event details":

  | Field | Type | Notes |
  |---|---|---|
  | `start` | `#[DateTime]` | local time of the site; required for the list (an event without start is not shown) |
  | `end` | `#[DateTime]` | optional |
  | `venue` | `?Venue` | optional |
  | `description` | `#[RichText]` | bold, italic, links |
  | `image` | `#[Image]` | optional |
  | `link` | `Link` | tickets or more information |

- **Venues** – post type `datebook_venue`, a data type (`public: false`): the title is the name ("Festhalle Rehungen"), the meta "Place" holds the address (multi-line; it may be partial, a town alone), latitude and longitude. Venues are reused by events and by the map block (e.g. the place of a weekly training).
- **No single pages** in version 1: an entry of the list expands to its description, address and map.

### Geocoding

- When a venue is saved (`wp_after_insert_post`) and its address differs from the address its coordinates were looked up for (a hidden field), the plugin asks Nominatim (OpenStreetMap) on the server: one request per save, with a User-Agent naming the plugin and the site, as the [usage policy](https://operations.osmfoundation.org/policies/nominatim/) requires.
- Coordinates typed by hand stay as long as the address does not change; a changed address is looked up again.
- A failed lookup (no result, no connection) leaves the coordinates as they are and shows a notice on the edit screen; the next save tries again.
- Coordinates `0, 0` mean none.

### Map

- Leaflet, bundled in the shared module `assets/shared/map.js` (no CDN; loaded once per page by the views of both blocks), its stylesheet imported into `assets/styles.css`. OpenStreetMap tiles load only after a click on "Load map"; until then a placeholder with a notice shows, and a link "Plan route" (OpenStreetMap directions to the coordinates) works without the map. The pin takes the color of `--datebook-marker`, which a theme sets.
- The privacy policy of a site that uses the map names the tile server (OpenStreetMap Foundation) and that it loads only after the click.

### Output

- Blocks: "Upcoming events" (number of events; past events drop out: an event is past when its end, or else its start, lies before now; an evening that ends before 6 in the morning shows only the end time) and "Map" (one venue, a post object attribute `?Venue`: a `#[PostReference]` would not resolve the data type, which is not viewable).
- Texts are English in the code and translated with the text domain `datebook`: attributes by the framework, templates with `__()`, PHP with WordPress' functions. `vendor/bin/wp-app-framework pot` writes `languages/datebook.pot`; `languages/datebook-de_DE.po` is the German translation (neutral "Veranstaltung"; a site names its section as it likes).
- schema.org `Event` as JSON-LD for the events of the list.
- Neutral markup with prefixed Tailwind classes; themes adapt it with overrides of the Twig templates.

## Steps

Each step is one or two commits; lint and build before each commit.

1. [x] Project from wp-app-starter (plugin, code checks, release workflow, browser tests); this plan.
2. [x] Post types Event and Venue with their meta; geocoding of venues with Nominatim; test plugin `tests/fake-nominatim`; browser tests.
3. [x] Blocks "Upcoming events" and "Map" (Leaflet, click to load), JSON-LD, browser tests.
4. [x] Translations: the texts of the plugin translatable (framework 0.19), German translation (`languages/datebook-de_DE.po`).
5. [x] Release 0.1.0 (after framework 0.19 with `#[DateTime]`).
6. [x] Web Coding Cat (release 0.2.0): the repository moved to the organization webcodingcat; package `webcodingcat/datebook`, PHP namespace `WebCodingCat\Datebook`, framework 0.20 (`webcodingcat/wp-app-framework`). Post types, blocks and options keep their names (`datebook_…`, `datebook/…`).

## Dependencies

- wp-app-framework 0.20 (`webcodingcat/wp-app-framework`, `^0.20`; `#[DateTime]` since 0.19).
- Repository: https://github.com/webcodingcat/datebook
