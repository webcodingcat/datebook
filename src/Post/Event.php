<?php

declare(strict_types=1);

namespace Saij\Datebook\Post;

use Saij\WpAppFramework\Post\AbstractPost;
use Saij\WpAppFramework\Post\PostType;
use Saij\WpAppFramework\Post\PostTypeLabels;
use Saij\WpAppFramework\Post\PostTypeSupport;

/**
 * An event: not public (no page of its own), shown by the blocks of the plugin; edited on the classic edit screen
 * with its details in a meta box.
 */
#[PostType('event', label: 'Events', singularLabel: 'Event', public: false, supports: [PostTypeSupport::Title], labels: new PostTypeLabels(addNewItem: 'Add an event'), menuIcon: 'dashicons-calendar-alt')]
final class Event extends AbstractPost
{
    public function getDetails(): EventDetails
    {
        return $this->meta(EventDetails::class);
    }
}
