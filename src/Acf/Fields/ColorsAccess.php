<?php

namespace Akyos\Access\Acf\Fields;

use Extended\ACF\Fields\Select;

class ColorsAccess
{
    public static function make(string $label, string $id)
    {
        return Select::make($label, $id)->choices([
            'primary' => 'Couleur primaire',
            'secondary' => 'Couleur secondaire',
            'light'  => 'Blanc',
            'dark' => 'Noir',
        ])
            ->default('primary');
    }
}
