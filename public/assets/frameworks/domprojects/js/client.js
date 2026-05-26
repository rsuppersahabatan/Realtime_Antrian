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

  // Handle new queue number created (antrian-baru-<id_layanan>-<nomor_antrian>)
  if (vdt[0] === 'antrian' && vdt[1] === 'baru') {
    var id_layanan = vdt[2];
    var nomor_antrian = vdt[3];
    var $list = $("#col-list-" + id_layanan);
    if ($list.length) {
      $list.find(".loket-list-empty").remove();
      if ($list.find('.loket-list-item[data-nomor="' + nomor_antrian + '"]').length === 0) {
        var html = '<div class="loket-list-item" data-nomor="' + nomor_antrian + '">' +
                   '<span class="li-nomor">' + nomor_antrian + '</span>';
        if (keterangan) {
          html += '<span class="li-sep">-</span>' +
                  '<span class="li-ket">' + keterangan + '</span>';
        }
        html += '</div>';
        $list.append(html);
      }
    }
    return;
  }

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

    // Update kolom "Daftar Antrian" (panel kiri): hapus item dengan nomor tsb
    // dari semua list (karena sudah tidak menunggu). Jika list kosong, tampilkan placeholder.
    var colCard = document.getElementById("col-" + loket_raw);
    if (colCard) {
      colCard.classList.remove("call-flash");
      void colCard.offsetWidth;
      colCard.classList.add("call-flash");
    }
    var waitingItems = document.querySelectorAll('.loket-list-item[data-nomor="' + nomor_antrian + '"]');
    waitingItems.forEach(function (item) {
      var parent = item.parentNode;
      item.parentNode.removeChild(item);
      if (parent && parent.children.length === 0) {
        var empty = document.createElement("div");
        empty.className = "loket-list-empty";
        empty.textContent = "Belum ada antrian menunggu";
        parent.appendChild(empty);
      }
    });

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

    putarSuara(buatDaftarSuara(nomor_antrian));
  } else {
    document.getElementById("online").textContent = head;
    var ketElFallback = document.getElementById("keterangan_text");
    if (ketElFallback) {
      ketElFallback.textContent = keterangan;
      ketElFallback.classList.toggle("is-empty", keterangan === "");
    }
    putarSuara(buatDaftarSuara(String(head)));
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

// ====== VERSION CHECK / CACHE BUSTER ======
(function () {
  var CLIENT_VERSION = "1.0.2";
  window.CLIENT_VERSION = CLIENT_VERSION;

  window.checkVersion = function (serverVersion) {
    if (serverVersion && serverVersion !== CLIENT_VERSION) {
      console.warn("Version mismatch! Server: " + serverVersion + ", Client: " + CLIENT_VERSION);
      var popup = document.getElementById("updatePopup");
      if (popup) {
        popup.classList.remove("hidden");
      }
      
      var reloadBtn = document.getElementById("updatePopupReloadBtn");
      if (reloadBtn) {
        var newReloadBtn = reloadBtn.cloneNode(true);
        reloadBtn.parentNode.replaceChild(newReloadBtn, reloadBtn);
        
        newReloadBtn.addEventListener("click", function () {
          var cleanUrl = window.location.href.replace(/[?&]v=[^&]+/g, '');
          var sep = cleanUrl.indexOf('?') >= 0 ? '&' : '?';
          window.location.href = cleanUrl + sep + 'v=' + serverVersion;
        });
      }
    }
  };
})();
