@php
  use function Akyos\Core\Helpers\attachment_image_url;
  use function Akyos\Core\Helpers\default_image_url;
  use function Akyos\Core\Helpers\attachment_image_html;

  $variant = $variant ?? 'image';
  $fallback = default_image_url($variant);
  $lgId = $lg ?? null;
  $lgUrl = attachment_image_url($lgId, 'full', $variant);
  $smUrl = !empty($sm) ? attachment_image_url($sm, 'full', $variant) : null;
  $mdUrl = !empty($md) ? attachment_image_url($md, 'full', $variant) : null;
  $title = $lgId ? get_the_title($lgId) : '';
@endphp

<picture {{ $attributes->merge(['class' => 'c-image']) }} @if($title) tooltip="{{ $title }}" @endif>
  @if($smUrl && $smUrl !== $fallback)
    <source media="(max-width: 700px)" srcset="{{ $smUrl }}">
  @endif
  @if($mdUrl && $mdUrl !== $fallback)
    <source media="(min-width: 1050px)" srcset="{{ $mdUrl }}">
  @endif
  {!! attachment_image_html($lgId, 'full', [], $variant) !!}
</picture>
