<?php

use Akyos\Access\Console\AkyosAccessCommand;

if (!function_exists('add_action')) {
    return;
}

add_action('cli_init', static function (): void {
    if (!class_exists('WP_CLI') || !class_exists(AkyosAccessCommand::class)) {
        return;
    }

    \WP_CLI::add_command('akyos-access', AkyosAccessCommand::class);
});
