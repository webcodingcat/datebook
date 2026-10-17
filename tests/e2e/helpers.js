/**
 * Helpers of the browser tests: posts the tests create through the REST API and delete again, and the requests the
 * test plugin tests/fake-nominatim answered.
 */

/** Posts (rest base and ID) the tests created; cleanUp() deletes them. */
const created = [];

/**
 * Creates a post of a post type of the plugin; cleanUp() deletes it.
 *
 * @param {Object} requestUtils
 * @param {string} type         "event" or "venue".
 * @param {Object} data         Fields: title, meta, status (default publish).
 * @return {Promise<Object>} The post as the REST API returns it.
 */
async function create( requestUtils, type, data ) {
	const post = await requestUtils.rest( {
		method: 'POST',
		path: `/wp/v2/datebook_${ type }`,
		data: { status: 'publish', ...data },
	} );
	created.push( [ `datebook_${ type }`, post.id ] );
	return post;
}

/**
 * Creates a published page with blocks; cleanUp() deletes it.
 *
 * @param {Object} requestUtils
 * @param {string} title
 * @param {string} content      Serialized blocks.
 * @return {Promise<string>} The URL of the page.
 */
async function createPage( requestUtils, title, content ) {
	const page = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: { title, content, status: 'publish' },
	} );
	created.push( [ 'pages', page.id ] );
	return page.link;
}

/**
 * Updates a post of a post type of the plugin.
 *
 * @param {Object} requestUtils
 * @param {string} type
 * @param {number} id
 * @param {Object} data
 * @return {Promise<Object>} The post as the REST API returns it.
 */
async function update( requestUtils, type, id, data ) {
	return requestUtils.rest( {
		method: 'POST',
		path: `/wp/v2/datebook_${ type }/${ id }`,
		data,
	} );
}

/**
 * @param {Object} requestUtils
 */
async function cleanUp( requestUtils ) {
	while ( created.length > 0 ) {
		const [ restBase, id ] = created.pop();
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wp/v2/${ restBase }/${ id }`,
			params: { force: true },
		} );
	}
}

/**
 * The geocoding requests the test plugin answered, or null without the test plugin (the release test installs
 * the release ZIP alone).
 *
 * @param {Object} requestUtils
 * @return {Promise<Array<{q: string, 'user-agent': string}>|null>} The requests.
 */
async function geocodingRequests( requestUtils ) {
	try {
		return await requestUtils.rest( {
			path: '/datebook-fake-nominatim/v1/requests',
		} );
	} catch ( error ) {
		// rest() throws the JSON of the error response.
		if ( error?.code === 'rest_no_route' ) {
			return null;
		}
		throw error;
	}
}

/**
 * @param {Object} requestUtils
 */
async function clearGeocodingRequests( requestUtils ) {
	await requestUtils.rest( {
		method: 'DELETE',
		path: '/datebook-fake-nominatim/v1/requests',
	} );
}

module.exports = {
	cleanUp,
	clearGeocodingRequests,
	create,
	createPage,
	geocodingRequests,
	update,
};
