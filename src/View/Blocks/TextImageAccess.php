<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\ButtonAccess;
use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\ButtonGroup;
use Extended\ACF\Fields\Gallery;
use Extended\ACF\Fields\WYSIWYGEditor;

class TextImageAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Texte et image - Access')
            ->setName('text-image-access')
            ->setDescription('Texte et image')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            TitleAccess::make('Titre', 'title'),
            WYSIWYGEditor::make('Texte', 'content'),
            ButtonAccess::make('Bouton', 'button'),
            Gallery::make('Images', 'images')->maxFiles(2)->format('id'),
            ButtonGroup::make('Position du contenu', 'position')
                ->choices([
                    'Image / Contenu' => 'default',
                    'Contenu / Image' => 'reverse'
                ])->default('default'),
        ];
    }

    public function render()
    {
        return view('blocks.text-image-access');
    }
}
