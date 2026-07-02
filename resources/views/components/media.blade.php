@props([
  'media' => null,
  'cover' => false,
  'variant' => 'image',
  'sm' => null,
  'md' => null,
])

@php
  use Akyos\Access\Support\MediaHelper;

  $normalized = MediaHelper::normalize($media);
  $cover = !empty($cover);
  $variant = $variant ?? 'image';
  $htmlAttributes = isset($attributes) && $attributes instanceof \Illuminate\View\ComponentAttributeBag
    ? $attributes
    : new \Illuminate\View\ComponentAttributeBag();
@endphp

@if($normalized && $normalized['type'] === 'image')
  @include('akyos-access::components.image', [
    'lg' => $normalized['id'],
    'sm' => $sm,
    'md' => $md,
    'variant' => $variant,
    'attributes' => $htmlAttributes,
  ])
@elseif($normalized && $normalized['type'] === 'video')
  <div {{ $htmlAttributes->merge(['class' => 'c-media c-media--video' . ($cover ? ' c-media--cover' : '')]) }}>
    <video
      @if($cover) autoplay muted loop playsinline @else controls playsinline @endif
      preload="{{ $cover ? 'auto' : 'metadata' }}"
    >
      <source src="{{ esc_url($normalized['url']) }}" type="{{ esc_attr($normalized['mime']) }}">
    </video>
  </div>
@elseif($normalized && $normalized['type'] === 'youtube')
  <div {{ $htmlAttributes->merge(['class' => 'c-media c-media--youtube' . ($cover ? ' c-media--cover' : '')]) }}>
    <iframe
      src="{{ esc_url(MediaHelper::youtubeEmbedUrl($normalized, $cover)) }}"
      title="Vidéo YouTube"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
      allowfullscreen
      loading="lazy"
    ></iframe>
  </div>
@else
  @include('akyos-access::components.image', [
    'lg' => null,
    'sm' => $sm,
    'md' => $md,
    'variant' => $variant,
    'attributes' => $htmlAttributes,
  ])
@endif
