<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Antrian Online - RS Persahabatan</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>

    <link rel="stylesheet" href="<?= base_url('assets/frameworks/domprojects/css/welcome.css') ?>">
</head>
<body>

<div class="header-blue no-print">
    <div class="container">
        <h1>ANTRIAN ONLINE</h1>
        <small>RS PERSAHABATAN</small>
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

                <h3 class="section-title">PILIH LAYANAN</h3>

                <input type="hidden" name="id_layanan" id="idLayanan" value="">

                <?php if ( ! empty($layanan)): ?>
                    <?php foreach ($layanan as $ly): ?>
                        <div class="layanan-card clearfix">
                            <span class="layanan-kode"><?= htmlspecialchars($ly['kode_huruf'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="layanan-nama"><?= htmlspecialchars($ly['nama_layanan'], ENT_QUOTES, 'UTF-8') ?></span>
                            <button type="button" data-id="<?= (int) $ly['id'] ?>" class="btn-ambil btn-pilih-layanan">AMBIL NOMOR</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">Belum ada layanan yang tersedia. Silakan hubungi petugas.</div>
                <?php endif; ?>

            <?= form_close() ?>
        </div>

        <div class="col-md-4">
            <h3 class="section-title">LOKET DIBUKA</h3>
            <div class="loket-list">
                <?php if ( ! empty($loket)): ?>
                    <?php foreach ($loket as $lk): ?>
                        <div class="loket-item">
                            <span class="kode"><?= htmlspecialchars($lk['kode_huruf'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                            <strong><?= htmlspecialchars($lk['nama_loket'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($lk['nama_layanan'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="loket-item text-muted">Belum ada loket yang dibuka saat ini.</div>
                <?php endif; ?>
            </div>

            <p class="text-muted" style="margin-top: 14px; font-size: 12px;">
                Setelah mengambil nomor, perhatikan panggilan di layar antrian.
            </p>

            <div class="clock-wrap">
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

<script src="<?= base_url('assets/frameworks/domprojects/js/welcome.js') ?>"></script>

</body>
</html>
