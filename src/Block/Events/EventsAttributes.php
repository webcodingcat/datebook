<?php

declare(strict_types=1);

namespace Saij\Datebook\Block\Events;

use Saij\WpAppFramework\Value\Label;

final readonly class EventsAttributes
{
    public function __construct(
        #[Label('Number of events')]
        public int $limit = 5,
    ) {}
}
