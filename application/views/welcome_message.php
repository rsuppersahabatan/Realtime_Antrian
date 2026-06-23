<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Antrian RS Persahabatan</title>
    <script defer src="https://umami.persahabatan.co.id/script.js" data-website-id="084ea29a-39c4-44d7-ba5a-534fb2daacb6"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>

    <?= isset($minified_css) ? $minified_css : '<link rel="stylesheet" href="' . base_url('assets/frameworks/domprojects/css/welcome.css') . '">' ?>
</head>
<body>

<div class="header-blue no-print">
    <div class="container" style="display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap;">
        <img src="https://cdn.jsdelivr.net/gh/rsuppersahabatan/website-assets@0.0.3/img/logoRS.png"
             alt="Logo RS Persahabatan"
             style="height:64px;width:auto;display:block;">
        <div style="text-align:left;">
            <h1>ANTRIAN ONLINE</h1>
            <small>ONLINE REALTIME ANTRIAN RS PERSAHABATAN</small>
        </div>
    </div>
</div>

<div class="container">

<?php if (($mode ?? 'pilih') === 'tiket' && ! empty($tiket)): ?>

    <?php
        $waktu_ambil = ! empty($tiket['waktu_ambil']) ? date('d M Y, H:i', strtotime($tiket['waktu_ambil'])) : date('d M Y, H:i');
        $posisi      = isset($tiket['antrian_di_depan']) ? (int) $tiket['antrian_di_depan'] : 0;
    ?>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="tiket-box">
                <div class="tiket-label">NOMOR ANTRIAN ANDA</div>
                <div class="tiket-nomor"><?= htmlspecialchars($tiket['nomor_antrian'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="tiket-layanan"><?= htmlspecialchars($tiket['nama_layanan'], ENT_QUOTES, 'UTF-8') ?></div>

                <div class="tiket-meta text-left">
                    <div class="row">
                        <div class="col-xs-6"><strong>Tanggal / Jam</strong><br><?= htmlspecialchars($waktu_ambil, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="col-xs-6"><strong>Kode Layanan</strong><br><?= htmlspecialchars($tiket['kode_huruf'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <?php if ( ! empty($tiket['nik'])): ?>
                        <?php
                            // Sensor NIK di tampilan: 6 digit tengah diganti * agar tidak terbaca orang lain.
                            $nik       = (string) $tiket['nik'];
                            $nik_mask  = strlen($nik) >= 16
                                ? substr($nik, 0, 6) . str_repeat('*', 6) . substr($nik, -4)
                                : $nik;
                        ?>
                        <div style="margin-top:10px;"><strong>NIK</strong><br><?= htmlspecialchars($nik_mask, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <div class="tiket-posisi">
                    <?php if ($posisi > 0): ?>
                        Masih ada <strong><?= $posisi ?></strong> antrian di depan Anda
                    <?php else: ?>
                        Anda adalah antrian berikutnya — harap siap dipanggil
                    <?php endif; ?>
                </div>

                <div style="margin-top: 22px;" class="no-print">
                    <a href="<?= site_url('welcome') ?>" class="btn btn-default">&larr; Kembali</a>
                    <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Tiket</button>
                </div>
            </div>

            <p class="text-center text-muted no-print" style="margin-top: 14px;">
                Mohon tunggu nomor Anda dipanggil pada layar antrian.
            </p>
        </div>
    </div>

<?php else: ?>

    <?php if ( ! empty($error)): ?>
        <div class="alert alert-danger alert-inline"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <?= form_open('welcome/ambil', ['id' => 'formAmbil', 'autocomplete' => 'off']); ?>

                <input type="hidden" name="tipe_kunjungan" id="tipeKunjungan" value="">

                <div id="visitTypeStep">
                    <h3 class="visit-type-title">SILAKAN PILIH KEPERLUAN ANDA</h3>
                    <p class="visit-type-sub">Pilih <strong>Check in</strong> jika sudah memiliki janji, atau <strong>Daftar</strong> untuk pendaftaran baru.</p>
                    <div class="visit-type-wrap">
                        <div class="visit-type-option">
                            <button type="button" class="btn-visit-type btn-checkin" data-tipe="checkin">Check in</button>
                            <p class="visit-type-desc">
                                Untuk pendonor yang sudah melakukan pendaftaran melalui link
                                dan datang sesuai tanggal rencana donor.
                            </p>
                        </div>
                        <div class="visit-type-option">
                            <button type="button" class="btn-visit-type btn-daftar" data-tipe="daftar">Daftar</button>
                            <p class="visit-type-desc">
                                Untuk pendaftaran <strong>offline</strong> di UTD RS Persahabatan.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="formStep" style="display:none;">
                    <div style="margin-bottom:10px;">
                        <span class="pilihan-badge" id="pilihanBadge">CHECK IN</span>
                        <button type="button" class="btn-ganti-pilihan" id="btnGantiPilihan">ganti pilihan</button>
                    </div>

                    <div class="nik-panel">
                        <h3 class="section-title" style="margin-top:0">INPUT DATA PENGUNJUNG</h3>
                        <label for="nik" style="font-weight:bold;">NIK (16 digit KTP)</label>
                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]{16}"
                            maxlength="16"
                            minlength="16"
                            id="nik"
                            name="nik"
                            class="form-control nik-input"
                            placeholder="Masukkan 16 digit NIK KTP"
                            value="<?= htmlspecialchars($nik_old ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="off">
                        <small class="text-muted">NIK digunakan untuk mengidentifikasi antrian Anda. Data tidak dibagikan ke publik.</small>
                    </div>

                    <div id="apiState" style="display:none;margin-top:18px;"></div>
                </div><!-- /#formStep -->

                <script>
                // Nilai dinamis dari server diteruskan ke welcome.js lewat objek
                // konfigurasi ini. Logika alur pilih keperluan + auto-call API
                // berada di assets/frameworks/domprojects/js/welcome.js.
                window.WELCOME_CFG = {
                    // Proxy server-side (Welcome::self_checkin / self_register)
                    // agar JWT/kredensial UTDRS tidak terekspos di browser.
                    endpoints: {
                        checkin: <?= json_encode(site_url('welcome/self_checkin')) ?>,
                        daftar:  <?= json_encode(site_url('welcome/self_register')) ?>
                    },
                    // Flashdata reload akibat error (NIK salah / layanan belum dipilih).
                    hasOldNik: <?= ! empty($nik_old) ? 'true' : 'false' ?>,
                    hasError:  <?= ! empty($error)   ? 'true' : 'false' ?>
                };
                </script>

            <?= form_close() ?>
        </div>
    </div>

<?php endif; ?>

</div>

<!-- ====== FOOTER RUNNING TEXT (fixed di paling bawah halaman) ====== -->
<div class="welcome-footer-news no-print">
    <div class="running-text">
        <strong>PENGUMUMAN:</strong>
        Jam operasional : 12:00 - 19:00 WIB. &nbsp;|&nbsp;
        Gunakan selalu masker di lingkungan rumah sakit. &nbsp;|&nbsp;
        Selamat datang di UPDRS RS Persahabatan. &nbsp;|&nbsp;
        Mohon menunggu nomor antrian Anda dipanggil.
    </div>
</div>

<?= isset($minified_js) ? $minified_js : '<script src="' . base_url('assets/frameworks/domprojects/js/welcome.js') . '"></script>' ?>

</body>
</html>
