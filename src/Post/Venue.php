<?php

declare(strict_types=1);

namespace Saij\Datebook\Post;

use Saij\WpAppFramework\Post\AbstractPost;
use Saij\WpAppFramework\Post\PostType;
use Saij\WpAppFramework\Post\PostTypeLabels;
use Saij\WpAppFramework\Post\PostTypeSupport;

/**
 * A venue: a data type, named by its title, with an address and the coordinates for the map.
 */
#[PostType('venue', label: 'Venues', singularLabel: 'Venue', public: false, supports: [PostTypeSupport::Title], labels: new PostTypeLabels(addNewItem: 'Add a venue'), menuIcon: 'dashicons-location')]
final class Venue extends AbstractPost
{
    public function getPlace(): VenuePlace
    {
        return $this->meta(VenuePlace::class);
    }
}
