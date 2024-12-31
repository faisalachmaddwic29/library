<?php
// admin/users/index.php
require_once '../../config/database.php';
requireAdmin();

// Handle delete action if needed
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    if ($stmt->execute([$_POST['delete_id']])) {
        redirect('/admin/users/index.php', 'User berhasil dihapus.', 'success');
    }
}

// Pagination dan search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$whereClause = "WHERE role = 'user'";
$params = [];

if ($search) {
    $whereClause .= " AND (username LIKE :searchTerm OR email LIKE :searchTerm)";
    $params[':searchTerm'] = "%$search%";
}

// Get total users
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereClause");
if ($search) {
    $totalStmt->execute($params);
} else {
    $totalStmt->execute();
}
$total_users = $totalStmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Get users with pagination
$query = "SELECT id, username, email, created_at FROM users $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

// Bind search term if present
if ($search) {
    $stmt->bindValue(':searchTerm', "%$search%", PDO::PARAM_STR);
}

$stmt->execute();
$users = $stmt->fetchAll();

$page_title = "Manajemen Pengguna";
require_once '../../includes/admin_header.php';

?>

<!-- Search -->
<div class="mb-6">
    <form class="flex items-center space-x-4">
        <div class="relative">
            <input type="text"
                   name="search"
                   value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari pengguna..."
                   class="w-96 pl-10 pr-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
        </div>
        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Cari
        </button>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Registrasi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($users as $user): ?>
            <tr>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                            <img class="h-10 w-10 rounded-full"
                                 src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=random"
                                 alt="<?php echo htmlspecialchars($user['username']); ?>">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php echo htmlspecialchars($user['email']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <form action="" method="POST" class="inline-block"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                        <input type="hidden" name="delete_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
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
                    <span class="font-medium"><?php echo min($offset + $limit, $total_users); ?></span>
                    dari
                    <span class="font-medium"><?php echo $total_users; ?></span>
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