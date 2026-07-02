<?php

namespace Akyos\Access\Support;

/**
 * Migre les données ACF des blocs Access : Image/Gallery → MediaAccess (flexible content).
 *
 * Usage : wp akyos-access migrate-media
 */
class MediaAccessBlockDataMigrator
{
    /** @var array<string, list<array<string, string>>> */
    private const RULES = [
        'hero-access' => [
            ['kind' => 'flexible', 'field' => 'image_background'],
        ],
        'form-access' => [
            ['kind' => 'flexible', 'field' => 'image'],
        ],
        'banner-access' => [
            ['kind' => 'repeater_flexible', 'repeater' => 'elements', 'field' => 'image'],
        ],
        'numbers-access' => [
            ['kind' => 'repeater_flexible', 'repeater' => 'numbers', 'field' => 'image'],
        ],
        'team-access' => [
            ['kind' => 'repeater_flexible', 'repeater' => 'teams', 'field' => 'image'],
        ],
        'services-access' => [
            ['kind' => 'repeater_flexible', 'repeater' => 'services', 'field' => 'image'],
        ],
        'text-image-access' => [
            ['kind' => 'gallery_to_repeater', 'field' => 'images', 'media' => 'media'],
        ],
        'text-image-inline-access' => [
            ['kind' => 'gallery_to_repeater', 'field' => 'images', 'media' => 'media'],
        ],
        'gallery-access' => [
            ['kind' => 'gallery_to_repeater', 'field' => 'gallery', 'media' => 'media'],
        ],
    ];

    /** @var array<string, array<string, mixed>> */
    private static array $fieldIndexCache = [];

    /**
     * @return array{data: array<string, mixed>, changed: bool}
     */
    public static function migrateBlockData(array $data, string $blockSlug): array
    {
        $rules = self::RULES[$blockSlug] ?? [];
        if ($rules === []) {
            return ['data' => $data, 'changed' => false];
        }

        $changed = false;
        $fieldIndex = self::fieldIndex($blockSlug);

        foreach ($rules as $rule) {
            $result = match ($rule['kind']) {
                'flexible' => self::migrateFlexibleField($data, $rule['field'], $fieldIndex),
                'repeater_flexible' => self::migrateRepeaterFlexible(
                    $data,
                    $rule['repeater'],
                    $rule['field'],
                    $fieldIndex
                ),
                'gallery_to_repeater' => self::migrateGalleryToRepeater(
                    $data,
                    $rule['field'],
                    $rule['media'],
                    $fieldIndex
                ),
                default => ['data' => $data, 'changed' => false],
            };

            if ($result['changed']) {
                $data = $result['data'];
                $changed = true;
            }
        }

        return ['data' => $data, 'changed' => $changed];
    }

    /**
     * @return array{content: string, blocks: int}
     */
    public static function migratePostContent(string $content): array
    {
        if (!function_exists('parse_blocks') || !function_exists('serialize_blocks')) {
            return ['content' => $content, 'blocks' => 0];
        }

        $blocks = parse_blocks($content);
        $count = self::migrateParsedBlocks($blocks);

        return [
            'content' => $count > 0 ? serialize_blocks($blocks) : $content,
            'blocks' => $count,
        ];
    }

