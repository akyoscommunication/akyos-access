@if(!empty($items))
  <section class="s-faq" style="{{ $styles }}" @if(!empty($classes)) animation-background="{{ $classes }}" @endif>
    <div class="container">
      @if(!empty($title) || !empty($description))
        <x-title_text name="s-faq" :title="$title ?? null" :description="$description ?? null"/>
      @endif

      <div class="s-faq-list">
        @foreach($items as $index => $item)
          @include('akyos-access::partials.faq-item', [
            'question' => $item['question'],
            'answer' => $item['answer'],
            'open' => !empty($open_first) && $index === 0,
          ])
        @endforeach
      </div>

      @if(\Akyos\Access\Acf\Fields\ButtonAccess::hasLink($button ?? null))
        <div class="s-faq-footer">
          <x-button
            href="{{ $button['link']['url'] }}"
            target="{{ $button['link']['target'] }}"
            appearance="{{ $button['color'] }}"
            icon="{{ $button['icon'] ?? null }}"
          >
            {!! $button['link']['title'] !!}
          </x-button>
        </div>
      @endif
    </div>

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
  </section>
@endif
