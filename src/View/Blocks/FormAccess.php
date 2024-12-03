<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\ButtonGroup;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\PostObject;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Textarea;

class FormAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Formulaire de contact - Access')
            ->setName('form-access')
            ->setDescription('Formulaire de contact')
            ->setCategory('form');
    }

    protected static function fields(): array
    {
        return [
            Tab::make('Contenu'),
            Textarea::make('Description', 'description')->newLines('br'),
            Textarea::make('Description courte', 'description_short')->newLines('br'),
            PostObject::make('Formulaire', 'form')->postTypes(['forminator_forms'])->format('id'),
            Image::make('Image', 'image')->format('id'),
            Tab::make('Options'),
            ButtonGroup::make('Position du contenu', 'content_position')
                ->choices([
                    'content-image' => 'Contenu / Image',
                    'image-content' => 'Image / Contenu',
                ])->default('content-image')
        ];
    }

    public function render()
    {
        return view('blocks.form-access');
    }
}
