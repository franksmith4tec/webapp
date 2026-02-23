<?php
/**
 * fetch.php - Unified API endpoint for billing and payment data
 * Handles all database queries for the billing component
 */

// Error reporting for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// DATABASE CONFIGURATION - UPDATE THESE!
// ============================================
$db_config = [
    'host' => 'localhost',
    'name' => 'tradfred_link_atlas_db', // Using payxone as per your SQL dump
    'user' => 'tradfred_link_atlas_user', // Change this to your database user
    'pass' => '*w!z_1DB$yRS', // Change this to your database password
    'charset' => 'utf8mb4'
];



// For your specific setup with latin1 tables, we'll handle encoding properly
$use_latin1 = true; // Set to true if your tables use latin1 charset

// ============================================
// DATABASE CONNECTION
// ============================================
try {
    // Set charset based on configuration
    $charset = $use_latin1 ? 'latin1' : $db_config['charset'];
    
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['name']};charset=$charset";
    
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 30
    ]);
    
    // Set proper encoding
    if ($use_latin1) {
        $pdo->exec("SET NAMES latin1");
    } else {
        $pdo->exec("SET NAMES utf8mb4");
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $e->getMessage()
    ]);
    exit();
}

// ============================================
// REQUEST HANDLING
// ============================================

// Parse request method
$method = $_SERVER['REQUEST_METHOD'];

// Parse input based on content type
$input = [];
if ($method === 'GET') {
    $input = $_GET;
} else {
    // For POST, PUT, DELETE - handle JSON input
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($content_type, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $input = json_decode($json, true) ?: [];
    } else {
        $input = $_POST;
    }
}

// ============================================
// API ENDPOINTS
// ============================================

// Test endpoint
if (isset($input['test'])) {
    echo json_encode([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => date('Y-m-d H:i:s'),
        'database' => $db_config['name'],
        'method' => $method,
        'charset' => $use_latin1 ? 'latin1' : 'utf8mb4'
    ]);
    exit();
}

// Get database stats
if (isset($input['stats'])) {
    try {
        $stats = [];
        
        // Get counts from all relevant tables
        $tables = ['payments', 'payment_requests', 'transactions', 'payment_card_info', 'payment_logs'];
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $stats[$table] = $stmt->fetch()['count'];
            } catch (Exception $e) {
                $stats[$table] = 0; // Table might not exist
            }
        }
        
        // Get latest record
        $latest = null;
        try {
            $stmt = $pdo->query("SELECT MAX(created_at) as latest FROM (
                SELECT created_at FROM payments UNION ALL
                SELECT created_at FROM payment_requests UNION ALL
                SELECT created_at FROM transactions
            ) as all_dates");
            $latest = $stmt->fetch()['latest'];
        } catch (Exception $e) {
            $latest = null;
        }
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'latest_record' => $latest,
            'database' => $db_config['name']
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to get stats',
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Get single record by ID from specified table
if (isset($input['id']) && isset($input['table'])) {
    $id = filter_var($input['id'], FILTER_VALIDATE_INT);
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $input['table']); // Sanitize table name
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit();
    }
    
    // Validate table exists
    $allowed_tables = ['payments', 'payment_requests', 'transactions', 'payment_card_info', 'payment_logs', 'billing_details'];
    if (!in_array($table, $allowed_tables)) {
        echo json_encode(['success' => false, 'error' => 'Invalid table']);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            // Convert encoding if using latin1
            if ($use_latin1) {
                $data = array_map(function($value) {
                    return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1') : $value;
                }, $data);
            }
            
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Record not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Query failed: ' . $e->getMessage()]);
    }
    exit();
}

// ============================================
// MAIN DATA FETCHING (PAGINATED)
// ============================================

// Default parameters
$page = isset($input['page']) ? max(1, intval($input['page'])) : 1;
$limit = isset($input['limit']) ? min(100, max(1, intval($input['limit']))) : 20;
$offset = ($page - 1) * $limit;

