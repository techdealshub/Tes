<?php
/*
 * extract_472.php
 *
 * Source:
 * samsung-galaxy-s26-ultra-review.html
 *
 * الهدف:
 * استخراج القيم القابلة للتعديل من صفحة المراجعة نفسها،
 * مع اسم دلالي صحيح + عدد الأحرف.
 *
 * لا يعتمد على class أو id لتسمية القيمة.
 * لا يضيف قيمًا وهمية للوصول إلى 472.
 */

mb_internal_encoding('UTF-8');

$sourceFile = __DIR__ . '/samsung-galaxy-s26-ultra-review.html';

if (!file_exists($sourceFile)) {
    die('ERROR: samsung-galaxy-s26-ultra-review.html not found.');
}

$html = file_get_contents($sourceFile);

if ($html === false) {
    die('ERROR: Cannot read the HTML file.');
}

libxml_use_internal_errors(true);

$dom = new DOMDocument();
$dom->loadHTML('<?xml encoding="UTF-8">' . $html);

$xpath = new DOMXPath($dom);

$fields = array();

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function cleanText($text)
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

function charCount($text)
{
    return mb_strlen($text, 'UTF-8');
}

function addField(&$fields, $name, $value)
{
    $value = cleanText($value);

    if ($value === '') {
        return;
    }

    $fields[] = array(
        'name'  => trim($name),
        'value' => $value,
        'count' => charCount($value)
    );
}

function nodeText($node)
{
    return cleanText($node->textContent);
}

function titleCaseName($text)
{
    $text = cleanText($text);

    if ($text === '') {
        return '';
    }

    return ucwords(strtolower($text));
}

/*
|--------------------------------------------------------------------------
| 1. TITLE
|--------------------------------------------------------------------------
*/

$titleNodes = $xpath->query('//head/title');

if ($titleNodes->length > 0) {
    addField(
        $fields,
        'Title',
        nodeText($titleNodes->item(0))
    );
}

/*
|--------------------------------------------------------------------------
| 2. DESCRIPTION
|--------------------------------------------------------------------------
|
| إذا كانت meta description موجودة نستعملها.
| وإذا لم تكن موجودة، نأخذ وصف الـ Hero باعتباره Description.
|--------------------------------------------------------------------------
*/

$descriptionNodes = $xpath->query(
    '//head/meta[translate(@name,
    "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
    "abcdefghijklmnopqrstuvwxyz")="description"]/@content'
);

if ($descriptionNodes->length > 0) {

    addField(
        $fields,
        'Description',
        $descriptionNodes->item(0)->nodeValue
    );

} else {

    $heroParagraph = $xpath->query(
        '//*[@id="hero-review"]//p[1]'
    );

    if ($heroParagraph->length > 0) {

        addField(
            $fields,
            'Description',
            nodeText($heroParagraph->item(0))
        );
    }
}

/*
|--------------------------------------------------------------------------
| 3. HERO
|--------------------------------------------------------------------------
*/

$badge = $xpath->query(
    '//*[@id="hero-review"]//*[contains(concat(" ",normalize-space(@class)," ")," review-badge ")]'
);

if ($badge->length > 0) {

    addField(
        $fields,
        'Review Badge',
        nodeText($badge->item(0))
    );
}

$h1 = $xpath->query(
    '//*[@id="hero-review"]//h1'
);

if ($h1->length > 0) {

    addField(
        $fields,
        'Review Title',
        nodeText($h1->item(0))
    );
}

/*
|--------------------------------------------------------------------------
| 4. HERO META
|--------------------------------------------------------------------------
*/

$metaItems = $xpath->query(
    '//*[@id="hero-review"]//*[contains(concat(" ",normalize-space(@class)," ")," meta-item ")]'
);

for ($i = 0; $i < $metaItems->length; $i++) {

    $item = $metaItems->item($i);

    $strong = $xpath->query('.//strong', $item);
    $span   = $xpath->query('.//span', $item);

    if ($strong->length > 0 && $span->length > 0) {

        $label = nodeText($strong->item(0));
        $value = nodeText($span->item(0));

        addField(
            $fields,
            $label,
            $value
        );
    }
}

/*
|--------------------------------------------------------------------------
| 5. MAIN CONTENT
|--------------------------------------------------------------------------
|
| نحدد اسم القسم من أقرب H2.
|--------------------------------------------------------------------------
*/

