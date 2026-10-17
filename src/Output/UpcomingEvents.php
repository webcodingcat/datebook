<?php

declare(strict_types=1);

namespace Saij\Datebook\Output;

use DateTimeImmutable;
use Saij\Datebook\Post\Event;
use Saij\Datebook\Post\EventDetailsFields;
use Saij\WpAppFramework\Query\All;
use Saij\WpAppFramework\Query\Any;
use Saij\WpAppFramework\Query\PostOrder;
use Saij\WpAppFramework\Value\DateTime;
use Saij\WpAppFramework\Value\Link;

/**
 * The events that are not over yet – their end, or else their start, lies at or after now – in the order of their
 * start, prepared for the template: dates in the formats of the site and the schema.org data of each event.
 *
 * @phpstan-type Item array{
 *     title: string,
 *     startIso: string,
 *     weekday: string,
 *     day: string,
 *     month: string,
 *     when: string,
 *     description: string,
 *     imageId: int|null,
 *     link: Link,
 *     venue: array{name: string, address: string}|null,
 *     map: array{name: string, address: string, lat: float, lng: float, zoom: int, route: string}|null,
 * }
 */
final readonly class UpcomingEvents
{
    /** Zoom of the map of a venue in the list: streets around the venue. */
    private const int ZOOM = 15;

    public function __construct(private MapView $maps) {}

    /**
     * @return array{items: list<Item>, jsonLd: string}
     */
    public function get(int $limit): array
    {
        $now = self::format(DateTime::FORMAT, new DateTimeImmutable('now', wp_timezone()));
        $events = Event::query()
            ->where(
                EventDetailsFields::start()->notEquals(''),
                Any::of(
                    EventDetailsFields::end()->greaterOrEqual($now),
                    All::of(EventDetailsFields::end()->equals(''), EventDetailsFields::start()->greaterOrEqual($now)),
                ),
            )
            ->orderBy(EventDetailsFields::start())
            ->orderBy(PostOrder::Title)
            ->limit(max(1, $limit))
            ->get();

        $items = [];
        $schema = [];
        foreach ($events as $event) {
            [$items[], $schema[]] = $this->item($event);
        }

        return [
            'items' => $items,
            'jsonLd' => $schema === [] ? '' : (string) wp_json_encode(
                ['@context' => 'https://schema.org', '@graph' => $schema],
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ),
        ];
    }

    /**
     * @return array{0: Item, 1: array<string, mixed>}
     */
    private function item(Event $event): array
    {
        $details = $event->getDetails();
        $start = self::time($details->getStart());
        $end = $details->getEnd() === '' ? null : self::time($details->getEnd());
        $venue = $details->getVenue();
        $address = $venue?->getPlace()->getAddress() ?? '';

        $item = [
            'title' => $event->getTitle(),
            'startIso' => $start->format('c'),
            'weekday' => self::format('D', $start),
            'day' => self::format('j', $start),
            'month' => self::format('M', $start),
            'when' => self::when($start, $end),
            'description' => $details->getDescription(),
            'imageId' => $details->getImageId(),
            'link' => $details->getLink(),
            'venue' => $venue === null ? null : ['name' => $venue->getTitle(), 'address' => $address],
            'map' => $venue === null ? null : $this->maps->of($venue, self::ZOOM),
        ];

        $schema = array_filter([
            '@type' => 'Event',
            'name' => $event->getTitle(),
            'startDate' => $start->format('c'),
            'endDate' => $end?->format('c'),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'description' => wp_strip_all_tags($details->getDescription()),
            'url' => $details->getLink()->url,
            'image' => $details->getImageId() === null ? null : wp_get_attachment_image_url($details->getImageId(), 'large'),
            'location' => $venue === null ? null : [
                '@type' => 'Place',
                'name' => $venue->getTitle(),
                'address' => str_replace("\n", ', ', $address),
            ],
        ], static fn(mixed $value): bool => $value !== null && $value !== '' && $value !== false);

        return [$item, $schema];
    }

    /**
     * A stored date (local time of the site) as a time in the time zone of the site.
     */
    private static function time(string $value): DateTimeImmutable
    {
        $time = DateTimeImmutable::createFromFormat('!' . DateTime::FORMAT, $value, wp_timezone());
        if ($time === false) {
            throw new \LogicException(sprintf('The value factory let the date "%s" pass.', $value));
        }

        return $time;
    }

    /**
     * "14 November 2026, 8:00 pm – 1:00 am" in the formats of the site; the end date only when it is another day.
     */
    private static function when(DateTimeImmutable $start, ?DateTimeImmutable $end): string
    {
        $date = self::option('date_format');
        $time = self::option('time_format');
        $text = self::format($date, $start) . ', ' . self::format($time, $start);
        if ($end === null) {
            return $text;
        }
        $sameDay = $end->format('Y-m-d') === $start->format('Y-m-d')
            || ($end > $start && $end->getTimestamp() - $start->getTimestamp() < DAY_IN_SECONDS && (int) $end->format('G') < 6);

        return $text . ' – ' . ($sameDay ? '' : self::format($date, $end) . ', ') . self::format($time, $end);
    }

    /**
     * A time in a format of PHP, localized like WordPress' date_i18n() (wp_date()) in the time zone of the site.
     */
    private static function format(string $format, DateTimeImmutable $time): string
    {
        $text = wp_date($format, $time->getTimestamp());
        if ($text === false) {
            throw new \LogicException(sprintf('wp_date() cannot format the time %s.', $time->format('c')));
        }

        return $text;
    }

    /**
     * A format of the general settings (date_format, time_format), which WordPress always has.
     */
    private static function option(string $name): string
    {
        $value = get_option($name);
        if (!is_string($value) || $value === '') {
            throw new \LogicException(sprintf('The option %s is no format.', $name));
        }

        return $value;
    }
}
