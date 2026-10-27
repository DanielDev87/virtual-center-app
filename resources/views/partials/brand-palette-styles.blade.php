@php($brandPaletteCss = \App\Services\BrandPaletteService::css())
@if($brandPaletteCss !== '')
<style id="brand-palette-styles">{!! $brandPaletteCss !!}</style>
@endif