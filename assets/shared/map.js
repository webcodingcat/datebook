/**
 * Maps of venues with Leaflet (bundled, no CDN): a map is drawn – and its tiles requested from OpenStreetMap – only
 * after a click on its "Load map" button. Shared by the views of the blocks, so Leaflet loads once per page.
 */
import L from 'leaflet';

/** Tiles of OpenStreetMap (https://operations.osmfoundation.org/policies/tiles/). */
const TILES = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';

/** The pin of the venue, colored by the CSS variable --datebook-marker (styles.css). */
const PIN =
	'<svg viewBox="0 0 24 32" width="24" height="32" aria-hidden="true"><path fill="currentColor" d="M12 0C5.4 0 0 5.4 0 12c0 9 12 20 12 20s12-11 12-20C24 5.4 18.6 0 12 0Z"/><circle cx="12" cy="12" r="4.5" fill="#fff"/></svg>';

/**
 * Prepares the maps of a view: each draws itself after a click on its button and is removed when the view unmounts.
 *
 * @param {import('@webcodingcat/wp-app-framework/view').BlockView} view
 * @param {Element[]}                                               elements Elements with data-datebook-map.
 */
export function mountMaps( view, elements ) {
	for ( const element of elements ) {
		const button = element.querySelector( '.datebook-map__load' );
		view.listen( button, 'click', () => {
			const map = draw( element );
			view.onUnmount( () => map.remove() );
		} );
	}
}

/**
 * @param {HTMLElement} element
 * @return {L.Map} The map.
 */
function draw( element ) {
	const frame = element.querySelector( '.datebook-map__frame' );
	frame.querySelector( '.datebook-map__consent' ).remove();
	const point = [
		Number( element.dataset.lat ),
		Number( element.dataset.lng ),
	];
	const map = L.map( frame, { scrollWheelZoom: false } ).setView(
		point,
		Number( element.dataset.zoom )
	);
	L.tileLayer( TILES, {
		maxZoom: 19,
		attribution: `<a href="https://www.openstreetmap.org/copyright">${ escape(
			element.dataset.attribution
		) }</a>`,
	} ).addTo( map );
	L.marker( point, {
		title: element.dataset.name,
		icon: L.divIcon( {
			className: 'datebook-map__marker',
			html: PIN,
			iconSize: [ 24, 32 ],
			iconAnchor: [ 12, 32 ],
		} ),
	} ).addTo( map );
	return map;
}

/**
 * @param {string} text
 * @return {string} The text as HTML.
 */
function escape( text ) {
	const span = document.createElement( 'span' );
	span.textContent = text;
	return span.innerHTML;
}
