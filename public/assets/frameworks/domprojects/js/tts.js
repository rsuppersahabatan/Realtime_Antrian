/**
 * TTS engine untuk display antrian.
 *
 * Default: panggilan diucapkan via edge-tts API (suara natural id-ID).
 * Bila request gagal / timeout → fallback ke pemutaran potongan WAV lama
 * (buatDaftarSuara + putarSuaraLegacy) supaya display tetap bersuara
 * walaupun service TTS down.
 */

// ====== KONFIGURASI EDGE-TTS ======
// Semua bisa dioverride dari display.php dengan men-set window.tts* SEBELUM
// file ini di-load, mis. `<script>window.ttsApiBase='https://tts.example';</script>`
var ttsApiBase   = (typeof window !== 'undefined' && window.ttsApiBase)   || 'http://localhost:8021';
var ttsApiKey    = (typeof window !== 'undefined' && window.ttsApiKey)    || '';
var ttsVoice     = (typeof window !== 'undefined' && window.ttsVoice)     || 'female';
var ttsRate      = (typeof window !== 'undefined' && window.ttsRate)      || '+0%';
var ttsLanguage  = (typeof window !== 'undefined' && window.ttsLanguage)  || 'indonesian';
var ttsTimeoutMs = (typeof window !== 'undefined' && window.ttsTimeoutMs) || 8000;

// ====== LEGACY (FALLBACK): pemutaran per-suku-kata pakai WAV lokal ======

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

function putarSuaraLegacy(daftarSuara) {
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

// ====== EDGE-TTS: helper, queue, playback ======

/**
 * Normalisasi nomor antrian agar diucapkan natural oleh TTS.
 * "A206" -> "A 206" supaya engine tidak menebak sebagai satu token aneh.
 */
function formatNomorUntukTTS(nomorAntrian) {
  var m = nomorAntrian.match(/^([a-zA-Z]+)(\d+)$/);
  if (m) {
    return m[1].toUpperCase().split('').join(' ') + ' ' + m[2];
  }
  return nomorAntrian;
}

/**
 * Susun kalimat panggilan dalam Bahasa Indonesia.
 * Bila keterangan kosong, bagian "atas nama" di-skip.
 */
function buatTeksPanggilan(nomorAntrian, loketName, keterangan) {
  var nomor = formatNomorUntukTTS(nomorAntrian);
  var teks = 'Nomor antrian ' + nomor;
  var ket = (keterangan || '').trim();
  if (ket !== '') {
    teks += ', atas nama ' + ket;
  }
  if (loketName) {
    teks += ', silahkan menuju ' + loketName;
  }
  return teks + '.';
}

// Queue panggilan TTS supaya panggilan beruntun tidak saling tumpang tindih.
var ttsQueue = [];
var ttsPlaying = false;
var ttsCurrentAudio = null;

function enqueueTTS(payload) {
  ttsQueue.push(payload);
  if (!ttsPlaying) processTTSQueue();
}

function processTTSQueue() {
  if (ttsQueue.length === 0) {
    ttsPlaying = false;
    return;
  }
  ttsPlaying = true;
  var payload = ttsQueue.shift();
  requestEdgeTTS(payload)
    .then(function () { processTTSQueue(); })
    .catch(function (err) {
      console.error('[tts] edge-tts gagal, fallback ke WAV lama:', err);
      try {
        putarSuaraLegacy(buatDaftarSuara(payload.nomor));
      } catch (e) {
        console.error('[tts] fallback legacy juga gagal:', e);
      }
      // Beri jeda kecil supaya fallback (yang asinkron via SoundManager)
      // sempat selesai sebelum panggilan berikutnya dari queue diproses.
      setTimeout(processTTSQueue, 800);
    });
}

function requestEdgeTTS(payload) {
  return new Promise(function (resolve, reject) {
    var ctrl = (typeof AbortController === 'function') ? new AbortController() : null;
    var timer = setTimeout(function () {
      if (ctrl) ctrl.abort();
      else reject(new Error('timeout'));
    }, ttsTimeoutMs);

    var headers = { 'Content-Type': 'application/json' };
    if (ttsApiKey) headers['X-API-Key'] = ttsApiKey;

    fetch(ttsApiBase + '/tts', {
      method: 'POST',
      headers: headers,
      body: JSON.stringify({
        text: payload.text,
        voice: ttsVoice,
        rate: ttsRate,
        language: ttsLanguage
      }),
      signal: ctrl ? ctrl.signal : undefined
    })
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function (data) {
        if (!data || !data.success || !data.audio_url) {
          throw new Error('respons TTS tidak valid');
        }
        var audio = new Audio(ttsApiBase + data.audio_url);
        ttsCurrentAudio = audio;
        audio.onended = function () {
          clearTimeout(timer);
          ttsCurrentAudio = null;
          resolve();
        };
        audio.onerror = function () {
          clearTimeout(timer);
          ttsCurrentAudio = null;
          reject(new Error('audio playback error'));
        };
        var p = audio.play();
        if (p && typeof p.then === 'function') {
          p.catch(function (e) {
            clearTimeout(timer);
            ttsCurrentAudio = null;
            reject(e);
          });
        }
      })
      .catch(function (err) {
        clearTimeout(timer);
        reject(err);
      });
  });
}

