<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Antrian Online - RS Persahabatan</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>

    <style>
    :root {
        --dp-primary: #00baae;
        --dp-primary-dark: #009186;
        --dp-accent: #ccdb2a;
        --dp-accent-dark: #a7b322;
        --dp-dark: #1f2d00;
    }

    ::selection      { background-color: var(--dp-primary); color: #fff; }
    ::-moz-selection { background-color: var(--dp-primary); color: #fff; }

    body {
        background-color: #f4f6f0;
        font: 14px/20px "Helvetica Neue", Helvetica, Arial, sans-serif;
        color: #4F5155;
        padding-bottom: 40px;
    }

    .header-blue {
        background: linear-gradient(90deg, var(--dp-primary) 0%, var(--dp-primary-dark) 100%);
        color: #fff;
        padding: 22px 0;
        margin-bottom: 24px;
        border-bottom: 5px solid var(--dp-accent);
        text-align: center;
    }
    .header-blue h1 { margin: 0; font-weight: bold; font-size: 26px; }
    .header-blue small { color: #e8fffd; letter-spacing: 1px; }

    .alert-inline { margin-bottom: 18px; }

    .section-title {
        font-size: 16px;
        color: var(--dp-primary-dark);
        font-weight: bold;
        letter-spacing: 0.5px;
        margin: 0 0 12px;
        padding-bottom: 6px;
        border-bottom: 2px solid var(--dp-accent);
    }

    /* --- Panel NIK --- */
    .nik-panel {
        background: #fff;
        border: 2px solid var(--dp-accent);
        border-radius: 10px;
        padding: 16px 18px;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0,186,174,.05);
    }
    .nik-input {
        font-size: 20px;
        letter-spacing: 3px;
        font-weight: bold;
        height: auto;
        padding: 10px 14px;
        border: 2px solid var(--dp-primary);
        margin-bottom: 6px;
    }
    .nik-input:focus {
        border-color: var(--dp-primary-dark);
        box-shadow: 0 0 0 3px rgba(0,186,174,.18);
        outline: none;
    }

    /* --- Daftar layanan --- */
    .layanan-card {
        background: #fff;
        border: 2px solid var(--dp-primary);
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 16px;
        box-shadow: 0 4px 10px rgba(0, 186, 174, 0.08);
        transition: transform .15s ease, box-shadow .15s ease;
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
        z-index: 1;
    }
    .layanan-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 186, 174, 0.18);
    }
    .layanan-kode {
        display: inline-block;
        background: var(--dp-primary);
        color: #fff;
        font-weight: 900;
        font-size: 26px;
        width: 56px; height: 56px; line-height: 56px;
        text-align: center;
        border-radius: 8px;
        flex: 0 0 auto;
    }
    .layanan-nama {
        font-size: 18px;
        font-weight: bold;
        color: var(--dp-dark);
        flex: 1 1 auto;
    }
    .btn-ambil {
        background: var(--dp-accent);
        border: 1px solid var(--dp-accent-dark);
        color: var(--dp-dark);
        font-weight: bold;
        padding: 10px 16px;
        border-radius: 6px;
        flex: 0 0 auto;
        cursor: pointer;
        position: relative;
        z-index: 2;
    }
    .btn-ambil:hover, .btn-ambil:focus {
        background: var(--dp-accent-dark);
        color: #fff;
    }

    /* --- Tiket --- */
    .tiket-box {
        background: #fff;
        border: 4px solid var(--dp-primary);
        border-radius: 12px;
        padding: 28px 20px;
        text-align: center;
        box-shadow: 0 8px 22px rgba(0, 186, 174, 0.2);
    }
    .tiket-label {
        color: var(--dp-primary-dark);
        font-size: 16px;
        letter-spacing: 2px;
        margin-bottom: 6px;
    }
    .tiket-nomor {
        font-size: 110px;
        line-height: 1;
        font-weight: 900;
        color: var(--dp-primary);
        margin: 6px 0 12px;
    }
    .tiket-layanan {
        font-size: 22px;
        color: var(--dp-dark);
        font-weight: bold;
        margin-bottom: 18px;
    }
    .tiket-meta {
        background: #f4f6f0;
        border-radius: 8px;
        padding: 14px;
        margin: 18px 0;
        font-size: 14px;
    }
    .tiket-meta strong { color: var(--dp-primary-dark); }
    .tiket-posisi {
        background: var(--dp-accent);
        color: var(--dp-dark);
        font-size: 18px;
        font-weight: bold;
        padding: 12px;
        border-radius: 6px;
        margin-top: 10px;
    }

    /* --- Info loket --- */
    .loket-list .loket-item {
        background: #fff;
        border-left: 4px solid var(--dp-accent);
        padding: 10px 14px;
        margin-bottom: 8px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,.05);
    }
    .loket-list .loket-item .kode {
        display: inline-block;
        background: var(--dp-primary);
        color: #fff;
        font-weight: bold;
        padding: 2px 8px;
        border-radius: 4px;
        margin-right: 8px;
        font-size: 12px;
    }

    @media print {
        .no-print { display: none !important; }
        body { background: #fff; }
        .tiket-box { box-shadow: none; border-color: #333; }
    }

    @media (max-width: 480px) {
        .tiket-nomor { font-size: 80px; }
        .layanan-card { flex-wrap: wrap; }
        .layanan-kode { width: 48px; height: 48px; line-height: 48px; font-size: 22px; }
        .layanan-nama { font-size: 16px; }
        .btn-ambil { display: block; width: 100%; margin-top: 14px; }
    }

    /* --- Jam analog --- */
    .clock-wrap {
        display: flex;
        justify-content: center;
        margin-top: 18px;
    }
    .clock {
        position: relative;
        width: 220px;
        height: 220px;
        background: #fff;
        border: 8px solid var(--dp-primary);
        border-radius: 50%;
        box-shadow: 0 4px 10px rgba(0, 186, 174, 0.15);
        box-sizing: border-box;
    }
    .clock::before {
        content: '';
        position: absolute;
        top: 50%; left: 50%;
        width: 12px; height: 12px;
        background: var(--dp-primary-dark);
        border: 3px solid #fff;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        z-index: 5;
    }
    .clock .numbers {
        position: absolute;
        inset: 10px;
    }
    .clock .number {
        position: absolute;
        inset: 0;
        display: flex;
        justify-content: center;
        font-size: 1rem;
        font-weight: bold;
        color: var(--dp-dark);
    }
    .clock .number span { position: absolute; top: 4px; }
    .clock .number1  { transform: rotate(30deg); }  .clock .number1  span { transform: rotate(-30deg); }
    .clock .number2  { transform: rotate(60deg); }  .clock .number2  span { transform: rotate(-60deg); }
    .clock .number3  { transform: rotate(90deg); }  .clock .number3  span { transform: rotate(-90deg); }
    .clock .number4  { transform: rotate(120deg); } .clock .number4  span { transform: rotate(-120deg); }
    .clock .number5  { transform: rotate(150deg); } .clock .number5  span { transform: rotate(-150deg); }
    .clock .number6  { transform: rotate(180deg); } .clock .number6  span { transform: rotate(-180deg); }
    .clock .number7  { transform: rotate(210deg); } .clock .number7  span { transform: rotate(-210deg); }
    .clock .number8  { transform: rotate(240deg); } .clock .number8  span { transform: rotate(-240deg); }
    .clock .number9  { transform: rotate(270deg); } .clock .number9  span { transform: rotate(-270deg); }
    .clock .number10 { transform: rotate(300deg); } .clock .number10 span { transform: rotate(-300deg); }
    .clock .number11 { transform: rotate(330deg); } .clock .number11 span { transform: rotate(-330deg); }

    .clock .hour,
    .clock .minute,
    .clock .second {
        position: absolute;
        left: 50%;
        bottom: 50%;
        transform-origin: bottom center;
        border-radius: 4px 4px 0 0;
    }
    .clock .hour   { width: 5px; height: 28%; margin-left: -2.5px; background: var(--dp-dark); }
    .clock .minute { width: 4px; height: 36%; margin-left: -2px;   background: var(--dp-primary-dark); }
    .clock .second { width: 2px; height: 40%; margin-left: -1px;   background: var(--dp-accent-dark); z-index: 4; }

    .clock-digital {
        text-align: center;
        margin-top: 8px;
        font-weight: bold;
        color: var(--dp-primary-dark);
        letter-spacing: 2px;
    }

    @media (max-width: 480px) {
        .clock { width: 180px; height: 180px; }
        .clock .number { font-size: 0.85rem; }
    }
    </style>
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

<script>
(function () {
    var nik       = document.getElementById('nik');
    var form      = document.getElementById('formAmbil');
    var idLayanan = document.getElementById('idLayanan');

    if (nik) {
        nik.addEventListener('input', function () {
            var only = this.value.replace(/\D+/g, '').slice(0, 16);
            if (only !== this.value) this.value = only;
        });

        // Cegah implicit submit via Enter — user wajib klik tombol layanan.
        nik.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
            }
        });
    }

    // Delegasi ke form: klik pada elemen .btn-pilih-layanan mana pun akan
    // mengisi id_layanan dari data-id, lalu submit form secara eksplisit.
    if (form) {
        form.addEventListener('click', function (e) {
            var btn = e.target.closest ? e.target.closest('.btn-pilih-layanan') : null;
            if ( ! btn) return;

            if (nik && nik.value.length !== 16) {
                nik.focus();
                alert('NIK harus 16 digit angka.');
                return;
            }

            if (idLayanan) {
                idLayanan.value = btn.getAttribute('data-id') || '';
            }
            form.submit();
        });
    }

    var hourHand   = document.getElementById('hour');
    var minuteHand = document.getElementById('minute');
    var secondHand = document.getElementById('second');
    var digital    = document.getElementById('clockDigital');

    if (hourHand && minuteHand && secondHand) {
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };

        var setClock = function () {
            var now = new Date();
            var h = now.getHours();
            var m = now.getMinutes();
            var s = now.getSeconds();

            hourHand.style.transform   = 'rotate(' + (30 * (h % 12) + m / 2) + 'deg)';
            minuteHand.style.transform = 'rotate(' + (6 * m + s / 10) + 'deg)';
            secondHand.style.transform = 'rotate(' + (6 * s) + 'deg)';

            if (digital) digital.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
        };

        setClock();
        setInterval(setClock, 1000);
    }
})();
</script>

</body>
</html>
