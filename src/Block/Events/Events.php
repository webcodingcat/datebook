<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Block\Events;

use WebCodingCat\Datebook\Output\UpcomingEvents;
use WebCodingCat\WpAppFramework\Block\Block;
use WebCodingCat\WpAppFramework\Block\BlockCategory;
use WebCodingCat\WpAppFramework\Twig\TemplateRenderer;

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
    ) {}

    public function render(EventsAttributes $attrs): string
    {
        return $this->renderer->render('Block/Events/Events.twig', $this->events->get($attrs->limit));
    }
}
