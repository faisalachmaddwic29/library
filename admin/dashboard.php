<?php
// admin/dashboard.php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php', 'Anda harus login sebagai admin untuk mengakses halaman ini.', 'error');
}

// Get statistics
$stats = [
    'total_books' => $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'active_borrowings' => $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed'")->fetchColumn(),
    'overdue_borrowings' => $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed' AND due_date < NOW()")->fetchColumn()
];

// Get recent borrowings
$recent_borrowings = $pdo->query("
    SELECT b.*, u.username, bk.title as book_title
    FROM borrowings b
    JOIN users u ON b.user_id = u.id
    JOIN books bk ON b.book_id = bk.id
    ORDER BY b.borrow_date DESC
    LIMIT 5
")->fetchAll();

// Get most borrowed books
$popular_books = $pdo->query("
    SELECT b.title, b.author, COUNT(br.id) as borrow_count
    FROM books b
    LEFT JOIN borrowings br ON b.id = br.book_id
    GROUP BY b.id
    ORDER BY borrow_count DESC
    LIMIT 5
")->fetchAll();

$page_title = "Dashboard Admin";
require_once '../includes/admin_header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Total Books -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                <i class="fas fa-book text-blue-500 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500 uppercase">Total Buku</p>
                <p class="text-2xl font-semibold text-gray-700"><?php echo $stats['total_books']; ?></p>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                <i class="fas fa-users text-green-500 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500 uppercase">Total Pengguna</p>
                <p class="text-2xl font-semibold text-gray-700"><?php echo $stats['total_users']; ?></p>
            </div>
        </div>
    </div>

    <!-- Active Borrowings -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-20">
                <i class="fas fa-handshake text-yellow-500 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500 uppercase">Peminjaman Aktif</p>
                <p class="text-2xl font-semibold text-gray-700"><?php echo $stats['active_borrowings']; ?></p>
            </div>
        </div>
    </div>

    <!-- Overdue Borrowings -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-500 bg-opacity-20">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500 uppercase">Terlambat</p>
                <p class="text-2xl font-semibold text-gray-700"><?php echo $stats['overdue_borrowings']; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Borrowings -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Peminjaman Terbaru</h2>
        </div>
        <div class="p-6">
            <?php if ($recent_borrowings): ?>
                <div class="space-y-4">
                    <?php foreach ($recent_borrowings as $borrowing): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <img class="h-10 w-10 rounded-full bg-gray-300"
                                     src="https://ui-avatars.com/api/?name=<?php echo urlencode($borrowing['username']); ?>"
                                     alt="<?php echo htmlspecialchars($borrowing['username']); ?>">
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($borrowing['username']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($borrowing['book_title']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-900">
                                    <?php echo date('d M Y', strtotime($borrowing['borrow_date'])); ?>
                                </p>
                                <p class="text-sm <?php echo $borrowing['status'] === 'borrowed' ? 'text-green-600' : 'text-gray-500'; ?>">
                                    <?php
                                    switch($borrowing['status']) {
                                        case 'pending':
                                            echo 'Menunggu';
                                            break;
                                        case 'borrowed':
                                            echo 'Dipinjam';
                                            break;
                                        case 'returned':
                                            echo 'Dikembalikan';
                                            break;
                                        case 'overdue':
                                            echo 'Terlambat';
                                            break;
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6">
                    <a href="/admin/borrowings/index.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat semua peminjaman
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Belum ada peminjaman</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Popular Books -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Buku Terpopuler</h2>
        </div>
        <div class="p-6">
            <?php if ($popular_books): ?>
                <div class="space-y-4">
                    <?php foreach ($popular_books as $book): ?>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($book['title']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($book['author']); ?></p>
                            </div>
                            <div class="flex items-center">
                                <span class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-full">
                                    <?php echo $book['borrow_count']; ?> kali dipinjam
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6">
                    <a href="books/index.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat semua buku
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Belum ada data peminjaman buku</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>