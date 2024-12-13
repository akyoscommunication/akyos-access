<?php

namespace Akyos\Access\View\Components;

use Illuminate\View\Component;

class TitleAccess extends Component
{
    public $tag;

    public function __construct($tag = 'h1')
    {
        $this->tag = $tag;
    }

    public function render()
    {
        return view('components.title');
    }
}
