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
    die('Error: could not read template file.');
}

/*
 * ============================================================
 * HELPERS
 * ============================================================
 */

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function isTextarea(string $field): bool
{
    $textareaFields = [
        'DESCRIPTION',
        'SUMMARY',
        'REVIEW',
        'REVIEW_SUMMARY',
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

    return in_array(strtoupper($field), $textareaFields, true);
}

function isNumberField(string $field): bool
{
    $numberFields = [
        'RATING',
        'SCORE',
        'FINAL_SCORE',
    ];

    return in_array(strtoupper($field), $numberFields, true);
}

function listHtml(string $value): string
{
    $lines = preg_split('/\R/', trim($value)) ?: [];
    $items = [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '') {
            continue;
        }

        $line = preg_replace('/^[\-\*\•]\s*/u', '', $line);
        $items[] = '<li>' . e($line) . '</li>';
    }

    if (!$items) {
        return '';
    }

    return '<ul>' . implode('', $items) . '</ul>';
}

function generateHtmlField(string $field, string $value): string
{
    $listFields = [
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

    if (in_array(strtoupper($field), $listFields, true)) {
        return listHtml($value);
    }

    return nl2br(e($value));
}

function normalizePhoneFileName(string $value, string $brand = ''): string
{
    $value = trim($value);
    $brand = trim($brand);

    if ($brand !== '') {
        $value = preg_replace(
            '/^' . preg_quote($brand, '/') . '\s+/iu',
            '',
            $value
        ) ?? $value;
    }

    $value = preg_replace('/[\/\\\\]+/', ' ', $value) ?? $value;
    $value = preg_replace('/[^A-Za-z0-9]+/', '_', $value) ?? $value;
    $value = trim($value, '_');

    return $value !== '' ? $value : 'phone';
}

function getFieldValue(
    array $allPlaceholders,
    array $data,
    string $fieldName
): string {
    $fieldName = strtoupper(trim($fieldName));

    foreach ($allPlaceholders as $placeholder) {
        if (strtoupper($placeholder) === $fieldName) {
            $key = 'field_' . (array_search(
                $placeholder,
                $allPlaceholders,
                true
            ) + 1);

            return trim((string)($data[$key] ?? ''));
        }
    }

    return '';
}

function findBrandDirectory(string $brand): ?string
{
    $brand = trim($brand);

    if ($brand === '') {
        return null;
    }

    $entries = @scandir(__DIR__);

    if ($entries === false) {
        return null;
    }

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        if (
            strcasecmp($entry, $brand) === 0 &&
            is_dir(__DIR__ . '/' . $entry . '/img')
        ) {
            return $entry;
        }
    }

    return null;
}

function findActualImagePath(
    string $title,
    string $brand,
    string $type,
    string $ratio
): string {
    $brandDirectory = findBrandDirectory($brand);

    if ($brandDirectory === null) {
        $brandDirectory = trim($brand);
    }

    $phone = normalizePhoneFileName($title, $brand);

    return
        'https://techdealshub.online/' .
        rawurlencode($brandDirectory) .
        '/img/' .
        rawurlencode($phone) .
        '_' .
        $type .
        '_' .
        $ratio .
        '.webp';
}

function getImagePhoneTitle(
    string $field,
    array $allPlaceholders,
    array $data,
    string $mainTitle
): string {
    $field = strtoupper($field);

    if ($field === 'ALT1_IMAGE') {
        $value = getFieldValue($allPlaceholders, $data, 'ALT1_NAME');
        return $value !== '' ? $value : $mainTitle;
    }

    if ($field === 'ALT2_IMAGE') {
        $value = getFieldValue($allPlaceholders, $data, 'ALT2_NAME');
        return $value !== '' ? $value : $mainTitle;
    }

    if ($field === 'ALT3_IMAGE') {
        $value = getFieldValue($allPlaceholders, $data, 'ALT3_NAME');
        return $value !== '' ? $value : $mainTitle;
    }

    return $mainTitle;
}

