/**
 * The blocks on the front end: the upcoming events (past ones drop out, sorted by start, opened to their details,
 * described as JSON-LD) and the map of a venue, which requests tiles from OpenStreetMap only after a click. Venues
 * here have coordinates and no address, so nothing is looked up.
 */
const { test, expect } = require( '../fixtures' );

const { cleanUp, create, createPage } = require( '../helpers' );

/** A transparent PNG of one pixel, the answer to every tile request. */
const TILE = Buffer.from(
	'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
	'base64'
);

/**
 * @param {Object} requestUtils
 * @return {Promise<Object>} A venue with coordinates.
 */
async function venue( requestUtils ) {
	return create( requestUtils, 'venue', {
		title: 'E2E barn',
		meta: { datebook_latitude: 50.94, datebook_longitude: 11.09 },
	} );
}

/**
 * @param {Object} requestUtils
 * @param {string} title
 * @param {Object} meta
 */
async function event( requestUtils, title, meta ) {
	return create( requestUtils, 'event', { title, meta } );
}

test.describe( 'Blocks', () => {
	test.afterEach( async ( { requestUtils } ) => {
		await cleanUp( requestUtils );
	} );

	test( 'list the events that are not over, by start, with their details and JSON-LD', async ( {
		page,
		requestUtils,
	} ) => {
		const barn = await venue( requestUtils );
		await event( requestUtils, 'Past', {
			datebook_start: '2020-01-01T20:00',
		} );
		await event( requestUtils, 'Without start', {} );
		await event( requestUtils, 'May', {
			datebook_start: '2099-05-01T20:00',
			datebook_end: '2099-05-02T01:00',
			datebook_venue: barn.id,
			datebook_description: 'Live <strong>band</strong>',
		} );
		await event( requestUtils, 'March', {
			datebook_start: '2099-03-01T19:00',
		} );
		await event( requestUtils, 'Ongoing', {
			datebook_start: '2020-01-01T00:00',
			datebook_end: '2099-01-01T00:00',
		} );
		const url = await createPage(
			requestUtils,
			'E2E events',
			'<!-- wp:datebook/events /-->'
		);
		await page.goto( url );

		const events = page.locator( '.datebook-event' );
		await expect( events.locator( '.datebook-event__title' ) ).toHaveText( [
			'Ongoing',
			'March',
			'May',
		] );

		// An evening that ends after midnight shows the end time only.
		const may = events.filter( { hasText: 'May' } );
		await expect( may.locator( '.datebook-event__meta' ) ).toHaveText(
			'May 1, 2099, 8:00 pm – 1:00 am · E2E barn'
		);
		await expect( may.locator( 'time' ) ).toHaveAttribute(
			'datetime',
			'2099-05-01T20:00:00+00:00'
		);
		await expect( may.getByText( 'Live band' ) ).toBeHidden();
		await may.locator( 'summary' ).click();
		await expect( may.getByText( 'Live band' ) ).toBeVisible();
		await expect( may.locator( '[data-datebook-map]' ) ).toBeVisible();

		const jsonLd = JSON.parse(
			await page
				.locator(
					'.datebook-events script[type="application/ld+json"]'
				)
				.textContent()
		);
		expect( jsonLd[ '@graph' ] ).toHaveLength( 3 );
		expect( jsonLd[ '@graph' ][ 2 ] ).toMatchObject( {
			'@type': 'Event',
			name: 'May',
			startDate: '2099-05-01T20:00:00+00:00',
			endDate: '2099-05-02T01:00:00+00:00',
			description: 'Live band',
			location: { '@type': 'Place', name: 'E2E barn' },
		} );
	} );

	test( 'show as many events as set, or a notice without any', async ( {
		page,
		requestUtils,
	} ) => {
		const url = await createPage(
			requestUtils,
			'E2E limit',
			'<!-- wp:datebook/events {"limit":2} /-->'
		);
		await page.goto( url );
		await expect( page.locator( '.datebook-events__empty' ) ).toHaveText(
			'No upcoming events.'
		);

		for ( const month of [ '01', '02', '03' ] ) {
			await event( requestUtils, `Event ${ month }`, {
				datebook_start: `2099-${ month }-01T20:00`,
			} );
		}
		await page.reload();
		await expect( page.locator( '.datebook-event__title' ) ).toHaveText( [
			'Event 01',
			'Event 02',
		] );
	} );

	test( 'load the map of a venue only after a click', async ( {
		page,
		requestUtils,
	} ) => {
		const tiles = [];
		await page.route( 'https://tile.openstreetmap.org/**', ( route ) => {
			tiles.push( route.request().url() );
			return route.fulfill( { contentType: 'image/png', body: TILE } );
		} );
		const barn = await venue( requestUtils );
		const url = await createPage(
			requestUtils,
			'E2E map',
			`<!-- wp:datebook/map {"venue":${ barn.id },"zoom":14} /-->`
		);
		await page.goto( url );

		const map = page.locator( '.datebook-map-block [data-datebook-map]' );
		await expect(
			map.getByRole( 'link', { name: 'Plan route' } )
		).toHaveAttribute(
			'href',
			'https://www.openstreetmap.org/directions?route=%3B50.94%2C11.09'
		);
		await expect( map.locator( '.leaflet-container' ) ).toHaveCount( 0 );
		expect( tiles ).toEqual( [] );

		await map.getByRole( 'button', { name: 'Load map' } ).click();
		await expect( map.locator( '.leaflet-container' ) ).toBeVisible();
		await expect( map.locator( '.datebook-map__marker' ) ).toBeVisible();
		await expect(
			map.locator( '.leaflet-control-attribution' )
		).toContainText( 'Map data © OpenStreetMap contributors' );
		await expect.poll( () => tiles.length ).toBeGreaterThan( 0 );
		expect( tiles[ 0 ] ).toMatch(
			/^https:\/\/tile\.openstreetmap\.org\/14\/\d+\/\d+\.png$/
		);
	} );
} );
