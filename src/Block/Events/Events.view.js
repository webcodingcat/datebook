import { BlockView } from '@webcodingcat/wp-app-framework/view';

import { mountMaps } from '#shared/map';

/**
 * The maps of the venues in the opened entries: loaded only on request.
 */
export default class Events extends BlockView {
	mount() {
		mountMaps( this, this.findAll( '[data-datebook-map]' ) );
	}
}
