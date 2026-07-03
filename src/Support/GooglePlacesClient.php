<?php

namespace Akyos\Access\Support;

class GooglePlacesClient
{
    public function __construct(private readonly string $apiKey)
    {
    }

    /**
     * @return list<array{place_id: string, name: string, address: string, rating: float|null, total: int|null}>
     */
    public function searchPlaces(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            throw new \InvalidArgumentException('Saisissez un nom ou une adresse.');
        }

        if ($this->apiKey === '') {
            throw new \InvalidArgumentException('Clé API Google Places manquante (onglet Google My Business du bloc).');
        }

        $url = add_query_arg([
            'query' => $query,
            'language' => 'fr',
            'key' => $this->apiKey,
        ], 'https://maps.googleapis.com/maps/api/place/textsearch/json');

        $body = $this->requestJson($url);
        $status = (string) ($body['status'] ?? 'UNKNOWN');

        if ($status !== 'OK' && $status !== 'ZERO_RESULTS') {
            $message = (string) ($body['error_message'] ?? $status);
            throw new \RuntimeException('Google Places : ' . $message);
        }

        $places = [];

        foreach ($body['results'] ?? [] as $result) {
            if (!is_array($result) || empty($result['place_id'])) {
                continue;
            }

            $places[] = [
                'place_id' => (string) $result['place_id'],
                'name' => (string) ($result['name'] ?? ''),
                'address' => (string) ($result['formatted_address'] ?? ''),
                'rating' => isset($result['rating']) ? (float) $result['rating'] : null,
                'total' => isset($result['user_ratings_total']) ? (int) $result['user_ratings_total'] : null,
            ];
        }

        return $places;
    }

    /**
     * @return array{place_name: string, rating: float|null, total: int|null, reviews: list<array<string, mixed>>}
     */
    public function fetchReviews(string $placeId): array
    {
        if ($placeId === '') {
            throw new \InvalidArgumentException('Sélectionnez un établissement Google (recherche ci-dessus).');
        }

        if ($this->apiKey === '') {
            throw new \InvalidArgumentException('Clé API Google Places manquante (onglet Google My Business du bloc).');
        }

        $url = add_query_arg([
            'place_id' => $placeId,
            'fields' => 'reviews,rating,user_ratings_total,name',
            'language' => 'fr',
            'key' => $this->apiKey,
        ], 'https://maps.googleapis.com/maps/api/place/details/json');

        $body = $this->requestJson($url);
        $status = (string) ($body['status'] ?? 'UNKNOWN');

        if ($status !== 'OK') {
            $message = (string) ($body['error_message'] ?? $status);
            throw new \RuntimeException('Google Places : ' . $message);
        }

        $result = is_array($body['result'] ?? null) ? $body['result'] : [];
        $reviews = [];

        foreach ($result['reviews'] ?? [] as $review) {
            if (!is_array($review)) {
                continue;
            }

            $normalized = self::normalizeReview($review);

            if ($normalized['author'] !== '' && $normalized['rating'] > 0) {
                $reviews[] = $normalized;
            }
        }

        return [
            'place_id' => $placeId,
            'place_name' => (string) ($result['name'] ?? ''),
            'rating' => isset($result['rating']) ? (float) $result['rating'] : null,
            'total' => isset($result['user_ratings_total']) ? (int) $result['user_ratings_total'] : null,
            'reviews' => $reviews,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function requestJson(string $url): array
    {
        $response = wp_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response)) {
            throw new \RuntimeException($response->get_error_message());
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if (!is_array($body)) {
            throw new \RuntimeException('Réponse Google Places invalide.');
        }

        return $body;
    }

    /**
     * @param array<string, mixed> $review
     *
     * @return array{gmb_id: string, author: string, rating: int, text: string, date: string, photo_url: string, relative_time: string}
     */
    public static function normalizeReview(array $review): array
    {
        $author = trim((string) ($review['author_name'] ?? ''));
        $time = isset($review['time']) ? (int) $review['time'] : 0;
        $text = trim((string) ($review['text'] ?? ''));

        return [
            'gmb_id' => hash('sha256', $author . '|' . $time . '|' . mb_substr($text, 0, 64)),
            'author' => $author,
            'rating' => max(0, min(5, (int) ($review['rating'] ?? 0))),
            'text' => $text,
            'date' => $time > 0 ? gmdate('Y-m-d', $time) : '',
            'photo_url' => trim((string) ($review['profile_photo_url'] ?? '')),
            'relative_time' => trim((string) ($review['relative_time_description'] ?? '')),
        ];
    }
}
