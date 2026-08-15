{{-- Shared form fields for Tambah & Edit Kejadian modals. $mode is 'create' or 'edit'. --}}
@php $isEdit = ($mode ?? 'create') === 'edit'; @endphp

<div class="modal-form-grid">
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kode Kejadian <span style="color:#DC2626;">*</span></label>
        @if($isEdit)
            <input type="text" name="kode_kejadian" required x-model="editForm.kode_kejadian"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="text" name="kode_kejadian" required value="KEJ-{{ date('Ymd') }}-{{ rand(100, 999) }}" readonly
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#64748B;">
        @endif
    </div>
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Waktu Kejadian <span style="color:#DC2626;">*</span></label>
        @if($isEdit)
            <input type="datetime-local" name="waktu_kejadian" required x-model="editForm.waktu_kejadian"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="datetime-local" name="waktu_kejadian" required
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
            <select name="jenis_kejadian" required
                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                <option value="">-- Pilih Jenis --</option>
                <option value="Kebakaran">Kebakaran</option>
                <option value="Rescue">Rescue / Penyelamatan</option>
                <option value="Penyelamatan">Penyelamatan</option>
                <option value="Non-Kebakaran">Non-Kebakaran</option>
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
            <select name="status" required
                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                <option value="Selesai">Selesai</option>
                <option value="Proses">Proses</option>
                <option value="Dibatalkan">Dibatalkan</option>
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
        <input type="text" name="kategori_detail" required placeholder="Contoh: Kebakaran Rumah, Evakuasi Ular, Sarang Tawon"
               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
    @endif
</div>

<div style="margin-bottom:14px;">
    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Lokasi Kejadian <span style="color:#DC2626;">*</span></label>
    @if($isEdit)
        <input type="text" name="lokasi" required x-model="editForm.lokasi" placeholder="Alamat lengkap lokasi kejadian"
               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
    @else
        <input type="text" name="lokasi" required placeholder="Alamat lengkap lokasi kejadian"
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
            <input type="text" name="kecamatan"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @endif
    </div>
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kelurahan</label>
        @if($isEdit)
            <input type="text" name="kelurahan" x-model="editForm.kelurahan"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="text" name="kelurahan"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @endif
    </div>
</div>

{{-- Section: Penanganan --}}
<div style="background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0; margin-bottom:14px;">
    <div style="font-size:12px; font-weight:800; color:#1E3A8A; margin-bottom:10px;">PENANGANAN</div>
    <div class="modal-form-grid">
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Pos / Regu <span style="color:#DC2626;">*</span></label>
            @if($isEdit)
                <input type="text" name="pos_regu" required x-model="editForm.pos_regu" placeholder="Contoh: Pos Mako / Regu A"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="text" name="pos_regu" required placeholder="Contoh: Pos Mako / Regu A"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Unit / Armada</label>
            @if($isEdit)
                <input type="text" name="unit_armada" x-model="editForm.unit_armada" placeholder="Contoh: Damkar-02"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="text" name="unit_armada" placeholder="Contoh: Damkar-02"
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
                <input type="time" name="waktu_terima_laporan"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Waktu Berangkat</label>
            @if($isEdit)
                <input type="time" name="waktu_berangkat" x-model="editForm.waktu_berangkat"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="time" name="waktu_berangkat"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Waktu Tiba</label>
            @if($isEdit)
                <input type="time" name="waktu_tiba" x-model="editForm.waktu_tiba"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="time" name="waktu_tiba"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Waktu Selesai</label>
            @if($isEdit)
                <input type="time" name="waktu_selesai" x-model="editForm.waktu_selesai"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="time" name="waktu_selesai"
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
                <input type="number" name="estimasi_kerugian" min="0" placeholder="0"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Korban Luka</label>
            @if($isEdit)
                <input type="number" name="korban_luka" min="0" x-model="editForm.korban_luka" value="0"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="number" name="korban_luka" min="0" value="0"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Korban Jiwa</label>
            @if($isEdit)
                <input type="number" name="korban_jiwa" min="0" x-model="editForm.korban_jiwa" value="0"
                       style="width:100%; padding:6px 10px; font-size:12.5px; border-radius:6px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="number" name="korban_jiwa" min="0" value="0"
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
                <input type="text" name="objek_terdampak" placeholder="Contoh: 1 unit rumah, kios"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @endif
        </div>
        <div>
            <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:3px;">Penyebab</label>
            @if($isEdit)
                <input type="text" name="penyebab" x-model="editForm.penyebab" placeholder="Contoh: Korsleting listrik"
                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
            @else
                <input type="text" name="penyebab" placeholder="Contoh: Korsleting listrik"
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
            <input type="text" name="nama_pelapor"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @endif
    </div>
    <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. HP Pelapor</label>
        @if($isEdit)
            <input type="text" name="no_hp_pelapor" x-model="editForm.no_hp_pelapor"
                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
        @else
            <input type="text" name="no_hp_pelapor"
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
                  style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; resize:vertical;"></textarea>
    @endif
</div>
