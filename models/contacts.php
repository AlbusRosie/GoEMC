<?php
    require_once __DIR__ . '/../config/database.php';
    class Mcontacts{
        // xem toàn bộ liên hệ
        public function mAllContacts(){
            $p = new Database();
            $con = $p->getConnection();
            if($con){
                $str= "SELECT * FROM contact";
                $tbl = $con -> query($str);
                if($tbl){
                    return $tbl;
                }else{
                    echo "Loi truy van";
                    return false;
                }
            }else{
                echo " <script> alert('ket noi CSDL that bai') </script> ";
                return false;
            }
        }
        //xem 1 liên hệ:
        public function viewbyid_lienhe($ma){
            $p = new Database();
            $con = $p->getConnection();
            if($con){
                $str = "SELECT * from contact where id = $ma";
                $tbl = $con ->query($str);
                if($tbl){
                    return $tbl;
                }else{
                    echo 'Looi  truy van';
                    return false;
                }
            }else{
                echo 'Looi KET NOI CSDL';
                return false;
            }
        }
        // thêm liên hệ
        public function themLH($tenKH,$emailKH,$sdt,$tieude,$noidung,$ngaytao,$trangthai){
            $p = new Database();
            $con = $p->getConnection();
            if($con){
                $str ="INSERT INTO contact(name, email, phone,title, content, date,status)
                       VALUES ('$tenKH', '$emailKH', '$sdt','$tieude','$noidung', CURDATE(), 0)";
                $tbl = $con ->query($str);
                if($tbl){
                    return $tbl;
                }else{
                    echo 'Lỗi truy vấn';
                    return false;
                }
            }else{
                echo 'Loi Ket noi CSDL';
                return false;
            }
        }
        // cập nhật trạng thái contact
        public function updateTT($ma){
            $p = new Database();
            $con = $p->getConnection();
            if($con){
                $str ="UPDATE contact set status = 1 where id =$ma";
                $tbl = $con ->query($str);
                if($tbl){
                    return $tbl;
                }else{
                    echo 'Loi  truy van';
                    return false;
                }
            }else{
                echo 'Loi KET NOI CSDL';
                return false;
            }
        }
        // xóa liên hệ:
        public function xoaLH($ma){
            $p = new Database();
            $con = $p->getConnection();
            if($con){
                $str ="DELETE from contact where id =$ma";
                $tbl = $con ->query($str);
                if($tbl){
                    return $tbl;
                }else{
                    echo 'Loi  truy van';
                    return false;
                }
            }else{
                echo 'Loi Ket noi CSDL';
                return false;
            }
        }
    }


?>