<?php
session_start();
// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
    include_once(__DIR__ . '/../controllers/ContactsController.php');
    $p = new Gcontacts();
    $ma = $_GET["id"];
    if($ma){
        $con = $p->getxoaLH($ma);
        if($con){
            echo ' <script>alert("Xóa thành công")</script>';
            echo ' <script>window.location.href="contacts.php"</script>';
        }else{
            echo '<script>alert("Xóa thất bại")</script>';
        }
    }else{
        echo 'Không tồn tại mã';
    }

?>