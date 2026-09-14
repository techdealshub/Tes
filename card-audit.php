<?php

/*
=========================================================
 TES — CARD AUDIT v1
 READ ONLY — لا يعدل أي HTML أو صور
=========================================================
*/

declare(strict_types=1);

$ROOT = __DIR__;
$OUTPUT = $ROOT . DIRECTORY_SEPARATOR . 'card-audit.txt';

/*
---------------------------------------------------------
الكاردات الحقيقية التي نريد فحصها
search-card وحدها ليست Card لأنها غالباً Modifier
---------------------------------------------------------
*/

$CARD_CLASSES = [
    'comparison-card',
    'review-card',
    'f-review-card',
    'trend-item',
    'product-card',
    'article-card',
    'news-card',
    'phone-card',
    'device-card',
    'brand-card',
    'featured-card',
    'popular-card',
    'editor-card',
    'latest-card',
    'spec-card'
];

/*
---------------------------------------------------------
مساعدات
---------------------------------------------------------
*/

function cleanText(string $text): string
{
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text ?? '');
}

function relativePath(string $fromFile, string $target): string
{
    if (
        preg_match('/^(https?:)?\/\//i', $target) ||
        str_starts_with($target, 'data:') ||
        str_starts_with($target, '#') ||
        str_starts_with($target, '/')
    ) {
        return $target;
    }

    $fromDir = dirname($fromFile);

    $fromParts = $fromDir === '.' ? [] : explode('/', str_replace('\\', '/', $fromDir));
    $targetParts = explode('/', str_replace('\\', '/', $target));

    while (
        count($fromParts) > 0 &&
        count($targetParts) > 0 &&
        $fromParts[0] === $targetParts[0]
    ) {
        array_shift($fromParts);
        array_shift($targetParts);
    }

    return str_repeat('../', count($fromParts)) . implode('/', $targetParts);
}

function hasCardClass(DOMElement $element, array $cardClasses): bool
{
    $class = trim($element->getAttribute('class'));

    if ($class === '') {
        return false;
    }

    $tokens = preg_split('/\s+/', $class);

    foreach ($tokens as $token) {
        if (in_array($token, $cardClasses, true)) {
            return true;
        }
    }

    return false;
}

function cardType(DOMElement $element, array $cardClasses): string
{
    $class = trim($element->getAttribute('class'));
    $tokens = preg_split('/\s+/', $class);

    foreach ($tokens as $token) {
        if (in_array($token, $cardClasses, true)) {
            return $token;
        }
    }

    return 'unknown';
}

function directCardParent(DOMElement $element, array $cardClasses): ?DOMElement
{
    $parent = $element->parentNode;

    while ($parent instanceof DOMElement) {

        if (hasCardClass($parent, $cardClasses)) {
            return $parent;
        }

        $parent = $parent->parentNode;
    }

    return null;
}

/*
---------------------------------------------------------
جمع ملفات HTML
---------------------------------------------------------
*/

$htmlFiles = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $ROOT,
        FilesystemIterator::SKIP_DOTS
    )
);

foreach ($iterator as $file) {

    if (!$file->isFile()) {
        continue;
    }

    $path = $file->getPathname();

    /*
    تجاهل مجلدات لا نريد فحصها
    */
    if (
        str_contains($path, DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR) ||
        str_contains($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR)
    ) {
        continue;
    }

    if (strtolower($file->getExtension()) !== 'html') {
        continue;
    }

    $htmlFiles[] = $path;
}

sort($htmlFiles, SORT_NATURAL | SORT_FLAG_CASE);

/*
---------------------------------------------------------
إخراج التقرير
---------------------------------------------------------
*/

$report = [];

$report[] = '=========================================================';
$report[] = 'TES — COMPLETE CARD AUDIT';
$report[] = 'READ ONLY';
$report[] = '=========================================================';
$report[] = '';
$report[] = 'Generated: ' . date('Y-m-d H:i:s');
$report[] = '';

$totalPages = 0;
$totalCards = 0;
$totalImages = 0;
$totalNoImage = 0;

