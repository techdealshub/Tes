<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/*
 * ============================================================
 * CONFIG
 * ============================================================
 */

$templatePath =
    __DIR__ .
    '/iphone-style-complete-placeholders-100-valid-V7-template.html';

if (!is_file($templatePath)) {
    die(
        'Error: template file not found: ' .
        basename($templatePath)
    );
}

$template = file_get_contents($templatePath);

if ($template === false) {
    die('Error: unable to read template file.');
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

$allOccurrences = $matches[1] ?? [];

$allPlaceholders = array_values(
    array_unique($allOccurrences)
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
 *
 * مثال:
 *
 * iPhone 17 Pro Max
 * -> iphone17promax
 *
 * Samsung Galaxy S26 Ultra
 * مع Samsung كـ BRAND
 * -> galaxys26ultra
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

    /*
     * إزالة اسم الشركة من بداية الاسم
     */
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

    /*
     * lowercase
     */
    $title =
        strtolower(
            trim($title)
        );

    /*
     * إزالة المسافات والرموز
     */
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
 * BUILD IMAGE URL
 *
 * هذه هي الدالة الوحيدة التي تبني مسارات الصور.
 *
 * لا تقوم بتغيير:
 * - href
 * - nav
 * - menu
 * - footer
 * - ALT1_LINK
 * - ALT2_LINK
 * - ALT3_LINK
 *
 * ============================================================
 */

function buildImageUrl(
    string $field,
    string $mainBrand,
    string $mainTitle,
    array $allPlaceholders,
    array $data
): string {

    $field =
        strtoupper(
            trim($field)
        );

    /*
     * --------------------------------------------------------
     * MAIN PHONE
     * --------------------------------------------------------
     */

    $brand =
        trim($mainBrand);

    $phoneTitle =
        trim($mainTitle);

    /*
     * --------------------------------------------------------
     * ALTERNATIVE PHONE 1
     * --------------------------------------------------------
     */

    if (
        $field === 'ALT1_IMAGE'
    ) {

        $altBrand =
            getFieldValue(
                $allPlaceholders,
                $data,
                'ALT1_BRAND'
            );

        $altName =
            getFieldValue(
                $allPlaceholders,
                $data,
                'ALT1_NAME'
            );

        if ($altBrand !== '') {
            $brand = $altBrand;
        }

        if ($altName !== '') {
            $phoneTitle = $altName;
        }
    }

    /*
     * --------------------------------------------------------
     * ALTERNATIVE PHONE 2
     * --------------------------------------------------------
     */

    elseif (
        $field === 'ALT2_IMAGE'
    ) {

        $altBrand =
            getFieldValue(
                $allPlaceholders,
                $data,
                'ALT2_BRAND'
            );

        $altName =
            getFieldValue(
                $allPlaceholders,
                $data,
                'ALT2_NAME'
            );

        if ($altBrand !== '') {
            $brand = $altBrand;
        }

        if ($altName !== '') {
            $phoneTitle = $altName;
        }
    }

    /*
     * --------------------------------------------------------
     * ALTERNATIVE PHONE 3
     * --------------------------------------------------------
     */

    elseif (
        $field === 'ALT3_IMAGE'
    ) {

        $altBrand =
            getFieldValue(
                $allPlaceholders,
                $data,
                'ALT3_BRAND'
            );

        $altName =
            getFieldValue(
                $allPlaceholders,
                $data,
                'ALT3_NAME'
            );

        if ($altBrand !== '') {
            $brand = $altBrand;
        }

        if ($altName !== '') {
            $phoneTitle = $altName;
        }
    }

    /*
     * لا يمكن بناء الصورة بدون BRAND و NAME
     */

    if (
        $brand === '' ||
        $phoneTitle === ''
    ) {
        return '';
    }

    /*
     * --------------------------------------------------------
     * BRAND DIRECTORY
     * --------------------------------------------------------
     *
     * Samsung -> samsung
     * Apple   -> apple
     * Xiaomi  -> xiaomi
     */

    $brandFolder =
        strtolower(
            trim($brand)
        );

    $brandFolder =
        preg_replace(
            '/[^a-z0-9]+/i',
            '',
            $brandFolder
        );

    /*
     * --------------------------------------------------------
     * PHONE FILE NAME
     * --------------------------------------------------------
     */

    $phoneName =
        normalizePhoneFileName(
            $phoneTitle,
            $brand
        );

    if (
        $brandFolder === '' ||
        $phoneName === ''
    ) {
        return '';
    }

    /*
     * --------------------------------------------------------
     * EXACT IMAGE SUFFIX
     * --------------------------------------------------------
     */

    $suffix = '';

    switch ($field) {

        case 'HERO_IMAGE':

            $suffix =
                '_hero_3_2.webp';

            break;

        case 'DESIGN_IMAGE':

            $suffix =
                '_design_1_1.webp';

            break;

        case 'DISPLAY_IMAGE':

            $suffix =
                '_display_1_1.webp';

            break;

        case 'CAMERA_SAMPLE_1':

            $suffix =
                '_camera_1_16_9.webp';

            break;

        case 'CAMERA_SAMPLE_2':

            $suffix =
                '_camera_2_16_9.webp';

            break;

        case 'CAMERA_SAMPLE_3':

            $suffix =
                '_camera_3_16_9.webp';

            break;

        case 'ALT1_IMAGE':
        case 'ALT2_IMAGE':
        case 'ALT3_IMAGE':

            $suffix =
                '_hero_3_2.webp';

            break;

        default:

            return '';
    }

    /*
     * --------------------------------------------------------
     * FINAL URL
     * --------------------------------------------------------
     */

    return
        'https://techdealshub.online/' .
        $brandFolder .
        '/img/' .
        $phoneName .
        $suffix;
}

/*
 * ============================================================
 * POST
 * ============================================================
 */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $data = [];

    /*
     * ========================================================
     * READ ALL FORM VALUES
     * ========================================================
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
     * ========================================================
     * GET MAIN BRAND
     * ========================================================
     */

    $brand =
        getFieldValue(
            $allPlaceholders,
            $data,
            'BRAND'
        );

    /*
     * ========================================================
     * GET MAIN TITLE
     * ========================================================
     */

    $firstTitleValue =
        getFieldValue(
            $allPlaceholders,
            $data,
            'TITLE'
        );

    /*
     * ========================================================
     * IMAGE FIELDS
     * ========================================================
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

    /*
     * هنا يتم تخزين المسارات التي تم إنشاؤها.
     */

    $generatedImages = [];

    /*
     * ========================================================
     * GENERATE ONLY THE 9 IMAGE PATHS
     * ========================================================
     */

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

        /*
         * بناء المسار الصحيح.
         */

        $generatedPath =
            buildImageUrl(
                $upperField,
                $brand,
                $firstTitleValue,
                $allPlaceholders,
                $data
            );

        /*
         * نغير قيمة IMAGE فقط.
         */

        if (
            $generatedPath !== ''
        ) {

            $data[$fieldNumber] =
                $generatedPath;
        }

        $generatedImages[
            $upperField
        ] =
            $generatedPath;
    }

    /*
     * ========================================================
     * AUTOMATIC IMAGE ALT
     * ========================================================
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
     * ========================================================
     * GENERATE *_HTML FIELDS
     * ========================================================
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

        $baseIndex =
            null;

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
     * ============================================================
     * PROTECT EXISTING HREF ATTRIBUTES
     * ============================================================
     *
     * الروابط الموجودة حرفياً داخل القالب يتم حمايتها.
     *
     * هذا يمنع PHP من تغيير:
     *
     * menu
     * nav
     * footer
     * offers
     * home
     * phones
     * accessories
     * وأي href آخر موجود أصلاً.
     *
     * ============================================================
     */

    $protectedHrefs = [];

    $hrefTokenPrefix =
        '__TDH_PROTECTED_HREF_' .
        bin2hex(
            random_bytes(8)
        ) .
        '_';

    $output =
        preg_replace_callback(
            "~\\bhref\\s*=\\s*([\"'])(.*?)\\1~isu",
            static function (
                array $match
            ) use (
                &$protectedHrefs,
                $hrefTokenPrefix
            ): string {

                $token =
                    $hrefTokenPrefix .
                    count(
                        $protectedHrefs
                    ) .
                    '__';

                $protectedHrefs[
                    $token
                ] =
                    $match[0];

                return $token;
            },
            $template
        ) ?? $template;

    /*
     * ============================================================
     * REPLACE PLACEHOLDERS
     * ============================================================
     */

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
                    use ($value): string
                {
                    return $value;
                },
                $output
            ) ?? $output;
    }

    /*
     * ============================================================
     * RESTORE HREF EXACTLY
     * ============================================================
     */

    if (
        !empty(
            $protectedHrefs
        )
    ) {

        $output =
            strtr(
                $output,
                $protectedHrefs
            );
    }

    /*
     * ============================================================
     * REMOVE REMAINING PLACEHOLDERS
     * ============================================================
     */

    $output =
        preg_replace(
            '/\{\{\s*[A-Za-z0-9_]+\s*\}\}/',
            '',
            $output
        );

    /*
     * ============================================================
     * CONSOLE LOG
     * ============================================================
     *
     * يعرض المسارات التسعة التي صنعها PHP.
     * لا يقوم بتغيير أي رابط.
     * ============================================================
     */

    $consoleImagesJson =
        json_encode(
            $generatedImages,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        );

    if (
        $consoleImagesJson === false
    ) {

        $consoleImagesJson =
            '{}';
    }

    $consoleScript =
        '<script>' .

        'console.group("TDH Generated Image Paths");' .

        'const TDH_GENERATED_IMAGES = ' .
        $consoleImagesJson .
        ';' .

        'Object.entries(TDH_GENERATED_IMAGES).forEach(([field,path]) => {' .

        'console.log(field + ":", path);' .

        '});' .

        'console.groupEnd();' .

        '</script>';

    if (
        stripos(
            $output,
            '</body>'
        ) !== false
    ) {

        $output =
            preg_replace(
                '~</body>~i',
                $consoleScript .
                '</body>',
                $output,
                1
            ) ?? $output;

    } else {

        $output .=
            $consoleScript;
    }

    /*
     * ============================================================
     * GET TITLE
     * ============================================================
     */

    $title =
        $firstTitleValue;

    if (
        $title === ''
    ) {

        $title =
            'Phone Review';
    }

    /*
     * ============================================================
     * GENERATE SLUG
     * ============================================================
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

    if (
        $slug === ''
    ) {

        $slug =
            'phone-review-' .
            date(
                'Ymd-His'
            );
    }

    $slug =
        preg_replace(
            '/-review$/i',
            '',
            $slug
        ) ?? $slug;

    /*
     * ============================================================
     * REVIEWS DIRECTORY
     * ============================================================
     */

    $reviewsDirectory =
        __DIR__ .
        '/reviews';

    if (
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
     * ============================================================
     * OUTPUT FILE
     * ============================================================
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
     * WRITE FILE
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

        $success =
            'Review created successfully: ' .
            'reviews/' .
            $outputName;
    }
}

?>

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
                text.split(
                    /\r?\n/
                );

            const entries = [];

            let currentField =
                null;

            lines.forEach(
                function (line) {

                    const separator =
                        line.indexOf(':');

                    if (
                        separator !== -1 &&
                        line
                            .substring(
                                0,
                                separator
                            )
                            .trim() !== ''
                    ) {

                        currentField =
                            normalizeField(
                                line.substring(
                                    0,
                                    separator
                                )
                            );

                        const value =
                            line.substring(
                                separator + 1
                            );

                        entries.push({

                            field:
                                currentField,

                            value:
                                value.trim()

                        });

                    }

                    else if (
                        currentField !== null &&
                        line.trim() !== ''
                    ) {

                        const last =
                            entries[
                                entries.length - 1
                            ];

                        if (last) {

                            last.value +=
                                '\n' +
                                line;
                        }

                    }

                }
            );

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