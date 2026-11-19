<?php
header('Content-Type: text/html; charset=utf-8');

function finish($message) {
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<a href="member.html">กลับหน้าสมาชิก</a>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    finish('กรุณาส่งข้อมูลจากฟอร์มสมัครสมาชิก');
}

$memberId = $_POST['member_id'] ?? '';
$fullName = $_POST['full_name'] ?? '';
$classroom = $_POST['classroom'] ?? '';

foreach ([$memberId, $fullName, $classroom] as $value) {
    if (!is_string($value)) {
        finish('รูปแบบข้อมูลไม่ถูกต้อง');
    }
}

$memberId = trim($memberId);
$fullName = trim($fullName);
$classroom = trim($classroom);

if ($memberId === '' || $fullName === '' || $classroom === '') {
    finish('กรุณากรอกข้อมูลให้ครบทุกช่อง');
}

if (mb_strlen($memberId, 'UTF-8') > 20 ||
    mb_strlen($fullName, 'UTF-8') > 100 ||
    mb_strlen($classroom, 'UTF-8') > 20) {
    finish('ข้อมูลยาวเกินขนาดที่กำหนด');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli(
        'localhost',
        'root',
        '616102009',
        'library_db'
    );
    $db->set_charset('utf8mb4');

    $statement = $db->prepare(
        'INSERT INTO members (member_id, full_name, classroom)
         VALUES (?, ?, ?)'
    );
    $statement->bind_param('sss', $memberId, $fullName, $classroom);
    $statement->execute();

    finish('สมัครสมาชิกสำเร็จ');
} catch (mysqli_sql_exception $error) {
    if ($error->getCode() === 1062) {
        finish('รหัสสมาชิกนี้มีอยู่แล้ว กรุณาตรวจสอบรหัส');
    }

    error_log((string) $error);
    finish('ข้อผิดพลาด ' . $error->getCode() . ' : ' . $error->getMessage());
}
