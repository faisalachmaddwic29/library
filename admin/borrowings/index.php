<?php
// admin/borrowings/index.php
require_once '../../config/database.php';
requireAdmin();

// Handle actions (approve, reject, return)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrowing_id = $_POST['borrowing_id'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($borrowing_id && $action) {
        try {
            switch ($action) {
                case 'approve':
                    $stmt = $pdo->prepare("
                        UPDATE borrowings b
                        JOIN books bk ON b.book_id = bk.id
                        SET b.status = 'borrowed',
                            b.approved_at = NOW(),
                            bk.available_copies = bk.available_copies - 1
                        WHERE b.id = ? AND b.status = 'pending'
                    ");
                    $stmt->execute([$borrowing_id]);
                    redirect('/admin/borrowings/index.php', 'Peminjaman berhasil disetujui.', 'success');
                    break;

                case 'reject':
                    $stmt = $pdo->prepare("
                        UPDATE borrowings
                        SET status = 'rejected',
                            return_date = NOW()
                        WHERE id = ? AND status = 'pending'
                    ");
                    $stmt->execute([$borrowing_id]);
                    redirect('/admin/borrowings/index.php', 'Peminjaman ditolak.', 'success');
                    break;

                case 'return':
                    $stmt = $pdo->prepare("
                        UPDATE borrowings b
                        JOIN books bk ON b.book_id = bk.id
                        SET b.status = 'returned',
                            b.return_date = NOW(),
                            bk.available_copies = bk.available_copies + 1
                        WHERE b.id = ? AND b.status = 'borrowed'
                    ");
                    $stmt->execute([$borrowing_id]);
                    redirect('/admin/borrowings/index.php', 'Buku berhasil dikembalikan.', 'success');
                    break;
            }
        } catch (PDOException $e) {
            redirect('/admin/borrowings/index.php', 'Terjadi kesalahan: ' . $e->getMessage(), 'error');
        }
    }
}

// Filters and pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_clauses = [];
$params = [];

if ($status_filter) {
    $where_clauses[] = "b.status = :status";
    $params[':status'] = $status_filter;
}

if ($search) {
    $where_clauses[] = "(u.username LIKE :search OR bk.title LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get total borrowings
$count_sql = "
    SELECT COUNT(*)
    FROM borrowings b
    JOIN users u ON b.user_id = u.id
    JOIN books bk ON b.book_id = bk.id
    $where_sql
";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_borrowings = $stmt->fetchColumn();
$total_pages = ceil($total_borrowings / $limit);

// Get borrowings with pagination
$sql = "
    SELECT b.*, u.username, bk.title as book_title, bk.author
    FROM borrowings b
    JOIN users u ON b.user_id = u.id
    JOIN books bk ON b.book_id = bk.id
    $where_sql
    ORDER BY b.borrow_date DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
// foreach ($params as $i => $param) {
//     $stmt->bindValue($i + 1, $param);
// }
// Bind the other parameters dynamically based on the $params array
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$borrowings = $stmt->fetchAll();

$page_title = "Manajemen Peminjaman";
require_once '../../includes/admin_header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Custom Select2 Styles */
    .select2-container--default .select2-selection--single {
        border: 1px solid #e5e7eb;
        /* Border seperti form-control Bootstrap */
        border-radius: .5rem;
        /* Rounded border */
        padding: 0.5rem .75rem;
        /* Padding seperti form-control */
        height: auto;
        font-size: 1rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        /* Adjusting line height */
    }

    .select2-container--default .select2-dropdown {
        border: 1px solid #e5e7eb;
        border-radius: .5rem;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-results__option {
        padding: 0.5rem 1rem;
        font-size: 1rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 8px !important;
    }
</style>
<!-- Filters and Search -->
<div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
    <div class="flex items-center gap-4">
        <form class="flex items-center gap-4">
            <select name="status" id="status" class="select2 pl-10 pr-4 py-2 border rounded-lg w-64 focus:ring-blue-500 focus:border-blue-500">
                <option value="" selected>Semua Status</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                <option value="borrowed" <?php echo $status_filter === 'borrowed' ? 'selected' : ''; ?>>Dipinjam</option>
                <option value="returned" <?php echo $status_filter === 'returned' ? 'selected' : ''; ?>>Dikembalikan</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
            </select>

            <div class="relative">
                <input type="text"
                    name="search"
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Cari peminjaman..."
                    class="pl-10 pr-4 py-2 border rounded-lg w-64 focus:ring-blue-500 focus:border-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>

            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Filter
            </button>
        </form>
    </div>
</div>

<!-- Borrowings Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buku</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Pinjam</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tenggat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($borrowings as $borrowing): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0">
                                <img class="h-10 w-10 rounded-full"
                                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($borrowing['username']); ?>"
                                    alt="<?php echo htmlspecialchars($borrowing['username']); ?>">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($borrowing['username']); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($borrowing['book_title']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($borrowing['author']); ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?php echo date('d/m/Y', strtotime($borrowing['borrow_date'])); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?php echo date('d/m/Y', strtotime($borrowing['due_date'])); ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        <?php
                        switch ($borrowing['status']) {
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
                            <?php
                            switch ($borrowing['status']) {
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
                    </td>
                    <td class="px-6 py-4 text-sm font-medium">
                        <?php if ($borrowing['status'] === 'pending'): ?>
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="borrowing_id" value="<?php echo $borrowing['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="text-green-600 hover:text-green-900 mr-3">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="borrowing_id" value="<?php echo $borrowing['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        <?php elseif ($borrowing['status'] === 'borrowed'): ?>
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="borrowing_id" value="<?php echo $borrowing['id']; ?>">
                                <input type="hidden" name="action" value="return">
                                <button type="submit" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                        <?php endif; ?>
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
                        <span class="font-medium"><?php echo min($offset + $limit, $total_borrowings); ?></span>
                        dari
                        <span class="font-medium"><?php echo $total_borrowings; ?></span>
                        hasil
                    </p>
                </div>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"
                            class="px-3 py-2 border rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"
                            class="px-3 py-2 border rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    console.log('test');
    $(document).ready(function() {
        $('#status').select2({
            // placeholder: "Pilih Status",
            // allowClear: true
        });
    });
</script>

<?php require_once '../../includes/admin_footer.php'; ?>