<?php

use Akyos\Access\Admin\GmbReviewsAjax;
use Akyos\Access\Console\AkyosAccessCommand;

if (!function_exists('add_action')) {
    return;
}

GmbReviewsAjax::register();

/**
 * Enregistre les blocs Gutenberg du package listés dans le manifest
 * akyos-blocks.json à la racine du thème parent. Historiquement fait par
 * Block::boot() d'akyos-x-core, retiré au commit a0ba3a4 (08/07/2026).
 */
add_action('init', static function (): void {
    if (
        !function_exists('acf_register_block_type')
        || !class_exists(\Akyos\Core\Classes\Block::class)
    ) {
        return;
    }

    // Un autre loader (akyos-x-core, thème) a déjà enregistré les blocs Access.
    foreach (array_keys(\WP_Block_Type_Registry::get_instance()->get_all_registered()) as $registered) {
        if (str_starts_with($registered, 'acf/') && str_ends_with($registered, '-access')) {
            return;
        }
    }

    $manifest = get_template_directory() . DIRECTORY_SEPARATOR . 'akyos-blocks.json';
    if (!is_file($manifest)) {
        return;
    }

    $blocks = json_decode((string) file_get_contents($manifest), true);
    if (!is_array($blocks)) {
        return;
    }

    foreach ($blocks as $block) {
        $class = 'Akyos\\Access\\View\\Blocks\\' . $block;
        if (class_exists($class)) {
            (new $class())->registerGutenberg();
        }
    }
}, 20);

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
