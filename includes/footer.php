    </main>

    <!-- Footer -->
    <footer class="footer-main">
        <style>
            /* Modern Footer Styles */
            .footer-main {
                background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                color: #333;
                padding: 4rem 0 2rem;
                border-top: 1px solid #e9ecef;
            }
            
            /* Footer Brand */
            .footer-brand {
                display: flex;
                align-items: center;
                font-family: 'Playfair Display', serif;
                margin-bottom: 1.5rem;
            }
            
            .footer-brand .brand-text {
                font-size: 1.8rem;
                font-weight: 600;
                color: #333;
                letter-spacing: -0.5px;
            }
            
            .footer-brand .brand-dot {
                width: 6px;
                height: 6px;
                background: #ff6b35;
                border-radius: 50%;
                margin-left: 6px;
            }
            
            /* Company Description */
            .company-description {
                color: #666;
                font-size: 0.95rem;
                line-height: 1.7;
                margin-bottom: 1.5rem;
                font-weight: 400;
            }
            
            /* Social Links */
            .social-links {
                display: flex;
                gap: 0.8rem;
            }
            
            .social-link {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                background: #fff;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                color: #666;
                font-size: 0.9rem;
                transition: all 0.3s ease;
            }
            
            .social-link:hover {
                background: #ff6b35;
                color: #fff;
                border-color: #ff6b35;
                transform: translateY(-2px);
            }
            
            /* Footer Titles */
            .footer-title {
                color: #333;
                font-weight: 600;
                font-size: 0.95rem;
                margin-bottom: 1.2rem;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            /* Footer Links */
            .footer-links {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .footer-links li {
                margin-bottom: 0.6rem;
            }
            
            .footer-links a {
                color: #666;
                text-decoration: none;
                font-size: 0.9rem;
                transition: color 0.3s ease;
                font-weight: 400;
            }
            
            .footer-links a:hover {
                color: #ff6b35;
            }
            
            /* Contact Info */
            .contact-item {
                display: flex;
                align-items: center;
                margin-bottom: 0.8rem;
                color: #666;
                font-size: 0.9rem;
                font-weight: 400;
            }
            
            .contact-item i {
                width: 18px;
                color: #ff6b35;
                margin-right: 0.8rem;
                font-size: 0.9rem;
            }
            
            /* Newsletter */
            .newsletter-input-group {
                position: relative;
                display: flex;
                align-items: center;
            }
            
            .newsletter-input {
                flex: 1;
                background: #fff;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 10px 15px;
                color: #333;
                font-size: 0.9rem;
                transition: all 0.3s ease;
                font-weight: 400;
            }
            
            .newsletter-input::placeholder {
                color: #999;
            }
            
            .newsletter-input:focus {
                outline: none;
                border-color: #ff6b35;
                box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
            }
            
            .newsletter-btn {
                position: absolute;
                right: 4px;
                background: #ff6b35;
                border: none;
                border-radius: 6px;
                width: 32px;
                height: 32px;
                color: #fff;
                font-weight: 500;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .newsletter-btn:hover {
                background: #e55a2b;
                transform: scale(1.05);
            }
            
            /* Footer Bottom */
            .footer-bottom {
                border-top: 1px solid #e9ecef;
                padding-top: 1.5rem;
                margin-top: 2.5rem;
            }
            
            .copyright {
                color: #666;
                font-size: 0.85rem;
                margin: 0;
                font-weight: 400;
            }
            
            .payment-methods {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 0.8rem;
            }
            
            .payment-text {
                color: #666;
                font-size: 0.85rem;
                font-weight: 400;
            }
            
            .payment-icon {
                color: #666;
                font-size: 1.1rem;
                transition: color 0.3s ease;
            }
            
            .payment-icon:hover {
                color: #ff6b35;
            }
            
            /* Responsive */
            @media (max-width: 768px) {
                .footer-main {
                    padding: 3rem 0 1.5rem;
                }
                
                .footer-brand .brand-text {
                    font-size: 1.5rem;
                }
                
                .social-link {
                    width: 35px;
                    height: 35px;
                    font-size: 0.8rem;
                }
                
                .payment-methods {
                    justify-content: center;
                    margin-top: 1rem;
                }
            }
        </style>
        <div class="container">
            <!-- Main Footer -->
            <div class="row mb-4">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-brand">
                        <span class="brand-text">EMCwood</span>
                        <span class="brand-dot"></span>
                    </div>
                    <p class="company-description">
                        Chuyên cung cấp các sản phẩm gỗ cao cấp, nội thất gỗ tự nhiên và công nghiệp với chất lượng đẳng cấp quốc tế.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="footer-title">Liên kết</h6>
                    <ul class="footer-links">
                        <li><a href="index.php?page=about">Về chúng tôi</a></li>
                        <li><a href="index.php?page=stores">Cửa hàng</a></li>
                        <li><a href="index.php?page=cooperation">Hợp tác</a></li>
                        <li><a href="index.php?page=news">Tin tức</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="footer-title">Danh mục</h6>
                    <ul class="footer-links">
                        <li><a href="index.php?page=products&category=1">Gỗ tự nhiên</a></li>
                        <li><a href="index.php?page=products&category=2">Gỗ công nghiệp</a></li>
                        <li><a href="index.php?page=products&category=3">Nội thất gỗ</a></li>
                        <li><a href="index.php?page=products&category=4">Ván gỗ</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="footer-title">Hỗ trợ</h6>
                    <ul class="footer-links">
                        <li><a href="index.php?page=contact">Liên hệ</a></li>
                        <li><a href="index.php?page=shipping">Vận chuyển</a></li>
                        <li><a href="index.php?page=warranty">Bảo hành</a></li>
                        <li><a href="index.php?page=faq">FAQ</a></li>
                    </ul>
                </div>

                <!-- Contact & Newsletter -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="footer-title">Liên hệ</h6>
                    <div class="contact-info mb-3">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>090-123-4567</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>info@emcwood.com</span>
                        </div>
                    </div>
                    
                    <h6 class="footer-title">Đăng ký tin</h6>
                    <form class="newsletter-form">
                        <div class="newsletter-input-group">
                            <input type="email" class="newsletter-input" placeholder="Email">
                            <button class="newsletter-btn" type="submit">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bottom Footer -->
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="copyright">
                            © 2024 EMCwood. Tất cả quyền được bảo lưu.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="payment-methods">
                            <span class="payment-text">Thanh toán:</span>
                            <i class="fab fa-cc-visa payment-icon"></i>
                            <i class="fab fa-cc-mastercard payment-icon"></i>
                            <i class="fab fa-cc-paypal payment-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html> 