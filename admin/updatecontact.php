<?php
session_start();
// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once(__DIR__ . '/../controllers/ContactsController.php');
require_once(__DIR__ . '/../phpmailer/PHPMailer.php');
require_once(__DIR__ . '/../phpmailer/SMTP.php');
require_once(__DIR__ . '/../phpmailer/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$p = new Gcontacts();
$ma = $_GET['id'] ?? 0;

if ($ma) {
    // Lấy thông tin liên hệ trước khi cập nhật
    $contactInfo = $p->get1contact($ma);
    $tenKH = $emailKH = "";

    if ($contactInfo && $contactInfo->rowCount() > 0) {
        $row = $contactInfo->fetch();
        $tenKH = $row['name'];
        $emailKH = $row['email'];
    }

    // Cập nhật trạng thái đã duyệt
    $updated = $p->getupdateTT($ma);

    if ($updated && !empty($emailKH)) {
        $mail = new PHPMailer(true);

        try {
            // Cấu hình SMTP Gmail
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'hngocphu281003@gmail.com';                // Gmail của bạn
            $mail->Password = 'nnzokgligvfzsqmk';                        // App Password viết liền không dấu cách
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('hngocphu281003@gmail.com', mb_encode_mimeheader('Hệ thống hỗ trợ', 'UTF-8'));
            $mail->addAddress($emailKH, $tenKH);

            $mail->isHTML(true);
            $mail->Subject = mb_encode_mimeheader('Cảm ơn bạn đã liên hệ với chúng tôi', 'UTF-8');
            $mail->Body    = "
                <p>Xin chào <strong>$tenKH</strong>,</p>
                <p>Chúng tôi đã nhận được thông tin từ bạn và rất cảm ơn sự quan tâm của bạn.</p>
                <p>Chúng tôi sẽ phản hồi trong thời gian sớm nhất có thể.</p>
                <p>Trân trọng,<br>Đội ngũ hỗ trợ EMC</p>
            ";

            $mail->send();
            echo '<script>alert("✅ Cập nhật và gửi email thành công!"); window.location.href = "contacts.php";</script>';
            exit;
        } catch (Exception $e) {
            echo '<script>alert("✅ Cập nhật thành công nhưng gửi email thất bại: ' . $mail->ErrorInfo . '"); window.location.href = "contacts.php";</script>';
            exit;
        }
    } else {
        echo '<script>alert("❌ Cập nhật thất bại hoặc không có email."); window.location.href = "contacts.php";</script>';
        exit;
    }
} else {
    echo '<script>alert("❌ Không tìm thấy liên hệ!"); window.location.href = "contacts.php";</script> ';
    exit;
}
