<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Block\Events;

use WebCodingCat\WpAppFramework\Value\Label;

final readonly class EventsAttributes
{
    public function __construct(
        #[Label('Number of events')]
        public int $limit = 5,
    ) {}
}
