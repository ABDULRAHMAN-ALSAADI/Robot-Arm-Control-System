<?php
// page name update_status.php

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

// Handle POST request (when Stop button is pressed)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get current running pose before stopping
        $stmt = $pdo->query("SELECT * FROM robot_status WHERE id = 1");
        $currentStatus = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Save current running pose as stopped pose and set status to 0
        $stmt = $pdo->prepare("UPDATE robot_status SET 
            status = 0, 
            stopped_motor1 = ?, 
            stopped_motor2 = ?, 
            stopped_motor3 = ?, 
            stopped_motor4 = ?, 
            stopped_motor5 = ?, 
            stopped_motor6 = ?, 
            last_updated = NOW() 
            WHERE id = 1");
        
        $stmt->execute([
            $currentStatus['current_motor1'],
            $currentStatus['current_motor2'],
            $currentStatus['current_motor3'],
            $currentStatus['current_motor4'],
            $currentStatus['current_motor5'],
            $currentStatus['current_motor6']
        ]);
        
        // Return JSON response for the web interface
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Robot stopped successfully',
                'stopped_pose' => [
                    'servo1' => (int)$currentStatus['current_motor1'],
                    'servo2' => (int)$currentStatus['current_motor2'],
                    'servo3' => (int)$currentStatus['current_motor3'],
                    'servo4' => (int)$currentStatus['current_motor4'],
                    'servo5' => (int)$currentStatus['current_motor5'],
                    'servo6' => (int)$currentStatus['current_motor6'],
                    'status' => 0
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

// Get current status and stopped pose from database
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
    <title>Last Stopped Pose</title>
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
            background-color: #FF5722;
            color: white;
            font-weight: bold;
        }
        .motor-value {
            font-weight: bold;
            font-size: 18px;
            color: #FF5722;
        }
        .refresh-btn {
            display: block;
            margin: 20px auto;
            padding: 12px 24px;
            background-color: #FF5722;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .refresh-btn:hover {
            background-color: #E64A19;
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
        <h1>⏹️ Last Stopped Pose</h1>
        
        <div class="status-indicator <?php echo ($status['status'] == 1) ? 'running' : 'stopped'; ?>">
            <?php if ($status['status'] == 1): ?>
                ✅ ROBOT IS CURRENTLY RUNNING
            <?php else: ?>
                ⏹️ ROBOT IS STOPPED
            <?php endif; ?>
        </div>
        
        <p style="text-align: center; color: #721c24; font-weight: bold;">
            This is the pose where the robot was stopped:
        </p>
        
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
                    <td class="motor-value"><?php echo $status['stopped_motor1']; ?>°</td>
                    <td class="motor-value"><?php echo $status['stopped_motor2']; ?>°</td>
                    <td class="motor-value"><?php echo $status['stopped_motor3']; ?>°</td>
                    <td class="motor-value"><?php echo $status['stopped_motor4']; ?>°</td>
                    <td class="motor-value"><?php echo $status['stopped_motor5']; ?>°</td>
                    <td class="motor-value"><?php echo $status['stopped_motor6']; ?>°</td>
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
            <p><strong>Stopped Pose JSON:</strong></p>
            <pre style="background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto;"><?php 
            echo json_encode([
                'servo1' => (int)$status['stopped_motor1'],
                'servo2' => (int)$status['stopped_motor2'],
                'servo3' => (int)$status['stopped_motor3'],
                'servo4' => (int)$status['stopped_motor4'],
                'servo5' => (int)$status['stopped_motor5'],
                'servo6' => (int)$status['stopped_motor6'],
                'status' => (int)$status['status']
            ], JSON_PRETTY_PRINT); 
            ?></pre>
        </div>
    </div>
</body>
</html>