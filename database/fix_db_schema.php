<?php
require 'config.php';

try {
    echo "Checking database schema...<br>";

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'queue'");
    if ($stmt->rowCount() == 0) {
        echo "Creating table 'queue'...<br>";
        $sql = "CREATE TABLE queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_code VARCHAR(50) NOT NULL,
            name VARCHAR(100) NOT NULL,
            contact VARCHAR(100),
            status VARCHAR(50) DEFAULT 'Request',
            quantity INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "Table 'queue' created.<br>";
    } else {
        echo "Table 'queue' exists.<br>";
    }

    // Check Columns
    $stmt = $pdo->prepare("DESCRIBE queue");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('status', $columns)) {
        echo "Adding 'status' column...<br>";
        $pdo->exec("ALTER TABLE queue ADD COLUMN status VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ลงทะเบียนแล้ว'");
        echo "Column 'status' added.<br>";
    } else {
        echo "Column 'status' exists. Checking size...<br>";
        // Force modify to ensure it's large enough for Thai text
        $pdo->exec("ALTER TABLE queue MODIFY COLUMN status VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ลงทะเบียนแล้ว'");
        echo "Column 'status' updated to VARCHAR(255).<br>";
    }

    if (!in_array('quantity', $columns)) {
        echo "Adding 'quantity' column...<br>";
        $pdo->exec("ALTER TABLE queue ADD COLUMN quantity INT DEFAULT 1");
        echo "Column 'quantity' added.<br>";
    } else {
        echo "Column 'quantity' exists.<br>";
    }
    
    // Update existing NULL statuses
    echo "Updating NULL statuses...<br>";
    $pdo->exec("UPDATE queue SET status = 'ลงทะเบียนแล้ว' WHERE status IS NULL OR status = ''");
    echo "Done.<br>";

    echo "<h3>Database Fixed Successfully!</h3>";
    echo "<a href='admin.php'>Go to Admin</a> | <a href='register.php'>Go to Register</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
