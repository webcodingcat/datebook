<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Post;

use WebCodingCat\WpAppFramework\Post\PostMeta;
use WebCodingCat\WpAppFramework\Value\Hidden;
use WebCodingCat\WpAppFramework\Value\Label;
use WebCodingCat\WpAppFramework\Value\PlainText;

/**
 * Where a venue is: its address (it may be partial, a town alone) and its coordinates. The coordinates are looked up
 * when the address changes (VenueGeocoding) and can be corrected by hand; 0, 0 means none.
 */
#[PostMeta('place', title: 'Place', postTypes: [Venue::class])]
final class VenuePlace
{
    public function __construct(
        #[PlainText(multiline: true)]
        private string $address = '',
        private float $latitude = 0.0,
        private float $longitude = 0.0,
        // The address the coordinates were looked up for; another address is looked up again.
        #[Hidden]
        #[Label('Geocoded address')]
        private string $geocodedAddress = '',
    ) {}

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== 0.0 || $this->longitude !== 0.0;
    }

    public function getGeocodedAddress(): string
    {
        return $this->geocodedAddress;
    }

    /**
     * Coordinates looked up for the address.
     */
    public function setGeocoded(float $latitude, float $longitude, string $address): void
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->geocodedAddress = $address;
    }
}
