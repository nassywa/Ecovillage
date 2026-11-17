
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
    <title>Profil Desa - EcoVillage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f4;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 7px 0 15px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 90px;
        }

        button {
            padding: 12px 20px;
            background: #26734d;
            color: white;
            border: 0;
            border-radius: 5px;
            cursor: pointer;
        }

        img {
            max-width: 150px;
            display: block;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Form Profil Desa</h2>

    <form id="profileForm" enctype="multipart/form-data">
        <label>Nama Desa</label>
        <input type="text" name="village_name" required>

        <label>Kecamatan</label>
        <input type="text" name="district">

        <label>Kabupaten</label>
        <input type="text" name="regency">

        <label>Provinsi</label>
        <input type="text" name="province">

        <label>Deskripsi Desa</label>
        <textarea name="description"></textarea>

        <label>Sejarah Desa</label>
        <textarea name="history"></textarea>

        <label>Visi Desa</label>
        <textarea name="vision"></textarea>

        <label>Misi Desa</label>
        <textarea name="mission"></textarea>

        <label>Informasi Geografis</label>
        <textarea name="geographic_info"></textarea>

        <label>Alamat Desa</label>
        <textarea name="village_address"></textarea>

        <label>Logo Desa</label>
        <input type="file" name="village_logo" accept="image/jpeg,image/png,image/webp">

        <div id="logoPreview"></div>

        <button type="submit">Simpan Profil Desa</button>
    </form>

    <p id="message"></p>
</div>

<script>
const form = document.getElementById('profileForm');
const message = document.getElementById('message');

async function loadProfile() {
    try {
        const response = await fetch('../app/get_village_profile.php');
        const result = await response.json();

        if (!result.success || !result.data) return;

        const data = result.data;

        Object.keys(data).forEach(key => {
            const field = form.elements[key];

            if (field && field.type !== 'file') {
                field.value = data[key] ?? '';
            }
        });

        if (data.village_logo_url) {
            document.getElementById('logoPreview').innerHTML =
                '<img src="' + data.village_logo_url + '" alt="Logo Desa">';
        }
    } catch (error) {
        message.textContent = 'Gagal memuat profil desa.';
    }
}

form.addEventListener('submit', async function(event) {
    event.preventDefault();

    const formData = new FormData(form);

    try {
        const response = await fetch('../app/save_village_profile.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        message.textContent = result.message;

        if (result.success) {
            loadProfile();
        }
    } catch (error) {
        message.textContent = 'Terjadi kesalahan saat menyimpan profil.';
    }
});

loadProfile();
</script>

</body>
</html>