$main = $xpath->query('//main');

if ($main->length > 0) {

    $mainNode = $main->item(0);

    /*
     * كل section داخل main
     */
    $sections = $xpath->query('.//section', $mainNode);

    for ($s = 0; $s < $sections->length; $s++) {

        $section = $sections->item($s);

        /*
         * اسم القسم
         */
        $sectionTitle = '';

        $h2 = $xpath->query('.//h2[1]', $section);

        if ($h2->length > 0) {
            $sectionTitle = nodeText($h2->item(0));
        }

        if ($sectionTitle === '') {
            $sectionTitle = 'Section';
        }

        /*
         * ----------------------------------------------------------
         * A. Cards التي تحتوي:
         * label + value + description
         * ----------------------------------------------------------
         */

        $cards = $xpath->query(
            './/*[contains(
                concat(" ",normalize-space(@class)," "),
                " spec-card "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " performance-card "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " gaming-card "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " camera-card "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " battery-card "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " charging-card "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " software-card "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " ai-card "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " audio-card "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " connectivity-card "
            )]',
            $section
        );

        for ($c = 0; $c < $cards->length; $c++) {

            $card = $cards->item($c);

            $labelNode = $xpath->query(
                './span[1]',
                $card
            );

            $valueNode = $xpath->query(
                './h3[1] | ./strong[1]',
                $card
            );

            $descriptionNode = $xpath->query(
                './p[1]',
                $card
            );

            if ($labelNode->length > 0 &&
                $valueNode->length > 0) {

                $label = nodeText($labelNode->item(0));
                $value = nodeText($valueNode->item(0));

                if ($label !== '' && $value !== '') {

                    addField(
                        $fields,
                        $label,
                        $value
                    );

                    /*
                     * وصف القيمة
                     */
                    if ($descriptionNode->length > 0) {

                        addField(
                            $fields,
                            $label . ' Description',
                            nodeText($descriptionNode->item(0))
                        );
                    }
                }
            }
        }

        /*
         * ----------------------------------------------------------
         * B. highlight-item
         * ----------------------------------------------------------
         */

        $highlightItems = $xpath->query(
            './/*[contains(
                concat(" ",normalize-space(@class)," "),
                " highlight-item "
            )]',
            $section
        );

        for ($h = 0; $h < $highlightItems->length; $h++) {

            $item = $highlightItems->item($h);

            $strong = $xpath->query('./strong[1]', $item);
            $span   = $xpath->query('./span[1]', $item);

            if ($strong->length > 0 &&
                $span->length > 0) {

                $label = nodeText($strong->item(0));
                $value = nodeText($span->item(0));

                addField(
                    $fields,
                    $label,
                    $value
                );
            }
        }

        /*
         * ----------------------------------------------------------
         * C. benchmark / game rows
         * ----------------------------------------------------------
         */

        $rows = $xpath->query(
            './/*[contains(
                concat(" ",normalize-space(@class)," "),
                " benchmark "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " benchmark-row "
            )
            or contains(
                concat(" ",normalize-space(@class)," "),
                " game-row "
            )]',
            $section
        );

        for ($r = 0; $r < $rows->length; $r++) {

            $row = $rows->item($r);

            $spans = $xpath->query('./span', $row);
            $strongs = $xpath->query('./strong', $row);

            if ($spans->length > 0 &&
                $strongs->length > 0) {

                $label = nodeText($spans->item(0));
                $value = nodeText($strongs->item(0));

                if ($label !== '' && $value !== '') {

                    addField(
                        $fields,
                        $label,
                        $value
                    );
                }
            }
        }

        /*
         * ----------------------------------------------------------
         * D. review-text
         * ----------------------------------------------------------
         */

        $reviewTexts = $xpath->query(
            './/*[contains(
                concat(" ",normalize-space(@class)," "),
                " review-text "
            )]',
            $section
        );

        for ($rt = 0; $rt < $reviewTexts->length; $rt++) {

            addField(
                $fields,
                $sectionTitle . ' Review Text',
                nodeText($reviewTexts->item($rt))
            );
        }

        /*
         * ----------------------------------------------------------
         * E. FAQ
         * ----------------------------------------------------------
         */

        $faqItems = $xpath->query(
            './/*[contains(
                concat(" ",normalize-space(@class)," "),
                " faq-item "
            )]',
            $section
        );

        for ($f = 0; $f < $faqItems->length; $f++) {

            $faq = $faqItems->item($f);

            $question = $xpath->query('./h3[1]', $faq);
            $answer   = $xpath->query('./p[1]', $faq);

            if ($question->length > 0) {

                addField(
                    $fields,
                    'FAQ Question',
                    nodeText($question->item(0))
                );
            }

            if ($answer->length > 0) {

                addField(
                    $fields,
                    'FAQ Answer',
                    nodeText($answer->item(0))
                );
            }
        }

        /*
         * ----------------------------------------------------------
         * F. Pros / Cons
         * ----------------------------------------------------------
         */

        $pros = $xpath->query(
            './/*[contains(
                concat(" ",normalize-space(@class)," "),
                " pros-card "
            ) or contains(
                concat(" ",normalize-space(@class)," "),
                " pros "
            )]',
            $section
        );

        for ($p = 0; $p < $pros->length; $p++) {

            $lis = $xpath->query('.//li', $pros->item($p));

            for ($x = 0; $x < $lis->length; $x++) {

                addField(
                    $fields,
                    'Pros ' . ($x + 1),
                    nodeText($lis->item($x))
                );
            }
        }

        $cons = $xpath->query(
            './/*[contains(
                concat(" ",normalize-space(@class)," "),
                " cons-card "
            ) or contains(
                concat(" ",normalize-space(@class)," "),
                " cons "
            )]',
            $section
        );

        for ($p = 0; $p < $cons->length; $p++) {

            $lis = $xpath->query('.//li', $cons->item($p));

            for ($x = 0; $x < $lis->length; $x++) {

                addField(
                    $fields,
                    'Cons ' . ($x + 1),
                    nodeText($lis->item($x))
                );
            }
        }

        /*
         * ----------------------------------------------------------
         * G. Alternative cards
         * ----------------------------------------------------------
         */

        $alternatives = $xpath->query(
            './/*[contains(
                concat(" ",normalize-space(@class)," "),
                " alt-card "
            )]',
            $section
        );

        for ($a = 0; $a < $alternatives->length; $a++) {

            $alt = $alternatives->item($a);

            $h3alt = $xpath->query('./h3[1]', $alt);

            if ($h3alt->length > 0) {

                addField(
                    $fields,
                    'Alternative Phone',
                    nodeText($h3alt->item(0))
                );
            }

            $palt = $xpath->query('./p[1]', $alt);

            if ($palt->length > 0) {

                addField(
                    $fields,
                    'Alternative Description',
                    nodeText($palt->item(0))
                );
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| إزالة التكرارات غير المقصودة
|--------------------------------------------------------------------------
|
| لا نحذف القيمة لمجرد أنها تكررت في الصفحة.
| التكرار في أماكن مختلفة يمكن أن يكون قيمة مستقلة.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| HTML Output
|--------------------------------------------------------------------------
*/

$total = count($fields);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>472 Review Values Extractor</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 25px;
    background: #07111f;
    color: #ffffff;
    font-family: Arial, sans-serif;
}

.container {
    max-width: 1200px;
    margin: auto;
}

.header {
    background: #0d1b2a;
    border: 1px solid #1e293b;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 25px;
}

.header h1 {
    margin: 0 0 10px;
    font-size: 28px;
}

.status {
    padding: 15px;
    border-radius: 12px;
    background: #111827;
    border: 1px solid #1e293b;
    margin-top: 15px;
}

.status.ok {
    border-color: #22c55e;
}

.status.error {
    border-color: #ef4444;
}

.part {
    background: #0d1b2a;
    border: 1px solid #1e293b;
    border-radius: 18px;
    margin-bottom: 25px;
    overflow: hidden;
}

.part-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 18px 20px;
    background: #111827;
    border-bottom: 1px solid #1e293b;
}

