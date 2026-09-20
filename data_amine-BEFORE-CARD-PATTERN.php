<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
/*
 * ============================================================
 * CONFIG
 * ============================================================
 */

$templatePath =
    __DIR__ .
    '/iphone-style-complete-placeholders-100-valid-V7-template.html';

$categoryPages = [
    'Flagship' => [
        'file'  => __DIR__ . '/flagship-phones.html',
        'grid'  => 'phoneGrid',
        'class' => 'phone-card',
    ],

    'Mid-Range' => [
        'file'  => __DIR__ . '/mid-range-phones.html',
        'grid'  => 'phoneGrid',
        'class' => 'phone-card',
    ],

    'Budget' => [
        'file'  => __DIR__ . '/budget-phones.html',
        'grid'  => 'grid',
        'class' => 'card',
    ],
];

if (!is_file($templatePath)) {
    die(
        'Error: template file not found: ' .
        basename($templatePath)
    );
}

$template =
    file_get_contents($templatePath);

if ($template === false) {
    die(
        'Error: unable to read template file.'
    );
}

/*
 * ============================================================
 * FIND ALL PLACEHOLDERS
 * ============================================================
 */

preg_match_all(
    '/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/',
    $template,
    $matches
);

$allOccurrences =
    $matches[1] ?? [];

$allPlaceholders =
    array_values(
        array_unique(
            $allOccurrences
        )
    );

/*
 * ============================================================
 * HTML AUTO-GENERATED FIELDS
 * ============================================================
 */

$htmlFields = [
    'BUY_POINTS',
    'SKIP_POINTS',
    'VIDEO_FEATURES',
    'SELFIE_FEATURES',
    'CHARGING_FEATURES',
    'SOFTWARE_FEATURES',
    'AI_CAPABILITIES',
    'AUDIO_FEATURES',
    'WIRELESS_FEATURES',
    'COOLING_FEATURES',
    'DURABILITY_FEATURES',
    'STORAGE_FEATURES',
    'COMPETITORS',
    'BEST_CHOICE',
    'USAGE_SCENARIOS',
];

/*
 * ============================================================
 * BASIC HELPERS
 * ============================================================
 */

function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
 * ============================================================
 * TEXTAREA DETECTION
 * ============================================================
 */

function isTextarea(string $field): bool
{
    $longWords = [
        'DESCRIPTION',
        'SUMMARY',
        'REVIEW',
        'DESC',
        'VERDICT',
        'POINT',
        'FEATURE',
        'ANSWER',
        'QUESTION',
        'TEXT',
        'SCENARIOS',
        'HIGHLIGHT',
        'BEST_CHOICE',
        'COMPETITORS',
    ];

    foreach ($longWords as $word) {

        if (
            stripos(
                $field,
                $word
            ) !== false
        ) {
            return true;
        }
    }

    return false;
}

/*
 * ============================================================
 * NUMBER FIELD
 * ============================================================
 */

function isNumberField(string $field): bool
{
    return (bool) preg_match(
        '/(SCORE|RATING|PERCENT|WIDTH|STARS|AVERAGE|TEMPERATURE|DEPTH|FPS)$/i',
        $field
    );
}

/*
 * ============================================================
 * HTML LIST GENERATOR
 * ============================================================
 */

function listHtml(
    string $value,
    string $tag
): string {

    $lines =
        preg_split(
            '/\R/',
            $value
        );

    if ($lines === false) {
        $lines = [];
    }

    $lines =
        array_values(
            array_filter(
                array_map(
                    'trim',
                    $lines
                ),
                static function (
                    string $line
                ): bool {

                    return $line !== '';

                }
            )
        );

    $out = '';

    foreach ($lines as $line) {

        $out .=
            '<' .
            $tag .
            '>' .
            htmlspecialchars(
                $line,
                ENT_QUOTES,
                'UTF-8'
            ) .
            '</' .
            $tag .
            ">\n";
    }

    return $out;
}

/*
 * ============================================================
 * GENERATE HTML FIELD
 * ============================================================
 */

function generateHtmlField(
    string $field,
    string $value
): string {

    $listValue =
        str_replace(
            '|',
            "\n",
            $value
        );

    $tag =
        in_array(
            $field,
            [
                'BUY_POINTS',
                'SKIP_POINTS',
            ],
            true
        )
        ? 'li'
        : 'span';

    return listHtml(
        $listValue,
        $tag
    );
}

/*
 * ============================================================
 * NORMALIZE PHONE FILE NAME
 * ============================================================
 */

function normalizePhoneFileName(
    string $title,
    string $brand = ''
): string {

    $title =
        trim($title);

    $brand =
        trim($brand);

    if ($brand !== '') {

        $title =
            preg_replace(
                '/^' .
                preg_quote(
                    $brand,
                    '/'
                ) .
                '\s+/iu',
                '',
                $title
            ) ?? $title;
    }

    $title =
        strtolower(
            trim($title)
        );

    $title =
        preg_replace(
            '/[^a-z0-9]+/i',
            '',
            $title
        );

    return trim(
        (string) $title
    );
}

/*
 * ============================================================
 * GET FIELD VALUE
 * ============================================================
 */

function getFieldValue(
    array $allPlaceholders,
    array $data,
    string $wantedField
): string {

    foreach (
        $allPlaceholders
        as $index => $field
    ) {

        if (
            strcasecmp(
                trim($field),
                trim($wantedField)
            ) === 0
        ) {

            return trim(
                (string) (
                    $data[
                        $index + 1
                    ] ?? ''
                )
            );
        }
    }

    return '';
}

/*
 * ============================================================
 * IMAGE TYPE
 * ============================================================
 */

function getImageType(
    string $field
): string {

    $field =
        strtoupper(
            trim($field)
        );

    $types = [
        'HERO_IMAGE' => 'hero',
        'DESIGN_IMAGE' => 'design',
        'DISPLAY_IMAGE' => 'display',
        'CAMERA_SAMPLE_1' => 'camera_1',
        'CAMERA_SAMPLE_2' => 'camera_2',
        'CAMERA_SAMPLE_3' => 'camera_3',
        'ALT1_IMAGE' => 'alt1',
        'ALT2_IMAGE' => 'alt2',
        'ALT3_IMAGE' => 'alt3',
    ];

    return
        $types[$field]
        ?? '';
}

/*
 * ============================================================
 * IMAGE RATIO
 * ============================================================
 */

function getImageRatio(
    string $field
): string {

    $field =
        strtoupper(
            trim($field)
        );

    if ($field === 'HERO_IMAGE') {
        return '3_2';
    }

    if ($field === 'DESIGN_IMAGE') {
        return '1_1';
    }

    if ($field === 'DISPLAY_IMAGE') {
        return '1_1';
    }

    if (
        $field === 'CAMERA_SAMPLE_1' ||
        $field === 'CAMERA_SAMPLE_2' ||
        $field === 'CAMERA_SAMPLE_3'
    ) {
        return '16_9';
    }

    if (
        $field === 'ALT1_IMAGE' ||
        $field === 'ALT2_IMAGE' ||
        $field === 'ALT3_IMAGE'
    ) {
        return '3_2';
    }

    return '';
}

/*
 * ============================================================
 * GET ALTERNATIVE PHONE NAME
 * ============================================================
 */

function getImagePhoneTitle(
    string $field,
    array $allPlaceholders,
    array $data,
    string $mainTitle
): string {

    $field =
        strtoupper(
            trim($field)
        );

    if ($field === 'ALT1_IMAGE') {

        $value =
            getFieldValue(
                $allPlaceholders,
                $data,
                'ALT1_NAME'
            );

        if ($value !== '') {
            return $value;
        }
    }

    if ($field === 'ALT2_IMAGE') {

        $value =
            getFieldValue(
                $allPlaceholders,
                $data,
                'ALT2_NAME'
            );

        if ($value !== '') {
            return $value;
        }
    }

    if ($field === 'ALT3_IMAGE') {

        $value =
            getFieldValue(
                $allPlaceholders,
                $data,
                'ALT3_NAME'
            );

        if ($value !== '') {
            return $value;
        }
    }

    return $mainTitle;
}

/*
 * ============================================================
 * GET ALTERNATIVE BRAND
 * ============================================================
 */

function getImageBrand(
    string $field,
    array $allPlaceholders,
    array $data,
    string $mainBrand
): string {

    $field =
        strtoupper(
            trim($field)
        );

    $prefix = '';

    if ($field === 'ALT1_IMAGE') {
        $prefix = 'ALT1';
    }

    elseif ($field === 'ALT2_IMAGE') {
        $prefix = 'ALT2';
    }

    elseif ($field === 'ALT3_IMAGE') {
        $prefix = 'ALT3';
    }

    if ($prefix === '') {
        return $mainBrand;
    }

    $possibleFields = [
        $prefix . '_BRAND',
        $prefix . '_MANUFACTURER',
        $prefix . '_COMPANY',
    ];

    foreach (
        $possibleFields
        as $possibleField
    ) {

        $value =
            getFieldValue(
                $allPlaceholders,
                $data,
                $possibleField
            );

        if ($value !== '') {
            return $value;
        }
    }

    return $mainBrand;
}

/*
 * ============================================================
 * FIND BRAND DIRECTORY
 * ============================================================
 */

function findBrandDirectory(
    string $brand
): string {

    $brand =
        trim(
            str_replace(
                '\\',
                '/',
                $brand
            )
        );

    $brand =
        trim(
            $brand,
            '/'
        );

    if ($brand === '') {
        return '';
    }

    $directPath =
        __DIR__ .
        '/' .
        $brand .
        '/img';

    if (
        is_dir($directPath)
    ) {
        return $brand;
    }

    $entries =
        @scandir(
            __DIR__
        );

    if (
        is_array($entries)
    ) {

        foreach (
            $entries
            as $entry
        ) {

            if (
                $entry === '.' ||
                $entry === '..'
            ) {
                continue;
            }

            if (
                strcasecmp(
                    $entry,
                    $brand
                ) !== 0
            ) {
                continue;
            }

            if (
                is_dir(
                    __DIR__ .
                    '/' .
                    $entry .
                    '/img'
                )
            ) {
                return $entry;
            }
        }
    }

    return '';
}

/*
 * ============================================================
 * GENERATE IMAGE URL
 * ============================================================
 */

function findActualImagePath(
    string $value,
    string $brand,
    string $title,
    string $field
): string {

    /*
     * IMPORTANT:
     * Do NOT invent a fallback directory.
     * If the brand directory does not exist,
     * return an empty value instead of creating:
     *
     * https://techdealshub.online//img/...
     */

    $brandDirectory =
        findBrandDirectory(
            $brand
        );

    if ($brandDirectory === '') {
        return '';
    }

    $phoneName =
        normalizePhoneFileName(
            $title,
            $brand
        );

    $imageType =
        getImageType(
            $field
        );

    $imageRatio =
        getImageRatio(
            $field
        );

    if (
        $phoneName === '' ||
        $imageType === '' ||
        $imageRatio === ''
    ) {
        return '';
    }

    return
        'https://techdealshub.online/' .
        rawurlencode(
            strtolower(
                $brandDirectory
            )
        ) .
        '/img/' .
        rawurlencode(
            $phoneName
        ) .
        '_' .
        rawurlencode(
            $imageType
        ) .
        '_' .
        rawurlencode(
            $imageRatio
        ) .
        '.webp';
}

/*
 * ============================================================
 * PHONE CARD ALTERNATIVE IMAGE BUILDER
 * ============================================================
 */

function normalizeCardImagePart(
    string $value
): string {

    $value =
        trim(
            strip_tags($value)
        );

    $value =
        strtolower($value);

    $value =
        preg_replace(
            '/[^a-z0-9]+/i',
            '',
            $value
        ) ?? '';

    return trim($value);
}

function fixPhoneCardAlternativeImages(
    string $html
): string {

    return preg_replace_callback(
        '~<article\b[^>]*\bclass\s*=\s*(["\'])[^"\']*\bphone-card\b[^"\']*\1[^>]*>.*?</article>~isu',
        static function (
            array $match
        ): string {

            $card =
                $match[0];

            if (
                !preg_match(
                    '~<span\b[^>]*>(.*?)</span>~isu',
                    $card,
                    $brandMatch
                )
            ) {
                return $card;
            }

            if (
                !preg_match(
                    '~<h3\b[^>]*>(.*?)</h3>~isu',
                    $card,
                    $titleMatch
                )
            ) {
                return $card;
            }

            $brand =
                normalizeCardImagePart(
                    $brandMatch[1]
                );

            $title =
                normalizeCardImagePart(
                    $titleMatch[1]
                );

            if (
                $brand === '' ||
                $title === ''
            ) {
                return $card;
            }

            $imagePath =
                'https://techdealshub.online/' .
                $brand .
                '/img/' .
                $title .
                '_hero_3_2.webp';

            return preg_replace_callback(
                '~(<img\b[^>]*\bsrc\s*=\s*)(["\'])([^"\']*)(\2)~isu',
                static function (
                    array $imageMatch
                ) use (
                    $imagePath
                ): string {

                    $src =
                        $imageMatch[3];

                    if (
                        stripos(
                            $src,
                            '_alt1_3_2.webp'
                        ) === false &&
                        stripos(
                            $src,
                            '_alt2_3_2.webp'
                        ) === false &&
                        stripos(
                            $src,
                            '_alt3_3_2.webp'
                        ) === false
                    ) {
                        return $imageMatch[0];
                    }

                    return
                        $imageMatch[1] .
                        $imageMatch[2] .
                        htmlspecialchars(
                            $imagePath,
                            ENT_QUOTES,
                            'UTF-8'
                        ) .
                        $imageMatch[4];
                },
                $card,
                1
            ) ?? $card;
        },
        $html
    ) ?? $html;
}