function getImageBrand(
    string $field,
    array $allPlaceholders,
    array $data,
    string $mainBrand
): string {
    $field = strtoupper($field);

    if ($field === 'ALT1_IMAGE') {
        foreach ([
            'ALT1_BRAND',
            'ALT1_MANUFACTURER',
            'ALT1_COMPANY'
        ] as $candidate) {
            $value = getFieldValue($allPlaceholders, $data, $candidate);

            if ($value !== '') {
                return $value;
            }
        }
    }

    if ($field === 'ALT2_IMAGE') {
        foreach ([
            'ALT2_BRAND',
            'ALT2_MANUFACTURER',
            'ALT2_COMPANY'
        ] as $candidate) {
            $value = getFieldValue($allPlaceholders, $data, $candidate);

            if ($value !== '') {
                return $value;
            }
        }
    }

    if ($field === 'ALT3_IMAGE') {
        foreach ([
            'ALT3_BRAND',
            'ALT3_MANUFACTURER',
            'ALT3_COMPANY'
        ] as $candidate) {
            $value = getFieldValue($allPlaceholders, $data, $candidate);

            if ($value !== '') {
                return $value;
            }
        }
    }

    return $mainBrand;
}

/*
 * ============================================================
 * FIX ALTERNATIVE PHONE CARD IMAGE PATHS ONLY
 * ============================================================
 */

function fixPhoneCardAlternativeImages(string $html): string
{
    return preg_replace_callback(
        '/<article\b[^>]*class\s*=\s*([\'"])[^\'"]*\bphone-card\b[^\'"]*\1[^>]*>.*?<\/article>/is',
        function (array $match): string {

            $card = $match[0];

            if (
                !preg_match(
                    '/<span\b[^>]*>\s*(.*?)\s*<\/span>/is',
                    $card,
                    $brandMatch
                )
            ) {
                return $card;
            }

            if (
                !preg_match(
                    '/<h3\b[^>]*>\s*(.*?)\s*<\/h3>/is',
                    $card,
                    $titleMatch
                )
            ) {
                return $card;
            }

            $brand = trim(strip_tags($brandMatch[1]));
            $title = trim(strip_tags($titleMatch[1]));

            if ($brand === '' || $title === '') {
                return $card;
            }

            $brandDirectory = preg_replace(
                '/[^A-Za-z0-9]+/',
                '',
                $brand
            ) ?? $brand;

            $phoneFileName = preg_replace(
                '/[^A-Za-z0-9]+/',
                '_',
                $title
            ) ?? $title;

            $phoneFileName = trim($phoneFileName, '_');

            if ($phoneFileName === '') {
                return $card;
            }

            $newSrc =
                'https://techdealshub.online/' .
                $brandDirectory .
                '/img/' .
                $phoneFileName .
                '_hero_3_2.webp';

            $card = preg_replace_callback(
                '/(<img\b[^>]*\bsrc\s*=\s*)([\'"])([^\'"]*)(\2)/i',
                function (array $imgMatch) use ($newSrc): string {

                    $oldSrc = $imgMatch[3];

                    if (
                        stripos($oldSrc, '_alt1_3_2.webp') !== false ||
                        stripos($oldSrc, '_alt2_3_2.webp') !== false ||
                        stripos($oldSrc, '_alt3_3_2.webp') !== false
                    ) {
                        return
                            $imgMatch[1] .
                            $imgMatch[2] .
                            $newSrc .
                            $imgMatch[4];
                    }

                    return $imgMatch[0];
                },
                $card
            ) ?? $card;

            return $card;
        },
        $html
    ) ?? $html;
}

/*
 * ============================================================
 * REVIEWS DATA
 * ============================================================
 */

function normalizeReviewScore(string $value): int|float
{
    $value = trim($value);

    if ($value === '') {
        return 0;
    }

    if (preg_match('/-?\d+(?:[.,]\d+)?/', $value, $match)) {
        $number = (float) str_replace(',', '.', $match[0]);

        return floor($number) === $number
            ? (int) $number
            : $number;
    }

    return 0;
}

