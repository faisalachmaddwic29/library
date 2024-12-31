<?php
// auth/register.php
require_once '../config/database.php';

if (isLoggedIn()) {
    redirect('/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $errors = [];

    if (strlen($username) < 3) {
        $errors[] = 'Username minimal 3 karakter';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid';
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email sudah terdaftar';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    }

    if ($password !== $password_confirm) {
        $errors[] = 'Password tidak cocok';
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);

            redirect('auth/login.php', 'Registrasi berhasil! Silakan login.', 'success');
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Perpustakaan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="min-h-screen bg-gradient-to-r from-blue-100 via-blue-50 to-white">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-xl rounded-xl sm:px-10 border border-gray-100">
                <div class="sm:mx-auto sm:w-full sm:max-w-md mb-6">
                    <h2 class="text-center text-3xl font-extrabold text-gray-900">Buat Akun Baru</h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Sudah punya akun?
                        <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                            Masuk sekarang
                        </a>
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <?php foreach ($errors as $error): ?>
                                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form class="space-y-6" action="register.php" method="POST">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" name="username" id="username" required
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm transition-all"
                                   placeholder="Masukkan username"
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="email" id="email" required
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm transition-all"
                                   placeholder="Masukkan email"
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password" id="password" required
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm transition-all"
                                   placeholder="Masukkan password">
                        </div>
                        <div id="password-strength" class="mt-1 text-xs"></div>
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password_confirm" id="password_confirm" required
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm transition-all"
                                   placeholder="Masukkan ulang password">
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-user-plus text-blue-500 group-hover:text-blue-400"></i>
                            </span>
                            Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Atau</span>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="/" class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password strength indicator
        const password = document.getElementById('password');
        const strengthDiv = document.getElementById('password-strength');

        password.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;
            let message = '';

            // Minimal length check
            if (value.length >= 6) strength++;

            // Uppercase letters check
            if (value.match(/[A-Z]/)) strength++;

            // Lowercase letters check
            if (value.match(/[a-z]/)) strength++;

            // Numbers check
            if (value.match(/[0-9]/)) strength++;

            // Special characters check
            if (value.match(/[^A-Za-z0-9]/)) strength++;

            // Update strength message
            switch(strength) {
                case 0:
                    message = '<span class="text-red-500">Sangat Lemah</span>';
                    break;
                case 1:
                    message = '<span class="text-red-500">Lemah</span>';
                    break;
                case 2:
                    message = '<span class="text-yellow-500">Sedang</span>';
                    break;
                case 3:
                    message = '<span class="text-blue-500">Bagus</span>';
                    break;
                case 4:
                case 5:
                    message = '<span class="text-green-500">Sangat Kuat</span>';
                    break;
            }

            strengthDiv.innerHTML = 'Kekuatan Password: ' + message;
        });

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirm');
            const usernameInput = document.getElementById('username');

            if (usernameInput.value.length < 3) {
                e.preventDefault();
                alert('Username minimal 3 karakter!');
                return;
            }

            if (passwordInput.value.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return;
            }

            if (passwordInput.value !== passwordConfirmInput.value) {
                e.preventDefault();
                alert('Password tidak cocok!');
                return;
            }
        });

        // Auto hide flash messages
        const flashMessage = document.querySelector('[role="alert"]');
        if (flashMessage) {
            setTimeout(() => {
                flashMessage.style.transition = 'all 0.5s ease';
                flashMessage.style.opacity = '0';
                flashMessage.style.transform = 'translateY(-10px)';
                setTimeout(() => flashMessage.remove(), 500);
            }, 4000);
        }
    });
    </script>
</body>
</html>