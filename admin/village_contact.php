
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
    <title>CMS Kontak Desa</title>

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
            max-width: 850px;
            margin: auto;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 25px;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button {
            background: #176b45;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.9;
        }

        .message {
            margin-bottom: 15px;
            font-weight: bold;
        }

        .note {
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Kontak Desa</h1>

    <p class="subtitle">
        Kelola informasi kontak dan lokasi Kantor Desa Marga Mulya.
    </p>

    <div class="panel">

        <h2>Informasi Kontak</h2>

        <div id="message" class="message"></div>

        <form id="contactForm">

            <div class="field">
                <label for="phone">Nomor Telepon</label>
                <input type="text" id="phone" name="phone"
                    placeholder="Masukkan nomor telepon desa">
            </div>

            <div class="field">
                <label for="email">Email Desa</label>
                <input type="email" id="email" name="email"
                    placeholder="Masukkan email desa">
            </div>

            <div class="field">
                <label for="village_address">Alamat Kantor Desa</label>
                <textarea id="village_address" name="village_address"
                    placeholder="Masukkan alamat kantor desa"></textarea>
            </div>

            <div class="field">
                <label for="office_hours">Jam Pelayanan</label>
                <input type="text" id="office_hours" name="office_hours"
                    placeholder="Contoh: Senin - Jumat, 08.00 - 15.00">
            </div>

            <div class="field">
                <label for="website">Website Desa</label>
                <input type="url" id="website" name="website"
                    placeholder="https://contoh.go.id">
            </div>

            <div class="field">
                <label for="instagram">Instagram</label>
                <input type="url" id="instagram" name="instagram"
                    placeholder="https://instagram.com/...">
            </div>

            <div class="field">
                <label for="facebook">Facebook</label>
                <input type="url" id="facebook" name="facebook"
                    placeholder="https://facebook.com/...">
            </div>

            <div class="field">
                <label for="map_url">Link Google Maps</label>
                <textarea id="map_url" name="map_url"
                    placeholder="Tempelkan link lokasi Google Maps"></textarea>
                <p class="note">
                    Gunakan link berbagi lokasi Google Maps.
                </p>
            </div>

            <button type="submit">Simpan Kontak Desa</button>

        </form>

    </div>

</div>

<script>
const contactForm = document.getElementById('contactForm');
const message = document.getElementById('message');

// Ambil kontak yang sudah tersimpan
async function loadContact() {

    try {

        const response = await fetch('../app/get_village_contact.php');
        const result = await response.json();

        if (response.status === 404) {
            return;
        }

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengambil kontak.');
        }

        const contact = result.data;

        document.getElementById('phone').value = contact.phone || '';
        document.getElementById('email').value = contact.email || '';
        document.getElementById('village_address').value = contact.village_address || '';
        document.getElementById('office_hours').value = contact.office_hours || '';
        document.getElementById('website').value = contact.website || '';
        document.getElementById('instagram').value = contact.instagram || '';
        document.getElementById('facebook').value = contact.facebook || '';
        document.getElementById('map_url').value = contact.map_url || '';

    } catch (error) {

        message.textContent = error.message;
        message.style.color = 'red';

    }
}

// Simpan atau perbarui kontak
contactForm.addEventListener('submit', async function(event) {

    event.preventDefault();

    const formData = new FormData(contactForm);

    try {

        const response = await fetch('../app/save_village_contact.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menyimpan kontak.');
        }

        message.textContent = result.message;
        message.style.color = 'green';

    } catch (error) {

        message.textContent = error.message;
        message.style.color = 'red';

    }

});

// Jalankan saat halaman dibuka
loadContact();
</script>

</body>
</html>
    