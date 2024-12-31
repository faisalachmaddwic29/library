<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-gradient-to-b from-blue-800 to-blue-900 text-white transition-transform duration-300 ease-in-out z-30 shadow-xl">
    <!-- Profile & Brand Section -->
    <div class="px-6 py-8 border-b border-blue-700">
        <div class="flex items-center space-x-4 mb-6">
            <div class="h-12 w-12 rounded-full bg-blue-700 flex items-center justify-center">
                <i class="fas fa-book-reader text-2xl text-white"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold">Library App</h2>
                <p class="text-sm text-blue-300">Admin Panel</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="px-4 py-6">
        <!-- Dashboard Link -->
        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" 
           class="block mb-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_page === 'dashboard' ? 'bg-blue-700 shadow-lg' : 'hover:bg-blue-700/50'; ?>">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg bg-blue-600/50 flex items-center justify-center mr-3">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="font-medium">Dashboard</span>
            </div>
        </a>

        <!-- Data Management Section -->
        <div class="mb-6">
            <h3 class="px-4 text-xs font-semibold text-blue-400 uppercase tracking-wider mb-3">
                Manajemen Data
            </h3>

            <!-- Books Link -->
            <a href="<?php echo BASE_URL; ?>/admin/books/index.php" 
               class="block mb-2 px-4 py-3 rounded-lg transition-all duration-200 <?php echo strpos($current_page, 'book') !== false ? 'bg-blue-700 shadow-lg' : 'hover:bg-blue-700/50'; ?>">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/50 flex items-center justify-center mr-3">
                        <i class="fas fa-book"></i>
                    </div>
                    <span class="font-medium">Buku</span>
                </div>
            </a>

            <!-- Users Link -->
            <a href="<?php echo BASE_URL; ?>/admin/users/index.php" 
               class="block mb-2 px-4 py-3 rounded-lg transition-all duration-200 <?php echo strpos($current_page, 'user') !== false ? 'bg-blue-700 shadow-lg' : 'hover:bg-blue-700/50'; ?>">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/50 flex items-center justify-center mr-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="font-medium">Pengguna</span>
                </div>
            </a>

            <!-- Borrowings Link -->
            <a href="<?php echo BASE_URL; ?>/admin/borrowings/index.php" 
               class="block mb-2 px-4 py-3 rounded-lg transition-all duration-200 <?php echo strpos($current_page, 'borrowing') !== false ? 'bg-blue-700 shadow-lg' : 'hover:bg-blue-700/50'; ?>">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/50 flex items-center justify-center mr-3">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <span class="font-medium">Peminjaman</span>
                </div>
            </a>
        </div>

        <!-- Account Section -->
        <div class="mt-auto">
            <h3 class="px-4 text-xs font-semibold text-blue-400 uppercase tracking-wider mb-3">
                Akun
            </h3>

            <!-- Profile Link -->
            <a href="<?php echo BASE_URL; ?>/admin/profile.php" 
               class="block mb-2 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_page === 'profile' ? 'bg-blue-700 shadow-lg' : 'hover:bg-blue-700/50'; ?>">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/50 flex items-center justify-center mr-3">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="font-medium">Profil</span>
                </div>
            </a>

            <!-- Logout Link -->
            <a href="<?php echo BASE_URL; ?>/auth/logout.php" 
               class="block px-4 py-3 rounded-lg transition-all duration-200 hover:bg-red-500/20 text-red-400 hover:text-red-300">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center mr-3">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="font-medium">Logout</span>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Mobile overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden backdrop-blur-sm"></div>

<!-- Toggle button for mobile -->
<button id="toggleSidebar" class="fixed lg:hidden bottom-6 right-6 bg-blue-600 text-white p-4 rounded-full shadow-lg z-30 hover:bg-blue-700 transition-colors duration-200">
    <i class="fas fa-bars text-lg"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    const closeBtn = document.getElementById('closeSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden'); // Prevent scrolling when sidebar is open
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    toggleBtn.addEventListener('click', openSidebar);
    overlay.addEventListener('click', closeSidebar);

    // Close sidebar on window resize if in mobile view
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
        }
    });

    // Add hover effect for menu items
    const menuItems = document.querySelectorAll('#sidebar a');
    menuItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.classList.add('transform', 'scale-[1.02]');
        });
        item.addEventListener('mouseleave', function() {
            this.classList.remove('transform', 'scale-[1.02]');
        });
    });
});
</script>