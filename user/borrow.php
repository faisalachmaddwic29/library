<?php
// user/borrow.php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$book_id = $_GET['book_id'] ?? null;
if (!$book_id) {
    redirect('/user/books.php', 'ID buku tidak valid.', 'error');
}

// Get book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    redirect('/user/books.php', 'Buku tidak ditemukan.', 'error');
}

// Check if book is available
if ($book['available_copies'] < 1) {
    redirect('/user/books.php', 'Buku tidak tersedia untuk dipinjam.', 'error');
}

// Check if user already has active borrowing for this book
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM borrowings
    WHERE user_id = ? AND book_id = ?
    AND status IN ('pending', 'borrowed')
");
$stmt->execute([$_SESSION['user_id'], $book_id]);
if ($stmt->fetchColumn() > 0) {
    redirect('/user/books.php', 'Anda sudah meminjam atau mengajukan peminjaman buku ini.', 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Set due date to 14 days from now
        $due_date = date('Y-m-d H:i:s', strtotime('+14 days'));

        $stmt = $pdo->prepare("
            INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, status)
            VALUES (?, ?, NOW(), ?, 'pending')
        ");
        $stmt->execute([$_SESSION['user_id'], $book_id, $due_date]);

        redirect('/user/borrowings.php', 'Permintaan peminjaman berhasil diajukan.', 'success');
    } catch (PDOException $e) {
        $error = 'Terjadi kesalahan saat mengajukan peminjaman.';
    }
}

$page_title = "Pinjam Buku";
$current_page = 'books';
require_once '../includes/user_header.php';
?>

<div class="min-h-screen flex flex-col">
    <div class="flex-grow">
        <div class="max-w-2xl mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center space-x-6 mb-6">
                        <!-- Updated book cover path -->
                        <div class="h-32 w-24 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                            <?php
                            $cover_path = $book['cover_image'] ? "/uploads/covers/" . $book['cover_image'] : '';
                            if ($book['cover_image'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $cover_path)):
                            ?>
                                <img src="<?php echo $cover_path; ?>"
                                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                                     class="h-full w-full object-cover">
                            <?php else: ?>
                                <div class="h-full w-full flex items-center justify-center">
                                    <i class="fas fa-book text-gray-400 text-3xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                <!-- Book info -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </h2>
                    <p class="text-gray-600"><?php echo htmlspecialchars($book['author']); ?></p>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo htmlspecialchars($book['category']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Borrowing details -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Peminjaman</h3>
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Tanggal Peminjaman:</span> <?php echo date('d F Y'); ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Tenggat Pengembalian:</span> <?php echo date('d F Y', strtotime('+14 days')); ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Status:</span> Menunggu Persetujuan
                    </p>
                </div>
            </div>

            <!-- Form submission -->
            <form method="POST" class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">
                        Dengan mengajukan peminjaman, Anda setuju untuk:
                    </p>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600 list-disc list-inside">
                        <li>Mengembalikan buku sebelum tenggat waktu</li>
                        <li>Menjaga kondisi buku tetap baik</li>
                        <li>Mematuhi peraturan perpustakaan</li>
                    </ul>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="/user/books.php"
                       class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Ajukan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
