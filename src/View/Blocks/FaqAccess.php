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
        $items = is_array($this->items ?? null) ? $this->items : [];

        $this->items = array_values(array_filter($items, static function ($item): bool {
            if (!is_array($item)) {
                return false;
            }

            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim(wp_strip_all_tags((string) ($item['answer'] ?? '')));

            return $question !== '' && $answer !== '';
        }));

        $this->open_first = !empty($this->open_first);
        $this->schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static function (array $item): array {
                return [
                    '@type' => 'Question',
                    'name' => wp_strip_all_tags($item['question'] ?? ''),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => wp_strip_all_tags($item['answer'] ?? ''),
                    ],
                ];
            }, $this->items),
        ];
    }

    public function render()
    {
        return view('akyos-access::blocks.faq-access');
    }
}
