<?php
// Basic MySQLi connection for Student Planner
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'student_planner';

$conn = new mysqli($host, $username, $password, $database);

// Stop execution if connection fails
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
