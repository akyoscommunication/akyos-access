<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\WYSIWYGEditor;

class TeamAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Équipe - Access')
            ->setName('team-access')
            ->setDescription('Équipe')
            ->setCategory('content');
    }

    protected static function fields(): array
    {
        return [
            TitleAccess::make('Titre', 'title'),
            WYSIWYGEditor::make('Description', 'description'),
            Repeater::make('Equipe', 'teams')
                ->fields([
                    Image::make('Image', 'image')->format('id'),
                    Text::make('Nom', 'name'),
                    Text::make('Fonction', 'job')
                ])
        ];
    }

    public function render()
    {
        return view('blocks.team-access');
    }
}
