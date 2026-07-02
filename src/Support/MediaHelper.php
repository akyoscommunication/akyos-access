<?php

namespace Akyos\Access\Support;

class MediaHelper
{
    /** Props Blade à retirer des attributs HTML avant merge(). */
    private const BLADE_PROPS = ['lg', 'sm', 'md', 'variant', 'rounded', 'media', 'cover', 'image'];

    /** Slot vide mais truthy pour @if($images[0]) dans les templates legacy. */
    public static function emptySlot(): array
    {
        return ['__empty__' => true];
    }

    public static function attachmentId(mixed $value): ?int
    {
        $normalized = self::normalize($value);

        if ($normalized !== null && $normalized['type'] === 'image') {
            return $normalized['id'];
        }

        return null;
    }

    /**
     * Récupère une prop passée par erreur dans $attributes (thème sans @props).
     */
    public static function bladeProp(mixed $value, mixed $attributes, string $key, mixed $default = null): mixed
    {
        if ($value !== null && $value !== '' && $value !== []) {
            return $value;
        }

        if ($attributes instanceof \Illuminate\View\ComponentAttributeBag && $attributes->has($key)) {
            return $attributes->get($key);
        }

        return $default;
    }

    /**
     * Attributs HTML sûrs pour ComponentAttributeBag::merge() (pas de tableaux).
     */
    public static function htmlAttributes(mixed $attributes): \Illuminate\View\ComponentAttributeBag
    {
        $bag = $attributes instanceof \Illuminate\View\ComponentAttributeBag
            ? $attributes
            : new \Illuminate\View\ComponentAttributeBag();

        $bag = $bag->except(self::BLADE_PROPS);

        // ponytail: legacy thèmes sans @props — upgrade = wrapper image.blade.php du bundle
        $filtered = [];
        foreach ($bag->getAttributes() as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }
            $filtered[$key] = $value;
        }

