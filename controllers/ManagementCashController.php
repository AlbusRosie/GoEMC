<?php
require_once __DIR__ . '/../models/Management_cash.php';

class GQLcash {

    // Lấy danh sách các đơn hàng đã completed
    public function getallcash() {
        $p = new MQLcash();
        $con = $p->Mallcash();
        if ($con && $con->rowCount() > 0) {
            return $con;
        } else {
            return false;
        }
    }
    // Lấy toàn bộ đơn hàng (dù trạng thái nào)
    public function getAllOrders() {
        $p = new MQLcash();
        $con = $p->getAllOrders();
        if ($con && $con->rowCount() > 0) {
            return $con;
        } else {
            return false;
        }
    }
    // Lấy thông tin đơn hàng theo ID
    public function getOrderDetail($id) {
        $p = new MQLcash();
        return $p->getOrderById($id);
    }

    // Lấy danh sách sản phẩm trong đơn hàng
    public function getOrderItems($id) {
        $p = new MQLcash();
        return $p->getOrderItems($id);
    }
    // chỉnh sửa trạng thái đơn hàng
    public function updateStatus($orderId, $status) {
        $p = new MQLcash();
        return $p->updateOrderStatus($orderId, $status);
    }

}
?>
