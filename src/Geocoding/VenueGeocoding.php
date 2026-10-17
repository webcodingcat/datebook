<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Geocoding;

use WebCodingCat\Datebook\Post\Venue;
use WebCodingCat\WpAppFramework\Hook\Hook;
use WP_Post;

/**
 * Looks up the coordinates of a venue when it is saved with an address other than the one its coordinates belong to;
 * coordinates typed by hand stay until the address changes. A failed lookup keeps the coordinates and leaves a notice
 * for the editor; the next save tries again.
 */
final readonly class VenueGeocoding
{
    /** Transient of the notice, per user. */
    private const string NOTICE = 'datebook_geocoding_notice_';

    public function __construct(private Nominatim $nominatim) {}

    /**
     * After the post and its meta are saved (wp_after_insert_post runs after save_post, where the meta box stores
     * the meta).
     */
    #[Hook('wp_after_insert_post')]
    public function geocode(int $postId, WP_Post $post): void
    {
        if ($post->post_status === 'auto-draft') {
            return;
        }
        $venue = Venue::find($postId);
        if ($venue === null) {
            return;
        }
        $place = $venue->getPlace();
        $address = trim($place->getAddress());
        if ($address === '' || $address === $place->getGeocodedAddress()) {
            return;
        }

        try {
            $coordinates = $this->nominatim->lookup($address);
        } catch (GeocodingException $exception) {
            set_transient(self::NOTICE . get_current_user_id(), $exception->getMessage(), 5 * MINUTE_IN_SECONDS);

            return;
        }
        $place->setGeocoded($coordinates->getLatitude(), $coordinates->getLongitude(), $address);
        $venue->save();
    }

    #[Hook('admin_notices')]
    public function notice(): void
    {
        $key = self::NOTICE . get_current_user_id();
        $message = get_transient($key);
        if (!is_string($message)) {
            return;
        }
        delete_transient($key);
        wp_admin_notice(
            esc_html($message) . ' ' . esc_html__('Enter the coordinates by hand, or correct the address and save again.', 'datebook'),
            ['type' => 'warning', 'dismissible' => true],
        );
    }
}
