<?php
/**
 * Renders assets/docs/USER_GUIDE.md into the Docs page layout.
 *
 * The Markdown file is the single source of truth: update it, deploy, done.
 *   # Title                -> page header title
 *   first paragraph        -> page header subtitle
 *   ## Section             -> <section class="docs-section"> + sidebar link
 *   ## Table of Contents   -> skipped (the sidebar replaces it)
 *   ### Subsection         -> <h3 id="..."> (GitHub-style anchors, so in-guide links work)
 *   tables / lists         -> existing docs-table / docs-list styling
 *   text after the last --- that is only paragraphs -> docs-footer
 *
 * Parsedown runs in safe mode: raw HTML in the Markdown is escaped, not rendered.
 */
require_once __DIR__ . '/vendor/Parsedown.php';

function docs_slug($text, array &$used) {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9 \-]/', '', $slug);
    $slug = str_replace(' ', '-', $slug);
    if ($slug === '') {
        $slug = 'section';
    }
    $base = $slug;
    for ($i = 1; isset($used[$slug]); $i++) {
        $slug = $base . '-' . $i;
    }
    $used[$slug] = true;
    return $slug;
}

function docs_inner_html(DOMNode $node) {
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }
    return $html;
}

/** Apply the docs CSS classes and heading ids inside a section. */
function docs_decorate(DOMElement $el, array &$usedIds) {
    $doc = $el->ownerDocument;
    foreach (iterator_to_array($el->getElementsByTagName('table')) as $table) {
        $table->setAttribute('class', 'docs-table');
        $wrapper = $doc->createElement('div');
        $wrapper->setAttribute('class', 'docs-table-wrapper');
        $table->parentNode->replaceChild($wrapper, $table);
        $wrapper->appendChild($table);
    }
    foreach (['ul', 'ol'] as $tag) {
        foreach (iterator_to_array($el->getElementsByTagName($tag)) as $list) {
            $list->setAttribute('class', 'docs-list');
        }
    }
    foreach (['h3', 'h4'] as $tag) {
        foreach (iterator_to_array($el->getElementsByTagName($tag)) as $h) {
            $h->setAttribute('id', docs_slug($h->textContent, $usedIds));
        }
    }
    foreach (iterator_to_array($el->getElementsByTagName('a')) as $a) {
        if (preg_match('#^https?://#i', $a->getAttribute('href'))) {
            $a->setAttribute('target', '_blank');
            $a->setAttribute('rel', 'noopener');
        }
    }
}

/**
 * @return array|null ['title', 'intro_html', 'sections' => [['id','title','html']], 'footer_html']
 */
function render_user_guide($path) {
    if (!is_readable($path)) {
        return null;
    }

    $parser = new Parsedown();
    $parser->setSafeMode(true);
    $html = $parser->text(file_get_contents($path));

    $doc = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="docs-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $root = (new DOMXPath($doc))->query('//div[@id="docs-root"]')->item(0);
    if (!$root) {
        return null;
    }

    $title = 'User Guide';
    $introHtml = '';
    $usedIds = [];
    $sections = [];       // each: ['id', 'title', 'el' => DOMElement container]
    $current = null;
    $skipping = false;
    $afterLastHr = [];    // top-level nodes after the most recent <hr>

    foreach (iterator_to_array($root->childNodes) as $node) {
        if (!($node instanceof DOMElement)) {
            continue;
        }
        $tag = strtolower($node->nodeName);

        if ($tag === 'h1') {
            $title = trim($node->textContent);
            continue;
        }
        if ($tag === 'hr') {
            $afterLastHr = [];
            continue;
        }
        if ($tag === 'h2') {
            $text = trim($node->textContent);
            $skipping = strcasecmp($text, 'Table of Contents') === 0;
            if (!$skipping) {
                $container = $doc->createElement('div');
                $current = count($sections);
                $sections[] = ['id' => docs_slug($text, $usedIds), 'title' => $text, 'el' => $container];
            }
            $afterLastHr = [];
            continue;
        }
        if ($skipping) {
            continue;
        }
        if ($current === null) {
            // Text before the first section: the first paragraph becomes the subtitle.
            if ($tag === 'p' && $introHtml === '') {
                $introHtml = docs_inner_html($node);
            }
            continue;
        }
        $sections[$current]['el']->appendChild($node);
        $afterLastHr[] = $node;
    }

    // Trailing "*Last updated ...*" style paragraphs after the final --- become the footer.
    $footerHtml = '';
    if ($afterLastHr && count(array_filter($afterLastHr, function ($n) {
        return strtolower($n->nodeName) !== 'p';
    })) === 0) {
        foreach ($afterLastHr as $n) {
            $footerHtml .= $doc->saveHTML($n);
            $n->parentNode->removeChild($n);
        }
    }

    $out = [];
    foreach ($sections as $s) {
        docs_decorate($s['el'], $usedIds);
        $out[] = ['id' => $s['id'], 'title' => $s['title'], 'html' => docs_inner_html($s['el'])];
    }

    return ['title' => $title, 'intro_html' => $introHtml, 'sections' => $out, 'footer_html' => $footerHtml];
}
