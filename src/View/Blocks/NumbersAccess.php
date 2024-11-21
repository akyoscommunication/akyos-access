<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Fields\WYSIWYGEditor;

class NumbersAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Chiffres clés - Access')
            ->setName('numbers-access')
            ->setDescription('Chiffres clés')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            TitleAccess::make('Titre', 'title'),
            WYSIWYGEditor::make('Description', 'description'),
            Repeater::make('Nombres', 'numbers')
                ->fields([
                    Image::make('Image', 'image')->format('id'),
                    Text::make('Nombre', 'number'),
                    Textarea::make('Description', 'description')
                ])
        ];
    }

    public function render()
    {
        return view('blocks.numbers-access');
    }
}
