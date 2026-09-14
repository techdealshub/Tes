<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/*
|--------------------------------------------------------------------------
| IMAGE PATH GENERATOR
|--------------------------------------------------------------------------
|
| المسار الأساسي:
|
| brand/img/phone/
|
| ويمكن الآن وضع الصور داخل مجلد فرعي:
|
| brand/img/phone/design/
| brand/img/phone/display/
| brand/img/phone/night_photo_sample/
| brand/img/phone/portrait_sample/
| brand/img/phone/camera_sample/
|
| مثال:
|
| apple/img/iphone17promax/design/photo.png
|
| إذا كانت الصورة 16:9 تصبح:
|
| apple/img/iphone17promax_design_16_9.png
|
| وإذا كانت 1:1 تصبح:
|
| apple/img/iphone17promax_design_1_1.png
|
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
|
| الصور الموجودة مباشرة داخل:
|
| brand/img/
|
| لا يتم فحصها.
|
| وكذلك الصور التي تم نقلها سابقًا إلى:
|
| brand/img/
|
| لن تتم معالجتها مرة أخرى.
|
|--------------------------------------------------------------------------
*/

$baseDir = __DIR__;


/*
|--------------------------------------------------------------------------
| الصورة المقبولة
|--------------------------------------------------------------------------
*/

$allowedExtensions = [
    'jpg',
    'jpeg',
    'png',
    'webp',
    'gif',
    'bmp',
];


/*
|--------------------------------------------------------------------------
| أقصى فرق مسموح عن النسبة القياسية
|--------------------------------------------------------------------------
*/

$ratioTolerance = 0.025;


/*
|--------------------------------------------------------------------------
| النسب المدعومة
|--------------------------------------------------------------------------
*/

$ratios = [
    '1_1'  => 1 / 1,

    '3_2'  => 3 / 2,
    '2_3'  => 2 / 3,

    '4_3'  => 4 / 3,
    '3_4'  => 3 / 4,

    '16_9' => 16 / 9,
    '9_16' => 9 / 16,
];


/*
|--------------------------------------------------------------------------
| المجلدات التي يمكن أن تكون Brands
|--------------------------------------------------------------------------
*/

$allowedBrands = [
    'apple',
    'samsung',
    'xiaomi',
    'google',
    'oneplus',
    'huawei',
    'honor',
    'oppo',
    'vivo',
    'realme',
    'sony',
    'motorola',
    'nothing',
    'asus',
    'zte',
    'lenovo',
    'nubia',
];


/*
|--------------------------------------------------------------------------
| FUNCTIONS
|--------------------------------------------------------------------------
*/


/*
 * lowercase آمن
 */
function toLowerSafe(string $value): string
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }

    return strtolower($value);
}


/*
 * تنظيف اسم الهاتف أو اسم المجلد الفرعي.
 *
 * مثال:
 *
 * iPhone 17 Pro Max
 * يصبح:
 *
 * iphone17promax
 *
 * و:
 *
 * Camera Sample
 * يصبح:
 *
 * camerasample
 */
function cleanPhoneName(string $name): string
{
    $name = trim($name);

    $name = toLowerSafe($name);

    /*
     * إزالة المسافات.
     */
    $name = preg_replace('/\s+/', '', $name) ?? $name;

    /*
     * نسمح فقط:
     *
     * a-z
     * 0-9
     * _
     * -
     */
    $name = preg_replace(
        '/[^a-z0-9_-]/',
        '',
        $name
    ) ?? $name;

    return $name;
}


/*
 * فحص امتداد الصورة.
 */
function isImageExtension(
    string $filename,
    array $allowedExtensions
): bool {

    $extension = pathinfo(
        $filename,
        PATHINFO_EXTENSION
    );

    $extension = toLowerSafe($extension);

    return in_array(
        $extension,
        $allowedExtensions,
        true
    );
}


/*
 * الحصول على اسم ملف غير مستخدم.
 *
 * مثال:
 *
 * iphone17promax_design_16_9.png
 *
 * إذا كان موجودًا:
 *
 * iphone17promax_design_16_9_2.png
 *
 * ثم:
 *
 * iphone17promax_design_16_9_3.png
 */
function getUniqueFilename(
    string $directory,
    string $baseName,
    string $extension
): string {

    $extension = toLowerSafe($extension);

    /*
     * المحاولة الأولى.
     */
    $filename =
        $baseName .
        '.' .
        $extension;

    if (
        !file_exists(
            $directory .
            DIRECTORY_SEPARATOR .
            $filename
        )
    ) {
        return $filename;
    }


    /*
     * الاسم موجود.
     *
     * نبدأ من 2.
     */
    $number = 2;

    while (true) {

        $filename =
            $baseName .
            '_' .
            $number .
            '.' .
            $extension;

        if (
            !file_exists(
                $directory .
                DIRECTORY_SEPARATOR .
                $filename
            )
        ) {
            return $filename;
        }

        $number++;
    }
}


