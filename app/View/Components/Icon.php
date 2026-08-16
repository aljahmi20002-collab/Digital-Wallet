<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class Icon extends Component
{
    public $name;

    public $width;

    public $height;

    public $class;

    public function __construct($name, $width = null, $height = null, $class = '')
    {
        $this->name   = $name;
        $this->width  = $width;
        $this->height = $height;
        $this->class  = $class;
    }

    public function render()
    {
        return view('components.icon');
    }

    public function svgContent(): ?string
    {
        $svgPath = "{$this->name}.svg";

        if (! Storage::disk('svg_assets')->exists($svgPath)) {
            return null;
        }

        $cacheKey = "svg.{$this->name}.".Storage::disk('svg_assets')->lastModified($svgPath);

        return Cache::rememberForever($cacheKey, function () use ($svgPath) {
            return Storage::disk('svg_assets')->get($svgPath);
        });
    }
}
