<?php
require_once __DIR__ . '/../controllers/ContactsController.php';
require_once __DIR__ . '/../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/SMTP.php';
require_once __DIR__ . '/../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$p = new Gcontacts();

if (isset($_REQUEST["btn_lienhe"])) {
    $tenKH = $_REQUEST["name"];
    $emailKH = $_REQUEST["email"];
    $sdt = $_REQUEST["phone"];
    $tieude = $_REQUEST["subject"];
    $noidung = $_REQUEST["message"];
    $ngaytao = date("Y-m-d");

    // Gửi email trước
    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';  // ✔ Thêm dòng này để hỗ trợ tiếng Việt
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hngocphu281003@gmail.com';          // ✅ Email của bạn
        $mail->Password = 'nnzokgligvfzsqmk';                      // ✅ App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Gửi từ đâu, đến đâu
        $mail->setFrom('hngocphu1508281003@gmail.com', 'Website Gỗ');
        $mail->addAddress('ngocphu1508281003@gmail.com', 'Admin'); // ✅ Email admin nhận
        $mail->addReplyTo($emailKH, $tenKH); // Cho phép trả lời khách

        // Nội dung mail
        $mail->isHTML(true);
        $mail->Subject = "Liên hệ mới từ $tenKH";
        $mail->Body = "
            <h3>Thông tin liên hệ:</h3>
            <p><strong>Họ tên:</strong> $tenKH</p>
            <p><strong>Email:</strong> $emailKH</p>
            <p><strong>Điện thoại:</strong> $sdt</p>
            <p><strong>Tiêu đề:</strong> $tieude</p>
            <p><strong>Nội dung:</strong><br>$noidung</p>
            <p><strong>Ngày gửi:</strong> $ngaytao</p>
        ";

        // Gửi mail
        $mail->send();

        // Nếu gửi thành công, mới lưu vào DB
        $con = $p->getthemLH($tenKH, $emailKH, $sdt, $tieude, $noidung, $ngaytao, 0);

        if ($con == true) {
            echo '
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                Swal.fire({
                    title: "Gửi thành công!",
                    text: "Chúng tôi sẽ liên hệ với bạn sớm nhất.",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    window.location.href = "index.php?page=contact";
                });
                </script>
                ';

        } else {
            echo '<script>alert("Gửi mail thành công nhưng lưu liên hệ thất bại!")</script>';
        }

    } catch (Exception $e) {
        echo '
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
            Swal.fire({
                title: "Lỗi gửi email",
                text: "'. addslashes($mail->ErrorInfo) .'",
                icon: "error",
                confirmButtonText: "Thử lại"
            });
            </script>
            ';

    }
}
?>

<style>
/* Hero Section */
.contact-hero {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 30%, #fafafa 70%, #f8f9fa 100%);
    padding: 10px 0;
    position: relative;
    overflow: hidden;
    font-family: 'Playfair Display', serif;
}

.contact-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.3" fill="%23000" opacity="0.02"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.contact-hero-content {
    position: relative;
    z-index: 2;
    padding: 4rem 0;
}

.contact-badge {
    display: inline-block;
    background: #1a1a1a;
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 2px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-bottom: 2.5rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-left: 3px solid #ff6b35;
}

.contact-title {
    font-family: 'Playfair Display', serif;
    font-size: 4.5rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 1rem;
    line-height: 1.1;
    letter-spacing: -2px;
}

.contact-subtitle {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 400;
    color: #666;
    margin-bottom: 1rem;
    letter-spacing: 1px;
}

.contact-description {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 2.5rem;
    font-weight: 300;
    line-height: 1.8;
    letter-spacing: 0.5px;
}

.contact-features {
    margin-top: 3.5rem;
}

.contact-feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
    color: #1a1a1a;
    font-weight: 400;
    letter-spacing: 0.5px;
}

.contact-feature-item i {
    font-size: 1.3rem;
    margin-right: 1rem;
    color: #ff6b35;
}

.contact-feature-item span {
    letter-spacing: 0.3px;
}

.contact-image-container {
    position: relative;
    border-radius: 0;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.contact-image {
    width: 100%;
    height: 600px;
    object-fit: cover;
}

/* Contact Section */
.contact-section {
    background: #fff;
    padding: 100px 0;
    font-family: 'Playfair Display', serif;
}

.contact-container {
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 25px 80px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    overflow: hidden;
    margin-top: -50px;
    position: relative;
    z-index: 10;
}

.contact-form-section {
    padding: 4rem;
    background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%);
}

.contact-form-title {
    font-size: 2.5rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 1rem;
    letter-spacing: -1px;
}

