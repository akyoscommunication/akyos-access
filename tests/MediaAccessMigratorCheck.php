<?php

/**
 * ponytail: garde-fou compteur répéteur vs ID attachment. Upgrade = PHPUnit.
 */
require dirname(__DIR__) . '/src/Support/MediaHelper.php';
require dirname(__DIR__) . '/src/Support/MediaAccessBlockDataMigrator.php';

use Akyos\Access\Support\MediaAccessBlockDataMigrator;

$migrated = [
    'images' => '1',
    '_images' => 'field_b364b799',
    'images_0_media' => ['image'],
    '_images_0_media' => 'field_e92ed68c',
    'images_0_media_0_file' => '658',
    '_images_0_media_0_file' => 'field_1c25e09d',
];

$result = MediaAccessBlockDataMigrator::migrateBlockData($migrated, 'text-image-access');
assert($result['data']['images_0_media_0_file'] === '658', 'ne pas écraser un MediaAccess déjà migré');
assert(($result['data']['images_0_media_0_file'] ?? null) !== '1', 'le compteur 1 ne doit pas devenir l’attachment #1');

$legacy = [
    'images' => ['233', '220'],
    '_images' => 'field_b364b799',
];
$result = MediaAccessBlockDataMigrator::migrateBlockData($legacy, 'text-image-access');
assert($result['changed'] === true, 'galerie d’IDs → répéteur MediaAccess');
assert($result['data']['images'] === '2', 'compteur = nombre d’images');
assert(($result['data']['images_0_media_0_file'] ?? null) === '233');
assert(($result['data']['images_1_media_0_file'] ?? null) === '220');

$hero = ['image_background' => 332, '_image_background' => 'field_03851ed4'];
$result = MediaAccessBlockDataMigrator::migrateBlockData($hero, 'hero-access');
assert($result['changed'] === true);
assert(($result['data']['image_background'][0] ?? null) === 'image');
assert(($result['data']['image_background_0_file'] ?? null) === '332');

echo "MediaAccessMigratorCheck OK\n";