.part-title {
    font-size: 19px;
    font-weight: 700;
}

.copy-btn {
    border: 0;
    background: #2563eb;
    color: #fff;
    padding: 10px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
}

.copy-btn:hover {
    background: #3b82f6;
}

textarea {
    width: 100%;
    min-height: 300px;
    display: block;
    border: 0;
    outline: none;
    resize: vertical;
    padding: 20px;
    background: #08111f;
    color: #e2e8f0;
    font-family: Consolas, monospace;
    font-size: 14px;
    line-height: 1.8;
}

.notice {
    padding: 18px;
    background: #111827;
    border: 1px solid #ef4444;
    border-radius: 14px;
    margin-bottom: 25px;
    line-height: 1.8;
}

.small {
    color: #94a3b8;
    font-size: 14px;
}

@media(max-width:700px) {

    body {
        padding: 12px;
    }

    .part-header {
        flex-direction: column;
        align-items: stretch;
    }

    .copy-btn {
        width: 100%;
    }
}
</style>
</head>

<body>

<div class="container">

<div class="header">

<h1>Samsung Galaxy S26 Ultra — Values Extractor</h1>

<div class="small">
Source:
samsung-galaxy-s26-ultra-review.html
</div>

<div class="status <?php echo ($total === 472 ? 'ok' : 'error'); ?>">