// Table to query (default to payment_requests which has most complete data)
$table = isset($input['table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $input['table']) : 'payment_requests';

// Validate table
$allowed_tables = ['payments', 'payment_requests', 'transactions', 'payment_card_info', 'payment_logs', 'billing_details'];
if (!in_array($table, $allowed_tables)) {
    $table = 'payment_requests'; // Fallback to default
}

// Sorting
$sort_field = isset($input['sort']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $input['sort']) : 'id';
$sort_order = isset($input['order']) && strtoupper($input['order']) === 'ASC' ? 'ASC' : 'DESC';

// Filtering
$where_clauses = [];
$params = [];

if (isset($input['search']) && !empty($input['search'])) {
    $search = '%' . $input['search'] . '%';
    
    // Search in relevant fields based on table
    switch ($table) {
        case 'payment_requests':
            $where_clauses[] = "(customer_name LIKE ? OR customer_email LIKE ? OR payment_id LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            break;
        case 'payments':
            $where_clauses[] = "(customer_first_name LIKE ? OR customer_last_name LIKE ? OR customer_email LIKE ? OR transaction_id LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            break;
        case 'transactions':
            $where_clauses[] = "(gateway_transaction_id LIKE ? OR payer_email LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            break;
        default:
            // Generic search on all text fields
            $where_clauses[] = "1=1"; // No search for other tables
    }
}

if (isset($input['status']) && !empty($input['status'])) {
    $where_clauses[] = "status = ?";
    $params[] = $input['status'];
}

if (isset($input['from_date']) && !empty($input['from_date'])) {
    $where_clauses[] = "created_at >= ?";
    $params[] = $input['from_date'];
}

if (isset($input['to_date']) && !empty($input['to_date'])) {
    $where_clauses[] = "created_at <= ?";
    $params[] = $input['to_date'];
}

// Build WHERE clause
$where_sql = empty($where_clauses) ? '' : 'WHERE ' . implode(' AND ', $where_clauses);

try {
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM $table $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetch()['total'];
    
    // Get paginated data
    $data_sql = "SELECT * FROM $table $where_sql ORDER BY $sort_field $sort_order LIMIT ? OFFSET ?";
    $data_stmt = $pdo->prepare($data_sql);
    
    // Merge parameters for limit and offset
    $data_params = array_merge($params, [$limit, $offset]);
    $data_stmt->execute($data_params);
    $data = $data_stmt->fetchAll();
    
    // Convert encoding if using latin1
    if ($use_latin1 && !empty($data)) {
        foreach ($data as &$row) {
            foreach ($row as $key => &$value) {
                if (is_string($value)) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                }
            }
        }
    }
    
    // Calculate pagination info
    $pages = ceil($total / $limit);
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $pages,
            'has_next' => $page < $pages,
            'has_prev' => $page > 1
        ],
        'table' => $table,
        'query' => [
            'sort_field' => $sort_field,
            'sort_order' => $sort_order,
            'filters_applied' => !empty($where_clauses)
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Query failed',
        'message' => $e->getMessage(),
        'sql' => $data_sql ?? null
    ]);
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get all available tables in database
 */
function getTables($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get table structure
 */
function getTableStructure($pdo, $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Sanitize output for JSON
 */
function sanitizeOutput($data) {
    if (is_array($data)) {
        return array_map('sanitizeOutput', $data);
    }
    if (is_string($data)) {
        // Remove any invalid UTF-8 characters
        $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        $data = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $data);
    }
    return $data;
}

/**
 * Log API request for debugging
 */
function logRequest($pdo, $method, $params, $response_code) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO payment_logs (log_type, message, data, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            'api_request',
            "{$method} request",
            json_encode(['params' => $params, 'response_code' => $response_code]),
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        ]);
    } catch (Exception $e) {
        // Silently fail - logging should not break the main functionality
    }
}
?>