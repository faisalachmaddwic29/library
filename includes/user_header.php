<?php
// includes/user_header.php
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php', 'Anda harus login untuk mengakses halaman ini.', 'error');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Perpustakaan Digital'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/user/dashboard.php" class="text-2xl text-blue-600">
                            <i class="fas fa-book-reader"></i>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/user/dashboard.php"
                           class="<?php echo $current_page === 'dashboard' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="/user/books.php"
                           class="<?php echo $current_page === 'books' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Katalog Buku
                        </a>
                        <a href="/user/borrowings.php"
                           class="<?php echo $current_page === 'borrowings' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Peminjaman Saya
                        </a>
                    </div>
                </div>

                <!-- Right side -->
                <div class="flex items-center">
                    <!-- User dropdown -->
                    <div class="ml-3 relative">
                        <div>
                            <button type="button"
                                    id="userMenuBtn"
                                    class="flex items-center space-x-3 text-gray-700 hover:text-gray-900 focus:outline-none">
                                <span class="hidden md:block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <img class="h-8 w-8 rounded-full"
                                     src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username']); ?>&background=random"
                                     alt="Profile">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                        </div>
                        <div id="userDropdown"
                             class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <a href="/user/profile.php"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <a href="/auth/logout.php"
                               class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div id="flashMessage"
             class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-md p-4 <?php echo $_SESSION['flash']['type'] === 'success' ? 'bg-green-100' : 'bg-red-100'; ?>">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <?php if ($_SESSION['flash']['type'] === 'success'): ?>
                            <i class="fas fa-check-circle text-green-400"></i>
                        <?php else: ?>
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm <?php echo $_SESSION['flash']['type'] === 'success' ? 'text-green-800' : 'text-red-800'; ?>">
                            <?php echo htmlspecialchars($_SESSION['flash']['message']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"></main>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    // Toggle dropdown when clicking the button
    userMenuBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!userMenuBtn.contains(e.target)) {
            userDropdown.classList.add('hidden');
        }
    });
});

// Auto hide flash messages after 3 seconds
const flashMessage = document.getElementById('flashMessage');
if (flashMessage) {
    setTimeout(() => {
        flashMessage.style.transition = 'opacity 0.5s ease';
        flashMessage.style.opacity = '0';
        setTimeout(() => flashMessage.remove(), 500);
    }, 3000);
}
</script>