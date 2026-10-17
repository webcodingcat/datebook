<?php

declare(strict_types=1);

namespace Saij\Datebook\Block\Events;

use Saij\Datebook\Output\Labels;
use Saij\Datebook\Output\UpcomingEvents;
use Saij\WpAppFramework\Block\Block;
use Saij\WpAppFramework\Block\BlockCategory;
use Saij\WpAppFramework\Twig\TemplateRenderer;

/**
 * The upcoming events: past events drop out by themselves. Each entry opens to its description, venue, link and a
 * map that loads on request; the events are described for search engines as schema.org JSON-LD.
 */
#[Block('events', title: 'Upcoming events', category: BlockCategory::Widgets, icon: 'calendar-alt', description: 'The next events; past events drop out by themselves.', keywords: ['dates', 'calendar', 'agenda'])]
final readonly class Events
{
    public function __construct(
        private TemplateRenderer $renderer,
        private UpcomingEvents $events,
        private Labels $labels,
    ) {}

    public function render(EventsAttributes $attrs): string
    {
        return $this->renderer->render('Block/Events/Events.twig', [
            ...$this->events->get($attrs->limit),
            'labels' => $this->labels->all(),
        ]);
    }
}
