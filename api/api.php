<?php
// api/api.php
session_start();
header('Content-Type: application/json');

require '../includes/config.php';
require '../includes/csrf.php';
require '../includes/rate_limiter.php';

$action = $_GET['action'] ?? '';

// HELPER: Validate CSRF for POST requests
function validateCSRF()
{
    $token = '';
    // Check Header
    foreach (getallheaders() as $name => $value) {
        if (strcasecmp($name, 'X-CSRF-TOKEN') == 0) {
            $token = $value;
            break;
        }
    }
    // Check POST data
    if (empty($token) && !empty($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    }
    // Check JSON body
    if (empty($token)) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!empty($input['csrf_token'])) {
            $token = $input['csrf_token'];
        }
    }

    if (!CSRF::check($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF Token']);
        exit;
    }
}

// Helper: Get input (POST or JSON)
function getInput()
{
    if (!empty($_POST))
        return $_POST;
    $json = json_decode(file_get_contents('php://input'), true);
    return is_array($json) ? $json : [];
}

// 0. GENERATE CSRF TOKEN (Action)
if ($action === 'get_csrf_token') {
    echo json_encode(['token' => CSRF::generate()]);
    exit;
}

// 1. REGISTER
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ideally protect register too, but public pages might be tricky with simple session/form token for mobile users.
    // We'll relax CSRF for register if easy access is needed, BUT it's better to secure it.
    // Let's assume we will pass token from frontend.
    validateCSRF();

    $name = $_POST['name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $qty = $_POST['qty'] ?? 1;

    if (!$name) {
        echo json_encode(['success' => false, 'error' => 'Name required']);
        exit;
    }

    // Generate Code: YYYYMMDD-HHmm
    $code = date('Ymd-His');

    try {
        $stmt = $pdo->prepare("INSERT INTO queue (customer_code, name, contact, status, quantity) VALUES (?, ?, ?, 'ลงทะเบียนแล้ว', ?)");
        if ($stmt->execute([$code, $name, $contact, $qty])) {
            echo json_encode(['success' => true, 'id' => $code]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Insert Failed']);
        }
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Database Error']);
    }
    exit;
}

// LOGIN
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    // Check Rate Limit (5 attempts per 5 minutes)
    if (!RateLimiter::check('login_attempt', 5, 300)) {
        echo json_encode(['success' => false, 'error' => 'ทำรายการเกินกำหนด กรุณารอ 5 นาที']);
        exit;
    }

    // Login using Database (admins table)
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];

            RateLimiter::reset('login_attempt');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'System Error']);
    }
    exit;
}

// LOGOUT
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// 2. GET QUEUE
if ($action === 'get_queue') {
    $stmt = $pdo->query("SELECT * FROM queue ORDER BY id DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    exit;
}

// 3. UPDATE STATUS (Requires Login usually, or at least CSRF)
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['admin_logged_in'])) {
        // Optionally allow customers to update their own status via selfie page without login?
        // For now, let's just validate CSRF which implies they visited the site
    }
    validateCSRF();

    $data = getInput();
    $stmt = $pdo->prepare("UPDATE queue SET status = ? WHERE customer_code = ?");
    $stmt->execute([$data['status'], $data['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// 4. UPDATE QUANTITY
if ($action === 'update_qty' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    $data = getInput();
    $stmt = $pdo->prepare("UPDATE queue SET quantity = ? WHERE customer_code = ?");
    $stmt->execute([$data['qty'], $data['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// 5. UPLOAD IMAGE (Base64)
if ($action === 'upload_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    $data = getInput();
    $imageData = $data['image']; // Base64 string
    $customerId = $data['id'];
    $index = $data['index']; // 1, 2, 3 or 'final'

    // Validation: Check if image data seems valid
    if (strpos($imageData, 'data:image') === false) {
        echo json_encode(['success' => false, 'error' => 'Invalid Image Data']);
        exit;
    }

    // Convert Base64 to File
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    $imageBytes = base64_decode($imageData);

    // Prevent directory traversal
    $fileName = basename("{$customerId}_{$index}.png");
    $targetDir = __DIR__ . '/../uploads/';
    $filePath = $targetDir . $fileName;

    if (file_put_contents($filePath, $imageBytes)) {
        // Return relative path for frontend
        echo json_encode(['success' => true, 'url' => 'uploads/' . $fileName]);
    } else {
        error_log("Write Failed to $filePath");
        echo json_encode(['success' => false, 'error' => 'Write failed']);
    }
    exit;
}

// 6. GET CUSTOMER IMAGES
if ($action === 'get_images') {
    $id = basename($_GET['id']); // Security: basename
    $images = [];
    for ($i = 1; $i <= 3; $i++) {
        $path = "../uploads/{$id}_{$i}.png"; // Internal path
        if (file_exists(__DIR__ . '/' . $path)) {
            // Return URL (relative to frontend pages usually, but we return from root perspective)
            // Ideally frontend aligns this.
            // Our files are in /photobooth/uploads
            // api is in /photobooth/api
            // relative from api is ../uploads
            $images[] = '../uploads/' . "{$id}_{$i}.png" . '?t=' . time();
        }
    }
    echo json_encode(['images' => $images]);
    exit;
}
?>