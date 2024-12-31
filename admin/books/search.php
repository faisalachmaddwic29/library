<?php
require_once '../../config/database.php';
requireAdmin();

header('Content-Type: application/json');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = 10;

$whereClause = '';
$params = [];

if ($search) {
    $whereClause = "WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$query = "SELECT * FROM books $whereClause ORDER BY created_at DESC LIMIT :limit";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

if ($search) {
    $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(3, $searchTerm, PDO::PARAM_STR);
}

$stmt->execute();
$books = $stmt->fetchAll();

echo json_encode($books);