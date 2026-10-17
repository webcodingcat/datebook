<?php

/**
 * Plugin Name: Datebook
 * Description: Upcoming events with venues and a map that loads only on request.
 * Author: Web Coding Cat
 * Author URI: https://webcodingcat.de
 * Version: 0.1.0
 * Requires at least: 7.1
 * Requires PHP: 8.5
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: datebook
 */

declare(strict_types=1);

// Release builds ship prefixed dependencies in vendor-prefixed/; source checkouts use Composer's vendor/.
require is_file(__DIR__ . '/vendor-prefixed/autoload.php')
    ? __DIR__ . '/vendor-prefixed/autoload.php'
    : __DIR__ . '/vendor/autoload.php';

WebCodingCat\WpAppFramework\Plugin::boot(__FILE__);
