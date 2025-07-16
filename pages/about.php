<style>
.about-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 30%, #fafafa 70%, #f8f9fa 100%);
    padding: 10px 0;
    position: relative;
    overflow: hidden;
    font-family: 'Playfair Display', serif;
}

.about-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.3" fill="%23000" opacity="0.02"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.about-hero {
    position: relative;
    z-index: 2;
    padding: 4rem 0;
}

.about-badge {
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

.about-title {
    font-family: 'Playfair Display', serif;
    font-size: 4.5rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 1rem;
    line-height: 1.1;
    letter-spacing: -2px;
}

.about-subtitle {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 400;
    color: #666;
    margin-bottom: 1rem;
    letter-spacing: 1px;
}

.about-description {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 2.5rem;
    font-weight: 300;
    line-height: 1.8;
    letter-spacing: 0.5px;
}

.about-features {
    margin-top: 3.5rem;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
    color: #1a1a1a;
    font-weight: 400;
    letter-spacing: 0.5px;
}

.feature-item i {
    font-size: 1.3rem;
    margin-right: 1rem;
    color: #ff6b35;
}

.feature-item span {
    letter-spacing: 0.3px;
}

.about-image-container {
    position: relative;
    border-radius: 0;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.about-image {
    width: 100%;
    height: 600px;
    object-fit: cover;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 1rem;
    letter-spacing: -1px;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 3rem;
    font-weight: 300;
    line-height: 1.8;
    letter-spacing: 0.5px;
}

.mission-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    padding: 2.5rem 2rem;
    text-align: center;
    height: 100%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.mission-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.mission-icon {
    width: 80px;
    height: 80px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    border: 1px solid #e0e0e0;
    transition: background-color 0.3s ease;
}

.mission-card:hover .mission-icon {
    background: #1a1a1a;
}

.mission-icon i {
    font-size: 2rem;
    color: #1a1a1a;
    transition: color 0.3s ease;
}

.mission-card:hover .mission-icon i {
    color: white;
}

.mission-title {
    font-size: 1.3rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 1rem;
    letter-spacing: 0.5px;
}

.mission-text {
    color: #666;
    line-height: 1.7;
    font-weight: 300;
    letter-spacing: 0.3px;
}

.values-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    padding: 3rem 2rem;
    margin-bottom: 4rem;
}

.value-item {
    text-align: center;
    margin-bottom: 2rem;
}

.value-icon {
    width: 100px;
    height: 100px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
}

.value-item:hover .value-icon {
    background: #1a1a1a;
    transform: scale(1.05);
}

.value-icon i {
    font-size: 2.5rem;
    color: #1a1a1a;
    transition: color 0.3s ease;
}

.value-item:hover .value-icon i {
    color: white;
}

.value-title {
    font-size: 1.2rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
}

.value-text {
    color: #666;
    font-size: 0.9rem;
    font-weight: 300;
    letter-spacing: 0.3px;
}

.services-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 3rem 2rem;
    margin-bottom: 4rem;
}

.service-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.service-image {
    height: 200px;
    overflow: hidden;
}

.service-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.service-card:hover .service-image img {
    transform: scale(1.05);
}

.service-content {
    padding: 1.5rem;
}

.service-title {
    font-size: 1.2rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 1rem;
    letter-spacing: 0.5px;
}

.service-text {
    color: #666;
    line-height: 1.7;
    font-weight: 300;
    letter-spacing: 0.3px;
    font-size: 0.9rem;
}

.why-choose-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    padding: 3rem 2rem;
    margin-bottom: 4rem;
}

