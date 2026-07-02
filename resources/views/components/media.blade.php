@props([
  'media' => null,
  'cover' => false,
  'variant' => 'image',
  'sm' => null,
  'md' => null,
  'rounded' => false,
])

@php
  use Akyos\Access\Support\MediaHelper;

  $rawAttributes = $attributes ?? null;
  $media = MediaHelper::bladeProp($media ?? null, $rawAttributes, 'media');
  $cover = (bool) MediaHelper::bladeProp($cover ?? null, $rawAttributes, 'cover', false);
  $variant = MediaHelper::bladeProp($variant ?? null, $rawAttributes, 'variant', 'image') ?? 'image';
  $rounded = (bool) MediaHelper::bladeProp($rounded ?? null, $rawAttributes, 'rounded', false);
  $htmlAttributes = MediaHelper::htmlAttributes($rawAttributes);

  $normalized = MediaHelper::normalize($media);
@endphp

@if($normalized && $normalized['type'] === 'image')
  @include('akyos-access::components.image', [
    'lg' => $normalized['id'],
    'sm' => $sm,
    'md' => $md,
    'variant' => $variant,
    'rounded' => $rounded,
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
    'rounded' => $rounded,
    'attributes' => $htmlAttributes,
  ])
@endif
