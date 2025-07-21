<?php
    require_once __DIR__ . '/../config/database.php';
    class MQLcash{
        // quản lý toàn bộ đơn hàng với trạng thái completed
        public function Mallcash() {
            $p = new Database();
            $con = $p->getConnection();
            if ($con) {
                $str = "SELECT 
                            o.id AS order_id,
                            o.created_at,
                            o.payment_status,
                            o.payment_method,
                            SUM(od.price * od.quantity) AS total_amount
                        FROM orders o
                        JOIN order_details od ON o.id = od.order_id
                        WHERE o.status = 'completed'
                        GROUP BY o.id, o.created_at, o.payment_status, o.payment_method
                        ORDER BY o.created_at DESC;";
                $tbl = $con->query($str);
                return $tbl ?: false;
            } else {
                echo "<script>alert('Kết nối CSDL thất bại')</script>";
                return false;
            }
        }
        // Lấy thông tin chi tiết của một đơn hàng theo ID
        public function getOrderById($orderId) {
            $db = new Database();
            $con = $db->getConnection();
            $stmt = $con->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            return $stmt->fetch();
        }

        // Lấy danh sách sản phẩm trong đơn hàng
        public function getOrderItems($orderId) {
            $db = new Database();
            $con = $db->getConnection();
            $stmt = $con->prepare("
                SELECT od.*, p.image_
                FROM order_details od
                JOIN products p ON od.product_id = p.id
                WHERE od.order_id = ?
            ");
            $stmt->execute([$orderId]);
            return $stmt->fetchAll();
        }
        // ✅ Lấy toàn bộ đơn hàng (không lọc, có trạng thái & phương thức thanh toán)
        public function getAllOrders() {
            $db = new Database();
            $con = $db->getConnection();

            if ($con) {
                $sql = "
                    SELECT 
                        o.id AS order_id,
                        o.created_at,
                        o.status,
                        o.payment_method,
                        SUM(od.quantity) AS total_quantity,
                        SUM(od.quantity * od.price) AS total_price
                    FROM orders o
                    JOIN order_details od ON o.id = od.order_id
                    GROUP BY o.id, o.created_at, o.status, o.payment_method
                    ORDER BY o.created_at DESC
                ";

                $result = $con->query($sql);
                return $result ?: false;
            } else {
                echo "<script>alert('Kết nối CSDL thất bại')</script>";
                return false;
            }
        }

        // chỉnh sửa trạng thái đơn hàng: 
        public function updateOrderStatus($orderId, $status) {
            $db = new Database();
            $con = $db->getConnection();

            if ($con) {
                $stmt = $con->prepare("UPDATE orders SET status = :status WHERE id = :id");
                return $stmt->execute([
                    ':status' => $status,
                    ':id' => $orderId
                ]);
            } else {
                return false;
            }
        }


    }


?>