/*
 * ============================================================
 * REVIEWS DATA
 * ============================================================
 */

function normalizeReviewScore(
    string $value
): int|float {

    $value =
        trim($value);

    if ($value === '') {
        return 0;
    }

    if (
        preg_match(
            '/-?\d+(?:[.,]\d+)?/',
            $value,
            $match
        )
    ) {

        $number =
            (float) str_replace(
                ',',
                '.',
                $match[0]
            );

        return floor($number) === $number
            ? (int) $number
            : $number;
    }

    return 0;
}

/*
 * ============================================================
 * FIRST REVIEW FIELD
 * ============================================================
 */

function getFirstReviewFieldValue(
    array $allPlaceholders,
    array $data,
    array $fieldNames
): string {

    foreach (
        $fieldNames
        as $fieldName
    ) {

        $value =
            getFieldValue(
                $allPlaceholders,
                $data,
                $fieldName
            );

        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function getBestFeatureBadge(
    array $data,
    array $allPlaceholders
): string {

    $features = [
        'SCORE_DESIGN'      => 'Best Design',
        'SCORE_DISPLAY'     => 'Best Display',
        'SCORE_PERFORMANCE' => 'Best Performance',
        'SCORE_CAMERA'      => 'Best Camera',
        'SCORE_BATTERY'     => 'Best Battery',
        'SCORE_SOFTWARE'    => 'Best Software',
    ];

    $bestName  = '';
    $bestScore = -1;

    foreach ($features as $field => $label) {

        $value = getFieldValue(
            $allPlaceholders,
            $data,
            $field
        );

        if ($value === null || trim((string)$value) === '') {
            continue;
        }

        $score = (float)str_replace(
            ',',
            '.',
            trim((string)$value)
        );

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestName  = $label;
        }
    }

    return $bestName;
}
function getCategoryPage(string $category): ?string
{
    $category = trim($category);

    return match ($category) {
        'Flagship'  => 'flagship-phones.html',
        'Mid-Range' => 'mid-range-phones.html',
        'Budget'    => 'budget-phones.html',
        default     => null,
    };
}
function getPhoneCardTemplate(
    string $html,
    string $gridId,
    string $cardClass
): ?string {
    $gridPattern = sprintf(
        '/(<[^>]+id=["\']%s["\'][^>]*>)(.*?)(<\/[^>]+>)/is',
        preg_quote($gridId, '/')
    );

    if (!preg_match($gridPattern, $html, $gridMatch)) {
        return null;
    }

    $gridContent = $gridMatch[2];

    $cardPattern = sprintf(
        '/<article\b[^>]*class=["\'][^"\']*\b%s\b[^"\']*["\'][^>]*>.*?<\/article>/is',
        preg_quote($cardClass, '/')
    );

    if (!preg_match($cardPattern, $gridContent, $cardMatch)) {
        return null;
    }

    return $cardMatch[0];
}
function fillPhoneCardTemplate(
    string $cardHtml,
    array $cardData,
    string $category
): string {

    $brand       = $cardData['brand'];
    $title       = $cardData['title'];
    $description = $cardData['description'];
    $price       = $cardData['price'];
    $rating      = $cardData['rating'];
    $reviews     = $cardData['reviews'];
    $display     = $cardData['display'];
    $processor   = $cardData['processor'];
    $camera      = $cardData['camera'];
    $battery     = $cardData['battery'];
    $heroImage   = $cardData['hero_image'];
    $badge       = $cardData['badge'];
    $reviewUrl   = $cardData['review_url'];

    /*
     * ============================================================
     * DATA BRAND
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/data-brand=(["\']).*?\1/i',
        'data-brand="' . e($brand) . '"',
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * DATA NAME
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/data-name=(["\']).*?\1/i',
        'data-name="' . e($title) . '"',
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * PRICE NUMBER
     * ============================================================
     */

    $priceNumber = '';

    if (
        preg_match_all(
            '/\d+(?:,\d{3})*(?:\.\d+)?/',
            $price,
            $priceMatches
        ) &&
        !empty($priceMatches[0])
    ) {

        $priceNumber = str_replace(
            ',',
            '',
            end($priceMatches[0])
        );
    }

    /*
     * ============================================================
     * DATA PRICE
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/data-price=(["\']).*?\1/i',
        'data-price="' . e($priceNumber) . '"',
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * RATING /5
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/data-rating=(["\']).*?\1/i',
        'data-rating="' . e($rating) . '"',
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * BADGE
     * ============================================================
     *
     * Works with:
     *
     * phone-badge
     * badge
     */

    if ($badge !== '') {

        $cardHtml = preg_replace(
            '/(<span\b[^>]*class=(["\'])[^"\']*\b(?:phone-)?badge\b[^"\']*\2[^>]*>).*?(<\/span>)/is',
            '$1' . e($badge) . '$3',
            $cardHtml,
            1
        );
    }

    /*
     * ============================================================
     * HERO IMAGE
     * ============================================================
     *
     * Only the first img src is changed.
     * No href is touched here.
     */

    $cardHtml = preg_replace_callback(
        '/(<img\b[^>]*\bsrc=)(["\']).*?\2/i',
        static function ($m) use ($heroImage) {

            return
                $m[1] .
                $m[2] .
                e($heroImage) .
                $m[2];
        },
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * ALT
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/(\balt=)(["\']).*?\2/i',
        '$1$2' . e($title) . '$2',
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * BRAND TEXT
     * ============================================================
     *
     * Supports:
     *
     * <span class="phone-brand">
     * <div class="phone-brand">
     * <span class="brand">
     * <div class="brand">
     */

    $cardHtml = preg_replace(
        '/(<(?:span|div)\b[^>]*class=(["\'])[^"\']*\b(?:phone-brand|brand)\b[^"\']*\2[^>]*>).*?(<\/(?:span|div)>)/is',
        '$1' . e($brand) . '$3',
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * TITLE
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/(<h3\b[^>]*>).*?(<\/h3>)/is',
        '$1' . e($title) . '$2',
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * DESCRIPTION
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/(<p\b[^>]*class=(["\'])[^"\']*\bphone-description\b[^"\']*\2[^>]*>).*?(<\/p>)/is',
        '$1' . e($description) . '$3',
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * RATING NUMBER
     * ============================================================
     */

    $cardHtml = preg_replace_callback(
        '/(<span\b[^>]*class=(["\'])[^"\']*\brating-number\b[^"\']*\2[^>]*>).*?(<\/span>)/is',
        static function ($m) use ($rating) {

            return
                $m[1] .
                e($rating) .
                $m[3];
        },
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * REVIEW COUNT
     * ============================================================
     *
     * Supports both card templates:
     *
     * rating-reviews
     * rating-count
     *
     * The original class is preserved.
     */

    $cardHtml = preg_replace_callback(
        '/(<span\b[^>]*class=(["\'])[^"\']*\b(?:rating-reviews|rating-count)\b[^"\']*\2[^>]*>).*?(<\/span>)/is',
        static function ($m) use ($reviews) {

            return
                $m[1] .
                e($reviews) .
                ' reviews' .
                $m[3];
        },
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * SPECS
     * ============================================================
     *
     * The original spec structure is preserved.
     *
     * Order:
     *
     * 1. Display
     * 2. Processor / Chip
     * 3. Camera
     * 4. Battery
     */

    $specValues = [
        $display,
        $processor,
        $camera,
        $battery
    ];

    $specIndex = 0;

    $cardHtml = preg_replace_callback(
        '/(<span\b[^>]*class=(["\'])[^"\']*\bspec-value\b[^"\']*\2[^>]*>).*?(<\/span>)/is',
        static function ($m) use (
            &$specIndex,
            $specValues
        ) {

            if (!isset($specValues[$specIndex])) {
                return $m[0];
            }

            $value =
                $specValues[$specIndex++];

            return
                $m[1] .
                e($value) .
                $m[3];
        },
        $cardHtml
    );

    /*
     * ============================================================
     * PRICE DISPLAY
     * ============================================================
     *
     * IMPORTANT:
     *
     * We DO NOT rebuild the .price element.
     *
     * Two existing card structures are supported:
     *
     * ------------------------------------------------------------
     * FLAGSHIP
     *
     * <div class="price">
     *     $1,299
     *     <span>Starting price</span>
     * </div>
     *
     * ------------------------------------------------------------
     * MID-RANGE / OTHER
     *
     * <div class="price">
     *     <strong>$449</strong>
     *     <span>Starting price</span>
     * </div>
     *
     * The original structure is preserved.
     */

    $priceFormatted = $price;

    if (
        $priceNumber !== '' &&
        is_numeric($priceNumber)
    ) {

        $priceFormatted =
            '$' .
            number_format(
                (float)$priceNumber,
                0,
                '.',
                ','
            );
    }

    /*
     * ------------------------------------------------------------
     * CASE 1:
     * Existing price contains <strong>
     * ------------------------------------------------------------
     */

    if (
        preg_match(
            '/<div\b[^>]*class=(["\'])[^"\']*\bprice\b[^"\']*\1[^>]*>.*?<strong\b[^>]*>.*?<\/strong>/is',
            $cardHtml
        )
    ) {

        $cardHtml = preg_replace_callback(
            '/(<div\b[^>]*class=(["\'])[^"\']*\bprice\b[^"\']*\2[^>]*>.*?<strong\b[^>]*>).*?(<\/strong>)/is',
            static function ($m) use ($priceFormatted) {

                return
                    $m[1] .
                    e($priceFormatted) .
                    $m[3];
            },
            $cardHtml,
            1
        );

    } else {

        /*
         * --------------------------------------------------------
         * CASE 2:
         * Existing price has plain text before Starting price
         * --------------------------------------------------------
         *
         * Example:
         *
         * <div class="price">
         *     $1,299
         *     <span>Starting price</span>
         * </div>
         *
         * Only the existing price text is replaced.
         * The <span> remains untouched.
         */

        $cardHtml = preg_replace_callback(
            '/(<div\b[^>]*class=(["\'])[^"\']*\bprice\b[^"\']*\2[^>]*>)(.*?)(<span\b[^>]*>\s*Starting price\s*<\/span>)/is',
            static function ($m) use ($priceFormatted) {

                return
                    $m[1] .
                    e($priceFormatted) .
                    $m[4];
            },
            $cardHtml,
            1
        );
    }

    /*
     * ============================================================
     * REVIEW URL
     * ============================================================
     *
     * ONLY the Review button is changed.
     *
     * Other href values remain untouched.
     */

    $safeReviewUrl = e($reviewUrl);

    /*
     * DATA SHARE URL
     */

    $cardHtml = preg_replace(
        '/(\bdata-share-url=)(["\']).*?\2/i',
        '$1$2' . $safeReviewUrl . '$2',
        $cardHtml
    );

    /*
     * REAL REVIEW BUTTON
     *
     * Supports different attribute orders.
     */

    $cardHtml = preg_replace_callback(
        '/<a\b[^>]*\bclass=(["\'])[^"\']*\bdetails-btn\b[^"\']*\1[^>]*>/i',
        static function ($m) use ($safeReviewUrl) {

            $tag = $m[0];

            /*
             * If href exists, replace only its value.
             */

            if (
                preg_match(
                    '/\bhref=(["\']).*?\1/i',
                    $tag
                )
            ) {

                $tag = preg_replace(
                    '/\bhref=(["\']).*?\1/i',
                    'href="' . $safeReviewUrl . '"',
                    $tag,
                    1
                );

            } else {

                /*
                 * If there is no href,
                 * add one without touching other attributes.
                 */

                $tag = preg_replace(
                    '/<a\b/i',
                    '<a href="' . $safeReviewUrl . '"',
                    $tag,
                    1
                );
            }

            return $tag;
        },
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * SHARE TITLE
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/(\bdata-share-title=)(["\']).*?\2/i',
        '$1$2' . e($title) . '$2',
        $cardHtml
    );

    /*
     * ============================================================
     * PHONE NAME
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/(\bdata-phone=)(["\']).*?\2/i',
        '$1$2' . e($title) . '$2',
        $cardHtml
    );

    /*
     * ============================================================
     * FAVORITE LABEL
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/(\baria-label=)(["\'])Add .*? to favorites\2/i',
        '$1$2Add ' .
        e($title) .
        ' to favorites$2',
        $cardHtml
    );

    /*
     * ============================================================
     * SHARE LABEL
     * ============================================================
     */

    $cardHtml = preg_replace(
        '/(\baria-label=)(["\'])Share .*?\2/i',
        '$1$2Share ' .
        e($title) .
        '$2',
        $cardHtml
    );

    /*
     * ============================================================
     * MARK ONLY THE NEW CARD
     * ============================================================
     *
     * This does not modify existing href values.
     */

    $cardHtml = preg_replace(
        '/<article\b/i',
        '<article data-tdh-generated="1" data-tdh-review-url="' .
        $safeReviewUrl .
        '"',
        $cardHtml,
        1
    );

    /*
     * ============================================================
     * RETURN
     * ============================================================
     */

    return $cardHtml;
}
function updateCategoryPhoneCard(
    array $data,
    array $allPlaceholders,
    string $reviewUrl
): string {

    $category = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'CATEGORY'
    ));

    /*
     * CATEGORY -> PAGE
     */

    $categoryPages = [
        'Flagship' => [
            'file'  => __DIR__ . '/flagship-phones.html',
            'grid'  => 'phoneGrid',
            'class' => 'phone-card',
        ],

        'Mid-Range' => [
            'file'  => __DIR__ . '/mid-range-phones.html',
            'grid'  => 'phoneGrid',
            'class' => 'phone-card',
        ],

        'Budget' => [
            'file'  => __DIR__ . '/budget-phones.html',
            'grid'  => 'phoneGrid',
            'class' => 'phone-card',
        ],
    ];

    if (!isset($categoryPages[$category])) {
        return 'CATEGORY_NOT_FOUND: ' . $category;
    }

    $config = $categoryPages[$category];

    $file = $config['file'];

    if (!is_file($file)) {
        return 'CATEGORY_FILE_NOT_FOUND: ' . basename($file);
    }

    $html = file_get_contents($file);

    if ($html === false) {
        return 'CATEGORY_FILE_READ_ERROR: ' . basename($file);
    }

    /*
     * ----------------------------------------------------------
     * FIND PHONE GRID
     * ----------------------------------------------------------
     */

    $gridPattern = sprintf(
        '/<div\b[^>]*\bid=(["\'])%s\1[^>]*>/i',
        preg_quote($config['grid'], '/')
    );

    if (!preg_match(
        $gridPattern,
        $html,
        $gridMatch,
        PREG_OFFSET_CAPTURE
    )) {
        return 'GRID_NOT_FOUND: ' . $config['grid'];
    }

    $gridOpen = $gridMatch[0][0];
    $gridStart = $gridMatch[0][1];

    $contentStart =
        $gridStart +
        strlen($gridOpen);

    /*
     * ----------------------------------------------------------
     * FIND END OF GRID
     * ----------------------------------------------------------
     */

    $afterGrid = substr(
        $html,
        $contentStart
    );

    preg_match_all(
        '/<\/?div\b[^>]*>/i',
        $afterGrid,
        $divMatches,
        PREG_OFFSET_CAPTURE
    );

    $depth = 1;
    $gridEnd = null;

    foreach ($divMatches[0] as $divMatch) {

        $tag = $divMatch[0];
        $offset = $divMatch[1];

        if (stripos($tag, '</div') === 0) {
            $depth--;
        } else {
            $depth++;
        }

        if ($depth === 0) {
            $gridEnd =
                $contentStart +
                $offset +
                strlen($tag);

            break;
        }
    }

    if ($gridEnd === null) {
        return 'GRID_END_NOT_FOUND: ' . $config['grid'];
    }

    /*
     * ----------------------------------------------------------
     * GET GRID CONTENT
     * ----------------------------------------------------------
     */

    $gridContent = substr(
        $html,
        $contentStart,
        $gridEnd -
        $contentStart -
        strlen('</div>')
    );

    /*
     * ----------------------------------------------------------
     * BUILD NEW PHONE DATA
     * ----------------------------------------------------------
     */

    $cardData = buildPhoneCardData(
        $data,
        $allPlaceholders,
        $reviewUrl
    );

    /*
     * ----------------------------------------------------------
     * GET CLEAN ORIGINAL CARD
     *
     * NEVER use a generated card as template.
     * ----------------------------------------------------------
     */

    $cardPattern = sprintf(
        '/<article\b(?=[^>]*\bclass=(["\'])[^"\']*\b%s\b[^"\']*\1)(?![^>]*\bdata-tdh-generated\s*=\s*(["\'])1\2)[^>]*>.*?<\/article>/is',
        preg_quote($config['class'], '/')
    );

    if (!preg_match(
        $cardPattern,
        $gridContent,
        $templateMatch
    )) {
        return 'CLEAN_CARD_TEMPLATE_NOT_FOUND';
    }

    $cleanCard = $templateMatch[0];

    /*
     * ----------------------------------------------------------
     * CREATE NEW CARD
     * ----------------------------------------------------------
     */

    $newCard = fillPhoneCardTemplate(
        $cleanCard,
        $cardData,
        $category
    );

    if (
        trim($newCard) === '' ||
        stripos($newCard, 'data-tdh-generated="1"') === false
    ) {
        return 'GENERATED_CARD_BUILD_ERROR';
    }

    /*
     * ----------------------------------------------------------
     * FIND GENERATED CARD FOR THIS PHONE ONLY
     * ----------------------------------------------------------
     */

    $phoneName = trim((string)$cardData['title']);

    $phoneNameEscaped = preg_quote(
        $phoneName,
        '/'
    );

    $generatedPhonePattern =
        '/<article\b(?=[^>]*\bdata-tdh-generated\s*=\s*(["\'])1\1)(?=[^>]*\bdata-name\s*=\s*(["\'])' .
        $phoneNameEscaped .
        '\2)[^>]*>.*?<\/article>/is';

    /*
     * ----------------------------------------------------------
     * REPLACE ONLY THIS PHONE'S OLD GENERATED CARD
     * ----------------------------------------------------------
     */

    if (preg_match(
        $generatedPhonePattern,
        $gridContent
    )) {

        $gridContent = preg_replace(
            $generatedPhonePattern,
            $newCard,
            $gridContent,
            1
        );

        if ($gridContent === null) {
            return 'GENERATED_CARD_REPLACE_ERROR';
        }

    } else {

        /*
         * ------------------------------------------------------
         * NO CARD FOR THIS PHONE
         *
         * Add a completely new card.
         * Keep all existing cards untouched.
         * ------------------------------------------------------
         */

        $gridContent =
            rtrim($gridContent) .
            "\n\n" .
            $newCard .
            "\n";
    }

    /*
     * ----------------------------------------------------------
     * REBUILD PAGE
     * ----------------------------------------------------------
     */

    $newHtml =
        substr(
            $html,
            0,
            $contentStart
        ) .
        $gridContent .
        substr(
            $html,
            $gridEnd -
            strlen('</div>')
        );

    /*
     * ----------------------------------------------------------
     * SAVE
     * ----------------------------------------------------------
     */

    if (
        file_put_contents(
            $file,
            $newHtml
        ) === false
    ) {
        return 'CATEGORY_FILE_WRITE_ERROR: ' .
            basename($file);
    }

    return 'CATEGORY_UPDATED: ' .
        basename($file);
}
function buildPhoneCardData(
    array $data,
    array $allPlaceholders,
    string $reviewUrl
): array {

    $title = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'TITLE'
    ));

    $brand = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'BRAND'
    ));

    $description = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'DESCRIPTION'
    ));
     
    $price = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'STARTING_PRICE'
    ));

    /*
     * USER_AVERAGE is /10.
     * The category card uses /5.
     *
     * 9.6 -> 4.8
     * 9.4 -> 4.7
     * 9.0 -> 4.5
     */
foreach ($allPlaceholders as $index => $field) {
    if (strcasecmp(trim($field), 'USER_AVERAGE') === 0) {
        echo '<pre>';
        echo 'USER_AVERAGE FIELD NUMBER = ' . ($index + 1) . PHP_EOL;
        echo 'POST VALUE = [' . ($_POST['field_' . ($index + 1)] ?? '') . ']' . PHP_EOL;
        echo '</pre>';
        break;
    }
}

$userAverage = trim((string)getFieldValue(
    $allPlaceholders,
    $data,
    'USER_AVERAGE'
));
    

$rating = '';

if ($userAverage !== '') {
    $rating = number_format(
    (float)str_replace(',', '.', $userAverage),
    1,
    '.',
    ''
);
  echo '<pre>';
echo 'USER_AVERAGE = [' . $userAverage . ']' . PHP_EOL;
echo 'RATING = [' . $rating . ']' . PHP_EOL;
echo '</pre>';
}



    $reviews = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'USER_TOTAL_REVIEWS'
    ));

    /*
     * Prevent duplicated "reviews"
     */

    $reviews = preg_replace(
        '/\s*reviews?\s*$/i',
        '',
        $reviews
    );

    $displaySize = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'DISPLAY_SIZE'
    ));

    $displayPanel = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'DISPLAY_PANEL'
    ));

    $processor = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'PROCESSOR'
    ));

    $mainCamera = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'MAIN_CAMERA'
    ));

    $battery = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'BATTERY_CAPACITY'
    ));

    $heroImage = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'HERO_IMAGE'
    ));

    $category = trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'CATEGORY'
    ));

    $badge = getBestFeatureBadge(
        $data,
        $allPlaceholders
    );

    return [
        'brand'       => $brand,
        'title'       => $title,
        'description' => $description,
        'price'       => $price,
        'rating'      => $rating,
        'reviews'     => $reviews,
        'display'     => trim(
            $displaySize . ' ' . $displayPanel
        ),
        'processor'   => $processor,
        'camera'      => $mainCamera,
        'battery'     => $battery,
        'hero_image'  => $heroImage,
        'category'    => $category,
        'badge'       => $badge,
        'review_url'  => $reviewUrl,
    ];
}
/*
 * ============================================================
 * UPDATE reviews-data.php
 * ============================================================
 */

