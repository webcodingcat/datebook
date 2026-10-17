<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Block\Map;

use WebCodingCat\Datebook\Output\MapView;
use WebCodingCat\WpAppFramework\Block\Block;
use WebCodingCat\WpAppFramework\Block\BlockCategory;
use WebCodingCat\WpAppFramework\Twig\TemplateRenderer;

/**
 * The map of a venue, e.g. the place of a weekly training. The map tiles load from OpenStreetMap only after a click;
 * until then the block shows the address, a notice and a link for directions.
 */
#[Block('map', title: 'Map', category: BlockCategory::Widgets, icon: 'location-alt', description: 'The map of a venue, loaded only on request.', keywords: ['directions', 'venue', 'openstreetmap'])]
final readonly class Map
{
    public function __construct(
        private TemplateRenderer $renderer,
        private MapView $maps,
    ) {}

    public function render(MapAttributes $attrs): string
    {
        return $this->renderer->render('Block/Map/Map.twig', [
            'map' => $attrs->venue === null ? null : $this->maps->of($attrs->venue, $attrs->zoom),
        ]);
    }
}
