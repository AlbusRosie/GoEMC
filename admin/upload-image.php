<?php
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra quyền admin (tạm thời bỏ qua để test)
session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     http_response_code(403);
//     echo json_encode(['error' => 'Unauthorized']);
//     exit;
// }

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Kiểm tra file upload
if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['upload'];
$upload_dir = '../assets/uploads/editor/';

// Tạo thư mục nếu chưa tồn tại
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Kiểm tra định dạng file
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$file_type = mime_content_type($file['tmp_name']);

if (!in_array($file_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, WEBP are allowed']);
    exit;
}

// Kiểm tra kích thước file (5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Maximum size is 5MB']);
    exit;
}

// Tạo tên file unique
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'editor_' . time() . '_' . uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Upload file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Trả về response cho CKEditor
    $response = [
        'url' => '/go/assets/uploads/editor/' . $filename,
        'uploaded' => 1
    ];
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to upload file',
        'debug' => [
            'tmp_name' => $file['tmp_name'],
            'filepath' => $filepath,
            'upload_dir' => $upload_dir,
            'exists' => file_exists($file['tmp_name']),
            'writable' => is_writable($upload_dir)
        ]
    ]);
}
?> 