function updateReviewsData(
    string $brand,
    string $title,
    string $score,
    string $cameraScore,
    string $gamingScore,
    string $description,
    string $category,
    string $image,
    string $url,
    array $sourceData = [],
    array $sourcePlaceholders = []
): bool {

    $dataPath =
        __DIR__ .
        '/reviews-data.php';

    $reviews = [];

    /*
     * ============================================================
     * READ EXISTING DATA
     * ============================================================
     */

    if (is_file($dataPath)) {

        $existingReturn = null;
        $existingOutput = '';
        $bufferStarted = false;

        try {

            ob_start();

            $bufferStarted = true;

            $existingReturn =
                include $dataPath;

            $existingOutput =
                (string) ob_get_clean();

            $bufferStarted = false;

        } catch (Throwable $e) {

            if ($bufferStarted) {
                ob_end_clean();
            }

            $existingReturn = null;
            $existingOutput = '';
        }

        /*
         * Existing PHP array
         */

        if (
            is_array($existingReturn) &&
            isset($existingReturn['reviews']) &&
            is_array($existingReturn['reviews'])
        ) {

            $reviews =
                $existingReturn['reviews'];

        } else {

            /*
             * Existing JSON output
             */

            $decoded =
                json_decode(
                    trim($existingOutput),
                    true
                );

            if (
                is_array($decoded) &&
                isset($decoded['reviews']) &&
                is_array($decoded['reviews'])
            ) {

                $reviews =
                    $decoded['reviews'];
            }
        }
    }

    /*
     * ============================================================
     * BUILD COMPLETE CARD DATA
     * ============================================================
     *
     * IMPORTANT:
     *
     * Do NOT use an undefined $cardData from the outer scope.
     *
     * Build it here from the SAME $data and $allPlaceholders
     * that belong to the phone currently being generated.
     *
     * ============================================================
     */

    $cardData = [];

    if (
        !empty($sourceData) &&
        !empty($sourcePlaceholders)
    ) {

        $cardData =
            buildPhoneCardData(
                $sourceData,
                $sourcePlaceholders,
                $url
            );
    }

    /*
     * ============================================================
     * FALLBACK VALUES
     * ============================================================
     *
     * These are used only if cardData could not be built.
     *
     * ============================================================
     */

    $cardBrand =
        trim(
            (string)(
                $cardData['brand']
                ?? $brand
            )
        );

    $cardTitle =
        trim(
            (string)(
                $cardData['title']
                ?? $title
            )
        );

    $cardDescription =
        trim(
            (string)(
                $cardData['description']
                ?? $description
            )
        );

    $cardPrice =
        trim(
            (string)(
                $cardData['price']
                ?? ''
            )
        );

    $cardRating =
    trim((string)$score);

    $cardReviews =
        trim(
            (string)(
                $cardData['reviews']
                ?? ''
            )
        );

    $cardDisplay =
        trim(
            (string)(
                $cardData['display']
                ?? ''
            )
        );

    $cardProcessor =
        trim(
            (string)(
                $cardData['processor']
                ?? ''
            )
        );

    $cardCamera =
        trim(
            (string)(
                $cardData['camera']
                ?? ''
            )
        );

    $cardBattery =
        trim(
            (string)(
                $cardData['battery']
                ?? ''
            )
        );

    $cardHeroImage =
        trim(
            (string)(
                $cardData['hero_image']
                ?? $image
            )
        );

    $cardCategory =
        trim(
            (string)(
                $cardData['category']
                ?? $category
            )
        );

    $cardBadge =
        trim(
            (string)(
                $cardData['badge']
                ?? ''
            )
        );

    $cardReviewUrl =
        trim(
            (string)(
                $cardData['review_url']
                ?? $url
            )
        );

    /*
     * ============================================================
     * NEW REVIEW RECORD
     * ============================================================
     */

    $newReview = [

        'brand' =>
            $cardBrand,

        'title' =>
            $cardTitle,

        'score' =>
            normalizeReviewScore(
                $cardRating
            ),

        'camera_score' =>
            normalizeReviewScore(
                $cameraScore
            ),

        'gaming_score' =>
            normalizeReviewScore(
                $gamingScore
            ),

        'description' =>
            $cardDescription,

        'category' =>
            $cardCategory !== ''
                ? $cardCategory
                : 'Smartphones',

        /*
         * Image
         */

        'image' =>
            $cardHeroImage,

        /*
         * Review URL
         */

        'url' =>
            $cardReviewUrl,

        /*
         * ========================================================
         * COMPLETE PHONE CARD VALUES
         * ========================================================
         */

        'price' =>
            $cardPrice,

        'reviews' =>
            $cardReviews,

        'display' =>
            $cardDisplay,

        'processor' =>
            $cardProcessor,

        'camera' =>
            $cardCamera,

        'battery' =>
            $cardBattery,

        'badge' =>
            $cardBadge,
    ];

    /*
     * ============================================================
     * REPLACE EXISTING PHONE
     * ============================================================
     */

    $replaced = false;

    foreach (
        $reviews
        as $index => $review
    ) {

        if (
            is_array($review) &&
            trim(
                (string)(
                    $review['url'] ?? ''
                )
            ) ===
            $cardReviewUrl
        ) {

            $reviews[$index] =
                $newReview;

            $replaced = true;

            break;
        }
    }

    /*
     * ============================================================
     * ADD NEW PHONE
     * ============================================================
     */

    if (!$replaced) {

        $reviews[] =
            $newReview;
    }

    /*
     * ============================================================
     * BUILD UNIQUE BRANDS
     * ============================================================
     */

    $brands = [];

    foreach (
        $reviews
        as $review
    ) {

        if (!is_array($review)) {
            continue;
        }

        $reviewBrand =
            trim(
                (string)(
                    $review['brand'] ?? ''
                )
            );

        if (
            $reviewBrand !== '' &&
            !in_array(
                $reviewBrand,
                $brands,
                true
            )
        ) {

            $brands[] =
                $reviewBrand;
        }
    }

    /*
     * ============================================================
     * FINAL PAYLOAD
     * ============================================================
     */

    $payload = [

        'reviews' =>
            array_values(
                $reviews
            ),

        'brands' =>
            $brands,
    ];

    /*
     * ============================================================
     * EXPORT PHP ARRAY
     * ============================================================
     */

    $exported =
        var_export(
            $payload,
            true
        );

    /*
     * ============================================================
     * CREATE reviews-data.php
     * ============================================================
     */

    $php =
        "<?php\n" .
        "declare(strict_types=1);\n\n" .
        "\$data = " .
        $exported .
        ";\n\n" .
        "if (basename(__FILE__) === basename(\$_SERVER['SCRIPT_FILENAME'] ?? '')) {\n" .
        "    header('Content-Type: application/json; charset=utf-8');\n" .
        "    echo json_encode(\$data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);\n" .
        "}\n\n" .
        "return \$data;\n";

    /*
     * ============================================================
     * WRITE FILE
     * ============================================================
     */

    $bytes =
        file_put_contents(
            $dataPath,
            $php
        );

    /*
     * ============================================================
     * VERIFY
     * ============================================================
     */

    return
        $bytes !== false &&
        is_file($dataPath) &&
        (filesize($dataPath) ?: 0) > 0;
}
function addReviewCardToReviewsHtml(
    string $brand,
    string $title,
    string $score,
    string $description,
    string $category,
    string $image,
    string $url
): bool {

    $reviewsHtmlPath = __DIR__ . '/reviews.html';

    if (!is_file($reviewsHtmlPath)) {
        return false;
    }

    $html = file_get_contents($reviewsHtmlPath);

    if ($html === false || $html === '') {
        return false;
    }

    /*
     * ============================================================
     * ESCAPE VALUES ONLY
     * لا نلمس أي href موجود مسبقاً في reviews.html
     * ============================================================
     */

    $e = static function (string $value): string {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    };

    $safeBrand = $e(trim($brand));
    $safeTitle = $e(trim($title));

    $safeDescription = $e(
        trim($description)
    );

    $safeCategory = $e(
        trim($category) !== ''
            ? trim($category)
            : 'Smartphones'
    );

    $safeImage = $e(
        trim($image)
    );

    $safeUrl = $e(
        trim($url)
    );

    /*
     * ============================================================
     * SCORE
     * ============================================================
     */

    $scoreNumber =
        preg_replace(
            '/[^0-9.]/',
            '',
            $score
        );

    $scoreValue =
        (float) $scoreNumber;

    $safeScore =
        number_format(
            $scoreValue,
            1,
            '.',
            ''
        );

    /*
     * ============================================================
     * UNIQUE MARKER
     * يعتمد على رابط المراجعة
     * ============================================================
     */

    $hash =
        hash(
            'sha256',
            trim($url)
        );

    $startMarker =
        '<!-- TDH_REVIEW_CARD_START:' .
        $hash .
        ' -->';

    $endMarker =
        '<!-- TDH_REVIEW_CARD_END:' .
        $hash .
        ' -->';

    /*
     * ============================================================
     * STATIC REVIEW CARD
     * ============================================================
     */

    $card =
        $startMarker . "\n" .

        '<article class="review-card">' . "\n" .

        '<a href="' .
        $safeUrl .
        '">' . "\n" .

        '<div class="card-top">' . "\n" .

        '<span class="brand">' .
        $safeBrand .
        '</span>' . "\n" .

        '<span class="rank">#1</span>' . "\n" .

        '</div>' . "\n" .

        '<div class="phone-image">' . "\n" .

        '<img src="' .
        $safeImage .
        '" alt="' .
        $safeTitle .
        '" loading="lazy">' . "\n" .

        '</div>' . "\n" .

        '<div class="card-body">' . "\n" .

        '<div class="title-row">' . "\n" .

        '<h3>' .
        $safeTitle .
        '</h3>' . "\n" .

        '<span class="score">' .

        '<i class="fa-solid fa-star"></i> ' .

        $safeScore .

        '</span>' . "\n" .

        '</div>' . "\n" .

        '<p class="description">' .
        $safeDescription .
        '</p>' . "\n" .

        '<div class="card-bottom">' . "\n" .

        '<span class="category">' .
        $safeCategory .
        '</span>' . "\n" .

        '<span class="read">' .
        'Read Full Review ' .
        '<i class="fa-solid fa-arrow-right"></i>' .
        '</span>' . "\n" .

        '</div>' . "\n" .

        '</div>' . "\n" .

        '</a>' . "\n" .

        '</article>' . "\n" .

        $endMarker . "\n";

    /*
     * ============================================================
     * إذا كانت البطاقة موجودة مسبقاً:
     * استبدل بطاقتها فقط
     * ============================================================
     */

    $existingPattern =
        '~' .
        preg_quote(
            $startMarker,
            '~'
        ) .
        '.*?' .
        preg_quote(
            $endMarker,
            '~'
        ) .
        '~s';

    if (
        preg_match(
            $existingPattern,
            $html
        )
    ) {

        $newHtml =
            preg_replace(
                $existingPattern,
                $card,
                $html,
                1
            );

        if ($newHtml === null) {
            return false;
        }

        return
            file_put_contents(
                $reviewsHtmlPath,
                $newHtml,
                
            ) !== false;
    }

    /*
     * ============================================================
     * أضف البطاقة بعد:
     *
     * <!-- TDH_STATIC_REVIEW_CARDS -->
     *
     * وقبل #reviewGrid
     *
     * ============================================================
     */

    $staticMarker =
        '<!-- TDH_STATIC_REVIEW_CARDS -->';

    $markerPosition =
        strpos(
            $html,
            $staticMarker
        );

    if ($markerPosition === false) {
        return false;
    }

    $insertPosition =
        $markerPosition +
        strlen($staticMarker);

    $newHtml =
        substr(
            $html,
            0,
            $insertPosition
        ) .
        "\n\n" .
        $card .
        "\n" .
        substr(
            $html,
            $insertPosition
        );

    /*
     * ============================================================
     * حفظ reviews.html
     *
     * لا يتم تعديل:
     * href
     * nav
     * menu
     * footer
     * CSS
     * JavaScript
     * أو أي محتوى آخر
     * ============================================================
     */

    return
        file_put_contents(
            $reviewsHtmlPath,
            $newHtml,
            
        ) !== false;
}

