<?php
// Debug file để kiểm tra product options
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/ProductOption.php';

// Khởi tạo kết nối database
$db = new Database();
$conn = $db->getConnection();

// Khởi tạo model
$productModel = new Product($conn);
$productOptionModel = new ProductOption($conn);

// Lấy ID sản phẩm từ URL (hoặc hardcode để test)
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1; // Default product ID = 1

echo "<h2>Debug Product Options for Product ID: $product_id</h2>";

// Lấy thông tin sản phẩm
$product = $productModel->getById($product_id);
if (!$product) {
    echo "<p style='color: red;'>Product not found!</p>";
    exit;
}

echo "<h3>Product Info:</h3>";
echo "<pre>" . print_r($product, true) . "</pre>";

// Lấy product options
$productOptions = $productOptionModel->getByProductId($product_id);

echo "<h3>Product Options:</h3>";
if (empty($productOptions)) {
    echo "<p style='color: orange;'>No product options found for this product.</p>";
    
    // Tạo sample options để test
    echo "<h4>Creating sample options...</h4>";
    
    // Tạo option "Kích thước"
    $sizeOptionId = $productOptionModel->create([
        'product_id' => $product_id,
        'name' => 'Kích thước'
    ]);
    
    if ($sizeOptionId) {
        echo "<p>Created size option with ID: $sizeOptionId</p>";
        
        // Tạo values cho size option
        $sizeValues = [
            ['value' => '4x8 feet', 'stock_quantity' => 10],
            ['value' => '4x6 feet', 'stock_quantity' => 5],
            ['value' => '6x8 feet', 'stock_quantity' => 3]
        ];
        
        foreach ($sizeValues as $valueData) {
            $valueData['option_id'] = $sizeOptionId;
            $valueId = $productOptionModel->createOptionValue($valueData);
            echo "<p>Created size value: {$valueData['value']} with ID: $valueId</p>";
        }
    }
    
    // Tạo option "Màu sắc"
    $colorOptionId = $productOptionModel->create([
        'product_id' => $product_id,
        'name' => 'Màu sắc'
    ]);
    
    if ($colorOptionId) {
        echo "<p>Created color option with ID: $colorOptionId</p>";
        
        // Tạo values cho color option
        $colorValues = [
            ['value' => 'Nâu tự nhiên', 'stock_quantity' => 8],
            ['value' => 'Nâu đậm', 'stock_quantity' => 6],
            ['value' => 'Trắng', 'stock_quantity' => 4]
        ];
        
        foreach ($colorValues as $valueData) {
            $valueData['option_id'] = $colorOptionId;
            $valueId = $productOptionModel->createOptionValue($valueData);
            echo "<p>Created color value: {$valueData['value']} with ID: $valueId</p>";
        }
    }
    
    // Lấy lại options sau khi tạo
    $productOptions = $productOptionModel->getByProductId($product_id);
    echo "<h4>Options after creation:</h4>";
}

echo "<pre>" . print_r($productOptions, true) . "</pre>";

// Test JavaScript data
echo "<h3>JavaScript Data:</h3>";
echo "<script>";
echo "const productOptions = " . json_encode($productOptions) . ";";
echo "console.log('Product Options:', productOptions);";
echo "</script>";

echo "<h3>HTML Options Rendering Test:</h3>";
if (!empty($productOptions)) {
    foreach ($productOptions as $option) {
        echo "<div class='option-group'>";
        echo "<h4>{$option['name']}</h4>";
        
        foreach ($option['values'] as $value) {
            $disabled = ($value['stock_quantity'] <= 0) ? 'disabled' : '';
            $disabledClass = ($value['stock_quantity'] <= 0) ? 'disabled' : '';
            
            echo "<label class='option-value-btn $disabledClass' style='display: inline-block; padding: 8px 12px; border: 1px solid #ddd; margin: 5px; cursor: pointer;'>";
            echo "<input type='radio' name='option_{$option['id']}' value='{$value['value']}' class='d-none' data-value-id='{$value['id']}' $disabled>";
            echo htmlspecialchars($value['value']);
            if ($value['stock_quantity'] <= 0) {
                echo " <span style='color: red;'>(Hết hàng)</span>";
            } else {
                echo " <span style='color: green;'>({$value['stock_quantity']} có sẵn)</span>";
            }
            echo "</label>";
        }
        
        echo "</div><br>";
    }
} else {
    echo "<p>No options to render.</p>";
}

// Test cart API endpoint
echo "<h3>Test Cart API:</h3>";
echo "<button onclick='testAddToCart()'>Test Add to Cart</button>";
echo "<div id='test-result'></div>";

echo "<script>
function testAddToCart() {
    const selectedOptions = {};
    
    // Get selected options
    document.querySelectorAll('input[type=\"radio\"]:checked').forEach(input => {
        const optionName = input.name.replace('option_', '');
        selectedOptions[optionName] = input.value;
    });
    
    const cartData = {
        product_id: $product_id,
        quantity: 1,
        selected_options: Object.keys(selectedOptions).length > 0 ? selectedOptions : null
    };
    
    console.log('Testing cart data:', cartData);
    
    fetch('index.php?page=api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(cartData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Cart API response:', data);
        document.getElementById('test-result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('test-result').innerHTML = '<p style=\"color: red;\">Error: ' + error.message + '</p>';
    });
}

// Auto-select first option for each group for testing
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.option-group').forEach(group => {
        const firstRadio = group.querySelector('input[type=\"radio\"]:not([disabled])');
        if (firstRadio) {
            firstRadio.checked = true;
        }
    });
});
</script>";

echo "<style>
.option-value-btn {
    background: white;
    border: 1px solid #ddd;
    padding: 8px 12px;
    margin: 5px;
    cursor: pointer;
    display: inline-block;
}
.option-value-btn:hover {
    background: #f0f0f0;
}
.option-value-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
input[type='radio']:checked + span {
    background: #007bff;
    color: white;
}
</style>";
?>