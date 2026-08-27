<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ArticleHtmlSanitizer
{
    private readonly HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $styledText = ['style'];
        $config = (new HtmlSanitizerConfig)
            ->allowElement('p', $styledText)
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('b')
            ->allowElement('em')
            ->allowElement('i')
            ->allowElement('u')
            ->allowElement('s')
            ->allowElement('span', $styledText)
            ->allowElement('h2', $styledText)
            ->allowElement('h3', $styledText)
            ->allowElement('h4', $styledText)
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li', $styledText)
            ->allowElement('blockquote', $styledText)
            ->allowElement('a', ['href', 'title', 'target', 'rel'])
            ->allowElement('figure')
            ->allowElement('figcaption', $styledText)
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'loading'])
            ->allowElement('video', ['controls', 'preload', 'poster', 'width', 'height'])
            ->allowElement('source', ['src', 'type'])
            ->allowElement('table')
            ->allowElement('thead')
            ->allowElement('tbody')
            ->allowElement('tr')
            ->allowElement('th', ['colspan', 'rowspan', 'style'])
            ->allowElement('td', ['colspan', 'rowspan', 'style'])
            ->allowElement('hr')
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->withAttributeSanitizer(new SafeInlineStyleSanitizer)
            ->withMaxInputLength(2_000_000);

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function sanitize(string $html): string
    {
        return trim($this->sanitizer->sanitize($html));
    }
}
