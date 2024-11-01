<?php

define('WPQR_NAME', 'WP QR Code Auto Generator');
define('WPQR_SHORT_NAME', 'WP QR Code');
define('WPQR_SLUG', 'wp-qr-code-auto-generator');
define('WPQR_VERSION', '1.0.0');
define('WPQR_KEY_OPTION', 'wpqr_options');
define('WPQR_KEY_URL', 'wpqr_url');
define('WPQR_KEY_ECLEVEL', 'wpqr_eclevel');
define('WPQR_KEY_MATRIX', 'wpqr_matrix');
define('WPQR_KEY_FRAME', 'wpqr_frame');
define('WPQR_KEY_EMBED', 'wpqr_embed');
define('WPQR_KEY_POSTS', 'wpqr_posts');

$upload_dir = wp_upload_dir();

define('WPQR_DIR', $upload_dir['basedir'] . '/wpqr-codes/');
define('WPQR_URL', $upload_dir['baseurl'] . '/wpqr-codes/');

if (!file_exists(WPQR_DIR)) {
  wp_mkdir_p(WPQR_DIR);
}

define('WPQR_TEMP_DIR', WPQR_DIR . 'temp/');
define('WPQR_TEMP_URL', WPQR_URL . 'temp/');

if (!file_exists(WPQR_TEMP_DIR)) {
  wp_mkdir_p(WPQR_TEMP_DIR);
}