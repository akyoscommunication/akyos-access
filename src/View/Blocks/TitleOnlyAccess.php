<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;

class TitleOnlyAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Titre - Access')
            ->setName('title-access')
            ->setDescription('Titre')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            TitleAccess::make('Titre', 'title'),
        ];
    }

    public function render()
    {
        return view('blocks.title-only-access');
    }
}
