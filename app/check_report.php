
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../public/login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Informasi Desa - EcoVillage</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f5;
            margin: 0;
            padding: 30px;
            color: #26332d;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1, h2 {
            color: #245c40;
        }

        .section {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input, select, textarea {
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
            border-radius: 6px;
            padding: 10px 16px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 15px;
        }

        .primary {
            background: #28784e;
            color: white;
        }

        .edit {
            background: #e6a23c;
            color: white;
            margin-right: 5px;
        }

        .delete {
            background: #d9534f;
            color: white;
        }

        .cancel {
            background: #777;
            color: white;
            margin-left: 5px;
        }

        .information-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 18px;
            margin-top: 15px;
        }

        .information-item h3 {
            margin-top: 0;
            color: #245c40;
        }

        .status {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 5px;
            font-size: 12px;
            background: #eee;
        }

        .message {
            margin-top: 12px;
            font-size: 14px;
        }

        #edit-section {
            display: none;
            border: 2px solid #e6a23c;
        }

        .empty {
            color: #777;
            padding: 15px 0;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>CMS Informasi Desa</h1>
    <p>Kelola data dan informasi Desa Marga Mulya melalui halaman ini.</p>

    <!-- FORM TAMBAH INFORMASI -->
    <div class="section">

        <h2>Tambah Informasi Desa</h2>

        <form id="information-form">

            <label for="category">Kategori</label>
            <select name="category" id="category" required>
                <option value="">Pilih kategori</option>
                <option value="Geografis">Kondisi Geografis</option>
                <option value="Pemerintahan">Pemerintahan Desa</option>
                <option value="Kependudukan">Kependudukan & Angkatan Kerja</option>
                <option value="Agama">Agama</option>
                <option value="Pendidikan">Pendidikan</option>
                <option value="Kesehatan">Kesehatan</option>
                <option value="Pertanian & Peternakan">Pertanian & Peternakan</option>
                <option value="Ekonomi">Ekonomi</option>
                <option value="Infrastruktur">Infrastruktur</option>
                <option value="Budaya & Kesenian">Budaya & Kesenian</option>
                <option value="Wisata">Wisata</option>
                <option value="Organisasi & Kegiatan Rutin">Organisasi & Kegiatan Rutin</option>
                <option value="UMKM & Produk Lokal">UMKM & Produk Lokal</option>
            </select>

            <label for="title">Judul Informasi</label>
            <input type="text" name="title" id="title" required>

            <label for="value">Nilai</label>
            <input type="text" name="value" id="value">

            <label for="unit">Satuan</label>
            <input type="text" name="unit" id="unit" placeholder="Contoh: Hektare, Orang, Unit">

            <label for="description">Deskripsi</label>
            <textarea name="description" id="description"></textarea>

            <label for="reference_year">Tahun Referensi</label>
            <input type="number" name="reference_year" id="reference_year"
                   min="1901" max="2155">

            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="Draft">Draft</option>
                <option value="Published">Published</option>
            </select>

            <label for="display_order">Urutan Tampilan</label>
            <input type="number" name="display_order" id="display_order"
                   value="0" min="0">

            <button type="submit" class="primary">Simpan Informasi</button>

            <div id="form-message" class="message"></div>

        </form>

    </div>


    <!-- FORM EDIT INFORMASI -->
    <div class="section" id="edit-section">

        <h2>Edit Informasi Desa</h2>

        <form id="edit-information-form">

            <input type="hidden" name="id">

            <label>Kategori</label>
            <select name="category" required>
                <option value="">Pilih kategori</option>
                <option value="Geografis">Kondisi Geografis</option>
                <option value="Pemerintahan">Pemerintahan Desa</option>
                <option value="Kependudukan">Kependudukan & Angkatan Kerja</option>
                <option value="Agama">Agama</option>
                <option value="Pendidikan">Pendidikan</option>
                <option value="Kesehatan">Kesehatan</option>
                <option value="Pertanian & Peternakan">Pertanian & Peternakan</option>
                <option value="Ekonomi">Ekonomi</option>
                <option value="Infrastruktur">Infrastruktur</option>
                <option value="Budaya & Kesenian">Budaya & Kesenian</option>
                <option value="Wisata">Wisata</option>
                <option value="Organisasi & Kegiatan Rutin">Organisasi & Kegiatan Rutin</option>
                <option value="UMKM & Produk Lokal">UMKM & Produk Lokal</option>
            </select>

            <label>Judul Informasi</label>
            <input type="text" name="title" required>

            <label>Nilai</label>
            <input type="text" name="value">

            <label>Satuan</label>
            <input type="text" name="unit">

            <label>Deskripsi</label>
            <textarea name="description"></textarea>

            <label>Tahun Referensi</label>
            <input type="number" name="reference_year" min="1901" max="2155">

            <label>Status</label>
            <select name="status" required>
                <option value="Draft">Draft</option>
                <option value="Published">Published</option>
            </select>

            <label>Urutan Tampilan</label>
            <input type="number" name="display_order" min="0">

            <button type="submit" class="primary">Simpan Perubahan</button>
            <button type="button" class="cancel" onclick="closeEditForm()">Batal</button>

            <div id="edit-message" class="message"></div>

        </form>

    </div>


    <!-- DAFTAR INFORMASI -->
    <div class="section">

        <h2>Daftar Informasi Desa</h2>

        <div id="information-list">
            Memuat informasi...
        </div>

    </div>

</div>


<script>

// Menghindari HTML dari database ditampilkan sebagai kode HTML.
function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, function(character) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[character];
    });
}


