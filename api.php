<?php
// api.php
header('Content-Type: application/json');
require 'config.php';

$action = $_GET['action'] ?? '';

// 1. REGISTER
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    
    if (!$name) { echo json_encode(['success' => false, 'error' => 'Name required']); exit; }

    // Generate Code: YYYYMMDD-HHmm
    $code = date('Ymd-His'); 

    $stmt = $pdo->prepare("INSERT INTO queue (customer_code, name, contact) VALUES (?, ?, ?)");
    if ($stmt->execute([$code, $name, $contact])) {
        echo json_encode(['success' => true, 'id' => $code]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Insert Failed']);
    }
    exit;
}

// 2. GET QUEUE
if ($action === 'get_queue') {
    $stmt = $pdo->query("SELECT * FROM queue ORDER BY id DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    exit;
}

// 3. UPDATE STATUS
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE queue SET status = ? WHERE customer_code = ?");
    $stmt->execute([$data['status'], $data['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// 4. UPDATE QUANTITY
if ($action === 'update_qty' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE queue SET quantity = ? WHERE customer_code = ?");
    $stmt->execute([$data['qty'], $data['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// 5. UPLOAD IMAGE (Base64)
if ($action === 'upload_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $imageData = $data['image']; // Base64 string
    $customerId = $data['id'];
    $index = $data['index']; // 1, 2, 3 or 'final'
    
    // Convert Base64 to File
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    $imageBytes = base64_decode($imageData);
    
    $fileName = "{$customerId}_{$index}.png";
    $filePath = 'uploads/' . $fileName;
    
    if (file_put_contents($filePath, $imageBytes)) {
        echo json_encode(['success' => true, 'url' => $filePath]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Write failed']);
    }
    exit;
}

// 6. GET CUSTOMER IMAGES
if ($action === 'get_images') {
    $id = $_GET['id'];
    $images = [];
    for ($i = 1; $i <= 3; $i++) {
        $path = "uploads/{$id}_{$i}.png";
        if (file_exists($path)) {
            // Return URL (relative)
            $images[] = $path . '?t=' . time(); // Add timestamp to bust cache
        }
    }
    echo json_encode(['images' => $images]);
    exit;
}
?>