function addMessage(data) {
  if (!data) return;
  var raw = data.toString();

  // Format payload: "loketXX-NOMOR" atau "loketXX-NOMOR|KETERANGAN"
  // Pisahkan keterangan terlebih dulu (separator "|") agar nomor antrian
  // tetap bersih dari teks keterangan.
  var pipeIdx = raw.indexOf("|");
  var keterangan = "";
  var head = raw;
  if (pipeIdx >= 0) {
    head = raw.substring(0, pipeIdx);
    keterangan = raw.substring(pipeIdx + 1).trim();
  }

  var vdt = head.split("-");

  if (vdt.length > 1) {
    var loket_raw = vdt[0].toLowerCase();
    var nomor_antrian = vdt[1];

    // Filter: hanya proses panggilan dari loket yang ter-assign ke client ini.
    // `loketLabels` di-render server-side berisi loket milik client/display ini saja,
    // jadi dipakai sebagai allowlist agar display tidak ikut bunyi/flash untuk
    // panggilan dari client lain yang di-broadcast lewat channel realtime yang sama.
    if (!loketLabels || !Object.prototype.hasOwnProperty.call(loketLabels, loket_raw)) {
      return;
    }

    // Update nomor antrian utama
    var mainNum = document.getElementById("online");
    mainNum.textContent = nomor_antrian;
    mainNum.classList.remove("call-flash");
    void mainNum.offsetWidth; // reflow untuk trigger ulang animasi
    mainNum.classList.add("call-flash");

    // Update keterangan di bawah nomor antrian
    var ketEl = document.getElementById("keterangan_text");
    if (ketEl) {
      ketEl.textContent = keterangan;
      ketEl.classList.toggle("is-empty", keterangan === "");
    }

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

    // Update keterangan di footer loket (di bawah nomor)
    var footerKet = document.getElementById("footer-ket-" + loket_raw);
    if (footerKet) {
      footerKet.textContent = keterangan;
      footerKet.classList.toggle("is-empty", keterangan === "");
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

    enqueueTTS({
      nomor: nomor_antrian,
      text: buatTeksPanggilan(nomor_antrian, loket_name, keterangan)
    });
  } else {
    document.getElementById("online").textContent = head;
    var ketElFallback = document.getElementById("keterangan_text");
    if (ketElFallback) {
      ketElFallback.textContent = keterangan;
      ketElFallback.classList.toggle("is-empty", keterangan === "");
    }
    enqueueTTS({
      nomor: String(head),
      text: buatTeksPanggilan(String(head), '', keterangan)
    });
  }
}

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
