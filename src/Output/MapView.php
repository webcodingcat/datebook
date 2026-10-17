<?php

declare(strict_types=1);

namespace Saij\Datebook\Output;

use Saij\Datebook\Post\Venue;

/**
 * The data of a map of a venue for the template Block/Map/MapCanvas.twig, or null for a venue without coordinates.
 */
final readonly class MapView
{
    /** OpenStreetMap's directions to a point, the start left to the visitor. */
    private const string ROUTE = 'https://www.openstreetmap.org/directions?route=%%3B%s%%2C%s';

    /**
     * @return array{name: string, address: string, lat: float, lng: float, zoom: int, route: string}|null
     */
    public function of(Venue $venue, int $zoom): ?array
    {
        $place = $venue->getPlace();
        if (!$place->hasCoordinates()) {
            return null;
        }

        return [
            'name' => $venue->getTitle(),
            'address' => $place->getAddress(),
            'lat' => $place->getLatitude(),
            'lng' => $place->getLongitude(),
            'zoom' => max(1, min(19, $zoom)),
            'route' => sprintf(self::ROUTE, $place->getLatitude(), $place->getLongitude()),
        ];
    }
}
