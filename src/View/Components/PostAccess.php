<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PostAccess extends Component
{
    public \WP_Post $post;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($post = null)
    {
        if ($post instanceof \WP_Post) {
            $this->post = $post;
        } else {
            $this->post = get_post($post);
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.post');
    }
}

