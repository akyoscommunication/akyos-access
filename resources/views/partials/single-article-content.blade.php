@php
  use Akyos\Access\Support\SinglePostHelper;

  $enhanced = $enhanced ?? SinglePostHelper::isEnabled();
  $repeater = get_field('repeater_content', get_the_ID());
  $data = $enhanced ? SinglePostHelper::prepare(get_the_ID()) : ['toc' => [], 'processed_contents' => []];
  $rows = $enhanced ? $data['processed_contents'] : (is_array($repeater) ? $repeater : []);
  $hasToc = $enhanced && count($data['toc']) > 0;
@endphp

@if(!empty($rows))
  <div class="container container--sm">
    @if($enhanced)
      <div class="single-content__meta">
        <time datetime="{{ get_the_date('c') }}">{{ get_the_date('d/m/Y') }}</time>
      </div>
    @elseif($showDate ?? false)
      <p class="single-content__date">{!! get_the_date('d/m/Y') !!}</p>
    @endif

    @if($enhanced)
      <div class="single-content__wrapper">
        @if($hasToc)
          <nav class="single-content__toc js-toc" aria-label="Sommaire">
            <p class="single-content__toc-title">Sommaire</p>
            <ul class="single-content__toc-list">
              @foreach($data['toc'] as $item)
                <li>
                  <a class="single-content__toc-link js-toc-link" href="#{{ $item['id'] }}">{!! $item['label'] !!}</a>
                </li>
              @endforeach
            </ul>
          </nav>
        @endif

        <div class="single-content__body">
          @foreach($rows as $el)
            <div class="single-content__text">
              {!! $el['content'] !!}
            </div>

            @if(!empty($el['images']))
              <div class="single-content__images">
                @foreach($el['images'] as $image)
                  <x-image :lg="$image"/>
                @endforeach
              </div>
            @endif
          @endforeach
        </div>
      </div>
    @else
      @foreach($rows as $el)
        <div class="single-content__text">
          {!! $el['content'] ?? '' !!}
        </div>

        @if(!empty($el['images']))
          <div class="single-content__images">
            @foreach($el['images'] as $image)
              <x-image :lg="$image"/>
            @endforeach
          </div>
        @endif
      @endforeach
    @endif
  </div>

  @if($enhanced)
    <script type="application/ld+json">{!! json_encode(
      SinglePostHelper::articleSchema(get_the_ID()),
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ) !!}</script>
  @endif
@endif
