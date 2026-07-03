<?php

namespace Akyos\Access\Admin;

use Akyos\Access\Support\GmbReviewImporter;
use Akyos\Access\Support\GooglePlacesClient;

class GmbReviewsAjax
{
    public static function register(): void
    {
        add_action('wp_ajax_akyos_access_fetch_gmb_reviews', [self::class, 'fetch']);
        add_action('wp_ajax_akyos_access_search_places', [self::class, 'search']);
        add_action('wp_ajax_akyos_access_import_gmb_reviews', [self::class, 'import']);
    }

    public static function fetch(): void
    {
        self::authorize();

        try {
            $client = new GooglePlacesClient(self::resolveApiKey());
            wp_send_json_success($client->fetchReviews(self::resolvePlaceId()));
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 400);
        }
    }

    public static function search(): void
    {
        self::authorize();

        $query = sanitize_text_field(wp_unslash($_POST['query'] ?? ''));

        try {
            $client = new GooglePlacesClient(self::resolveApiKey());
            wp_send_json_success(['places' => $client->searchPlaces($query)]);
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 400);
        }
    }

    public static function import(): void
    {
        self::authorize();

        $raw = json_decode((string) wp_unslash($_POST['reviews'] ?? '[]'), true);

        if (!is_array($raw) || $raw === []) {
            wp_send_json_error(['message' => 'Aucun avis sélectionné.'], 400);
        }

        wp_send_json_success([
            'reviews' => GmbReviewImporter::import($raw),
        ]);
    }

    private static function authorize(): void
    {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission refusée.'], 403);
        }

        check_ajax_referer('akyos_access_gmb', 'nonce');
    }

    private static function resolveApiKey(): string
    {
        return trim(sanitize_text_field(wp_unslash($_POST['api_key'] ?? '')));
    }

    private static function resolvePlaceId(): string
    {
        return trim(sanitize_text_field(wp_unslash($_POST['place_id'] ?? '')));
    }
}
