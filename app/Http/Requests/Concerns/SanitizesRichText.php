<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Services\RichTextSanitizer;

trait SanitizesRichText
{
    protected function sanitizeRichText(string $field): void
    {
        $value = $this->input($field);

        if (! is_string($value) || strlen($value) > RichTextSanitizer::MAX_LENGTH) {
            return;
        }

        $this->merge([
            $field => RichTextSanitizer::clean($value),
        ]);
    }
}
