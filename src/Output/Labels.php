<?php

declare(strict_types=1);

namespace Saij\Datebook\Output;

/**
 * The texts of the front end, translated with the text domain of the plugin and passed to the templates.
 */
final readonly class Labels
{
    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return [
            'empty' => __('No upcoming events.', 'datebook'),
            'more' => __('More information', 'datebook'),
            'consent' => __('The map is loaded from OpenStreetMap. Your browser then sends your IP address to its servers.', 'datebook'),
            'load' => __('Load map', 'datebook'),
            'route' => __('Plan route', 'datebook'),
            'attribution' => __('Map data © OpenStreetMap contributors', 'datebook'),
            'chooseVenue' => __('Choose a venue with coordinates in the block settings.', 'datebook'),
        ];
    }
}
