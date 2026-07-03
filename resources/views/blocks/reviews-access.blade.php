@if(!empty($reviews))
  <section class="s-reviews" style="{{ $styles }}" @if(!empty($classes)) animation-background="{{ $classes }}" @endif>
    <div class="container">
      @if(!empty($title) || !empty($description) || !empty($aggregate_rating))
        <div class="s-reviews__header">
          @if(!empty($title) || !empty($description))
            <x-title_text name="s-reviews" :title="$title ?? null" :description="$description ?? null"/>
          @endif

          @if(!empty($aggregate_rating))
            <p class="s-reviews__aggregate">
              @if(!empty($show_stars))
                <span class="s-reviews__stars" aria-hidden="true">{!! str_repeat('★', (int) round($aggregate_rating)) !!}</span>
              @endif
              <span class="s-reviews__score">{{ number_format($aggregate_rating, 1, ',', ' ') }}/5</span>
              <span class="s-reviews__count">({{ count($reviews) }} avis affichés)</span>
            </p>
          @endif
        </div>
      @endif

      <div class="s-reviews-list">
        <x-slider
          name="reviews"
          :per="3"
          :perMd="2"
          :perSm="1"
          :perXs="1"
          :modules="['navigation']"
          :extra="['spaceBetween' => 24]"
        >
          @foreach($reviews as $review)
            <div class="swiper-slide">
              @include('akyos-access::partials.review-item', ['review' => $review, 'show_stars' => !empty($show_stars)])
            </div>
          @endforeach
        </x-slider>
      </div>

      @if(\Akyos\Access\Acf\Fields\ButtonAccess::hasLink($button ?? null))
        <div class="s-reviews-footer">
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
