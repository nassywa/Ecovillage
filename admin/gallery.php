
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Galeri Desa</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .field {
            margin-bottom: 15px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button {
            border: none;
            padding: 11px 17px;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            background: #176b45;
        }

        button:hover {
            opacity: 0.9;
        }

        .cancel-btn {
            background: #777;
        }

        .delete-btn {
            background: #c0392b;
        }

        .edit-btn {
            background: #2874a6;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 18px;
        }

        .gallery-card {
            border: 1px solid #eee;
            border-radius: 9px;
            overflow: hidden;
        }

        .gallery-card img {
            width: 100%;
            height: 170px;
            object-fit: cover;
            display: block;
        }

        .gallery-content {
            padding: 15px;
        }

        .gallery-content h3 {
            margin: 0 0 8px;
        }

        .gallery-content p {
            color: #666;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .actions button {
            flex: 1;
            padding: 9px;
        }

        .status {
            font-size: 12px;
            color: #176b45;
            font-weight: bold;
        }

        #preview {
            display: none;
            width: 180px;
            height: 120px;
            object-fit: cover;
            margin-top: 10px;
            border-radius: 7px;
        }

        .message {
            margin-bottom: 15px;
            font-weight: bold;
        }

        @media (max-width: 650px) {
            body {
                padding: 15px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Manajemen Galeri Desa</h1>
    <p class="subtitle">Kelola foto dan dokumentasi kegiatan Desa Marga Mulya.</p>

    <div class="panel">

        <h2 id="formTitle">Tambah Foto Galeri</h2>

        <div id="message" class="message"></div>

        <form id="galleryForm">

            <input type="hidden" id="galleryId" name="id">

            <div class="form-grid">

                <div class="field">
                    <label for="title">Judul Foto</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="field">
                    <label for="category">Kategori</label>
                    <select id="category" name="category">
                        <option value="">Pilih kategori</option>
                        <option value="Kegiatan Desa">Kegiatan Desa</option>
                        <option value="Wisata">Wisata</option>
                        <option value="Budaya">Budaya</option>
                        <option value="Pertanian">Pertanian</option>
                        <option value="Infrastruktur">Infrastruktur</option>
                        <option value="UMKM">UMKM</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="field full">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description"></textarea>
                </div>

                <div class="field">
                    <label for="image">Foto Galeri</label>
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <img id="preview" alt="Preview foto">
                    <small>Maksimal 5 MB. JPG, PNG, atau WEBP.</small>
                </div>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Draft">Draft</option>
                        <option value="Published">Published</option>
                    </select>
                </div>

                <div class="field">
                    <label for="display_order">Urutan Tampilan</label>
                    <input
                        type="number"
                        id="display_order"
                        name="display_order"
                        value="0"
                        min="0"
                    >
                </div>

            </div>

            <button type="submit" id="submitButton">Simpan Foto</button>
            <button type="button" class="cancel-btn" id="cancelButton" style="display:none;">
                Batal Edit
            </button>

        </form>

    </div>

    <div class="panel">

        <h2>Daftar Foto Galeri</h2>

        <div id="galleryList" class="gallery-grid">
            Memuat data galeri...
        </div>

    </div>

</div>

<script>
const form = document.getElementById('galleryForm');
const message = document.getElementById('message');
const galleryList = document.getElementById('galleryList');
const imageInput = document.getElementById('image');
const preview = document.getElementById('preview');

let galleryData = [];

// Escape teks dari database sebelum ditampilkan sebagai HTML
function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, function(char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char];
    });
}

// Preview foto
imageInput.addEventListener('change', function() {
    const file = this.files[0];

    if (!file) {
        preview.style.display = 'none';
        return;
    }

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (!allowedTypes.includes(file.type) || file.size > 5 * 1024 * 1024) {
        alert('Pilih foto JPG, PNG, atau WEBP maksimal 5 MB.');
        this.value = '';
        preview.style.display = 'none';
        return;
    }

    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
});

// Ambil data galeri
async function loadGallery() {
    galleryList.textContent = 'Memuat data galeri...';

    try {
        const response = await fetch('../app/list_gallery.php');
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengambil data galeri.');
        }

        galleryData = result.data || [];

        if (galleryData.length === 0) {
            galleryList.textContent = 'Belum ada foto galeri.';
            return;
        }

        galleryList.innerHTML = galleryData.map(item => `
            <div class="gallery-card">
                <img
                    src="../${encodeURI(item.image)}"
                    alt="${escapeHTML(item.title)}"
                >

                <div class="gallery-content">
                    <h3>${escapeHTML(item.title)}</h3>
                    <p>${escapeHTML(item.category || 'Tanpa kategori')}</p>
                    <p>${escapeHTML(item.description || 'Tidak ada deskripsi')}</p>
                    <span class="status">${escapeHTML(item.status)}</span>

                    <div class="actions">
                        <button
                            class="edit-btn"
                            onclick="editGallery(${Number(item.id)})"
                        >
                            Edit
                        </button>

                        <button
                            class="delete-btn"
                            onclick="deleteGallery(${Number(item.id)})"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

    } catch (error) {
        galleryList.textContent = error.message;
    }
}

// Tambah atau edit galeri
form.addEventListener('submit', async function(event) {
    event.preventDefault();

    const id = document.getElementById('galleryId').value;
    const formData = new FormData(form);

    const endpoint = id
        ? '../app/update_gallery.php'
        : '../app/save_gallery.php';

    if (id) {
        formData.set('id', id);
    }

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menyimpan galeri.');
        }

        message.textContent = result.message;
        message.style.color = 'green';

        resetForm();
        await loadGallery();

    } catch (error) {
        message.textContent = error.message;
        message.style.color = 'red';
    }
});

// Isi form untuk edit
function editGallery(id) {
    const item = galleryData.find(gallery => Number(gallery.id) === id);

    if (!item) return;

    document.getElementById('galleryId').value = item.id;
    document.getElementById('title').value = item.title;
    document.getElementById('category').value = item.category || '';
    document.getElementById('description').value = item.description || '';
    document.getElementById('status').value = item.status;
    document.getElementById('display_order').value = item.display_order;

    imageInput.required = false;

    document.getElementById('formTitle').textContent = 'Edit Foto Galeri';
    document.getElementById('submitButton').textContent = 'Simpan Perubahan';
    document.getElementById('cancelButton').style.display = 'inline-block';

    preview.src = '../' + item.image;
    preview.style.display = 'block';

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Hapus galeri
async function deleteGallery(id) {
    if (!confirm('Apakah kamu yakin ingin menghapus foto galeri ini?')) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);

    try {
        const response = await fetch('../app/delete_gallery.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menghapus galeri.');
        }

        message.textContent = result.message;
        message.style.color = 'green';

        resetForm();
        await loadGallery();

    } catch (error) {
        message.textContent = error.message;
        message.style.color = 'red';
    }
}

// Reset form
function resetForm() {
    form.reset();

    document.getElementById('galleryId').value = '';
    document.getElementById('formTitle').textContent = 'Tambah Foto Galeri';
    document.getElementById('submitButton').textContent = 'Simpan Foto';
    document.getElementById('cancelButton').style.display = 'none';

    imageInput.required = true;
    preview.style.display = 'none';
    preview.removeAttribute('src');
}

document.getElementById('cancelButton').addEventListener('click', resetForm);

// Jalankan saat halaman dibuka
loadGallery();
</script>

</body>
</html>
