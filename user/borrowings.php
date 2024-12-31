<?php
require_once '../config/database.php';
require_once '../includes/user_header.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? '';

// Prepare WHERE clause
$where_clause = "WHERE b.user_id = ?";
$params = [$user_id];

if ($status_filter) {
    $where_clause .= " AND b.status = ?";
    $params[] = $status_filter;
}

// Get borrowings
$stmt = $pdo->prepare("
    SELECT b.*, bk.title as book_title, bk.author, bk.cover_image
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    $where_clause
    ORDER BY b.borrow_date DESC
");

$stmt->execute($params);
$borrowings = $stmt->fetchAll();


$page_title = "Peminjaman Saya";
$current_page = 'borrowings';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Status Filter with Icons -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <div class="hidden sm:flex space-x-4" aria-label="Tabs">
            <a href="?status="
               class="<?php echo !$status_filter ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'; ?> flex items-center px-4 py-2 border rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-list-ul mr-2"></i>Semua
            </a>
            <a href="?status=pending"
               class="<?php echo $status_filter === 'pending' ? 'bg-yellow-50 border-yellow-500' : 'hover:bg-gray-50'; ?> flex items-center px-4 py-2 border rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-clock mr-2"></i>Menunggu
            </a>
            <a href="?status=borrowed"
               class="<?php echo $status_filter === 'borrowed' ? 'bg-green-50 border-green-500' : 'hover:bg-gray-50'; ?> flex items-center px-4 py-2 border rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-book-reader mr-2"></i>Dipinjam
            </a>
            <a href="?status=returned"
               class="<?php echo $status_filter === 'returned' ? 'bg-blue-50 border-blue-500' : 'hover:bg-gray-50'; ?> flex items-center px-4 py-2 border rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-check-circle mr-2"></i>Dikembalikan
            </a>
            <a href="?status=rejected"
               class="<?php echo $status_filter === 'rejected' ? 'bg-red-50 border-red-500' : 'hover:bg-gray-50'; ?> flex items-center px-4 py-2 border rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-times-circle mr-2"></i>Ditolak
            </a>
        </div>

        <!-- Mobile Filter -->
        <div class="sm:hidden">
            <select onchange="window.location.href='?status=' + this.value"
                    class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                <option value="borrowed" <?php echo $status_filter === 'borrowed' ? 'selected' : ''; ?>>Dipinjam</option>
                <option value="returned" <?php echo $status_filter === 'returned' ? 'selected' : ''; ?>>Dikembalikan</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>
    </div>

    <!-- Borrowings List -->
    <?php if ($borrowings): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($borrowings as $borrowing): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="p-4 border-b">
                        <!-- Book info -->
                        <div class="flex space-x-4">
                            <div class="w-24 h-32 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                <?php
                                $cover_path = $borrowing['cover_image'] ? "/uploads/covers/" . $borrowing['cover_image'] : '';
                                if ($borrowing['cover_image'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $cover_path)):
                                ?>
                                    <img src="<?php echo $cover_path; ?>"
                                         alt="<?php echo htmlspecialchars($borrowing['book_title']); ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-book text-gray-400 text-3xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2">
                                    <?php echo htmlspecialchars($borrowing['book_title']); ?>
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-user-edit mr-1"></i>
                                    <?php echo htmlspecialchars($borrowing['author']); ?>
                                </p>

                                <!-- Status Badge -->
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php
                                    switch($borrowing['status']) {
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'borrowed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'returned':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'rejected':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                    }
                                    ?>">
                                    <i class="<?php
                                        switch($borrowing['status']) {
                                            case 'pending':
                                                echo 'fas fa-clock';
                                                break;
                                            case 'borrowed':
                                                echo 'fas fa-book-reader';
                                                break;
                                            case 'returned':
                                                echo 'fas fa-check-circle';
                                                break;
                                            case 'rejected':
                                                echo 'fas fa-times-circle';
                                                break;
                                        }
                                        ?> mr-1"></i>
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
                                        case 'rejected':
                                            echo 'Ditolak';
                                            break;
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Dates Section -->
                    <div class="p-4 bg-gray-50">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">
                                    <i class="fas fa-calendar-plus mr-1"></i>
                                    Dipinjam:
                                </p>
                                <p class="font-medium">
                                    <?php echo date('d/m/Y', strtotime($borrowing['borrow_date'])); ?>
                                </p>
                            </div>

                            <?php if ($borrowing['status'] === 'borrowed'): ?>
                                <div>
                                    <p class="text-gray-600">
                                        <i class="fas fa-calendar-times mr-1"></i>
                                        Tenggat:
                                    </p>
                                    <p class="font-medium">
                                        <?php echo date('d/m/Y', strtotime($borrowing['due_date'])); ?>
                                    </p>
                                    <?php
                                    $due_date = strtotime($borrowing['due_date']);
                                    $now = time();
                                    $days_left = ceil(($due_date - $now) / (60 * 60 * 24));
                                    ?>
                                    <p class="mt-1 <?php echo $days_left <= 3 ? 'text-red-600 font-bold' : 'text-gray-600'; ?>">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?php echo $days_left; ?> hari tersisa
                                    </p>
                                </div>
                            <?php elseif ($borrowing['return_date']): ?>
                                <div>
                                    <p class="text-gray-600">
                                        <i class="fas fa-calendar-check mr-1"></i>
                                        Dikembalikan:
                                    </p>
                                    <p class="font-medium">
                                        <?php echo date('d/m/Y', strtotime($borrowing['return_date'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
            <i class="fas fa-book-open text-gray-400 text-4xl mb-4"></i>
            <p class="text-gray-500 mb-4">Tidak ada riwayat peminjaman</p>
            <a href="books.php"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-search mr-2"></i>
                Lihat Katalog Buku
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation for cards
    const cards = document.querySelectorAll('.bg-white');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Animate status badges
    const badges = document.querySelectorAll('.rounded-full');
    badges.forEach(badge => {
        badge.addEventListener('mouseenter', () => {
            badge.style.transform = 'scale(1.05)';
        });
        badge.addEventListener('mouseleave', () => {
            badge.style.transform = 'scale(1)';
        });
    });
});
</script>