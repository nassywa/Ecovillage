
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
    <title>Laporan Warga - EcoVillage</title>

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

        main {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .back-link {
            color: #176b45;
            text-decoration: none;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        th {
            background: #f8faf9;
            color: #176b45;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 5px;
            background: #e8f1ec;
            font-size: 12px;
        }

        .message {
            padding: 15px;
            color: #666;
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
    <h1>Laporan Warga</h1>
    <p>Pengelolaan laporan masyarakat Desa Marga Mulya</p>
</header>

<main>

    <div class="toolbar">
        <div>
            <h2>Daftar Laporan</h2>
            <p>Halo, <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>.</p>
        </div>

        <a href="dashboard.php" class="back-link">
            Kembali ke Dashboard
        </a>
    </div>

    <section class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nomor Tiket</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>                    
                </tr>
            </thead>

            <tbody id="reportTable">
                <tr>
                    <td colspan="6" class="message">
                        Memuat data laporan...
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

</main>

<script>
async function loadReports() {
    const table = document.getElementById('reportTable');

    try {
        const response = await fetch('../app/list_reports.php');
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengambil laporan.');
        }

        if (result.data.length === 0) {
            table.innerHTML = `
                <tr>
                    <td colspan="6" class="message">
                        Belum ada laporan warga.
                    </td>
                </tr>
            `;
            return;
        }

        table.innerHTML = result.data.map((report, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${escapeHTML(report.ticket_number)}</td>
                <td>${escapeHTML(report.category)}</td>
                <td>${escapeHTML(report.location)}</td>
                <td>${escapeHTML(report.created_at)}</td>
                <td>
    <span class="status">
        ${escapeHTML(report.status)}
    </span>
</td>
<td>
    <button onclick="viewReport(${Number(report.id)})">
        Detail
    </button>
</td>
            </tr>
        `).join('');

    } catch (error) {
        table.innerHTML = `
            <tr>
                <td colspan="6" class="message">
                    ${escapeHTML(error.message)}
                </td>
            </tr>
        `;
    }
}

function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);
}

loadReports();

async function viewReport(id) {
    window.location.href = `report_detail.php?id=${id}`;
}
</script>

</body>
</html>
