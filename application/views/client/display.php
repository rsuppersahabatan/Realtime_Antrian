<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <title>Display Antrian - UPDRS RS PERSAHABATAN</title>
    <!-- jQuery harus diload sebelum script lokal jika ingin menggunakan global $ -->
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/script/soundmanager2-nodebug-jsmin.js') ?>"></script>
    <script src="<?= base_url('assets/script/terbilang.js') ?>"></script>
    <link
      rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
    />

    <style>
      :root {
        --dp-primary: #00baae;
        --dp-primary-dark: #009186;
        --dp-accent: #ccdb2a;
        --dp-accent-dark: #a7b322;
        --dp-dark: #1f2d00;
      }

      body {
        background-color: #f4f6f0;
        overflow: hidden; /* Menghilangkan scrollbar pada display TV */
      }

      .header-blue {
        background: linear-gradient(90deg, var(--dp-primary) 0%, var(--dp-primary-dark) 100%);
        color: white;
        padding: 15px 0;
        margin-bottom: 20px;
        border-bottom: 5px solid var(--dp-accent);
      }

      /* Styling Nomor Utama */
      .main-queue {
        background: #fff;
        border: 4px solid var(--dp-primary);
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 6px 18px rgba(0, 186, 174, 0.15);
      }

      .main-queue h2 {
        font-size: 30px;
        color: var(--dp-primary-dark);
        margin-top: 10px;
        font-weight: bold;
      }

      .main-number {
        font-size: 160px;
        font-weight: 900;
        color: var(--dp-primary);
        line-height: 1;
        margin: 20px 0;
      }

      .main-counter {
        background-color: var(--dp-accent);
        color: var(--dp-dark);
        font-size: 40px;
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
      }

      /* Sidebar Styling */
      .side-queue .panel {
        margin-bottom: 10px;
        border: none;
        border-left: 5px solid var(--dp-accent);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }

      .side-number {
        font-size: 45px;
        font-weight: bold;
        color: var(--dp-primary);
      }

      .side-label {
        font-size: 18px;
        color: var(--dp-primary-dark);
        font-weight: bold;
      }

      /* Running Text */
      .footer-news {
        position: fixed;
        bottom: 0;
        width: 100%;
        background: var(--dp-dark);
        color: var(--dp-accent);
        padding: 10px 0;
        font-size: 20px;
        border-top: 3px solid var(--dp-primary);
      }
    </style>

    <script type="text/javascript">
      var socketPort = 8085;
      var socketUrl =
        window.location.protocol +
        "//" +
        window.location.hostname +
        ":" +
        socketPort;

      var audioBase = "<?= base_url('assets/audio/') ?>";
      var soundManagerReady = false;

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
          "Gagal memuat socket.io.js. Pastikan Node.js berjalan di port " +
            socketPort,
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

          $("#online").html(nomor_antrian);

          var slug = loket_raw.replace(/[^a-z0-9]/g, "");
          var $side = $("#side-" + slug);
          if ($side.length) {
            $side.html(nomor_antrian);
          }

          var loket_name = "LOKET X";
          if (loket_raw.startsWith("loket")) {
            loket_name =
              "LOKET " + loket_raw.replace("loket", "").replace(/^0+/, "");
          } else if (loket_raw.startsWith("kasir")) {
            loket_name =
              "KASIR " + loket_raw.replace("kasir", "").replace(/^0+/, "");
          } else {
            loket_name = loket_raw.toUpperCase();
          }

          $("#loket_display").html("SILAHKAN KE " + loket_name);

          putarSuara(buatDaftarSuara(nomor_antrian));
        } else {
          $("#online").html(data);
          putarSuara(buatDaftarSuara(String(data)));
        }
      }
    </script>
  </head>
  <body>
    <div class="header-blue text-center">
      <div class="container-fluid">
        <h1 style="margin: 0; font-weight: bold">
          ANTRIAN UPDRS RS PERSAHABATAN
        </h1>
      </div>
    </div>

    <div class="container-fluid">
      <div class="row">
        <div class="col-md-8">
          <div class="main-queue">
            <h2>NOMOR ANTRIAN</h2>
            <div class="main-number" id="online">A-000</div>
            <div class="main-counter" id="loket_display">
              MENUNGGU PANGGILAN
            </div>
          </div>
        </div>

        <div class="col-md-4 side-queue">
          <?php if (!empty($loket)): ?>
            <?php foreach ($loket as $lk): ?>
              <?php
                $nama_loket = !empty($lk['nama_loket']) ? $lk['nama_loket'] : ('LOKET ' . $lk['id']);
                $slug_loket = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama_loket));
                $nomor_terakhir = !empty($lk['nomor_terakhir']) ? $lk['nomor_terakhir'] : '-';
              ?>
              <div class="panel panel-default">
                <div class="panel-body">
                  <div class="row">
                    <div class="col-xs-6 side-label"><?= strtoupper($nama_loket) ?></div>
                    <div class="col-xs-6 text-right side-number" id="side-<?= $slug_loket ?>">
                      <?= htmlspecialchars($nomor_terakhir, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="panel panel-default">
              <div class="panel-body text-center">
                <div class="side-label">BELUM ADA LOKET DIBUKA</div>
              </div>
            </div>
          <?php endif; ?>

          <div
            class="embed-responsive embed-responsive-16by9"
            style="margin-top: 20px; border-radius: 10px; overflow: hidden"
          >
            <!-- Kotak video / banner -->
          </div>
        </div>
      </div>
    </div>

    <div class="footer-news">
      <marquee behavior="scroll" direction="left">
        <strong>PENGUMUMAN:</strong> Jam operasional : 12:00 - 19:00 WIB. |
        Gunakan selalu masker di lingkungan rumah sakit.
      </marquee>
    </div>

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
