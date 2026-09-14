<?php
declare(strict_types=1);

/*
 * ============================================================
 * TDH - AUTOMATIC IMAGE PATH GENERATOR
 * ============================================================
 *
 * الصور يتم توليدها تلقائياً من:
 *
 * BRAND + TITLE
 *
 * ولا يتم إدخال رابط الصورة يدوياً.
 *
 * ============================================================
 */


/*
 * ------------------------------------------------------------
 * 1. تحويل اسم البراند إلى اسم المجلد
 * ------------------------------------------------------------
 *
 * Apple       -> apple
 * Samsung     -> samsung
 * Xiaomi      -> xiaomi
 */
function normalizeBrandFolder(
    string $brand
): string {

    $brand = strtolower(
        trim($brand)
    );

    $brand = preg_replace(
        '/[^a-z0-9]+/i',
        '',
        $brand
    ) ?? '';

    return $brand;
}


/*
 * ------------------------------------------------------------
 * 2. تحويل اسم الهاتف إلى اسم الملف
 * ------------------------------------------------------------
 *
 * Apple + iPhone 17 Pro Max
 *        ↓
 * iphone17promax
 *
 * Samsung + Samsung Galaxy S26 Ultra
 *        ↓
 * galaxys26ultra
 */
function normalizePhoneFileName(
    string $title,
    string $brand
): string {

    $title = trim($title);
    $brand = trim($brand);

    if ($brand !== '') {

        $pattern =
            '/^' .
            preg_quote(
                $brand,
                '/'
            ) .
            '\s+/iu';

        $title =
            preg_replace(
                $pattern,
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
        ) ?? '';

    return $title;
}


/*
 * ------------------------------------------------------------
 * 3. نوع الصورة
 * ------------------------------------------------------------
 */
function getImageType(
    string $field
): string {

    $field =
        strtoupper(
            trim($field)
        );

    switch ($field) {

        case 'HERO_IMAGE':
            return 'hero';

        case 'DESIGN_IMAGE':
            return 'design';

        case 'DISPLAY_IMAGE':
            return 'display';

        case 'CAMERA_SAMPLE_1':
            return 'camera_1';

        case 'CAMERA_SAMPLE_2':
            return 'camera_2';

        case 'CAMERA_SAMPLE_3':
            return 'camera_3';

        case 'ALT1_IMAGE':
        case 'ALT2_IMAGE':
        case 'ALT3_IMAGE':
            return 'hero';

        default:
            return '';
    }
}


/*
 * ------------------------------------------------------------
 * 4. نسبة الصورة
 * ------------------------------------------------------------
 */
function getImageRatio(
    string $field
): string {

    $field =
        strtoupper(
            trim($field)
        );

    switch ($field) {

        case 'HERO_IMAGE':
        case 'ALT1_IMAGE':
        case 'ALT2_IMAGE':
        case 'ALT3_IMAGE':
            return '3_2';

        case 'DESIGN_IMAGE':
        case 'DISPLAY_IMAGE':
            return '1_1';

        case 'CAMERA_SAMPLE_1':
        case 'CAMERA_SAMPLE_2':
        case 'CAMERA_SAMPLE_3':
            return '16_9';

        default:
            return '';
    }
}


/*
 * ------------------------------------------------------------
 * 5. إنشاء رابط الصورة
 * ------------------------------------------------------------
 */
function generateImagePath(
    string $brand,
    string $title,
    string $field
): string {

    $brandFolder =
        normalizeBrandFolder(
            $brand
        );

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
        $brandFolder === '' ||
        $phoneName === '' ||
        $imageType === '' ||
        $imageRatio === ''
    ) {
        return '';
    }

    return
        'https://techdealshub.online/' .
        $brandFolder .
        '/img/' .
        $phoneName .
        '_' .
        $imageType .
        '_' .
        $imageRatio .
        '.webp';
}


/*
 * ============================================================
 * 6. الصور التسعة
 * ============================================================
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
 * ============================================================
 * 7. إنشاء روابط الصور
 * ============================================================
 *
 * يجب أن تكون هذه المتغيرات موجودة مسبقاً:
 *
 * $brand
 * $firstTitleValue
 * $data
 * $placeholderIndex
 *
 * ============================================================
 */

$generatedImages = [];


/*
 * ------------------------------------------------------------
 * الصور الرئيسية
 * ------------------------------------------------------------
 */
$mainImageFields = [

    'HERO_IMAGE',
    'DESIGN_IMAGE',
    'DISPLAY_IMAGE',
    'CAMERA_SAMPLE_1',
    'CAMERA_SAMPLE_2',
    'CAMERA_SAMPLE_3',

];


foreach (
    $mainImageFields
    as $field
) {

    $generatedImages[$field] =
        generateImagePath(
            $brand,
            $firstTitleValue,
            $field
        );
}


/*
 * ------------------------------------------------------------
 * الصور البديلة
 * ------------------------------------------------------------
 */
for (
    $i = 1;
    $i <= 3;
    $i++
) {

    $field =
        'ALT' .
        $i .
        '_IMAGE';

    $brandField =
        'ALT' .
        $i .
        '_BRAND';

    $titleField =
        'ALT' .
        $i .
        '_TITLE';


    /*
     * الحصول على رقم placeholder
     */
    $brandIndex =
        $placeholderIndex[
            $brandField
        ] ?? null;

    $titleIndex =
        $placeholderIndex[
            $titleField
        ] ?? null;


    /*
     * إذا لم يوجد placeholder
     */
    if (
        $brandIndex === null ||
        $titleIndex === null
    ) {

        $generatedImages[$field] = '';

        continue;
    }


    /*
     * قراءة البراند البديل
     */
    $altBrand =
        trim(
            (string) (
                $data[
                    $brandIndex
                ] ?? ''
            )
        );


    /*
     * قراءة اسم الهاتف البديل
     */
    $altTitle =
        trim(
            (string) (
                $data[
                    $titleIndex
                ] ?? ''
            )
        );


    /*
     * إنشاء الرابط
     */
    $generatedImages[$field] =
        generateImagePath(
            $altBrand,
            $altTitle,
            $field
        );
}


/*
 * ============================================================
 * 8. وضع روابط الصور داخل $data
 * ============================================================
 *
 * هنا يتم استبدال قيمة placeholder الخاصة بالصورة
 * فقط.
 *
 * ============================================================
 */

foreach (
    $generatedImages
    as $field => $imagePath
) {

    if (
        !isset(
            $placeholderIndex[$field]
        )
    ) {
        continue;
    }

    $index =
        $placeholderIndex[$field];

    $data[$index] =
        $imagePath;
}


/*
 * ============================================================
 * 9. Console للتحقق من المسارات
 * ============================================================
 *
 * هذا فقط للتحقق من الروابط التي تم توليدها.
 * لا يقوم بتغيير أي href موجود في القالب.
 *
 * ============================================================
 */

$consoleImagesJson =
    json_encode(
        $generatedImages,
        JSON_UNESCAPED_SLASHES |
        JSON_UNESCAPED_UNICODE |
        JSON_PRETTY_PRINT
    );

if (
    $consoleImagesJson === false
) {

    $consoleImagesJson = '{}';
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


/*
 * ============================================================
 * 10. مهم جداً
 * ============================================================
 *
 * لا يوجد هنا:
 *
 * fixMenuLinks()
 * تغيير href
 * تغيير nav
 * تغيير footer
 * تغيير src الموجود في القالب
 * تغيير CSS
 * تغيير HTML
 *
 * الصور فقط يتم توليد قيمتها من placeholders.
 *
 * ============================================================
 */