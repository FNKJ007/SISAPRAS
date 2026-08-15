{{-- Shared form fields for Tambah & Edit Kejadian modals. $mode is 'create' or 'edit'. --}}
@php $isEdit = ($mode ?? 'create') === 'edit'; @endphp

<div class="modal-form-grid">
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kode Kejadian <span style="color:#DC2626;">*</span></label>
        @if($isEdit)
            <input type="text" name="kode_kejadian" required x-model="editForm.kode_kejadian"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="text" id="createKodeKejadian" name="kode_kejadian" required value="{{ old('kode_kejadian', 'KEJ-' . date('Ymd') . '-' . rand(100, 999)) }}" readonly
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid {{ $errors->has('kode_kejadian') ? '#FCA5A5' : '#CBD5E1' }}; outline:none; background:#F8FAFC; color:#64748B;">
            <span style="display:block; font-size:10.5px; color:#94A3B8; margin-top:3px;">Otomatis mengikuti Jenis &amp; Status: TK65 (Kebakaran), RESC (Rescue), PRESC (Rescue Pending).</span>
            @error('kode_kejadian')
                <span style="display:block; font-size:11px; color:#DC2626; margin-top:3px; font-weight:600;">{{ $message }}</span>
            @enderror
        @endif
    </div>
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Waktu Kejadian <span style="color:#DC2626;">*</span></label>
        @if($isEdit)
            <input type="datetime-local" name="waktu_kejadian" required x-model="editForm.waktu_kejadian"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="datetime-local" name="waktu_kejadian" required value="{{ old('waktu_kejadian') }}"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @endif
    </div>
</div>

