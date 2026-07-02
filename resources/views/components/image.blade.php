@props([
  'lg' => null,
  'sm' => null,
  'md' => null,
  'variant' => 'image',
  'rounded' => false,
])

@php
  use Akyos\Access\Support\MediaHelper;
  use function Akyos\Core\Helpers\attachment_image_url;
  use function Akyos\Core\Helpers\default_image_url;
  use function Akyos\Core\Helpers\attachment_image_html;

  $rawAttributes = $attributes ?? null;
  $lg = MediaHelper::bladeProp($lg ?? null, $rawAttributes, 'lg');
  $sm = MediaHelper::bladeProp($sm ?? null, $rawAttributes, 'sm');
  $md = MediaHelper::bladeProp($md ?? null, $rawAttributes, 'md');
  $variant = MediaHelper::bladeProp($variant ?? null, $rawAttributes, 'variant', 'image') ?? 'image';
  $rounded = (bool) MediaHelper::bladeProp($rounded ?? null, $rawAttributes, 'rounded', false);
  $htmlAttributes = MediaHelper::htmlAttributes($rawAttributes);

  $fallback = default_image_url($variant);
  $lgId = MediaHelper::attachmentId($lg);
  $smId = !empty($sm) ? MediaHelper::attachmentId($sm) : null;
  $mdId = !empty($md) ? MediaHelper::attachmentId($md) : null;
  $lgUrl = attachment_image_url($lgId, 'full', $variant);
  $smUrl = $smId ? attachment_image_url($smId, 'full', $variant) : null;
  $mdUrl = $mdId ? attachment_image_url($mdId, 'full', $variant) : null;
  $title = $lgId ? (string) get_the_title($lgId) : '';
@endphp

<picture {{ $htmlAttributes->merge(['class' => 'c-image']) }} @if($rounded) rounded @endif @if($title !== '') tooltip="{{ $title }}" @endif>
  @if($smUrl && $smUrl !== $fallback)
    <source media="(max-width: 700px)" srcset="{{ $smUrl }}">
  @endif
  @if($mdUrl && $mdUrl !== $fallback)
    <source media="(min-width: 1050px)" srcset="{{ $mdUrl }}">
  @endif
  {!! attachment_image_html($lgId, 'full', [], $variant) !!}
</picture>
