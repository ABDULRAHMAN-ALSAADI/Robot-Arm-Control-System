<?php
// page name get_run_pose.php

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "robot_control";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle POST request (when Run button is pressed)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motor1 = (int)($_POST['motor1'] ?? 90);
    $motor2 = (int)($_POST['motor2'] ?? 90);
    $motor3 = (int)($_POST['motor3'] ?? 90);
    $motor4 = (int)($_POST['motor4'] ?? 90);
    $motor5 = (int)($_POST['motor5'] ?? 90);
    $motor6 = (int)($_POST['motor6'] ?? 90);

    try {
        // Update robot status to running (1) and save current motor positions
        $stmt = $pdo->prepare("UPDATE robot_status SET status = 1, current_motor1 = ?, current_motor2 = ?, current_motor3 = ?, current_motor4 = ?, current_motor5 = ?, current_motor6 = ?, last_updated = NOW() WHERE id = 1");
        $stmt->execute([$motor1, $motor2, $motor3, $motor4, $motor5, $motor6]);
        
        // Return JSON response for the web interface
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Robot is now running with new pose',
                'motor_data' => [
                    'servo1' => $motor1,
                    'servo2' => $motor2,
                    'servo3' => $motor3,
                    'servo4' => $motor4,
                    'servo5' => $motor5,
                    'servo6' => $motor6,
                    'status' => 1
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            exit();
        }
        
    } catch(Exception $e) {
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit();
        }
    }
}

// If not AJAX request, show HTML page for direct access
header('Content-Type: text/html; charset=UTF-8');

// Get current running pose from database (for display and ESP32 to read)
try {
    $stmt = $pdo->query("SELECT * FROM robot_status WHERE id = 1");
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    die("Error getting status: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Running Pose</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .status-indicator {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 18px;
        }
        .running {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        .stopped {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 16px;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        .motor-value {
            font-weight: bold;
            font-size: 18px;
            color: #2196F3;
        }
        .refresh-btn {
            display: block;
            margin: 20px auto;
            padding: 12px 24px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .refresh-btn:hover {
            background-color: #0b7dda;
        }
        .timestamp {
            text-align: center;
            color: #666;
            margin-top: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Current Running Pose</h1>
        
        <div class="status-indicator <?php echo ($status['status'] == 1) ? 'running' : 'stopped'; ?>">
            <?php if ($status['status'] == 1): ?>
                ✅ ROBOT IS RUNNING
            <?php else: ?>
                ⏹️ ROBOT IS STOPPED
            <?php endif; ?>
        </div>
        
        <?php if ($status['status'] == 1): ?>
            <p style="text-align: center; color: #155724; font-weight: bold;">
                This is the pose the robot is currently executing:
            </p>
        <?php else: ?>
            <p style="text-align: center; color: #721c24; font-weight: bold;">
                Robot is stopped. Last running pose was:
            </p>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Servo 1</th>
                    <th>Servo 2</th>
                    <th>Servo 3</th>
                    <th>Servo 4</th>
                    <th>Servo 5</th>
                    <th>Servo 6</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="motor-value"><?php echo $status['current_motor1']; ?>°</td>
                    <td class="motor-value"><?php echo $status['current_motor2']; ?>°</td>
                    <td class="motor-value"><?php echo $status['current_motor3']; ?>°</td>
                    <td class="motor-value"><?php echo $status['current_motor4']; ?>°</td>
                    <td class="motor-value"><?php echo $status['current_motor5']; ?>°</td>
                    <td class="motor-value"><?php echo $status['current_motor6']; ?>°</td>
                    <td class="motor-value"><?php echo ($status['status'] == 1) ? '1' : '0'; ?></td>
                </tr>
            </tbody>
        </table>
        
        <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Page</button>
        
        <div class="timestamp">
            Last Updated: <?php echo date('Y-m-d H:i:s', strtotime($status['last_updated'])); ?>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
            <h3>📡 For ESP32:</h3>
            <p><strong>JSON Data:</strong></p>
            <pre style="background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto;"><?php 
            echo json_encode([
                'servo1' => (int)$status['current_motor1'],
                'servo2' => (int)$status['current_motor2'],
                'servo3' => (int)$status['current_motor3'],
                'servo4' => (int)$status['current_motor4'],
                'servo5' => (int)$status['current_motor5'],
                'servo6' => (int)$status['current_motor6'],
                'status' => (int)$status['status']
            ], JSON_PRETTY_PRINT); 
            ?></pre>
        </div>
    </div>
</body>
</html>