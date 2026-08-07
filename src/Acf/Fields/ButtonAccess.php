<?php

namespace Akyos\Access\Acf\Fields;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Link;

class ButtonAccess
{
    public static function hasLink(mixed $button): bool
    {
        return is_array($button) && !empty($button['link']['url']);
    }

    public static function make(string $label, string $id, $layout = 'table'): Group
    {
        return Group::make($label, $id)->fields([
            Link::make('Lien', 'link'),
            ColorsAccess::make('Couleur', 'color'),
            Image::make('Icône', 'icon')->format('id'),
        ])->layout($layout);
    }
}
