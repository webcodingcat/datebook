import { BlockView } from '@saij/wp-app-framework/view';

import { mountMaps } from '#shared/map';

/**
 * The map of the venue: loaded only on request.
 */
export default class Map extends BlockView {
	mount() {
		mountMaps( this, this.findAll( '[data-datebook-map]' ) );
	}
}
