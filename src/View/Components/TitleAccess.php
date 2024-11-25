<?php

namespace Akyos\Access\View\Components;

use Illuminate\View\Component;

class TitleAccess extends Component
{
    public $tag;
    public $appearance;
    public $position;

    public function __construct($tag = 'h1', $appearance = null, $position = 'left')
    {
        $this->tag = $tag;
        $this->appearance = $appearance;
        $this->position = $position;
    }

    public function render()
    {
        return view('components.title');
    }
}
