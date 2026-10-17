/**
 * First browser tests of the project: extend them with tests of your blocks.
 * Each test deletes exactly what it created.
 */
const { test, expect } = require( '../fixtures' );

test.describe( 'Datebook', () => {
	test( 'renders the front page', async ( { page } ) => {
		const response = await page.goto( '/' );
		expect( response.status() ).toBe( 200 );
	} );
} );
