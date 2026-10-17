<?php

declare(strict_types=1);

namespace Saij\Datebook\Post;

use Saij\WpAppFramework\Query\Field\PostField;
use Saij\WpAppFramework\Query\Field\StringField;
use Saij\WpAppFramework\Query\MetaFields;

/**
 * The fields of EventDetails for queries: start and end compare as text (local time `Y-m-d\TH:i`).
 */
#[MetaFields(EventDetails::class)]
final class EventDetailsFields
{
    public static function start(): StringField
    {
        return new StringField(EventDetails::class, 'start');
    }

    public static function end(): StringField
    {
        return new StringField(EventDetails::class, 'end');
    }

    /**
     * @return PostField<Venue>
     */
    public static function venue(): PostField
    {
        return new PostField(EventDetails::class, 'venue', Venue::class);
    }
}
