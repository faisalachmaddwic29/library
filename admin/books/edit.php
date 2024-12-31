<?php
// admin/books/edit.php
require_once '../../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/auth/login.php', 'Anda harus login sebagai admin untuk mengakses halaman ini.', 'error');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    redirect('/admin/books/index.php', 'ID buku tidak valid.', 'error');
}

// Get book data
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    redirect('/admin/books/index.php', 'Buku tidak ditemukan.', 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $total_copies = (int)($_POST['total_copies'] ?? 1);
    $remove_cover = isset($_POST['remove_cover']);
    
    $errors = [];
    
    // Basic validation
    if (empty($title)) $errors[] = 'Judul buku wajib diisi';
    if (empty($author)) $errors[] = 'Penulis wajib diisi';
    if (empty($isbn)) $errors[] = 'ISBN wajib diisi';
    if ($total_copies < 1) $errors[] = 'Jumlah copy minimal 1';
    
    // Handle file upload
    $cover_image = $book['cover_image'];
    if ($remove_cover) {
        if ($cover_image) {
            unlink('../../uploads/covers/' . $cover_image);
            $cover_image = null;
        }
    } elseif (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['cover_image']['name']);
        $allowed_types = ['jpg', 'jpeg', 'png'];
        
        if (!in_array(strtolower($file_info['extension']), $allowed_types)) {
            $errors[] = 'Format file harus JPG, JPEG, atau PNG';
        } else {
            $upload_dir = '../../uploads/covers/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Remove old cover if exists
            if ($book['cover_image']) {
                unlink($upload_dir . $book['cover_image']);
            }
            
            $cover_image = uniqid() . '.' . $file_info['extension'];
            $destination = $upload_dir . $cover_image;
            
            if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $destination)) {
                $errors[] = 'Gagal mengupload file';
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE books 
                SET title = ?, author = ?, isbn = ?, category = ?, description = ?, 
                    cover_image = ?, total_copies = ?, available_copies = available_copies + (? - total_copies)
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $author, $isbn, $category, $description, $cover_image, 
                $total_copies, $total_copies, $id
            ]);
            
            redirect('/admin/books/index.php', 'Buku berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan saat menyimpan data.';
        }
    }
}

$page_title = "Edit Buku";
require_once '../../includes/admin_header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="p-6">
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
                           value="<?php echo htmlspecialchars($book['title']); ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="author" class="block text-sm font-medium text-gray-700 mb-2">Penulis</label>
                    <input type="text" id="author" name="author" required
                           value="<?php echo htmlspecialchars($book['author']); ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="isbn" class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                    <input type="text" id="isbn" name="isbn" required
                           value="<?php echo htmlspecialchars($book['isbn']); ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select id="category" name="category" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Kategori</option>
                        <?php
                        $categories = ['Fiksi', 'Non-Fiksi', 'Pendidikan', 'Teknologi', 'Bisnis', 'Lainnya'];
                        foreach ($categories as $cat) {
                            $selected = $book['category'] === $cat ? 'selected' : '';
                            echo "<option value=\"$cat\" $selected>$cat</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label for="total_copies" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Copy</label>
                    <input type="number" id="total_copies" name="total_copies" required min="1"
                           value="<?php echo htmlspecialchars($book['total_copies']); ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cover Buku</label>
                    <?php if ($book['cover_image']): ?>
                        <div class="mb-2">
                            <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                alt="Current cover" 
                                class="h-32 rounded">
                        </div>
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="remove_cover" name="remove_cover" class="mr-2">
                            <label for="remove_cover" class="text-sm text-gray-600">Hapus cover</label>
                        </div>
                    <?php endif; ?>
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
                          class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($book['description']); ?></textarea>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
            <a href="index.php" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Perbarui
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

document.getElementById('remove_cover').addEventListener('change', function() {
    const fileInput = document.getElementById('cover_image');
    fileInput.disabled = this.checked;
});
</script>

<?php require_once '../../includes/admin_footer.php'; ?>