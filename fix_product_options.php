<?php
/**
 * File sửa lỗi cho chức năng product options
 * 
 * Các vấn đề đã phát hiện:
 * 1. HTML không có thuộc tính required cho input radio khi option is_required = 1
 * 2. JavaScript validation logic có thể không hoạt động đúng
 * 3. Cần kiểm tra xem có dữ liệu product options trong database không
 */

echo "<h2>Phân tích vấn đề Product Options</h2>";

echo "<h3>Vấn đề 1: HTML Input Required</h3>";
echo "<p>Trong file pages/product.php, dòng 1208:</p>";
echo "<pre>
&lt;input type=\"radio\" name=\"option_&lt;?php echo \$option['id']; ?&gt;\" 
       value=\"&lt;?php echo htmlspecialchars(\$value['value']); ?&gt;\" 
       class=\"d-none\"
       data-stock=\"&lt;?php echo \$value['stock_quantity']; ?&gt;\"
       data-value-id=\"&lt;?php echo \$value['id']; ?&gt;\"
       &lt;?php echo (\$value['stock_quantity'] &lt;= 0) ? 'disabled' : ''; ?&gt;
       &lt;?php echo (\$option['is_required']) ? 'required' : ''; ?&gt;&gt;
</pre>";
echo "<p style='color: green;'>✓ Đã có thuộc tính required - OK</p>";

echo "<h3>Vấn đề 2: JavaScript Validation</h3>";
echo "<p>Trong function addToCart(), logic validation có thể cải thiện:</p>";
echo "<pre>
// Hiện tại:
const requiredOptions = document.querySelectorAll('input[required]');

// Nên thay thành:
const requiredOptions = document.querySelectorAll('input[type=\"radio\"][required]');
</pre>";

echo "<h3>Vấn đề 3: Database Data</h3>";
echo "<p>Cần kiểm tra xem có dữ liệu product options trong database không.</p>";

// Kiểm tra database
require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Kiểm tra có bao nhiêu product options
    $stmt = $conn->query("SELECT COUNT(*) as count FROM product_options");
    $optionCount = $stmt->fetch()['count'];
    
    echo "<p>Số lượng product options trong database: <strong>$optionCount</strong></p>";
    
    if ($optionCount == 0) {
        echo "<p style='color: red;'>⚠️ Không có product options nào trong database!</p>";
        echo "<p>Cần tạo dữ liệu mẫu.</p>";
    } else {
        // Hiển thị một số options mẫu
        $stmt = $conn->query("
            SELECT po.*, p.name as product_name 
            FROM product_options po 
            JOIN products p ON po.product_id = p.id 
            LIMIT 5
        ");
        $options = $stmt->fetchAll();
        
        echo "<h4>Một số product options mẫu:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Product</th><th>Option Name</th><th>Required</th></tr>";
        foreach ($options as $option) {
            echo "<tr>";
            echo "<td>{$option['id']}</td>";
            echo "<td>{$option['product_name']}</td>";
            echo "<td>{$option['name']}</td>";
            echo "<td>" . ($option['is_required'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi database: " . $e->getMessage() . "</p>";
}

echo "<h3>Giải pháp đề xuất:</h3>";
echo "<ol>";
echo "<li><strong>Sửa JavaScript validation</strong> - Cải thiện logic kiểm tra required options</li>";
echo "<li><strong>Thêm debug logging</strong> - Thêm console.log để debug</li>";
echo "<li><strong>Kiểm tra API endpoint</strong> - Đảm bảo API cart/add hoạt động đúng</li>";
echo "<li><strong>Tạo dữ liệu mẫu</strong> - Nếu chưa có product options</li>";
echo "</ol>";

echo "<h3>Code sửa lỗi:</h3>";
echo "<h4>1. Sửa JavaScript trong product.php:</h4>";
echo "<pre>
// Thay thế function addToCart() hiện tại bằng:
function addToCart() {
    const quantity = document.getElementById('quantity').value;
    const productId = " . (isset($_GET['id']) ? $_GET['id'] : 1) . ";
    
    console.log('=== ADD TO CART DEBUG ===');
    console.log('Product ID:', productId);
    console.log('Quantity:', quantity);
    console.log('Available options:', productOptions);
    
    // Validate required options - Cải thiện logic
    const allOptionGroups = document.querySelectorAll('.option-group');
    let isValid = true;
    let missingOptions = [];
    
    console.log('Found option groups:', allOptionGroups.length);
    
    allOptionGroups.forEach(group => {
        const requiredInput = group.querySelector('input[required]');
        if (requiredInput) {
            const optionName = group.querySelector('.option-title').textContent.trim().replace(/\s*\*$/, '');
            const checkedInput = group.querySelector('input[type=\"radio\"]:checked');
            
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
    
    // Get selected options - Cải thiện logic
    const selectedOptions = {};
    allOptionGroups.forEach(group => {
        const checkedInput = group.querySelector('input[type=\"radio\"]:checked');
        if (checkedInput) {
            const optionName = group.querySelector('.option-title').textContent.trim().replace(/\s*\*$/, '');
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
        console.log('Response headers:', response.headers);
        return response.text(); // Đổi thành text() để debug
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Parsed response:', data);
            if (data.success) {
                alert(data.message);
                // Update cart count in header if exists
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.cart_count;
                }
            } else {
                alert('Lỗi: ' + data.message);
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response was:', text);
            alert('Lỗi: Phản hồi từ server không hợp lệ');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Có lỗi xảy ra khi thêm vào giỏ hàng: ' + error.message);
    });
}
</pre>";

echo "<h4>2. Kiểm tra API endpoint:</h4>";
echo "<p>Truy cập: <a href='debug_product_options.php?id=1' target='_blank'>debug_product_options.php?id=1</a></p>";

echo "<h4>3. Tạo dữ liệu mẫu nếu cần:</h4>";
echo "<p>Chạy script tạo dữ liệu mẫu trong debug_product_options.php</p>";
?>