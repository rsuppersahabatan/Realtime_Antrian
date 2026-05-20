<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <script defer src="https://umami.persahabatan.co.id/script.js" data-website-id="084ea29a-39c4-44d7-ba5a-534fb2daacb6"></script>

    <?php
      $client_name = !empty($client['nama_client']) ? $client['nama_client'] : 'Display Antrian';
      $accent      = !empty($display['color_scheme']) ? $display['color_scheme'] : '#00baae';
      $font_family = !empty($display['font_family']) ? $display['font_family'] : 'Inter';
      $font_size   = !empty($display['font_size']) ? (int) $display['font_size'] : 100;
      // Hanya font yang benar-benar tersedia di Google Fonts yang boleh diload dari CDN.
      // Font sistem (Arial, Tahoma, dll.) tidak ada di Google Fonts -> jangan di-request
      // karena Google akan mengembalikan halaman HTML error (MIME mismatch).
      $google_fonts   = array('Poppins', 'Inter', 'Roboto', 'Open Sans');
      $is_google_font = in_array($font_family, $google_fonts, true);
      $video_src   = !empty($display['video_source']) ? $display['video_source'] : 'youtube';
      $video_link  = isset($display['video_link']) ? trim($display['video_link']) : '';
      $footer_text = isset($display['footer_text']) ? $display['footer_text'] : '';
      $footer_mode = !empty($display['footer_mode']) ? $display['footer_mode'] : 'running';

      // Ekstrak YouTube ID dari URL apa pun (youtu.be / watch?v= / embed/)
      $youtube_id = '';
      if ($video_src === 'youtube' && $video_link !== '') {
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|v/))([A-Za-z0-9_-]{6,})~', $video_link, $m)) {
          $youtube_id = $m[1];
        } else {
          $youtube_id = preg_replace('/[^A-Za-z0-9_-]/', '', $video_link);
        }
      }
    ?>

    <title>Display Antrian - <?= htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') ?></title>
    <!-- jQuery harus diload sebelum script lokal jika ingin menggunakan global $ -->
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/script/soundmanager2-nodebug-jsmin.js') ?>"></script>
    <script src="<?= base_url('assets/script/terbilang.js') ?>"></script>
    <link
      rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
    />
    <?php if ($is_google_font && $font_family !== 'Inter'): ?>
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=<?= rawurlencode($font_family) ?>:wght@400;600;700;900&family=Inter:wght@400;600;700;900&display=swap"
    />
    <?php else: ?>
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap"
    />
    <?php endif; ?>

    <?= isset($minified_css) ? $minified_css : '<link rel="stylesheet" href="' . base_url('assets/frameworks/domprojects/css/client.css') . '" />' ?>

    <!-- Override dinamis HARUS setelah client.css agar selector dengan specificity sama menang. -->
    <style>
      :root {
        /* color_scheme HANYA mengganti --dp-primary (warna brand utama).
           --dp-accent sengaja TIDAK dioverride supaya button "MENUNGGU PANGGILAN"
           dan border header tetap chartreuse default — kontras & readable. */
        --dp-primary: <?= htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') ?>;
        --dp-primary-dark: <?= htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') ?>;
        --dp-font-scale: <?= number_format($font_size / 100, 4, '.', '') ?>;
      }
      body { font-family: '<?= htmlspecialchars($font_family, ENT_QUOTES, 'UTF-8') ?>', 'Inter', 'Helvetica Neue', Arial, sans-serif; }

      /* Skala ukuran nomor antrian mengikuti font_size (100 = default = sama dengan client.css).
         Floor 96px supaya tetap dominan walau scale kecil; ceiling 220px sesuai default. */
      .main-number {
        font-size: clamp(96px, calc(18vw * var(--dp-font-scale)), calc(220px * var(--dp-font-scale)));
        line-height: 1;
        margin: 0;
      }

      /* Beri ruang internal panel: padding generous supaya badge & button tidak mepet ke border. */
      .display-body { padding: 20px 24px; gap: 24px; }
      .main-panel {
        padding: 48px 32px;
        gap: 14px;
        justify-content: center;          /* tetap center, bukan space-between, agar simetris */
        overflow: hidden;
      }
      .main-panel > * { flex-shrink: 0; }
      .main-loket-label { line-height: 1.15; margin-bottom: 4px; }
      .main-direction   { margin-top: 8px; line-height: 1.2; }
      /* Kecilkan tinggi visual dashes placeholder & angka pendek agar tidak "ngambang" */
      .main-number { letter-spacing: 2px; }

      /* Label pendukung — skala lebih halus, tidak ikut menyusut ekstrim saat font_size kecil. */
      .main-loket-label { font-size: clamp(20px, calc(1.4vw * var(--dp-font-scale) + 16px), 30px); }
      .main-queue-label { font-size: clamp(15px, calc(1.0vw * var(--dp-font-scale) + 13px), 22px); }
      .main-direction   { font-size: clamp(16px, calc(1.0vw * var(--dp-font-scale) + 14px), 24px); }
    </style>

    <script type="text/javascript">
      var socketUrl = <?php $__s = $this->config->item('socket_url'); echo $__s ? json_encode($__s) : "window.location.protocol + '//' + window.location.host"; ?>;

      // Override default ttsApiBase di tts.js. HARUS diset sebelum tts.js / client.min.js
      // di-load (di akhir <body>) agar `var ttsApiBase = window.ttsApiBase || '<default>'`
      // membaca nilai ini. Panggilan TTS: POST `${ttsApiBase}/tts` dengan kalimat berisi
      // nomor antrian + nama (keterangan) + loket — lihat buatTeksPanggilan() di tts.js.
      window.ttsApiBase = <?= json_encode(isset($tts_api_base) ? $tts_api_base : 'http://localhost:8085') ?>;

      var audioBase = "<?= base_url('assets/audio/') ?>";
      var soundManagerReady = false;

      // Map channel_id (loketNN) -> nama_loket ("Dokter 1", "Aftap 2", dll)
      var loketLabels = <?php
        $labels = [];
        if (!empty($loket)) {
          foreach ($loket as $lk) {
            $cid = 'loket' . str_pad((int) $lk['id'], 2, '0', STR_PAD_LEFT);
            $labels[$cid] = !empty($lk['nama_loket']) ? $lk['nama_loket'] : ('LOKET ' . $lk['id']);
          }
        }
        echo json_encode($labels);
      ?>;

      soundManager.url = "<?= base_url('assets/swf/') ?>";
      soundManager.preferFlash = false;
      soundManager.useHTML5Audio = true;
      soundManager.onready(function () {
        soundManagerReady = true;
      });

      var script = document.createElement("script");
      script.src = socketUrl + "/socket.io/socket.io.js";
      script.onload = function () {
        var socket = io.connect(socketUrl);
        socket.on("connect", function (data) {
          console.log("Socket connected");
        });
        socket.on("message", function (data) {
          console.log("received a message: ", data);
          addMessage(data);
        });
      };

      script.onerror = function () {
        console.error(
          "Gagal memuat socket.io.js. Pastikan reverse proxy '/socket.io/' terhubung ke service Node.js.",
        );
      };
      document.head.appendChild(script);
    </script>
  </head>
  <body>
    <div class="display-wrapper">

      <!-- ====== HEADER ====== -->
      <div class="display-header">
        <div class="header-brand">
          <div class="header-logo" style="background:transparent;border:none;overflow:visible;padding:0;flex:0 0 52px;">
            <img src="https://cdn.jsdelivr.net/gh/rsuppersahabatan/website-assets@0.0.3/img/logokemkes512.png" alt="Logo Kemkes" style="width:52px;height:52px;object-fit:contain;display:block;" />
          </div>
          <div class="header-title">
            <h1>ANTRIAN ONLINE</h1>
            <small><?= htmlspecialchars(strtoupper($client_name), ENT_QUOTES, 'UTF-8') ?></small>
          </div>
        </div>
        <div class="header-clock">
          <div class="clock-time" id="clockTime">--:--:--</div>
          <div class="clock-date" id="clockDate">--</div>
        </div>
      </div>

      <!-- ====== BODY ====== -->
      <div class="display-body">

        <!-- Panel Utama — nomor antrian besar -->
        <div class="main-panel">
          <div class="main-loket-label" id="loket_display">MENUNGGU</div>
          <div class="main-queue-label">NOMOR ANTRIAN</div>
          <div class="main-number" id="online">---</div>
          <div class="main-keterangan" id="keterangan_text"></div>
          <div class="main-direction" id="direction_text">MENUNGGU PANGGILAN</div>
        </div>

        <!-- Panel Samping — Video (YouTube / Local) -->
        <div class="side-panel" style="padding:0;overflow:hidden;">
          <?php if ($video_src === 'youtube' && $youtube_id !== ''): ?>
            <iframe
              width="100%"
              height="100%"
              src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($youtube_id, ENT_QUOTES, 'UTF-8') ?>?autoplay=1&mute=1&loop=1&playlist=<?= htmlspecialchars($youtube_id, ENT_QUOTES, 'UTF-8') ?>&controls=0&showinfo=0&rel=0"
              frameborder="0"
              allow="autoplay; encrypted-media"
              allowfullscreen
              style="display:block;border:none;border-radius:18px;"
            ></iframe>
          <?php elseif ($video_src === 'local' && $video_link !== ''): ?>
            <video
              width="100%"
              height="100%"
              autoplay
              muted
              loop
              playsinline
              style="display:block;border:none;border-radius:18px;object-fit:cover;background:#000;"
              src="<?= htmlspecialchars($video_link, ENT_QUOTES, 'UTF-8') ?>"
            ></video>
          <?php else: ?>
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.4);font-weight:600;">
              VIDEO BELUM DIATUR
            </div>
          <?php endif; ?>
        </div>

              <!-- <div class="side-panel">
          <div class="side-panel-title">DAFTAR LOKET</div>
          <?php if (!empty($loket)): ?>
            <?php foreach ($loket as $lk): ?>
              <?php
                $nama_loket = !empty($lk['nama_loket']) ? $lk['nama_loket'] : ('LOKET ' . $lk['id']);
                $channel_id = 'loket' . str_pad((int) $lk['id'], 2, '0', STR_PAD_LEFT);
                $nomor_terakhir = !empty($lk['nomor_terakhir']) ? $lk['nomor_terakhir'] : '-';
              ?>
              <div class="side-card" id="sidecard-<?= $channel_id ?>">
                <div class="side-card-label"><?= strtoupper($nama_loket) ?></div>
                <div class="side-card-number" id="side-<?= $channel_id ?>">
                  <?= htmlspecialchars($nomor_terakhir, ENT_QUOTES, 'UTF-8') ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="side-card">
              <div class="side-card-label" style="width:100%;text-align:center;color:rgba(255,255,255,0.5);">
                BELUM ADA LOKET DIBUKA
              </div>
            </div>
          <?php endif; ?>
        </div> -->

      </div>

      <!-- ====== FOOTER LOKET BAR ====== -->
      <div class="display-footer-loket">
        <?php if (!empty($loket)): ?>
          <?php foreach ($loket as $lk): ?>
            <?php
              $nama_loket = !empty($lk['nama_loket']) ? $lk['nama_loket'] : ('LOKET ' . $lk['id']);
              $channel_id = 'loket' . str_pad((int) $lk['id'], 2, '0', STR_PAD_LEFT);
              $nomor_terakhir = !empty($lk['nomor_terakhir']) ? $lk['nomor_terakhir'] : '---';
              $ket_terakhir = !empty($lk['keterangan_terakhir']) ? $lk['keterangan_terakhir'] : '';
              $ket_class = $ket_terakhir === '' ? 'footer-loket-keterangan is-empty' : 'footer-loket-keterangan';
            ?>
            <div class="footer-loket-item" id="footer-item-<?= $channel_id ?>">
              <div class="footer-loket-name"><?= strtoupper($nama_loket) ?></div>
              <div class="footer-loket-number" id="footer-<?= $channel_id ?>">
                <?= htmlspecialchars($nomor_terakhir, ENT_QUOTES, 'UTF-8') ?>
              </div>
              <div class="<?= $ket_class ?>" id="footer-ket-<?= $channel_id ?>">
                <?= htmlspecialchars($ket_terakhir, ENT_QUOTES, 'UTF-8') ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="footer-loket-item" style="flex:1;justify-content:center;color:rgba(255,255,255,0.5);font-weight:600;">
            BELUM ADA LOKET YANG DI-ASSIGN UNTUK CLIENT INI
          </div>
        <?php endif; ?>
      </div>

      <!-- ====== FOOTER TEXT (statis / running) ====== -->
      <?php if (trim((string) $footer_text) !== ''): ?>
        <div class="footer-news">
          <?php if ($footer_mode === 'running'): ?>
            <div class="running-text">
              <strong>PENGUMUMAN:</strong>
              <?= htmlspecialchars($footer_text, ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php else: ?>
            <div class="static-text" style="text-align:center;padding:8px 16px;font-weight:600;">
              <?= htmlspecialchars($footer_text, ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>

    <!-- ====== POPUP AUDIO + FULLSCREEN ====== -->
    <div class="audio-popup-backdrop" id="audioPopup">
      <div class="audio-popup" role="dialog" aria-modal="true" aria-labelledby="audioPopupText">
        <button type="button" class="audio-popup-close-x" id="audioPopupCloseX" aria-label="Tutup">&times;</button>
        <div class="audio-popup-body">
          <div class="audio-popup-icon">&#9834;</div>
          <div class="audio-popup-text" id="audioPopupText">
            Klik tombol <strong>Aktifkan Audio + Fullscreen</strong> untuk
            mengaktifkan audio di halaman ini dan membuat halaman menjadi
            fullscreen. Klik tombol <strong>Close</strong> untuk mengaktifkan
            audio saja.
          </div>
        </div>
        <div class="audio-popup-footer">
          <button type="button" class="audio-popup-btn audio-popup-btn-close" id="audioPopupCloseBtn">
            <span aria-hidden="true">&times;</span> Close
          </button>
          <button type="button" class="audio-popup-btn audio-popup-btn-primary" id="audioPopupEnableBtn">
            <span aria-hidden="true">&#10003;</span> Aktifkan Audio + Fullscreen
          </button>
        </div>
      </div>
    </div>

    <?= isset($minified_js) ? $minified_js : '<script src="' . base_url('assets/frameworks/domprojects/js/tts.js') . '"></script>' ?>

    <!-- Fallback jQuery if not loaded from relative path, keep Bootstrap js functioning -->
    <script>
      window.jQuery ||
        document.write(
          '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"><\/script>',
        );
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  </body>

</html>
