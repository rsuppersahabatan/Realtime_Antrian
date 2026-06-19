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

    <style>
        .visit-type-wrap {
            display:flex;
            justify-content:center;
            align-items:flex-start;
            gap:32px;
            flex-wrap:wrap;
            padding:48px 16px 32px;
        }
        .visit-type-option {
            display:flex;
            flex-direction:column;
            align-items:center;
            max-width:260px;
            flex:1 1 220px;
        }
        .btn-visit-type {
            border:none;
            border-radius:9999px;
            padding:22px 56px;
            font-size:22px;
            font-weight:600;
            color:#0f172a;
            cursor:pointer;
            box-shadow:0 4px 14px rgba(0,0,0,.12);
            transition:transform .15s ease, box-shadow .15s ease, filter .15s ease;
            width:100%;
            min-width:200px;
        }
        .btn-visit-type:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,0,0,.16); filter:brightness(1.03); }
        .btn-visit-type:active { transform:translateY(0); }
        .btn-visit-type.btn-checkin { background:#5ed16a; }
        .btn-visit-type.btn-daftar  { background:#8ccdd6; }
        .visit-type-desc {
            margin-top:14px;
            font-size:13px;
            line-height:1.5;
            color:#475569;
            text-align:center;
            padding:0 6px;
        }
        .visit-type-title {
            text-align:center;
            margin:24px 0 0;
            font-weight:700;
            color:#0a7a6b;
            letter-spacing:.5px;
        }
        .visit-type-sub {
            text-align:center;
            color:#64748b;
            margin-top:6px;
            font-size:13px;
        }
        .pilihan-badge {
            display:inline-block;
            padding:4px 12px;
            border-radius:9999px;
            font-size:12px;
            font-weight:700;
            letter-spacing:.5px;
            color:#0f172a;
        }
        .pilihan-badge.checkin { background:#5ed16a; }
        .pilihan-badge.daftar  { background:#8ccdd6; }
        .btn-ganti-pilihan {
            background:transparent;
            border:none;
            color:#0a7a6b;
            font-size:12px;
            text-decoration:underline;
            cursor:pointer;
            padding:0;
            margin-left:8px;
        }
    </style>

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
                        <h3 class="section-title" style="margin-top:0">DATA PENGUNJUNG</h3>
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
                            value="<?= htmlspecialchars($nik_old ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <small class="text-muted">NIK digunakan untuk mengidentifikasi antrian Anda. Data tidak dibagikan ke publik.</small>
                    </div>

                    <h3 class="section-title">PILIH LAYANAN
                    <p class="text-muted" style="margin-top: 14px; font-size: 12px;">
                        Setelah mengambil nomor, perhatikan panggilan di layar antrian.
                    </p>
                    </h3>

                    <input type="hidden" name="id_layanan" id="idLayanan" value="">

                <?php if ( ! empty($layanan)): ?>
                    <?php $vh = 320; ?>
                    <div
                        id="layananRunning"
                        class="layanan-running"
                        style="height:<?= $vh ?>px;overflow-y:auto;position:relative;scroll-behavior:auto;mask-image:linear-gradient(180deg, transparent 0, #000 24px, #000 calc(100% - 24px), transparent 100%);-webkit-mask-image:linear-gradient(180deg, transparent 0, #000 24px, #000 calc(100% - 24px), transparent 100%);">
                        <div class="layanan-running-track">
                            <?php for ($pass = 0; $pass < 1; $pass++): ?>
                                <?php foreach ($layanan as $ly): ?>
                                    <div class="layanan-card clearfix"<?= $pass === 1 ? ' aria-hidden="true"' : '' ?>>
                                        <span class="layanan-kode"><?= htmlspecialchars($ly['kode_huruf'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="layanan-nama"><?= htmlspecialchars($ly['nama_layanan'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <button type="button" data-id="<?= (int) $ly['id'] ?>" class="btn-ambil btn-pilih-layanan"<?= $pass === 1 ? ' tabindex="-1"' : '' ?>>AMBIL NOMOR</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <small class="text-muted" style="display:block;margin-top:6px;">
                        Arahkan kursor (atau scroll) ke daftar layanan untuk menjeda gerakan, lalu klik <strong>AMBIL NOMOR</strong>.
                    </small>
                    <style>
                        /* Sembunyikan scrollbar default tapi tetap bisa di-scroll dengan mouse wheel/touch. */
                        .layanan-running { scrollbar-width: thin; scrollbar-color: rgba(0,186,174,0.5) transparent; }
                        .layanan-running::-webkit-scrollbar { width: 8px; }
                        .layanan-running::-webkit-scrollbar-track { background: transparent; }
                        .layanan-running::-webkit-scrollbar-thumb { background: rgba(0,186,174,0.45); border-radius: 4px; }
                        .layanan-running::-webkit-scrollbar-thumb:hover { background: rgba(0,186,174,0.75); }
                    </style>
                    <script>
                    (function () {
                        var box = document.getElementById('layananRunning');
                        if ( ! box) return;

                        var track = box.querySelector('.layanan-running-track');
                        if ( ! track) return;

                        var paused      = false;
                        var pausedManual = false;
                        var pixelsPerSec = 30;     // kecepatan auto-scroll (px / detik)
                        var lastTs       = null;
                        var manualTimer  = null;

                        function step(ts) {
                            if (lastTs === null) lastTs = ts;
                            var dt = ts - lastTs;
                            lastTs = ts;

                            if ( ! paused && ! pausedManual) {
                                var half = track.scrollHeight / 2;
                                box.scrollTop += (pixelsPerSec * dt) / 1000;
                                // Loop seamless: saat lewat setengah track (akhir set pertama),
                                // mundur tepat sebanyak half — visual tetap kontinyu.
                                if (box.scrollTop >= half) {
                                    box.scrollTop -= half;
                                }
                            }
                            requestAnimationFrame(step);
                        }
                        requestAnimationFrame(step);

                        // Hover & focus = pause supaya user bisa klik tombol.
                        box.addEventListener('mouseenter', function () { paused = true; });
                        box.addEventListener('mouseleave', function () { paused = false; });
                        box.addEventListener('focusin',    function () { paused = true; });
                        box.addEventListener('focusout',   function () { paused = false; });

                        // Scroll manual (wheel / drag scrollbar / touch) = pause sementara,
                        // resume otomatis 1.5 detik setelah aktivitas terakhir berhenti.
                        function bumpManual() {
                            pausedManual = true;
                            if (manualTimer) clearTimeout(manualTimer);
                            manualTimer = setTimeout(function () { pausedManual = false; }, 1500);
                        }
                        box.addEventListener('wheel',     bumpManual, { passive: true });
                        box.addEventListener('touchmove', bumpManual, { passive: true });
                        box.addEventListener('scroll', function () {
                            // scroll juga di-trigger oleh auto-scroll; bumpManual hanya kalau
                            // sedang pause (misal hover) — artinya user yang scroll.
                            if (paused) bumpManual();
                        });
                    })();
                    </script>
                <?php else: ?>
                    <div class="alert alert-warning">Belum ada layanan yang tersedia. Silakan hubungi petugas.</div>
                <?php endif; ?>

                </div><!-- /#formStep -->

                <script>
                (function () {
                    var visitStep   = document.getElementById('visitTypeStep');
                    var formStep    = document.getElementById('formStep');
                    var tipeInput   = document.getElementById('tipeKunjungan');
                    var nikInput    = document.getElementById('nik');
                    var pilihanBdg  = document.getElementById('pilihanBadge');
                    var btnGanti    = document.getElementById('btnGantiPilihan');
                    var form        = document.getElementById('formAmbil');

                    function showForm(tipe) {
                        tipeInput.value = tipe;
                        if (tipe === 'checkin') {
                            pilihanBdg.textContent = 'CHECK IN';
                            pilihanBdg.className   = 'pilihan-badge checkin';
                        } else {
                            pilihanBdg.textContent = 'DAFTAR';
                            pilihanBdg.className   = 'pilihan-badge daftar';
                        }
                        if (visitStep) visitStep.style.display = 'none';
                        if (formStep)  formStep.style.display  = 'block';
                        if (nikInput) {
                            nikInput.setAttribute('required', 'required');
                            try { nikInput.focus(); } catch (e) {}
                        }
                    }

                    function showVisit() {
                        tipeInput.value = '';
                        if (visitStep) visitStep.style.display = 'block';
                        if (formStep)  formStep.style.display  = 'none';
                        if (nikInput)  nikInput.removeAttribute('required');
                    }

                    document.querySelectorAll('.btn-visit-type').forEach(function (btn) {
                        btn.addEventListener('click', function () { showForm(this.dataset.tipe); });
                    });
                    if (btnGanti) btnGanti.addEventListener('click', showVisit);

                    // Saat reload akibat error (NIK salah / layanan belum dipilih),
                    // server kirim flashdata "nik" / "error" — buka langsung form supaya
                    // user tidak perlu klik ulang Check in / Daftar.
                    var hasOldNik = <?= ! empty($nik_old) ? 'true' : 'false' ?>;
                    var hasError  = <?= ! empty($error)   ? 'true' : 'false' ?>;
                    if (hasOldNik || hasError) {
                        showForm('checkin');
                    }

                    if (form) {
                        form.addEventListener('submit', function (e) {
                            if ( ! tipeInput.value) {
                                e.preventDefault();
                                showVisit();
                            }
                        });
                    }
                })();
                </script>

            <?= form_close() ?>
        </div>
    </div>

<?php endif; ?>

</div>

<!-- ====== FOOTER RUNNING TEXT (fixed di paling bawah halaman) ====== -->
<style>
    body { padding-bottom: 56px; }
    .welcome-footer-news {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        background: #1f2d00;
        color: #ccdb2a;
        padding: 12px 0;
        font-size: 16px;
        font-weight: 600;
        border-top: 2px solid #ccdb2a;
        overflow: hidden;
        z-index: 1000;
    }
    .welcome-footer-news .running-text {
        display: inline-block;
        white-space: nowrap;
        padding-left: 100%;
        animation: welcome-marquee 28s linear infinite;
    }
    @keyframes welcome-marquee {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-100%); }
    }
</style>

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
