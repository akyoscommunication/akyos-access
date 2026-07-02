<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\MediaAccess;
use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\WYSIWYGEditor;

class BannerAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Bandeau - Access')
            ->setName('banner-access')
            ->setDescription('Bandeau')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            TitleAccess::make('Titre', 'title'),
            WYSIWYGEditor::make('Description', 'description'),
            Repeater::make('Elements', 'elements')
                ->fields([
                    MediaAccess::make('Média', 'image'),
                    Link::make('Lien', 'link'),
                ])
        ];
    }

    public function render()
    {
        return view('blocks.banner-access');
    }
}
