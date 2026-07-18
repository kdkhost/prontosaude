<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DropzoneUpload extends Component
{
    public $name;
    public $url;
    public $maxFilesize;
    public $acceptedFiles;
    public $hintDimensions;
    public $maxFiles;

    public function __construct(
        $name = 'file',
        $url = '/admin/upload',
        $maxFilesize = 2,
        $acceptedFiles = 'image/*',
        $hintDimensions = 'Tamanho ideal: 800x600px',
        $maxFiles = 1
    ) {
        $this->name = $name;
        $this->url = $url;
        $this->maxFilesize = $maxFilesize;
        $this->acceptedFiles = $acceptedFiles;
        $this->hintDimensions = $hintDimensions;
        $this->maxFiles = $maxFiles;
    }

    public function render(): View|Closure|string
    {
        return view('components.dropzone-upload');
    }
}
