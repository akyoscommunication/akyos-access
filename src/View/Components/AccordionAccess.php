<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AccordionAccess extends Component
{

    public $title;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title = null)
    {
        $this->title = $title;
    }

    public function render()
    {
        return view('components.accordion');
    }
}
