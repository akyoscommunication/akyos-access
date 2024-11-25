<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SliderAccess extends Component
{
    public $name;
    public $per;
    public $per_xs;
    public $per_sm;
    public $per_md;
    public $navigation;
    public $autoheight;
    public $gap;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name, $per, $persm, $permd, $perxs, $navigation, $autoheight = false, $gap = 20)
    {
        $this->name = $name;
        $this->per = $per;
        $this->navigation = $navigation;
        $this->per_sm = $persm;
        $this->per_md = $permd;
        $this->per_xs = $perxs;
        $this->autoheight = $autoheight;
        $this->gap = $gap;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.slider');
    }
}
