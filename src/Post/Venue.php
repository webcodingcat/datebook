<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Post;

use WebCodingCat\WpAppFramework\Post\AbstractPost;
use WebCodingCat\WpAppFramework\Post\PostType;
use WebCodingCat\WpAppFramework\Post\PostTypeLabels;
use WebCodingCat\WpAppFramework\Post\PostTypeSupport;

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
