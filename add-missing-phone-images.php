<?php

const DRY_RUN = false;

$root = __DIR__;

$imageMap = [

    'iphone 17 pro max' => [
        'square' => 'apple/img/iphone17promax_1_1.png',
        'wide'   => 'apple/img/iphone17promax_16_9.png',
    ],

    'iphone 17 pro' => [
        'square' => null,
        'wide'   => null,
    ],

    'galaxy s26 ultra' => [
        'square' => 'Samsung/img/galaxys26ultra_1_1.png',
        'wide'   => 'Samsung/img/galaxys26ultra_16_9.png',
    ],

    'samsung galaxy s26 ultra' => [
        'square' => 'Samsung/img/galaxys26ultra_1_1.png',
        'wide'   => 'Samsung/img/galaxys26ultra_16_9.png',
    ],

    'xiaomi 16 ultra' => [
        'square' => 'Xiaomi/img/xiaomi16ultra_1_1.png',
        'wide'   => 'Xiaomi/img/xiaomi16ultra_16_9.png',
    ],

    'google pixel 10 pro' => [
        'square' => 'Google/img/googlepixel10pro_1_1.png',
        'wide'   => 'Google/img/Googlepixel10pro_16_9.png',
    ],

    'pixel 10 pro' => [
        'square' => 'Google/img/googlepixel10pro_1_1.png',
        'wide'   => 'Google/img/Googlepixel10pro_16_9.png',
    ],
];

function normalizeText(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strtolower($text);
    $text = preg_replace('/\s+/u', ' ', trim($text));

    return $text;
}

function getCardName(DOMElement $card): string
{
    foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag) {

        foreach ($card->getElementsByTagName($tag) as $heading) {

            $text = trim($heading->textContent);

            if ($text !== '') {
                return normalizeText($text);
            }
        }
    }

    return '';
}

function getClasses(DOMElement $element): array
{
    $class = trim($element->getAttribute('class'));

    if ($class === '') {
        return [];
    }

    return preg_split('/\s+/', $class);
}

function hasClass(DOMElement $element, string $class): bool
{
    return in_array($class, getClasses($element), true);
}

function containsImage(DOMElement $card): bool
{
    return $card->getElementsByTagName('img')->length > 0;
}

function isConfirmedCard(DOMElement $element): bool
{
    $confirmed = [
        'phone-card',
        'product-card',
        'featured-card',
        'f-review-card',
    ];

    foreach ($confirmed as $class) {

        if (hasClass($element, $class)) {
            return true;
        }
    }

    return false;
}

function detectImageType(DOMElement $card): string
{
    if (hasClass($card, 'f-review-card')) {
        return 'square';
    }

    foreach ($card->getElementsByTagName('*') as $child) {

        if ($child instanceof DOMElement &&
            hasClass($child, 'phone-image')) {

            return 'wide';
        }
    }

    if (hasClass($card, 'featured-card')) {
        return 'wide';
    }

    return 'square';
}

function findImageForName(
    string $name,
    array $imageMap,
    string $type
): ?string {

    if (!isset($imageMap[$name])) {
        return null;
    }

    if (!isset($imageMap[$name][$type])) {
        return null;
    }

    return $imageMap[$name][$type];
}

function collectHtmlFiles(string $root): array
{
    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $root,
            FilesystemIterator::SKIP_DOTS
        )
    );

    foreach ($iterator as $file) {

        if (!$file->isFile()) {
            continue;
        }

        $path = $file->getPathname();

        if (strpos($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) !== false) {
            continue;
        }

        if (strpos($path, DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR) !== false) {
            continue;
        }

        if (strtolower($file->getExtension()) !== 'html') {
            continue;
        }

        $files[] = $path;
    }

    sort($files);

    return $files;
}

$files = collectHtmlFiles($root);

$pagesScanned = 0;
$cardsScanned = 0;
$imagesAdded = 0;
$filesChanged = 0;
$skipped = 0;

echo PHP_EOL;
echo "==============================================" . PHP_EOL;
echo " TES MISSING PHONE IMAGE AUDIT" . PHP_EOL;
echo "==============================================" . PHP_EOL;
echo " MODE       : " . (DRY_RUN ? 'DRY RUN' : 'WRITE') . PHP_EOL;
echo " HTML FILES : " . count($files) . PHP_EOL;
echo "==============================================" . PHP_EOL;

