<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Geocoding;

/**
 * A point on the map, in degrees (WGS 84).
 */
final readonly class Coordinates
{
    public function __construct(
        private float $latitude,
        private float $longitude,
    ) {}

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }
}
