<details class="c-accordion s-faq-item" accordion @if(!empty($open)) open @endif>
  <summary>
    <h3 class="c-accordion-trigger">{!! $question !!} @icon('chevron')</h3>
  </summary>
  <div class="c-accordion-content">
    {!! $answer !!}
  </div>
</details>
