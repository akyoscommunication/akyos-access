<?php

namespace Akyos\Access\View\Components;

use Illuminate\View\Component;

class ImageAccess extends Component
{
    public $lg;
    public $sm;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($lg = null, $sm = null)
    {
        $this->lg = $lg;
        $this->sm = $sm;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.image');
    }
}
