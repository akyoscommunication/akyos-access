<?php

use Akyos\Access\Admin\GmbReviewsAjax;
use Akyos\Access\Console\AkyosAccessCommand;

if (!function_exists('add_action')) {
    return;
}

GmbReviewsAjax::register();

add_action('acf/input/admin_enqueue_scripts', static function (): void {
    if (!is_admin()) {
        return;
    }

    $baseUri = get_template_directory_uri() . '/vendor/akyos/akyos-access/resources/assets';
    $version = '1.0.2';

    wp_enqueue_style(
        'akyos-access-reviews-admin',
        $baseUri . '/css/reviews-admin.css',
        [],
        $version
    );

    wp_enqueue_script(
        'akyos-access-reviews-admin',
        $baseUri . '/js/reviews-admin.js',
        ['acf-input', 'jquery'],
        $version,
        true
    );

    wp_localize_script('akyos-access-reviews-admin', 'akyosAccessReviews', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('akyos_access_gmb'),
    ]);
});

add_action('cli_init', static function (): void {
    if (!class_exists('WP_CLI') || !class_exists(AkyosAccessCommand::class)) {
        return;
    }

    \WP_CLI::add_command('akyos-access', AkyosAccessCommand::class);
});