/*
 * ============================================================
 * EMBED REVIEWS DATA INTO reviews.html
 * ============================================================
 *
 * IMPORTANT:
 * reviews.html must work on GitHub Pages.
 * Therefore it must NOT fetch reviews-data.php.
 *
 * PHP reads the data here and permanently writes the values
 * into JavaScript inside reviews.html.
 */

function embedReviewsDataIntoHtml(): bool
{
    $reviewsHtmlPath =
        __DIR__ . '/reviews.html';

    if (!is_file($reviewsHtmlPath)) {
        return false;
    }

    $html =
        file_get_contents($reviewsHtmlPath);

    if ($html === false || $html === '') {
        return false;
    }

    /*
     * READ reviews-data.php
     */

    $dataPath =
        __DIR__ . '/reviews-data.php';

    if (!is_file($dataPath)) {
        return false;
    }

    $data = null;

    try {

        $data =
            include $dataPath;

    } catch (Throwable $e) {

        return false;
    }

    if (
        !is_array($data) ||
        !isset($data['reviews']) ||
        !is_array($data['reviews'])
    ) {
        return false;
    }

    $reviews =
        array_values(
            $data['reviews']
        );

    /*
     * BUILD BRAND LIST FROM REVIEWS
     */

    $brands = [];

    foreach ($reviews as $review) {

        if (!is_array($review)) {
            continue;
        }

        $brand =
            trim(
                (string) (
                    $review['brand'] ?? ''
                )
            );

        if (
            $brand !== '' &&
            !in_array(
                $brand,
                $brands,
                true
            )
        ) {
            $brands[] = $brand;
        }
    }

    /*
     * JAVASCRIPT SAFE JSON
     */

    $reviewsJson =
        json_encode(
            $reviews,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        );

    $brandsJson =
        json_encode(
            $brands,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        );

    if (
        $reviewsJson === false ||
        $brandsJson === false
    ) {
        return false;
    }

    /*
     * ========================================================
     * REPLACE EMPTY JS ARRAYS
     * ========================================================
     */
     $reviewsPattern =
    '/var\s+reviews\s*=\s*\[[\s\S]*?\]\s*;/';

$reviewsReplaced = 0;

$html =
    preg_replace(
        $reviewsPattern,
        'var reviews = ' .
        $reviewsJson .
        ';',
        $html,
        1,
        $reviewsReplaced
    );

if (
    $html === null ||
    $reviewsReplaced !== 1
) {
    return false;
}

$brandPattern =
    '/var\s+brandList\s*=\s*\[[\s\S]*?\]\s*;/';

$brandsReplaced = 0;

$html =
    preg_replace(
        $brandPattern,
        'var brandList = ' .
        $brandsJson .
        ';',
        $html,
        1,
        $brandsReplaced
    );

if (
    $html === null ||
    $brandsReplaced !== 1
) {
    return false;
}

    /*
     * ========================================================
     * REMOVE RUNTIME FETCH
     * ========================================================
     *
     * The old reviews.html code loaded:
     *
     * fetch('reviews-data.php?...')
     *
     * This is not needed on GitHub Pages.
     */

    $fetchPattern =
        '~fetch\s*\(\s*[\'"]reviews-data\.php\?ts=[^)]*\)\s*.*?\.catch\s*\(\s*function\s*\(\s*error\s*\)\s*\{.*?\}\s*\)\s*;~is';

    $directRenderCode = <<<'JS'
renderBrands(brandList);
renderBrandShowcase(reviews);

var totalReviews =
    reviews.length;

var topScore = 0;

reviews.forEach(function(r){

    var score =
        Number(r.score) || 0;

    if(score > topScore){
        topScore = score;
    }

});

var totalReviewsElement =
    document.getElementById(
        'totalReviews'
    );

if(totalReviewsElement){

    totalReviewsElement.textContent =
        totalReviews;

}

var brandCountElement =
    document.getElementById(
        'brandCount'
    );

if(brandCountElement){

    brandCountElement.textContent =
        brandList.length;

}

var topScoreElement =
    document.getElementById(
        'topScore'
    );

if(topScoreElement){

    topScoreElement.textContent =
        topScore.toFixed(1);

}

render();
JS;

    $html =
        preg_replace(
            $fetchPattern,
            $directRenderCode,
            $html,
            1
        ) ?? $html;

    /*
     * ========================================================
     * WRITE reviews.html
     * ========================================================
     */

    return
        file_put_contents(
            $reviewsHtmlPath,
            $html
        ) !== false;
}

/*
 * ============================================================
 * UPDATE featured-reviews.html
 * ============================================================
 *
 * SOURCE:
 *     reviews-data.php
 *
 * IMPORTANT:
 *     - No placeholders
 *     - Existing href values are NOT changed
 *     - Existing card structure is NOT replaced
 *     - Only card content is updated
 *
 * IMAGE RULE:
 *
 *     Card #1:
 *     https://techdealshub.online/{brand}/img/{phone}_1_1.webp
 *
 *     Cards #2 and onward:
 *     https://techdealshub.online/{brand}/img/{phone}_hero_3_2.webp
 *
 * The 12 existing review cards are processed in their
 * existing order.
 */
