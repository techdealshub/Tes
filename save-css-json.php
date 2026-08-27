<?php

header("Content-Type: application/json; charset=utf-8");

try {

    $input = file_get_contents("php://input");

    if ($input === false || trim($input) === "") {
        throw new Exception("لم يتم استلام بيانات JSON.");
    }

    /*
     * التأكد من أن البيانات JSON صحيحة
     */
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception(
            "JSON غير صالح: " . json_last_error_msg()
        );
    }

    /*
     * اسم الملف النهائي
     */
    $filename = "css-values-discovered.json";

    /*
     * يحفظ الملف في نفس مجلد save-css-json.php
     */
    $filepath = __DIR__ . DIRECTORY_SEPARATOR . $filename;

    /*
     * تحويل JSON إلى صيغة مرتبة
     */
    $json = json_encode(
        $data,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        throw new Exception(
            "فشل تحويل البيانات إلى JSON."
        );
    }

    /*
     * الكتابة إلى الملف
     */
    $bytes = file_put_contents(
        $filepath,
        $json,
        LOCK_EX
    );

    if ($bytes === false) {
        throw new Exception(
            "PHP لا يستطيع الكتابة في هذا المجلد."
        );
    }

    echo json_encode([
        "success" => true,
        "message" => "تم حفظ الملف بنجاح.",
        "filename" => $filename,
        "path" => $filepath,
        "bytes" => $bytes
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}