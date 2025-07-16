<?php
/**
 * Test API Cart Add Endpoint
 */
session_start();

echo "<h2>Test API Cart Add Endpoint</h2>";

// Test data
$testData = [
    'product_id' => 1,
    'quantity' => 2,
    'selected_options' => [
        'Kích thước' => '4x8 feet',
        'Màu sắc' => 'Nâu tự nhiên'
    ]
];

echo "<h3>Test Data:</h3>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Simulate the API call
echo "<h3>Simulating API Call:</h3>";

// Set up the environment like the real API call
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simulate the raw input
$rawInput = json_encode($testData);
echo "<p>Raw input: <code>$rawInput</code></p>";

// Test JSON decode
$input = json_decode($rawInput, true);
echo "<p>Decoded input:</p>";
echo "<pre>" . print_r($input, true) . "</pre>";

// Extract data like in CartController
$productId = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 1);
$selectedOptions = $input['selected_options'] ?? null;

echo "<p>Extracted data:</p>";
echo "<ul>";
echo "<li>Product ID: $productId</li>";
echo "<li>Quantity: $quantity</li>";
echo "<li>Selected Options: " . ($selectedOptions ? json_encode($selectedOptions) : 'null') . "</li>";
echo "</ul>";

// Test database connection
echo "<h3>Testing Database Connection:</h3>";
try {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/models/Product.php';
    require_once __DIR__ . '/models/Cart.php';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test Product model
    $productModel = new Product($conn);
    $product = $productModel->getById($productId);
    
    if ($product) {
        echo "<p style='color: green;'>✓ Product found: " . htmlspecialchars($product['name']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Product not found with ID: $productId</p>";
    }
    
    // Test Cart model
    $cartModel = new Cart($conn);
    
    // Test stock check
    $stockCheck = $cartModel->checkStock($productId, $quantity, $selectedOptions);
    echo "<p>Stock check result: " . ($stockCheck ? 'OK' : 'FAILED') . "</p>";
    
    // Test session
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();
    
    echo "<p>User ID: " . ($userId ?: 'null') . "</p>";
    echo "<p>Session ID: $sessionId</p>";
    
    // Test add to cart
    echo "<h3>Testing Add to Cart:</h3>";
    $result = $cartModel->addToCart($productId, $quantity, $selectedOptions, $userId, $sessionId);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Add to cart successful</p>";
        
        // Get cart count
        $cartCount = $cartModel->getCartCount($userId, $sessionId);
        echo "<p>Cart count: $cartCount</p>";
        
        // Get cart items
        $cartItems = $cartModel->getCart($userId, $sessionId);
        echo "<p>Cart items:</p>";
        echo "<pre>" . print_r($cartItems, true) . "</pre>";
        
    } else {
        echo "<p style='color: red;'>✗ Add to cart failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test the actual API endpoint
echo "<h3>Testing Actual API Endpoint:</h3>";
echo "<button onclick='testRealAPI()'>Test Real API</button>";
echo "<div id='api-result'></div>";

echo "<script>
function testRealAPI() {
    const testData = " . json_encode($testData) . ";
    
    console.log('Testing real API with data:', testData);
    
    fetch('index.php?page=api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(testData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        document.getElementById('api-result').innerHTML = '<h4>API Response:</h4><pre>' + text + '</pre>';
        
        try {
            const data = JSON.parse(text);
            console.log('Parsed response:', data);
        } catch (e) {
            console.error('JSON parse error:', e);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('api-result').innerHTML = '<p style=\"color: red;\">Error: ' + error.message + '</p>';
    });
}
</script>";

// Show current cart contents
echo "<h3>Current Cart Contents:</h3>";
try {
    if (isset($cartModel)) {
        $currentCart = $cartModel->getCart($userId, $sessionId);
        if (empty($currentCart)) {
            echo "<p>Cart is empty</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Product</th><th>Quantity</th><th>Options</th><th>Price</th></tr>";
            foreach ($currentCart as $item) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                echo "<td>" . $item['quantity'] . "</td>";
                echo "<td>" . ($item['selected_options'] ? htmlspecialchars($item['selected_options']) : 'None') . "</td>";
                echo "<td>" . number_format($item['total_price']) . "₫</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error getting cart: " . $e->getMessage() . "</p>";
}
?>