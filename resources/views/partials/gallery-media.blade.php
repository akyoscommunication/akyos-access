@php
  use Akyos\Access\Support\MediaHelper;
  $lightbox = MediaHelper::lightboxUrl($media ?? null);
@endphp

@if($lightbox)
  <a href="{{ $lightbox }}" class="glightbox s-gallery-item">
@else
  <div class="s-gallery-item">
@endif
  @include('akyos-access::components.media', get_defined_vars())
@if($lightbox)
  </a>
@else
  </div>
@endif
