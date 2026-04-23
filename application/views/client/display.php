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

    <style>
      :root {
        --dp-primary: #00baae;
        --dp-primary-dark: #009186;
        --dp-accent: #ccdb2a;
        --dp-accent-dark: #a7b322;
        --dp-dark: #1f2d00;
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
        background: linear-gradient(135deg, #0a1a18 0%, #112520 50%, #0d1f1a 100%);
        color: #ffffff;
        overflow: hidden;
        height: 100vh;
        width: 100vw;
      }

      /* =========================================
         LAYOUT UTAMA - Full viewport grid
         ========================================= */
      .display-wrapper {
        display: flex;
        flex-direction: column;
        height: 100vh;
        width: 100vw;
      }

      /* =========================================
         HEADER BAR
         ========================================= */
      .display-header {
        background: linear-gradient(90deg, var(--dp-primary) 0%, var(--dp-primary-dark) 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 30px;
        border-bottom: 4px solid var(--dp-accent);
        flex-shrink: 0;
        position: relative;
        z-index: 10;
      }

      .header-brand {
        display: flex;
        align-items: center;
        gap: 16px;
      }

      .header-logo {
        width: 52px;
        height: 52px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 900;
        color: #fff;
        border: 2px solid rgba(255,255,255,0.4);
      }

      .header-title {
        display: flex;
        flex-direction: column;
      }

      .header-title h1 {
        font-size: 22px;
        font-weight: 900;
        letter-spacing: 1.5px;
        margin: 0;
        line-height: 1.2;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
      }

      .header-title small {
        font-size: 12px;
        letter-spacing: 3px;
        opacity: 0.85;
        font-weight: 600;
      }

      .header-clock {
        text-align: right;
      }

      .clock-time {
        font-size: 36px;
        font-weight: 900;
        letter-spacing: 2px;
        text-shadow: 0 2px 8px rgba(0,0,0,0.2);
      }

      .clock-date {
        font-size: 13px;
        opacity: 0.85;
        letter-spacing: 1px;
        font-weight: 600;
      }

      /* =========================================
         AREA KONTEN UTAMA
         ========================================= */
      .display-body {
        flex: 1;
        display: flex;
        padding: 24px 30px;
        gap: 24px;
        min-height: 0;
      }

      /* --- Panel Antrian Utama (kiri besar) --- */
      .main-panel {
        flex: 2;
        background: linear-gradient(145deg, #f8fdf7 0%, #eef5ec 100%);
        border-radius: 16px;
        border: 3px solid var(--dp-primary);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        box-shadow:
          0 0 40px rgba(0, 186, 174, 0.15),
          inset 0 0 60px rgba(0, 186, 174, 0.03);
      }

      .main-panel::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle at center, rgba(0,186,174,0.04) 0%, transparent 70%);
        pointer-events: none;
      }

      .main-loket-label {
        background: linear-gradient(90deg, var(--dp-primary) 0%, var(--dp-primary-dark) 100%);
        color: #fff;
        padding: 10px 50px;
        border-radius: 50px;
        font-size: 28px;
        font-weight: 900;
        letter-spacing: 3px;
        text-transform: uppercase;
        box-shadow: 0 4px 16px rgba(0,186,174,0.25);
        z-index: 2;
        margin-bottom: 10px;
      }

      .main-queue-label {
        font-size: 22px;
        font-weight: 700;
        color: var(--dp-primary-dark);
        letter-spacing: 5px;
        text-transform: uppercase;
        z-index: 2;
        margin-bottom: 0;
      }

      .main-number {
        font-size: clamp(100px, 18vw, 220px);
        font-weight: 900;
        color: var(--dp-primary);
        line-height: 1;
        z-index: 2;
        text-shadow: 0 4px 20px rgba(0, 186, 174, 0.2);
        animation: numberPulse 3s ease-in-out infinite;
        letter-spacing: 4px;
      }

      @keyframes numberPulse {
        0%, 100% { text-shadow: 0 4px 20px rgba(0,186,174,0.2); }
        50% { text-shadow: 0 4px 30px rgba(0,186,174,0.35); }
      }

      .main-direction {
        background: var(--dp-accent);
        color: var(--dp-dark);
        padding: 10px 40px;
        border-radius: 8px;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: 2px;
        z-index: 2;
        margin-top: 10px;
        box-shadow: 0 4px 12px rgba(204, 219, 42, 0.25);
        text-align: center;
      }

      /* --- Panel Samping (kanan) --- */
      .side-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 0;
        overflow-y: auto;
      }

      .side-panel-title {
        font-size: 16px;
        font-weight: 800;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--dp-accent);
        text-align: center;
        padding-bottom: 8px;
        border-bottom: 2px solid rgba(204, 219, 42, 0.3);
        flex-shrink: 0;
      }

      .side-card {
        background: linear-gradient(145deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.03) 100%);
        border: 2px solid rgba(0, 186, 174, 0.3);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s ease;
        flex-shrink: 0;
        backdrop-filter: blur(10px);
      }

      .side-card:hover,
      .side-card.active {
        border-color: var(--dp-primary);
        background: linear-gradient(145deg, rgba(0,186,174,0.15) 0%, rgba(0,186,174,0.05) 100%);
        box-shadow: 0 0 20px rgba(0, 186, 174, 0.1);
      }

      .side-card-label {
        font-size: 14px;
        font-weight: 700;
        color: rgba(255,255,255,0.8);
        letter-spacing: 1px;
      }

      .side-card-number {
        font-size: 32px;
        font-weight: 900;
        color: var(--dp-accent);
        letter-spacing: 2px;
      }

      /* =========================================
         FOOTER BAR - SEMUA LOKET
         ========================================= */
      .display-footer-loket {
        background: linear-gradient(90deg, rgba(0,186,174,0.12) 0%, rgba(0,145,134,0.12) 100%);
        border-top: 3px solid var(--dp-primary);
        display: flex;
        align-items: stretch;
        flex-shrink: 0;
        overflow-x: auto;
      }

      .footer-loket-item {
        flex: 1;
        text-align: center;
        padding: 12px 10px;
        border-right: 1px solid rgba(0, 186, 174, 0.2);
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 140px;
        transition: background 0.3s ease;
      }

      .footer-loket-item:last-child {
        border-right: none;
      }

      .footer-loket-item.highlight {
        background: rgba(0, 186, 174, 0.2);
      }

      .footer-loket-name {
        font-size: 12px;
        font-weight: 800;
        color: var(--dp-primary);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 2px;
      }

      .footer-loket-number {
        font-size: 26px;
        font-weight: 900;
        color: #fff;
        letter-spacing: 2px;
      }

      /* =========================================
         RUNNING TEXT
         ========================================= */
      .footer-news {
        background: var(--dp-dark);
        color: var(--dp-accent);
        padding: 8px 0;
        font-size: 16px;
        font-weight: 600;
        border-top: 2px solid var(--dp-accent);
        flex-shrink: 0;
        overflow: hidden;
        position: relative;
      }

      .running-text {
        display: inline-block;
        white-space: nowrap;
        animation: scrollText 30s linear infinite;
        padding-left: 100%;
      }

      @keyframes scrollText {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-100%); }
      }

      .running-text strong {
        color: #fff;
        margin-right: 8px;
      }

      /* =========================================
         POPUP AUDIO + FULLSCREEN
         ========================================= */
      .audio-popup-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(8px);
      }
      .audio-popup-backdrop.hidden {
        display: none;
      }
      .audio-popup {
        background: #fff;
        border-radius: 16px;
        width: 90%;
        max-width: 520px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        font-family: 'Inter', Arial, sans-serif;
        position: relative;
      }
      .audio-popup-close-x {
        position: absolute;
        top: 12px;
        right: 16px;
        background: none;
        border: none;
        font-size: 24px;
        color: #888;
        cursor: pointer;
        line-height: 1;
      }
      .audio-popup-close-x:hover {
        color: #333;
      }
      .audio-popup-body {
        display: flex;
        align-items: flex-start;
        padding: 28px 30px 24px 30px;
        gap: 18px;
      }
      .audio-popup-icon {
        flex: 0 0 auto;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--dp-primary) 0%, var(--dp-primary-dark) 100%);
        color: #fff;
        font-weight: bold;
        font-size: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .audio-popup-text {
        font-size: 15px;
        color: #333;
        line-height: 1.6;
        padding-top: 4px;
      }
      .audio-popup-footer {
        border-top: 1px solid #e5e5e5;
        padding: 14px 20px;
        text-align: right;
        background: #f8f9fa;
      }
      .audio-popup-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 22px;
        font-size: 14px;
        font-weight: 700;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        margin-left: 8px;
        transition: all 0.2s ease;
      }
      .audio-popup-btn-close {
        background: #2c2f33;
        color: #fff;
      }
      .audio-popup-btn-close:hover {
        background: #1f2226;
        transform: translateY(-1px);
      }
      .audio-popup-btn-primary {
        background: linear-gradient(135deg, var(--dp-primary) 0%, var(--dp-primary-dark) 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(0, 186, 174, 0.3);
      }
      .audio-popup-btn-primary:hover {
        box-shadow: 0 6px 20px rgba(0, 186, 174, 0.4);
        transform: translateY(-1px);
      }

      /* =========================================
         ANIMASI PANGGILAN BARU
         ========================================= */
      @keyframes callFlash {
        0%   { transform: scale(1); }
        15%  { transform: scale(1.08); }
        30%  { transform: scale(1); }
        45%  { transform: scale(1.05); }
        60%  { transform: scale(1); }
        100% { transform: scale(1); }
      }

      .call-flash {
        animation: callFlash 0.8s ease;
      }

      @keyframes borderFlash {
        0%, 100% { border-color: var(--dp-primary); }
        25%      { border-color: var(--dp-accent); }
        50%      { border-color: var(--dp-primary); }
        75%      { border-color: var(--dp-accent); }
      }

      .border-flash {
        animation: borderFlash 1.2s ease;
      }

      /* =========================================
         RESPONSIVE - layar kecil
         ========================================= */
      @media (max-width: 768px) {
        .display-body {
          flex-direction: column;
          padding: 12px;
          gap: 12px;
        }
        .side-panel {
          flex-direction: row;
          flex-wrap: wrap;
          overflow-y: visible;
          gap: 8px;
        }
        .side-card {
          flex: 1 1 45%;
        }
        .main-loket-label { font-size: 20px; padding: 8px 30px; }
        .main-number { font-size: 80px !important; }
        .main-direction { font-size: 16px; padding: 8px 20px; }
        .clock-time { font-size: 24px; }
        .footer-loket-item { min-width: 100px; padding: 8px 6px; }
        .footer-loket-number { font-size: 20px; }
      }
    </style>

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

      /**
       * Bangun daftar nama file audio dari nomor antrian.
       * Mendukung alfanumerik, misal "A206" -> ["huruf/a","dua","ratus","enam"].
       * Angka murni, misal "206" -> ["dua","ratus","enam"].
       */
      function buatDaftarSuara(nomorAntrian) {
        var daftar = [];
        var matchAlfa = nomorAntrian.match(/^([a-zA-Z]+)(\d+)$/);
        if (matchAlfa) {
          var prefixHuruf = matchAlfa[1].toUpperCase();
          var angka = matchAlfa[2];
          for (var h = 0; h < prefixHuruf.length; h++) {
            daftar.push("huruf/" + prefixHuruf[h].toLowerCase());
          }
          if (parseInt(angka, 10) > 0) {
            var s = terbilang(angka).trim().replace(/\s+/g, "-");
            daftar = daftar.concat(s.split("-"));
          }
        } else if (/^\d+$/.test(nomorAntrian) && parseInt(nomorAntrian, 10) > 0) {
          var s2 = terbilang(nomorAntrian).trim().replace(/\s+/g, "-");
          daftar = s2.split("-");
        }
        return daftar.filter(function (x) { return x && x.length > 0; });
      }

      // Sequence counter supaya setiap panggilan punya ID sound unik — kalau
      // message datang beruntun, sound baru tidak bentrok dengan ID lama yang
      // belum sempat destruct.
      var soundSeq = 0;

      function putarSuara(daftarSuara) {
        if (!soundManagerReady || !daftarSuara || daftarSuara.length === 0) return;
        var seqId = ++soundSeq;
        var sounds = [];
        for (var i = 0; i < daftarSuara.length; i++) {
          (function (idx) {
            var isLast = idx === daftarSuara.length - 1;
            sounds[idx] = soundManager.createSound({
              id: "disp-" + seqId + "-" + idx,
              volume: 100,
              url: audioBase + daftarSuara[idx] + ".wav",
              onfinish: function () {
                if (!isLast && sounds[idx + 1]) {
                  sounds[idx + 1].play();
                }
                this.destruct();
              },
            });
          })(i);
        }
        sounds[0].play();
      }

      function addMessage(data) {
        if (!data) return;
        var vdt = data.toString().split("-");

        if (vdt.length > 1) {
          var loket_raw = vdt[0].toLowerCase();
          var nomor_antrian = vdt[1];

          // Update nomor antrian utama
          var mainNum = document.getElementById("online");
          mainNum.textContent = nomor_antrian;
          mainNum.classList.remove("call-flash");
          void mainNum.offsetWidth; // reflow untuk trigger ulang animasi
          mainNum.classList.add("call-flash");

          // Flash border pada main panel
          var mainPanel = document.querySelector(".main-panel");
          if (mainPanel) {
            mainPanel.classList.remove("border-flash");
            void mainPanel.offsetWidth;
            mainPanel.classList.add("border-flash");
          }

          // Update panel samping
          var $side = $("#side-" + loket_raw);
          if ($side.length) {
            $side.html(nomor_antrian);
          }

          // Update footer loket
          var footerNum = document.getElementById("footer-" + loket_raw);
          if (footerNum) {
            footerNum.textContent = nomor_antrian;
          }

          // Highlight footer item yang aktif
          var footerItems = document.querySelectorAll(".footer-loket-item");
          footerItems.forEach(function(item) { item.classList.remove("highlight"); });
          var activeFooter = document.getElementById("footer-item-" + loket_raw);
          if (activeFooter) activeFooter.classList.add("highlight");

          // Highlight side card yang aktif
          var sideCards = document.querySelectorAll(".side-card");
          sideCards.forEach(function(card) { card.classList.remove("active"); });
          var activeSide = document.getElementById("sidecard-" + loket_raw);
          if (activeSide) activeSide.classList.add("active");

          // Utamakan nama_loket dari DB (mis. "Dokter 1", "Aftap 2").
          // Jika tidak ketemu (loket belum di-list / ditambah setelah halaman load),
          // fallback ke parsing prefix loket/kasir agar tetap tampil ramah.
          var loket_name;
          if (loketLabels && loketLabels[loket_raw]) {
            loket_name = loketLabels[loket_raw];
          } else if (loket_raw.indexOf("loket") === 0) {
            loket_name = "LOKET " + loket_raw.replace("loket", "").replace(/^0+/, "");
          } else if (loket_raw.indexOf("kasir") === 0) {
            loket_name = "KASIR " + loket_raw.replace("kasir", "").replace(/^0+/, "");
          } else {
            loket_name = loket_raw.toUpperCase();
          }

          document.getElementById("loket_display").textContent = loket_name.toUpperCase();
          document.getElementById("direction_text").textContent = "SILAHKAN KE " + loket_name.toUpperCase();

          putarSuara(buatDaftarSuara(nomor_antrian));
        } else {
          document.getElementById("online").textContent = data;
          putarSuara(buatDaftarSuara(String(data)));
        }
      }
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

        <!-- Panel Samping — daftar semua loket -->
        <div class="side-panel">
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
        </div>

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

    <script>
      // ====== JAM DIGITAL ======
      (function () {
        var hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        var bulan = ['Januari','Februari','Maret','April','Mei','Juni',
                     'Juli','Agustus','September','Oktober','November','Desember'];

        function pad(n) { return n < 10 ? '0' + n : '' + n; }

        function updateClock() {
          var now = new Date();
          var h = now.getHours();
          var m = now.getMinutes();
          var s = now.getSeconds();

          document.getElementById('clockTime').textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
          document.getElementById('clockDate').textContent =
            hari[now.getDay()] + ', ' + now.getDate() + ' ' + bulan[now.getMonth()] + ' ' + now.getFullYear();
        }

        updateClock();
        setInterval(updateClock, 1000);
      })();

      // ====== POPUP AUDIO + FULLSCREEN ======
      (function () {
        var popup = document.getElementById("audioPopup");
        var btnClose = document.getElementById("audioPopupCloseBtn");
        var btnCloseX = document.getElementById("audioPopupCloseX");
        var btnEnable = document.getElementById("audioPopupEnableBtn");

        function unlockAudio() {
          try {
            if (window.soundManager && soundManager.setup) {
              soundManager.setup({ useHTML5Audio: true, preferFlash: false });
            }
            // Putar audio diam singkat untuk membuka autoplay policy browser.
            var unlock = new Audio();
            unlock.src =
              "data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQAAAAA=";
            unlock.volume = 0;
            var p = unlock.play();
            if (p && typeof p.then === "function") {
              p.catch(function () {});
            }
          } catch (e) {}
        }

        function requestFullscreen() {
          var el = document.documentElement;
          var fn =
            el.requestFullscreen ||
            el.webkitRequestFullscreen ||
            el.msRequestFullscreen ||
            el.mozRequestFullScreen;
          if (fn) {
            try {
              fn.call(el);
            } catch (e) {}
          }
        }

        function hidePopup() {
          popup.classList.add("hidden");
        }

        btnClose.addEventListener("click", function () {
          unlockAudio();
          hidePopup();
        });

        btnCloseX.addEventListener("click", function () {
          unlockAudio();
          hidePopup();
        });

        btnEnable.addEventListener("click", function () {
          unlockAudio();
          requestFullscreen();
          hidePopup();
        });
      })();
    </script>

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
