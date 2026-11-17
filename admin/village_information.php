
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
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f7f5;
        }

        form, .information-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            max-width: 700px;
        }

        input, select, textarea {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 7px;
            box-sizing: border-box;
        }

        label {
            display: block;
            margin-top: 15px;
        }

        button {
            padding: 10px 16px;
            margin-top: 15px;
            cursor: pointer;
        }

        .photo-preview {
            max-width: 250px;
            max-height: 180px;
            display: none;
            margin-top: 10px;
            border-radius: 6px;
            object-fit: cover;
        }

        .information-photo {
            max-width: 250px;
            max-height: 180px;
            display: block;
            object-fit: cover;
            border-radius: 6px;
            margin: 10px 0;
        }

        #edit-section {
            display: none;
        }
    </style>
</head>

<body>

    <h1>Manajemen Informasi Desa</h1>

    <p>
        Kelola data geografis, pemerintahan, kependudukan,
        pendidikan, kesehatan, ekonomi, dan potensi desa.
    </p>

    <hr>

    <h2>Tambah Informasi Desa</h2>

    <form id="information-form">

        <label>Kategori</label>
        <select name="category" required>
            <option value="">Pilih Kategori</option>
            <option value="Geografis">Geografis</option>
            <option value="Pemerintahan Desa">Pemerintahan Desa</option>
            <option value="Kependudukan">Kependudukan</option>
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

        <label>Foto Informasi</label>
        <input
            type="file"
            name="image"
            accept="image/jpeg,image/png,image/webp"
            id="add-image"
        >

        <img id="add-preview" class="photo-preview" alt="Preview foto">

        <label>Tahun Referensi</label>
        <input type="number" name="reference_year" min="1901" max="2155">

        <label>Status</label>
        <select name="status">
            <option value="Draft">Draft</option>
            <option value="Published">Published</option>
        </select>

        <label>Urutan Tampilan</label>
        <input type="number" name="display_order" value="0" min="0">

        <button type="submit">Simpan Informasi</button>

    </form>

    <p id="form-message"></p>

    <hr>

    <div id="edit-section">

        <h2>Edit Informasi Desa</h2>

        <form id="edit-information-form">

            <input type="hidden" name="id">

            <label>Kategori</label>
            <select name="category" required>
                <option value="Geografis">Geografis</option>
                <option value="Pemerintahan Desa">Pemerintahan Desa</option>
                <option value="Kependudukan">Kependudukan</option>
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

            <label>Foto Informasi</label>
            <p>Foto lama akan tetap digunakan jika kamu tidak memilih foto baru.</p>

            <img id="edit-current-preview" class="photo-preview" alt="Foto saat ini">

            <input
                type="file"
                name="image"
                accept="image/jpeg,image/png,image/webp"
                id="edit-image"
            >

            <img id="edit-new-preview" class="photo-preview" alt="Preview foto baru">

            <label>Tahun Referensi</label>
            <input type="number" name="reference_year" min="1901" max="2155">

            <label>Status</label>
            <select name="status">
                <option value="Draft">Draft</option>
                <option value="Published">Published</option>
            </select>

            <label>Urutan Tampilan</label>
            <input type="number" name="display_order" min="0">

            <button type="submit">Simpan Perubahan</button>
            <button type="button" onclick="closeEditForm()">Batal</button>

        </form>

        <p id="edit-message"></p>

    </div>

    <hr>

    <h2>Daftar Informasi Desa</h2>

    <div id="information-list">
        Memuat informasi...
    </div>

    <script>

        let informationData = [];

        function escapeHTML(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[character]);
        }

        function previewImage(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            input.addEventListener('change', function() {

                const file = this.files[0];

                if (!file) {
                    preview.style.display = 'none';
                    preview.removeAttribute('src');
                    return;
                }

                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (!allowedTypes.includes(file.type)) {
                    alert('Format foto harus JPG, PNG, atau WEBP.');
                    this.value = '';
                    preview.style.display = 'none';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran foto maksimal 5 MB.');
                    this.value = '';
                    preview.style.display = 'none';
                    return;
                }

                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            });
        }

        previewImage('add-image', 'add-preview');
        previewImage('edit-image', 'edit-new-preview');

        async function loadInformation() {

            const container = document.getElementById('information-list');

            container.innerHTML = 'Memuat data...';

            try {

                const response = await fetch(
                    '../app/list_village_information.php'
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message);
                }

                informationData = result.data;

                if (informationData.length === 0) {
                    container.innerHTML = '<p>Belum ada informasi desa.</p>';
                    return;
                }

                container.innerHTML = informationData.map(item => `

                    <div class="information-card">

                        <h3>${escapeHTML(item.title)}</h3>

                        <p>Kategori: ${escapeHTML(item.category)}</p>

                        <p>
                            Nilai:
                            ${escapeHTML(item.value ?? '-')}
                            ${escapeHTML(item.unit ?? '')}
                        </p>

                        <p>Deskripsi: ${escapeHTML(item.description ?? '-')}</p>

                        <p>Tahun: ${escapeHTML(item.reference_year ?? '-')}</p>

                        <p>Status: ${escapeHTML(item.status)}</p>

                        ${
                            item.image
                                ? `<img
                                    class="information-photo"
                                    src="../${escapeHTML(item.image)}"
                                    alt="Foto ${escapeHTML(item.title)}"
                                >`
                                : '<p>Belum ada foto.</p>'
                        }

                        <button
                            type="button"
                            onclick="editInformation(${Number(item.id)})"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            onclick="deleteInformation(${Number(item.id)})"
                        >
                            Hapus
                        </button>

                    </div>

                `).join('');

            } catch (error) {

                container.innerHTML =
                    'Gagal memuat data: ' + escapeHTML(error.message);

            }
        }

        // Tambah informasi desa
        document.getElementById('information-form')
            .addEventListener('submit', async function(event) {

                event.preventDefault();

                const form = this;
                const formData = new FormData(form);
                const message = document.getElementById('form-message');

                message.textContent = 'Menyimpan informasi...';

                try {

                    const response = await fetch(
                        '../app/save_village_information.php',
                        {
                            method: 'POST',
                            body: formData
                        }
                    );

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message);
                    }

                    message.textContent = 'Berhasil: ' + result.message;

                    form.reset();

                    document.getElementById('add-preview').style.display = 'none';

                    loadInformation();

                } catch (error) {

                    message.textContent = 'Gagal: ' + error.message;

                }
            });

        // Buka formulir edit dan isi data lama
        function editInformation(id) {

            const item = informationData.find(
                data => Number(data.id) === Number(id)
            );

            if (!item) {
                alert('Informasi tidak ditemukan.');
                return;
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
            form.elements['display_order'].value = item.display_order;

            document.getElementById('edit-image').value = '';
            document.getElementById('edit-new-preview').style.display = 'none';

            const currentPreview = document.getElementById(
                'edit-current-preview'
            );

            if (item.image) {
                currentPreview.src = '../' + item.image;
                currentPreview.style.display = 'block';
            } else {
                currentPreview.style.display = 'none';
            }

            document.getElementById('edit-section').style.display = 'block';

            document.getElementById('edit-section').scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Simpan perubahan informasi
        document.getElementById('edit-information-form')
            .addEventListener('submit', async function(event) {

                event.preventDefault();

                const formData = new FormData(this);
                const message = document.getElementById('edit-message');

                message.textContent = 'Menyimpan perubahan...';

                try {

                    const response = await fetch(
                        '../app/update_village_information.php',
                        {
                            method: 'POST',
                            body: formData
                        }
                    );

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message);
                    }

                    message.textContent = result.message;

                    alert('Informasi berhasil diperbarui.');

                    closeEditForm();
                    loadInformation();

                } catch (error) {

                    message.textContent = 'Gagal: ' + error.message;

                }
            });

        function closeEditForm() {

            document.getElementById('edit-information-form').reset();

            document.getElementById('edit-section').style.display = 'none';

            document.getElementById('edit-current-preview').style.display = 'none';
            document.getElementById('edit-new-preview').style.display = 'none';

            document.getElementById('edit-message').textContent = '';
        }

        // Hapus informasi desa
        async function deleteInformation(id) {

            const confirmation = confirm(
                'Apakah kamu yakin ingin menghapus informasi ini?'
            );

            if (!confirmation) {
                return;
            }

            const formData = new FormData();
            formData.append('id', id);

            try {

                const response = await fetch(
                    '../app/delete_village_information.php',
                    {
                        method: 'POST',
                        body: formData
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message);
                }

                alert('Informasi berhasil dihapus.');

                loadInformation();

            } catch (error) {

                alert('Gagal menghapus informasi: ' + error.message);

            }
        }

        loadInformation();

    </script>

</body>
</html>
