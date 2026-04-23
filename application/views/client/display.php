<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Display Antrian - UPDRS RS PERSAHABATAN</title>
    <!-- jQuery harus diload sebelum script lokal jika ingin menggunakan global $ -->
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/script/soundmanager2-nodebug-jsmin.js') ?>"></script>
    <script src="<?= base_url('assets/script/terbilang.js') ?>"></script>
    <link
      rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap"
    />

    <link rel="stylesheet" href="<?= base_url('assets/frameworks/domprojects/css/client.css') ?>" />

    <script type="text/javascript">
      var socketUrl = <?php $__s = $this->config->item('socket_url'); echo $__s ? json_encode($__s) : "window.location.protocol + '//' + window.location.host"; ?>;

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
          <div class="header-logo">RS</div>
          <div class="header-title">
            <h1>ANTRIAN UPDRS</h1>
            <small>RS PERSAHABATAN</small>
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
          <div class="main-direction" id="direction_text">MENUNGGU PANGGILAN</div>
        </div>

        <!-- Panel Samping — Video YouTube -->
        <div class="side-panel" style="padding:0;overflow:hidden;">
          <iframe
            width="100%"
            height="100%"
            src="https://www.youtube.com/embed/ebZwRzwEpT8?autoplay=1&mute=1&loop=1&playlist=ebZwRzwEpT8&controls=0&showinfo=0&rel=0"
            frameborder="0"
            allow="autoplay; encrypted-media"
            allowfullscreen
            style="display:block;border:none;border-radius:18px;"
          ></iframe>
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
            ?>
            <div class="footer-loket-item" id="footer-item-<?= $channel_id ?>">
              <div class="footer-loket-name"><?= strtoupper($nama_loket) ?></div>
              <div class="footer-loket-number" id="footer-<?= $channel_id ?>">
                <?= htmlspecialchars($nomor_terakhir, ENT_QUOTES, 'UTF-8') ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- ====== RUNNING TEXT ====== -->
      <div class="footer-news">
        <div class="running-text">
          <strong>PENGUMUMAN:</strong>
          Jam operasional : 12:00 - 19:00 WIB. &nbsp;|&nbsp;
          Gunakan selalu masker di lingkungan rumah sakit. &nbsp;|&nbsp;
          Selamat datang di UPDRS RS Persahabatan. &nbsp;|&nbsp;
          Mohon menunggu nomor antrian Anda dipanggil.
        </div>
      </div>

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

    <script src="<?= base_url('assets/frameworks/domprojects/js/client.js') ?>"></script>
    
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
