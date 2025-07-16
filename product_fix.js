// Fixed JavaScript for product.php - Replace the addToCart function

// Cart Functions - FIXED VERSION
function addToCart() {
    const quantity = document.getElementById('quantity').value;
    const productId = parseInt(document.querySelector('[data-product-id]')?.getAttribute('data-product-id')) || 
                     parseInt(window.location.search.match(/id=(\d+)/)?.[1]) || 1;
    
    console.log('=== ADD TO CART DEBUG ===');
    console.log('Product ID:', productId);
    console.log('Quantity:', quantity);
    console.log('Available options:', typeof productOptions !== 'undefined' ? productOptions : 'undefined');
    
    // Validate required options - Improved logic
    const allOptionGroups = document.querySelectorAll('.option-group');
    let isValid = true;
    let missingOptions = [];
    
    console.log('Found option groups:', allOptionGroups.length);
    
    allOptionGroups.forEach(group => {
        const requiredInput = group.querySelector('input[required]');
        if (requiredInput) {
            const optionTitle = group.querySelector('.option-title');
            const optionName = optionTitle ? optionTitle.textContent.trim().replace(/\s*\*$/, '') : 'Unknown Option';
            const checkedInput = group.querySelector('input[type="radio"]:checked');
            
            console.log('Checking required option:', optionName, 'Checked:', !!checkedInput);
            
            if (!checkedInput) {
                missingOptions.push(optionName);
                isValid = false;
            }
        }
    });
    
    if (!isValid) {
        console.log('Validation failed. Missing options:', missingOptions);
        alert('Vui lòng chọn: ' + missingOptions.join(', '));
        return;
    }
    
    // Get selected options - Improved logic
    const selectedOptions = {};
    allOptionGroups.forEach(group => {
        const checkedInput = group.querySelector('input[type="radio"]:checked');
        if (checkedInput) {
            const optionTitle = group.querySelector('.option-title');
            const optionName = optionTitle ? optionTitle.textContent.trim().replace(/\s*\*$/, '') : 'Unknown Option';
            selectedOptions[optionName] = checkedInput.value;
        }
    });
    
    console.log('Selected options:', selectedOptions);
    
    // Prepare data for API
    const cartData = {
        product_id: productId,
        quantity: parseInt(quantity),
        selected_options: Object.keys(selectedOptions).length > 0 ? selectedOptions : null
    };
    
    console.log('Sending cart data:', cartData);
    
    // Send request to API
    fetch('index.php?page=api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(cartData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        return response.text(); // Changed to text() for better debugging
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Parsed response:', data);
            if (data.success) {
                alert(data.message || 'Đã thêm sản phẩm vào giỏ hàng');
                // Update cart count in header if exists
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement && data.cart_count !== undefined) {
                    cartCountElement.textContent = data.cart_count;
                }
                // Optionally reload cart count from server
                updateCartCount();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể thêm vào giỏ hàng'));
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response was:', text);
            
            // Check if response contains HTML (error page)
            if (text.includes('<html') || text.includes('<!DOCTYPE')) {
                alert('Lỗi: Server trả về trang HTML thay vì JSON. Kiểm tra console để xem chi tiết.');
            } else {
                alert('Lỗi: Phản hồi từ server không hợp lệ');
            }
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Có lỗi xảy ra khi thêm vào giỏ hàng: ' + error.message);
    });
}

// Helper function to update cart count
function updateCartCount() {
    fetch('index.php?page=api/cart/get', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.cart_count !== undefined) {
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.cart_count;
            }
        }
    })
    .catch(error => {
        console.log('Could not update cart count:', error);
    });
}

// Enhanced selectOption function
function selectOption(element, optionId, optionValue, valueId) {
    console.log('Selecting option:', { optionId, optionValue, valueId });
    
    // Check if option is disabled
    const radioInput = element.querySelector('input[type="radio"]');
    if (radioInput && radioInput.disabled) {
        console.log('Option is disabled, cannot select');
        return; // Don't allow selection of disabled options
    }
    
    // Remove selected class from all options in this group
    const optionGroup = element.closest('.option-group');
    if (optionGroup) {
        optionGroup.querySelectorAll('.option-value-btn').forEach(btn => {
            btn.classList.remove('selected');
        });
    }
    
    // Add selected class to clicked option
    element.classList.add('selected');
    
    // Check the radio input
    if (radioInput) {
        radioInput.checked = true;
        console.log('Radio input checked:', radioInput.checked);
    }
    
    // Update price
    updatePrice();
}

// Add debugging for page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PRODUCT PAGE DEBUG ===');
    console.log('Product options available:', typeof productOptions !== 'undefined' ? productOptions : 'undefined');
    console.log('Option groups found:', document.querySelectorAll('.option-group').length);
    console.log('Required inputs found:', document.querySelectorAll('input[required]').length);
    
    // Add product ID to page for easier access
    const productIdFromUrl = window.location.search.match(/id=(\d+)/)?.[1];
    if (productIdFromUrl) {
        document.body.setAttribute('data-product-id', productIdFromUrl);
        console.log('Product ID from URL:', productIdFromUrl);
    }
    
    // Initial price update
    if (typeof updatePrice === 'function') {
        updatePrice();
    }
    
    // Add click handlers to option buttons if they don't have onclick
    document.querySelectorAll('.option-value-btn').forEach(btn => {
        if (!btn.getAttribute('onclick')) {
            btn.addEventListener('click', function() {
                const radioInput = this.querySelector('input[type="radio"]');
                if (radioInput && !radioInput.disabled) {
                    const optionGroup = this.closest('.option-group');
                    const optionTitle = optionGroup?.querySelector('.option-title');
                    const optionName = optionTitle?.textContent.trim().replace(/\s*\*$/, '') || 'Unknown';
                    
                    selectOption(this, optionName, radioInput.value, radioInput.getAttribute('data-value-id'));
                }
            });
        }
    });
});

// Debug function to check current state
function debugProductOptions() {
    console.log('=== DEBUG PRODUCT OPTIONS ===');
    console.log('Available productOptions:', typeof productOptions !== 'undefined' ? productOptions : 'undefined');
    console.log('Option groups:', document.querySelectorAll('.option-group').length);
    console.log('Required inputs:', document.querySelectorAll('input[required]').length);
    console.log('Checked inputs:', document.querySelectorAll('input[type="radio"]:checked').length);
    
    const selectedOptions = {};
    document.querySelectorAll('.option-group').forEach(group => {
        const checkedInput = group.querySelector('input[type="radio"]:checked');
        if (checkedInput) {
            const optionTitle = group.querySelector('.option-title');
            const optionName = optionTitle ? optionTitle.textContent.trim().replace(/\s*\*$/, '') : 'Unknown';
            selectedOptions[optionName] = checkedInput.value;
        }
    });
    
    console.log('Currently selected options:', selectedOptions);
    return selectedOptions;
}

// Make debug function available globally
window.debugProductOptions = debugProductOptions;