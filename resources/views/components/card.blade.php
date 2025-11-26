@props(['title' => '', 'subtitle' => '', 'class' => '', 'headerClass' => '', 'bodyClass' => '', 'footerClass' => ''])

<div class="card {{ $class }}">
    @if($title || $subtitle || isset($header))
    <div class="card-header {{ $headerClass }}">
        @if($title)
        <h5 class="card-title mb-0">{{ $title }}</h5>
        @endif
        @if($subtitle)
        <h6 class="card-subtitle text-muted">{{ $subtitle }}</h6>
        @endif
        @if(isset($header))
        {{ $header }}
        @endif
    </div>
    @endif
    
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
    <div class="card-footer {{ $footerClass }}">
        {{ $footer }}
    </div>
    @endif
</div>