<div class="modal-form-grid">
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jenis Kejadian <span style="color:#DC2626;">*</span></label>
        @if($isEdit)
            <select name="jenis_kejadian" required x-model="editForm.jenis_kejadian"
                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                <option value="Kebakaran">Kebakaran</option>
                <option value="Rescue">Rescue</option>
                <option value="Penyelamatan">Penyelamatan</option>
                <option value="Non-Kebakaran">Non-Kebakaran</option>
            </select>
        @else
            <select id="createJenisKejadian" name="jenis_kejadian" required
                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                <option value="" {{ old('jenis_kejadian') ? '' : 'selected' }}>-- Pilih Jenis --</option>
                <option value="Kebakaran" {{ old('jenis_kejadian') === 'Kebakaran' ? 'selected' : '' }}>Kebakaran</option>
                <option value="Rescue" {{ old('jenis_kejadian') === 'Rescue' ? 'selected' : '' }}>Rescue / Penyelamatan</option>
                <option value="Penyelamatan" {{ old('jenis_kejadian') === 'Penyelamatan' ? 'selected' : '' }}>Penyelamatan</option>
                <option value="Non-Kebakaran" {{ old('jenis_kejadian') === 'Non-Kebakaran' ? 'selected' : '' }}>Non-Kebakaran</option>
            </select>
        @endif
    </div>
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status <span style="color:#DC2626;">*</span></label>
        @if($isEdit)
            <select name="status" required x-model="editForm.status"
                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                <option value="Proses">Proses</option>
                <option value="Selesai">Selesai</option>
                <option value="Dibatalkan">Dibatalkan</option>
            </select>
        @else
            <select id="createStatusKejadian" name="status" required
                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                <option value="Selesai" {{ old('status', 'Selesai') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="Proses" {{ old('status') === 'Proses' ? 'selected' : '' }}>Proses (Pending)</option>
                <option value="Dibatalkan" {{ old('status') === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        @endif
    </div>
</div>

<div style="margin-bottom:14px;">
    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kategori Detail Kejadian <span style="color:#DC2626;">*</span></label>
    @if($isEdit)
        <input type="text" name="kategori_detail" required x-model="editForm.kategori_detail" placeholder="Contoh: Kebakaran Rumah, Evakuasi Ular, Sarang Tawon"
               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
    @else
        <input type="text" name="kategori_detail" required placeholder="Contoh: Kebakaran Rumah, Evakuasi Ular, Sarang Tawon" value="{{ old('kategori_detail') }}"
               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
    @endif
</div>

<div style="margin-bottom:14px;">
    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Lokasi Kejadian <span style="color:#DC2626;">*</span></label>
    @if($isEdit)
        <input type="text" name="lokasi" required x-model="editForm.lokasi" placeholder="Alamat lengkap lokasi kejadian"
               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
    @else
        <input type="text" name="lokasi" required placeholder="Alamat lengkap lokasi kejadian" value="{{ old('lokasi') }}"
               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
    @endif
</div>

<div class="modal-form-grid">
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kecamatan</label>
        @if($isEdit)
            <input type="text" name="kecamatan" x-model="editForm.kecamatan"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="text" name="kecamatan" value="{{ old('kecamatan') }}"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @endif
    </div>
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kelurahan</label>
        @if($isEdit)
            <input type="text" name="kelurahan" x-model="editForm.kelurahan"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="text" name="kelurahan" value="{{ old('kelurahan') }}"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @endif
    </div>
</div>

{{-- Section: Penanganan --}}
<div style="background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0; margin-bottom:14px;">
    <div style="font-size:12px; font-weight:800; color:#1E3A8A; margin-bottom:10px;">PENANGANAN</div>
    <div class="modal-form-grid-3">
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Pos / Regu <span style="color:#DC2626;">*</span></label>
            @if($isEdit)
                <input type="text" name="pos_regu" required x-model="editForm.pos_regu" placeholder="Contoh: Pos Mako / Regu A"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="text" name="pos_regu" required placeholder="Contoh: Pos Mako / Regu A" value="{{ old('pos_regu') }}"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Danru / Komandan Regu</label>
            @if($isEdit)
                <input type="text" name="komandan_regu" x-model="editForm.komandan_regu" placeholder="Nama Danru bertugas"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="text" name="komandan_regu" placeholder="Nama Danru bertugas" value="{{ old('komandan_regu') }}"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Unit / Armada</label>
            @if($isEdit)
                <input type="text" name="unit_armada" x-model="editForm.unit_armada" placeholder="Contoh: Damkar-02"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="text" name="unit_armada" placeholder="Contoh: Damkar-02" value="{{ old('unit_armada') }}"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
    </div>
    <div class="modal-form-grid-3" style="margin-top:10px;">
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Waktu Terima Laporan</label>
            @if($isEdit)
                <input type="time" name="waktu_terima_laporan" x-model="editForm.waktu_terima_laporan"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="time" name="waktu_terima_laporan" value="{{ old('waktu_terima_laporan') }}"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Waktu Berangkat</label>
            @if($isEdit)
                <input type="time" name="waktu_berangkat" x-model="editForm.waktu_berangkat"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="time" name="waktu_berangkat" value="{{ old('waktu_berangkat') }}"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Waktu Tiba</label>
            @if($isEdit)
                <input type="time" name="waktu_tiba" x-model="editForm.waktu_tiba"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="time" name="waktu_tiba" value="{{ old('waktu_tiba') }}"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Waktu Selesai</label>
            @if($isEdit)
                <input type="time" name="waktu_selesai" x-model="editForm.waktu_selesai"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai') }}"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
    </div>
</div>

{{-- Section: Dampak --}}
<div style="background:#FEF2F2; padding:14px; border-radius:10px; border:1px solid #FCA5A5; margin-bottom:14px;">
    <div style="font-size:12px; font-weight:800; color:#991B1B; margin-bottom:10px;">DAMPAK KEJADIAN</div>
    <div class="modal-form-grid-3">
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Estimasi Kerugian (Rp)</label>
            @if($isEdit)
                <input type="number" name="estimasi_kerugian" min="0" x-model="editForm.estimasi_kerugian" placeholder="0"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="number" name="estimasi_kerugian" min="0" placeholder="0" value="{{ old('estimasi_kerugian') }}"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Korban Luka</label>
            @if($isEdit)
                <input type="number" name="korban_luka" min="0" x-model="editForm.korban_luka" value="0"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="number" name="korban_luka" min="0" value="{{ old('korban_luka', 0) }}"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Korban Jiwa</label>
            @if($isEdit)
                <input type="number" name="korban_jiwa" min="0" x-model="editForm.korban_jiwa" value="0"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="number" name="korban_jiwa" min="0" value="{{ old('korban_jiwa', 0) }}"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
    </div>
    <div class="modal-form-grid" style="margin-top:10px; margin-bottom:0;">
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Objek Terdampak</label>
            @if($isEdit)
                <input type="text" name="objek_terdampak" x-model="editForm.objek_terdampak" placeholder="Contoh: 1 unit rumah, kios"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="text" name="objek_terdampak" placeholder="Contoh: 1 unit rumah, kios" value="{{ old('objek_terdampak') }}"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Penyebab</label>
            @if($isEdit)
                <input type="text" name="penyebab" x-model="editForm.penyebab" placeholder="Contoh: Korsleting listrik"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="text" name="penyebab" placeholder="Contoh: Korsleting listrik" value="{{ old('penyebab') }}"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
    </div>
</div>

{{-- Section: Pelapor & Catatan --}}
<div class="modal-form-grid">
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Pelapor</label>
        @if($isEdit)
            <input type="text" name="nama_pelapor" x-model="editForm.nama_pelapor"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="text" name="nama_pelapor" value="{{ old('nama_pelapor') }}"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @endif
    </div>
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. HP Pelapor</label>
        @if($isEdit)
            <input type="text" name="no_hp_pelapor" x-model="editForm.no_hp_pelapor"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="text" name="no_hp_pelapor" value="{{ old('no_hp_pelapor') }}"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @endif
    </div>
</div>

<div>
    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Keterangan</label>
    @if($isEdit)
        <textarea name="keterangan" rows="2" x-model="editForm.keterangan" placeholder="Keterangan tambahan..."
                  style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; resize:vertical;"></textarea>
    @else
        <textarea name="keterangan" rows="2" placeholder="Keterangan tambahan..."
                  style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; resize:vertical;">{{ old('keterangan') }}</textarea>
    @endif
</div>

{{-- Section: Dokumen Pendukung (PDF) --}}
<div style="margin-top:14px; background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">
        <i data-lucide="file-text" style="width:13px; height:13px; display:inline; vertical-align:-2px; margin-right:4px; color:#DC2626;"></i>
        Dokumen Laporan (PDF)
    </label>

    @if($isEdit)
        <template x-if="editForm.file_laporan">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; background:#FFFFFF; border:1px solid #E2E8F0; border-radius:8px; padding:8px 12px; margin-bottom:8px;">
                <a :href="'/storage/' + editForm.file_laporan" target="_blank" style="display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; color:#1D4ED8; text-decoration:none; flex:1;">
                    <i data-lucide="file-check-2" style="width:14px; height:14px;"></i>
                    <span>Lihat dokumen yang sudah diunggah</span>
                </a>
            </div>
        </template>
        <input type="file" name="file_laporan" accept="application/pdf"
               style="width:100%; padding:7px 12px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
        <span style="display:block; font-size:10.5px; color:#94A3B8; margin-top:4px;">Kosongkan jika tidak ingin mengganti file. Format PDF, maks. 5MB.</span>
    @else
        <input type="file" name="file_laporan" accept="application/pdf"
               style="width:100%; padding:7px 12px; font-size:12.5px; border-radius:8px; border:1px solid {{ $errors->has('file_laporan') ? '#FCA5A5' : '#CBD5E1' }}; outline:none; background:#FFFFFF;">
        <span style="display:block; font-size:10.5px; color:#94A3B8; margin-top:4px;">Opsional. Unggah laporan/berita acara kejadian dalam format PDF, maks. 5MB.</span>
        @error('file_laporan')
            <span style="display:block; font-size:11px; color:#DC2626; margin-top:3px; font-weight:600;">{{ $message }}</span>
        @enderror
    @endif
</div>
