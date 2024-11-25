<?php

namespace Akyos\Access\View\Components;

use Illuminate\View\Component;

class TitleAccess extends Component
{
    public $tag;
    public $position;

    public function __construct($tag = 'h1', $position = 'left')
    {
        $this->tag = $tag;
        $this->position = $position;
    }

    public function render()
    {
        return view('components.title');
    }
}
