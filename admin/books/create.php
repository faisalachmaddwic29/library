<?php
// admin/books/create.php
require_once '../../config/database.php';
requireAdmin(); // Sudah mencakup pengecekan login dan admin, jadi hapus pengecekan dobel

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $total_copies = (int)($_POST['total_copies'] ?? 1);
    
    $errors = [];
    
    // Basic validation
    if (empty($title)) $errors[] = 'Judul buku wajib diisi';
    if (empty($author)) $errors[] = 'Penulis wajib diisi';
    if (empty($isbn)) $errors[] = 'ISBN wajib diisi';
    if ($total_copies < 1) $errors[] = 'Jumlah copy minimal 1';
    
    // Handle file upload
    $cover_image = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['cover_image']['name']);
        $allowed_types = ['jpg', 'jpeg', 'png'];
        
        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            $errors[] = 'Format file harus JPG, JPEG, atau PNG';
        } else {
            // Perbaiki path upload dengan ROOT_PATH
            $upload_dir = ROOT_PATH . '/uploads/covers/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $cover_image = uniqid() . '.' . $file_info['extension'];
            $destination = $upload_dir . $cover_image;
            
            if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $destination)) {
                $errors[] = 'Gagal mengupload file. Error: ' . error_get_last()['message'];
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO books (title, author, isbn, category, description, cover_image, total_copies, available_copies) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $author, $isbn, $category, $description, $cover_image, 
                $total_copies, $total_copies
            ]);
            
            redirect('/admin/books/index.php', 'Buku berhasil ditambahkan.', 'success');
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan saat menyimpan data.';
            if ($cover_image && file_exists($upload_dir . $cover_image)) {
                unlink($upload_dir . $cover_image);
            }
        }
    }
}

$page_title = "Tambah Buku Baru";
require_once '../../includes/admin_header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="create.php" method="POST" enctype="multipart/form-data" class="p-6">
            <?php if (!empty($errors)): ?>
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Buku</label>
                    <input type="text" id="title" name="title" required
                           value="<?php echo htmlspecialchars($title ?? ''); ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="author" class="block text-sm font-medium text-gray-700 mb-2">Penulis</label>
                    <input type="text" id="author" name="author" required
                           value="<?php echo htmlspecialchars($author ?? ''); ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="isbn" class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                    <input type="text" id="isbn" name="isbn" required
                           value="<?php echo htmlspecialchars($isbn ?? ''); ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select id="category" name="category" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Kategori</option>
                        <option value="Fiksi" <?php echo ($category ?? '') === 'Fiksi' ? 'selected' : ''; ?>>Fiksi</option>
                        <option value="Non-Fiksi" <?php echo ($category ?? '') === 'Non-Fiksi' ? 'selected' : ''; ?>>Non-Fiksi</option>
                        <option value="Pendidikan" <?php echo ($category ?? '') === 'Pendidikan' ? 'selected' : ''; ?>>Pendidikan</option>
                        <option value="Teknologi" <?php echo ($category ?? '') === 'Teknologi' ? 'selected' : ''; ?>>Teknologi</option>
                        <option value="Bisnis" <?php echo ($category ?? '') === 'Bisnis' ? 'selected' : ''; ?>>Bisnis</option>
                        <option value="Lainnya" <?php echo ($category ?? '') === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                </div>
                
                <div>
                    <label for="total_copies" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Copy</label>
                    <input type="number" id="total_copies" name="total_copies" required min="1"
                           value="<?php echo htmlspecialchars($total_copies ?? 1); ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-2">Cover Buku</label>
                    <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           onchange="previewImage(this);">
                    <div id="imagePreview" class="mt-2 hidden">
                        <img src="" alt="Preview" class="max-h-32 rounded">
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="index.php"
                   class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewImg.src = '';
        preview.classList.add('hidden');
    }
}
</script>

<?php require_once '../../includes/admin_footer.php'; ?>