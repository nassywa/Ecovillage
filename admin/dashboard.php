
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - EcoVillage</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #222;
        }

        header {
            background: #176b45;
            color: white;
            padding: 25px 35px;
        }

        header h1 {
            margin: 0 0 8px;
        }

        header p {
            margin: 0;
        }

        main {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-decoration: none;
            color: #222;
            border: 1px solid #e5e5e5;
            transition: 0.2s;
        }

        .menu-card:hover {
            transform: translateY(-3px);
            border-color: #176b45;
        }

        .menu-card h2 {
            color: #176b45;
            font-size: 19px;
            margin-top: 0;
        }

        .menu-card p {
            color: #666;
            line-height: 1.5;
            font-size: 14px;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            header {
                padding: 22px 20px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>EcoVillage Admin</h1>
    <p>Dashboard Pengelolaan Website Desa Marga Mulya</p>
</header>

<main>

    <section class="welcome">
        <h2>Selamat Datang, <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>!</h2>
        <p>Silakan pilih menu yang ingin kamu kelola.</p>
    </section>

    <section class="menu-grid">

       <a href="village.php" class="menu-card">
            <h2>Profil Desa</h2>
            <p>Kelola identitas, sejarah, visi, misi, dan informasi geografis desa.</p>
        </a>

        <a href="village_information.php" class="menu-card">
            <h2>Informasi Desa</h2>
            <p>Kelola data kependudukan, pendidikan, ekonomi, wisata, dan potensi desa.</p>
        </a>

        <a href="gallery.php" class="menu-card">
            <h2>Galeri Desa</h2>
            <p>Tambah, edit, dan hapus foto dokumentasi kegiatan desa.</p>
        </a>

        <a href="village_contact.php" class="menu-card">
            <h2>Kontak Desa</h2>
            <p>Kelola nomor telepon, email, alamat, dan media sosial desa.</p>
        </a>

    </section>

</main>

<footer>
    EcoVillage CMS &copy; 2026
</footer>

</body>
</html>
