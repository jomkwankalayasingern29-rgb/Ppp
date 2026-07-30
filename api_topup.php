<?php
// ตั้งค่า Header ให้รองรับ JSON และ CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

error_reporting(0);

// เบอร์โทรศัพท์ TrueMoney Wallet ที่จะรับเงิน
$my_phone = '0827525423';

// เรียกใช้ Library
if (!file_exists('./src/Voucher.php')) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบไฟล์ src/Voucher.php บน Server']);
    exit;
}

require_once('./src/Voucher.php');
use M4h45amu7x\Voucher;

// รับข้อมูลจาก Fetch API หน้าเว็บ
$input = json_decode(file_get_contents('php://input'), true);
$voucher_url = isset($input['giftLink']) ? trim($input['giftLink']) : '';

if (empty($voucher_url)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกลิงก์ซองอั่งเปา']);
    exit;
}

try {
    // ส่งเบอร์รับเงินและลิงก์ซองไปประมวลผล
    $voucher = new Voucher($my_phone, $voucher_url);
    
    // สั่งดึงเงินเข้าเบอร์จริงทันที
    $result = $voucher->redeem();

    // เช็กผลลัพธ์จาก TrueMoney
    if (isset($result['status']['code']) && $result['status']['code'] == 'SUCCESS') {
        // ดึงจำนวนเงินจริงที่ดึงได้จากซอง
        $amount = floatval($result['data']['my_ticket']['amount_baht']);

        echo json_encode([
            'success' => true,
            'amount' => $amount,
            'message' => "เติมเงินสำเร็จ! ได้รับเงินจริง {$amount} บาท"
        ]);
    } else {
        // แจ้งข้อผิดพลาดจริงจาก TrueMoney (เช่น ซองหมด, ซองถูกใช้ไปแล้ว)
        $msg = isset($result['status']['message']) ? $result['status']['message'] : 'ซองอั่งเปาไม่ถูกต้องหรือถูกใช้งานไปแล้ว';
        echo json_encode(['success' => false, 'message' => $msg]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อระบบเติมเงิน']);
}
