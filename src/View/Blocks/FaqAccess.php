<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\ButtonAccess;
use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Fields\WYSIWYGEditor;

class FaqAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('FAQ - Access')
            ->setName('faq-access')
            ->setDescription('Questions fréquentes en accordéon (3 à 10)')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            Tab::make('En-tête', 'header'),
            TitleAccess::make('Titre', 'title'),
            WYSIWYGEditor::make('Introduction', 'description'),

            Tab::make('Questions', 'questions'),
            Repeater::make('Questions', 'items')
                ->fields([
                    Text::make('Question', 'question'),
                    WYSIWYGEditor::make('Réponse', 'answer'),
                ])
                ->layout('block')
                ->collapsed('question'),

            Tab::make('Options', 'options'),
            TrueFalse::make('Ouvrir la 1re question', 'open_first')
                ->default(false),
            ButtonAccess::make('Bouton', 'button'),
        ];
    }

    public function data()
    {
        $this->description = self::formatRichText($this->description ?? '');

        $items = is_array($this->items ?? null) ? $this->items : [];
        $decoded = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $decoded[] = [
                'question' => self::decodeRichText($item['question'] ?? ''),
                'answer' => self::formatRichText($item['answer'] ?? ''),
            ];
        }

        $this->items = array_values(array_filter($decoded, static function (array $item): bool {
            $question = trim($item['question']);
            $answer = trim(wp_strip_all_tags($item['answer']));

            return $question !== '' && $answer !== '';
        }));

        $this->open_first = !empty($this->open_first);
        $this->schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static function (array $item): array {
                return [
                    '@type' => 'Question',
                    'name' => wp_strip_all_tags($item['question']),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => wp_strip_all_tags($item['answer']),
                    ],
                ];
            }, $this->items),
        ];
    }

    /** Décode les séquences unicode du JSON de blocs Gutenberg (u003c → <). */
    public static function decodeRichText(mixed $value): string
    {
        if (!is_string($value) || $value === '') {
            return '';
        }

        $value = trim($value);

        // WYSIWYG ACF : <p>u003cpu003e…u003c/pu003e</p>
        if (preg_match('/^<p>(.+u003c.+)<\/p>$/s', $value, $match)) {
            $value = $match[1];
        }

        $value = preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            static fn (array $m): string => mb_convert_encoding(pack('H*', $m[1]), 'UTF-8', 'UCS-2BE'),
            $value
        );

        $value = str_replace(
            ['u003c', 'u003e', 'u0026', 'u0027', 'u0022', 'u00a0'],
            ['<', '>', '&', "'", '"', ' '],
            $value
        );

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // ponytail: double <p> résiduel après décodage
        $value = preg_replace('/^<p>\s*<p>/', '<p>', $value);
        $value = preg_replace('/<\/p>\s*<\/p>$/', '</p>', $value);

        return $value;
    }

    public static function formatRichText(mixed $value): string
    {
        $value = self::decodeRichText($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/<(p|ul|ol|li|h[1-6]|blockquote|div|table)\b/i', $value)) {
            return $value;
        }

        return wpautop($value);
    }

    public function render()
    {
        return view('akyos-access::blocks.faq-access');
    }
}
