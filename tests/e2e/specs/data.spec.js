/**
 * Events and venues: the post types and their meta in the REST API, dates as the local time of the site. Venues here
 * have no address, so nothing is looked up (the release test runs without the fake geocoder).
 */
const { test, expect } = require( '../fixtures' );

const { cleanUp, create } = require( '../helpers' );

test.describe( 'Events and venues', () => {
	test.afterEach( async ( { requestUtils } ) => {
		await cleanUp( requestUtils );
	} );

	test( 'are post types without pages, with their meta in the REST API', async ( {
		requestUtils,
	} ) => {
		const types = await requestUtils.rest( {
			path: '/wp/v2/types',
			params: { context: 'edit' },
		} );
		// No pages of their own: the blocks of the plugin show them.
		expect( types.datebook_event ).toMatchObject( {
			name: 'Events',
			rest_base: 'datebook_event',
			viewable: false,
		} );
		expect( types.datebook_venue ).toMatchObject( {
			name: 'Venues',
			viewable: false,
		} );

		const schema = await requestUtils.rest( {
			method: 'OPTIONS',
			path: '/wp/v2/datebook_event',
		} );
		const meta = schema.schema.properties.meta.properties;
		expect( Object.keys( meta ).sort() ).toEqual( [
			'datebook_description',
			'datebook_end',
			'datebook_image_id',
			'datebook_link',
			'datebook_start',
			'datebook_venue',
		] );
		expect( meta.datebook_start ).toMatchObject( {
			type: 'string',
			pattern: '^([0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2})?$',
		} );
	} );

	test( 'store start and end as the local time of the site, with a venue', async ( {
		requestUtils,
	} ) => {
		const venue = await create( requestUtils, 'venue', {
			title: 'E2E hall',
		} );
		const event = await create( requestUtils, 'event', {
			title: 'E2E dance evening',
			meta: {
				datebook_start: '2026-11-14T20:00',
				datebook_end: '2026-11-15T01:00',
				datebook_venue: venue.id,
				datebook_description: '<strong>Live</strong> band',
			},
		} );
		const stored = await requestUtils.rest( {
			path: `/wp/v2/datebook_event/${ event.id }`,
			params: { context: 'edit' },
		} );
		expect( stored.meta ).toMatchObject( {
			datebook_start: '2026-11-14T20:00',
			datebook_end: '2026-11-15T01:00',
			datebook_venue: venue.id,
			datebook_description: '<strong>Live</strong> band',
		} );

		// Another format is rejected; the date picker never produces it.
		await expect(
			create( requestUtils, 'event', {
				title: 'E2E wrong start',
				meta: { datebook_start: '14.11.2026 20:00' },
			} )
		).rejects.toMatchObject( { code: 'rest_invalid_pattern' } );
	} );
} );
