<?php
// user/dashboard.php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

// Get user's statistics
$user_id = $_SESSION['user_id'];

// Get active borrowings count
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM borrowings
    WHERE user_id = ? AND status = 'borrowed'
");
$stmt->execute([$user_id]);
$active_borrowings = $stmt->fetchColumn();

// Get pending requests count
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM borrowings
    WHERE user_id = ? AND status = 'pending'
");
$stmt->execute([$user_id]);
$pending_requests = $stmt->fetchColumn();

// Get recent borrowings
$stmt = $pdo->prepare("
    SELECT b.*, bk.title as book_title, bk.author, bk.cover_image
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    WHERE b.user_id = ?
    ORDER BY b.borrow_date DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_borrowings = $stmt->fetchAll();

$page_title = "Dashboard";
$current_page = 'dashboard';
require_once '../includes/user_header.php';
?>

<!-- Main Container -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <p class="mt-1 text-sm text-gray-600">Berikut adalah ringkasan aktivitas peminjaman Anda</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Active Borrowings Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-blue-500 transition-colors duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-gradient-to-br from-blue-500 to-blue-600">
                    <i class="fas fa-book-reader text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Buku Dipinjam</p>
                    <div class="flex items-baseline">
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $active_borrowings; ?></p>
                        <p class="ml-2 text-sm text-gray-600">buku</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-yellow-500 transition-colors duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-gradient-to-br from-yellow-500 to-yellow-600">
                    <i class="fas fa-clock text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Menunggu Persetujuan</p>
                    <div class="flex items-baseline">
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $pending_requests; ?></p>
                        <p class="ml-2 text-sm text-gray-600">permintaan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Borrowings Section -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="border-b border-gray-200 bg-gray-50 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Peminjaman Terbaru</h2>
                <a href="/user/borrowings.php" class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200 flex items-center">
                    Lihat Semua
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <?php if ($recent_borrowings): ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($recent_borrowings as $borrowing): ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="h-20 w-14 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 border border-gray-200">
                                <?php if ($borrowing['cover_image']): ?>
                                    <img src="/uploads/covers/<?php echo htmlspecialchars($borrowing['cover_image']); ?>"
                                        alt="<?php echo htmlspecialchars($borrowing['book_title']); ?>"
                                        class="h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="h-full w-full flex items-center justify-center">
                                        <i class="fas fa-book text-gray-400 text-xl"></i>
                                    </div>
                                <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors duration-200">
                                        <?php echo htmlspecialchars($borrowing['book_title']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <?php echo htmlspecialchars($borrowing['author']); ?>
                                    </p>
                                    <?php if ($borrowing['status'] === 'borrowed'): ?>
                                        <div class="flex items-center mt-2 text-sm text-gray-500">
                                            <i class="far fa-calendar-alt mr-2"></i>
                                            <span>Tenggat: <?php echo date('d M Y', strtotime($borrowing['due_date'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <?php
                                $status_classes = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'borrowed' => 'bg-green-100 text-green-800 border-green-200',
                                    'returned' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'rejected' => 'bg-red-100 text-red-800 border-red-200'
                                ];
                                $status_labels = [
                                    'pending' => 'Menunggu',
                                    'borrowed' => 'Dipinjam',
                                    'returned' => 'Dikembalikan',
                                    'rejected' => 'Ditolak'
                                ];
                                $status_icons = [
                                    'pending' => 'fas fa-clock',
                                    'borrowed' => 'fas fa-book-reader',
                                    'returned' => 'fas fa-check-circle',
                                    'rejected' => 'fas fa-times-circle'
                                ];
                                ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border
                                    <?php echo $status_classes[$borrowing['status']] ?? 'bg-gray-100 text-gray-800 border-gray-200'; ?>">
                                    <i class="<?php echo $status_icons[$borrowing['status']] ?? 'fas fa-info-circle'; ?> mr-1.5 text-xs"></i>
                                    <?php echo $status_labels[$borrowing['status']] ?? 'Unknown'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <i class="fas fa-book text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">Belum Ada Peminjaman</h3>
                <p class="text-sm text-gray-500">Mulai pinjam buku untuk melihat riwayat peminjaman Anda</p>
                <a href="/user/books.php" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 mt-4 transition-colors duration-200">
                    Cari Buku
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>