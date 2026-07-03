<article class="s-reviews-item">
  @if(!empty($review['photo_url']))
    <div class="s-reviews-item__avatar">
      <img
        src="{{ $review['photo_url'] }}"
        alt="{{ $review['photo_alt'] ?: $review['author'] }}"
        width="64"
        height="64"
        loading="lazy"
        decoding="async"
      >
    </div>
  @endif

  <div class="s-reviews-item__body">
    @if(!empty($show_stars) && !empty($review['rating']))
      <div class="s-reviews-item__stars" aria-label="Note : {{ $review['rating'] }} sur 5">
        @for($i = 1; $i <= 5; $i++)
          <span @class(['s-reviews-item__star', 'is-filled' => $i <= $review['rating']]) aria-hidden="true">★</span>
        @endfor
      </div>
    @endif

    @if(!empty($review['text']))
      <blockquote class="s-reviews-item__text">{!! $review['text'] !!}</blockquote>
    @endif

    <footer class="s-reviews-item__meta">
      @if(!empty($review['author']))
        <cite class="s-reviews-item__author">{{ $review['author'] }}</cite>
      @endif
      @if(!empty($review['date']))
        <time class="s-reviews-item__date" datetime="{{ $review['date'] }}">
          {{ wp_date('j F Y', strtotime($review['date'])) }}
        </time>
      @endif
    </footer>
  </div>
</article>