function updateFeaturedReviewsHtml(): bool
{
    $featuredPath = __DIR__ . '/featured-reviews.html';
    $dataPath     = __DIR__ . '/reviews-data.php';

    if (!is_file($featuredPath) || !is_file($dataPath)) {
        return false;
    }

    $html = file_get_contents($featuredPath);

    if ($html === false || trim($html) === '') {
        return false;
    }

    try {
        $data = include $dataPath;
    } catch (Throwable $e) {
        return false;
    }

    if (
        !is_array($data) ||
        !isset($data['reviews']) ||
        !is_array($data['reviews'])
    ) {
        return false;
    }

    $reviews = array_values($data['reviews']);

    if (empty($reviews)) {
        return false;
    }

    /* ============================================================
     * HELPERS
     * ============================================================ */

    $normalizeTitle = static function ($value): string {
        $value = html_entity_decode(
            strip_tags((string)$value),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $value = trim($value);

        $value = preg_replace(
            '/\s+Reviews?\s*$/iu',
            '',
            $value
        ) ?? $value;

        $value = mb_strtolower(
            trim($value),
            'UTF-8'
        );

        $value = preg_replace(
            '/[^\p{L}\p{N}]+/u',
            '',
            $value
        ) ?? '';

        return trim($value);
    };

    $escape = static function ($value): string {
        return htmlspecialchars(
            (string)$value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    };

    $scoreToCardRating = static function ($score): float {
        $scoreString = trim((string)$score);

        if ($scoreString === '') {
            return 0.0;
        }

        if (
            !preg_match(
                '/-?\d+(?:[.,]\d+)?/',
                $scoreString,
                $match
            )
        ) {
            return 0.0;
        }

        $number = (float)str_replace(
            ',',
            '.',
            $match[0]
        );

        return $number / 2;
    };

    $getReviewUrl = static function (
        array $review
    ): string {
        foreach (['url', 'href'] as $field) {
            if (
                isset($review[$field]) &&
                trim((string)$review[$field]) !== ''
            ) {
                return trim((string)$review[$field]);
            }
        }

        return '';
    };

    $getReadyImage = static function (
        array $review
    ): string {
        foreach (['image', 'src', 'img'] as $field) {
            if (
                isset($review[$field]) &&
                trim((string)$review[$field]) !== ''
            ) {
                return trim((string)$review[$field]);
            }
        }

        return '';
    };

    $buildPhoneName = static function (
        string $brand,
        string $title
    ): string {
        $phoneName = trim($title);

        $phoneName = preg_replace(
            '/\s+Reviews?\s*$/iu',
            '',
            $phoneName
        ) ?? $phoneName;

        if ($brand !== '') {
            $phoneName = preg_replace(
                '/^' . preg_quote($brand, '/') . '\s+/iu',
                '',
                $phoneName
            ) ?? $phoneName;
        }

        $phoneName = mb_strtolower(
            trim($phoneName),
            'UTF-8'
        );

        $phoneName = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $phoneName
        );

        if ($phoneName === false) {
            $phoneName = $title;
        }

        $phoneName = preg_replace(
            '/[^a-z0-9]+/i',
            '',
            $phoneName
        ) ?? '';

        return trim($phoneName);
    };

    $buildBrandDirectory = static function (
        string $brand
    ): string {
        $brand = mb_strtolower(
            trim($brand),
            'UTF-8'
        );

        $brand = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $brand
        );

        if ($brand === false) {
            return '';
        }

        return preg_replace(
            '/[^a-z0-9]+/i',
            '',
            $brand
        ) ?? '';
    };

    $buildCardImage = static function (
        array $review
    ) use (
        $getReadyImage,
        $buildPhoneName,
        $buildBrandDirectory
    ): string {

        /*
         * Use the prepared image first.
         */
        $readyImage = $getReadyImage($review);

        if ($readyImage !== '') {
            return $readyImage;
        }

        $brand = trim(
            (string)($review['brand'] ?? '')
        );

        $title = trim(
            (string)($review['title'] ?? '')
        );

        $phoneName = $buildPhoneName(
            $brand,
            $title
        );

        $brandDirectory = $buildBrandDirectory(
            $brand
        );

        if (
            $phoneName !== '' &&
            $brandDirectory !== ''
        ) {
            return
                'https://techdealshub.online/' .
                $brandDirectory .
                '/img/' .
                $phoneName .
                '_hero_3_2.webp';
        }

        return '';
    };

    /* ============================================================
     * MASTER DATA
     * ============================================================ */

    $phonesByTitle = [];

    foreach ($reviews as $review) {

        if (!is_array($review)) {
            continue;
        }

        $title = trim(
            (string)($review['title'] ?? '')
        );

        if ($title === '') {
            continue;
        }

        $key = $normalizeTitle($title);

        if ($key === '') {
            continue;
        }

        /*
         * Last prepared record wins.
         */
        $phonesByTitle[$key] = $review;
    }

    if (empty($phonesByTitle)) {
        return false;
    }

    /* ============================================================
     * RANK ALL VALID PHONES
     * ============================================================ */

    $rankedPhones = [];

    foreach ($phonesByTitle as $key => $review) {

        $rating = $scoreToCardRating(
            $review['score'] ?? ''
        );

        /*
         * Existing minimum threshold.
         */
        if ($rating < 4.3) {
            continue;
        }

        $rankedPhones[] = [
            'key'    => $key,
            'review' => $review,
            'rating' => $rating
        ];
    }

    if (empty($rankedPhones)) {
        return false;
    }

    /*
     * Highest rating first.
     *
     * When ratings are equal, keep the original
     * data order instead of randomly changing order.
     */
    usort(
        $rankedPhones,
        static function (
            array $a,
            array $b
        ): int {
            $comparison =
                $b['rating'] <=> $a['rating'];

            if ($comparison !== 0) {
                return $comparison;
            }

            return 0;
        }
    );

    /* ============================================================
     * REVIEW CARD MATCHER
     * ============================================================ */

    $cardPattern =
        '~<a\b(?=[^>]*\bclass=(["\'])[^"\']*\breview-card\b[^"\']*\1)[^>]*>.*?</a>~is';

    $getCardTitle = static function (
        string $card
    ) use (
        $normalizeTitle
    ): string {

        if (
            !preg_match(
                '~<h3\b[^>]*>(.*?)</h3>~is',
                $card,
                $match
            )
        ) {
            return '';
        }

        return $normalizeTitle(
            $match[1]
        );
    };

    /* ============================================================
     * UPDATE EXISTING REVIEW CARD
     * ============================================================ */

    $updateReviewCard = static function (
        string $card,
        array $review
    ) use (
        $escape,
        $scoreToCardRating,
        $buildCardImage,
        $getReviewUrl
    ): string {

        $title = trim(
            (string)($review['title'] ?? '')
        );

        $description = trim(
            (string)($review['description'] ?? '')
        );

        $badge = trim(
            (string)($review['badge'] ?? '')
        );

        $url = $getReviewUrl($review);

        $rating = $scoreToCardRating(
            $review['score'] ?? ''
        );

        $ratingText = number_format(
            $rating,
            1,
            '.',
            ''
        );

        /* HREF */
        if ($url !== '') {

            $card = preg_replace_callback(
                '~(\bhref=)(["\']).*?\2~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $url
                ): string {
                    return
                        $m[1] .
                        $m[2] .
                        $escape($url) .
                        $m[2];
                },
                $card,
                1
            ) ?? $card;
        }

        /* IMAGE */
        $image = $buildCardImage($review);

        if ($image !== '') {

            $card = preg_replace_callback(
                '~(<img\b[^>]*\bsrc=)(["\'])(.*?)\2~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $image
                ): string {
                    return
                        $m[1] .
                        $m[2] .
                        $escape($image) .
                        $m[2];
                },
                $card,
                1
            ) ?? $card;
        }

        /* ALT */
        if ($title !== '') {

            $card = preg_replace_callback(
                '~(\balt=)(["\']).*?\2~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $title
                ): string {
                    return
                        $m[1] .
                        $m[2] .
                        $escape($title) .
                        $m[2];
                },
                $card,
                1
            ) ?? $card;
        }

        /* BADGE */
        $card = preg_replace_callback(
            '~(<span\b[^>]*class=(["\'])[^"\']*\breview-badge\b[^"\']*\2[^>]*>).*?(</span>)~is',
            static function (
                array $m
            ) use (
                $escape,
                $badge
            ): string {
                return
                    $m[1] .
                    $escape($badge) .
                    $m[3];
            },
            $card,
            1
        ) ?? $card;

        /* TITLE */
        if ($title !== '') {

            $card = preg_replace_callback(
                '~(<h3\b[^>]*>).*?(</h3>)~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $title
                ): string {
                    return
                        $m[1] .
                        $escape($title) .
                        $m[2];
                },
                $card,
                1
            ) ?? $card;
        }

        /* DESCRIPTION */
        $card = preg_replace_callback(
            '~(<p\b[^>]*class=(["\'])[^"\']*\breview-card-description\b[^"\']*\2[^>]*>).*?(</p>)~is',
            static function (
                array $m
            ) use (
                $escape,
                $description
            ): string {
                return
                    $m[1] .
                    $escape($description) .
                    $m[3];
            },
            $card,
            1
        ) ?? $card;

        /* RATING */
        $card = preg_replace_callback(
            '~(<div\b[^>]*class=(["\'])[^"\']*\bcard-rating\b[^"\']*\2[^>]*>.*?<strong\b[^>]*>).*?(</strong>)~is',
            static function (
                array $m
            ) use (
                $ratingText
            ): string {
                return
                    $m[1] .
                    $ratingText .
                    $m[3];
            },
            $card,
            1
        ) ?? $card;

        return $card;
    };

    /* ============================================================
     * UPDATE EXISTING REVIEW CARDS EVERYWHERE
     *
     * This only changes review-card elements.
     * Header/footer/menu links are untouched.
     * ============================================================ */

    $newHtml = preg_replace_callback(
        $cardPattern,
        static function (
            array $match
        ) use (
            $getCardTitle,
            $phonesByTitle,
            $updateReviewCard
        ): string {

            $card = $match[0];

            $key = $getCardTitle($card);

            if (
                $key === '' ||
                !isset($phonesByTitle[$key])
            ) {
                return $card;
            }

            return $updateReviewCard(
                $card,
                $phonesByTitle[$key]
            );
        },
        $html
    );

    if ($newHtml === null) {
        return false;
    }

    /* ============================================================
     * SECTION FINDER
     * ============================================================ */

    $getSection = static function (
        string $source,
        string $sectionId
    ): ?array {

        $pattern =
            '~<section\b(?=[^>]*\bid=(["\'])' .
            preg_quote($sectionId, '~') .
            '\1)[^>]*>.*?</section>~is';

        if (
            !preg_match(
                $pattern,
                $source,
                $match,
                PREG_OFFSET_CAPTURE
            )
        ) {
            return null;
        }

        return [
            'html'   => $match[0][0],
            'offset' => $match[0][1],
            'length' => strlen($match[0][0])
        ];
    };

    /* ============================================================
     * FIND COMPLETE FEATURED GRID
     * ============================================================ */

    $findFeaturedGrid = static function (
        string $section
    ): ?array {

        if (
            !preg_match(
                '~<div\b[^>]*class=(["\'])[^"\']*\bfeatured-grid\b[^"\']*\1[^>]*>~is',
                $section,
                $openMatch,
                PREG_OFFSET_CAPTURE
            )
        ) {
            return null;
        }

        $openTag = $openMatch[0][0];
        $start   = $openMatch[0][1];

        $contentStart =
            $start +
            strlen($openTag);

        $rest = substr(
            $section,
            $contentStart
        );

        if ($rest === false) {
            return null;
        }

        preg_match_all(
            '~</?div\b[^>]*>~is',
            $rest,
            $tags,
            PREG_OFFSET_CAPTURE
        );

        $depth = 1;

        foreach ($tags[0] as $tagMatch) {

            $tag    = $tagMatch[0];
            $offset = $tagMatch[1];

            if (
                preg_match(
                    '~^<div\b~i',
                    $tag
                )
            ) {
                $depth++;
                continue;
            }

            $depth--;

            if ($depth === 0) {

                return [
                    'open' =>
                        $openTag,

                    'inside' =>
                        substr(
                            $rest,
                            0,
                            $offset
                        ),

                    'close' =>
                        $tag,

                    'start' =>
                        $start,

                    'end' =>
                        $contentStart +
                        $offset +
                        strlen($tag)
                ];
            }
        }

        return null;
    };

    /* ============================================================
     * BUILD REVIEW CARD TEMPLATE
     * ============================================================ */

    $templates = [];

    preg_match_all(
        $cardPattern,
        $newHtml,
        $templateMatches
    );

    foreach (
        $templateMatches[0] ?? [] as $candidate
    ) {

        if (
            !preg_match(
                '~\bmain-review\b~i',
                $candidate
            )
        ) {
            $templates[] = $candidate;
        }
    }

    if (empty($templates)) {
        $templates =
            $templateMatches[0] ?? [];
    }

    if (empty($templates)) {
        return false;
    }

    $normalTemplate = $templates[0];

    /* ============================================================
     * BUILD NEW REVIEW CARD
     * ============================================================ */

    $buildReviewCard = static function (
        array $review
    ) use (
        $normalTemplate,
        $escape,
        $scoreToCardRating,
        $buildCardImage,
        $getReviewUrl
    ): string {

        $card = $normalTemplate;

        $card = preg_replace(
            '~\s+main-review\b~i',
            '',
            $card
        ) ?? $card;

        $title = trim(
            (string)($review['title'] ?? '')
        );

        $description = trim(
            (string)($review['description'] ?? '')
        );

        $badge = trim(
            (string)($review['badge'] ?? '')
        );

        $url = $getReviewUrl($review);

        $rating = $scoreToCardRating(
            $review['score'] ?? ''
        );

        $ratingText = number_format(
            $rating,
            1,
            '.',
            ''
        );

        /* HREF */
        if ($url !== '') {

            $card = preg_replace_callback(
                '~(\bhref=)(["\']).*?\2~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $url
                ): string {
                    return
                        $m[1] .
                        $m[2] .
                        $escape($url) .
                        $m[2];
                },
                $card,
                1
            ) ?? $card;
        }

        /* IMAGE */
        $image = $buildCardImage($review);

        if ($image !== '') {

            $card = preg_replace_callback(
                '~(<img\b[^>]*\bsrc=)(["\'])(.*?)\2~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $image
                ): string {
                    return
                        $m[1] .
                        $m[2] .
                        $escape($image) .
                        $m[2];
                },
                $card,
                1
            ) ?? $card;
        }

        /* ALT */
        if ($title !== '') {

            $card = preg_replace_callback(
                '~(\balt=)(["\']).*?\2~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $title
                ): string {
                    return
                        $m[1] .
                        $m[2] .
                        $escape($title) .
                        $m[2];
                },
                $card,
                1
            ) ?? $card;
        }

        /* BADGE */
        $card = preg_replace_callback(
            '~(<span\b[^>]*class=(["\'])[^"\']*\breview-badge\b[^"\']*\2[^>]*>).*?(</span>)~is',
            static function (
                array $m
            ) use (
                $escape,
                $badge
            ): string {
                return
                    $m[1] .
                    $escape($badge) .
                    $m[3];
            },
            $card,
            1
        ) ?? $card;

        /* TITLE */
        if ($title !== '') {

            $card = preg_replace_callback(
                '~(<h3\b[^>]*>).*?(</h3>)~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $title
                ): string {
                    return
                        $m[1] .
                        $escape($title) .
                        $m[2];
                },
                $card,
                1
            ) ?? $card;
        }

        /* DESCRIPTION */
        $card = preg_replace_callback(
            '~(<p\b[^>]*class=(["\'])[^"\']*\breview-card-description\b[^"\']*\2[^>]*>).*?(</p>)~is',
            static function (
                array $m
            ) use (
                $escape,
                $description
            ): string {
                return
                    $m[1] .
                    $escape($description) .
                    $m[3];
            },
            $card,
            1
        ) ?? $card;

        /* RATING */
        $card = preg_replace_callback(
            '~(<div\b[^>]*class=(["\'])[^"\']*\bcard-rating\b[^"\']*\2[^>]*>.*?<strong\b[^>]*>).*?(</strong>)~is',
            static function (
                array $m
            ) use (
                $ratingText
            ): string {
                return
                    $m[1] .
                    $ratingText .
                    $m[3];
            },
            $card,
            1
        ) ?? $card;

        return $card;
    };

    /* ============================================================
     * RECONCILE A REVIEW SECTION
     *
     * Unlike the old add-only function:
     *
     * 1. Keeps cards that belong to the section.
     * 2. Removes cards that no longer belong.
     * 3. Adds missing qualifying cards.
     *
     * Each section has its OWN membership.
     * Therefore the same phone can exist in multiple sections.
     * ============================================================ */

    $reconcileReviewSection = static function (
        string $source,
        string $sectionId,
        array $phones
    ) use (
        $getSection,
        $findFeaturedGrid,
        $cardPattern,
        $getCardTitle,
        $buildReviewCard
    ): string {

        $sectionInfo = $getSection(
            $source,
            $sectionId
        );

        if ($sectionInfo === null) {
            return $source;
        }

        $sectionHtml =
            $sectionInfo['html'];

        $grid = $findFeaturedGrid(
            $sectionHtml
        );

        if ($grid === null) {
            return $source;
        }

        /*
         * Desired membership for THIS section only.
         */
        $desired = [];

        foreach ($phones as $phone) {

            $key = trim(
                (string)($phone['key'] ?? '')
            );

            if (
                $key === '' ||
                !isset($phone['review']) ||
                !is_array($phone['review'])
            ) {
                continue;
            }

            $desired[$key] = $phone;
        }

        /*
         * Extract all existing review cards from this grid.
         */
        $existingCards = [];

        preg_match_all(
            $cardPattern,
            $grid['inside'],
            $matches,
            PREG_OFFSET_CAPTURE
        );

        foreach (
            $matches[0] ?? [] as $match
        ) {

            $card =
                $match[0];

            $offset =
                $match[1];

            $key =
                $getCardTitle($card);

            if ($key === '') {
                continue;
            }

            $existingCards[$key] = [
                'card'   => $card,
                'offset' => $offset
            ];
        }

        /*
         * Rebuild ONLY the card area.
         *
         * Existing qualifying cards remain structurally intact.
         * Wrong cards are omitted.
         */
        $newInside = $grid['inside'];

        /*
         * Remove all existing review cards from the grid.
         */
        $newInside =
            preg_replace(
                $cardPattern,
                '',
                $newInside
            ) ?? $newInside;

        /*
         * Add desired cards in the ranked order.
         *
         * Existing card = use its complete original HTML.
         * Missing card = build from template.
         */
        $cardsOutput = '';

        foreach ($phones as $phone) {

            $key = trim(
                (string)($phone['key'] ?? '')
            );

            if ($key === '') {
                continue;
            }

            /*
             * Existing card:
             * keep its complete HTML after the data update.
             */
            if (isset($existingCards[$key])) {

                $cardsOutput .=
                    "\n\n" .
                    $existingCards[$key]['card'] .
                    "\n\n";

                continue;
            }

            /*
             * Missing card:
             * add it.
             */
            if (
                isset($phone['review']) &&
                is_array($phone['review'])
            ) {

                $newCard =
                    $buildReviewCard(
                        $phone['review']
                    );

                if ($newCard !== '') {

                    $cardsOutput .=
                        "\n\n" .
                        $newCard .
                        "\n\n";
                }
            }
        }

        /*
         * Important:
         *
         * $newInside contains the original grid whitespace,
         * comments and non-card content.
         *
         * Cards are inserted at the beginning of the grid.
         * No nested HTML is reconstructed.
         */
        $newInside =
            $cardsOutput .
            ltrim($newInside);

        $newGrid =
            $grid['open'] .
            $newInside .
            $grid['close'];

        $newSection =
            substr(
                $sectionHtml,
                0,
                $grid['start']
            ) .
            $newGrid .
            substr(
                $sectionHtml,
                $grid['end']
            );

        return
            substr(
                $source,
                0,
                $sectionInfo['offset']
            ) .
            $newSection .
            substr(
                $source,
                $sectionInfo['offset'] +
                $sectionInfo['length']
            );
    };

    /* ============================================================
     * SECTION DISTRIBUTION
     * ============================================================ */

    /*
     * FEATURED REVIEWS
     *
     * ONLY #1, #2, #3.
     */
    $featuredPhones =
        array_slice(
            $rankedPhones,
            0,
            3
        );

    /*
     * MORE FEATURED PHONES
     *
     * #4 and below.
     */
    $moreFeaturedPhones =
        array_slice(
            $rankedPhones,
            3
        );

    /*
     * EDITOR'S PICKS
     *
     * Every phone >= 4.8,
     * regardless of ranking position.
     */
    $editorsPicksPhones =
        array_values(
            array_filter(
                $rankedPhones,
                static function (
                    array $phone
                ): bool {
                    return
                        (float)$phone['rating'] >= 4.8;
                }
            )
        );

    /*
     * FEATURED PHONE SCORES
     *
     * ONLY TOP 5.
     */
    $featuredScorePhones =
        array_slice(
            $rankedPhones,
            0,
            5
        );

    /* ============================================================
     * RECONCILE FEATURED REVIEWS
     * ============================================================ */

    $newHtml =
        $reconcileReviewSection(
            $newHtml,
            'featured-reviews',
            $featuredPhones
        );

    /* ============================================================
     * RECONCILE MORE FEATURED
     * ============================================================ */

    $newHtml =
        $reconcileReviewSection(
            $newHtml,
            'more-featured',
            $moreFeaturedPhones
        );

    /* ============================================================
     * RECONCILE EDITOR'S PICKS
     * ============================================================ */

    $newHtml =
        $reconcileReviewSection(
            $newHtml,
            'editors-picks',
            $editorsPicksPhones
        );

    /* ============================================================
     * FEATURED PHONE SCORES
     * ============================================================ */

    $scoreItemPattern =
        '~<a\b(?=[^>]*\bclass=(["\'])[^"\']*\bscore-item\b[^"\']*\1)[^>]*>.*?</a>~is';

    $getScoreTitle = static function (
        string $item
    ) use (
        $normalizeTitle
    ): string {

        if (
            !preg_match(
                '~<h3\b[^>]*>(.*?)</h3>~is',
                $item,
                $match
            )
        ) {
            return '';
        }

        return $normalizeTitle(
            $match[1]
        );
    };

    $updateScoreItem = static function (
        string $item,
        array $review
    ) use (
        $escape,
        $scoreToCardRating,
        $buildCardImage,
        $getReviewUrl
    ): string {

        $title = trim(
            (string)($review['title'] ?? '')
        );

        $brand = trim(
            (string)($review['brand'] ?? '')
        );

        $url = $getReviewUrl($review);

        $image = $buildCardImage($review);

        $rating = $scoreToCardRating(
            $review['score'] ?? ''
        );

        $ratingText = number_format(
            $rating,
            1,
            '.',
            ''
        );

        /* HREF */
        if ($url !== '') {

            $item = preg_replace_callback(
                '~(\bhref=)(["\']).*?\2~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $url
                ): string {
                    return
                        $m[1] .
                        $m[2] .
                        $escape($url) .
                        $m[2];
                },
                $item,
                1
            ) ?? $item;
        }

        /* IMAGE */
        if ($image !== '') {

            $item = preg_replace_callback(
                '~(<img\b[^>]*\bsrc=)(["\'])(.*?)\2~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $image
                ): string {
                    return
                        $m[1] .
                        $m[2] .
                        $escape($image) .
                        $m[2];
                },
                $item,
                1
            ) ?? $item;
        }

        /* ALT */
        if ($title !== '') {

            $item = preg_replace_callback(
                '~(\balt=)(["\']).*?\2~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $title
                ): string {
                    return
                        $m[1] .
                        $m[2] .
                        $escape($title) .
                        $m[2];
                },
                $item,
                1
            ) ?? $item;
        }

        /* SCORE TITLE */
        if ($title !== '') {

            $item = preg_replace_callback(
                '~(<div\b[^>]*class=(["\'])[^"\']*\bscore-phone\b[^"\']*\2[^>]*>.*?<h3\b[^>]*>).*?(</h3>)~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $title
                ): string {
                    return
                        $m[1] .
                        $escape($title) .
                        $m[3];
                },
                $item,
                1
            ) ?? $item;
        }

        /* SCORE BRAND */
        if ($brand !== '') {

            $item = preg_replace_callback(
                '~(<div\b[^>]*class=(["\'])[^"\']*\bscore-phone\b[^"\']*\2[^>]*>.*?<p\b[^>]*>).*?(</p>)~is',
                static function (
                    array $m
                ) use (
                    $escape,
                    $brand
                ): string {
                    return
                        $m[1] .
                        $escape($brand) .
                        $m[3];
                },
                $item,
                1
            ) ?? $item;
        }

        /* SCORE NUMBER */
        $item = preg_replace_callback(
            '~(<div\b[^>]*class=(["\'])[^"\']*\bscore-number\b[^"\']*\2[^>]*>).*?(</div>)~is',
            static function (
                array $m
            ) use (
                $ratingText
            ): string {
                return
                    $m[1] .
                    $ratingText .
                    $m[3];
            },
            $item,
            1
        ) ?? $item;

        return $item;
    };

    /* ============================================================
     * RECONCILE SCORE ITEMS
     *
     * Existing wrong items are removed.
     * Missing TOP 5 items are added.
     * ============================================================ */

    $scoreSectionInfo =
        $getSection(
            $newHtml,
            'featured-scores'
        );

    if ($scoreSectionInfo !== null) {

        $scoreSection =
            $scoreSectionInfo['html'];

        $scoreListPattern =
            '~<div\b[^>]*class=(["\'])[^"\']*\bscore-list\b[^"\']*\1[^>]*>~is';

        if (
            preg_match(
                $scoreListPattern,
                $scoreSection,
                $scoreListOpen,
                PREG_OFFSET_CAPTURE
            )
        ) {

            $scoreOpen =
                $scoreListOpen[0][0];

            $scoreStart =
                $scoreListOpen[0][1];

            $scoreContentStart =
                $scoreStart +
                strlen($scoreOpen);

            $scoreRest =
                substr(
                    $scoreSection,
                    $scoreContentStart
                );

            preg_match_all(
                '~</?div\b[^>]*>~is',
                $scoreRest,
                $scoreDivs,
                PREG_OFFSET_CAPTURE
            );

            $depth = 1;
            $scoreListEnd = null;

            foreach (
                $scoreDivs[0] as $tagMatch
            ) {

                $tag =
                    $tagMatch[0];

                $offset =
                    $tagMatch[1];

                if (
                    preg_match(
                        '~^<div\b~i',
                        $tag
                    )
                ) {
                    $depth++;
                } else {
                    $depth--;

                    if ($depth === 0) {

                        $scoreListEnd =
                            $scoreContentStart +
                            $offset +
                            strlen($tag);

                        break;
                    }
                }
            }

            if ($scoreListEnd !== null) {

                $scoreInside =
                    substr(
                        $scoreSection,
                        $scoreContentStart,
                        $scoreListEnd -
                        $scoreContentStart -
                        strlen('</div>')
                    );

                if ($scoreInside === false) {
                    $scoreInside = '';
                }

                /*
                 * Existing score items.
                 */
                preg_match_all(
                    $scoreItemPattern,
                    $scoreInside,
                    $scoreMatches
                );

                $existingScoreItems = [];

                foreach (
                    $scoreMatches[0] ?? [] as $scoreItem
                ) {

                    $key =
                        $getScoreTitle(
                            $scoreItem
                        );

                    if ($key === '') {
                        continue;
                    }

                    /*
                     * Update this item's current data first.
                     */
                    if (
                        isset($phonesByTitle[$key])
                    ) {
                        $scoreItem =
                            $updateScoreItem(
                                $scoreItem,
                                $phonesByTitle[$key]
                            );
                    }

                    $existingScoreItems[$key] =
                        $scoreItem;
                }

                /*
                 * Structural template.
                 */
                $scoreTemplate = '';

                foreach (
                    $existingScoreItems as $scoreItem
                ) {
                    $scoreTemplate =
                        $scoreItem;
                    break;
                }

                /*
                 * If no score item exists,
                 * we cannot safely invent its HTML structure.
                 */
                if ($scoreTemplate !== '') {

                    $newScoreInside = '';

                    /*
                     * EXACTLY TOP 5.
                     *
                     * Existing desired item:
                     * keep it.
                     *
                     * Missing desired item:
                     * create it.
                     */
                    foreach (
                        $featuredScorePhones as $phone
                    ) {

                        $key =
                            trim(
                                (string)($phone['key'] ?? '')
                            );

                        if ($key === '') {
                            continue;
                        }

                        if (
                            isset(
                                $existingScoreItems[$key]
                            )
                        ) {

                            $newScoreInside .=
                                "\n\n" .
                                $existingScoreItems[$key] .
                                "\n\n";

                            continue;
                        }

                        if (
                            isset($phone['review']) &&
                            is_array($phone['review'])
                        ) {

                            $newScoreItem =
                                $updateScoreItem(
                                    $scoreTemplate,
                                    $phone['review']
                                );

                            $newScoreInside .=
                                "\n\n" .
                                $newScoreItem .
                                "\n\n";
                        }
                    }

                    /*
                     * Build the score list using ONLY
                     * the TOP 5 desired items.
                     */
                    $newScoreList =
                        $scoreOpen .
                        $newScoreInside .
                        '</div>';

                    $newScoreSection =
                        substr(
                            $scoreSection,
                            0,
                            $scoreStart
                        ) .
                        $newScoreList .
                        substr(
                            $scoreSection,
                            $scoreListEnd
                        );

                    $newHtml =
                        substr(
                            $newHtml,
                            0,
                            $scoreSectionInfo['offset']
                        ) .
                        $newScoreSection .
                        substr(
                            $newHtml,
                            $scoreSectionInfo['offset'] +
                            $scoreSectionInfo['length']
                        );
                }
            }
        }
    }

    /* ============================================================
     * HERO
     *
     * Always follows #1.
     * ============================================================ */

    $heroSectionPattern =
        '~<section\b[^>]*class=(["\'])[^"\']*\bhero\b[^"\']*\1[^>]*>.*?</section>~is';

    if (
        preg_match(
            $heroSectionPattern,
            $newHtml,
            $heroMatch,
            PREG_OFFSET_CAPTURE
        )
    ) {

        $heroHtml =
            $heroMatch[0][0];

        $topPhone =
            $rankedPhones[0] ?? null;

        if (
            is_array($topPhone) &&
            isset($topPhone['review']) &&
            is_array($topPhone['review'])
        ) {

            $review =
                $topPhone['review'];

            $heroTitle =
                trim(
                    (string)($review['title'] ?? '')
                );

            $heroDescription =
                trim(
                    (string)($review['description'] ?? '')
                );

            $heroUrl =
                $getReviewUrl($review);

            $heroImage =
                $buildCardImage($review);

            $heroRating =
                $scoreToCardRating(
                    $review['score'] ?? ''
                );

            $heroRatingText =
                number_format(
                    $heroRating,
                    1,
                    '.',
                    ''
                );

            $heroDisplayTitle =
                preg_replace(
                    '/\s+Reviews?\s*$/iu',
                    '',
                    $heroTitle
                ) ?? $heroTitle;

            /* HERO TITLE */
            if ($heroDisplayTitle !== '') {

                $heroHtml =
                    preg_replace_callback(
                        '~(<div\b[^>]*class=(["\'])[^"\']*\bhero-content\b[^"\']*\2[^>]*>.*?<h1\b[^>]*>).*?(</h1>)~is',
                        static function (
                            array $m
                        ) use (
                            $escape,
                            $heroDisplayTitle
                        ): string {
                            return
                                $m[1] .
                                $escape(
                                    $heroDisplayTitle
                                ) .
                                $m[3];
                        },
                        $heroHtml,
                        1
                    ) ?? $heroHtml;
            }

            /* HERO DESCRIPTION */
            if ($heroDescription !== '') {

                $heroHtml =
                    preg_replace_callback(
                        '~(<div\b[^>]*class=(["\'])[^"\']*\bhero-content\b[^"\']*\2[^>]*>.*?<p\b[^>]*>).*?(</p>)~is',
                        static function (
                            array $m
                        ) use (
                            $escape,
                            $heroDescription
                        ): string {
                            return
                                $m[1] .
                                $escape(
                                    $heroDescription
                                ) .
                                $m[3];
                        },
                        $heroHtml,
                        1
                    ) ?? $heroHtml;
            }

            /* HERO IMAGE */
            if ($heroImage !== '') {

                $heroHtml =
                    preg_replace_callback(
                        '~(<img\b[^>]*\bsrc=)(["\'])(.*?)\2~is',
                        static function (
                            array $m
                        ) use (
                            $escape,
                            $heroImage
                        ): string {
                            return
                                $m[1] .
                                $m[2] .
                                $escape(
                                    $heroImage
                                ) .
                                $m[2];
                        },
                        $heroHtml,
                        1
                    ) ?? $heroHtml;
            }

            /* HERO IMAGE ALT */
            if ($heroDisplayTitle !== '') {

                $heroHtml =
                    preg_replace_callback(
                        '~(\balt=)(["\']).*?\2~is',
                        static function (
                            array $m
                        ) use (
                            $escape,
                            $heroDisplayTitle
                        ): string {
                            return
                                $m[1] .
                                $m[2] .
                                $escape(
                                    $heroDisplayTitle
                                ) .
                                $m[2];
                        },
                        $heroHtml,
                        1
                    ) ?? $heroHtml;
            }

            /* HERO RATING */
            $heroHtml =
                preg_replace_callback(
                    '~(<div\b[^>]*class=(["\'])[^"\']*\brating\b[^"\']*\2[^>]*>.*?<strong\b[^>]*>).*?(</strong>)~is',
                    static function (
                        array $m
                    ) use (
                        $heroRatingText
                    ): string {
                        return
                            $m[1] .
                            $heroRatingText .
                            $m[3];
                    },
                    $heroHtml,
                    1
                ) ?? $heroHtml;

            /* HERO LINK */
            if ($heroUrl !== '') {

                $heroHtml =
                    preg_replace_callback(
                        '~(\bhref=)(["\']).*?\2~is',
                        static function (
                            array $m
                        ) use (
                            $escape,
                            $heroUrl
                        ): string {
                            return
                                $m[1] .
                                $m[2] .
                                $escape(
                                    $heroUrl
                                ) .
                                $m[2];
                        },
                        $heroHtml,
                        1
                    ) ?? $heroHtml;
            }

            /*
             * Replace ONLY the hero section.
             */
            $newHtml =
                substr(
                    $newHtml,
                    0,
                    $heroMatch[0][1]
                ) .
                $heroHtml .
                substr(
                    $newHtml,
                    $heroMatch[0][1] +
                    strlen($heroMatch[0][0])
                );
        }
    }

    /* ============================================================
     * FINAL WRITE
     * ============================================================ */

    if ($newHtml === $html) {
        return true;
    }

    return file_put_contents(
        $featuredPath,
        $newHtml
    ) !== false;
}
/*
 * ============================================================
 * POST
 * ============================================================
 */

