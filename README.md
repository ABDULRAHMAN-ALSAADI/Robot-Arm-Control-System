# 🤖 Robot Arm Control System

A web-based control panel for managing a 6-axis robot arm with real-time motor control and pose saving functionality.

## 🌟 Features

- 🎛️ Interactive Control Panel - Control 6 servo motors with smooth sliders

- 💾 Save & Load Poses - Store your favorite robot positions with custom names

- ▶️ Run/Stop Control - Start and stop robot movements with visual feedback

- 📊 Real-time Status - Monitor current robot position and running status

- 🔄 Database Integration - All poses and status saved in XAMPP - MySQL database

## 🛠️ Technologies Used

- Frontend: HTML5, CSS3, JavaScript (Vanilla)

- Backend: PHP

- Database: MySQL

- Server: XAMPP (Apache + MySQL)

- Hardware: ESP32 compatible (ready for integration)

## 📦 Installation

### Prerequisites

- XAMPP or similar LAMP/WAMP stack

- Modern web browser

- Basic knowledge of PHP and MySQL

### Setup Steps

1- Clone this repository

2- Start XAMPP

- Start Apache and MySQL services

3- Create Database

- Open phpMyAdmin (http://localhost/phpmyadmin)

- Import the database.sql file or run the SQL commands:

4- Configure Database Connection

- Update database credentials in PHP files if needed:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "robot_control";

```

5- Deploy Files

- Copy all files to your htdocs folder (or web server directory).
  
- Access the control panel at http://localhost/robot-arm-control/

## 🎮 How to Use

Basic Controls

- Move Sliders ⤵️ - Adjust each motor position (0-180 degrees)

- Enter Move Name ✏️ - Give your pose a descriptive name

- Save Move 💾 - Store the current position to database

- Run ▶️ - Execute the current motor positions

- Stop ⏹️ - Stop robot movement and save stopped position


## 🔌 Hardware Integration

This system is designed to work with ESP32 microcontrollers. The robot status is stored in the database and can be read by your ESP32 using HTTP requests:

- Get current pose: GET /get_run_pose.php
  
Access the current pose at http://localhost/robot_control/get_run_pose.php

- Get stopped pose: GET /update_status.php
  
Access the stopped pose at http://localhost/robot_control/update_status.php

  ## 🚀 Screenshots
  
<img width="1920" height="930" alt="Screenshot 2025-07-24 224746" src="https://github.com/user-attachments/assets/b31400a7-56f1-44f4-bde4-0299cf6080a0" />

<img width="1920" height="930" alt="Screenshot 2025-07-26 150037" src="https://github.com/user-attachments/assets/d340df06-6960-4e43-b54a-6d42a812543b" />

<img width="1920" height="932" alt="Screenshot 2025-07-25 231710" src="https://github.com/user-attachments/assets/5fd01e2f-e7a4-4a8c-802f-098581d08aa9" />

