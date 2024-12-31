<?php
require_once '../config/database.php';
require_once '../includes/user_header.php';

// Pagination and search logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

try {
    // Base query without search
    $where_sql = '';
    $params = [];

    // Add search if provided
    if ($search) {
        $where_sql = "WHERE (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
        $search_term = "%$search%";
        $params = [$search_term, $search_term, $search_term];
    }

    // Get total count of books
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM books $where_sql");
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $total_books = $stmt->fetchColumn();
    $total_pages = ceil($total_books / $limit);

    // Get books for current page
    $sql = "SELECT * FROM books $where_sql ORDER BY title ASC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);

    if (!empty($params)) {
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $books = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "<div class='max-w-7xl mx-auto px-4 py-8'>
            <div class='bg-red-50 border-l-4 border-red-400 p-4 rounded-md'>
                <div class='flex'>
                    <div class='flex-shrink-0'>
                        <i class='fas fa-exclamation-circle text-red-400'></i>
                    </div>
                    <div class='ml-3'>
                        <p class='text-red-700'>Maaf, terjadi kesalahan. Silakan coba lagi nanti.</p>
                    </div>
                </div>
            </div>
          </div>";
    exit;
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Search Form -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="relative">
            <input type="text"
                   id="searchInput"
                   placeholder="Cari judul buku..."
                   class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <!-- Loading indicator -->
            <div id="searchLoading" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                <i class="fas fa-circle-notch fa-spin text-blue-500"></i>
            </div>
        </div>
    </div>

    <!-- Books Grid -->
    <div id="booksGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Book cards will be inserted here -->
    </div>

    <!-- No results message -->
    <div id="noResults" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-search text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-yellow-800 font-medium">Tidak ada hasil</h3>
                <p class="text-yellow-700 mt-1">
                    Tidak ditemukan buku yang sesuai dengan pencarian
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const booksGrid = document.getElementById('booksGrid');
    const noResults = document.getElementById('noResults');
    const searchLoading = document.getElementById('searchLoading');
    let searchTimeout;

    // Function to create book card HTML
    function createBookCard(book) {
        // Tentukan path cover image
        const coverPath = book.cover_image
            ? `/uploads/covers/${book.cover_image}`  // Sesuaikan dengan path folder Anda
            : '';

        return `
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                <div class="aspect-w-3 aspect-h-4 relative">
                    ${book.cover_image
                        ? `<img src="${coverPath}" alt="${book.title}" class="w-full h-64 object-cover rounded-t-lg">`
                        : `<div class="w-full h-64 bg-gray-100 flex items-center justify-center rounded-t-lg">
                             <i class="fas fa-book text-gray-400 text-4xl"></i>
                           </div>`
                    }
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-1 text-sm rounded-full ${book.available_copies > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${book.available_copies} tersedia
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                        ${book.title}
                    </h3>
                    <p class="text-gray-600 mb-4">
                        <i class="fas fa-user-edit mr-2 text-gray-400"></i>
                        ${book.author}
                    </p>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">
                            ${book.category}
                        </span>
                        <span class="text-sm text-gray-500">
                            ISBN: ${book.isbn}
                        </span>
                    </div>
                    <button onclick="window.location.href='borrow.php?book_id=${book.id}'"
                            class="w-full py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors
                            ${book.available_copies === 0 ? 'opacity-50 cursor-not-allowed' : ''}"
                            ${book.available_copies === 0 ? 'disabled' : ''}>
                        ${book.available_copies > 0
                            ? '<i class="fas fa-book-reader mr-2"></i>Pinjam Buku'
                            : '<i class="fas fa-clock mr-2"></i>Tidak Tersedia'
                        }
                    </button>
                </div>
            </div>
        `;
    }

    // Function to perform search
    function performSearch() {
        const searchTerm = searchInput.value.trim();

        // Show loading
        searchLoading.classList.remove('hidden');

        // Fetch results
        fetch('search.php?search=' + encodeURIComponent(searchTerm))
            .then(response => response.json())
            .then(data => {
                console.log('Data received:', data); // Debug: log data

                if (data.success) {
                    // Clear current results
                    booksGrid.innerHTML = '';

                    if (data.data && data.data.length > 0) {
                        // Show results
                        data.data.forEach(book => {
                            booksGrid.insertAdjacentHTML('beforeend', createBookCard(book));
                        });
                        booksGrid.classList.remove('hidden');
                        noResults.classList.add('hidden');
                    } else {
                        // Show no results message
                        booksGrid.classList.add('hidden');
                        noResults.classList.remove('hidden');
                    }
                } else {
                    console.error('Error fetching results');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                // Hide loading
                searchLoading.classList.add('hidden');
            });
    }

    // Debounce search input
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 300);
    });

    // Initial load of all books
    performSearch();
});
</script>