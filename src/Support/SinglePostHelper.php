<?php

namespace Akyos\Access\Support;

class SinglePostHelper
{
    public static function isEnabled(): bool
    {
        return (bool) get_field('single_post_enhanced', 'option');
    }

    /** @return array{toc: list<array{id: string, label: string}>, processed_contents: list<array{content: string, images: mixed}>} */
    public static function prepare(int $postId): array
    {
        $repeater = get_field('repeater_content', $postId);
        if (!is_array($repeater)) {
            return ['toc' => [], 'processed_contents' => []];
        }

        $toc = [];
        $headingIndex = 0;
        $processed = [];

        foreach ($repeater as $row) {
            if (!is_array($row)) {
                continue;
            }

            $content = (string) ($row['content'] ?? '');
            if ($content === '') {
                continue;
            }

            $tocBefore = count($toc);

            $content = preg_replace_callback(
                '/<h([23])([^>]*)>(.*?)<\/h\1>/is',
                static function (array $match) use (&$toc, &$headingIndex): string {
                    $headingIndex++;
                    $id = 'section-' . $headingIndex;
                    $toc[] = ['id' => $id, 'label' => trim(strip_tags($match[3]))];

                    if (preg_match('/\bid\s*=/', $match[2])) {
                        return '<h' . $match[1] . $match[2] . '>' . $match[3] . '</h' . $match[1] . '>';
                    }

                    return '<h' . $match[1] . $match[2] . ' id="' . esc_attr($id) . '">' . $match[3] . '</h' . $match[1] . '>';
                },
                $content
            ) ?? $content;

            // ponytail: fallback = 1 entrée sommaire par bloc répéteur
            if (count($toc) === $tocBefore) {
                $headingIndex++;
                $id = 'section-' . $headingIndex;
                $toc[] = ['id' => $id, 'label' => self::rowTocLabel($content, $headingIndex)];
                $content = self::injectSectionId($content, $id);
            }

            $processed[] = [
                'content' => $content,
                'images' => $row['images'] ?? null,
            ];
        }

        return ['toc' => $toc, 'processed_contents' => $processed];
    }

    private static function rowTocLabel(string $content, int $sectionIndex): string
    {
        if (preg_match('/<strong[^>]*>(.*?)<\/strong>/is', $content, $match)) {
            return self::truncateLabel(trim(strip_tags($match[1])));
        }

        $text = trim(strip_tags($content));
        if ($text !== '') {
            return self::truncateLabel(wp_trim_words($text, 8, '…'));
        }

        return 'Section ' . $sectionIndex;
    }

    private static function truncateLabel(string $label): string
    {
        if (mb_strlen($label) <= 80) {
            return $label;
        }

        return mb_substr($label, 0, 77) . '…';
    }

    private static function injectSectionId(string $content, string $id): string
    {
        if (preg_match('/\bid="' . preg_quote($id, '/') . '"/', $content)) {
            return $content;
        }

        if (preg_match('/<h[23][^>]*\bid\s*=/i', $content)) {
            return $content;
        }

        if (preg_match('/<h[23]([^>]*)>/i', $content)) {
            return preg_replace(
                '/<h([23])([^>]*)>/',
                '<h$1$2 id="' . esc_attr($id) . '">',
                $content,
                1
            ) ?? $content;
        }

        return preg_replace(
            '/<p([^>]*)>/',
            '<p$1 id="' . esc_attr($id) . '">',
            $content,
            1
        ) ?? $content;
    }

    /** @return array<string, mixed> */
    public static function articleSchema(int $postId): array
    {
        $thumbnail = get_the_post_thumbnail_url($postId, 'full');

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => wp_strip_all_tags(get_the_title($postId)),
            'datePublished' => get_the_date('c', $postId),
            'dateModified' => get_the_modified_date('c', $postId),
            'author' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
            ],
            'mainEntityOfPage' => get_permalink($postId),
            'image' => $thumbnail ?: null,
        ], static fn ($value) => $value !== null && $value !== '');
    }
}