    /**
     * @param list<string> $postTypes
     * @return array{updated_posts: int, migrated_blocks: int, messages: list<string>}
     */
    public static function migratePosts(
        bool $dryRun = false,
        ?int $postId = null,
        array $postTypes = ['page', 'post']
    ): array {
        $query = [
            'post_type' => $postTypes,
            'post_status' => ['publish', 'draft', 'pending', 'future', 'private'],
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        if ($postId !== null && $postId > 0) {
            $query['post__in'] = [$postId];
        }

        $postIds = function_exists('get_posts') ? get_posts($query) : [];
        $updatedPosts = 0;
        $migratedBlocks = 0;
        $messages = [];

        foreach ($postIds as $id) {
            $content = (string) get_post_field('post_content', $id);
            if ($content === '' || !str_contains($content, 'acf/')) {
                continue;
            }

            $result = self::migratePostContent($content);
            if ($result['blocks'] === 0) {
                continue;
            }

            $migratedBlocks += $result['blocks'];
            $title = function_exists('get_the_title') ? (get_the_title($id) ?: "(#{$id})") : "(#{$id})";

            if ($dryRun) {
                $messages[] = sprintf('[dry-run] #%d %s — %d bloc(s)', $id, $title, $result['blocks']);
                $updatedPosts++;
                continue;
            }

            $save = wp_update_post([
                'ID' => $id,
                'post_content' => $result['content'],
            ], true);

            if (is_wp_error($save)) {
                $messages[] = sprintf('#%d %s : %s', $id, $title, $save->get_error_message());
                continue;
            }

            $messages[] = sprintf('#%d %s — %d bloc(s) migré(s)', $id, $title, $result['blocks']);
            $updatedPosts++;
        }

        return [
            'updated_posts' => $updatedPosts,
            'migrated_blocks' => $migratedBlocks,
            'messages' => $messages,
        ];
    }

    /**
     * @param list<array<string, mixed>> $blocks
     */
    public static function migrateParsedBlocks(array &$blocks): int
    {
        $count = 0;

        foreach ($blocks as &$block) {
            if (!empty($block['innerBlocks'])) {
                $count += self::migrateParsedBlocks($block['innerBlocks']);
            }

            $name = (string) ($block['blockName'] ?? '');
            if (!str_starts_with($name, 'acf/')) {
                continue;
            }

            $slug = substr($name, 4);
            $attrs = $block['attrs'] ?? [];
            $data = $attrs['data'] ?? null;
            if (!is_array($data)) {
                continue;
            }

            $result = self::migrateBlockData($data, $slug);
            if (!$result['changed']) {
                continue;
            }

            $block['attrs'] = $attrs;
            $block['attrs']['data'] = $result['data'];
            $count++;
        }
        unset($block);

        return $count;
    }

    /**
     * @return array{data: array<string, mixed>, changed: bool}
     */
    private static function migrateFlexibleField(array $data, string $field, array $fieldIndex): array
    {
        $layout = self::extractLayoutFromData($data, $field);
        if ($layout === null) {
            return ['data' => $data, 'changed' => false];
        }

        if (self::hasCorrectFlexibleFlat($data, $field, $layout)) {
            return ['data' => $data, 'changed' => false];
        }

        self::purgeKeys($data, $field);
        $data = array_merge($data, self::encodeFlexibleFlat($field, $fieldIndex[$field] ?? null, $layout));

        return ['data' => $data, 'changed' => true];
    }

    /**
     * @return array{data: array<string, mixed>, changed: bool}
     */
    private static function migrateRepeaterFlexible(
        array $data,
        string $repeater,
        string $field,
        array $fieldIndex
    ): array {
        $changed = false;
        $rowIndexes = self::repeaterRowIndexes($data, $repeater);

        foreach ($rowIndexes as $index) {
            $flexKey = "{$repeater}_{$index}_{$field}";

            $layout = self::extractLayoutFromData($data, $flexKey);
            if ($layout === null) {
                continue;
            }

            if (self::hasCorrectFlexibleFlat($data, $flexKey, $layout)) {
                continue;
            }

            self::purgeKeys($data, $flexKey);

            $repeaterField = $fieldIndex[$repeater] ?? null;
            $flexField = is_array($repeaterField) ? ($repeaterField['sub_fields'][$field] ?? null) : null;

            $data = array_merge($data, self::encodeFlexibleFlat($flexKey, $flexField, $layout));
            $changed = true;
        }

        return ['data' => $data, 'changed' => $changed];
    }

    /**
     * @return array{data: array<string, mixed>, changed: bool}
     */
    private static function migrateGalleryToRepeater(
        array $data,
        string $field,
        string $mediaField,
        array $fieldIndex
    ): array {
        $repeaterMeta = $fieldIndex[$field] ?? null;
        $mediaMeta = is_array($repeaterMeta) ? ($repeaterMeta['sub_fields'][$mediaField] ?? null) : null;
        $ids = MediaHelper::extractAttachmentIds($data[$field] ?? null);

        if ($ids !== []) {
            self::purgeKeys($data, $field);
            $data[$field] = (string) count($ids);
            if (is_array($repeaterMeta) && !empty($repeaterMeta['key'])) {
                $data['_' . $field] = $repeaterMeta['key'];
            }

            foreach ($ids as $index => $id) {
                $layout = MediaHelper::toFlexibleLayout($id);
                if ($layout === null) {
                    continue;
                }

                $prefix = "{$field}_{$index}_{$mediaField}";
                $data = array_merge($data, self::encodeFlexibleFlat($prefix, $mediaMeta, $layout));
            }

            return ['data' => $data, 'changed' => true];
        }

        $changed = false;
        $count = (int) ($data[$field] ?? 0);

        for ($i = 0; $i < $count; $i++) {
            $prefix = "{$field}_{$i}_{$mediaField}";
            $layout = self::extractLayoutFromData($data, $prefix);
            if ($layout === null || self::hasCorrectFlexibleFlat($data, $prefix, $layout)) {
                continue;
            }

            self::purgeKeys($data, $prefix);
            $data = array_merge($data, self::encodeFlexibleFlat($prefix, $mediaMeta, $layout));
            $changed = true;
        }

        return ['data' => $data, 'changed' => $changed];
    }

    /**
     * @return array{acf_fc_layout: string, file?: int|string, url?: string}|null
     */
    private static function extractLayoutFromData(array $data, string $field): ?array
    {
        $layout = MediaHelper::toFlexibleLayout($data[$field] ?? null);
        if ($layout !== null) {
            return $layout;
        }

        if (!array_key_exists("{$field}_0_acf_fc_layout", $data)) {
            return null;
        }

        $layout = ['acf_fc_layout' => (string) $data["{$field}_0_acf_fc_layout"]];
        foreach (['file', 'url'] as $sub) {
            $key = "{$field}_0_{$sub}";
            if (array_key_exists($key, $data) && $data[$key] !== '' && $data[$key] !== null) {
                $layout[$sub] = $data[$key];
            }
        }

        return $layout;
    }

    /**
     * @param array{acf_fc_layout: string, file?: int|string, url?: string} $layout
     */
    private static function hasCorrectFlexibleFlat(array $data, string $field, array $layout): bool
    {
        $value = $data[$field] ?? null;
        if (!is_array($value) || ($value[0] ?? null) !== $layout['acf_fc_layout']) {
            return false;
        }

        foreach ($layout as $sub => $expected) {
            if ($sub === 'acf_fc_layout' || $expected === null || $expected === '') {
                continue;
            }

            if ((string) ($data["{$field}_0_{$sub}"] ?? '') !== (string) $expected) {
                return false;
            }
        }

        return true;
    }

    /** @return list<int> */
    private static function repeaterRowIndexes(array $data, string $repeater): array
    {
        $indexes = [];
        $pattern = '/^' . preg_quote($repeater, '/') . '_(\d+)_/';

        foreach (array_keys($data) as $key) {
            if (preg_match($pattern, $key, $matches)) {
                $indexes[] = (int) $matches[1];
            }
        }

        if ($indexes !== []) {
            return array_values(array_unique($indexes));
        }

        $count = (int) ($data[$repeater] ?? 0);
        if ($count <= 0) {
            return [];
        }

        return range(0, $count - 1);
    }

    private static function purgeKeys(array &$data, string $prefix): void
    {
        foreach (array_keys($data) as $key) {
            if ($key === $prefix || str_starts_with($key, $prefix . '_')) {
                unset($data[$key]);
            }
        }
    }

    /**
     * @param array{key?: string, layouts?: array<string, array{key?: string, sub_fields?: array<string, array{key?: string}>}>}|null $fieldMeta
     * @param array{acf_fc_layout: string, file?: int, url?: string} $layout
     * @return array<string, mixed>
     */
    private static function encodeFlexibleFlat(string $fieldName, ?array $fieldMeta, array $layout): array
    {
        $out = [];
        $layoutName = $layout['acf_fc_layout'];

        // Format bloc ACF : ["image"] + image_background_0_file (pas de clé acf_fc_layout)
        $out[$fieldName] = [$layoutName];
        if (!empty($fieldMeta['key'])) {
            $out['_' . $fieldName] = $fieldMeta['key'];
        }

        $prefix = $fieldName . '_0_';
        $layoutMeta = $fieldMeta['layouts'][$layoutName] ?? null;

        foreach ($layout as $sub => $value) {
            if ($sub === 'acf_fc_layout' || $value === null || $value === '') {
                continue;
            }

            $out[$prefix . $sub] = is_scalar($value) ? (string) $value : $value;
            $subKey = $layoutMeta['sub_fields'][$sub]['key'] ?? null;
            if ($subKey) {
                $out['_' . $prefix . $sub] = $subKey;
            }
        }

        return $out;
    }

    /** @return array<string, mixed> */
    private static function fieldIndex(string $blockSlug): array
    {
        if (isset(self::$fieldIndexCache[$blockSlug])) {
            return self::$fieldIndexCache[$blockSlug];
        }

        if (!function_exists('acf_get_field_groups') || !function_exists('acf_get_fields')) {
            return self::$fieldIndexCache[$blockSlug] = [];
        }

        $groups = acf_get_field_groups(['block' => 'acf/' . $blockSlug]);
        if ($groups === []) {
            return self::$fieldIndexCache[$blockSlug] = [];
        }

        $fields = acf_get_fields($groups[0]['key'] ?? $groups[0]['ID'] ?? '');
        if (!is_array($fields)) {
            return self::$fieldIndexCache[$blockSlug] = [];
        }

        return self::$fieldIndexCache[$blockSlug] = self::indexAcfFields($fields);
    }

    /**
     * @param list<array<string, mixed>> $fields
     * @return array<string, mixed>
     */
    private static function indexAcfFields(array $fields): array
    {
        $index = [];

        foreach ($fields as $field) {
            $name = $field['name'] ?? '';
            if ($name === '') {
                continue;
            }

            $entry = ['key' => $field['key'] ?? ''];

            if (($field['type'] ?? '') === 'flexible_content' && !empty($field['layouts'])) {
                $entry['layouts'] = [];
                foreach ($field['layouts'] as $layout) {
                    $layoutName = $layout['name'] ?? '';
                    if ($layoutName === '') {
                        continue;
                    }
                    $entry['layouts'][$layoutName] = [
                        'key' => $layout['key'] ?? '',
                        'sub_fields' => self::indexAcfFields($layout['sub_fields'] ?? []),
                    ];
                }
            }

            if (($field['type'] ?? '') === 'repeater' && !empty($field['sub_fields'])) {
                $entry['sub_fields'] = self::indexAcfFields($field['sub_fields']);
            }

            $index[$name] = $entry;
        }

        return $index;
    }
}