.why-item {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.why-icon {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e0e0e0;
    flex-shrink: 0;
    transition: background-color 0.3s ease;
}

.why-item:hover .why-icon {
    background: #1a1a1a;
}

.why-icon i {
    font-size: 1.2rem;
    color: #1a1a1a;
    transition: color 0.3s ease;
}

.why-item:hover .why-icon i {
    color: white;
}

.why-content h6 {
    font-size: 1.1rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
}

.why-content p {
    color: #666;
    font-size: 0.9rem;
    font-weight: 300;
    letter-spacing: 0.3px;
    margin: 0;
}

.cta-section {
    background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
    border-radius: 12px;
    padding: 3rem 2rem;
    text-align: center;
    color: white;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 300;
    margin-bottom: 1rem;
    letter-spacing: -1px;
}

.cta-text {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    font-weight: 300;
    line-height: 1.8;
    letter-spacing: 0.5px;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary-custom {
    background: #ff6b35;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    padding: 0.8rem 2rem;
    transition: background 0.3s;
    text-decoration: none;
    color: white;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary-custom:hover {
    background: #e55a2b;
    color: white;
    text-decoration: none;
}

.btn-outline-custom {
    background: transparent;
    border: 2px solid white;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    padding: 0.8rem 2rem;
    transition: all 0.3s;
    text-decoration: none;
    color: white;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-outline-custom:hover {
    background: white;
    color: #1a1a1a;
    text-decoration: none;
}

@media (max-width: 768px) {
    .about-title {
        font-size: 3rem;
    }
    
    .about-subtitle {
        font-size: 1.2rem;
    }
    
    .about-image {
        height: 400px;
    }
    
    .about-section {
        padding: 6rem 0;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .cta-title {
        font-size: 2rem;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .mission-card,
    .values-section,
    .services-section,
    .why-choose-section,
    .cta-section {
        padding: 2rem 1.5rem;
    }
}
</style>

<section class="about-section">
    <div class="container">
    <!-- Hero Section -->
        <div class="about-hero">
            <div class="row align-items-center">
        <div class="col-lg-6">
                    <div class="about-content">
                        <div class="about-badge">Thương hiệu hàng đầu</div>
                        <h1 class="about-title">Crafted To IMPRESS</h1>
                        <h2 class="about-subtitle">EMCwood</h2>
                        <p class="about-description">
                            Thương hiệu nội thất gỗ cao cấp hàng đầu Việt Nam. 
                            Chúng tôi chuyên cung cấp các sản phẩm nội thất gỗ tự nhiên và công nghiệp với thiết kế hiện đại, 
                            chất lượng đẳng cấp quốc tế và dịch vụ tận tâm.
                        </p>
                        <div class="about-features">
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Chất lượng gỗ tự nhiên 100%</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Thiết kế độc quyền, hiện đại</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Bảo hành chính hãng 12 tháng</span>
                            </div>
                        </div>
                    </div>
        </div>
        <div class="col-lg-6">
                    <div class="about-image-container">
                        <img src="assets/uploads/product_1752552835_des_0.jpg" alt="EMCwood - Nội thất gỗ cao cấp" class="about-image">
                    </div>
                </div>
            </div>
    </div>

    <!-- Mission & Vision -->
    <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2 class="section-title">Sứ Mệnh & Tầm Nhìn</h2>
                <p class="section-subtitle">Định hướng phát triển của chúng tôi</p>
            </div>
        <div class="col-md-6 mb-4">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h4 class="mission-title">Sứ Mệnh</h4>
                    <p class="mission-text">
                        Cung cấp các sản phẩm gỗ chất lượng cao với giá cả hợp lý, 
                        đáp ứng nhu cầu đa dạng của khách hàng và góp phần bảo vệ môi trường 
                        thông qua việc tái sử dụng gỗ hiệu quả.
                    </p>
            </div>
        </div>
        <div class="col-md-6 mb-4">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h4 class="mission-title">Tầm Nhìn</h4>
                    <p class="mission-text">
                        Trở thành đơn vị hàng đầu trong lĩnh vực thanh lý gỗ tại Việt Nam, 
                        được khách hàng tin tưởng và lựa chọn, đồng thời góp phần phát triển 
                        bền vững ngành gỗ nước nhà.
                    </p>
            </div>
        </div>
    </div>

    <!-- Core Values -->
        <div class="values-section">
            <div class="row">
        <div class="col-12 text-center mb-4">
                    <h2 class="section-title">Giá Trị Cốt Lõi</h2>
                    <p class="section-subtitle">Những nguyên tắc chúng tôi luôn tuân thủ</p>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5 class="value-title">Uy Tín</h5>
                        <p class="value-text">Cam kết chất lượng và dịch vụ tốt nhất</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5 class="value-title">Tận Tâm</h5>
                        <p class="value-text">Luôn đặt lợi ích khách hàng lên hàng đầu</p>
                    </div>
        </div>
                <div class="col-md-3 col-sm-6">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-leaf"></i>
            </div>
                        <h5 class="value-title">Bền Vững</h5>
                        <p class="value-text">Góp phần bảo vệ môi trường</p>
        </div>
            </div>
                <div class="col-md-3 col-sm-6">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-rocket"></i>
        </div>
                        <h5 class="value-title">Đổi Mới</h5>
                        <p class="value-text">Không ngừng cải tiến và phát triển</p>
            </div>
        </div>
        </div>
    </div>

    <!-- Services -->
        <div class="services-section">
            <div class="row">
        <div class="col-12 text-center mb-4">
                    <h2 class="section-title">Dịch Vụ Của Chúng Tôi</h2>
                    <p class="section-subtitle">Đa dạng các dịch vụ đáp ứng mọi nhu cầu</p>
        </div>
        <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="assets/uploads/product_1752552835_des_1.jpg" alt="Thanh lý gỗ">
                        </div>
                        <div class="service-content">
                            <h5 class="service-title">Thanh Lý Gỗ</h5>
                            <p class="service-text">
                        Chuyên thanh lý các loại gỗ tự nhiên, gỗ công nghiệp với giá tốt nhất thị trường. 
                        Đảm bảo chất lượng và nguồn gốc rõ ràng.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="assets/uploads/product_1752552835_des_2.jpg" alt="Nội thất gỗ">
                        </div>
                        <div class="service-content">
                            <h5 class="service-title">Nội Thất Gỗ</h5>
                            <p class="service-text">
                        Cung cấp đa dạng nội thất gỗ như bàn, ghế, tủ, giường với thiết kế đẹp, 
                        chất lượng cao và giá cả hợp lý.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="assets/uploads/product_1752552835_des_3.jpg" alt="Tư vấn thiết kế">
                        </div>
                        <div class="service-content">
                            <h5 class="service-title">Tư Vấn Thiết Kế</h5>
                            <p class="service-text">
                        Đội ngũ chuyên gia tư vấn thiết kế nội thất, giúp khách hàng lựa chọn 
                        sản phẩm phù hợp với không gian và ngân sách.
                    </p>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Choose Us -->
        <div class="why-choose-section">
            <div class="row">
        <div class="col-12 text-center mb-4">
                    <h2 class="section-title">Tại Sao Chọn Chúng Tôi?</h2>
                    <p class="section-subtitle">Những lý do khiến khách hàng tin tưởng</p>
        </div>
                <div class="col-md-6">
                    <div class="why-item">
                        <div class="why-icon">
                        <i class="fas fa-check"></i>
                        </div>
                        <div class="why-content">
                            <h6>Kinh nghiệm hơn 10 năm</h6>
                            <p>Với hơn 10 năm hoạt động trong lĩnh vực gỗ, chúng tôi hiểu rõ nhu cầu của khách hàng.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fas fa-check"></i>
                </div>
                        <div class="why-content">
                            <h6>Chất lượng đảm bảo</h6>
                            <p>Tất cả sản phẩm đều được kiểm định chất lượng nghiêm ngặt trước khi bán.</p>
        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fas fa-check"></i>
                </div>
                        <div class="why-content">
                            <h6>Giá cả cạnh tranh</h6>
                            <p>Cam kết giá tốt nhất thị trường với chất lượng tương xứng.</p>
        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fas fa-check"></i>
                </div>
                        <div class="why-content">
                            <h6>Dịch vụ chuyên nghiệp</h6>
                            <p>Đội ngũ nhân viên tận tâm, chuyên nghiệp, sẵn sàng hỗ trợ 24/7.</p>
        </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact CTA -->
        <div class="cta-section">
            <h3 class="cta-title">Bạn Cần Tư Vấn?</h3>
            <p class="cta-text">
                        Hãy liên hệ với chúng tôi ngay hôm nay để được tư vấn miễn phí 
                        và nhận báo giá tốt nhất!
                    </p>
            <div class="cta-buttons">
                <a href="index.php?page=contact" class="btn-primary-custom">
                    <i class="fas fa-phone"></i>
                    Liên hệ ngay
                </a>
                <a href="tel:0901234567" class="btn-outline-custom">
                    <i class="fas fa-phone"></i>
                    090-123-4567
                </a>
            </div>
        </div>
    </div>
</section> 