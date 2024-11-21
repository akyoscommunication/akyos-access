<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\Gallery;
use Extended\ACF\Fields\WYSIWYGEditor;

class GalleryAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Galerie - Access')
            ->setName('gallery-access')
            ->setDescription('Galerie')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            TitleAccess::make('Titre', 'title'),
            WYSIWYGEditor::make('Description', 'description'),
            Gallery::make('Galerie', 'gallery')->maxFiles(6)->format('id'),
        ];
    }

    public function render()
    {
        return view('blocks.gallery-access');
    }
}