$success = '';
$error = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $data = [];

    /*
     * READ ALL FORM VALUES
     */

    foreach (
        $allPlaceholders
        as $index => $field
    ) {

        $fieldNumber =
            $index + 1;

        $data[$fieldNumber] =
            trim(
                (string) (
                    $_POST[
                        'field_' .
                        $fieldNumber
                    ] ?? ''
                )
            );
    }

    /*
     * GET MAIN BRAND
     */

    $brand =
        getFieldValue(
            $allPlaceholders,
            $data,
            'BRAND'
        );

    /*
     * GET MAIN TITLE
     */

    $firstTitleValue =
        getFieldValue(
            $allPlaceholders,
            $data,
            'TITLE'
        );

    /*
     * REQUIRED FIELDS
     */

    if ($brand === '') {

        $error =
            'BRAND is required. Review HTML was not created.';

    } elseif ($firstTitleValue === '') {

        $error =
            'TITLE is required. Review HTML was not created.';
    }

    /*
     * IMAGE FIELDS
     */

    $imageFields = [
        'HERO_IMAGE',
        'DESIGN_IMAGE',
        'DISPLAY_IMAGE',
        'CAMERA_SAMPLE_1',
        'CAMERA_SAMPLE_2',
        'CAMERA_SAMPLE_3',
        'ALT1_IMAGE',
        'ALT2_IMAGE',
        'ALT3_IMAGE',
    ];

    $generatedImages = [];

    if ($error === '') {

        foreach (
            $allPlaceholders
            as $index => $field
        ) {

            $upperField =
                strtoupper(
                    trim($field)
                );

            if (
                !in_array(
                    $upperField,
                    $imageFields,
                    true
                )
            ) {
                continue;
            }

            $fieldNumber =
                $index + 1;

            if (
                $upperField === 'ALT1_IMAGE' ||
                $upperField === 'ALT2_IMAGE' ||
                $upperField === 'ALT3_IMAGE'
            ) {

                $imageBrand =
                    getImageBrand(
                        $upperField,
                        $allPlaceholders,
                        $data,
                        $brand
                    );

                $imagePhoneTitle =
                    getImagePhoneTitle(
                        $upperField,
                        $allPlaceholders,
                        $data,
                        $firstTitleValue
                    );

                $generatedPath =
                    findActualImagePath(
                        '',
                        $imageBrand,
                        $imagePhoneTitle,
                        $upperField
                    );

            } else {

                $generatedPath =
                    findActualImagePath(
                        '',
                        $brand,
                        $firstTitleValue,
                        $upperField
                    );
            }

            $data[$fieldNumber] =
                $generatedPath;

            $generatedImages[
                $upperField
            ] =
                $generatedPath;
        }
    }

    /*
     * AUTOMATIC IMAGE ALT
     */

    foreach (
        $allPlaceholders
        as $index => $field
    ) {

        $fieldNumber =
            $index + 1;

        if (
            strcasecmp(
                trim($field),
                'HERO_IMAGE_ALT'
            ) === 0
        ) {

            if (
                trim(
                    (string) (
                        $data[
                            $fieldNumber
                        ] ?? ''
                    )
                ) === ''
            ) {

                $data[$fieldNumber] =
                    $firstTitleValue;
            }
        }

        if (
            strcasecmp(
                trim($field),
                'DESIGN_IMAGE_ALT'
            ) === 0
        ) {

            if (
                trim(
                    (string) (
                        $data[
                            $fieldNumber
                        ] ?? ''
                    )
                ) === ''
            ) {

                $data[$fieldNumber] =
                    $firstTitleValue;
            }
        }
    }

    /*
     * GENERATE *_HTML FIELDS
     */

    foreach (
        $allPlaceholders
        as $index => $field
    ) {

        if (
            substr(
                $field,
                -5
            ) !== '_HTML'
        ) {
            continue;
        }

        $baseField =
            substr(
                $field,
                0,
                -5
            );

        $baseIndex = null;

        foreach (
            $allPlaceholders
            as $x => $possibleField
        ) {

            if (
                strcasecmp(
                    $possibleField,
                    $baseField
                ) === 0
            ) {

                $baseIndex =
                    $x;

                break;
            }
        }

        if (
            $baseIndex !== null
        ) {

            $baseValue =
                (string) (
                    $data[
                        $baseIndex + 1
                    ] ?? ''
                );

            $data[
                $index + 1
            ] =
                generateHtmlField(
                    $baseField,
                    $baseValue
                );
        }
    }

    /*
     * REPLACE ONLY PLACEHOLDERS
     */

    $output =
        $template;

    foreach (
        $allPlaceholders
        as $index => $field
    ) {

        $fieldNumber =
            $index + 1;

        $value =
            (string) (
                $data[
                    $fieldNumber
                ] ?? ''
            );

        $pattern =
            '/\{\{\s*' .
            preg_quote(
                $field,
                '/'
            ) .
            '\s*\}\}/';

        $output =
            preg_replace_callback(
                $pattern,
                static function ()
                    use ($value): string {

                    return $value;
                },
                $output
            ) ?? $output;
    }

    /*
     * PHONE-CARD ALTERNATIVE IMAGES
     */

    $output =
        fixPhoneCardAlternativeImages(
            $output
        );

    /*
     * IMAGE LOG
     */

    foreach (
        $generatedImages
        as $field => $path
    ) {

        error_log(
            '[TDH IMAGE] ' .
            $field .
            ': ' .
            $path
        );
    }

    /*
     * TITLE
     */

    $title =
        $firstTitleValue;

    /*
     * GENERATE SLUG
     */

    $slug =
        strtolower(
            trim($title)
        );

    $slug =
        preg_replace(
            '/[^a-z0-9]+/i',
            '-',
            $slug
        );

    $slug =
        trim(
            (string) $slug,
            '-'
        );

    if ($slug === '') {

        $slug =
            'phone-review-' .
            date(
                'Ymd-His'
            );
    }

    /*
     * REMOVE TRAILING -review
     */

    $slug =
        preg_replace(
            '/-review$/i',
            '',
            $slug
        ) ?? $slug;

    /*
     * REVIEWS DIRECTORY
     */

    $reviewsDirectory =
        __DIR__ .
        '/reviews';

    if (
        $error === '' &&
        !is_dir(
            $reviewsDirectory
        )
    ) {

        if (
            !mkdir(
                $reviewsDirectory,
                0755,
                true
            )
        ) {

            $error =
                'Could not create the reviews directory.';
        }
    }

    /*
     * OUTPUT FILE
     */

    $outputName =
        $slug .
        '-reviews.html';

    $outputPath =
        $reviewsDirectory .
        '/' .
        $outputName;

    /*
     * ============================================================
     * WRITE REVIEW HTML
     * ============================================================
     */

    if (
        empty($error) &&
        file_put_contents(
            $outputPath,
            $output
        ) === false
    ) {

        $error =
            'Could not create the review HTML file.';

    } elseif (
        empty($error)
    ) {

        /*
         * ========================================================
         * UPDATE reviews-data.php
         * ========================================================
         *
         * THIS WAS THE MISSING PART.
         */

        $reviewImage =
            $generatedImages[
                'HERO_IMAGE'
            ] ?? '';

        $reviewScore =
    getFirstReviewFieldValue(
        $allPlaceholders,
        $data,
        [
            'OVERALL_SCORE',
            'FINAL_SCORE',
            'RATING',
            'SCORE'
        ]
    );
        $reviewCameraScore =
    getFirstReviewFieldValue(
        $allPlaceholders,
        $data,
        [
            'SCORE_CAMERA'
        ]
    );

$reviewGamingScore =
    getFirstReviewFieldValue(
        $allPlaceholders,
        $data,
        [
            'GAMING_SCORE'
        ]
    );
        $reviewDescription =
            getFirstReviewFieldValue(
                $allPlaceholders,
                $data,
                [
                    'DESCRIPTION',
                    'REVIEW_DESCRIPTION',
                    'SUMMARY',
                    'REVIEW_SUMMARY'
                ]
            );

        $reviewCategory =
            getFirstReviewFieldValue(
                $allPlaceholders,
                $data,
                [
                    'CATEGORY',
                    'PRODUCT_CATEGORY'
                ]
            );

        /*
         * IMPORTANT:
         * This is the exact URL stored in reviews-data.php.
         */

        $reviewUrl =
            'reviews/' .
            $outputName;
      $categoryUpdateResult = updateCategoryPhoneCard(
    $data,
    $allPlaceholders,
    $reviewUrl
);
      echo '<pre>';
echo htmlspecialchars($categoryUpdateResult, ENT_QUOTES, 'UTF-8');
echo '</pre>';

        /*
         * ACTUALLY CALL THE FUNCTION
         */
$finalScore =
    trim((string)getFieldValue(
        $allPlaceholders,
        $data,
        'FINAL_SCORE'
    ));

   $reviewsDataUpdated =
    updateReviewsData(
        $brand,
        $title,
        $finalScore,
        $reviewCameraScore,
        $reviewGamingScore,
        $reviewDescription,
        $reviewCategory,
        $reviewImage,
        $reviewUrl,
        $data,
        $allPlaceholders
    );

if (!$reviewsDataUpdated) {

    $error =
        'Review HTML was created, but reviews-data.php could not be updated.';

    $success = '';

} else {

    /*
     * ========================================================
     * UPDATE reviews.html
     * ========================================================
     */

    $reviewsDataEmbedded =
        embedReviewsDataIntoHtml();

    if (!$reviewsDataEmbedded) {

        $error =
            'Review data was saved, but reviews.html could not be updated.';

        $success = '';

    } else {

        /*
         * ========================================================
         * UPDATE FEATURED REVIEWS
         * ========================================================
         */

        $featuredReviewsUpdated =
            updateFeaturedReviewsHtml();

        if (!$featuredReviewsUpdated) {

            $error =
                'Review data was saved and reviews.html was updated, but featured-reviews.html could not be updated.';

            $success = '';

                } else {

            $success =
                'Review created successfully: ' .
                'reviews/' .
                $outputName .
                ' | reviews-data.php updated' .
                ' | reviews.html updated' .
                ' | featured-reviews.html updated.';
        }
    }
}
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Add Phone Review</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 24px;
    background: #f4f6f8;
    color: #1f2937;
    font-family: Arial, sans-serif;
}

