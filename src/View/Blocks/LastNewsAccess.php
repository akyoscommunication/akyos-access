<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\ButtonAccess;
use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\WYSIWYGEditor;

class LastNewsAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Dernières actualités - Access')
            ->setName('last-news-access')
            ->setDescription('Dernières actualités')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            TitleAccess::make('Titre', 'title'),
            WYSIWYGEditor::make('Description', 'description'),
            ButtonAccess::make('Bouton', 'button'),
            Message::make('Message', 'message')->body('Affiche les 4 dernières actualités. Les actualités sont modifiables dans l\'onglet Article du Back Office')
        ];
    }

    public function render()
    {
        return view('blocks.last-news-access');
    }
}
