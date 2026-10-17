/**
 * Geocoding of venues: the address is looked up once when it changes, coordinates typed by hand stay, and a failed
 * lookup leaves a notice. Needs the test plugin tests/fake-nominatim (dev pools); the release test, which installs
 * the release ZIP alone, skips it.
 */
const { test, expect } = require( '../fixtures' );

const {
	cleanUp,
	clearGeocodingRequests,
	create,
	geocodingRequests,
	update,
} = require( '../helpers' );

/** An address the test plugin knows, as an editor enters it, and as the plugin sends it. */
const ADDRESS = 'Unter dem Berge 6\n99097 Erfurt';
const QUERY = 'Unter dem Berge 6, 99097 Erfurt';

/**
 * @param {Object} requestUtils
 * @param {number} id
 * @return {Promise<Object>} The meta of the venue.
 */
async function place( requestUtils, id ) {
	const venue = await requestUtils.rest( {
		path: `/wp/v2/datebook_venue/${ id }`,
		params: { context: 'edit' },
	} );
	return venue.meta;
}

test.describe( 'Geocoding', () => {
	test.beforeEach( async ( { requestUtils } ) => {
		test.skip(
			( await geocodingRequests( requestUtils ) ) === null,
			'needs the test plugin tests/fake-nominatim (dev pools only)'
		);
		await clearGeocodingRequests( requestUtils );
	} );

	test.afterEach( async ( { requestUtils } ) => {
		await cleanUp( requestUtils );
	} );

	test( 'looks up a new or changed address once and keeps coordinates typed by hand', async ( {
		requestUtils,
	} ) => {
		const venue = await create( requestUtils, 'venue', {
			title: 'E2E multi-purpose hall',
			meta: { datebook_address: ADDRESS },
		} );
		expect( await place( requestUtils, venue.id ) ).toMatchObject( {
			datebook_latitude: 50.9581,
			datebook_longitude: 11.0927,
		} );
		const requests = await geocodingRequests( requestUtils );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].q ).toBe( QUERY );
		expect( requests[ 0 ][ 'user-agent' ] ).toMatch(
			/^Datebook WordPress plugin \(http/
		);

		// Coordinates corrected by hand stay while the address stays.
		await update( requestUtils, 'venue', venue.id, {
			meta: { datebook_latitude: 50.95 },
		} );
		expect( await place( requestUtils, venue.id ) ).toMatchObject( {
			datebook_latitude: 50.95,
		} );
		expect( await geocodingRequests( requestUtils ) ).toHaveLength( 1 );

		// A changed address is looked up again.
		await update( requestUtils, 'venue', venue.id, {
			meta: { datebook_address: 'Rehungen' },
		} );
		expect( await place( requestUtils, venue.id ) ).toMatchObject( {
			datebook_latitude: 51.4466,
			datebook_longitude: 10.5503,
		} );
		expect( await geocodingRequests( requestUtils ) ).toHaveLength( 2 );
	} );

	test( 'leaves the coordinates and a notice when the lookup fails, and tries again on the next save', async ( {
		admin,
		page,
		requestUtils,
	} ) => {
		const venue = await create( requestUtils, 'venue', {
			title: 'E2E offline',
			meta: { datebook_address: 'Offline' },
		} );
		expect( await place( requestUtils, venue.id ) ).toMatchObject( {
			datebook_latitude: 0,
			datebook_longitude: 0,
		} );

		await admin.visitAdminPage( 'edit.php', 'post_type=datebook_venue' );
		await expect( page.locator( '.notice-warning' ) ).toContainText(
			'The address could not be looked up: cURL error 28'
		);
		// The notice shows once.
		await page.reload();
		await expect( page.locator( '.notice-warning' ) ).toHaveCount( 0 );

		await update( requestUtils, 'venue', venue.id, { title: 'E2E retry' } );
		expect( await geocodingRequests( requestUtils ) ).toHaveLength( 2 );
	} );
} );