.wrap {
    max-width: 1100px;
    margin: 0 auto;
}

h1 {
    margin: 0 0 8px;
}

.meta {
    margin-bottom: 20px;
    color: #64748b;
}

.notice {
    padding: 14px 16px;
    border-radius: 10px;
    margin-bottom: 18px;
    background: #e8f5e9;
    color: #166534;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

form {
    background: #fff;
    padding: 22px;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(0,0,0,.06);
}

.field {
    margin-bottom: 16px;
}

label {
    display: block;
    font-weight: 700;
    margin-bottom: 7px;
    word-break: break-word;
}

input,
textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 11px 12px;
    font: inherit;
    background: #fff;
}

textarea {
    min-height: 100px;
    resize: vertical;
}

.hint {
    font-size: 12px;
    color: #64748b;
    margin-top: 5px;
}

button {
    border: 0;
    border-radius: 9px;
    padding: 13px 20px;
    font-weight: 700;
    cursor: pointer;
    background: #111827;
    color: white;
}

.section {
    margin: 28px 0 10px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e5e7eb;
}

.field-number {
    display: inline-block;
    margin-right: 6px;
    padding: 3px 7px;
    border-radius: 6px;
    background: #e5e7eb;
    color: #374151;
    font-size: 11px;
    font-weight: 700;
}