/*
|--------------------------------------------------------------------------
| تحديد النسبة
|--------------------------------------------------------------------------
*/

function detectAspectRatio(
    int $width,
    int $height,
    array $ratios,
    float $tolerance
): ?array {

    if (
        $width <= 0 ||
        $height <= 0
    ) {
        return null;
    }


    /*
     * النسبة الحقيقية للصورة.
     */
    $actualRatio =
        $width /
        $height;


    $bestName = null;
    $bestRatio = 0.0;
    $bestDifference = PHP_FLOAT_MAX;


    foreach (
        $ratios
        as $name => $targetRatio
    ) {

        /*
         * الفرق النسبي.
         */
        $difference =
            abs(
                $actualRatio -
                $targetRatio
            ) /
            $targetRatio;


        if (
            $difference <
            $bestDifference
        ) {

            $bestDifference =
                $difference;

            $bestName =
                $name;

            $bestRatio =
                $targetRatio;
        }
    }


    /*
     * إذا كانت النسبة بعيدة جدًا
     * لا نصنفها.
     */
    if (
        $bestDifference >
        $tolerance
    ) {
        return null;
    }


    return [
        'name' => $bestName,
        'target' => $bestRatio,
        'actual' => $actualRatio,
        'difference' => $bestDifference,
    ];
}


/*
|--------------------------------------------------------------------------
| عرض النسبة الحقيقية
|--------------------------------------------------------------------------
*/

function formatRatio(float $ratio): string
{
    return number_format(
        $ratio,
        4,
        '.',
        ''
    );
}


/*
|--------------------------------------------------------------------------
| النتائج
|--------------------------------------------------------------------------
*/

$results = [];

$totalFound = 0;
$totalRenamed = 0;
$totalSkipped = 0;
$totalErrors = 0;


/*
|--------------------------------------------------------------------------
| SCAN
|--------------------------------------------------------------------------
|
| نبحث الآن داخل:
|
| brand/img/phone/
|
| ثم داخل:
|
| brand/img/phone/subfolder/
|
| فقط.
|
| ولا نبحث داخل:
|
| brand/img/
|
| مباشرة.
|
|--------------------------------------------------------------------------
*/

