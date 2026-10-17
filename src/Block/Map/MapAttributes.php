<?php

declare(strict_types=1);

namespace Saij\Datebook\Block\Map;

use Saij\Datebook\Post\Venue;
use Saij\WpAppFramework\Value\Label;

final readonly class MapAttributes
{
    public function __construct(
        // A post object: resolves for the data type, which a #[PostReference] would not (not viewable).
        public ?Venue $venue = null,
        #[Label('Zoom (1–19)')]
        public int $zoom = 16,
    ) {}
}
