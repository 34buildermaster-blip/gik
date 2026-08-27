<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

class SafeInlineStyleSanitizer implements AttributeSanitizerInterface
{
    private const FONT_FAMILIES = [
        "'LINE Seed Sans TH', sans-serif",
        "'Prompt', sans-serif",
        "'Tahoma', sans-serif",
        "'Arial', sans-serif",
        "'Georgia', serif",
        'LINE Seed Sans TH, sans-serif',
        'Prompt, sans-serif',
        'Tahoma, sans-serif',
        'Arial, sans-serif',
        'Georgia, serif',
    ];

    private const FONT_SIZES = ['14px', '16px', '18px', '20px', '24px', '30px', '36px'];

    public function getSupportedElements(): ?array
    {
        return ['p', 'span', 'h2', 'h3', 'h4', 'li', 'blockquote', 'th', 'td', 'figcaption'];
    }

    public function getSupportedAttributes(): ?array
    {
        return ['style'];
    }

    public function sanitizeAttribute(
        string $element,
        string $attribute,
        string $value,
        HtmlSanitizerConfig $config,
    ): ?string {
        $safe = [];

        foreach (explode(';', $value) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $rawValue] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);

            $sanitized = match ($property) {
                'font-family' => in_array($rawValue, self::FONT_FAMILIES, true) ? $rawValue : null,
                'font-size' => in_array(strtolower($rawValue), self::FONT_SIZES, true) ? strtolower($rawValue) : null,
                'color', 'background-color' => $this->sanitizeColor($rawValue),
                'text-align' => in_array(strtolower($rawValue), ['left', 'center', 'right', 'justify'], true)
                    ? strtolower($rawValue)
                    : null,
                default => null,
            };

            if ($sanitized !== null) {
                $safe[] = $property.': '.$sanitized;
            }
        }

        return $safe === [] ? null : implode('; ', array_unique($safe));
    }

    private function sanitizeColor(string $value): ?string
    {
        $value = strtolower(trim($value));

        if (preg_match('/^#[0-9a-f]{3}([0-9a-f]{3})?$/', $value) === 1) {
            return $value;
        }

        if (preg_match('/^rgba?\(\s*(?:\d{1,3}\s*,\s*){2}\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/', $value) !== 1) {
            return null;
        }

        preg_match_all('/\d+(?:\.\d+)?/', $value, $matches);
        $channels = array_map('floatval', $matches[0]);

        if (count($channels) < 3 || max(array_slice($channels, 0, 3)) > 255) {
            return null;
        }

        return $value;
    }
}
