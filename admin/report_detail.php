
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$reportId = $_GET['id'] ?? '';

if (!ctype_digit((string) $reportId) || (int) $reportId < 1) {
    header('Location: reports.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Laporan - EcoVillage</title>

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
            max-width: 850px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        .field {
            margin-bottom: 18px;
        }

        .field strong {
            display: block;
            color: #176b45;
            margin-bottom: 6px;
        }

        .field p {
            margin: 0;
            line-height: 1.6;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .report-photo {
            max-width: 100%;
            max-height: 350px;
            border-radius: 8px;
            object-fit: contain;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #176b45;
            text-decoration: none;
        }

        .message {
            color: #666;
            padding: 15px 0;
        }

        select,
        textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        button {
            background: #176b45;
            color: white;
            border: none;
            padding: 11px 18px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #125536;
        }

        hr {
            border: 0;
            border-top: 1px solid #eee;
            margin: 25px 0;
        }

        @media (max-width: 600px) {
            header {
                padding: 22px 20px;
            }

            .card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Detail Laporan Warga</h1>
    <p>Informasi lengkap laporan masyarakat</p>
</header>

<main>

    <a href="reports.php" class="back-link">
        &larr; Kembali ke Daftar Laporan
    </a>

    <section class="card">

        <div id="reportContent">
            <p class="message">Memuat detail laporan...</p>
        </div>

        <hr>

        <h2>Perbarui Penanganan Laporan</h2>

        <form id="statusForm">

            <div class="field">
                <strong>Status Baru</strong>

                <select id="newStatus" required>
                    <option value="Diterima">Diterima</option>
                    <option value="Diproses">Diproses</option>
                    <option value="Diteruskan">Diteruskan</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </div>

            <div class="field">
                <strong>Catatan Admin</strong>

                <textarea
                    id="adminNote"
                    rows="4"
                    placeholder="Tambahkan catatan penanganan laporan..."
                ></textarea>
            </div>

            <button type="submit">Simpan Perubahan</button>

            <p id="statusMessage" class="message"></p>

        </form>

        <hr>

        <h2>Riwayat Penanganan</h2>

        <div id="reportHistory">
            <p class="message">Memuat riwayat laporan...</p>
        </div>

    </section>

</main>

<script>

const reportId = <?= json_encode((int) $reportId) ?>;

// Mengambil detail laporan
async function loadReportDetail() {
    const content = document.getElementById('reportContent');

    try {
        const response = await fetch(
            `../app/report_detail.php?id=${reportId}`
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengambil detail laporan.');
        }

        const report = result.data;

        document.getElementById('newStatus').value = report.status;

        content.innerHTML = `
            <div class="field">
                <strong>Nomor Tiket</strong>
                <p>${escapeHTML(report.ticket_number)}</p>
            </div>

            <div class="field">
                <strong>Kategori</strong>
                <p>${escapeHTML(report.category)}</p>
            </div>

            <div class="field">
                <strong>Deskripsi</strong>
                <p>${escapeHTML(report.description)}</p>
            </div>

            <div class="field">
                <strong>Lokasi</strong>
                <p>${escapeHTML(report.location)}</p>
            </div>

            <div class="field">
                <strong>RT / RW</strong>
                <p>${escapeHTML(report.rt || '-')} / ${escapeHTML(report.rw || '-')}</p>
            </div>

            <div class="field">
                <strong>Status</strong>
                <p>${escapeHTML(report.status)}</p>
            </div>

            <div class="field">
                <strong>Catatan Admin</strong>
                <p>${escapeHTML(report.admin_note || '-')}</p>
            </div>

            <div class="field">
                <strong>Tanggal Laporan</strong>
                <p>${escapeHTML(report.created_at)}</p>
            </div>

            <div class="field">
                <strong>Foto Laporan</strong>
                ${
                    report.photo_url
                    ? `<img class="report-photo"
                        src="${escapeHTML(report.photo_url)}"
                        alt="Foto laporan warga">`
                    : '<p>Tidak ada foto laporan.</p>'
                }
            </div>
        `;

        await loadReportHistory(report.ticket_number);

    } catch (error) {
        content.innerHTML = `
            <p class="message">${escapeHTML(error.message)}</p>
        `;
    }
}

// Mengambil riwayat perubahan status
async function loadReportHistory(ticketNumber) {
    const historyContainer = document.getElementById('reportHistory');

    try {
        const response = await fetch(
            `../app/report_history.php?ticket_number=${encodeURIComponent(ticketNumber)}`
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengambil riwayat laporan.');
        }

        if (result.history.length === 0) {
            historyContainer.innerHTML = `
                <p class="message">Belum ada riwayat penanganan.</p>
            `;
            return;
        }

        historyContainer.innerHTML = result.history.map(item => `
            <div class="field">
                <strong>
                    ${escapeHTML(item.old_status || 'Laporan dibuat')}
                    &rarr;
                    ${escapeHTML(item.new_status)}
                </strong>

                <p>${escapeHTML(item.note || 'Tidak ada catatan.')}</p>

                <p>${escapeHTML(item.created_at)}</p>
            </div>
        `).join('');

    } catch (error) {
        historyContainer.innerHTML = `
            <p class="message">${escapeHTML(error.message)}</p>
        `;
    }
}

// Mengamankan teks sebelum ditampilkan sebagai HTML
function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);
}

// Mengirim perubahan status ke API
document.getElementById('statusForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const message = document.getElementById('statusMessage');
    const newStatus = document.getElementById('newStatus').value;
    const note = document.getElementById('adminNote').value;

    const formData = new FormData();

    formData.append('report_id', reportId);
    formData.append('status', newStatus);
    formData.append('note', note);

    try {
        const response = await fetch('../app/update_report_status.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal memperbarui status.');
        }

        message.textContent = result.message;

        document.getElementById('adminNote').value = '';

        await loadReportDetail();

    } catch (error) {
        message.textContent = error.message;
    }
});

// Jalankan saat halaman dibuka
loadReportDetail();

</script>

</body>
</html>
