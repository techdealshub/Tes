<?php
declare(strict_types=1);

/*
 * add_reviews.php
 *
 * This form is generated DIRECTLY from template_reviews_fixed.html.
 * Every unique {{PLACEHOLDER}} in the template becomes exactly ONE input.
 * Repeated placeholders (including TITLE) are therefore never duplicated.
 *
 * The *_HTML placeholders are generated automatically from their base fields
 * and are NOT shown as separate inputs.
 */

$message = '';
$error = '';

$templatePath = __DIR__ . '/template_reviews_fixed.html';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function lines(string $value): array
{
    $items = preg_split('/\R/u', trim($value)) ?: [];
    return array_values(array_filter(
        array_map('trim', $items),
        static fn($v) => $v !== ''
    ));
}

function listHtml(string $value, string $tag = 'span'): string
{
    $out = '';
    foreach (lines($value) as $item) {
        $out .= '<' . $tag . '>' . e($item) . '</' . $tag . '>';
    }
    return $out;
}

function slugify(string $value): string
{
    $value = trim($value);

    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

    return trim($value, '-');
}

function fieldLabel(string $field): string
{
    $label = strtolower($field);
    $label = str_replace('_', ' ', $label);
    return ucwords($label);
}

function isMultilineField(string $field): bool
{
    $multiline = [
        'DESCRIPTION',
        'REVIEW_DESCRIPTION',
        'SUMMARY',
        'OVERALL_DESCRIPTION',
        'PROCESSOR_DESC',
        'CPU_DESCRIPTION',
        'GPU_DESCRIPTION',
        'RAM_DESC',
        'STORAGE_DESC',
        'SLOT_DESCRIPTION',
        'GAMING_DESC',
        'PERFORMANCE_REVIEW',
        'MAIN_CAMERA_DESC',
        'ULTRAWIDE_CAMERA_DESC',
        'TELEPHOTO_CAMERA_DESC',
        'SELFIE_CAMERA_DESC',
        'CAMERA_REVIEW',
        'MAX_VIDEO_RESOLUTION_DESC',
        'FOURK_RECORDING_DESC',
        'VIDEO_STABILIZATION_DESC',
        'AUDIO_QUALITY_DESC',
        'JACK_DESCRIPTION',
        'VIDEO_REVIEW',
        'FRONT_CAMERA_DESC',
        'SELFIE_VIDEO_DESC',
        'SELFIE_PORTRAIT_DESC',
        'SELFIE_LOWLIGHT_DESC',
        'SELFIE_REVIEW',
        'BATTERY_CAPACITY_DESC',
        'CHARGING_SPEED_DESC',
        'WIRELESS_CHARGING_DESC',
        'BATTERY_TYPE_DESC',
        'BATTERY_REVIEW',
        'WIRED_CHARGING_DESC',
        'WIRELESS_CHARGING_POWER_DESC',
        'REVERSE_CHARGING_DESC',
        'GPU_PERFORMANCE_DESC',
        'FRAME_RATE_DESCRIPTION',
        'THERMALS_DESCRIPTION',
        'GAMING_MODE_DESCRIPTION',
        'GAMING_REVIEW',
        'CHARGER_INCLUDED_DESC',
        'CHARGING_REVIEW',
        'OPERATING_SYSTEM_DESC',
        'USER_INTERFACE_DESC',
        'SOFTWARE_UPDATES_DESC',
        'AI_FEATURES_DESC',
        'SOFTWARE_REVIEW',
        'AI_ASSISTANT_DESC',
        'AI_PHOTO_EDITING_DESC',
        'AI_TRANSLATION_DESC',
        'AI_PERFORMANCE_DESC',
        'AI_REVIEW',
        'FACE_UNLOCK_DESCRIPTION',
        'EMERGENCY_SOS_DESCRIPTION',
        'MODEL_AI_DESCRIPTION',
        'ACCELEROMETER_DESCRIPTION',
        'GYROSCOPE_DESCRIPTION',
        'BAROMETER_DESCRIPTION',
        'COMPASS_DESCRIPTION',
        'SPEAKER_SYSTEM_DESC',
        'SOUND_QUALITY_DESC',
        'DOLBY_SUPPORT_DESC',
        'HEADPHONE_SUPPORT_DESC',
        'AUDIO_REVIEW',
        'MOBILE_NETWORK_DESC',
        'WIFI_DESC',
        'BLUETOOTH_DESC',
        'USB_PORT_DESC',
        'CONNECTIVITY_REVIEW',
        'ANTUTU_DESC',
        'GEEKBENCH_DESC',
        'THREEDMARK_DESC',
        'PCMARK_DESC',
        'BENCHMARK_REVIEW',
        'DAILY_TASKS_DESC',
        'MULTITASKING_DESC',
        'HEAVY_APPS_DESC',
        'LONGTERM_PERFORMANCE_DESC',
        'USAGE_REVIEW',
        'COOLING_SYSTEM_DESC',
        'GAMING_TEMPERATURE_DESC',
        'PERFORMANCE_STABILITY_DESC',
        'DAILY_USAGE_TEMP_DESC',
        'HEAT_REVIEW',
        'FRAME_MATERIAL_DESC',
        'FRONT_PROTECTION_DESC',
        'WATER_RESISTANCE_DESC',
        'BUILD_QUALITY_DESC',
        'DURABILITY_REVIEW',
        'RAM_CAPACITY_DESC',
        'RAM_TYPE_DESC',
        'STORAGE_TYPE_DESC',
        'STORAGE_OPTIONS_DESC',
        'STORAGE_REVIEW',
        'STARTING_PRICE_DESC',
        'PREMIUM_PRICE_DESC',
        'VALUE_RATING_DESC',
        'BEST_FOR_DESC',
        'VALUE_VERDICT',
        'COMPETITION_REVIEW',
        'ALT1_DESC',
        'ALT2_DESC',
        'ALT3_DESC',
        'FINAL_VERDICT_TEXT',
        'REVIEW_1_TEXT',
        'REVIEW_2_TEXT',
        'FAQ_ANSWER_1',
        'FAQ_ANSWER_2',
        'FAQ_ANSWER_3',
        'FAQ_ANSWER_4',
        'DESIGN_DESCRIPTION_1',
        'DESIGN_DESCRIPTION_2',
        'FINGERPRINT_SENSOR_DESCRIPTION',
        'GEEKBENCH_SINGLE_DESC',
        'PCMARK_BATTERY_DESC'
    ];

    return in_array($field, $multiline, true);
}

