<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

try {
    $search = $_GET['search'] ?? '';
    
    if (empty($search)) {
        // Jika search kosong, ambil semua buku
        $sql = "SELECT * FROM books ORDER BY title ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        // Jika ada kata kunci pencarian
        $sql = "SELECT * FROM books 
                WHERE title LIKE ? 
                OR author LIKE ? 
                OR isbn LIKE ? 
                ORDER BY title ASC";
        $search_term = "%$search%";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$search_term, $search_term, $search_term]);
    }
    
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $books
    ]);

} catch (PDOException $e) {
    // Log error dan kirim response error
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>