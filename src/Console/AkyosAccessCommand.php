<?php

namespace Akyos\Access\Console;

use Akyos\Access\Support\MediaAccessBlockDataMigrator;

class AkyosAccessCommand
{
    /**
     * Migre Image/Gallery → MediaAccess (flexible content) dans les blocs ACF Access.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Simule sans enregistrer en base.
     *
     * [--post-id=<id>]
     * : Limite la migration à un seul contenu.
     *
     * ## EXAMPLES
     *
     *     wp akyos-access migrate-media --dry-run
     *     wp akyos-access migrate-media --post-id=12
     *
     * @subcommand migrate-media
     */
    public function migrate_media(array $args, array $assocArgs): void
    {
        $dryRun = isset($assocArgs['dry-run']);
        $postId = isset($assocArgs['post-id']) ? (int) $assocArgs['post-id'] : null;
        $postTypes = apply_filters('akyos_access_media_migration_post_types', ['page', 'post']);

        $result = MediaAccessBlockDataMigrator::migratePosts($dryRun, $postId, $postTypes);

        foreach ($result['messages'] as $message) {
            \WP_CLI::log($message);
        }

        if ($result['updated_posts'] === 0) {
            \WP_CLI::success('Rien à migrer (déjà au format MediaAccess ou pas de blocs concernés).');

            return;
        }

        \WP_CLI::success(sprintf(
            '%s : %d page(s), %d bloc(s) MediaAccess.',
            $dryRun ? 'Simulation OK' : 'Migration terminée',
            $result['updated_posts'],
            $result['migrated_blocks']
        ));
    }

    /**
     * Répare l'encodage WYSIWYG corrompu (u003c, u0026…) dans les blocs ACF Access.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Simule sans enregistrer en base.
     *
     * [--post-id=<id>]
     * : Limite la réparation à un seul contenu.
     *
     * ## EXAMPLES
     *
     *     wp akyos-access repair-encoding --dry-run
     *     wp akyos-access repair-encoding --post-id=12
     *
     * @subcommand repair-encoding
     */
    public function repair_encoding(array $args, array $assocArgs): void
    {
        $dryRun = isset($assocArgs['dry-run']);
        $postId = isset($assocArgs['post-id']) ? (int) $assocArgs['post-id'] : null;
        $postTypes = apply_filters('akyos_access_media_migration_post_types', ['page', 'post']);

        $result = MediaAccessBlockDataMigrator::repairPosts($dryRun, $postId, $postTypes);

        foreach ($result['messages'] as $message) {
            \WP_CLI::log($message);
        }

        if ($result['updated_posts'] === 0) {
            \WP_CLI::success('Rien à réparer (encodage OK ou pas de blocs concernés).');

            return;
        }

        \WP_CLI::success(sprintf(
            '%s : %d page(s), %d bloc(s) réparé(s).',
            $dryRun ? 'Simulation OK' : 'Réparation terminée',
            $result['updated_posts'],
            $result['repaired_blocks']
        ));
    }
}
