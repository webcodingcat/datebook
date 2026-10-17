<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Post;

use WebCodingCat\WpAppFramework\Post\PostMeta;
use WebCodingCat\WpAppFramework\Value\DateTime;
use WebCodingCat\WpAppFramework\Value\Image;
use WebCodingCat\WpAppFramework\Value\Label;
use WebCodingCat\WpAppFramework\Value\Link;
use WebCodingCat\WpAppFramework\Value\RichText;

/**
 * When and where an event takes place, and what it is about. Start and end are the local time of the site.
 */
#[PostMeta('details', title: 'Event details', postTypes: [Event::class])]
final class EventDetails
{
    public function __construct(
        #[DateTime]
        private string $start = '',
        #[DateTime]
        private string $end = '',
        private ?Venue $venue = null,
        #[RichText(allowedFormats: ['core/bold', 'core/italic', 'core/link'])]
        private string $description = '',
        #[Image]
        #[Label('Image')]
        private ?int $imageId = null,
        #[Label('Link (tickets, more information)')]
        private Link $link = new Link(),
    ) {}

    public function getStart(): string
    {
        return $this->start;
    }

    public function setStart(string $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): string
    {
        return $this->end;
    }

    public function setEnd(string $end): void
    {
        $this->end = $end;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): void
    {
        $this->venue = $venue;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getImageId(): ?int
    {
        return $this->imageId;
    }

    public function getLink(): Link
    {
        return $this->link;
    }
}
