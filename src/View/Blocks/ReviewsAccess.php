<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\ButtonAccess;
use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\DatePicker;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Fields\WYSIWYGEditor;

class ReviewsAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Avis clients - Access')
            ->setName('reviews-access')
            ->setDescription('Avis clients manuels ou importés depuis Google My Business')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            Tab::make('En-tête', 'header'),
            TitleAccess::make('Titre', 'title'),
            WYSIWYGEditor::make('Introduction', 'description'),

            Tab::make('Google My Business', 'gmb'),
            Text::make('Clé API Google Places', 'gmb_api_key')
                ->helperText('Optionnel — requis uniquement pour importer depuis Google. Activez Places API dans Google Cloud.'),
            Text::make('Place ID Google', 'gmb_place_id')
                ->helperText('Rempli automatiquement via la recherche, ou saisi manuellement.'),
            Message::make('Import Google', 'gmb_import_ui')
                ->body('<div class="akyos-gmb-importer" data-akyos-gmb-importer><p>Recherchez votre établissement, récupérez les avis Google, sélectionnez ceux à importer, puis éditez-les librement dans l’onglet Avis.</p></div>'),

            Tab::make('Avis', 'reviews_tab'),
            Repeater::make('Avis', 'reviews')
                ->fields([
                    Image::make('Photo', 'photo')->format('id'),
                    Text::make('Auteur', 'author'),
                    Number::make('Note', 'rating')->min(1)->max(5)->step(1),
                    DatePicker::make('Date', 'date')->displayFormat('d/m/Y')->format('Y-m-d'),
                    Textarea::make('Avis', 'text')->rows(4)->newLines('br'),
                    Text::make('ID Google', 'gmb_id')
                        ->helperText('Rempli automatiquement à l’import — sert à éviter les doublons.'),
                ])
                ->layout('block')
                ->collapsed('author'),

            Tab::make('Options', 'options'),
            TrueFalse::make('Afficher les étoiles Google', 'show_stars')
                ->default(true),
            ButtonAccess::make('Bouton', 'button'),
        ];
    }

    public function data(): void
    {
        $this->description = FaqAccess::formatRichText($this->description ?? '');

        $items = is_array($this->reviews ?? null) ? $this->reviews : [];
        $normalized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $author = trim((string) ($item['author'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));

            if ($author === '' && $text === '') {
                continue;
            }

            $photoId = (int) ($item['photo'] ?? 0);
            $rating = max(0, min(5, (int) ($item['rating'] ?? 0)));

            $normalized[] = [
                'author' => $author,
                'text' => nl2br(esc_html($text)),
                'rating' => $rating,
                'date' => trim((string) ($item['date'] ?? '')),
                'photo_id' => $photoId,
                'photo_url' => $photoId > 0 ? (wp_get_attachment_image_url($photoId, 'thumbnail') ?: '') : '',
                'photo_alt' => $photoId > 0 ? (string) get_post_meta($photoId, '_wp_attachment_image_alt', true) : '',
            ];
        }

        $this->reviews = $normalized;
        $this->show_stars = !isset($this->show_stars) || !empty($this->show_stars);

        $ratings = array_filter(array_column($normalized, 'rating'));
        $this->aggregate_rating = $ratings !== []
            ? round(array_sum($ratings) / count($ratings), 1)
            : null;

        $this->schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => array_values(array_map(static function (array $item, int $index): array {
                $review = [
                    '@type' => 'Review',
                    'position' => $index + 1,
                    'author' => [
                        '@type' => 'Person',
                        'name' => $item['author'],
                    ],
                    'reviewBody' => wp_strip_all_tags($item['text']),
                ];

                if ($item['rating'] > 0) {
                    $review['reviewRating'] = [
                        '@type' => 'Rating',
                        'ratingValue' => $item['rating'],
                        'bestRating' => 5,
                    ];
                }

                if ($item['date'] !== '') {
                    $review['datePublished'] = $item['date'];
                }

                return $review;
            }, $normalized, array_keys($normalized))),
        ];
    }

    public function render()
    {
        return view('akyos-access::blocks.reviews-access');
    }
}
