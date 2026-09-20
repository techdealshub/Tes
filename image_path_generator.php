<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/*
|--------------------------------------------------------------------------
| IMAGE PATH GENERATOR
|--------------------------------------------------------------------------
|
| المصدر:
|
| brand/img/phone/subfolder/image
|
| النتيجة:
|
| brand/img/phone_subfolder_ratio.extension
|
| مثال:
|
| samsung/img/samsunggalaxys26ultra/camera_1/photo.png
|
| إذا كانت الصورة 16:9:
|
| samsung/img/samsunggalaxys26ultra_camera_1_16_9.png
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

    '1_2'  => 1 / 2,
    '2_1'  => 2 / 1,

    '3_2'  => 3 / 2,
    '2_3'  => 2 / 3,

    '4_3'  => 4 / 3,
    '3_4'  => 3 / 4,

    '16_9' => 16 / 9,
    '9_16' => 9 / 16,
];


/*
|--------------------------------------------------------------------------
| Brands
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

function toLowerSafe(string $value): string
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }

    return strtolower($value);
}


function cleanPhoneName(string $name): string
{
    $name = trim($name);

    $name = toLowerSafe($name);

    $name = preg_replace('/\s+/', '', $name) ?? $name;

    $name = preg_replace(
        '/[^a-z0-9_-]/',
        '',
        $name
    ) ?? $name;

    return $name;
}


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


function getUniqueFilename(
    string $directory,
    string $baseName,
    string $extension
): string {
    $extension = toLowerSafe($extension);

    return $baseName . '.' . $extension;
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
*/

foreach (
    $allowedBrands
    as $brand
) {

    /*
     * brand/
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
     * brand/img/
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
     * البحث عن مجلدات الهواتف
     * داخل brand/img/
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

        if (
            $phoneFolder === '.' ||
            $phoneFolder === '..'
        ) {
            continue;
        }


        /*
         * brand/img/phone/
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
         * البحث عن المجلدات الفرعية.
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

            if (
                $subFolder === '.' ||
                $subFolder === '..'
            ) {
                continue;
            }


            /*
             * brand/img/phone/subfolder/
             */
            $subFolderDir =
                $phoneDir .
                DIRECTORY_SEPARATOR .
                $subFolder;

            if (
                !is_dir($subFolderDir)
            ) {
                continue;
            }


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
             * قراءة الصور.
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


                if (
                    !is_file($oldPath)
                ) {
                    continue;
                }


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
                 * قراءة أبعاد الصورة.
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
                        'path' => '-',
                        'status' => 'error',
                        'message' =>
                            'Could not read original image dimensions.',
                    ];

                    continue;
                }


                $width =
                    (int)$imageInfo[0];

                $height =
                    (int)$imageInfo[1];


                $actualRatio =
                    $width /
                    $height;


                /*
                 * تحديد النسبة.
                 */
                $detected =
                    detectAspectRatio(
                        $width,
                        $height,
                        $ratios,
                        $ratioTolerance
                    );


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
                        'path' => '-',
                        'status' => 'skipped',
                        'message' =>
                            'Aspect ratio is not close enough to a supported ratio.',
                    ];

                    continue;
                }


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
                 | الاسم النهائي
                 |--------------------------------------------------------------------------
                 |
                 | phone + subfolder + ratio
                 |
                 | مثال:
                 |
                 | samsunggalaxys26ultra_camera_1_16_9.png
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
                 * الحصول على اسم غير موجود.
                 */
                $newFilename =
                    getUniqueFilename(
                        $imgDir,
                        $baseName,
                        $extension
                    );


                /*
                 |--------------------------------------------------------------------------
                 | المسار النهائي
                 |--------------------------------------------------------------------------
                 |
                 | brand/img/
                 |
                 | مثال:
                 |
                 | samsung/img/
                 | samsunggalaxys26ultra_camera_1_16_9.png
                 |
                 |--------------------------------------------------------------------------
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
                        'path' =>
                            $brand .
                            '/img/' .
                            $newFilename,
                        'status' => 'already',
                        'message' =>
                            'Already organized.',
                    ];

                    continue;
                }


                /*
                 * النقل وإعادة التسمية.
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
                        'path' =>
                            $brand .
                            '/img/' .
                            $newFilename,
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
                        'path' =>
                            $brand .
                            '/img/' .
                            $newFilename,
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

    min-width: 1250px;

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

.final-path {
    display: block;

    margin-top: 6px;

    color: #60a5fa;

    font-size: 12px;

    font-weight: 500;

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
    <?php if ($item['new'] !== '-'): ?>
        <span class="final-path">
            <?= htmlspecialchars(
                'https://techdealshub.online/' . $item['path'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </span>
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
            samsung/img/samsunggalaxys26ultra/camera_1/photo.png
        </code>

        <br><br>

        If the image is 16:9, it becomes:

        <code>
            samsung/img/samsunggalaxys26ultra_camera_1_16_9.png
        </code>

        <br><br>

        If another image with the same name already exists,
        a unique number is added automatically:

        <code>
            samsunggalaxys26ultra_camera_1_16_9_2.png
        </code>

        <br><br>

        After processing, the image is moved directly to:

        <code>
            brand/img/
        </code>

    </div>

</div>

</body>

</html>