<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Antrian Online - RS Persahabatan</title>
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
        <div class="col-md-8">
            <?= form_open('welcome/ambil', ['id' => 'formAmbil', 'autocomplete' => 'off']); ?>

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
                        value="<?= htmlspecialchars($nik_old ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
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

            <?= form_close() ?>
        </div>

        <div class="col-md-4">

            <div class="video-wrap" style="position:relative;width:100%;padding-top:56.25%;overflow:hidden;border-radius:12px;">
                <iframe
                    src="https://www.youtube.com/embed/ebZwRzwEpT8?autoplay=1&mute=1&loop=1&playlist=ebZwRzwEpT8&controls=0&showinfo=0&rel=0"
                    frameborder="0"
                    allow="autoplay; encrypted-media"
                    allowfullscreen
                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"
                ></iframe>
            </div>

            <div class="running-text-wrap" style="margin-top:14px;background:#0a7a6b;color:#fff;border-radius:10px;overflow:hidden;padding:10px 0;">
                <div class="running-text" style="white-space:nowrap;display:inline-block;padding-left:100%;animation:marquee 22s linear infinite;font-weight:600;font-size:14px;">
                    <strong>PENGUMUMAN:</strong>
                    Jam operasional : 12:00 - 19:00 WIB. &nbsp;|&nbsp;
                    Gunakan selalu masker di lingkungan rumah sakit. &nbsp;|&nbsp;
                    Selamat datang di UPDRS RS Persahabatan. &nbsp;|&nbsp;
                    Mohon menunggu nomor antrian Anda dipanggil.
                </div>
            </div>
            <style>
                @keyframes marquee {
                    0%   { transform: translateX(0); }
                    100% { transform: translateX(-100%); }
                }
            </style>

            <div class="clock-wrap" style="margin-top:55px;">
                <div class="clock" aria-label="Jam saat ini">
                    <div class="hour"   id="hour"></div>
                    <div class="minute" id="minute"></div>
                    <div class="second" id="second"></div>
                    <div class="numbers">
                        <div class="number number1"><span>1</span></div>
                        <div class="number number2"><span>2</span></div>
                        <div class="number number3"><span>3</span></div>
                        <div class="number number4"><span>4</span></div>
                        <div class="number number5"><span>5</span></div>
                        <div class="number number6"><span>6</span></div>
                        <div class="number number7"><span>7</span></div>
                        <div class="number number8"><span>8</span></div>
                        <div class="number number9"><span>9</span></div>
                        <div class="number number10"><span>10</span></div>
                        <div class="number number11"><span>11</span></div>
                        <div class="number number12"><span>12</span></div>
                    </div>
                </div>
            </div>
            <div class="clock-digital" id="clockDigital">--:--:--</div>
        </div>


    </div>

<?php endif; ?>

</div>

<?= isset($minified_js) ? $minified_js : '<script src="' . base_url('assets/frameworks/domprojects/js/welcome.js') . '"></script>' ?>

</body>
</html>