foreach ($files as $file) {

    $html = @file_get_contents($file);

    if ($html === false) {
        echo "[ERROR] Cannot read: " . $file . PHP_EOL;
        continue;
    }

    $pagesScanned++;

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();

    $loaded = $dom->loadHTML(
        '<?xml encoding="UTF-8">' . $html,
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    if (!$loaded) {
        echo "[ERROR] Cannot parse: " . $file . PHP_EOL;
        libxml_clear_errors();
        continue;
    }

    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    $nodes = $xpath->query(
        '//*[contains(concat(" ", normalize-space(@class), " "), " phone-card ")
          or contains(concat(" ", normalize-space(@class), " "), " product-card ")
          or contains(concat(" ", normalize-space(@class), " "), " featured-card ")
          or contains(concat(" ", normalize-space(@class), " "), " f-review-card ")]'
    );

    if ($nodes === false) {
        continue;
    }

    $fileChanged = false;

    foreach ($nodes as $node) {

        if (!($node instanceof DOMElement)) {
            continue;
        }

        $cardsScanned++;

        /*
         * Do not process nested confirmed cards.
         */
        $nested = false;

        $parent = $node->parentNode;

        while ($parent instanceof DOMElement) {

            if (isConfirmedCard($parent)) {
                $nested = true;
                break;
            }

            $parent = $parent->parentNode;
        }

        if ($nested) {
            continue;
        }

        /*
         * NEVER touch cards that already contain an image.
         */
        if (containsImage($node)) {
            continue;
        }

        $name = getCardName($node);

        if ($name === '') {
            $skipped++;
            continue;
        }

        $type = detectImageType($node);

        $image = findImageForName(
            $name,
            $imageMap,
            $type
        );

        if ($image === null) {
            echo "[SKIP] No mapping: " .
                 basename($file) .
                 " | " .
                 $name .
                 " | " .
                 $type .
                 PHP_EOL;

            $skipped++;
            continue;
        }

        $absoluteImage = $root . DIRECTORY_SEPARATOR .
                         str_replace('/', DIRECTORY_SEPARATOR, $image);

        if (!is_file($absoluteImage)) {

            echo "[ERROR] Image not found: " .
                 $image .
                 " | " .
                 basename($file) .
                 PHP_EOL;

            $skipped++;
            continue;
        }

        echo "[ADD] " .
             basename($file) .
             " | " .
             $name .
             " | " .
             $type .
             " | " .
             $image .
             PHP_EOL;

        if (DRY_RUN) {

            $imagesAdded++;
            continue;
        }

        $img = $dom->createElement('img');

        $img->setAttribute('src', $image);
        $img->setAttribute(
            'alt',
            ucwords($name)
        );
        $img->setAttribute(
            'loading',
            'lazy'
        );

        /*
         * Prefer the existing phone-image container.
         */
        $phoneImage = null;

        foreach ($node->getElementsByTagName('*') as $child) {

            if ($child instanceof DOMElement &&
                hasClass($child, 'phone-image')) {

                $phoneImage = $child;
                break;
            }
        }

        if ($phoneImage !== null) {

            $phoneImage->insertBefore(
                $img,
                $phoneImage->firstChild
            );

        } else {

            $node->insertBefore(
                $img,
                $node->firstChild
            );
        }

        $imagesAdded++;
        $fileChanged = true;
    }

    /*
     * IMPORTANT:
     * Only write this specific HTML file if THIS file
     * actually received an image.
     */
    if (!DRY_RUN && $fileChanged) {

        $newHtml = $dom->saveHTML();

        if ($newHtml !== false && $newHtml !== $html) {

            /*
             * Backup before modifying.
             */
            $backup = $file . '.before-image-insert.bak';

            if (!file_exists($backup)) {
                @copy($file, $backup);
            }

            file_put_contents(
                $file,
                $newHtml
            );

            $filesChanged++;
        }
    }
}

echo PHP_EOL;
echo "==============================================" . PHP_EOL;
echo " AUDIT COMPLETE" . PHP_EOL;
echo "==============================================" . PHP_EOL;
echo " HTML FILES SCANNED : " . $pagesScanned . PHP_EOL;
echo " CARDS SCANNED      : " . $cardsScanned . PHP_EOL;
echo " IMAGES TO ADD      : " . $imagesAdded . PHP_EOL;
echo " FILES CHANGED      : " . $filesChanged . PHP_EOL;
echo " SKIPPED            : " . $skipped . PHP_EOL;
echo " MODE               : " . (DRY_RUN ? 'DRY RUN' : 'WRITE') . PHP_EOL;
echo "==============================================" . PHP_EOL;

?>
