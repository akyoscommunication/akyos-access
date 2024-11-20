<?php

namespace Akyos\Access\Acf\Fields;

use app\Acf\Fields\Position;
use Extended\ACF\Fields\ButtonGroup;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Textarea;

class TitleAccess
{
    public static function make(string $label, string $id, $layout = 'table'): Group
    {
        return Group::make($label, $id)->fields([
            Textarea::make('Valeur', 'value')
                ->rows(2)
                ->newLines('br'),
            ButtonGroup::make('Balise', 'tag')->choices([
                'h1' => 'h1',
                'h2' => 'h2',
                'h3' => 'h3',
            ]),
            Position::make('Position', 'position')->default('left'),
        ])->layout($layout);
    }
}
