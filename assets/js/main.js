// Main JavaScript for MOHO Website

document.addEventListener('DOMContentLoaded', function() {
    // Product Card Click Handler (for cards without direct links)
    const productCards = document.querySelectorAll('.product-card:not(:has(.product-link))');
    productCards.forEach(card => {
        card.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            if (productId) {
                window.location.href = `index.php?page=product&id=${productId}`;
            }
        });
    });
    
    // Search Form Handler
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('.search-input');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
    
    // Newsletter Form Handler
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (email && isValidEmail(email)) {
                // Here you would typically send the email to your server
                alert('Cảm ơn bạn đã đăng ký nhận tin!');
                emailInput.value = '';
            } else {
                alert('Vui lòng nhập email hợp lệ!');
                emailInput.focus();
            }
        });
    }
    

    
    // Mobile Menu Toggle
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });
    }
    
    // Smooth Scrolling for Anchor Links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Lazy Loading for Images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    

    
    // Utility Functions
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Add loading states to buttons
    const buttons = document.querySelectorAll('button[type="submit"]');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('loading')) {
                this.classList.add('loading');
                this.innerHTML = '<span class="loading"></span> Đang xử lý...';
                
                // Reset after 3 seconds (for demo purposes)
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.innerHTML = this.getAttribute('data-original-text') || 'Gửi';
                }, 3000);
            }
        });
    });
    
    // Store original button text
    buttons.forEach(button => {
        button.setAttribute('data-original-text', button.innerHTML);
    });
});

// Product Detail Page Specific Functions
if (window.location.href.includes('page=product')) {
    // Product Image Gallery
    let currentImageIndex = 0;
    const images = [
        // This will be populated by PHP
    ];
    
    function setMainImage(thumbnail, imageSrc) {
        document.getElementById('mainImage').src = imageSrc;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail-image').forEach(img => img.classList.remove('active'));
        thumbnail.classList.add('active');
        
        // Update current index
        currentImageIndex = images.indexOf(imageSrc);
    }
    
    function changeImage(direction) {
        if (direction === 'next') {
            currentImageIndex = (currentImageIndex + 1) % images.length;
        } else {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
        }
        
        const newImageSrc = images[currentImageIndex];
        document.getElementById('mainImage').src = newImageSrc;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail-image').forEach((img, index) => {
            if (index === currentImageIndex) {
                img.classList.add('active');
            } else {
                img.classList.remove('active');
            }
        });
    }
    
    // Quantity Controls
    function changeQuantity(delta) {
        const quantityInput = document.getElementById('quantity');
        let currentQuantity = parseInt(quantityInput.value);
        currentQuantity = Math.max(1, currentQuantity + delta);
        quantityInput.value = currentQuantity;
    }
    
    // Color Selection
    document.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            const colorName = this.getAttribute('data-color');
            document.querySelector('.selected-color strong').textContent = colorName;
        });
    });
    
    // Cart Functions
    function addToCart() {
        const quantity = document.getElementById('quantity').value;
        alert('Đã thêm ' + quantity + ' sản phẩm vào giỏ hàng!');
    }
    
    function buyNow() {
        const quantity = document.getElementById('quantity').value;
        alert('Chuyển đến trang thanh toán với ' + quantity + ' sản phẩm!');
    }
    
    // Make functions globally available
    window.setMainImage = setMainImage;
    window.changeImage = changeImage;
    window.changeQuantity = changeQuantity;
    window.addToCart = addToCart;
    window.buyNow = buyNow;
} 