function getFirstReviewFieldValue(
    array $allPlaceholders,
    array $data,
    array $fieldNames
): string {

    foreach ($fieldNames as $fieldName) {

        $value = getFieldValue(
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

function updateReviewsData(
    string $brand,
    string $title,
    string $score,
    string $description,
    string $category,
    string $image,
    string $url
): bool {

    $dataPath = __DIR__ . '/reviews-data.php';
    $reviews = [];

    /*
     * Read the existing reviews-data.php if it already exists.
     */
    if (is_file($dataPath)) {

        $rawFile = @file_get_contents($dataPath);

        if ($rawFile !== false && trim($rawFile) !== '') {

            $rawTrimmed = trim($rawFile);

            /*
             * Support plain JSON too.
             */
            $decoded = json_decode($rawTrimmed, true);

            if (
                is_array($decoded) &&
                isset($decoded['reviews']) &&
                is_array($decoded['reviews'])
            ) {
                $reviews = $decoded['reviews'];

            } else {

                /*
                 * Support the PHP format generated by this function.
                 */
                $existingReturn = null;
                $existingOutput = '';
                $bufferStarted = false;

                try {
                    ob_start();
                    $bufferStarted = true;

                    $existingReturn = include $dataPath;

                    $existingOutput = (string) ob_get_clean();
                    $bufferStarted = false;

                } catch (Throwable $e) {

                    if ($bufferStarted) {
                        ob_end_clean();
                    }

                    $existingReturn = null;
                    $existingOutput = '';
                }

                if (
                    is_array($existingReturn) &&
                    isset($existingReturn['reviews']) &&
                    is_array($existingReturn['reviews'])
                ) {
                    $reviews = $existingReturn['reviews'];

                } else {

                    $decodedOutput =
                        json_decode(
                            trim($existingOutput),
                            true
                        );

                    if (
                        is_array($decodedOutput) &&
                        isset($decodedOutput['reviews']) &&
                        is_array($decodedOutput['reviews'])
                    ) {
                        $reviews = $decodedOutput['reviews'];
                    }
                }
            }
        }
    }

    /*
     * New / updated review.
     */
    $newReview = [
        'brand' => trim($brand),
        'title' => trim($title),
        'score' => normalizeReviewScore($score),
        'description' => trim($description),
        'category' => trim($category) !== ''
            ? trim($category)
            : 'Smartphones',
        'image' => trim($image),
        'url' => trim($url),
    ];

    /*
     * Update an existing review with the same URL,
     * otherwise append a new review.
     */
    $replaced = false;

    foreach ($reviews as $index => $review) {

        if (
            is_array($review) &&
            trim((string)($review['url'] ?? '')) ===
            trim($url)
        ) {
            $reviews[$index] = $newReview;
            $replaced = true;
            break;
        }
    }

    if (!$replaced) {
        $reviews[] = $newReview;
    }

    /*
     * Build unique brands.
     */
    $brands = [];

    foreach ($reviews as $review) {

        if (!is_array($review)) {
            continue;
        }

        $reviewBrand =
            trim((string)($review['brand'] ?? ''));

        if (
            $reviewBrand !== '' &&
            !in_array($reviewBrand, $brands, true)
        ) {
            $brands[] = $reviewBrand;
        }
    }

    /*
     * Final data structure consumed by reviews.html.
     */
    $payload = [
        'reviews' => array_values($reviews),
        'brands' => $brands,
    ];

    /*
     * Create a PHP file that:
     * 1. Returns the array when included by PHP.
     * 2. Outputs JSON when requested directly by reviews.html.
     */
    $exported = var_export($payload, true);

    $php =
        "<?php\n" .
        "declare(strict_types=1);\n\n" .
        "\$data = " . $exported . ";\n\n" .
        "if (basename(__FILE__) === basename(\$_SERVER['SCRIPT_FILENAME'] ?? '')) {\n" .
        "    header('Content-Type: application/json; charset=utf-8');\n" .
        "    echo json_encode(\$data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);\n" .
        "}\n\n" .
        "return \$data;\n";

    /*
     * IMPORTANT:
     * Do not suppress the write completely.
     *
     * First try the normal LOCK_EX write.
     * If the environment refuses the lock, try a normal write.
     */
    $bytes = file_put_contents(
        $dataPath,
        $php,
        LOCK_EX
    );

    if ($bytes === false) {

        $bytes = file_put_contents(
            $dataPath,
            $php
        );
    }

    /*
     * Final verification:
     * the file must actually exist and contain data.
     */
    if (
        $bytes === false ||
        !is_file($dataPath) ||
        filesize($dataPath) === 0
    ) {
        return false;
    }

    return true;
}

/*
 * ============================================================
 * FIND ALL PLACEHOLDERS
 * ============================================================
 */

preg_match_all(
    '/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/',
    $template,
    $placeholderMatches
);

$allPlaceholders = array_values(
    array_unique($placeholderMatches[1] ?? [])
);

/*
 * ============================================================
 * FORM SUBMISSION
 * ============================================================
 */

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = $_POST;

    $brand = getFieldValue(
        $allPlaceholders,
        $data,
        'BRAND'
    );

    $title = getFirstReviewFieldValue(
        $allPlaceholders,
        $data,
        ['TITLE', 'PRODUCT_TITLE', 'PHONE_TITLE']
    );

    /*
     * --------------------------------------------------------
     * Automatically generate image paths.
     * --------------------------------------------------------
     */

    $imageMap = [
        'HERO_IMAGE' => ['hero', '3_2'],
        'DESIGN_IMAGE' => ['design_1', '1_1'],
        'DISPLAY_IMAGE' => ['display_1', '1_1'],
        'CAMERA_SAMPLE_1' => ['camera_1', '16_9'],
        'CAMERA_SAMPLE_2' => ['camera_2', '16_9'],
        'CAMERA_SAMPLE_3' => ['camera_3', '16_9'],
        'ALT1_IMAGE' => ['alt1', '3_2'],
        'ALT2_IMAGE' => ['alt2', '3_2'],
        'ALT3_IMAGE' => ['alt3', '3_2'],
    ];

    foreach ($imageMap as $field => $parts) {

        $fieldIndex = array_search(
            $field,
            $allPlaceholders,
            true
        );

        if ($fieldIndex === false) {
            continue;
        }

        $key = 'field_' . ($fieldIndex + 1);

        $imageTitle = getImagePhoneTitle(
            $field,
            $allPlaceholders,
            $data,
            $title
        );

        $imageBrand = getImageBrand(
            $field,
            $allPlaceholders,
            $data,
            $brand
        );

        $data[$key] = findActualImagePath(
            $imageTitle,
            $imageBrand,
            $parts[0],
            $parts[1]
        );
    }

    /*
     * Automatically fill image ALT values if blank.
     */
    foreach ([
        'HERO_IMAGE_ALT',
        'DESIGN_IMAGE_ALT'
    ] as $altField) {

        $fieldIndex = array_search(
            $altField,
            $allPlaceholders,
            true
        );

        if ($fieldIndex === false) {
            continue;
        }

        $key = 'field_' . ($fieldIndex + 1);

        if (trim((string)($data[$key] ?? '')) === '') {
            $data[$key] = $title;
        }
    }

    /*
     * --------------------------------------------------------
     * Generate HTML versions of list fields.
     * --------------------------------------------------------
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

    foreach ($htmlFields as $field) {

        $fieldIndex = array_search(
            $field,
            $allPlaceholders,
            true
        );

        if ($fieldIndex === false) {
            continue;
        }

        $key = 'field_' . ($fieldIndex + 1);

        $data[$key] = generateHtmlField(
            $field,
            trim((string)($data[$key] ?? ''))
        );
    }

    /*
     * --------------------------------------------------------
     * Replace ONLY placeholders.
     * --------------------------------------------------------
     */

    $result = preg_replace_callback(
        '/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/',
        function (array $match) use (
            $allPlaceholders,
            $data
        ): string {

            $fieldName = $match[1];

            $fieldIndex = array_search(
                $fieldName,
                $allPlaceholders,
                true
            );

            if ($fieldIndex === false) {
                return $match[0];
            }

            $key = 'field_' . ($fieldIndex + 1);

            return (string)($data[$key] ?? '');
        },
        $template
    );

    if ($result === null) {
        $error = 'Error while replacing placeholders.';
    } else {

        /*
         * Only the requested alternative phone-card image src
         * correction is applied here.
         */
        $result = fixPhoneCardAlternativeImages($result);

        /*
         * ----------------------------------------------------
         * Output review filename.
         * ----------------------------------------------------
         */

        $slugSource = $title;

        $slug = strtolower(
            preg_replace(
                '/[^A-Za-z0-9]+/',
                '-',
                trim($slugSource)
            ) ?? ''
        );

        $slug = trim($slug, '-');

        if (preg_match('/-review$/i', $slug)) {
            $slug = preg_replace('/-review$/i', '', $slug);
        }

        if ($slug === '') {
            $slug = 'phone';
        }

        $reviewsDir = __DIR__ . '/reviews';

        if (!is_dir($reviewsDir)) {
            @mkdir($reviewsDir, 0775, true);
        }

        if (!is_dir($reviewsDir)) {
            $error =
                'Error: reviews directory could not be created.';
        } else {

            $outputPath =
                $reviewsDir .
                '/' .
                $slug .
                '-reviews.html';

            $written = @file_put_contents(
                $outputPath,
                $result,
                LOCK_EX
            );

            if ($written === false) {

                $written = @file_put_contents(
                    $outputPath,
                    $result
                );
            }

            if ($written === false) {

                $error =
                    'Review HTML could not be written: ' .
                    basename($outputPath);

            } else {

                /*
                 * ------------------------------------------------
                 * Update/create reviews-data.php automatically.
                 * ------------------------------------------------
                 */

                $reviewScore = getFirstReviewFieldValue(
                    $allPlaceholders,
                    $data,
                    ['RATING', 'SCORE', 'FINAL_SCORE']
                );

                $reviewDescription = getFirstReviewFieldValue(
                    $allPlaceholders,
                    $data,
                    [
                        'DESCRIPTION',
                        'SUMMARY',
                        'REVIEW',
                        'REVIEW_SUMMARY'
                    ]
                );

                $reviewCategory = getFirstReviewFieldValue(
                    $allPlaceholders,
                    $data,
                    ['CATEGORY']
                );

                $reviewImage = getFieldValue(
                    $allPlaceholders,
                    $data,
                    'HERO_IMAGE'
                );

                $reviewUrl =
                    'reviews/' .
                    $slug .
                    '-reviews.html';

                $reviewsDataUpdated =
                    updateReviewsData(
                        $brand,
                        $title,
                        $reviewScore,
                        $reviewDescription,
                        $reviewCategory,
                        $reviewImage,
                        $reviewUrl
                    );

                if (!$reviewsDataUpdated) {

                    $dataPath =
                        __DIR__ .
                        '/reviews-data.php';

                    $error =
                        'Review HTML was created, but reviews-data.php could not be created/updated. ' .
                        'Check that this PHP folder is writable: ' .
                        __DIR__;

                } else {

                    $message =
                        'Review HTML was created and reviews-data.php was created/updated successfully.';
                }
            }
        }
    }
}

/*
 * ============================================================
 * FORM HTML
 * ============================================================
 */
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Review</title>
<style>
*{box-sizing:border-box}
body{
    margin:0;
    padding:24px;
    background:#f4f4f4;
    color:#111;
    font-family:Arial,sans-serif
}
.wrap{
    max-width:1100px;
    margin:auto;
    background:#fff;
    padding:24px;
    border-radius:14px
}
h1{margin-top:0}
.field{
    margin-bottom:18px
}
.field label{
    display:block;
    font-weight:700;
    margin-bottom:7px
}
.field input,
.field textarea{
    width:100%;
    padding:11px;
    border:1px solid #ccc;
    border-radius:8px;
    font:inherit
}
.field textarea{
    min-height:120px;
    resize:vertical
}
button{
    padding:12px 20px;
    border:0;
    border-radius:8px;
    cursor:pointer;
    background:#111;
    color:#fff;
    font-weight:700
}
.success{
    background:#e8f8ed;
    color:#176b32;
    padding:12px;
    border-radius:8px;
    margin-bottom:18px
}
.error{
    background:#fdecec;
    color:#9b1c1c;
    padding:12px;
    border-radius:8px;
    margin-bottom:18px
}
.quick{
    width:100%;
    min-height:150px;
    margin-bottom:20px;
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
    font:14px/1.5 monospace
}
.quick-btn{
    margin-bottom:18px;
}
small{
    color:#666
}
</style>
</head>
<body>

<div class="wrap">

<h1>Add Review</h1>

<?php if ($message !== ''): ?>
<div class="success"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
<div class="error"><?= e($error) ?></div>
<?php endif; ?>

<textarea
    class="quick"
    id="quickFill"
    placeholder="FIELD: VALUE
TITLE: Samsung Galaxy S26 Ultra
BRAND: Samsung
RATING: 9.5
DESCRIPTION: ...
..."
></textarea>

<button
    type="button"
    class="quick-btn"
    id="quickFillBtn"
>
Quick Fill
</button>

<form method="post">

<?php foreach ($allPlaceholders as $index => $field): ?>

<?php
$key = 'field_' . ($index + 1);
$value = (string)($data[$key] ?? '');
?>

<div
    class="field"
    data-field="<?= e($field) ?>"
>

<label for="<?= e($key) ?>">
    <?= e($field) ?>
</label>

<?php if (isTextarea($field)): ?>

<textarea
    id="<?= e($key) ?>"
    name="<?= e($key) ?>"
    data-field="<?= e($field) ?>"
><?= e($value) ?></textarea>

<?php else: ?>

<input
    id="<?= e($key) ?>"
    name="<?= e($key) ?>"
    data-field="<?= e($field) ?>"
    type="<?= isNumberField($field) ? 'number' : 'text' ?>"
    value="<?= e($value) ?>"
>

<?php endif; ?>

</div>

<?php endforeach; ?>

<button type="submit">
Create Review
</button>

</form>

</div>

<script>
/*
 * ============================================================
 * QUICK FILL
 * DO NOT CHANGE
 * ============================================================
 */

(function(){

    const quick = document.getElementById('quickFill');
    const button = document.getElementById('quickFillBtn');

    if(!quick || !button){
        return;
    }

    button.addEventListener('click', function(){

        const text = quick.value || '';

        const lines = text.split(/\r?\n/);

        let currentField = null;
        let currentValue = [];

        const parsed = {};

        function normalizeField(name){

            return name
                .trim()
                .toUpperCase()
                .replace(/[^A-Z0-9]+/g,'_')
                .replace(/^_+|_+$/g,'');

        }

        function saveCurrent(){

            if(!currentField){
                return;
            }

            parsed[currentField] =
                currentValue.join('\n').trim();

        }

        lines.forEach(function(line){

            const match =
                line.match(
                    /^\s*([A-Za-z0-9 _-]+)\s*:\s*(.*)$/
                );

            if(match){

                saveCurrent();

                currentField =
                    normalizeField(match[1]);

                currentValue =
                    [match[2] || ''];

            }else if(currentField){

                currentValue.push(line);

            }

        });

        saveCurrent();

        let filled = 0;
        let notFound = [];

        Object.keys(parsed).forEach(function(field){

            const selector =
                '[data-field="' +
                CSS.escape(field) +
                '"]';

            const element =
                document.querySelector(selector);

            if(element){

                element.value =
                    parsed[field];

                element.dispatchEvent(
                    new Event(
                        'input',
                        {bubbles:true}
                    )
                );

                filled++;

            }else{

                notFound.push(field);

            }

        });

        let report =
            'Filled: ' + filled;

        if(notFound.length){

            report +=
                '\nNot found: ' +
                notFound.join(', ');

        }

        alert(report);

    });

})();
</script>

</body>
</html>
