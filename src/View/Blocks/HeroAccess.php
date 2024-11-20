<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;

class HeroAccess extends Block
{
    protected static function block(): GutenbergBlock
    {
        return (new GutenbergBlock())
            ->setTitle('Entête - Access')
            ->setName('hero-access')
            ->setDescription('Entête de la page')
            ->setCategory('header');
    }

    protected static function fields(): array
    {
        return [];
    }

    public function render()
    {
        return view('blocks.hero-access');
    }
}
