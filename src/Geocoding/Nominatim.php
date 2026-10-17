<?php

declare(strict_types=1);

namespace WebCodingCat\Datebook\Geocoding;

/**
 * Looks up addresses with Nominatim, the geocoder of OpenStreetMap, on the server. Its usage policy
 * (https://operations.osmfoundation.org/policies/nominatim/) asks for a User-Agent that names the application and at
 * most one request per second; the plugin sends one request when an editor saves a changed address.
 */
final readonly class Nominatim
{
    public const string URL = 'https://nominatim.openstreetmap.org/search';

    /**
     * @throws GeocodingException
     */
    public function lookup(string $address): Coordinates
    {
        $query = implode(', ', array_filter(array_map(trim(...), explode("\n", $address)), static fn(string $line): bool => $line !== ''));
        $response = wp_remote_get(add_query_arg([
            'q' => rawurlencode($query),
            'format' => 'jsonv2',
            'limit' => '1',
            'accept-language' => rawurlencode(get_bloginfo('language')),
        ], self::URL), [
            'timeout' => 5,
            'user-agent' => 'Datebook WordPress plugin (' . home_url() . ')',
        ]);
        if (is_wp_error($response)) {
            throw new GeocodingException(sprintf(/* translators: %s: the error */ __('The address could not be looked up: %s', 'datebook'), $response->get_error_message()));
        }
        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200) {
            throw new GeocodingException(sprintf(/* translators: %d: HTTP status */ __('The address could not be looked up: OpenStreetMap answered with status %d.', 'datebook'), $status));
        }
        $results = json_decode(wp_remote_retrieve_body($response), true);
        $first = is_array($results) ? $results[0] ?? null : null;
        $latitude = is_array($first) ? $first['lat'] ?? null : null;
        $longitude = is_array($first) ? $first['lon'] ?? null : null;
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            throw new GeocodingException(sprintf(/* translators: %s: the address */ __('OpenStreetMap found no place for the address "%s".', 'datebook'), $query));
        }

        return new Coordinates((float) $latitude, (float) $longitude);
    }
}
