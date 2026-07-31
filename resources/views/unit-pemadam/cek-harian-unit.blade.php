@extends('layouts.app')

@section('title', 'Cek Harian Unit')

@section('content')

    @if (session('success'))
        <div class="alert-success" style="margin-bottom:16px;padding:10px 16px;background:#e6f4ea;color:#1e7e34;border-radius:5px;font-size:14px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="form-card">

        <div class="form-card-title">Cek Harian Unit Pemadam</div>

        <form action="{{ route('unit-pemadam.cek-harian-unit.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ============ Nama Pemeriksa ============ --}}
            <div class="form-group">
                <label for="nama_pemeriksa">Nama Pemeriksa</label>
                <input type="text" name="nama_pemeriksa" id="nama_pemeriksa"
                       value="{{ old('nama_pemeriksa') }}" required>
            </div>

            {{-- ============ Jabatan ============ --}}
            <div class="form-group">
                <label for="jabatan">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan"
                       value="{{ old('jabatan') }}" required>
            </div>

            {{-- ============ Unit ============ --}}
            <div class="form-group has-caret">
                <label for="unit">Unit</label>
                <select name="unit" id="unit" required>
                    <option value="" disabled {{ old('unit') ? '' : 'selected' }}></option>
                    @foreach ($unitList as $value => $label)
                        <option value="{{ $value }}" {{ old('unit') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Pos | Regu ============ --}}
            <div class="form-grid">
                <div class="form-group has-caret">
                    <label for="pos">Pos</label>
                    <select name="pos" id="pos" required>
                        <option value="" disabled {{ old('pos') ? '' : 'selected' }}></option>
                        @foreach ($posList as $value => $label)
                            <option value="{{ $value }}" {{ old('pos') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group has-caret">
                    <label for="regu">Regu</label>
                    <select name="regu" id="regu" required>
                        <option value="" disabled {{ old('regu') ? '' : 'selected' }}></option>
                        @foreach ($reguList as $value => $label)
                            <option value="{{ $value }}" {{ old('regu') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- ============ Komandan Regu ============ --}}
            <div class="form-group">
                <label for="komandan_regu">Komandan Regu</label>
                <input type="text" name="komandan_regu" id="komandan_regu"
                       value="{{ old('komandan_regu') }}" required>
            </div>

            {{-- ============ Pemanasan Kendaraan (upload) ============ --}}
            <div class="form-group upload-group">
                <div class="upload-group-header">
                    <div>
                        <label>Pemanasan Kendaraan</label>
                        <p class="form-hint">
                            <em>(Unit harus dioperasikan dan dikendarai minimal sejauh 1 KM. Silakan lampirkan dokumentasi sebagai bukti)</em>
                        </p>
                    </div>
                    <label class="upload-btn" for="foto_pemanasan">
                        <input type="file" id="foto_pemanasan" name="foto_pemanasan[]" multiple accept="image/*" hidden>
                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                               </svg>
                        </span>
                    </label>
                </div>
                <div class="upload-underline"></div>
            </div>

            {{-- ============ BBM (upload) ============ --}}
            <div class="form-group upload-group">
                <div class="upload-group-header">
                    <label>BBM</label>
                    <label class="upload-btn" for="foto_bbm">
                        <input type="file" id="foto_bbm" name="foto_bbm[]" multiple accept="image/*" hidden>
                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                             </svg>
                        </span>
                    </label>
                </div>
                <div class="upload-underline"></div>
            </div>

            
             {{-- ============ Bagian: Tangki & Pompa ============ --}}
            <div class="form-grid">
                @foreach ($leverairItems as $key => $label)
                    <div class="form-group has-caret">
                        <label for="{{ $key }}">{{ $label }}</label>
                        <select name="{{ $key }}" id="{{ $key }}" required>
                            <option value="" disabled {{ old($key) ? '' : 'selected' }}></option>
                            @foreach ($levelairOptions as $value => $optLabel)
                                <option value="{{ $value }}" {{ old($key) == $value ? 'selected' : '' }}>
                                    {{ $optLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

                @foreach ($tangkiPompaItems as $key => $label)
                    <div class="form-group has-caret">
                        <label for="{{ $key }}">{{ $label }}</label>
                        <select name="{{ $key }}" id="{{ $key }}" required>
                            <option value="" disabled {{ old($key) ? '' : 'selected' }}></option>
                            @foreach ($kondisiOptions as $value => $optLabel)
                                <option value="{{ $value }}" {{ old($key) == $value ? 'selected' : '' }}>
                                    {{ $optLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            {{-- ============ Catatan Khusus: Tangki & Pompa ============ --}}
            <div class="form-group">
                <label for="catatan_tangki_pompa">Catatan Khusus Terkait Pemeriksaan Tangki dan Pompa</label>
                <input type="text" name="catatan_tangki_pompa" id="catatan_tangki_pompa"
                       value="{{ old('catatan_tangki_pompa') }}">
            </div>

            {{-- ============ Dokumentasi: Tangki & Pompa (upload) ============ --}}
            <div class="form-group upload-group">
                <div class="upload-group-header">
                    <div>
                        <label>Dokumentasi Pengecekan Tangki dan Pompa <em>(maksimal 3 foto)</em></label>
                        <p class="form-hint">Upload maksimum 5 file yang didukung: image. Maks 10 MB per file.</p>
                    </div>
                    <label class="upload-btn" for="foto_tangki_pompa">
                        <input type="file" id="foto_tangki_pompa" name="foto_tangki_pompa[]" multiple accept="image/*" hidden>
                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                 <line x1="12" y1="5" x2="12" y2="19"></line>
                                 <line x1="5" y1="12" x2="19" y2="12"></line>
                                 </svg>
                        </span>
                    </label>
                </div>
                <div class="upload-underline"></div>
            </div>

            {{-- ============ Bagian: Bagian Dalam Unit ============ --}}
            <div class="form-grid">
                @foreach ($bagianDalamItems as $key => $label)
                    <div class="form-group has-caret">
                        <label for="{{ $key }}">{{ $label }}</label>
                        <select name="{{ $key }}" id="{{ $key }}" required>
                            <option value="" disabled {{ old($key) ? '' : 'selected' }}></option>
                            @foreach ($kondisiOptions as $value => $optLabel)
                                <option value="{{ $value }}" {{ old($key) == $value ? 'selected' : '' }}>
                                    {{ $optLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            {{-- ============ Catatan Khusus: Bagian Dalam ============ --}}
            <div class="form-group">
                <label for="catatan_bagian_dalam">Catatan Khusus Terkait Pemeriksaan Unit Bagian Dalam</label>
                <input type="text" name="catatan_bagian_dalam" id="catatan_bagian_dalam"
                       value="{{ old('catatan_bagian_dalam') }}">
            </div>

            {{-- ============ Dokumentasi: Bagian Dalam (upload) ============ --}}
            <div class="form-group upload-group">
                <div class="upload-group-header">
                    <label>Dokumentasi Pemeriksaan Bagian Dalam Unit <em>(maksimal 3 foto)</em></label>
                    <label class="upload-btn" for="foto_bagian_dalam">
                        <input type="file" id="foto_bagian_dalam" name="foto_bagian_dalam[]" multiple accept="image/*" hidden>
                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                 <line x1="12" y1="5" x2="12" y2="19"></line>
                                 <line x1="5" y1="12" x2="19" y2="12"></line>
                              </svg>
                        </span>
                    </label>
                </div>
                <div class="upload-underline"></div>
            </div>

            {{-- ============ Bagian: Bagian Luar Unit ============ --}}
            <div class="form-grid">
                @foreach ($bagianLuarItems as $key => $label)
                    <div class="form-group has-caret">
                        <label for="{{ $key }}">{{ $label }}</label>
                        <select name="{{ $key }}" id="{{ $key }}" required>
                            <option value="" disabled {{ old($key) ? '' : 'selected' }}></option>
                            @foreach ($kondisiOptions as $value => $optLabel)
                                <option value="{{ $value }}" {{ old($key) == $value ? 'selected' : '' }}>
                                    {{ $optLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            {{-- ============ Catatan Khusus: Bagian Luar ============ --}}
            <div class="form-group">
                <label for="catatan_bagian_luar">Catatan Khusus Terkait Pemeriksaan Unit Bagian Luar</label>
                <input type="text" name="catatan_bagian_luar" id="catatan_bagian_luar"
                       value="{{ old('catatan_bagian_luar') }}">
            </div>

            {{-- ============ Dokumentasi: Bagian Luar (upload) ============ --}}
            <div class="form-group upload-group">
                <div class="upload-group-header">
                    <label>Dokumentasi Pemeriksaan Bagian Luar Unit <em>(maksimal 3 foto)</em></label>
                    <label class="upload-btn" for="foto_bagian_luar">
                        <input type="file" id="foto_bagian_luar" name="foto_bagian_luar[]" multiple accept="image/*" hidden>
                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                 <line x1="12" y1="5" x2="12" y2="19"></line>
                                 <line x1="5" y1="12" x2="19" y2="12"></line>
                              </svg>
                        </span>
                    </label>
                </div>
                <div class="upload-underline"></div>
            </div>

            {{-- ============ Catatan Khusus (umum) ============ --}}
            <div class="form-group">
                <label for="catatan_khusus">Catatan Khusus</label>
                <p class="form-hint">(Silakan tulis jika ada catatan khusus di luar poin-poin di atas yang telah disebutkan)</p>
                <input type="text" name="catatan_khusus" id="catatan_khusus"
                       value="{{ old('catatan_khusus') }}">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Kirim</button>
            </div>

        </form>
    </div>

@endsection
