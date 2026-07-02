@props([
  'lg' => null,
  'sm' => null,
  'md' => null,
  'variant' => 'image',
])

@php
  use Akyos\Access\Support\MediaHelper;
  use function Akyos\Core\Helpers\attachment_image_url;
  use function Akyos\Core\Helpers\default_image_url;
  use function Akyos\Core\Helpers\attachment_image_html;

  $variant = $variant ?? 'image';
  $fallback = default_image_url($variant);
  $lgId = MediaHelper::attachmentId($lg);
  $smId = !empty($sm) ? MediaHelper::attachmentId($sm) : null;
  $mdId = !empty($md) ? MediaHelper::attachmentId($md) : null;
  $lgUrl = attachment_image_url($lgId, 'full', $variant);
  $smUrl = $smId ? attachment_image_url($smId, 'full', $variant) : null;
  $mdUrl = $mdId ? attachment_image_url($mdId, 'full', $variant) : null;
  $title = $lgId ? (string) get_the_title($lgId) : '';
  $htmlAttributes = isset($attributes) && $attributes instanceof \Illuminate\View\ComponentAttributeBag
    ? $attributes
    : new \Illuminate\View\ComponentAttributeBag();
@endphp

<picture {{ $htmlAttributes->merge(['class' => 'c-image']) }} @if($title !== '') tooltip="{{ $title }}" @endif>
  @if($smUrl && $smUrl !== $fallback)
    <source media="(max-width: 700px)" srcset="{{ $smUrl }}">
  @endif
  @if($mdUrl && $mdUrl !== $fallback)
    <source media="(min-width: 1050px)" srcset="{{ $mdUrl }}">
  @endif
  {!! attachment_image_html($lgId, 'full', [], $variant) !!}
</picture>