.contact-form-subtitle {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 3rem;
    font-weight: 300;
    line-height: 1.7;
}

.contact-form label {
    font-weight: 500;
    color: #1a1a1a;
    margin-bottom: 0.8rem;
    font-size: 1rem;
}

.contact-form .form-control {
    border-radius: 16px;
    border: 2px solid #f0f0f0;
    font-size: 1rem;
    padding: 1.2rem 1.5rem;
    margin-bottom: 1.8rem;
    transition: all 0.3s ease;
    background: #fff;
    font-family: inherit;
}

.contact-form .form-control:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
    outline: none;
}

.contact-form textarea.form-control {
    min-height: 160px;
    resize: vertical;
}

.contact-form .btn-primary {
    background: linear-gradient(135deg, #1a1a1a, #333);
    border: none;
    border-radius: 16px;
    font-size: 1.2rem;
    font-weight: 500;
    padding: 1.2rem 3rem;
    transition: all 0.3s ease;
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    position: relative;
    overflow: hidden;
    font-family: inherit;
}

.contact-form .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    transition: left 0.3s ease;
}

.contact-form .btn-primary:hover::before {
    left: 0;
}

.contact-form .btn-primary span {
    position: relative;
    z-index: 2;
}

.contact-form .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 45px rgba(0,0,0,0.2);
}

/* Contact Info Section */
.contact-info-section {
    padding: 4rem;
    background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
    color: white;
    position: relative;
}



.contact-info-content {
    position: relative;
    z-index: 2;
}

.contact-info-title {
    font-size: 2.5rem;
    font-weight: 300;
    margin-bottom: 1rem;
    letter-spacing: -1px;
}

.contact-info-subtitle {
    font-size: 1.1rem;
    color: #ccc;
    margin-bottom: 3rem;
    font-weight: 300;
    line-height: 1.7;
}

.contact-info-block {
    margin-bottom: 2rem;
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    padding: 2rem;
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.3s ease;
}

.contact-info-block:hover {
    background: rgba(255,255,255,0.1);
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.2);
}

.contact-info-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 12px 35px rgba(255, 107, 53, 0.3);
}

.contact-info-content h6 {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: white;
}

