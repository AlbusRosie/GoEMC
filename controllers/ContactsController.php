<?php
    require_once __DIR__ . '/../models/contacts.php';
    class Gcontacts{
        // xem toàn bộ liên hệ:
        public function getallcontact(){
            $p = new Mcontacts();
            $con = $p-> mAllContacts();
            if($con){
                if($con->rowCount() > 0){
                    return $con;
                }else{
                    return 0;
                }
            }else{
                echo "lỗi liên hệ ";
                return false;
            }
        } 
        // xem 1 liên hệ
        public function get1contact($ma){
            $p = new Mcontacts();
            $con = $p-> viewbyid_lienhe($ma);
            if($con){
                if($con -> rowCount() > 0){
                    return $con;
                }else{
                    return 0;
                }
            }else{
                echo "loi lieen he ";
                return false;
            }
        }
        //thêm liên hệ
        public function getthemLH($tenKH,$emailKH,$sdt,$tieude,$noidung,$ngaytao,$trangthai){
            $p = new Mcontacts();
            $con = $p->themLH($tenKH,$emailKH,$sdt,$tieude,$noidung,$ngaytao,$trangthai);
            if($con){
                return $con;
            }else{
                echo 'Lỗi thêm';
                return false;
            }
        }
         // cập nhật trạng thái liên hệ
        public function getupdateTT($ma){
            $p = new Mcontacts();
            $con = $p->updateTT($ma);
            if($con){
                return $con;
            }else{
                echo 'Lỗi';
                return false;
            }
        }
        // Xóa liên hệ
        public function getxoaLH($ma){
            $p = new Mcontacts();
            $con = $p->xoaLH($ma);
            if($con){
                return $con;
            }else{
                echo 'Lỗi xóa';
                return false;
            }
        }
    }
?>