foreach (
    $allowedBrands
    as $brand
) {

    /*
     * مجلد Brand.
     */
    $brandDir =
        $baseDir .
        DIRECTORY_SEPARATOR .
        $brand;


    if (
        !is_dir($brandDir)
    ) {
        continue;
    }


    /*
     * مجلد img.
     */
    $imgDir =
        $brandDir .
        DIRECTORY_SEPARATOR .
        'img';


    if (
        !is_dir($imgDir)
    ) {
        continue;
    }


    /*
     * نأخذ فقط مجلدات الهواتف
     * الموجودة داخل img.
     */
    $entries = @scandir($imgDir);


    if (
        $entries === false
    ) {
        continue;
    }


    foreach (
        $entries
        as $phoneFolder
    ) {

        /*
         * تجاهل . و ..
         */
        if (
            $phoneFolder === '.' ||
            $phoneFolder === '..'
        ) {
            continue;
        }


        /*
         * يجب أن يكون مجلد هاتف.
         */
        $phoneDir =
            $imgDir .
            DIRECTORY_SEPARATOR .
            $phoneFolder;


        if (
            !is_dir($phoneDir)
        ) {
            continue;
        }


        /*
         * اسم الهاتف.
         */
        $phoneName =
            cleanPhoneName(
                $phoneFolder
            );


        if (
            $phoneName === ''
        ) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | STEP 1
        |--------------------------------------------------------------------------
        |
        | البحث داخل مجلد الهاتف عن المجلدات الفرعية.
        |
        | مثال:
        |
        | iphone17promax/
        | ├── design/
        | ├── display/
        | ├── camera_sample/
        | ├── night_photo_sample/
        | └── portrait_sample/
        |
        |--------------------------------------------------------------------------
        */

        $subFolders =
            @scandir($phoneDir);


        if (
            $subFolders === false
        ) {
            continue;
        }


        foreach (
            $subFolders
            as $subFolder
        ) {

            /*
             * تجاهل . و ..
             */
            if (
                $subFolder === '.' ||
                $subFolder === '..'
            ) {
                continue;
            }


            /*
             * المسار الكامل للمجلد الفرعي.
             */
            $subFolderDir =
                $phoneDir .
                DIRECTORY_SEPARATOR .
                $subFolder;


            /*
             * يجب أن يكون مجلدًا.
             */
            if (
                !is_dir($subFolderDir)
            ) {
                continue;
            }


            /*
             * تنظيف اسم المجلد الفرعي.
             *
             * مثال:
             *
             * Camera Sample
             *
             * يصبح:
             *
             * camerasample
             */
            $subFolderName =
                cleanPhoneName(
                    $subFolder
                );


            if (
                $subFolderName === ''
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 2
            |--------------------------------------------------------------------------
            |
            | قراءة الصور الموجودة داخل المجلد الفرعي.
            |
            |--------------------------------------------------------------------------
            */

            $files =
                @scandir($subFolderDir);


            if (
                $files === false
            ) {
                continue;
            }


            foreach (
                $files
                as $file
            ) {

                if (
                    $file === '.' ||
                    $file === '..'
                ) {
                    continue;
                }


                $oldPath =
                    $subFolderDir .
                    DIRECTORY_SEPARATOR .
                    $file;


                /*
                 * تجاهل المجلدات.
                 */
                if (
                    !is_file($oldPath)
                ) {
                    continue;
                }


                /*
                 * تجاهل أي شيء ليس صورة.
                 */
                if (
                    !isImageExtension(
                        $file,
                        $allowedExtensions
                    )
                ) {
                    continue;
                }


                $totalFound++;


                /*
                 * =====================================================
                 * قراءة الأبعاد الأصلية الحقيقية للصورة
                 * =====================================================
                 */

                $imageInfo =
                    @getimagesize(
                        $oldPath
                    );


                if (
                    $imageInfo === false ||
                    !isset(
                        $imageInfo[0],
                        $imageInfo[1]
                    )
                ) {

                    $totalErrors++;

                    $results[] = [
                        'brand' => $brand,
                        'phone' => $phoneName,
                        'subfolder' => $subFolderName,
                        'old' => $file,
                        'width' => 0,
                        'height' => 0,
                        'actual_ratio' => '-',
                        'detected' => '-',
                        'new' => '-',
                        'status' => 'error',
                        'message' =>
                            'Could not read original image dimensions.',
                    ];

                    continue;
                }


                /*
                 * الأبعاد الأصلية.
                 */
                $width =
                    (int)$imageInfo[0];

                $height =
                    (int)$imageInfo[1];


                /*
                 * النسبة الحقيقية.
                 */
                $actualRatio =
                    $width / $height;


                /*
                 * تحديد أقرب نسبة ضمن
                 * هامش 2.5%.
                 */
                $detected =
                    detectAspectRatio(
                        $width,
                        $height,
                        $ratios,
                        $ratioTolerance
                    );


                /*
                 * إذا لم توجد نسبة مناسبة:
                 *
                 * لا نعيد تسمية الصورة.
                 */
                if (
                    $detected === null
                ) {

                    $totalSkipped++;

                    $results[] = [
                        'brand' => $brand,
                        'phone' => $phoneName,
                        'subfolder' => $subFolderName,
                        'old' => $file,
                        'width' => $width,
                        'height' => $height,
                        'actual_ratio' =>
                            formatRatio($actualRatio),
                        'detected' => 'No match',
                        'new' => '-',
                        'status' => 'skipped',
                        'message' =>
                            'Aspect ratio is not close enough to a supported ratio.',
                    ];

                    continue;
                }


                /*
                 * النسبة المختارة.
                 *
                 * مثال:
                 *
                 * 1_1
                 * 3_2
                 * 4_3
                 * 16_9
                 */
                $ratioName =
                    $detected['name'];


                /*
                 * الامتداد الأصلي.
                 */
                $extension =
                    pathinfo(
                        $file,
                        PATHINFO_EXTENSION
                    );

                $extension =
                    toLowerSafe($extension);


                /*
                |--------------------------------------------------------------------------
                | الاسم الأساسي النهائي
                |--------------------------------------------------------------------------
                |
                | مثال:
                |
                | iphone17promax
                | +
                | design
                | +
                | 16_9
                |
                | =
                |
                | iphone17promax_design_16_9
                |
                |--------------------------------------------------------------------------
                */

                $baseName =
                    $phoneName .
                    '_' .
                    $subFolderName .
                    '_' .
                    $ratioName;


                /*
                 * اسم فريد.
                 */
                $newFilename =
                    getUniqueFilename(
                        $imgDir,
                        $baseName,
                        $extension
                    );


                /*
                 * المسار الجديد.
                 *
                 * مهم:
                 *
                 * الصورة تنتقل إلى img مباشرة.
                 */
                $newPath =
                    $imgDir .
                    DIRECTORY_SEPARATOR .
                    $newFilename;


                /*
                 * منع نفس المسار.
                 */
                if (
                    realpath($oldPath) !==
                    false &&
                    realpath($newPath) !==
                    false &&
                    realpath($oldPath) ===
                    realpath($newPath)
                ) {

                    $totalSkipped++;

                    $results[] = [
                        'brand' => $brand,
                        'phone' => $phoneName,
                        'subfolder' => $subFolderName,
                        'old' => $file,
                        'width' => $width,
                        'height' => $height,
                        'actual_ratio' =>
                            formatRatio($actualRatio),
                        'detected' => $ratioName,
                        'new' => $newFilename,
                        'status' => 'already',
                        'message' =>
                            'Already organized.',
                    ];

                    continue;
                }


                /*
                 * =====================================================
                 * MOVE + RENAME
                 * =====================================================
                 *
                 * الصورة لا يتم تعديلها.
                 *
                 * فقط يتم نقلها وإعادة تسميتها.
                 */
                $success =
                    @rename(
                        $oldPath,
                        $newPath
                    );


                if ($success) {

                    $totalRenamed++;

                    $results[] = [
                        'brand' => $brand,
                        'phone' => $phoneName,
                        'subfolder' => $subFolderName,
                        'old' => $file,
                        'width' => $width,
                        'height' => $height,
                        'actual_ratio' =>
                            formatRatio($actualRatio),
                        'detected' => $ratioName,
                        'new' => $newFilename,
                        'status' => 'renamed',
                        'message' =>
                            'Moved and renamed successfully.',
                    ];

                } else {

                    $totalErrors++;

                    $results[] = [
                        'brand' => $brand,
                        'phone' => $phoneName,
                        'subfolder' => $subFolderName,
                        'old' => $file,
                        'width' => $width,
                        'height' => $height,
                        'actual_ratio' =>
                            formatRatio($actualRatio),
                        'detected' => $ratioName,
                        'new' => $newFilename,
                        'status' => 'error',
                        'message' =>
                            'Could not move/rename image. Check permissions.',
                    ];
                }
            }
        }
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

<title>Image Path Generator</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 24px;

    background:
        radial-gradient(
            circle at top left,
            rgba(59, 130, 246, .14),
            transparent 35%
        ),
        #070b14;

    color: #e5e7eb;

    font-family:
        Arial,
        Helvetica,
        sans-serif;
}

.container {
    width: min(1250px, 100%);
    margin: 0 auto;
}

.header {
    margin-bottom: 24px;
}

.header h1 {
    margin: 0 0 8px;
    font-size: 30px;
}

.header p {
    margin: 0;
    color: #94a3b8;
    line-height: 1.6;
}

.refresh {
    display: inline-block;

    margin-top: 18px;

    padding: 12px 18px;

    border-radius: 12px;

    text-decoration: none;

    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    font-weight: 700;
}

.stats {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 14px;

    margin-bottom: 24px;
}

.stat {
    padding: 18px;

    border-radius: 18px;

    background:
        rgba(15, 23, 42, .88);

    border:
        1px solid
        rgba(148, 163, 184, .12);
}

.stat span {
    display: block;

    margin-bottom: 6px;

    color: #94a3b8;

    font-size: 13px;
}

.stat strong {
    font-size: 25px;
}

.table-wrap {
    overflow-x: auto;

    border-radius: 20px;

    border:
        1px solid
        rgba(148, 163, 184, .12);

    background:
        rgba(15, 23, 42, .84);
}

table {
    width: 100%;

    min-width: 1150px;

    border-collapse: collapse;
}

th,
td {
    padding: 14px;

    text-align: left;

    border-bottom:
        1px solid
        rgba(148, 163, 184, .08);

    vertical-align: middle;
}

th {
    color: #94a3b8;

    font-size: 12px;

    font-weight: 700;

    background:
        rgba(2, 6, 23, .55);
}

td {
    font-size: 14px;
}

.filename {
    word-break: break-all;
}

.original {
    color: #fbbf24;
}

.final {
    color: #4ade80;

    font-weight: 700;

    word-break: break-all;
}

.dimensions {
    white-space: nowrap;
}

.ratio {
    font-weight: 700;

    white-space: nowrap;
}

.real-ratio {
    color: #94a3b8;

    font-size: 12px;

    margin-top: 4px;
}

.badge {
    display: inline-block;

    padding: 6px 10px;

    border-radius: 999px;

    font-size: 11px;

    font-weight: 800;
}

.badge.success {
    color: #86efac;

    background:
        rgba(34, 197, 94, .12);
}

.badge.skip {
    color: #fcd34d;

    background:
        rgba(234, 179, 8, .12);
}

.badge.error {
    color: #fca5a5;

    background:
        rgba(239, 68, 68, .12);
}

.empty {
    padding: 50px 20px;

    text-align: center;

    color: #94a3b8;
}

.note {
    margin-top: 20px;

    padding: 16px;

    border-radius: 14px;

    color: #cbd5e1;

    background:
        rgba(15, 23, 42, .72);

    border:
        1px solid
        rgba(148, 163, 184, .10);

    line-height: 1.7;
}

@media (max-width: 700px) {

    body {
        padding: 16px;
    }

    .stats {
        grid-template-columns: 1fr 1fr;
    }

    .header h1 {
        font-size: 25px;
    }
}

</style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>Image Path Generator</h1>

        <p>
            Original dimensions are detected automatically.
            Images inside phone subfolders are moved and renamed only once.
        </p>

        <a
            class="refresh"
            href=""
        >
            Refresh &amp; Scan
        </a>

    </div>


    <div class="stats">

        <div class="stat">

            <span>Images Found</span>

            <strong>
                <?= $totalFound ?>
            </strong>

        </div>


        <div class="stat">

            <span>Renamed / Moved</span>

            <strong>
                <?= $totalRenamed ?>
            </strong>

        </div>


        <div class="stat">

            <span>Skipped</span>

            <strong>
                <?= $totalSkipped ?>
            </strong>

        </div>


        <div class="stat">

            <span>Errors</span>

            <strong>
                <?= $totalErrors ?>
            </strong>

        </div>

    </div>


    <?php if (empty($results)): ?>

        <div class="empty">

            <strong>
                No new images found.
            </strong>

            <br><br>

            Put new images inside:

            <br>

            <code>
                brand/img/phone/subfolder/
            </code>

        </div>

    <?php else: ?>

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Brand
                        </th>

                        <th>
                            Phone
                        </th>

                        <th>
                            Subfolder
                        </th>

                        <th>
                            Original
                        </th>

                        <th>
                            Original Dimensions
                        </th>

                        <th>
                            Real Ratio
                        </th>

                        <th>
                            Detected
                        </th>

                        <th>
                            Final Name
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach (
                    $results
                    as $item
                ): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $item['brand'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $item['phone'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $item['subfolder'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>


                        <td class="filename original">

                            <?= htmlspecialchars(
                                $item['old'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </td>


                        <td class="dimensions">

                            <?php if (
                                $item['width'] > 0
                            ): ?>

                                <?= $item['width'] ?>
                                ×
                                <?= $item['height'] ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php if (
                                $item['actual_ratio'] !== '-'
                            ): ?>

                                <strong>
                                    <?= htmlspecialchars(
                                        $item['actual_ratio'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <td class="ratio">

                            <?= htmlspecialchars(
                                $item['detected'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </td>


                        <td class="filename final">

                            <?php if (
                                $item['new'] !== '-'
                            ): ?>

                                <?= htmlspecialchars(
                                    $item['new'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                <div class="real-ratio">

                                    <?= htmlspecialchars(
                                        $item['brand']
                                        . '/img/'
                                        . $item['new'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php if (
                                $item['status']
                                === 'renamed'
                            ): ?>

                                <span class="badge success">
                                    RENAMED
                                </span>

                            <?php elseif (
                                $item['status']
                                === 'skipped'
                            ): ?>

                                <span class="badge skip">
                                    SKIPPED
                                </span>

                            <?php elseif (
                                $item['status']
                                === 'already'
                            ): ?>

                                <span class="badge success">
                                    ALREADY
                                </span>

                            <?php else: ?>

                                <span class="badge error">
                                    ERROR
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>


    <div class="note">

        <strong>How it works:</strong>

        New images are read from:

        <code>brand/img/phone/subfolder/</code>

        <br><br>

        For example:

        <code>
            apple/img/iphone17promax/design/photo.png
        </code>

        <br><br>

        If the image is 16:9, it becomes:

        <code>
            apple/img/iphone17promax_design_16_9.png
        </code>

        <br><br>

        If another image with the same name already exists,
        a unique number is added automatically:

        <code>
            iphone17promax_design_16_9_2.png
        </code>

        <br><br>

        After processing, the image is moved directly to:

        <code>
            brand/img/
        </code>

        Therefore, refreshing this page does not process
        the already organized images again.

    </div>

</div>

</body>

</html>