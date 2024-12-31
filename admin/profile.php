<?php
// admin/profile.php
require_once '../config/database.php';
requireAdmin();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Basic validation
    if (strlen($username) < 3) {
        $errors[] = 'Username minimal 3 karakter';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }
    
    // Check if email is taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = 'Email sudah digunakan';
    }
    
    // Password validation
    if ($new_password) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Password saat ini tidak sesuai';
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = 'Password baru minimal 6 karakter';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'Konfirmasi password tidak sesuai';
        }
    }
    
    if (empty($errors)) {
        try {
            if ($new_password) {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, password = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $username,
                    $email,
                    password_hash($new_password, PASSWORD_DEFAULT),
                    $user_id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $user_id]);
            }
            
            $_SESSION['username'] = $username;
            redirect('/admin/profile.php', 'Profil berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan saat memperbarui profil.';
        }
    }
}

$page_title = "Profil Saya";
require_once '../includes/admin_header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="flex items-center space-x-6 mb-8">
                <img class="h-24 w-24 rounded-full" 
                     src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&size=96&background=random" 
                     alt="Profile">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="text-sm text-gray-500">Bergabung sejak <?php echo date('d F Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" 
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ubah Password</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">
                                Password Saat Ini
                            </label>
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">
                                Password Baru
                            </label>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                                Konfirmasi Password Baru
                            </label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="library/admin/dashboard.php" 
                       class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>