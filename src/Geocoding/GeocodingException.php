<?php

declare(strict_types=1);

namespace Saij\Datebook\Geocoding;

use RuntimeException;

/**
 * An address could not be looked up: no connection, an error of the service or no result. The message is shown to
 * editors.
 */
final class GeocodingException extends RuntimeException {}
