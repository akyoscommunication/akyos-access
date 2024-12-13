<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\ButtonAccess;
use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Fields\WYSIWYGEditor;

class ServicesAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Services - Access')
            ->setName('services-access')
            ->setDescription('Liste des services')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            TitleAccess::make('Titre', 'title'),
            WYSIWYGEditor::make('Description', 'description'),
            ButtonAccess::make('Bouton', 'button'),

            Tab::make('Contenus'),
            Repeater::make('Services', 'services')
                ->fields([
                    TitleAccess::make('Titre', 'title'),
                    Image::make('Image', 'image')->format('id'),
                    Textarea::make('Description', 'description')->maxLength(200),
                    Link::make('Lien', 'link')
                ])->layout('block')->collapsed('title')
        ];
    }

    public function render()
    {
        return view('blocks.services-access');
    }
}