.contact-info-content p, .contact-info-content a {
    color: #ccc;
    font-size: 1.1rem;
    margin-bottom: 0;
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-info-content a:hover {
    color: #ff6b35;
}

.social-links-contact {
    display: flex;
    gap: 1.2rem;
    margin-bottom: 3rem;
}

.social-link-contact {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: white;
    border: 2px solid rgba(255,255,255,0.2);
    transition: all 0.3s ease;
    text-decoration: none;
}

.social-link-contact:hover {
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    border-color: #ff6b35;
    transform: translateY(-4px);
    box-shadow: 0 12px 35px rgba(255, 107, 53, 0.3);
    color: white;
}

/* Map Section */
.contact-map-section {
    padding: 4rem;
    background: #f8f9fa;
}

.contact-map-title {
    font-size: 2.5rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 1rem;
    letter-spacing: -1px;
    text-align: center;
}

.contact-map-subtitle {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 3rem;
    font-weight: 300;
    line-height: 1.7;
    text-align: center;
}

.contact-map-full {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    border: 1px solid #f0f0f0;
    background: #fff;
}

.contact-map-full iframe {
    width: 100%;
    height: 500px;
    border: none;
}

/* Alert Styling */
.alert {
    border-radius: 16px;
    border: none;
    padding: 1.2rem 2rem;
    margin-bottom: 2.5rem;
    font-weight: 500;
    font-size: 1rem;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border-left: 4px solid #dc3545;
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-title {
        font-size: 3rem;
    }
    
    .contact-subtitle {
        font-size: 1.2rem;
    }
    
    .contact-image {
        height: 400px;
    }
    
    .contact-hero {
        padding: 0;
    }
    
    .contact-description {
        padding-left: 12px;
        padding-right: 12px;
        text-align: left;
    }
    
    .contact-form-section,
    .contact-info-section,
    .contact-map-section {
        padding: 2rem 1.5rem;
    }
    
    .contact-info-block {
        padding: 1.5rem;
        gap: 1rem;
    }
    
    .contact-info-icon {
        width: 56px;
        height: 56px;
        font-size: 1.5rem;
    }
    
    .social-link-contact {
        width: 56px;
        height: 56px;
        font-size: 1.4rem;
    }
    
    .contact-map-full iframe {
        height: 350px;
    }
    .contact-form .btn-primary {
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
    .contact-hero,
    .contact-hero-content,
    .contact-hero .container,
    .contact-hero .row,
    .contact-hero .col-lg-6,
    .contact-content,
    .contact-badge,
    .contact-title,
    .contact-subtitle {
        margin-top: 0 !important;
        padding-top: 0.25rem;
    }
}
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container">
        <div class="contact-hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="contact-content">
                        <div class="contact-badge">Liên hệ ngay</div>
                        <h1 class="contact-title">Hãy để chúng tôi giúp bạn</h1>
                        <h2 class="contact-subtitle">EMCwood</h2>
                        <p class="contact-description">
                            Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn mọi lúc. 
                            Hãy liên hệ với chúng tôi để được tư vấn miễn phí về các sản phẩm nội thất gỗ cao cấp 
                            với chất lượng đẳng cấp quốc tế.
                        </p>
                        <div class="contact-features">
                            <div class="contact-feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Tư vấn miễn phí 24/7</span>
                            </div>
                            <div class="contact-feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Giao hàng toàn quốc</span>
                            </div>
                            <div class="contact-feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Bảo hành chính hãng</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="contact-image-container">
                        <img src="assets/uploads/product_1752552835_des_0.jpg" alt="EMCwood - Liên hệ" class="contact-image">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-container">
            <div class="row g-0">
                <div class="col-lg-6">
                    <div class="contact-form-section">
                        <h2 class="contact-form-title">Gửi tin nhắn cho chúng tôi</h2>
                        <p class="contact-form-subtitle">Điền thông tin bên dưới, chúng tôi sẽ phản hồi bạn trong thời gian sớm nhất!</p>
                        <form method="POST" action="" class="contact-form">
                        <div class="row">
                                <div class="col-md-6">
                                    <label for="name">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="phone">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                            </div>
                                <div class="col-md-6">
                                    <label for="subject">Tiêu đề</label>
                                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>">
                        </div>
                            </div>
                            <div>
                                <label for="message">Nội dung <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="6" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                            </div>
                        <button type="submit" class="btn btn-primary btn-lg" name="btn_lienhe">
                                <span><i class="fas fa-paper-plane me-2"></i>Gửi tin nhắn</span>
                        </button>
                    </form>
                </div>
            </div>
                <div class="col-lg-6">
                    <div class="contact-info-section">
                        <div class="contact-info-content">
                            <h2 class="contact-info-title">Thông tin liên hệ</h2>
                            <p class="contact-info-subtitle">Chúng tôi luôn sẵn sàng phục vụ bạn mọi lúc, mọi nơi.</p>
                            
                            <div class="contact-info-block">
                                <div class="contact-info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                                <div class="contact-info-content">
                                    <h6>Địa chỉ</h6>
                                    <p>68 Phạm Ngọc Thảo, Tây Thạnh, Tân Phú, Hồ Chí Minh</p>
                        </div>
                    </div>
                    
                            <div class="contact-info-block">
                                <div class="contact-info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                                <div class="contact-info-content">
                                    <h6>Hotline</h6>
                                    <a href="tel:0901234567">090-123-4567</a>
                        </div>
                    </div>
                    
                            <div class="contact-info-block">
                                <div class="contact-info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                                <div class="contact-info-content">
                                    <h6>Email</h6>
                                    <a href="mailto:info@thanhlygo.com">info@thanhlygo.com</a>
                        </div>
                    </div>
                    
                            <div class="contact-info-block">
                                <div class="contact-info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                                <div class="contact-info-content">
                                    <h6>Giờ làm việc</h6>
                                    <p>Thứ 2 - Chủ nhật: 8:00 - 20:00</p>
                </div>
            </div>
            
                            <div class="social-links-contact">
                                <a href="#" class="social-link-contact" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                                <a href="#" class="social-link-contact" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                                <a href="#" class="social-link-contact" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                                <a href="#" class="social-link-contact" title="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Map Section -->
        <div class="contact-map-section">
            <h2 class="contact-map-title">Vị trí của chúng tôi</h2>
            <p class="contact-map-subtitle">Ghé thăm showroom để trải nghiệm trực tiếp các sản phẩm nội thất gỗ cao cấp</p>
            <div class="contact-map-full">
                <iframe src="https://www.google.com/maps?q=68+Ph%E1%BA%A1m+Ng%E1%BB%8Dc+Th%E1%BA%A3o,+T%C3%A2y+Th%E1%BA%A1nh,+T%C3%A2n+Ph%C3%BA,+H%E1%BB%93+Ch%C3%AD+Minh&output=embed" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</section> 