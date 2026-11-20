<?php
/**
 * Allowlist-based HTML sanitizer for CMS content (news, product sections, use cases).
 *
 * Editors can use normal formatting HTML (paragraphs, lists, links, images,
 * tables, inline styles, YouTube/Vimeo embeds), but scripts, event handlers,
 * javascript: URLs, forms and other active content are removed.
 * Applied when rendering, so content already in the database is covered too.
 */

const SANITIZE_ALLOWED_TAGS = [
    'p', 'br', 'hr', 'div', 'span', 'section', 'article',
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
    'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'small', 'mark', 'abbr', 'cite', 'q',
    'a', 'img', 'figure', 'figcaption', 'iframe',
    'ul', 'ol', 'li', 'dl', 'dt', 'dd',
    'blockquote', 'pre', 'code', 'kbd',
    'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
    'details', 'summary',
];

// Removed together with everything inside them.
const SANITIZE_DROP_TAGS = [
    'script', 'style', 'noscript', 'template', 'object', 'embed', 'applet',
    'form', 'input', 'button', 'select', 'textarea', 'option',
    'link', 'meta', 'base', 'svg', 'math', 'frame', 'frameset',
];

const SANITIZE_GLOBAL_ATTRS = ['class', 'id', 'title', 'style', 'lang', 'dir'];

const SANITIZE_TAG_ATTRS = [
    'a' => ['href', 'target', 'rel'],
    'img' => ['src', 'alt', 'width', 'height', 'loading'],
    'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder', 'loading'],
    'ol' => ['start', 'type', 'reversed'],
    'td' => ['colspan', 'rowspan'],
    'th' => ['colspan', 'rowspan', 'scope'],
    'col' => ['span'],
    'colgroup' => ['span'],
    'abbr' => ['title'],
    'details' => ['open'],
];

const SANITIZE_URL_ATTRS = ['href', 'src'];
const SANITIZE_URL_SCHEMES = ['http', 'https', 'mailto'];
const SANITIZE_IFRAME_HOSTS = ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com'];

function sanitize_html($html) {
    $html = (string)$html;
    if (trim($html) === '') {
        return '';
    }

    $doc = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $doc->loadHTML(
        '<?xml encoding="utf-8"?><div id="__sanitize_root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $root = null;
    foreach ($doc->getElementsByTagName('div') as $div) {
        if ($div->getAttribute('id') === '__sanitize_root') {
            $root = $div;
            break;
        }
    }
    if (!$root) {
        return htmlspecialchars($html);
    }

    sanitize_children($root);

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    return $out;
}

function sanitize_children(DOMNode $parent) {
    // Copy the list first: we modify the tree while walking it.
    $children = [];
    foreach ($parent->childNodes as $child) {
        $children[] = $child;
    }

    foreach ($children as $node) {
        if ($node instanceof DOMText) {
            continue; // includes CDATA
        }
        if (!($node instanceof DOMElement)) {
            $parent->removeChild($node); // comments, processing instructions
            continue;
        }

        $tag = strtolower($node->nodeName);

        if (in_array($tag, SANITIZE_DROP_TAGS, true)) {
            $parent->removeChild($node);
            continue;
        }

        if (!in_array($tag, SANITIZE_ALLOWED_TAGS, true)) {
            // Unknown tag: keep its (sanitized) content, drop the tag itself.
            sanitize_children($node);
            while ($node->firstChild) {
                $parent->insertBefore($node->firstChild, $node);
            }
            $parent->removeChild($node);
            continue;
        }

        if ($tag === 'iframe' && !sanitize_is_allowed_embed($node->getAttribute('src'))) {
            $parent->removeChild($node);
            continue;
        }

        sanitize_attributes($node, $tag);
        sanitize_children($node);
    }
}

function sanitize_attributes(DOMElement $el, $tag) {
    $allowed = array_merge(SANITIZE_GLOBAL_ATTRS, SANITIZE_TAG_ATTRS[$tag] ?? []);

    $attrs = [];
    foreach ($el->attributes as $attr) {
        $attrs[] = $attr->nodeName;
    }

    foreach ($attrs as $name) {
        $lower = strtolower($name);
        $value = $el->getAttribute($name);

        if (!in_array($lower, $allowed, true)
            || (in_array($lower, SANITIZE_URL_ATTRS, true) && !sanitize_is_safe_url($value))
            || ($lower === 'style' && preg_match('/expression|javascript:|behavior|-moz-binding/i', $value))) {
            $el->removeAttribute($name);
        }
    }

    if ($tag === 'a' && strtolower($el->getAttribute('target')) === '_blank') {
        $el->setAttribute('rel', 'noopener noreferrer');
    }
}

function sanitize_is_safe_url($url) {
    // Browsers ignore control characters and whitespace inside schemes ("java\nscript:").
    $clean = preg_replace('/[\x00-\x20\x7f]+/', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $clean, $m)) {
        return in_array(strtolower($m[1]), SANITIZE_URL_SCHEMES, true);
    }
    return true; // relative URL, #anchor or protocol-relative
}

function sanitize_is_allowed_embed($src) {
    $parts = parse_url(trim($src));
    return $parts !== false
        && ($parts['scheme'] ?? '') === 'https'
        && in_array(strtolower($parts['host'] ?? ''), SANITIZE_IFRAME_HOSTS, true);
}