</style>

</head>

<body>

<!-- =========================================================
     QUICK FILL
     ========================================================= -->

<div
    id="quickFillBox"
    style="
        background:#fff;
        padding:22px;
        border-radius:14px;
        box-shadow:0 4px 18px rgba(0,0,0,.06);
        margin-bottom:18px;
    "
>

    <h2 style="margin:0 0 8px;">
        Quick Fill
    </h2>

    <div
        style="
            color:#64748b;
            font-size:13px;
            margin-bottom:10px;
        "
    >

        Write one field per line using:
        <strong>FIELD: VALUE</strong>

    </div>

    <textarea
        id="quickFillInput"
        style="
            width:100%;
            min-height:180px;
            border:1px solid #cbd5e1;
            border-radius:8px;
            padding:11px 12px;
            font:inherit;
            resize:vertical;
        "
        placeholder="TITLE: Samsung Galaxy S26 Ultra
BRAND: Samsung
RAM: 12
STORAGE: 256
BATTERY: 5000
RATING: 9.5"
    ></textarea>

    <div
        style="
            display:flex;
            gap:10px;
            margin-top:10px;
        "
    >

        <button
            type="button"
            id="quickFillButton"
            style="
                background:#2563eb;
                flex:1;
            "
        >
            Fill Fields
        </button>

        <button
            type="button"
            id="quickClearButton"
            style="
                background:#64748b;
                flex:1;
            "
        >
            Clear
        </button>

    </div>

    <div
        id="quickFillStatus"
        style="
            margin-top:10px;
            font-size:13px;
            color:#166534;
        "
    ></div>

</div>

<div class="wrap">

    <h1>
        Add Phone Review
    </h1>

    <div class="meta">

        Template fields:
        <?= count($allPlaceholders) ?>

        |
        Unique fields only.
        Repeated placeholders use the same field value.

    </div>

    <?php if (!empty($success)): ?>

        <div class="notice">
            <?= e($success) ?>
        </div>

    <?php endif; ?>

    <?php if (!empty($error)): ?>

        <div class="notice error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <form method="post">

        <?php

        $lastPrefix = '';

        foreach (
            $allPlaceholders
            as $index => $field
        ):

            $fieldNumber =
                $index + 1;

            $prefix =
                strtoupper(
                    (string) strtok(
                        $field,
                        '_'
                    )
                );

            if (
                $prefix !==
                $lastPrefix
            ):

                $lastPrefix =
                    $prefix;

        ?>

            <h2 class="section">
                <?= e($prefix) ?>
            </h2>

        <?php endif; ?>

        <div
            class="field"
            data-field="<?= e($field) ?>"
        >

            <label
                for="<?= e(
                    'field_' .
                    $fieldNumber
                ) ?>"
            >

                <span class="field-number">
                    #<?= $fieldNumber ?>
                </span>

                <?= e($field) ?>

            </label>

            <?php if (isTextarea($field)): ?>

                <textarea
                    id="<?= e(
                        'field_' .
                        $fieldNumber
                    ) ?>"
                    name="<?= e(
                        'field_' .
                        $fieldNumber
                    ) ?>"
                    placeholder="<?= e($field) ?>"
                ><?= e(
                    (string) (
                        $_POST[
                            'field_' .
                            $fieldNumber
                        ] ?? ''
                    )
                ) ?></textarea>

            <?php else: ?>

                <input
                    id="<?= e(
                        'field_' .
                        $fieldNumber
                    ) ?>"
                    name="<?= e(
                        'field_' .
                        $fieldNumber
                    ) ?>"
                    type="text"
                    value="<?= e(
                        (string) (
                            $_POST[
                                'field_' .
                                $fieldNumber
                            ] ?? ''
                        )
                    ) ?>"
                    placeholder="<?= e($field) ?>"
                >

            <?php endif; ?>

            <?php if (
                in_array(
                    $field,
                    $htmlFields,
                    true
                )
            ): ?>

                <div class="hint">

                    For multiple items, separate them with |
                    or put each item on a new line.

                </div>

            <?php endif; ?>

        </div>

        <?php endforeach; ?>

        <button type="submit">
            Generate Review HTML
        </button>

    </form>

</div>

<script>

(function () {

    const input =
        document.getElementById(
            'quickFillInput'
        );

    const fillButton =
        document.getElementById(
            'quickFillButton'
        );

    const clearButton =
        document.getElementById(
            'quickClearButton'
        );

    const status =
        document.getElementById(
            'quickFillStatus'
        );

    /*
     * NORMALIZE FIELD NAME
     */

    function normalizeField(name) {

        return String(name)
            .trim()
            .toUpperCase()
            .replace(/\s+/g, '_');

    }

    /*
     * QUICK FILL
     */

    fillButton.addEventListener(
        'click',
        function () {

            const text =
                input.value;

            if (!text.trim()) {

                status.textContent =
                    'Nothing to fill.';

                status.style.color =
                    '#991b1b';

                return;
            }

            const fields = {};

            document
                .querySelectorAll(
                    '.field[data-field]'
                )
                .forEach(
                    function (container) {

                        const fieldName =
                            normalizeField(
                                container.getAttribute(
                                    'data-field'
                                )
                            );

                        const element =
                            container.querySelector(
                                'input[name^="field_"], textarea[name^="field_"]'
                            );

                        if (!element) {
                            return;
                        }

                        if (
                            !fields[fieldName]
                        ) {

                            fields[fieldName] =
                                element;
                        }

                    }
                );

            const lines =
    text.split(/\r?\n/);

const entries = [];

let currentField = null;

/*
 * Only real template fields can start a new field.
 * This prevents HTML, URLs, CSS, etc. containing ":"
 * from being interpreted as field names.
 */

lines.forEach(function (line) {

    const match =
        line.match(
            /^\s*([A-Za-z0-9_]+)\s*:(.*)$/
        );

    if (
        match &&
        Object.prototype.hasOwnProperty.call(
            fields,
            normalizeField(match[1])
        )
    ) {

        currentField =
            normalizeField(match[1]);

        entries.push({

            field:
                currentField,

            value:
                match[2].trim()

        });

        return;
    }

    /*
     * Continuation line.
     * Keep it as part of the current field.
     */

    if (
        currentField !== null
    ) {

        const last =
            entries[
                entries.length - 1
            ];

        if (last) {

            /*
             * Preserve multiline HTML/text.
             */

            if (last.value !== '') {

                last.value +=
                    '\n' +
                    line;

            } else {

                last.value =
                    line;
            }

        }
    }

});

            let filled = 0;

            const notFound = [];

            entries.forEach(
                function (entry) {

                    const fieldName =
                        entry.field;

                    const value =
                        entry.value;

                    if (
                        Object.prototype.hasOwnProperty.call(
                            fields,
                            fieldName
                        )
                    ) {

                        const element =
                            fields[fieldName];

                        element.value =
                            value;

                        element.dispatchEvent(
                            new Event(
                                'input',
                                {
                                    bubbles: true
                                }
                            )
                        );

                        element.dispatchEvent(
                            new Event(
                                'change',
                                {
                                    bubbles: true
                                }
                            )
                        );

                        filled++;

                    }

                    else {

                        if (
                            notFound.indexOf(
                                fieldName
                            ) === -1
                        ) {

                            notFound.push(
                                fieldName
                            );
                        }

                    }

                }
            );

            status.style.color =
                '#166534';

            if (
                notFound.length > 0
            ) {

                status.textContent =
                    'Filled ' +
                    filled +
                    ' field(s). Not found: ' +
                    notFound.join(
                        ', '
                    );

            }

            else {

                status.textContent =
                    'Filled ' +
                    filled +
                    ' field(s).';
            }

        }
    );

    /*
     * CLEAR
     */

    clearButton.addEventListener(
        'click',
        function () {

            input.value =
                '';

            status.textContent =
                '';

            input.focus();

        }
    );

})();

</script>

</body>
</html>
