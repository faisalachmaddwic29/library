<?php
require_once '../../config/database.php';
requireAdmin();

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        isbn VARCHAR(50) NOT NULL,
        category VARCHAR(100) NOT NULL,
        description TEXT,
        cover_image VARCHAR(255),
        total_copies INT NOT NULL DEFAULT 1,
        available_copies INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    error_log("Error creating books table: " . $e->getMessage());
}

// Handle delete action
if (isset($_POST['delete_id'])) {
    // Get book info for cover image deletion
    $stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    $book = $stmt->fetch();

    // Delete the book
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    if ($stmt->execute([$_POST['delete_id']])) {
        // Delete cover image if exists
        if ($book && $book['cover_image']) {
            $cover_path = ROOT_PATH . '/uploads/covers/' . $book['cover_image'];
            if (file_exists($cover_path)) {
                unlink($cover_path);
            }
        }
        redirect('/admin/books/index.php', 'Buku berhasil dihapus.', 'success');
    }
}

// Pagination dan search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$whereClause = '';
$params = [];

if ($search) {
    $whereClause = "WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Get total books for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM books $whereClause");
$search ? $totalStmt->execute($params) : $totalStmt->execute();
$total_books = $totalStmt->fetchColumn();
$total_pages = ceil($total_books / $limit);

// Get books with pagination and search
$query = "SELECT * FROM books $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

if ($search) {
    $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(3, $searchTerm, PDO::PARAM_STR);
}

$stmt->execute();
$books = $stmt->fetchAll();

$page_title = "Manajemen Buku";
require_once '../../includes/admin_header.php';
?>

<!-- Search and Add New Button -->
<div class="flex justify-between items-center mb-6">
    <div class="relative">
        <input type="text" 
               id="searchInput"
               name="search" 
               value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Cari buku..." 
               class="w-96 pl-10 pr-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
        </div>
    </div>
    
    <a href="create.php" 
       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
        <i class="fas fa-plus mr-2"></i>Tambah Buku
    </a>
</div>

<!-- Books Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cover</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul & Penulis</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($books as $book): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="h-20 w-16 bg-gray-200 rounded overflow-hidden">
                    <?php if ($book['cover_image']): ?>
                        <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                             alt="<?php echo htmlspecialchars($book['title']); ?>"
                             class="h-full w-full object-cover">
                    <?php else: ?>
                        <div class="h-full w-full flex items-center justify-center bg-gray-200">
                            <i class="fas fa-book text-gray-400 text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($book['title']); ?></div>
                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($book['author']); ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php echo htmlspecialchars($book['isbn']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        <?php echo htmlspecialchars($book['category']); ?>
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php echo $book['available_copies']; ?> / <?php echo $book['total_copies']; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        <a href="edit.php?id=<?php echo $book['id']; ?>" 
                           class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="" method="POST" class="inline-block" 
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
                            <input type="hidden" name="delete_id" value="<?php echo $book['id']; ?>">
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="mt-6">
    <nav class="flex items-center justify-between">
        <div class="flex-1 flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-700">
                    Menampilkan
                    <span class="font-medium"><?php echo $offset + 1; ?></span>
                    sampai
                    <span class="font-medium"><?php echo min($offset + $limit, $total_books); ?></span>
                    dari
                    <span class="font-medium"><?php echo $total_books; ?></span>
                    hasil
                </p>
            </div>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" 
                       class="px-3 py-2 border rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" 
                       class="px-3 py-2 border rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</div>
<?php endif; ?>

<?php require_once '../../includes/admin_footer.php'; ?>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        let title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        let author = row.querySelector('td:nth-child(2) div:last-child').textContent.toLowerCase();
        let isbn = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        let category = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
        
        if (title.includes(searchText) || 
            author.includes(searchText) || 
            isbn.includes(searchText) || 
            category.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>   