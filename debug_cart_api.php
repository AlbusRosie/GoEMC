<?php
// Debug Cart API
session_start();

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';

// Test data
$testData = [
    'product_id' => 1,
    'quantity' => 2,
    'selected_options' => [
        'Kích thước' => '4x8 feet',
        'Màu sắc' => 'Đỏ tự nhiên'
    ]
];

// Set raw input
$rawInput = json_encode($testData);

echo "<h2>Debug Cart API</h2>";
echo "<h3>Test Data:</h3>";
echo "<pre>" . print_r($testData, true) . "</pre>";

// Include necessary files
require_once 'config/database.php';
require_once 'models/Cart.php';
require_once 'models/Product.php';
require_once 'controllers/CartController.php';

// Initialize
$db = new Database();
$conn = $db->getConnection();
$cartController = new CartController($conn);

// Mock the input
$_POST = $testData;

echo "<h3>Testing CartController::addToCart()</h3>";

// Capture output
ob_start();
$cartController->addToCart();
$output = ob_get_clean();

echo "<h3>Output:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Parse JSON response
$response = json_decode($output, true);
echo "<h3>Parsed Response:</h3>";
echo "<pre>" . print_r($response, true) . "</pre>";

echo "<h3>Session ID: " . session_id() . "</h3>";
echo "<h3>Session Data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
?> 