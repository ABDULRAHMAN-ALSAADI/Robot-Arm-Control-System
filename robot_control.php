<?php
// page name robot_control.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$servername = "localhost";
$username = "root";  // Default XAMPP username
$password = "";      // Default XAMPP password (empty)
$dbname = "robot_control";

try {
    // Create connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Handle different actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'save':
        saveMove();
        break;
    case 'load':
        loadMove();
        break;
    case 'list':
        listMoves();
        break;
    case 'delete':
        deleteMove();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function saveMove() {
    global $pdo;
    
    try {
        // Get POST data
        $move_name = $_POST['move_name'] ?? '';
        $motor1 = (int)($_POST['motor1'] ?? 90);
        $motor2 = (int)($_POST['motor2'] ?? 90);
        $motor3 = (int)($_POST['motor3'] ?? 90);
        $motor4 = (int)($_POST['motor4'] ?? 90);
        $motor5 = (int)($_POST['motor5'] ?? 90);
        $motor6 = (int)($_POST['motor6'] ?? 90);
        
        // Validate input
        if (empty($move_name)) {
            echo json_encode(['success' => false, 'message' => 'Move name is required']);
            return;
        }
        
        // Check if move name already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM robot_moves WHERE move_name = ?");
        $stmt->execute([$move_name]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Move name already exists']);
            return;
        }
        
        // Insert new move
        $stmt = $pdo->prepare("INSERT INTO robot_moves (move_name, motor1, motor2, motor3, motor4, motor5, motor6, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$move_name, $motor1, $motor2, $motor3, $motor4, $motor5, $motor6]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Move saved successfully',
            'move_id' => $pdo->lastInsertId()
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function loadMove() {
    global $pdo;
    
    try {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid move ID']);
            return;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM robot_moves WHERE id = ?");
        $stmt->execute([$id]);
        $move = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($move) {
            echo json_encode(['success' => true, 'move' => $move]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Move not found']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function listMoves() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM robot_moves ORDER BY id ASC");
        $moves = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'moves' => $moves]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteMove() {
    global $pdo;
    
    try {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid move ID']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM robot_moves WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Move deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Move not found']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>