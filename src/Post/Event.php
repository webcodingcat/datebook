<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Post;

use WebCodingCat\WpAppFramework\Post\AbstractPost;
use WebCodingCat\WpAppFramework\Post\PostType;
use WebCodingCat\WpAppFramework\Post\PostTypeLabels;
use WebCodingCat\WpAppFramework\Post\PostTypeSupport;

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
