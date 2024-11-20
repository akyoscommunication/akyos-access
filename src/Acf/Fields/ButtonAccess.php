<?php

namespace Akyos\Access\Acf\Fields;

use App\Acf\Fields\Colors;
use Extended\ACF\Fields\ButtonGroup;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Select;

class ButtonAccess
{
    public static function make(string $label, string $id, $layout = 'table'): Group
    {
        return Group::make($label, $id)->fields([
            Link::make('Lien', 'link'),
            Colors::make('Couleur', 'color'),
            Image::make('Icône', 'icon')->format('id'),
        ])->layout($layout);
    }
}
