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

// ====== Alur pilih keperluan (Check in / Daftar) + auto-call API UTDRS ======
// Dipindahkan dari inline <script> di welcome_message.php. Nilai dinamis dari
// server (endpoint proxy + flag flashdata) diteruskan via window.WELCOME_CFG.
(function () {
    var cfg = window.WELCOME_CFG || {};

    var visitStep   = document.getElementById('visitTypeStep');
    var formStep    = document.getElementById('formStep');
    var tipeInput   = document.getElementById('tipeKunjungan');
    var nikInput    = document.getElementById('nik');
    var pilihanBdg  = document.getElementById('pilihanBadge');
    var btnGanti    = document.getElementById('btnGantiPilihan');
    var form        = document.getElementById('formAmbil');
    var apiState    = document.getElementById('apiState');

    // Panggil proxy server-side (Welcome::self_checkin / self_register)
    // agar JWT/kredensial UTDRS tidak terekspos di browser. Backend CI
    // yang menambahkan Authorization: Bearer ke API UTDRS.
    var API_ENDPOINTS = cfg.endpoints || {};

    var apiInflight  = false;
    var apiSucceeded = false;

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
        });
    }

    function setApiState(html) {
        if ( ! apiState) return;
        apiState.innerHTML  = html;
        apiState.style.display = html === '' ? 'none' : 'block';
    }

    function showForm(tipe) {
        tipeInput.value = tipe;
        if (tipe === 'checkin') {
            pilihanBdg.textContent = 'CHECK IN';
            pilihanBdg.className   = 'pilihan-badge checkin';
        } else {
            pilihanBdg.textContent = 'DAFTAR';
            pilihanBdg.className   = 'pilihan-badge daftar';
        }
        if (visitStep) visitStep.style.display = 'none';
        if (formStep)  formStep.style.display  = 'block';
        if (nikInput) {
            nikInput.setAttribute('required', 'required');
            try { nikInput.focus(); } catch (e) {}
        }
    }

    function showVisit() {
        tipeInput.value = '';
        if (visitStep) visitStep.style.display = 'block';
        if (formStep)  formStep.style.display  = 'none';
        if (nikInput) {
            nikInput.removeAttribute('required');
            nikInput.removeAttribute('readonly');
            nikInput.value = '';
        }
        apiInflight  = false;
        apiSucceeded = false;
        setApiState('');
    }

    function callApi(tipe, nik) {
        var endpoint = API_ENDPOINTS[tipe];
        if ( ! endpoint) return;

        apiInflight = true;
        var label = tipe === 'checkin' ? 'check-in' : 'pendaftaran';
        setApiState('<div class="alert alert-info" style="margin:0;">Memproses ' + label + '… mohon tunggu.</div>');

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nik: nik })
        })
        .then(function (res) {
            return res.json()
                .catch(function () { return null; })
                .then(function (body) { return { ok: res.ok, status: res.status, body: body }; });
        })
        .then(function (result) {
            apiInflight = false;
            var body = result.body || {};
            // Proxy server-side selalu balas HTTP 200; status nyata ada
            // di body.status (Success/Error).
            var isOk = body.status
                ? (String(body.status).toLowerCase() === 'success')
                : result.ok;
            if (isOk) {
                apiSucceeded = true;
                var msg = body.message || body.msg
                       || (tipe === 'checkin' ? 'Check-in berhasil.' : 'Pendaftaran berhasil.');
                var extra = '';
                // Check-in: tampilkan nomor antrian. Register: tampilkan kode registrasi.
                var bigCode = body.nomor_antrian || body.uniq_code || body.kode_pendonor;
                if (bigCode) {
                    extra += '<div style="font-size:36px;font-weight:900;color:#0a7a6b;margin-top:8px;">'
                          +  escapeHtml(bigCode) + '</div>';
                }
                if (tipe !== 'checkin' && (body.uniq_code || body.kode_pendonor)) {
                    extra += '<div style="margin-top:4px;font-size:13px;color:#555;">Kode Registrasi — simpan untuk Form Consent</div>';
                }
                if (body.nama) {
                    extra += '<div style="margin-top:4px;">' + escapeHtml(body.nama) + '</div>';
                }
                setApiState(
                    '<div class="alert alert-success" style="margin:0;text-align:center;">'
                  + '<strong>' + escapeHtml(msg) + '</strong>'
                  + extra
                  + '</div>'
                );
                if (nikInput) nikInput.setAttribute('readonly', 'readonly');
            } else {
                var emsg = body.message || body.msg || body.error
                        || ('Permintaan gagal (HTTP ' + result.status + ').');
                setApiState('<div class="alert alert-danger" style="margin:0;">' + escapeHtml(emsg) + '</div>');
            }
        })
        .catch(function (err) {
            apiInflight = false;
            setApiState(
                '<div class="alert alert-danger" style="margin:0;">'
              + 'Tidak dapat terhubung ke server: ' + escapeHtml(err && err.message ? err.message : 'unknown')
              + '</div>'
            );
        });
    }

    document.querySelectorAll('.btn-visit-type').forEach(function (btn) {
        btn.addEventListener('click', function () { showForm(this.dataset.tipe); });
    });
    if (btnGanti) btnGanti.addEventListener('click', showVisit);

    if (nikInput) {
        // Trigger auto-call begitu NIK valid 16 digit. setTimeout(0) supaya kita
        // melihat nilai SETELAH filter karakter non-digit di IIFE atas berjalan.
        nikInput.addEventListener('input', function () {
            var el = this;
            setTimeout(function () {
                if (apiInflight || apiSucceeded) return;
                if ( ! tipeInput.value) return;
                if ( ! /^\d{16}$/.test(el.value)) return;
                callApi(tipeInput.value, el.value);
            }, 0);
        });
    }

    // Saat reload akibat error (NIK salah / layanan belum dipilih),
    // server kirim flashdata "nik" / "error" — buka langsung form supaya
    // user tidak perlu klik ulang Check in / Daftar.
    if (cfg.hasOldNik || cfg.hasError) {
        showForm('checkin');
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            // Form sekarang murni interaksi sisi-klien (fetch ke API eksternal).
            // Cegah submit konvensional supaya halaman tidak reload.
            e.preventDefault();
            if ( ! tipeInput.value) {
                showVisit();
                return;
            }
            if (nikInput && /^\d{16}$/.test(nikInput.value) && ! apiInflight && ! apiSucceeded) {
                callApi(tipeInput.value, nikInput.value);
            }
        });
    }
})();
