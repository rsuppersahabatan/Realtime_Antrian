<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Display Antrian Online - RS PERSAHABATAN</title>
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap"
    />
    <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }
      body {
        font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
        background: linear-gradient(135deg, #0a1a18 0%, #112520 50%, #0d1f1a 100%);
        color: #ffffff;
        min-height: 100vh;
        padding: 40px 20px;
      }
      .page-header {
        max-width: 1200px;
        margin: 0 auto 32px;
        display: flex;
        align-items: center;
        gap: 16px;
        border-bottom: 3px solid #ccdb2a;
        padding-bottom: 20px;
      }
      .page-header img { width: 56px; height: 56px; object-fit: contain; }
      .page-header h1 {
        font-size: 24px;
        font-weight: 900;
        letter-spacing: 2px;
        line-height: 1.2;
      }
      .page-header small {
        display: block;
        font-size: 12px;
        color: rgba(255,255,255,0.7);
        letter-spacing: 1.5px;
        font-weight: 600;
      }
      .subtitle {
        max-width: 1200px;
        margin: 0 auto 24px;
        color: rgba(255,255,255,0.6);
        font-size: 14px;
      }

      .grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
      }

      .card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(0,186,174,0.35);
        border-radius: 14px;
        padding: 20px;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-height: 180px;
        transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        position: relative;
      }
      .card:hover {
        background: rgba(0,186,174,0.15);
        border-color: #ccdb2a;
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0,0,0,0.35);
      }
      .card.inactive {
        opacity: 0.55;
        filter: grayscale(0.4);
      }
      .card.inactive:hover {
        transform: none;
        box-shadow: none;
      }

      .card-status {
        align-self: flex-start;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 999px;
        letter-spacing: 1.5px;
      }
      .card-status.aktif    { background: #16a34a; color: #fff; }
      .card-status.nonaktif { background: #4b5563; color: #d1d5db; }

      .card-name {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: 1px;
        line-height: 1.2;
      }
      .card-id {
        font-size: 12px;
        color: rgba(255,255,255,0.5);
        letter-spacing: 1px;
        margin-top: 4px;
      }

      .card-lokets {
        margin-top: auto;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
      }
      .badge {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 4px;
        background: rgba(0,186,174,0.25);
        color: #fff;
        font-weight: 600;
        letter-spacing: 0.5px;
      }
      .badge-empty {
        background: rgba(255,255,255,0.08);
        color: rgba(255,255,255,0.5);
        font-style: italic;
      }

      .empty-state {
        max-width: 1200px;
        margin: 60px auto;
        text-align: center;
        color: rgba(255,255,255,0.5);
        padding: 40px 20px;
        border: 1px dashed rgba(255,255,255,0.15);
        border-radius: 14px;
      }

      @media (max-width: 600px) {
        .grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
        .card { min-height: 150px; padding: 16px; }
        .card-name { font-size: 16px; }
      }
    </style>
  </head>
  <body>
    <div class="page-header">
      <img src="https://cdn.jsdelivr.net/gh/rsuppersahabatan/website-assets@0.0.3/img/logokemkes512.png" alt="Logo Kemkes" />
      <div>
        <h1>PILIH DISPLAY ANTRIAN</h1>
        <small>ANTRIAN ONLINE &mdash; RS PERSAHABATAN</small>
      </div>
    </div>

    <div class="subtitle">
      Klik salah satu kotak di bawah untuk membuka tampilan antrian.
      Setiap display hanya menampilkan.
    </div>

    <?php if (empty($clients)): ?>
      <div class="empty-state">
        Belum ada client/display yang dibuat. Silakan tambahkan dari halaman admin.
      </div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($clients as $c): ?>
          <?php
            $aktif = (isset($c['is_active']) && $c['is_active'] === 'ya');
            $jml   = isset($c['lokets']) ? count($c['lokets']) : 0;
            $href  = site_url('client/'.$c['id']);
          ?>
          <a href="<?= $href ?>" class="card<?= $aktif ? '' : ' inactive' ?>" title="Buka display untuk <?= htmlspecialchars($c['nama_client'], ENT_QUOTES, 'UTF-8') ?>">
            <span class="card-status <?= $aktif ? 'aktif' : 'nonaktif' ?>">
              <?= $aktif ? 'AKTIF' : 'NONAKTIF' ?>
            </span>
            <div>
              <div class="card-name"><?= htmlspecialchars($c['nama_client'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="card-id">ID #<?= (int) $c['id'] ?> &middot; <?= $jml ?> loket</div>
            </div>
            <div class="card-lokets">
              <?php if ($jml === 0): ?>
                <span class="badge badge-empty">belum ada loket</span>
              <?php else: ?>
                <?php foreach ($c['lokets'] as $l): ?>
                  <span class="badge"><?= htmlspecialchars($l['nama_loket'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </body>
</html>
