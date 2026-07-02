<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\MediaAccess;
use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Access\Support\MediaHelper;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
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
            MediaAccess::repeater('Galerie', 'gallery', 6),
        ];
    }

    public function data()
    {
        $this->gallery = MediaHelper::normalizeListWithPlaceholders($this->gallery ?? null, 6);
    }

    public function render()
    {
        return view('blocks.gallery-access');
    }
}