function isListField(string $field): bool
{
    return in_array($field, [
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
        'USAGE_SCENARIOS'
    ], true);
}

function isUrlField(string $field): bool
{
    return str_contains($field, 'IMAGE') ||
           str_contains($field, 'LINK') ||
           $field === 'ALT1_LINK' ||
           $field === 'ALT2_LINK' ||
           $field === 'ALT3_LINK';
}

function isGeneratedHtmlField(string $field): bool
{
    return str_ends_with($field, '_HTML');
}

/*
 * Read the exact template and discover every unique placeholder.
 * This is intentional: the PHP file cannot drift away from the template.
 */
if (!is_file($templatePath) || !is_readable($templatePath)) {
    $error = 'template_reviews_fixed.html was not found next to add_reviews.php.';
    $template = '';
    $fields = [];
} else {
    $template = file_get_contents($templatePath);

    if ($template === false) {
        $error = 'Unable to read template_reviews_fixed.html.';
        $template = '';
        $fields = [];
    } else {
        preg_match_all('/\{\{\s*([A-Z0-9_]+)\s*\}\}/', $template, $matches);

        $fields = [];
        foreach (($matches[1] ?? []) as $field) {
            $field = strtoupper(trim($field));

            if ($field === '' || isGeneratedHtmlField($field)) {
                continue;
            }

            if (!in_array($field, $fields, true)) {
                $fields[] = $field;
            }
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['generate'])) {
    if ($template === '') {
        $error = 'The template could not be loaded.';
    } else {
        $data = [];

        foreach ($fields as $field) {
            $key = strtolower($field);
            $value = $_POST[$key] ?? '';
            $data[$field] = is_array($value) ? '' : trim((string)$value);
        }

        $title = trim((string)($data['TITLE'] ?? ''));

        if ($title === '') {
            $error = 'Title is required.';
        } else {
            /* Automatic fallbacks for image alt text. */
            if (isset($data['HERO_IMAGE_ALT']) && $data['HERO_IMAGE_ALT'] === '') {
                $data['HERO_IMAGE_ALT'] = $title;
            }

            if (isset($data['DESIGN_IMAGE_ALT']) && $data['DESIGN_IMAGE_ALT'] === '') {
                $data['DESIGN_IMAGE_ALT'] = $title . ' design';
            }

            /*
             * Generate HTML versions used by the template.
             * User enters one field only; the template may use it many times.
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
                'USAGE_SCENARIOS'
            ];

            $htmlData = [];

            foreach ($htmlFields as $field) {
                if (!array_key_exists($field, $data)) {
                    continue;
                }

                $raw = str_replace('|', "\n", $data[$field]);
                $tag = in_array($field, ['BUY_POINTS', 'SKIP_POINTS'], true)
                    ? 'li'
                    : 'span';

                $htmlData[$field . '_HTML'] = listHtml($raw, $tag);
            }

            /* Replace every placeholder. Repeated placeholders are safe. */
            foreach ($data as $key => $value) {
                $template = str_replace(
                    '{{' . $key . '}}',
                    e($value),
                    $template
                );
            }

            foreach ($htmlData as $key => $value) {
                $template = str_replace(
                    '{{' . $key . '}}',
                    $value,
                    $template
                );
            }

            /* Never leave an unresolved placeholder in the generated page. */
            $template = preg_replace('/\{\{\s*[A-Z0-9_]+\s*\}\}/', '', $template) ?? $template;

            $slug = slugify($title);

            if ($slug === '') {
                $error = 'The title could not be converted into a valid filename.';
            } else {
                $outputName = $slug . '-review.html';
                $outputPath = __DIR__ . '/' . $outputName;

                if (@file_put_contents($outputPath, $template, LOCK_EX) === false) {
                    $error = 'The review could not be written. Check folder permissions.';
                } else {
                    $message = 'Review generated successfully: ' . $outputName;
                }
            }
        }
    }
}

$fieldCount = count($fields);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Phone Review</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{color-scheme:dark}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{
    margin:0;
    padding:30px 15px 60px;
    background:#050816;
    color:#e5edf8;
    font-family:Inter,Arial,sans-serif;
    line-height:1.5;
}
.wrapper{width:min(1180px,100%);margin:auto}
.header{
    margin-bottom:20px;
    padding:24px;
    background:#0b1324;
    border:1px solid #1d2a40;
    border-radius:20px;
}
h1{margin:0 0 8px;font-size:26px;color:#fff}
.header p{margin:0;color:#94a3b8;font-size:13px}
.counter{
    margin-top:14px;
    color:#93c5fd;
    font-size:13px;
    font-weight:700;
}
.notice{
    margin:0 auto 18px;
    padding:14px 17px;
    border-radius:12px;
    border:1px solid #28558c;
    background:#0b1a30;
    color:#b9d7ff;
}
.notice.error{border-color:#7f1d1d;background:#2a0d12;color:#fecaca}
form{
    padding:24px;
    background:#0b1324;
    border:1px solid #1d2a40;
    border-radius:20px;
    box-shadow:0 20px 60px rgba(0,0,0,.30);
}
.section{
    margin:0 0 28px;
    padding:20px;
    border:1px solid #1d2a40;
    border-radius:16px;
    background:#08111f;
}
.section h2{
    margin:0 0 18px;
    padding-bottom:10px;
    border-bottom:1px solid #1d2a40;
    color:#fff;
    font-size:18px;
}
.field{margin:0 0 15px}
label{
    display:block;
    margin:0 0 7px;
    color:#a9b8ca;
    font-size:12px;
    font-weight:700;
}
input,textarea{
    width:100%;
    display:block;
    padding:11px 13px;
    border:1px solid #263753;
    border-radius:10px;
    outline:0;
    background:#07101f;
    color:#fff;
    font:inherit;
}
input{min-height:42px}
textarea{min-height:92px;resize:vertical}
input:focus,textarea:focus{border-color:#3b82f6}
input::placeholder,textarea::placeholder{color:#52657d}
.help{
    margin:0 0 10px;
    color:#64748b;
    font-size:11px;
}
button{
    width:100%;
    margin-top:8px;
    padding:14px 22px;
    border:0;
    border-radius:11px;
    background:linear-gradient(135deg,#3b82f6,#2563eb);
    color:#fff;
    font-weight:800;
    font-size:15px;
    cursor:pointer;
}
button:hover{filter:brightness(1.08)}
.bulk-helper{
    margin:0 0 24px;
    padding:18px;
    border:1px solid #263753;
    border-radius:14px;
    background:#07101f;
}
.bulk-helper h2{margin:0 0 6px;font-size:17px;color:#fff}
.bulk-helper p{margin:0 0 12px;color:#94a3b8;font-size:12px}
.bulk-helper textarea{min-height:180px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
.bulk-helper-actions{display:flex;gap:10px;margin-top:10px}
.bulk-helper-actions button{width:auto;margin:0;flex:1}
.bulk-helper .fill-btn{background:linear-gradient(135deg,#10b981,#059669)}
.bulk-helper .clear-btn{background:#172033;border:1px solid #263753}
.bulk-status{margin-top:10px;color:#93c5fd;font-size:12px;min-height:18px}
.topbar{
    position:sticky;
    top:10px;
    z-index:10;
    display:flex;
    justify-content:space-between;
    gap:12px;
    margin-bottom:18px;
    padding:12px 15px;
    background:rgba(7,16,31,.94);
    border:1px solid #1d2a40;
    border-radius:13px;
    backdrop-filter:blur(12px);
}
.topbar span{color:#93c5fd;font-size:12px;font-weight:700}
.topbar a{color:#9fb0c4;font-size:12px;text-decoration:none}
@media(max-width:700px){
    body{padding:15px 10px 40px}
    form,.header{padding:16px}
    .section{padding:15px}
}
</style>
</head>
<body>
<div class="wrapper">

<div class="header">
    <h1>Add Phone Review</h1>
    <p>
        This form is built directly from <strong>template_reviews_fixed.html</strong>.
        Every unique template field appears once, even when the same placeholder
        is repeated multiple times inside the template.
    </p>
    <div class="counter">Template fields: <?= $fieldCount ?></div>
</div>

<?php if ($message !== ''): ?>
    <div class="notice"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="notice error"><?= e($error) ?></div>
<?php endif; ?>

<div class="topbar">
    <span><?= $fieldCount ?> unique fields</span>
    <a href="#review-form">Go to form ↓</a>
</div>

<form method="POST" action="" id="review-form" autocomplete="off">

<div class="bulk-helper">
    <h2>Quick Fill All Fields</h2>
    <p>
        Paste one field per line using <strong>Field: value</strong>.
        All existing fields remain visible and editable.
    </p>
    <textarea id="bulk-helper" placeholder="Example:
Title: iPhone 17 Pro Max
Description: Excellent flagship phone
Brand: Apple
Model: iPhone 17 Pro Max
Display: 6.9-inch OLED
Processor: A19 Pro
RAM: 12GB"></textarea>
    <div class="bulk-helper-actions">
        <button type="button" class="fill-btn" id="fill-fields">Fill Fields Automatically</button>
        <button type="button" class="clear-btn" id="clear-helper">Clear</button>
    </div>
    <div class="bulk-status" id="bulk-status"></div>
</div>

<?php
$currentSection = '';

foreach ($fields as $field):
    $section = 'General';

    if (str_starts_with($field, 'DESIGN_') || in_array($field, [
        'MATERIAL','DIMENSIONS','WEIGHT','PROTECTION','BUILD_QUALITY'
    ], true)) {
        $section = 'Design & Build';
    } elseif (str_starts_with($field, 'DISPLAY_')) {
        $section = 'Display';
    } elseif (str_starts_with($field, 'PERFORMANCE_') || in_array($field, [
        'PROCESSOR','PROCESSOR_DESC','CPU','CPU_DESCRIPTION','GPU','GPU_DESCRIPTION',
        'RAM','RAM_DESC','STORAGE','STORAGE_DESC','SLOT_STATUS','SLOT_DESCRIPTION',
        'GAMING_SCORE','GAMING_DESC'
    ], true)) {
        $section = 'Performance';
    } elseif (str_starts_with($field, 'CAMERA_') || in_array($field, [
        'MAIN_CAMERA','MAIN_CAMERA_DESC','ULTRAWIDE_CAMERA','ULTRAWIDE_CAMERA_DESC',
        'TELEPHOTO_CAMERA','TELEPHOTO_CAMERA_DESC','SELFIE_CAMERA','SELFIE_CAMERA_DESC',
        'DAYLIGHT_SCORE','NIGHT_SCORE','PORTRAIT_SCORE','VIDEO_SCORE'
    ], true)) {
        $section = 'Camera';
    } elseif (str_starts_with($field, 'VIDEO_') || str_starts_with($field, 'SELFIE_')) {
        $section = 'Video & Selfie';
    } elseif (str_starts_with($field, 'BATTERY_') || str_starts_with($field, 'CHARGING_') ||
              in_array($field, ['BATTERY_CAPACITY','BATTERY_CAPACITY_DESC','CHARGING_SPEED','CHARGING_SPEED_DESC','WIRELESS_CHARGING','WIRELESS_CHARGING_DESC','BATTERY_TYPE','BATTERY_TYPE_DESC','WEB_BROWSING','VIDEO_PLAYBACK','BATTERY_GAMING','MIXED_USAGE','CHARGE_50','CHARGE_100','WIRED_CHARGING','WIRED_CHARGING_DESC','WIRELESS_CHARGING_POWER','WIRELESS_CHARGING_POWER_DESC','REVERSE_CHARGING','REVERSE_CHARGING_DESC'], true)) {
        $section = 'Battery & Charging';
    } elseif (str_starts_with($field, 'SOFTWARE_') || str_starts_with($field, 'AI_') ||
              in_array($field, ['OPERATING_SYSTEM','OPERATING_SYSTEM_DESC','USER_INTERFACE','USER_INTERFACE_DESC','SOFTWARE_UPDATES','SOFTWARE_UPDATES_DESC','AI_FEATURES','AI_FEATURES_DESC','INTERFACE_SPEED','CUSTOMIZATION_SCORE','MULTITASKING_SCORE','EASE_OF_USE'], true)) {
        $section = 'Software & AI';
    } elseif ($field === 'AUDIO_SUBTITLE' || str_starts_with($field, 'AUDIO_') ||
              in_array($field, ['FACE_UNLOCK','FACE_UNLOCK_DESCRIPTION','EMERGENCY_SOS','EMERGENCY_SOS_DESCRIPTION','MODEL_AI','MODEL_AI_DESCRIPTION','ACCELEROMETER','ACCELEROMETER_DESCRIPTION','GYROSCOPE','GYROSCOPE_DESCRIPTION','BAROMETER','BAROMETER_DESCRIPTION','COMPASS','COMPASS_DESCRIPTION','SPEAKER_SYSTEM','SPEAKER_SYSTEM_DESC','SOUND_QUALITY','SOUND_QUALITY_DESC','DOLBY_SUPPORT','DOLBY_SUPPORT_DESC','HEADPHONE_SUPPORT','HEADPHONE_SUPPORT_DESC'], true)) {
        $section = 'Audio & Sensors';
    } elseif (str_starts_with($field, 'CONNECTIVITY_') ||
              in_array($field, ['MOBILE_NETWORK','MOBILE_NETWORK_DESC','WIFI','WIFI_DESC','BLUETOOTH','BLUETOOTH_DESC','USB_PORT','USB_PORT_DESC','NFC','GPS','SIM_SUPPORT','ESIM','WIRELESS_FEATURES','CONNECTIVITY_REVIEW'], true)) {
        $section = 'Connectivity';
    } elseif (str_starts_with($field, 'BENCHMARK_') ||
              in_array($field, ['ANTUTU_TITLE','ANTUTU_SCORE','ANTUTU_DESC','GEEKBENCH_TITLE','GEEKBENCH_SCORE','GEEKBENCH_DESC','THREEDMARK_TITLE','THREEDMARK_SCORE','THREEDMARK_DESC','PCMARK_TITLE','PCMARK_SCORE','PCMARK_DESC','CPU_PERFORMANCE_SCORE','GPU_PERFORMANCE_SCORE','MEMORY_SPEED_SCORE','THERMAL_STABILITY_SCORE','PERFORMANCE_CATEGORY_TITLE','CPU_CHART_PERCENT','GPU_CHART_PERCENT','GAMING_CHART_PERCENT'], true)) {
        $section = 'Benchmarks';
    } elseif (str_starts_with($field, 'USAGE_') ||
              in_array($field, ['DAILY_TASKS_TITLE','DAILY_TASKS_DESC','MULTITASKING_TITLE','MULTITASKING_DESC','HEAVY_APPS_TITLE','HEAVY_APPS_DESC','LONGTERM_PERFORMANCE_TITLE','LONGTERM_PERFORMANCE_DESC','USAGE_RATING_TITLE','APP_PERFORMANCE_SCORE','USAGE_MULTITASKING_SCORE','RESPONSIVENESS_SCORE','STABILITY_SCORE'], true)) {
        $section = 'Daily Usage';
    } elseif (str_starts_with($field, 'HEAT_') || str_starts_with($field, 'COOLING_') ||
              in_array($field, ['COOLING_SYSTEM','COOLING_SYSTEM_DESC','GAMING_TEMPERATURE','GAMING_TEMPERATURE_DESC','PERFORMANCE_STABILITY','PERFORMANCE_STABILITY_DESC','DAILY_USAGE_TEMP','DAILY_USAGE_TEMP_DESC','THERMAL_TEST_TITLE','IDLE_TEMPERATURE','NORMAL_USAGE_TEMP','GAMING_SESSION_TEMP','STRESS_TEST_TEMP'], true)) {
        $section = 'Thermals & Cooling';
    } elseif (str_starts_with($field, 'DURABILITY_') ||
              in_array($field, ['FRAME_MATERIAL','FRAME_MATERIAL_DESC','FRONT_PROTECTION','FRONT_PROTECTION_DESC','WATER_RESISTANCE','WATER_RESISTANCE_DESC','BUILD_QUALITY_RATING','BUILD_QUALITY_DESC','PROTECTION_FEATURES_TITLE','DUST_RESISTANCE','WATER_DEPTH','SCREEN_PROTECTION','FRAME_STRENGTH'], true)) {
        $section = 'Durability';
    } elseif (str_starts_with($field, 'STORAGE_') ||
              in_array($field, ['RAM_CAPACITY','RAM_CAPACITY_DESC','RAM_TYPE','RAM_TYPE_DESC','STORAGE_TYPE','STORAGE_TYPE_DESC','STORAGE_OPTIONS','STORAGE_OPTIONS_DESC','APP_LOADING_SPEED','MULTITASKING_MEMORY','GAME_LOADING','FILE_TRANSFER'], true)) {
        $section = 'Storage';
    } elseif (str_starts_with($field, 'PRICE_') || str_starts_with($field, 'VALUE_') ||
              in_array($field, ['STARTING_PRICE','STARTING_PRICE_DESC','PREMIUM_PRICE','PREMIUM_PRICE_DESC','VALUE_RATING','VALUE_RATING_DESC','BEST_FOR','BEST_FOR_DESC','PERFORMANCE_VALUE','CAMERA_VALUE','BATTERY_VALUE','SUPPORT_VALUE','COMPETITORS','VALUE_VERDICT'], true)) {
        $section = 'Price & Value';
    } elseif (str_starts_with($field, 'COMPETITION_') ||
              str_starts_with($field, 'COMPETITOR_') ||
              str_starts_with($field, 'THIS_PHONE_') ||
              str_starts_with($field, 'COMPARE_') ||
              $field === 'BEST_CHOICE') {
        $section = 'Competition';
    } elseif (str_starts_with($field, 'ALT')) {
        $section = 'Alternatives';
    } elseif (str_starts_with($field, 'RATING_') || str_starts_with($field, 'BUYER_') ||
              str_starts_with($field, 'SKIP_') || str_starts_with($field, 'FINAL_') ||
              str_starts_with($field, 'USER_') || str_starts_with($field, 'REVIEW_')) {
        $section = 'Ratings & Verdict';
    } elseif (str_starts_with($field, 'FAQ_')) {
        $section = 'FAQ';
    }

    if ($section !== $currentSection):
        if ($currentSection !== '') echo "</div>";
        $currentSection = $section;
        echo '<div class="section">';
        echo '<h2>' . e($section) . '</h2>';
    endif;

    $key = strtolower($field);
    $value = isset($_POST[$key]) && !is_array($_POST[$key])
        ? (string)$_POST[$key]
        : '';

    $required = $field === 'TITLE' ? ' required' : '';
    $type = isUrlField($field) ? 'url' : 'text';
?>
    <div class="field">
        <label for="<?= e($key) ?>">
            <?= e(fieldLabel($field)) ?><?= $field === 'TITLE' ? ' *' : '' ?>
        </label>

        <?php if (isMultilineField($field) || isListField($field)): ?>
            <textarea
                id="<?= e($key) ?>"
                name="<?= e($key) ?>"
                data-field="<?= e($field) ?>"
                placeholder="<?= isListField($field) ? 'One item per line, or use | between items' : '' ?>"
                <?= $required ?>
            ><?= e($value) ?></textarea>
            <?php if (isListField($field)): ?>
                <div class="help">For multiple items, use one per line or separate them with <strong>|</strong>.</div>
            <?php endif; ?>
        <?php else: ?>
            <input
                type="<?= $type ?>"
                id="<?= e($key) ?>"
                name="<?= e($key) ?>"
                data-field="<?= e($field) ?>"
                value="<?= e($value) ?>"
                <?= $required ?>
            >
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if ($currentSection !== ''): ?>
</div>
<?php endif; ?>

<button type="submit" name="generate">Generate / Update Review</button>
</form>

</div>

<script>
(function () {
    const helper = document.getElementById('bulk-helper');
    const fillBtn = document.getElementById('fill-fields');
    const clearBtn = document.getElementById('clear-helper');
    const status = document.getElementById('bulk-status');

    function normalize(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/[\s\-]+/g, '_')
            .replace(/[^a-z0-9_]/g, '');
    }

    function buildFieldMap() {
        const map = {};
        document.querySelectorAll('[data-field]').forEach(function (el) {
            const key = normalize(el.dataset.field);
            if (key && !map[key]) map[key] = el;
        });
        return map;
    }

    function parseInput(raw) {
        const result = [];
        let current = null;

        raw.replace(/\r\n?/g, '\n').split('\n').forEach(function (line) {
            const trimmed = line.trim();
            if (!trimmed) return;

            const match = trimmed.match(/^([^:]+):\s*(.*)$/);

            if (match) {
                const label = match[1].trim();

                // Block1 / Block2 / ... are optional separators.
                if (/^block\s*\d+$/i.test(label)) {
                    current = null;
                    return;
                }

                current = {
                    label: normalize(label),
                    value: match[2]
                };
                result.push(current);
            } else if (current) {
                // Continuation lines are kept in the same field.
                current.value += '\n' + line;
            }
        });

        return result;
    }

    fillBtn.addEventListener('click', function () {
        const raw = helper.value.trim();

        if (!raw) {
            status.textContent = 'Paste your data first.';
            return;
        }

        const map = buildFieldMap();
        const rows = parseInput(raw);
        let filled = 0;
        const unknown = [];

        rows.forEach(function (row) {
            const field = map[row.label];

            if (!field) {
                unknown.push(row.label);
                return;
            }

            field.value = row.value.trim();
            field.dispatchEvent(new Event('input', {bubbles:true}));
            field.dispatchEvent(new Event('change', {bubbles:true}));
            filled++;
        });

        status.textContent = unknown.length
            ? 'Filled ' + filled + ' fields. Unknown: ' + unknown.join(', ')
            : 'Done — ' + filled + ' fields filled. You can edit them normally below.';
    });

    clearBtn.addEventListener('click', function () {
        helper.value = '';
        status.textContent = '';
        helper.focus();
    });
})();
</script>

</body>
</html>
