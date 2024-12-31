<?php
require_once __DIR__ . '/../config/database.php';
requireAdmin();

if (!isLoggedIn() || !isAdmin()) {
    redirect('/auth/login.php', 'Anda harus login sebagai admin untuk mengakses halaman ini.', 'error');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel - Perpustakaan Digital'; ?></title>
    <script>
        console.log("Script URL: <?php echo BASE_URL; ?>/assets/js/script.js");
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#475569'
                    }
                }
            }
        }
    </script>
    <!-- <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Transition for sidebar */
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }

        /* Loading spinner */
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Dropdown menu animation */
        .dropdown-animation {
            animation: dropdownFade 0.2s ease-in-out;
        }

        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen transition-all duration-300">
        <!-- Top Navigation -->
        <nav class="bg-white shadow-md">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Left side -->
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 focus:outline-none">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="ml-4 text-xl font-semibold text-gray-800">
                            <?php echo $page_title ?? 'Dashboard'; ?>
                        </h1>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center space-x-4">

                        <!-- User menu -->
                        <div class="relative">
                            <button id="userMenuBtn" class="flex items-center space-x-3 text-gray-700 hover:text-gray-900 focus:outline-none">
                                <span class="hidden md:block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <img class="h-8 w-8 rounded-full bg-gray-300"
                                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username']); ?>&background=random"
                                    alt="Profile">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <!-- User dropdown menu -->
                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 dropdown-animation">
                                <a href="/admin/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profil
                                </a>
                                <a href="/admin/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>Pengaturan
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Breadcrumbs -->
        <?php if (isset($breadcrumbs)): ?>
            <div class="bg-white border-b">
                <div class="mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center space-x-2 h-10 text-sm">
                        <a href="/admin/dashboard.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-home"></i>
                        </a>
                        <?php foreach ($breadcrumbs as $label => $url): ?>
                            <span class="text-gray-400">/</span>
                            <?php if ($url): ?>
                                <a href="<?php echo $url; ?>" class="text-gray-600 hover:text-gray-900"><?php echo $label; ?></a>
                            <?php else: ?>
                                <span class="text-gray-800"><?php echo $label; ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <main class="py-6 px-4 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <div id="flashMessage"
                    class="mb-6 px-4 py-3 rounded-lg <?php echo $_SESSION['flash']['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                    <?php
                    echo htmlspecialchars($_SESSION['flash']['message']);
                    unset($_SESSION['flash']);
                    ?>
                </div>
            <?php endif; ?>