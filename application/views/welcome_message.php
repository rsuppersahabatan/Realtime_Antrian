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

    /* --- Daftar layanan --- */
    .layanan-card {
        background: #fff;
        border: 2px solid var(--dp-primary);
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 16px;
        box-shadow: 0 4px 10px rgba(0, 186, 174, 0.08);
        transition: transform .15s ease, box-shadow .15s ease;
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
        margin-right: 14px;
        vertical-align: middle;
    }
    .layanan-nama {
        display: inline-block;
        vertical-align: middle;
        font-size: 18px;
        font-weight: bold;
        color: var(--dp-dark);
    }
    .btn-ambil {
        background: var(--dp-accent);
        border: 1px solid var(--dp-accent-dark);
        color: var(--dp-dark);
        font-weight: bold;
        padding: 10px 16px;
        border-radius: 6px;
        float: right;
        margin-top: 8px;
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
        .layanan-kode { width: 48px; height: 48px; line-height: 48px; font-size: 22px; }
        .layanan-nama { font-size: 16px; }
        .btn-ambil { float: none; display: block; width: 100%; margin-top: 14px; }
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
            <h3 class="section-title">PILIH LAYANAN</h3>

            <?php if ( ! empty($layanan)): ?>
                <?php foreach ($layanan as $ly): ?>
                    <div class="layanan-card clearfix">
                        <span class="layanan-kode"><?= htmlspecialchars($ly['kode_huruf'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="layanan-nama"><?= htmlspecialchars($ly['nama_layanan'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?= form_open('welcome/ambil', ['class' => 'form-inline', 'style' => 'display:inline']); ?>
                            <input type="hidden" name="id_layanan" value="<?= (int) $ly['id'] ?>">
                            <button type="submit" class="btn-ambil">AMBIL NOMOR</button>
                        <?= form_close() ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">Belum ada layanan yang tersedia. Silakan hubungi petugas.</div>
            <?php endif; ?>
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
        </div>
    </div>

<?php endif; ?>

</div>

</body>
</html>
