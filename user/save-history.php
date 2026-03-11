<?php
require_once '../config/config.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error'=>'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if($data) {
    $stmt = $pdo->prepare("INSERT INTO user_weather_history (id, location, temperature, cond, humidity, wind_speed, cloth_rec) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $data['location'],
        $data['temperature'],
        $data['cond'],
        $data['humidity'],
        $data['wind_speed'],
        $data['cloth_rec']
    ]);
    echo json_encode(['success'=>true]);
}
?>