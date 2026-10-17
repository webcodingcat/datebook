<?php

/**
 * Plugin Name: Datebook fake Nominatim
 * Description: Test plugin of Datebook: answers the geocoding requests to Nominatim, so browser tests need no network, and records them (REST route datebook-fake-nominatim/v1/requests: GET lists, DELETE clears).
 * Requires PHP: 8.5
 * License: GPL-2.0-or-later
 */

declare(strict_types=1);

/** Addresses (the query Datebook sends) and their coordinates; "Offline" fails like a lost connection. */
const DATEBOOK_FAKE_NOMINATIM_PLACES = [
    'Unter dem Berge 6, 99097 Erfurt' => ['50.9581', '11.0927'],
    'Rehungen' => ['51.4466', '10.5503'],
];

add_filter('pre_http_request', static function (mixed $preempt, array $args, string $url): mixed {
    if (!str_starts_with($url, 'https://nominatim.openstreetmap.org/search?')) {
        return $preempt;
    }
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
    $address = is_string($query['q'] ?? null) ? $query['q'] : '';
    $requests = get_option('datebook_fake_nominatim', []);
    update_option('datebook_fake_nominatim', [
        ...(is_array($requests) ? $requests : []),
        ['q' => $address, 'user-agent' => is_string($args['user-agent'] ?? null) ? $args['user-agent'] : ''],
    ]);
    if ($address === 'Offline') {
        return new WP_Error('http_request_failed', 'cURL error 28: Connection timed out');
    }
    $place = DATEBOOK_FAKE_NOMINATIM_PLACES[$address] ?? null;

    return [
        'headers' => [],
        'body' => (string) json_encode($place === null ? [] : [['lat' => $place[0], 'lon' => $place[1], 'display_name' => $address]]),
        'response' => ['code' => 200, 'message' => 'OK'],
        'cookies' => [],
        'filename' => null,
    ];
}, 10, 3);

add_action('rest_api_init', static function (): void {
    register_rest_route('datebook-fake-nominatim/v1', '/requests', [
        [
            'methods' => 'GET',
            'callback' => static fn(): array => (array) get_option('datebook_fake_nominatim', []),
            'permission_callback' => static fn(): bool => current_user_can('manage_options'),
        ],
        [
            'methods' => 'DELETE',
            'callback' => static fn(): bool => delete_option('datebook_fake_nominatim'),
            'permission_callback' => static fn(): bool => current_user_can('manage_options'),
        ],
    ]);
});