<strong>
Found: <?php echo $total; ?> values
</strong>

<?php if ($total === 472): ?>

<br>
Exactly 472 values were found.

<?php else: ?>

<br>
Expected: 472 values.
<br>
The extractor found <?php echo $total; ?> values.
<br>
No fake values were added.

<?php endif; ?>

</div>

</div>

<?php if ($total !== 472): ?>

<div class="notice">

<strong>WARNING</strong><br><br>

The source file was processed, but the semantic extraction rules
did not produce exactly 472 editable values.

This page intentionally does NOT manufacture missing values.

The number above is the real number extracted from:

<strong>samsung-galaxy-s26-ultra-review.html</strong>

</div>

<?php endif; ?>

<?php

/*
|--------------------------------------------------------------------------
| تقسيم إلى 9 أجزاء
|--------------------------------------------------------------------------
|
| 1-8 = 50
| 9   = 72
|
| لن يتم إنشاء الجزء التاسع إلا إذا كان لدينا 472 بالضبط.
|--------------------------------------------------------------------------
*/

if ($total === 472) {

    $parts = array();

    $start = 0;

    for ($partNumber = 1; $partNumber <= 8; $partNumber++) {

        $parts[] = array_slice($fields, $start, 50);
        $start += 50;
    }

    $parts[] = array_slice($fields, $start, 72);

    for ($p = 0; $p < 9; $p++) {

        $partNumber = $p + 1;
        $partFields = $parts[$p];

        $lines = array();

        foreach ($partFields as $field) {

            $lines[] =
                $field['name'] .
                ': ' .
                $field['count'];
        }

        $text = implode("\n", $lines);

        $safeText = htmlspecialchars(
            $text,
            ENT_QUOTES,
            'UTF-8'
        );

        ?>

        <div class="part">

            <div class="part-header">

                <div class="part-title">
                    Part <?php echo $partNumber; ?>
                    —
                    <?php echo count($partFields); ?> Values
                </div>

                <button
                    class="copy-btn"
                    type="button"
                    onclick="copyPart('part_<?php echo $partNumber; ?>', this)"
                >
                    Copy Part <?php echo $partNumber; ?>
                </button>

            </div>

            <textarea
                id="part_<?php echo $partNumber; ?>"
                readonly><?php echo $safeText; ?></textarea>

        </div>

        <?php
    }
}

?>

</div>

<script>

function copyPart(id, button) {

    var textarea = document.getElementById(id);

    if (!textarea) {
        return;
    }

    textarea.select();
    textarea.setSelectionRange(0, 999999);

    if (navigator.clipboard &&
        navigator.clipboard.writeText) {

        navigator.clipboard.writeText(
            textarea.value
        ).then(function () {

            var oldText = button.innerText;

            button.innerText = 'Copied ✓';

            setTimeout(function () {
                button.innerText = oldText;
            }, 1500);

        }).catch(function () {

            document.execCommand('copy');

            var oldText = button.innerText;

            button.innerText = 'Copied ✓';

            setTimeout(function () {
                button.innerText = oldText;
            }, 1500);
        });

    } else {

        document.execCommand('copy');

        var oldText = button.innerText;

        button.innerText = 'Copied ✓';

        setTimeout(function () {
            button.innerText = oldText;
        }, 1500);
    }
}

</script>

</body>
</html>