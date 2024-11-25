<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\ButtonAccess;
use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\ButtonGroup;
use Extended\ACF\Fields\Gallery;
use Extended\ACF\Fields\WYSIWYGEditor;

class TextImageInlineAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Texte et images alignées - Access')
            ->setName('text-image-inline-access')
            ->setDescription('Texte et images alignées')
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
                    'first' => 'Contenu / Image / Image',
                    'second' => 'Image / Contenu / Image',
                    'third' => 'Image / Image / Contenu'
                ])->default('first'),
        ];
    }

    public function render()
    {
        return view('blocks.text-image-inline-access');
    }
}
