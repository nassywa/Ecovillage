<?php

declare(strict_types=1);

namespace Database;

use PDO;

function seed(PDO $pdo, string $adminEmail, string $adminPass): void
{
    $ins = function (string $table, array $row) use ($pdo): int {
        $cols = array_keys($row);
        $st = $pdo->prepare("INSERT INTO `$table` (`" . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')');
        $st->execute(array_values($row));
        return (int) $pdo->lastInsertId();
    };

    $img = [];
    foreach (glob(__DIR__ . '/seed-media/*') ?: [] as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = ['svg' => 'image/svg+xml', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][$ext];
        $data = file_get_contents($file);
        $st = $pdo->prepare('INSERT INTO media (mime, size, original_name, data) VALUES (?, ?, ?, ?)');
        $st->bindValue(1, $mime);
        $st->bindValue(2, strlen($data), PDO::PARAM_INT);
        $st->bindValue(3, basename($file));
        $st->bindValue(4, $data, PDO::PARAM_LOB);
        $st->execute();
        $img[pathinfo($file, PATHINFO_FILENAME)] = (int) $pdo->lastInsertId();
    }
    $I = fn (string $k) => $img[$k] ?? null;

    $adminId = $ins('users', ['name' => 'Admin Desa Marga Mulya', 'email' => strtolower($adminEmail), 'phone' => '081234500001',
        'password_hash' => password_hash($adminPass, PASSWORD_DEFAULT), 'role' => 'admin']);
    // Seed accounts need stable IDs for fixture relations, but must not ship
    // with a documented/default password. Their random hashes are sufficient
    // for fixture data; create real warga credentials through registration.
    $wargaHash1 = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
    $wargaHash2 = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
    $warga1 = $ins('users', ['name' => 'Rahmat Hidayat', 'email' => 'warga@margamulya.id', 'phone' => '081234500002',
        'password_hash' => $wargaHash1, 'role' => 'warga']);
    $warga2 = $ins('users', ['name' => 'Nur Aini', 'email' => 'nuraini@contoh.id', 'phone' => '081234500003',
        'password_hash' => $wargaHash2, 'role' => 'warga']);

    $settings = [
        'site_name' => 'Desa Marga Mulya',
        'village_name' => 'Desa Marga Mulya',
        'region_short' => 'Kab. Kotawaringin Barat',
        'region_line' => 'Kecamatan Pangkalan Banteng, Kabupaten Kotawaringin Barat, Kalimantan Tengah',
        'hero_title' => 'Selamat Datang di Desa Marga Mulya',
        'hero_subtitle' => 'Bersama membangun desa maju, hijau, dan sejahtera',
        'hero_image' => (string) $I('hero'),
        'marquee_text' => 'Desa Maju • Hijau • Sejahtera',
        'home_announcement' => '',
        'quick_intro' => 'Sampaikan aspirasi, pantau perkembangan laporan, dan jelajahi potensi desa dalam satu tempat.',
        'cta_title' => 'Ada yang bisa kami bantu?',
        'cta_text' => 'Sampaikan keluhan, saran, atau pertanyaan Anda. Tim Pemerintah Desa siap melayani pada jam kerja.',
        'stat_penduduk' => '4862',
        'stat_kk' => '1347',
        'stat_rw' => '3',
        'stat_rt' => '12',
        'stat_pelayanan' => '95',
        'contact_address' => 'Jl. Poros Marga Mulya No. 1, Desa Marga Mulya, Kecamatan Pangkalan Banteng, Kabupaten Kotawaringin Barat, Kalimantan Tengah',
        'contact_phone' => '(0532) 555-0142',
        'contact_whatsapp' => '6281234500001',
        'contact_email' => 'kantor@margamulya.example',
        'contact_hours_text' => 'Senin sampai Jumat, 08.00 sampai 16.00 WIB. Sabtu dan Minggu libur.',
        'map_lat' => '-2.4710',
        'map_lng' => '111.9200',
        'map_zoom' => '13',
        'office_lat' => '-2.4705',
        'office_lng' => '111.9195',
        'social_facebook' => 'https://facebook.com/saepul.haris.9659?mibextid=rS40aB7S9Ucbxw6v',
        'social_tiktok' => 'https://tiktok.com/@saepul_haris27?_t=8p0a93cBblf&_r=1',
        'social_youtube' => 'https://youtube.com/@karangtaruna.margamulya?si=llcKrWQuQAYbd69g',
        'social_instagram' => 'https://instagram.com/saepul_haris27?psln=N2h1Zm9pcTZqeTk5',
        'jelajah_intro' => 'Dari aliran sungai hingga hamparan sawah dan kebun, inilah sudut-sudut yang membuat desa kami berkesan.',
        'gallery_intro' => 'Potret kegiatan dan keindahan Desa Marga Mulya.',
        'lapor_intro' => 'Sampaikan keluhan atau aspirasi Anda. Setiap laporan mendapat nomor tiket untuk dipantau.',
        'footer_about' => 'Situs resmi Pemerintah Desa Marga Mulya: pusat informasi, pelayanan, dan aspirasi warga.',
        'admin_register_code' => 'MARGAMULYA-ADMIN-2026',

        'profile_image' => (string) $I('gerbang'),
        'office_image' => (string) $I('kantor'),
        'profil_tentang' => 'Desa Marga Mulya adalah desa agraris di Kecamatan Pangkalan Banteng yang berkomitmen membangun masyarakat sejahtera dengan tetap menjaga kelestarian lingkungan. Sawah, kebun, aliran Sungai Kenanga, dan semangat gotong royong warganya menjadi kekuatan utama desa ini.',
        'profil_sejarah' => "Kisah Marga Mulya bermula pada awal 1980-an, ketika kawasan di tepi Sungai Kenanga ini dibuka sebagai permukiman baru. Rombongan keluarga pertama tiba pada 1982 dan bersama-sama membuka lahan, membangun rumah kayu, serta menggali sumur untuk kebutuhan sehari-hari.\n\nNama \"Marga Mulya\" dipilih dalam musyawarah warga. \"Marga\" berarti jalan, sedangkan \"Mulya\" berarti mulia. Nama ini menjadi harapan agar desa selalu berada di jalan menuju kehidupan yang mulia dan sejahtera. Pada 1989, Marga Mulya resmi menjadi desa definitif dengan tiga dusun yang kini menjadi tiga RW.\n\nTahun 1990-an menjadi masa pembentukan sawah dan kelompok tani pertama. Memasuki 2000-an, warga mengembangkan perkebunan, peternakan sapi, serta usaha rumah tangga seperti anyaman dan olahan pangan.\n\nPembangunan jalan poros desa pada 2015 membuka akses pasar yang lebih luas. Badan Usaha Milik Desa (BUMDes) Marga Lestari berdiri pada 2020 untuk mengelola wisata Sungai Kenanga dan pemasaran beras organik. Kini Marga Mulya melangkah menuju desa digital melalui layanan informasi dan pengaduan daring, tanpa meninggalkan tradisi gotong royong yang tumbuh sejak awal.",
        'profil_visi' => 'Desa yang maju, hijau, dan mandiri',
        'profil_misi_ringkas' => 'Meningkatkan kesejahteraan dan menjaga lingkungan',
        'profil_misi' => "Meningkatkan kesejahteraan warga melalui pertanian, peternakan, dan UMKM yang berkelanjutan\nMenjaga kelestarian lingkungan, Sungai Kenanga, dan ruang hijau desa\nMewujudkan pelayanan publik yang cepat, terbuka, dan mudah dijangkau\nMemperkuat gotong royong, kesehatan, dan pendidikan masyarakat",
        'geo_luas' => '3500',
        'geo_utara' => 'Desa Tanjung Asri',
        'geo_timur' => 'Desa Bukit Permai dan hutan produksi',
        'geo_selatan' => 'Sungai Kenanga dan Desa Karya Bakti',
        'geo_barat' => 'Desa Lestari Jaya',
        'geo_ketinggian' => '± 28 mdpl',
        'geo_iklim' => 'Tropis basah, curah hujan ± 2.600 mm/tahun',
        'geo_jarak' => '± 6 km ke pusat kecamatan, ± 58 km ke Pangkalan Bun',
    ];
    foreach ($settings as $k => $v) {
        $ins('settings', ['skey' => $k, 'svalue' => $v]);
    }

    foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $i => $day) {
        $closed = $i >= 5;
        $ins('office_hours', ['day' => $day, 'open_time' => $closed ? null : '08.00', 'close_time' => $closed ? null : '16.00', 'is_closed' => $closed ? 1 : 0, 'sort' => $i]);
    }

    $officials = [
        ['Budi Santoso', 'Kepala Desa', 1], ['Siti Aminah', 'Sekretaris Desa', 2],
        ['Ahmad Fadli', 'Kasi Pemerintahan', 3], ['Dewi Lestari', 'Kasi Pelayanan', 3], ['Rudi Hartono', 'Kasi Kesejahteraan', 3],
        ['Rina Wulandari', 'Kaur Keuangan', 3], ['Agus Prasetyo', 'Kaur Umum & Perencanaan', 3],
        ['Joko Susilo', 'Kepala Dusun I (RW 01)', 4], ['Hendra Wijaya', 'Kepala Dusun II (RW 02)', 4], ['Sri Rahayu', 'Kepala Dusun III (RW 03)', 4],
    ];
    foreach ($officials as $i => [$n, $p, $t]) {
        $ins('officials', ['name' => $n, 'position' => $p, 'tier' => $t, 'sort' => $i]);
    }

    $sections = [
        ['A', 'Pemerintahan Desa', 'landmark', 'Aparat, keamanan, fasilitas, lembaga, dan wilayah administrasi'],
        ['B', 'Kependudukan & Angkatan Kerja', 'users', 'Penduduk, umur, tenaga kerja, mata pencaharian, dan pendidikan warga'],
        ['C', 'Agama', 'heart', 'Jumlah pemeluk agama dan fasilitas ibadah'],
        ['D', 'Pendidikan', 'graduation-cap', 'Sekolah formal, PAUD, lembaga non-formal, dan pendidikan kesetaraan'],
        ['E', 'Kesehatan', 'activity', 'Fasilitas, tenaga kesehatan, gizi, sanitasi, dan KB'],
        ['F', 'Pertanian & Peternakan', 'leaf', 'Sawah, hasil panen, kelompok tani, dan ternak'],
        ['G', 'Ekonomi', 'wallet', 'Industri, lembaga keuangan, perdagangan, PBB, dan listrik'],
        ['H', 'Infrastruktur', 'route', 'Jalan, permukaan jalan, jembatan, dan sarana transportasi'],
    ];
    $sec = [];
    foreach ($sections as $i => [$code, $title, $icon, $desc]) {
        $sec[$code] = $ins('stat_sections', ['code' => $code, 'title' => $title, 'icon' => $icon, 'description' => $desc, 'sort' => $i]);
    }

    $sec['Z'] = $ins('stat_sections', ['code' => 'Z', 'title' => 'Kondisi Geografis', 'icon' => 'map', 'description' => 'Penggunaan lahan (tampil di Profil Desa)', 'sort' => 99]);

    $groupSort = 0;
    $group = function (string $code, string $title, string $type, string $unit, array $rows, ?string $desc = null, ?string $slug = null) use ($ins, $sec, &$groupSort): void {
        $gid = $ins('stat_groups', ['section_id' => $sec[$code], 'slug' => $slug, 'title' => $title, 'chart_type' => $type, 'unit' => $unit, 'description' => $desc, 'sort' => $groupSort++]);
        $n = 0;
        foreach ($rows as $label => $val) {
            if (is_array($val)) {
                foreach ($val as $series => $v) {
                    $ins('stat_items', ['group_id' => $gid, 'label' => (string) $label, 'series' => $series, 'value' => $v, 'sort' => $n++]);
                }
            } else {
                $ins('stat_items', ['group_id' => $gid, 'label' => (string) $label, 'series' => null, 'value' => $val, 'sort' => $n++]);
            }
        }
    };

    $group('Z', 'Luas wilayah menurut penggunaan', 'doughnut', 'ha', [
        'Pemukiman' => 240, 'Persawahan' => 580, 'Perkebunan' => 1950, 'Perkuburan' => 12, 'Pekarangan' => 380,
        'Taman' => 7, 'Perkantoran' => 9, 'Prasarana umum lainnya' => 322,
    ], 'Total 3.500 ha', 'penggunaan-lahan');

    $group('A', 'Aparat desa & kecamatan menurut jenis kelamin', 'stacked', 'orang', [
        'Aparat Desa' => ['Laki-laki' => 8, 'Perempuan' => 4], 'Aparat Kecamatan' => ['Laki-laki' => 15, 'Perempuan' => 9],
    ]);
    $group('A', 'Aparat menurut status kepegawaian', 'bar', 'orang', [
        'PNS' => ['Aparat Desa' => 1, 'Aparat Kecamatan' => 18], 'Non-PNS' => ['Aparat Desa' => 11, 'Aparat Kecamatan' => 6],
    ]);
    $group('A', 'Aparat menurut pendidikan terakhir', 'bar', 'orang', [
        'SMA/sederajat' => ['Aparat Desa' => 6, 'Aparat Kecamatan' => 5], 'D3' => ['Aparat Desa' => 2, 'Aparat Kecamatan' => 4],
        'S1' => ['Aparat Desa' => 4, 'Aparat Kecamatan' => 13], 'S2' => ['Aparat Desa' => 0, 'Aparat Kecamatan' => 2],
    ]);
    $group('A', 'Keamanan & ketertiban', 'cards', 'unit', ['Pos Polisi' => 1, 'Anggota Linmas' => 30, 'Pos Kamling' => 12]);
    $group('A', 'Anggota Linmas per RW', 'bar', 'orang', ['RW 01' => 10, 'RW 02' => 11, 'RW 03' => 9]);
    $group('A', 'Fasilitas pemerintahan', 'cards', 'unit', ['Kantor Pemerintahan Desa' => 1, 'Balai Desa' => 1, 'Gedung Serbaguna' => 1]);
    $group('A', 'Wilayah administrasi', 'cards', 'wilayah', ['Rukun Warga (RW)' => 3, 'Rukun Tetangga (RT)' => 12, 'Dusun' => 3]);
    $group('A', 'Lembaga kemasyarakatan desa', 'hbar', 'anggota', ['BPD' => 7, 'LPM' => 9, 'Tim Penggerak PKK' => 24, 'Karang Taruna' => 46, 'Kader Posyandu' => 30]);

    $group('B', 'Ringkasan kependudukan', 'cards', 'jiwa', [
        'Jumlah penduduk' => 4862, 'Rumah tangga (KK)' => 1347, 'Pencari kerja terdaftar' => 142, 'Tenaga kerja usia 18–56 th' => 2786,
    ]);
    $group('B', 'Penduduk menurut jenis kelamin', 'doughnut', 'jiwa', ['Laki-laki' => 2448, 'Perempuan' => 2414]);
    $group('B', 'Penduduk menurut kelompok umur', 'bar', 'jiwa', [
        '0–4' => 352, '5–14' => 768, '15–24' => 812, '25–39' => 1124, '40–54' => 986, '55–64' => 498, '65+' => 322,
    ]);
    $group('B', 'Penduduk dan KK per RW', 'bar', 'jiwa/KK', [
        'RW 01' => ['Penduduk' => 1616, 'KK' => 448], 'RW 02' => ['Penduduk' => 1626, 'KK' => 451], 'RW 03' => ['Penduduk' => 1620, 'KK' => 448],
    ]);
    $group('B', 'Mata pencaharian pokok', 'hbar', 'orang', [
        'Petani' => 1046, 'Karyawan swasta' => 452, 'Lainnya (pedagang, wiraswasta, dll)' => 398, 'Pengrajin' => 68,
        'Guru' => 64, 'PNS' => 56, 'Nelayan sungai' => 46, 'TNI' => 9, 'POLRI' => 7,
    ]);
    $group('B', 'Tingkat pendidikan penduduk', 'bar', 'jiwa', [
        'Belum/tidak sekolah' => 532, 'Tidak tamat SD' => 338, 'SD/sederajat' => 1252, 'SMP/sederajat' => 1071,
        'SMA/sederajat' => 1266, 'D1–D3' => 164, 'S1' => 222, 'S2' => 17,
    ]);
    $group('B', 'Pernikahan & perceraian per tahun', 'line', 'peristiwa', [
        '2022' => ['Pernikahan' => 44, 'Perceraian' => 5], '2023' => ['Pernikahan' => 52, 'Perceraian' => 4],
        '2024' => ['Pernikahan' => 48, 'Perceraian' => 6], '2025' => ['Pernikahan' => 57, 'Perceraian' => 3],
        '2026' => ['Pernikahan' => 38, 'Perceraian' => 2],
    ], 'Data 2026 sampai Oktober');

    $group('C', 'Jumlah pemeluk agama', 'doughnut', 'jiwa', ['Islam' => 4256, 'Kristen Protestan' => 287, 'Katolik' => 191, 'Hindu' => 89, 'Buddha' => 39]);
    $group('C', 'Fasilitas ibadah', 'bar', 'unit', ['Masjid' => 5, 'Mushola' => 14, 'Gereja' => 3, 'Pura' => 1]);

    $group('D', 'Jumlah sekolah negeri & swasta per jenjang', 'stacked', 'sekolah', [
        'SD/MI' => ['Negeri' => 3, 'Swasta' => 1], 'SLTP/MTs' => ['Negeri' => 1, 'Swasta' => 1],
        'SLTA/SMK/MA' => ['Negeri' => 1, 'Swasta' => 1], 'Perguruan Tinggi' => ['Negeri' => 0, 'Swasta' => 0],
    ]);
    $group('D', 'Guru & ruang kelas per jenjang', 'bar', 'orang/ruang', [
        'SD/MI' => ['Guru' => 38, 'Ruang kelas' => 26], 'SLTP/MTs' => ['Guru' => 22, 'Ruang kelas' => 12],
        'SLTA/SMK/MA' => ['Guru' => 18, 'Ruang kelas' => 9], 'Perguruan Tinggi' => ['Guru' => 0, 'Ruang kelas' => 0],
    ]);
    $group('D', 'Jumlah murid per jenjang', 'bar', 'murid', ['SD/MI' => 702, 'SLTP/MTs' => 403, 'SLTA/SMK/MA' => 286, 'Perguruan Tinggi' => 0]);
    $group('D', 'PAUD, TK, dan TPA', 'cards', 'lembaga', ['PAUD' => 3, 'Taman Kanak-kanak' => 2, 'Taman Pendidikan Anak' => 4]);
    $group('D', 'Lembaga pendidikan non-formal', 'hbar', 'lembaga', [
        'Kursus komputer' => 2, 'Kursus menjahit' => 2, 'Kursus bahasa' => 1, 'Salon & kecantikan' => 1, 'Montir / bengkel' => 2, 'Elektronik' => 1,
    ]);
    $group('D', 'Peserta pendidikan kesetaraan', 'bar', 'peserta', ['Paket A' => 9, 'Paket B' => 21, 'Paket C' => 34]);

    $group('E', 'Fasilitas kesehatan', 'bar', 'unit', [
        'Rumah sakit' => 0, 'Puskesmas' => 0, 'Pustu' => 1, 'Praktik dokter' => 1, 'Praktik bidan' => 3, 'Posyandu' => 6, 'Apotek' => 2,
    ], 'Rumah sakit & puskesmas terdekat di pusat kecamatan');
    $group('E', 'Tenaga kesehatan', 'cards', 'orang', ['Dokter' => 1, 'Bidan' => 4, 'Perawat' => 6, 'Dukun bayi terlatih' => 3]);
    $group('E', 'Cakupan imunisasi bayi', 'hbar', '%', ['BCG' => 98, 'Polio' => 97, 'DPT-HB-Hib' => 96, 'Hepatitis B' => 95, 'Campak/MR' => 94]);
    $group('E', 'Status gizi balita', 'doughnut', 'balita', ['Gizi baik' => 312, 'Gizi kurang' => 14, 'Gizi lebih' => 8]);
    $group('E', 'Sumber air bersih rumah tangga', 'pie', 'KK', ['Sumur gali' => 552, 'Sumur bor' => 523, 'Pamsimas' => 231, 'Air hujan' => 22, 'Mata air' => 19]);
    $group('E', 'Jenis rumah', 'pie', 'unit', ['Permanen' => 801, 'Semi permanen' => 428, 'Kayu/papan' => 118]);
    $group('E', 'Jenis jamban', 'pie', 'KK', ['Jamban sendiri' => 1214, 'Jamban bersama' => 104, 'Belum memiliki' => 29]);
    $group('E', 'Bahan bakar memasak', 'pie', 'KK', ['LPG' => 1170, 'Kayu bakar' => 153, 'Minyak tanah' => 24]);
    $group('E', 'Program KB', 'bar', 'pasangan', ['Pasangan usia subur' => 873, 'Peserta KB aktif' => 679, 'Peserta KB baru 2026' => 78]);

    $group('F', 'Luas sawah menurut jenis pengairan', 'doughnut', 'ha', ['Irigasi setengah teknis' => 136, 'Irigasi sederhana' => 180, 'Tadah hujan' => 264], 'Total 580 ha');
    $group('F', 'Luas panen', 'bar', 'ha', ['Padi' => 560, 'Jagung' => 96, 'Ketela' => 34, 'Sayuran' => 45, 'Buah-buahan' => 70]);
    $group('F', 'Produksi pertanian', 'bar', 'ton', ['Padi' => 2800, 'Jagung' => 528, 'Ketela' => 646, 'Sayuran' => 270, 'Buah-buahan' => 490]);
    $group('F', 'Kelompok tani', 'cards', 'kelompok', ['Kelompok tani' => 22, 'Gapoktan' => 1, 'Kelompok wanita tani' => 5]);
    $group('F', 'Populasi ternak', 'bar', 'ekor', ['Sapi' => 684, 'Kambing' => 472, 'Domba' => 88, 'Kerbau' => 26]);
    $group('F', 'Populasi unggas', 'bar', 'ekor', ['Ayam ras' => 9800, 'Ayam kampung' => 5420, 'Puyuh' => 1800, 'Itik' => 1060]);

    $group('G', 'Industri menengah & besar', 'bar', 'unit', [
        'Makanan & minuman' => 1, 'Pengolahan kayu' => 1, 'Pengolahan hasil kebun' => 2, 'Tekstil' => 0, 'Kertas' => 0, 'Kimia' => 0, 'Logam' => 0,
    ]);
    $group('G', 'Industri kecil & kerajinan rumah tangga', 'hbar', 'usaha', [
        'Makanan olahan' => 62, 'Anyaman' => 23, 'Kain & jahit' => 18, 'Kayu' => 15, 'Kulit' => 3, 'Lainnya' => 9,
    ]);
    $group('G', 'Lembaga keuangan', 'cards', 'unit', ['Bank' => 1, 'BPR' => 0, 'KUD' => 1, 'Koperasi' => 4, 'Pegadaian' => 0]);
    $group('G', 'Sarana perdagangan', 'bar', 'unit', ['Pasar modern' => 1, 'Pasar tradisional' => 1, 'Pasar tanpa bangunan' => 3]);
    $group('G', 'Penerimaan PBB', 'line', 'juta Rp', [
        '2022' => ['Target' => 148, 'Realisasi' => 131], '2023' => ['Target' => 153, 'Realisasi' => 142], '2024' => ['Target' => 158, 'Realisasi' => 151],
        '2025' => ['Target' => 164, 'Realisasi' => 159], '2026' => ['Target' => 171, 'Realisasi' => 118],
    ], 'Realisasi 2026 sampai Oktober');
    $group('G', 'Penggunaan listrik rumah tangga', 'pie', 'KK', ['PLN' => 1292, 'Tenaga surya / genset' => 38, 'Belum berlistrik' => 17]);

    $group('H', 'Panjang & kondisi jalan', 'stacked', 'km', [
        'Jalan desa' => ['Baik' => 14.6, 'Sedang' => 9.3, 'Rusak' => 4.7],
        'Jalan antar desa' => ['Baik' => 7.2, 'Sedang' => 3.1, 'Rusak' => 1.8],
        'Jalan kabupaten' => ['Baik' => 5.4, 'Sedang' => 1.6, 'Rusak' => 0.6],
        'Jalan provinsi' => ['Baik' => 3.8, 'Sedang' => 0.7, 'Rusak' => 0],
        'Jalan negara' => ['Baik' => 0, 'Sedang' => 0, 'Rusak' => 0],
    ], 'Total 52,8 km; tidak ada ruas jalan negara di wilayah desa');
    $group('H', 'Jenis permukaan jalan', 'doughnut', 'km', ['Aspal' => 21.4, 'Tanah' => 12.2, 'Makadam' => 10.3, 'Sirtu' => 5.6, 'Beton' => 3.3]);
    $group('H', 'Jembatan & sarana transportasi', 'cards', 'unit', ['Jembatan beton' => 6, 'Jembatan kayu' => 4, 'Dermaga sungai' => 1, 'Halte' => 2]);

    $outer = [[-2.4430, 111.8950], [-2.4405, 111.9180], [-2.4470, 111.9390], [-2.4700, 111.9480], [-2.4930, 111.9330],
        [-2.5010, 111.9150], [-2.4880, 111.8990], [-2.4660, 111.8920]];
    $clip = function (array $subject) use ($outer): array {
        $inside = function ($p, $a, $b) {
            return (($b[1] - $a[1]) * ($p[0] - $a[0]) - ($b[0] - $a[0]) * ($p[1] - $a[1])) <= 0;
        };
        $intersect = function ($p, $q, $a, $b) {
            $x1 = $p[1]; $y1 = $p[0]; $x2 = $q[1]; $y2 = $q[0]; $x3 = $a[1]; $y3 = $a[0]; $x4 = $b[1]; $y4 = $b[0];
            $den = ($x1 - $x2) * ($y3 - $y4) - ($y1 - $y2) * ($x3 - $x4);
            if (abs($den) < 1e-12) {
                return $q;
            }
            $t = (($x1 - $x3) * ($y3 - $y4) - ($y1 - $y3) * ($x3 - $x4)) / $den;
            return [$y1 + $t * ($y2 - $y1), $x1 + $t * ($x2 - $x1)];
        };
        $output = $subject;
        $n = count($outer);

        $area = 0;
        for ($i = 0; $i < $n; $i++) {
            $a = $outer[$i]; $b = $outer[($i + 1) % $n];
            $area += $a[1] * $b[0] - $b[1] * $a[0];
        }
        $poly = $area > 0 ? array_reverse($outer) : $outer;
        for ($i = 0; $i < $n; $i++) {
            $a = $poly[$i]; $b = $poly[($i + 1) % $n];
            $input = $output; $output = [];
            if (!$input) {
                break;
            }
            $s = end($input);
            foreach ($input as $e) {
                if ($inside($e, $a, $b)) {
                    if (!$inside($s, $a, $b)) {
                        $output[] = $intersect($s, $e, $a, $b);
                    }
                    $output[] = $e;
                } elseif ($inside($s, $a, $b)) {
                    $output[] = $intersect($s, $e, $a, $b);
                }
                $s = $e;
            }
        }
        return array_map(fn ($p) => [round($p[0], 5), round($p[1], 5)], $output);
    };
    $rect = fn ($n, $s, $w, $e) => [[$n, $w], [$n, $e], [$s, $e], [$s, $w]];

    $ins('map_regions', ['level' => 'desa', 'name' => 'Desa Marga Mulya', 'color' => '#16803C', 'leader' => 'Budi Santoso',
        'households' => 1347, 'population' => 4862, 'sort' => 0, 'geojson' => json_encode($outer)]);
    $latN = -2.4400; $latS = -2.5015; $latMid = -2.4705;
    $lngCuts = [111.8915, 111.9105, 111.9295, 111.9485];
    $rwColors = ['#16803C', '#2563EB', '#D97706'];
    $rtShades = [['#16803C', '#22A04E', '#4CB96F', '#0F6B31'], ['#2563EB', '#3B82F6', '#60A5FA', '#1D4ED8'], ['#D97706', '#F59E0B', '#FBBF24', '#B45309']];
    $rwLeaders = ['Joko Susilo', 'Hendra Wijaya', 'Sri Rahayu'];
    $rtLeaders = ['Slamet Riyadi', 'Wahyu Nugroho', 'Teguh Santosa', 'Bambang Irawan', 'Yusuf Maulana', 'Ani Marlina', 'Dedi Kurniawan', 'Fitri Handayani', 'Eko Saputro', 'Lilis Suryani', 'Darmawan', 'Maria Ulfa'];
    $rtPop = [[412, 398, 405, 401], [409, 417, 396, 404], [398, 411, 402, 409]];
    $rtKK = [[114, 110, 113, 111], [113, 116, 110, 112], [110, 114, 112, 112]];
    $sort = 1;
    $rtNo = 1;
    for ($w = 0; $w < 3; $w++) {
        $a = $lngCuts[$w]; $b = $lngCuts[$w + 1]; $mid = ($a + $b) / 2;
        $ins('map_regions', ['level' => 'rw', 'name' => sprintf('RW %02d', $w + 1), 'parent_name' => 'Desa Marga Mulya', 'color' => $rwColors[$w],
            'leader' => $rwLeaders[$w], 'households' => array_sum($rtKK[$w]), 'population' => array_sum($rtPop[$w]),
            'geojson' => json_encode($clip($rect($latN, $latS, $a, $b))), 'sort' => $sort++]);
        $cells = [[$latN, $latMid, $a, $mid], [$latN, $latMid, $mid, $b], [$latMid, $latS, $a, $mid], [$latMid, $latS, $mid, $b]];
        foreach ($cells as $k => [$n, $s, $ww, $e]) {
            $ins('map_regions', ['level' => 'rt', 'name' => sprintf('RT %02d', $rtNo), 'parent_name' => sprintf('RW %02d', $w + 1),
                'color' => $rtShades[$w][$k], 'leader' => $rtLeaders[$rtNo - 1], 'households' => $rtKK[$w][$k], 'population' => $rtPop[$w][$k],
                'geojson' => json_encode($clip($rect($n, $s, $ww, $e))), 'sort' => $sort++]);
            $rtNo++;
        }
    }
    $points = [
        ['Kantor Desa Marga Mulya', 'Kantor Desa', -2.4705, 111.9195, 'Pelayanan administrasi warga, Senin–Jumat 08.00–16.00 WIB'],
        ['Balai Desa', 'Fasilitas Umum', -2.4712, 111.9214, 'Tempat musyawarah dan kegiatan warga'],
        ['Taman Desa', 'Wisata', -2.4690, 111.9228, 'Ruang santai untuk keluarga'],
        ['SD Negeri 1 Marga Mulya', 'Pendidikan', -2.4560, 111.9030, null],
        ['SD Negeri 2 Marga Mulya', 'Pendidikan', -2.4850, 111.9330, null],
        ['SMP Negeri Marga Mulya', 'Pendidikan', -2.4620, 111.9260, null],
        ['Puskesmas Pembantu', 'Kesehatan', -2.4740, 111.9170, 'Layanan kesehatan dasar'],
        ['Posyandu Melati', 'Kesehatan', -2.4560, 111.9100, 'RW 01'],
        ['Posyandu Mawar', 'Kesehatan', -2.4820, 111.9200, 'RW 02'],
        ['Dermaga Sungai Kenanga', 'Wisata', -2.4955, 111.9180, 'Wisata perahu dan spot senja'],
        ['Pasar Desa', 'Fasilitas Umum', -2.4725, 111.9150, 'Buka setiap Selasa dan Sabtu pagi'],
        ['Galeri UMKM BUMDes', 'UMKM', -2.4695, 111.9240, 'Etalase produk warga'],
    ];
    foreach ($points as $i => [$n, $t, $la, $ln, $d]) {
        $ins('map_points', ['name' => $n, 'type' => $t, 'lat' => $la, 'lng' => $ln, 'description' => $d, 'sort' => $i]);
    }

    $places = [
        ['Sungai Kenanga', 'Wisata', 'Wisata Alam', 'Aliran sungai di batas selatan desa. Warga menyusuri sungai dengan perahu kayu, memancing, atau menikmati senja dari dermaga BUMDes.', 'Batas selatan desa', 'Sewa perahu Rp 10.000', '07.00–18.00', '0812 3450 1107', 'sungai', 1],
        ['Hamparan Persawahan', 'Wisata', 'Agrowisata', 'Sawah seluas 580 hektare yang menghijau sepanjang musim tanam. Tersedia paket belajar menanam dan memanen padi bersama petani.', 'RW 01 – RW 02', 'Gratis (paket edukasi Rp 25.000)', 'Setiap hari', '0812 3450 1108', 'sawah', 1],
        ['Perkebunan Desa', 'Wisata', 'Agrowisata', 'Kebun terluas di wilayah desa, mencakup 1.950 hektare. Pengunjung bisa berkeliling kebun buah dan belajar budidaya bersama kelompok tani.', 'RW 03', 'Gratis', '07.00–16.00', null, 'kebun', 1],
        ['Taman Desa', 'Wisata', 'Ruang Publik', 'Ruang santai untuk keluarga di dekat Kantor Desa, dengan area bermain anak, bangku taman, dan jalur jalan kaki yang teduh.', 'Dekat Kantor Desa', 'Gratis', '06.00–21.00', null, 'taman', 1],
        ['Bukit Pandang Senja', 'Wisata', 'Alam', 'Bukit kecil di sisi timur desa dengan pemandangan sawah dan kebun yang luas. Paling indah saat matahari terbenam.', 'RT 08 / RW 02', 'Gratis', 'Setiap hari', null, 'bukit', 0],
        ['Jalur Sepeda Lingkar Sawah', 'Wisata', 'Olahraga', 'Rute sepeda sepanjang 7 km mengelilingi sawah dan kebun. Ramai pesepeda setiap Minggu pagi.', 'Start di Balai Desa', 'Gratis', 'Minggu, 06.00–09.00', null, 'sepeda', 0],

        ['Beras Organik', 'UMKM', 'Pertanian', 'Beras organik hasil sawah kelompok Tani Maju yang ditanam tanpa pestisida kimia. Pulen, wangi, dan dikemas karung 5 kg dan 10 kg.', 'RT 03 / RW 01', 'Rp 15.000/kg', 'Setiap hari, 07.00–17.00', '0812 3450 1101', 'beras', 1],
        ['Anyaman Warga', 'UMKM', 'Kerajinan', 'Tas, tikar, dan keranjang dari rotan dan purun buatan kelompok perajin Anyaman Mulya. Sudah dipasarkan sampai Pangkalan Bun.', 'RT 02 / RW 01', 'Rp 45.000', '08.00–16.00', '0812 3450 1103', 'anyaman', 1],
        ['Hasil Kebun Segar', 'UMKM', 'Pertanian', 'Sayur dan buah segar langsung dari kebun warga: cabai, tomat, terong, pisang, dan buah musiman. Bisa pesan antar di dalam desa.', 'Galeri UMKM BUMDes', 'Rp 20.000/kg', '07.00–15.00', '0812 3450 1110', 'sayur', 1],
        ['Warung Sari Rasa', 'UMKM', 'Kuliner', 'Warung makan rumahan dengan menu nasi sayur, ikan bakar sungai, dan sambal khas Bu Sari. Favorit pekerja kebun saat makan siang.', 'RT 01 / RW 01', 'Rp 12.000–25.000', '07.00–20.00', '0812 3450 1102', 'warung', 0],
        ['Keripik Pisang Bu Rina', 'UMKM', 'Kuliner', 'Keripik pisang kepok renyah rasa original, manis, dan balado dari pisang kebun warga.', 'RT 06 / RW 02', 'Rp 10.000/bungkus', '08.00–17.00', '0812 3450 1104', 'keripik', 0],
        ['Madu Kelulut Marga Lestari', 'UMKM', 'Pertanian', 'Madu lebah kelulut yang dibudidayakan di pinggir hutan desa, dipanen langsung oleh peternak binaan BUMDes.', 'RT 10 / RW 03', 'Rp 85.000/botol', 'Pemesanan via WA', '0812 3450 1105', 'madu', 0],

        ['Festival Panen Raya', 'Budaya', 'Acara Tahunan', 'Perayaan syukur setelah panen padi setiap bulan Juni: pawai hasil bumi, lomba masak, bazar UMKM, dan pentas seni warga.', 'Lapangan Desa', 'Gratis', 'Setiap Juni', null, 'festival', 0],
        ['Sanggar Seni Karang Taruna', 'Budaya', 'Kesenian', 'Wadah anak muda belajar tari kreasi, musik, dan teater. Latihan rutin setiap Sabtu sore dan tampil di acara desa.', 'Balai Desa', 'Gratis', 'Sabtu, 15.00–17.00', '0812 3450 1109', 'sanggar', 0],
        ['Permainan Tradisional Anak', 'Budaya', 'Tradisi', 'Kegiatan rutin mengenalkan engklek, egrang, dan layang-layang kepada anak-anak.', 'Lapangan RW 02', 'Gratis', 'Minggu sore', null, 'permainan', 0],
        ['Jumat Bersih Gotong Royong', 'Budaya', 'Tradisi', 'Kebiasaan warga membersihkan saluran air, jalan, dan fasilitas umum bersama setiap Jumat pagi di pekan pertama.', 'Seluruh RT', 'Gratis', 'Jumat pekan pertama', null, 'gotongroyong', 0],
    ];
    foreach ($places as [$n, $c, $sub, $d, $loc, $price, $hours, $contact, $image, $feat]) {
        $ins('places', ['name' => $n, 'category' => $c, 'subcategory' => $sub, 'description' => $d, 'location' => $loc, 'price' => $price,
            'hours' => $hours, 'contact' => $contact, 'image_id' => $I($image), 'featured' => $feat, 'status' => 'Tampil']);
    }

    $news = [
        ['Gotong Royong Bersihkan Lingkungan Desa', 'Berita', '2026-11-08', 'gotongroyong', 1,
            "Ratusan warga dari seluruh RT turun bersama membersihkan fasilitas umum dan lingkungan sekitar menjelang musim hujan. Kegiatan dimulai pukul 07.00 WIB dan selesai menjelang siang.\n\nSelain membersihkan saluran air, warga juga merapikan bahu jalan, memangkas ranting pohon yang membahayakan, dan mengumpulkan sampah plastik untuk bank sampah Karang Taruna.\n\nKepala Desa Budi Santoso berterima kasih kepada seluruh warga dan berharap kebiasaan Jumat Bersih terus terjaga."],
        ['Program Tanam Pohon untuk Desa Hijau', 'Kegiatan', '2026-11-10', 'tanampohon', 0,
            "Warga bersama perangkat desa menanam 1.200 bibit pohon di sepanjang jalan poros desa dan bantaran Sungai Kenanga. Bibit yang ditanam meliputi trembesi, mahoni, durian, dan petai.\n\nSetiap RT mendapat tanggung jawab merawat bibit di wilayahnya selama satu tahun.\n\nWarga yang ingin ikut mengadopsi pohon bisa mendaftar di Kantor Desa pada jam pelayanan."],
        ['Jadwal dan Jam Pelayanan Kantor Desa', 'Berita', '2026-11-06', 'pelayanan', 0,
            "Pelayanan administrasi di Kantor Desa Marga Mulya dibuka Senin sampai Jumat, pukul 08.00 sampai 16.00 WIB. Sabtu, Minggu, dan hari libur nasional kantor tutup.\n\nWarga yang membutuhkan surat keterangan diharapkan membawa KTP dan Kartu Keluarga asli. Untuk pengaduan di luar jam kerja, gunakan layanan LaporDesa di website ini.\n\nTerima kasih atas perhatiannya."],
        ['Pelatihan UMKM Digital untuk Warga', 'Berita', '2026-10-28', 'pelatihan', 0,
            "Pelaku usaha lokal belajar memasarkan produk lewat media sosial dan marketplace di Balai Desa. Sebanyak 35 peserta belajar memotret produk dengan ponsel dan menulis deskripsi yang menarik.\n\nPelatihan diselenggarakan BUMDes Marga Lestari bersama pendamping desa. Setiap peserta pulang dengan akun toko daring yang siap digunakan.\n\nPendampingan lanjutan dilakukan setiap bulan selama tiga bulan ke depan."],
        ['Kerja Bakti Bersihkan Saluran Air', 'Kegiatan', '2026-10-20', 'gotongroyong', 0,
            "Seluruh RT ikut membersihkan saluran agar aman dari genangan. Lumpur dan sampah yang menyumbat drainase di RW 02 dan RW 03 berhasil diangkat.\n\nPemerintah desa menyediakan alat dan konsumsi, sementara warga bergotong royong sejak pagi.\n\nWarga diimbau tidak membuang sampah ke saluran air agar tidak terjadi banjir saat hujan deras."],
        ['Panen Raya Padi Musim Ini', 'Berita', '2026-10-12', 'panen', 0,
            "Hasil panen meningkat berkat perbaikan irigasi. Kelompok tani mencatat rata-rata 5 ton gabah kering panen per hektare.\n\nKenaikan hasil juga didukung penggunaan benih unggul dan pupuk organik buatan kelompok.\n\nSebagian hasil panen akan diolah menjadi beras organik kemasan oleh BUMDes."],
        ['Pendataan Warga Tahun 2026', 'Berita', '2026-10-03', 'pendataan', 0,
            "Petugas akan mendatangi tiap rumah untuk pendataan warga tahun 2026. Mohon siapkan Kartu Keluarga dan KTP seluruh anggota keluarga.\n\nPendataan berlangsung mulai 5 sampai 30 Oktober 2026 dan dilakukan oleh kader yang membawa surat tugas dari Pemerintah Desa.\n\nData yang terkumpul dipakai untuk perencanaan program desa dan bantuan sosial yang tepat sasaran."],
        ['Festival Desa Marga Mulya 2026 Sukses Digelar', 'Berita', '2026-09-25', 'festival', 0,
            "Festival Desa Marga Mulya 2026 berlangsung meriah selama dua hari di Lapangan Desa, diisi pentas seni, lomba masak olahan pangan lokal, dan bazar 40 UMKM.\n\nProduk yang paling diminati adalah beras organik, madu kelulut, dan anyaman warga.\n\nFestival tahun depan direncanakan menghadirkan lebih banyak paket wisata Sungai Kenanga."],
        ['Posyandu Balita Rutin Bulan November', 'Kegiatan', '2026-09-15', 'posyandu', 0,
            "Posyandu di enam titik kembali membuka layanan penimbangan, imunisasi, dan pemberian makanan tambahan pada minggu kedua setiap bulan.\n\nOrang tua diimbau membawa buku KIA.\n\nJadwal lengkap dapat ditanyakan ke kader posyandu di masing-masing RW."],
        ['Musyawarah Desa Rencana Kerja 2027', 'Organisasi', '2026-08-25', 'rapat', 0,
            "BPD bersama pemerintah desa menggelar musyawarah untuk menyusun Rencana Kerja Pemerintah Desa tahun 2027.\n\nUsulan prioritas warga antara lain perbaikan jalan desa di RW 02, penambahan lampu penerangan jalan, dan pengembangan wisata Sungai Kenanga.\n\nHasil musyawarah akan dibahas lebih lanjut dalam penyusunan APBDes 2027."],
        ['Jalan Usaha Tani Sepanjang 1,2 Km Selesai Dibeton', 'Berita', '2026-08-15', 'jalan', 0,
            "Jalan usaha tani di RW 03 sepanjang 1,2 km kini sudah dibeton dan menghubungkan permukiman dengan area perkebunan.\n\nSebelumnya jalan sulit dilewati kendaraan pengangkut hasil panen saat musim hujan.\n\nPembangunan dibiayai dana desa 2026 dan dikerjakan secara padat karya oleh warga setempat."],
        ['PKK Gelar Lomba Pekarangan Hijau', 'Organisasi', '2026-08-05', 'taman', 0,
            "Tim Penggerak PKK menggelar lomba pekarangan hijau antar-RT. Penilaian meliputi tanaman obat keluarga, sayuran, kebersihan, dan pengolahan sampah rumah tangga.\n\nJuara pertama diraih RT 07 yang memanfaatkan pekarangan sempit dengan rak vertikal dan kolam ikan kecil.\n\nLomba ini menjadi bagian dari gerakan kemandirian pangan keluarga."],
        ['Karang Taruna Bentuk Kepengurusan Baru', 'Organisasi', '2026-07-20', 'sanggar', 0,
            "Karang Taruna Desa Marga Mulya membentuk kepengurusan baru periode 2026–2029 melalui musyawarah anggota di Balai Desa.\n\nPengurus baru menyiapkan tiga program utama: sanggar seni, bank sampah, dan pelatihan keterampilan digital bagi pemuda.\n\nPemuda desa yang ingin bergabung bisa menghubungi pengurus melalui media sosial Karang Taruna."],
    ];
    foreach ($news as [$t, $c, $d, $image, $feat, $content]) {
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($t)), '-');
        $ins('news', ['title' => $t, 'slug' => $slug, 'category' => $c, 'featured' => $feat, 'excerpt' => mb_substr(strtok($content, "\n"), 0, 180),
            'content' => $content, 'image_id' => $I($image), 'author' => 'Admin Desa', 'published_at' => $d, 'status' => 'Terbit']);
    }

    $gallery = [
        ['Gotong royong', 'Kegiatan', 'gotongroyong', 'Disetujui', null],
        ['Panen raya', 'Kegiatan', 'panen', 'Disetujui', null],
        ['Taman desa', 'Wisata', 'taman', 'Disetujui', null],
        ['Pelatihan UMKM', 'UMKM', 'pelatihan', 'Disetujui', null],
        ['Sungai Kenanga', 'Wisata', 'sungai', 'Disetujui', $warga1],
        ['Kerja bakti', 'Kegiatan', 'pendataan', 'Disetujui', null],
        ['Pelayanan kantor desa', 'Pemerintahan', 'pelayanan', 'Disetujui', null],
        ['Hamparan sawah', 'Wisata', 'sawah', 'Disetujui', null],
        ['Tanam pohon', 'Kegiatan', 'tanampohon', 'Disetujui', null],
        ['Musyawarah desa', 'Pemerintahan', 'rapat', 'Disetujui', null],
        ['Anyaman warga', 'UMKM', 'anyaman', 'Disetujui', null],
        ['Senja di bukit', 'Wisata', 'bukit', 'Disetujui', $warga2],
        ['Festival desa', 'Kegiatan', 'festival', 'Disetujui', null],
        ['Kantor Desa Marga Mulya', 'Pemerintahan', 'kantor', 'Disetujui', null],
        ['Bersepeda pagi keliling sawah', 'Wisata', 'sepeda', 'Menunggu', $warga1],
        ['Hasil kebun warga', 'UMKM', 'sayur', 'Menunggu', $warga2],
    ];
    $total = count($gallery);
    foreach ($gallery as $i => [$t, $c, $image, $st, $uid]) {
        $ins('gallery', ['title' => $t, 'category' => $c, 'image_id' => $I($image), 'status' => $st, 'user_id' => $uid,
            'uploader_name' => $uid === $warga1 ? 'Rahmat Hidayat' : ($uid === $warga2 ? 'Nur Aini' : 'Admin Desa'),
            'sort' => 0, 'created_at' => date('Y-m-d H:i:s', strtotime('2026-11-12 -' . ($i * 3) . ' days'))]);
    }

    $cats = [['Jalan Rusak', 'construction'], ['Sampah & Kebersihan', 'trash-2'], ['Lampu Jalan', 'lightbulb'], ['Air Bersih', 'droplets'],
        ['Drainase & Banjir', 'waves'], ['Fasilitas Umum', 'building-2'], ['Keamanan & Ketertiban', 'shield-alert'], ['Lainnya', 'circle-alert']];
    foreach ($cats as $i => [$n, $icon]) {
        $ins('report_categories', ['name' => $n, 'icon' => $icon, 'sort' => $i]);
    }
    $reports = [
        ['LD-2610-A1F3C', 'Jalan Rusak', 'Jl. Poros RW 02 depan SDN 2 (RT 06)', -2.4850, 111.9320, 'Jalan berlubang cukup dalam sekitar 3 meter, sering membuat motor tergelincir saat hujan.', 'Selesai', '2026-10-03 08:12', $warga1, 'Rahmat Hidayat', 'jalan', 'Sudah ditambal dengan sirtu dan dipadatkan oleh tim padat karya.'],
        ['LD-2610-B72D1', 'Lampu Jalan', 'Simpang empat Balai Desa (RT 02)', -2.4712, 111.9210, 'Lampu penerangan jalan mati sudah satu minggu, kondisi gelap di malam hari.', 'Selesai', '2026-10-09 19:40', $warga2, 'Nur Aini', null, 'Lampu sudah diganti dengan LED tenaga surya.'],
        ['LD-2610-C0E44', 'Sampah & Kebersihan', 'Belakang Pasar Desa (RT 05)', -2.4730, 111.9148, 'Sampah pasar menumpuk dan belum diangkut sejak hari Sabtu.', 'Selesai', '2026-10-14 07:05', $warga2, 'Nur Aini', null, 'Sampah sudah diangkut. Jadwal angkut ditambah menjadi 2 kali seminggu.'],
        ['LD-2610-D19A7', 'Drainase & Banjir', 'Saluran air RT 09', null, null, 'Saluran tersumbat lumpur sehingga air meluap ke halaman rumah warga saat hujan deras.', 'Diproses', '2026-10-22 16:30', $warga1, 'Rahmat Hidayat', null, 'Normalisasi saluran dijadwalkan pada kegiatan Jumat Bersih.'],
        ['LD-2610-E5B20', 'Air Bersih', 'Sumur bor umum RT 11', null, null, 'Pompa sumur bor umum mati sehingga 30 KK kesulitan air bersih.', 'Diproses', '2026-10-27 09:15', $warga1, 'Rahmat Hidayat', null, 'Teknisi BUMDes sedang memesan suku cadang pompa.'],
        ['LD-2611-F2C81', 'Fasilitas Umum', 'Dermaga Sungai Kenanga', -2.4955, 111.9180, 'Papan lantai dermaga mulai lapuk dan beberapa paku mencuat, berbahaya untuk anak-anak.', 'Diterima', '2026-11-02 10:20', $warga2, 'Nur Aini', 'sungai', 'Laporan diterima, akan disurvei pengelola BUMDes.'],
        ['LD-2611-0A9B3', 'Keamanan & Ketertiban', 'Pos Kamling RT 03', null, null, 'Lampu dan kentongan pos kamling rusak, ronda malam jadi kurang efektif.', 'Diterima', '2026-11-05 21:10', $warga2, 'Nur Aini', null, null],
        ['LD-2611-1C7E6', 'Jalan Rusak', 'Jalan usaha tani RW 01 (RT 04)', -2.4600, 111.9060, 'Jembatan kayu kecil di jalan usaha tani mulai patah di sisi kanan.', 'Menunggu', '2026-11-12 06:45', $warga1, 'Rahmat Hidayat', null, null],
        ['LD-2611-2D5F0', 'Lampu Jalan', 'Jl. Melati RT 08', null, null, 'Dua tiang lampu jalan mati bergantian, mohon dicek kabelnya.', 'Menunggu', '2026-11-15 18:55', $warga1, 'Rahmat Hidayat', null, null],
        ['LD-2611-3E2A9', 'Lainnya', 'Lapangan Desa', null, null, 'Mohon penambahan tempat sampah terpilah di lapangan desa karena banyak pengunjung saat sore hari.', 'Ditolak', '2026-11-06 15:00', $warga2, 'Nur Aini', null, 'Usulan sudah masuk program bank sampah Karang Taruna, sehingga laporan ini ditutup dan dialihkan ke program tersebut.'],
        ['LD-2611-4F8B4', 'Sampah & Kebersihan', 'Tepi jalan poros RT 10', -2.4800, 111.9400, 'Ada pembuangan sampah liar di tepi jalan dekat kebun.', 'Menunggu', '2026-11-18 07:30', $warga2, 'Nur Aini', 'gotongroyong', null],
        ['LD-2609-5A1C2', 'Air Bersih', 'Pamsimas RW 03', null, null, 'Air Pamsimas keruh selama tiga hari.', 'Selesai', '2026-09-18 08:00', $warga1, 'Rahmat Hidayat', null, 'Bak penampung sudah dikuras dan filter diganti.'],
    ];
    foreach ($reports as [$ticket, $cat, $loc, $lat, $lng, $desc, $status, $created, $uid, $name, $photo, $note]) {
        $accepted = $status !== 'Menunggu' ? date('Y-m-d', strtotime($created . ' +1 day')) . ' 09:' . sprintf('%02d', (crc32($ticket) % 50) + 5) . ':00' : null;
        $finished = $status === 'Selesai' ? date('Y-m-d', strtotime($created . ' +6 days')) . ' 14:20:00' : null;
        $rid = $ins('reports', ['ticket' => $ticket, 'user_id' => $uid, 'reporter_name' => $name, 'category' => $cat, 'location' => $loc,
            'lat' => $lat, 'lng' => $lng, 'description' => $desc, 'photo_id' => $photo ? $I($photo) : null, 'status' => $status, 'admin_note' => $note,
            'created_at' => $created, 'accepted_at' => $status === 'Ditolak' ? null : $accepted, 'finished_at' => $finished]);
        $ins('report_updates', ['report_id' => $rid, 'status' => 'Menunggu', 'note' => 'Laporan berhasil dikirim dan menunggu verifikasi petugas desa.', 'created_at' => $created]);
        if ($status === 'Ditolak') {
            $ins('report_updates', ['report_id' => $rid, 'status' => 'Ditolak', 'note' => $note, 'created_at' => date('Y-m-d', strtotime($created . ' +1 day')) . ' 10:00:00']);
            continue;
        }
        if ($accepted) {
            $ins('report_updates', ['report_id' => $rid, 'status' => 'Diterima', 'note' => $status === 'Diterima' && $note ? $note : 'Laporan diverifikasi dan diteruskan ke petugas terkait.', 'created_at' => $accepted]);
        }
        if (in_array($status, ['Diproses', 'Selesai'], true)) {
            $ins('report_updates', ['report_id' => $rid, 'status' => 'Diproses', 'note' => $status === 'Diproses' ? $note : 'Petugas sedang menangani di lapangan.', 'created_at' => date('Y-m-d', strtotime($accepted . ' +1 day')) . ' 10:30:00']);
        }
        if ($status === 'Selesai') {
            $ins('report_updates', ['report_id' => $rid, 'status' => 'Selesai', 'note' => $note, 'created_at' => $finished]);
        }
    }

    $ins('messages', ['name' => 'Andi Pratama', 'email' => 'andi@contoh.id', 'phone' => null, 'subject' => 'Kunjungan studi banding',
        'message' => 'Selamat siang, kami dari kelompok tani desa tetangga ingin studi banding tentang beras organik. Kapan waktu yang tepat?', 'is_read' => 0]);
    $ins('messages', ['name' => 'Maya Sari', 'email' => null, 'phone' => '081377788899', 'subject' => 'Syarat surat domisili',
        'message' => 'Mohon info syarat membuat surat keterangan domisili untuk keperluan kerja.', 'is_read' => 1]);
}
