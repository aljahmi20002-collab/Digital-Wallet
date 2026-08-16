@php use App\View\Components\Icon; @endphp
@props(['name', 'width' => null, 'height' => null, 'class' => ''])

@php
    $svgContent = (new Icon($name))->svgContent();
    $fallbackWidth = $width ?: 18;
    $fallbackHeight = $height ?: 18;

    if ($svgContent) {
        $dom = new DOMDocument();
        $dom->loadXML($svgContent);
        $svg = $dom->getElementsByTagName('svg')->item(0);

        if ($width) {
            $svg->setAttribute('width', $width);
        }

        if ($height) {
            $svg->setAttribute('height', $height);
        }

        if ($class) {
            $existingClasses = $svg->getAttribute('class');
            $svg->setAttribute('class', trim("$existingClasses $class"));
        }

        $svgContent = $dom->saveXML($svg);
    }
@endphp

@if ($svgContent)
    {!! $svgContent !!}
@else
    <span class="icon-fallback" aria-label="{{ __('Missing icon') }}: {{ $name }}" role="img">
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $fallbackWidth }}" height="{{ $fallbackHeight }}" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
             class="{{ trim($class) }}">
            <path d="M10 9h.01"/>
            <path d="M13.3 10.3c-.9-.9-2.5-.9-3.4 0-.9.9-.9 2.5 0 3.4.9.9 2.5.9 3.4 0"/>
            <path d="M12 15.5V16"/>
            <circle cx="12" cy="12" r="9.5"/>
        </svg>
    </span>
@endif
