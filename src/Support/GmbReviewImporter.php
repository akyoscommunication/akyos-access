<?php

namespace Akyos\Access\Support;

class GmbReviewImporter
{
    /**
     * @param list<array<string, mixed>> $reviews
     *
     * @return list<array{gmb_id: string, author: string, rating: int, text: string, date: string, photo: int|null}>
     */
    public static function import(array $reviews): array
    {
        self::loadMediaDeps();

        $imported = [];

        foreach ($reviews as $review) {
            if (!is_array($review)) {
                continue;
            }

            $author = trim((string) ($review['author'] ?? ''));
            $photoUrl = trim((string) ($review['photo_url'] ?? ''));

            $imported[] = [
                'gmb_id' => (string) ($review['gmb_id'] ?? ''),
                'author' => $author,
                'rating' => max(1, min(5, (int) ($review['rating'] ?? 5))),
                'text' => trim((string) ($review['text'] ?? '')),
                'date' => trim((string) ($review['date'] ?? '')),
                'photo' => self::sideloadPhoto($photoUrl, $author),
            ];
        }

        return $imported;
    }

    public static function sideloadPhoto(string $url, string $author): ?int
    {
        if ($url === '') {
            return null;
        }

        self::loadMediaDeps();

        $attachmentId = media_sideload_image(
            $url,
            0,
            $author !== '' ? sprintf('Photo avis — %s', $author) : 'Photo avis client',
            'id'
        );

        if (is_wp_error($attachmentId)) {
            return null;
        }

        return (int) $attachmentId;
    }

    private static function loadMediaDeps(): void
    {
        if (!function_exists('media_sideload_image')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
    }
}