        return new \Illuminate\View\ComponentAttributeBag($filtered);
    }

    /** @return list<mixed> */
    public static function normalizeList(mixed $value): array
    {
        if ($value === null || $value === false || $value === '' || $value === []) {
            return [];
        }

        if (!is_array($value)) {
            return [$value];
        }

        if (self::isAttachmentArray($value) || isset($value['acf_fc_layout'])) {
            return [$value];
        }

        $items = [];
        foreach ($value as $row) {
            if (!is_array($row)) {
                $items[] = $row;
                continue;
            }
            if (array_key_exists('media', $row)) {
                $items[] = $row['media'];
            } elseif (isset($row['acf_fc_layout'])) {
                $items[] = $row;
            } elseif (array_key_exists('image', $row)) {
                $items[] = $row['image'];
            } else {
                $items[] = $row;
            }
        }

        return $items;
    }

    /** @return list<mixed> */
    public static function normalizeListWithPlaceholders(mixed $value, int $minItems = 1): array
    {
        $items = self::normalizeList($value);

        if ($items !== []) {
            return $items;
        }

        return array_fill(0, max(1, $minItems), self::emptySlot());
    }

    public static function normalize(mixed $value): ?array
    {
        if (is_array($value) && isset($value['__empty__'])) {
            return null;
        }

        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (is_string($value) && is_numeric($value)) {
            $value = (int) $value;
        }

        // Repeater ACF : { media: ... } ou ancien { image: 123 }
        if (is_array($value)) {
            if (array_key_exists('media', $value)) {
                return self::normalize($value['media']);
            }
            if (array_key_exists('image', $value) && !isset($value['acf_fc_layout'])) {
                return self::normalize($value['image']);
            }
            if (isset($value['acf_fc_layout'])) {
                return self::normalizeLayout($value);
            }
            if (self::isAttachmentArray($value)) {
                return self::normalizeAttachmentArray($value);
            }
            if (isset($value[0])) {
                if (is_array($value[0])) {
                    return self::normalizeLayout($value[0]);
                }
                if (is_numeric($value[0])) {
                    return self::normalize((int) $value[0]);
                }
            }
        }

        if (is_numeric($value)) {
            return self::normalizeAttachmentId((int) $value);
        }

        if (is_string($value)) {
            if (self::youtubeId($value) !== null) {
                return self::normalizeYoutube($value);
            }
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return self::normalizeUrl($value);
            }
        }

        return null;
    }

    private static function isAttachmentArray(array $value): bool
    {
        return isset($value['ID']) || isset($value['id']);
    }

    private static function normalizeAttachmentArray(array $value): ?array
    {
        $id = (int) ($value['ID'] ?? $value['id'] ?? 0);

        return $id > 0 ? self::normalizeAttachmentId($id) : null;
    }

    private static function normalizeAttachmentId(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        if (wp_attachment_is_image($id)) {
            return self::normalizeImage($id);
        }

        $mime = (string) get_post_mime_type($id);
        if (str_starts_with($mime, 'image/')) {
            return self::normalizeImage($id);
        }

        return self::normalizeVideo($id);
    }

    private static function normalizeUrl(string $url): ?array
    {
        if (self::youtubeId($url) !== null) {
            return self::normalizeYoutube($url);
        }

        $mime = wp_check_filetype($url)['type'] ?? '';
        if (str_starts_with($mime, 'video/')) {
            return [
                'type' => 'video',
                'id' => 0,
                'url' => $url,
                'mime' => $mime,
            ];
        }

        return null;
    }

    private static function normalizeLayout(array $row): ?array
    {
        $layout = $row['acf_fc_layout'] ?? '';

        // Legacy : ID image à la place du nom de layout (migration Image → FlexibleContent)
        if (is_numeric($layout)) {
            return self::normalizeAttachmentId((int) $layout);
        }

        return match ($layout) {
            'image' => self::normalizeImage((int) ($row['file'] ?? $row['image'] ?? 0))
                ?? self::normalize($row['file'] ?? $row['image'] ?? null),
            'video' => self::normalizeVideo((int) ($row['file'] ?? 0))
                ?? self::normalize($row['file'] ?? null),
            'youtube' => self::normalizeYoutube((string) ($row['url'] ?? '')),
            default => null,
        };
    }

    private static function normalizeImage(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        if (wp_attachment_is_image($id)) {
            return ['type' => 'image', 'id' => $id];
        }

        $mime = (string) get_post_mime_type($id);
        if (str_starts_with($mime, 'image/')) {
            return ['type' => 'image', 'id' => $id];
        }

        return null;
    }

    private static function normalizeVideo(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $mime = (string) get_post_mime_type($id);
        if ($mime !== '' && !str_starts_with($mime, 'video/')) {
            return null;
        }

        $url = wp_get_attachment_url($id);
        if (!$url) {
            return null;
        }

        return [
            'type' => 'video',
            'id' => $id,
            'url' => $url,
            'mime' => $mime ?: 'video/mp4',
        ];
    }

    private static function normalizeYoutube(string $url): ?array
    {
        $id = self::youtubeId($url);
        if ($id === null) {
            return null;
        }

        return [
            'type' => 'youtube',
            'url' => $url,
            'id' => $id,
            'embed_url' => 'https://www.youtube-nocookie.com/embed/' . $id,
        ];
    }

    public static function youtubeId(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtube\.com/embed/|youtu\.be/)([a-zA-Z0-9_-]{11})~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function lightboxUrl(mixed $media): ?string
    {
        $normalized = self::normalize($media);
        if ($normalized === null || $normalized['type'] !== 'image') {
            return null;
        }

        return wp_get_attachment_image_url($normalized['id'], 'full') ?: null;
    }

    public static function youtubeEmbedUrl(array $media, bool $cover = false): string
    {
        $params = [
            'rel' => '0',
            'modestbranding' => '1',
        ];

        if ($cover) {
            $params['autoplay'] = '1';
            $params['mute'] = '1';
            $params['loop'] = '1';
            $params['playlist'] = $media['id'];
            $params['controls'] = '0';
            $params['playsinline'] = '1';
        }

        return add_query_arg($params, $media['embed_url']);
    }
}