foreach ($htmlFiles as $filePath) {

    $relativeFile = ltrim(
        str_replace(
            ['\\', $ROOT],
            ['/', ''],
            $filePath
        ),
        '/'
    );

    $html = @file_get_contents($filePath);

    if ($html === false) {
        continue;
    }

    $dom = new DOMDocument();

    libxml_use_internal_errors(true);

    $loaded = @$dom->loadHTML(
        '<?xml encoding="UTF-8">' . $html,
        LIBXML_NOWARNING | LIBXML_NOERROR
    );

    libxml_clear_errors();

    if (!$loaded) {
        continue;
    }

    $xpath = new DOMXPath($dom);

    $allElements = $xpath->query('//*');

    $cards = [];

    if ($allElements !== false) {

        foreach ($allElements as $element) {

            if (!$element instanceof DOMElement) {
                continue;
            }

            if (!hasCardClass($element, $CARD_CLASSES)) {
                continue;
            }

            /*
            إذا كان Card داخل Card من نفس النظام
            نأخذ الـ Card الخارجي فقط.
            */
            $parentCard = directCardParent($element, $CARD_CLASSES);

            if ($parentCard !== null) {
                continue;
            }

            $cards[] = $element;
        }
    }

    $totalPages++;
    $totalCards += count($cards);

    $report[] = '';
    $report[] = '=========================================================';
    $report[] = 'PAGE #' . $totalPages;
    $report[] = 'FILE: ' . $relativeFile;
    $report[] = 'CARDS: ' . count($cards);
    $report[] = '=========================================================';
    $report[] = '';

    if (count($cards) === 0) {
        $report[] = '[NO CARDS DETECTED]';
        $report[] = '';
        continue;
    }

    $cardNumber = 0;

    foreach ($cards as $card) {

        $cardNumber++;

        $type = cardType($card, $CARD_CLASSES);

        $class = trim($card->getAttribute('class'));
        $id = trim($card->getAttribute('id'));

        $text = cleanText($card->textContent ?? '');

        $report[] = '---------------------------------------------------------';
        $report[] = 'CARD #' . $cardNumber;
        $report[] = 'TYPE: ' . $type;
        $report[] = 'CLASS: ' . ($class !== '' ? $class : '[none]');
        $report[] = 'ID: ' . ($id !== '' ? $id : '[none]');
        $report[] = 'TEXT: ' . ($text !== '' ? $text : '[empty]');
        $report[] = '';

        /*
        -----------------------------------------------------
        Headings
        -----------------------------------------------------
        */

        $headings = $xpath->query(
            './/h1 | .//h2 | .//h3 | .//h4 | .//h5 | .//h6',
            $card
        );

        $report[] = 'HEADINGS:';

        if ($headings !== false && $headings->length > 0) {

            foreach ($headings as $heading) {

                $headingText = cleanText(
                    $heading->textContent ?? ''
                );

                $report[] =
                    '  ' .
                    strtolower($heading->nodeName) .
                    ': ' .
                    ($headingText !== '' ? $headingText : '[empty]');
            }

        } else {

            $report[] = '  [none]';
        }

        $report[] = '';

        /*
        -----------------------------------------------------
        Links
        -----------------------------------------------------
        */

        $links = $xpath->query('.//a', $card);

        $report[] = 'LINKS: ' .
            (($links !== false) ? $links->length : 0);

        if ($links !== false && $links->length > 0) {

            $linkNumber = 0;

            foreach ($links as $link) {

                $linkNumber++;

                $href = trim($link->getAttribute('href'));
                $linkText = cleanText($link->textContent ?? '');

                $report[] =
                    '  LINK #' . $linkNumber .
                    ' TEXT: ' . ($linkText !== '' ? $linkText : '[empty]');

                $report[] =
                    '       HREF: ' . ($href !== '' ? $href : '[empty]');
            }
        }

        $report[] = '';

        /*
        -----------------------------------------------------
        Images
        -----------------------------------------------------
        */

        $images = $xpath->query('.//img', $card);

        $imageCount = ($images !== false)
            ? $images->length
            : 0;

        $totalImages += $imageCount;

        $report[] = 'IMAGES: ' . $imageCount;

        if ($imageCount === 0) {

            $totalNoImage++;

            $report[] = '  [NO IMAGE]';

        } else {

            $imageNumber = 0;

            foreach ($images as $img) {

                $imageNumber++;

                $src = trim($img->getAttribute('src'));
                $alt = trim($img->getAttribute('alt'));
                $width = trim($img->getAttribute('width'));
                $height = trim($img->getAttribute('height'));

                $report[] =
                    '  IMAGE #' . $imageNumber;

                $report[] =
                    '    SRC: ' .
                    ($src !== '' ? $src : '[empty]');

                $report[] =
                    '    ALT: ' .
                    ($alt !== '' ? $alt : '[empty]');

                if ($width !== '') {
                    $report[] = '    WIDTH: ' . $width;
                }

                if ($height !== '') {
                    $report[] = '    HEIGHT: ' . $height;
                }

                /*
                فحص الصورة محلياً إذا كانت relative
                */
                if (
                    $src !== '' &&
                    !preg_match('/^(https?:)?\/\//i', $src) &&
                    !str_starts_with($src, 'data:')
                ) {

                    $srcClean = preg_replace('/[?#].*$/', '', $src);

                    $imageAbsolute = realpath(
                        dirname($filePath) .
                        DIRECTORY_SEPARATOR .
                        str_replace(
                            ['/', '\\'],
                            DIRECTORY_SEPARATOR,
                            $srcClean
                        )
                    );

                    if ($imageAbsolute !== false && is_file($imageAbsolute)) {
                        $report[] = '    FILE EXISTS: YES';
                    } else {
                        $report[] = '    FILE EXISTS: NO';
                    }
                }
            }
        }

        $report[] = '';

        /*
        -----------------------------------------------------
        Data attributes
        -----------------------------------------------------
        */

        $attributes = [];

        foreach ($card->attributes as $attribute) {

            $name = $attribute->name;

            if (str_starts_with(strtolower($name), 'data-')) {
                $attributes[$name] = $attribute->value;
            }
        }

        $report[] = 'DATA ATTRIBUTES: ' . count($attributes);

        if (count($attributes) > 0) {

            foreach ($attributes as $name => $value) {

                $value = cleanText($value);

                $report[] =
                    '  ' .
                    $name .
                    ': ' .
                    ($value !== '' ? $value : '[empty]');
            }
        }

        $report[] = '';

        /*
        -----------------------------------------------------
        HTML size
        -----------------------------------------------------
        */

        $outerHTML = $dom->saveHTML($card);

        $report[] =
            'HTML LENGTH: ' .
            strlen($outerHTML);

        $report[] = '';
    }
}