// Mengambil dan menampilkan seluruh informasi untuk admin.
async function loadInformation() {

    const list = document.getElementById('information-list');
    list.textContent = 'Memuat informasi...';

    try {

        const response = await fetch('../app/list_village_information.php');
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengambil informasi.');
        }

        if (!result.data || result.data.length === 0) {
            list.innerHTML = '<p class="empty">Belum ada informasi desa.</p>';
            return;
        }

        list.innerHTML = result.data.map(function(item) {

            return `
                <div class="information-item">

                    <h3>${escapeHTML(item.title)}</h3>

                    <p>
                        <strong>Kategori:</strong>
                        ${escapeHTML(item.category)}
                    </p>

                    <p>
                        <strong>Nilai:</strong>
                        ${escapeHTML(item.value || '-')}
                        ${escapeHTML(item.unit || '')}
                    </p>

                    <p>
                        <strong>Deskripsi:</strong>
                        ${escapeHTML(item.description || '-')}
                    </p>

                    <p>
                        <strong>Tahun:</strong>
                        ${escapeHTML(item.reference_year || '-')}
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <span class="status">${escapeHTML(item.status)}</span>
                    </p>

                    <p>
                        <strong>Urutan:</strong>
                        ${escapeHTML(item.display_order)}
                    </p>

                    <button type="button"
                        class="edit"
                        onclick="editInformation(${Number(item.id)})">
                        Edit
                    </button>

                    <button type="button"
                        class="delete"
                        onclick="deleteInformation(${Number(item.id)})">
                        Hapus
                    </button>

                </div>
            `;

        }).join('');

    } catch (error) {

        list.textContent = 'Gagal memuat informasi: ' + error.message;

    }
}


// Menambahkan informasi baru.
document.getElementById('information-form').addEventListener('submit', async function(event) {

    event.preventDefault();

    const form = this;
    const formData = new FormData(form);
    const message = document.getElementById('form-message');

    message.textContent = 'Menyimpan informasi...';

    try {

        const response = await fetch('../app/save_village_information.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menyimpan informasi.');
        }

        message.textContent = result.message;

        form.reset();

        loadInformation();

    } catch (error) {

        message.textContent = 'Gagal: ' + error.message;

    }

});


// Membuka formulir edit dan mengisi data yang dipilih.
async function editInformation(id) {

    try {

        const response = await fetch('../app/list_village_information.php');
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengambil data.');
        }

        const item = result.data.find(function(data) {
            return Number(data.id) === Number(id);
        });

        if (!item) {
            throw new Error('Informasi tidak ditemukan.');
        }

        const form = document.getElementById('edit-information-form');

        form.elements['id'].value = item.id;
        form.elements['category'].value = item.category;
        form.elements['title'].value = item.title;
        form.elements['value'].value = item.value ?? '';
        form.elements['unit'].value = item.unit ?? '';
        form.elements['description'].value = item.description ?? '';
        form.elements['reference_year'].value = item.reference_year ?? '';
        form.elements['status'].value = item.status;
        form.elements['display_order'].value = item.display_order ?? 0;

        document.getElementById('edit-message').textContent = '';

        document.getElementById('edit-section').style.display = 'block';

        document.getElementById('edit-section').scrollIntoView({
            behavior: 'smooth'
        });

    } catch (error) {

        alert('Gagal membuka formulir edit: ' + error.message);

    }

}


// Menyimpan perubahan informasi.
document.getElementById('edit-information-form').addEventListener('submit', async function(event) {

    event.preventDefault();

    const form = this;
    const formData = new FormData(form);
    const message = document.getElementById('edit-message');

    message.textContent = 'Menyimpan perubahan...';

    try {

        const response = await fetch('../app/update_village_information.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal memperbarui informasi.');
        }

        alert('Informasi berhasil diperbarui.');

        closeEditForm();

        loadInformation();

    } catch (error) {

        message.textContent = 'Gagal: ' + error.message;

    }

});


// Menutup formulir edit.
function closeEditForm() {

    document.getElementById('edit-section').style.display = 'none';

}


// Menghapus informasi setelah admin memberikan konfirmasi.
async function deleteInformation(id) {

    const confirmation = confirm('Apakah kamu yakin ingin menghapus informasi ini?');

    if (!confirmation) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);

    try {

        const response = await fetch('../app/delete_village_information.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menghapus informasi.');
        }

        alert('Informasi berhasil dihapus.');

        loadInformation();

    } catch (error) {

        alert('Gagal menghapus informasi: ' + error.message);

    }

}


// Memuat daftar informasi saat halaman pertama kali dibuka.
loadInformation();

</script>

</body>
</html>
