<?php

declare(strict_types=1);

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class RichTextSanitizer
{
    public const MAX_LENGTH = 50000;

    private static ?HTMLPurifier $purifier = null;

    /**
     * Allow-list HTML produced by the TipTap editor.
     * Strings longer than MAX_LENGTH are returned unchanged so validation can reject them
     * without running the purifier.
     */
    public static function clean(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        if (strlen($html) > self::MAX_LENGTH) {
            return $html;
        }

        return self::purifier()->purify($html);
    }

    /**
     * Clean HTML that will be stored or sent to a browser.
     * Over-long input is discarded instead of returned raw.
     */
    public static function cleanStored(?string $html): ?string
    {
        if ($html !== null && strlen($html) > self::MAX_LENGTH) {
            return '';
        }

        return self::clean($html);
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier instanceof HTMLPurifier) {
            return self::$purifier;
        }

        $cachePath = storage_path('framework/cache/htmlpurifier');

        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.DefinitionID', 'rich-text-editor');
        $config->set('HTML.DefinitionRev', 1);
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Allowed', implode(',', [
            'p',
            'br',
            'strong',
            'b',
            'em',
            'i',
            'u',
            's',
            'strike',
            'mark',
            'ul',
            'ol',
            'li',
            'blockquote',
            'pre',
            'code',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'hr',
            'a[href|target|rel]',
            'span[class|data-type|data-id|data-label]',
        ]));
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
        ]);
        $config->set('Attr.AllowedFrameTargets', [
            '_blank' => true,
        ]);
        $config->set('Attr.AllowedClasses', [
            'mention' => true,
        ]);
        $config->set('HTML.TargetBlank', false);
        $config->set('HTML.Nofollow', false);
        $config->set('AutoFormat.AutoParagraph', false);
        $config->set('AutoFormat.RemoveEmpty', false);

        $definition = $config->maybeGetRawHTMLDefinition();

        if ($definition !== null) {
            $definition->addElement('mark', 'Inline', 'Inline', 'Common');
            $definition->addAttribute('span', 'data-type', 'Text');
            $definition->addAttribute('span', 'data-id', 'Text');
            $definition->addAttribute('span', 'data-label', 'Text');
        }

        return self::$purifier = new HTMLPurifier($config);
    }
}
