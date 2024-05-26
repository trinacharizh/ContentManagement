<?php
header("Content-Type: application/json");

$host = 'localhost';
$db = 'my_database';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT users.id AS user_id, users.name, users.email, users.pass, posts.id AS post_id, posts.title, posts.content 
                         FROM users 
                         LEFT JOIN posts ON users.id = posts.user_id");
    $data = $stmt->fetchAll();
    echo json_encode($data);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $pdo->beginTransaction();

    try {
        // Insert user
        $sql = "INSERT INTO users (name, email, pass) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['name'], $input['email'], $input['pass']]);
        $user_id = $pdo->lastInsertId();

        // Insert post
        $sql = "INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $input['title'], $input['content']]);

        $pdo->commit();
        echo json_encode(['message' => 'User and post added successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>