/*
---------------------------------------------------------
Summary
---------------------------------------------------------
*/

$report[] = '';
$report[] = '=========================================================';
$report[] = 'FINAL SUMMARY';
$report[] = '=========================================================';
$report[] = 'HTML PAGES : ' . $totalPages;
$report[] = 'CARDS      : ' . $totalCards;
$report[] = 'IMAGES     : ' . $totalImages;
$report[] = 'NO IMAGE   : ' . $totalNoImage;
$report[] = 'MODIFIED   : 0';
$report[] = '=========================================================';
$report[] = '';

file_put_contents(
    $OUTPUT,
    implode(PHP_EOL, $report)
);

echo PHP_EOL;
echo "==============================================" . PHP_EOL;
echo " TES CARD AUDIT COMPLETE" . PHP_EOL;
echo "==============================================" . PHP_EOL;
echo "HTML PAGES : " . $totalPages . PHP_EOL;
echo "CARDS      : " . $totalCards . PHP_EOL;
echo "IMAGES     : " . $totalImages . PHP_EOL;
echo "NO IMAGE   : " . $totalNoImage . PHP_EOL;
echo "MODIFIED   : 0" . PHP_EOL;
echo "----------------------------------------------" . PHP_EOL;
echo "REPORT     : card-audit.txt" . PHP_EOL;
echo "==============================================" . PHP_EOL;
echo PHP_EOL;
?>
