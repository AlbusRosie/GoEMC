<?php
session_start();
require_once 'config/database.php';
require_once 'models/Cart.php';

echo "<h2>Test Database Connection and Cart Table</h2>";

// Test database connection
echo "<h3>Test 1: Database Connection</h3>";
try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "Database connection: <span style='color: green;'>SUCCESS</span><br>";
} catch (Exception $e) {
    echo "Database connection: <span style='color: red;'>FAILED</span> - " . $e->getMessage() . "<br>";
    exit;
}

// Test cart table structure
echo "<h3>Test 2: Cart Table Structure</h3>";
try {
    $stmt = $conn->query("DESCRIBE cart");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Cart table columns:<br>";
    echo "<pre>";
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . " - " . $column['Null'] . " - " . $column['Key'] . "<br>";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "Error describing cart table: " . $e->getMessage() . "<br>";
}

// Test cart table data
echo "<h3>Test 3: Cart Table Data</h3>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM cart");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total cart items: " . $result['count'] . "<br>";
    
    if ($result['count'] > 0) {
        $stmt = $conn->query("SELECT * FROM cart LIMIT 5");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample cart items:<br>";
        echo "<pre>";
        foreach ($items as $item) {
            print_r($item);
        }
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "Error querying cart table: " . $e->getMessage() . "<br>";
}

// Test session cart
echo "<h3>Test 4: Session Cart</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session data:<br>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Test Cart model
echo "<h3>Test 5: Cart Model</h3>";
try {
    $cart = new Cart($conn);
    echo "Cart model created successfully<br>";
    
    // Test getCart method
    $cartItems = $cart->getCart(null, session_id());
    echo "Cart items for current session: " . count($cartItems) . "<br>";
    
    // Test getCartCount method
    $cartCount = $cart->getCartCount(null, session_id());
    echo "Cart count for current session: " . $cartCount . "<br>";
    
    // Test getCartTotal method
    $cartTotal = $cart->getCartTotal(null, session_id());
    echo "Cart total for current session: " . number_format($cartTotal) . " VND<br>";
    
} catch (Exception $e) {
    echo "Error with Cart model: " . $e->getMessage() . "<br>";
}

// Test product options
echo "<h3>Test 6: Product Options</h3>";
try {
    require_once 'models/ProductOption.php';
    $productOption = new ProductOption($conn);
    
    $options = $productOption->getByProductId(1);
    echo "Product options for product ID 1:<br>";
    echo "<pre>" . print_r($options, true) . "</pre>";
    
} catch (Exception $e) {
    echo "Error with ProductOption model: " . $e->getMessage() . "<br>";
}

echo "<h3>Test completed!</h3>";
?> 