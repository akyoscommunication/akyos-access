<?php

namespace Akyos\Access\Acf\Fields;

use Extended\ACF\Fields\Select;

class PositionAccess
{
    public static function make(string $label, string $id)
    {
        return Select::make($label, $id)->choices([
            'left' => 'Gauche',
            'center' => 'Centre',
            'right' => 'Droite',
        ])
            ->default('left');
    }
}
