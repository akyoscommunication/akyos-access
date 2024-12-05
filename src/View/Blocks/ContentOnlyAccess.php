<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\WYSIWYGEditor;

class ContentOnlyAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Contenu seul')
            ->setName('content-only-access')
            ->setCategory('content')
            ->setDescription('Bloque pour mettre du contenu');
    }

    protected static function fields(): array
    {
        return [
            WYSIWYGEditor::make('Contenu', 'content')
        ];
    }

    public function render()
    {
        return view('blocks.content-only-access');
    }
}
