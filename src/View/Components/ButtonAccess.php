<?php

namespace Akyos\Access\View\Components;

use Illuminate\View\Component;

class ButtonAccess extends Component
{
    public $appearance;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($appearance = null)
    {
        $this->appearance = $appearance;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.button');
    }
}
