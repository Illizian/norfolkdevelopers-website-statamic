<?php

namespace App\ViewModels;

use Illuminate\Support\Str;
use Statamic\View\ViewModel;

class BardExcerpt extends ViewModel
{
    public function data(): array
    {
        // Combine content blocks
        $html = collect($this->cascade->get('bard'))
            ->filter(fn($set) => $set['type'] === "text")
            ->implode('text', " ");

        // Remove HTML tags
        $content = trim( // 3. strip leading/trailing space
            preg_replace(
                '/ {2,}/', // 2. strip extra spaces
                ' ',
                preg_replace(
                    '/<[^>]*>/', // 1. strip_tags
                    ' ',
                    $html
                )
            )
        );

        // Return an Excerpt
        return [
            "excerpt" => Str::words($content, 16, '…'),
        ];
    }
}
