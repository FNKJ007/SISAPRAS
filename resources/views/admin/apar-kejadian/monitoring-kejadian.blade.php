@extends('layouts.admin')

@section('content')
<!-- Header Halaman -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">Monitoring Kejadian</h1>
                <small class="text-muted">APAR & Kejadian / Monitoring Kejadian Fire & Rescue</small>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" class="btn btn-danger font-weight-bold shadow-sm rounded-pill px-3" data-toggle="modal" data-target="#modalTambahKejadian">
                    <i class="fas fa-plus mr-1"></i> Tambah Kejadian
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">

        <!-- Ringkasan Kartu Statistik (Info-Box AdminLTE) -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box shadow-sm border-0">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-bullhorn"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Total Kejadian</span>
                        <span class="info-box-number h5 mb-0 font-weight-bold">{{ $totalKejadian ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box shadow-sm border-0">
                    <span class="info-box-icon bg-warning elevation-1 text-white"><i class="fas fa-fire"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Kejadian Kebakaran</span>
                        <span class="info-box-number h5 mb-0 font-weight-bold">{{ $totalKebakaran ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box shadow-sm border-0">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-life-ring"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Giat Rescue</span>
                        <span class="info-box-number h5 mb-0 font-weight-bold">{{ $totalRescue ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box shadow-sm border-0">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Est. Total Kerugian</span>
                        <span class="info-box-number h6 mb-0 font-weight-bold">Rp {{ number_format($totalKerugian ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data Kejadian -->
        <div class="card card-outline card-danger shadow-sm mt-3">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-dark mt-1">Daftar Kejadian Damkar</h3>
                
                <!-- Filter & Search -->
                <div class="card-tools">
                    <form method="GET" action="{{ route('admin.apar.monitoring-kejadian') }}" class="form-inline">
                        <select name="jenis" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <option value="">-- Semua Jenis --</option>
                            <option value="Kebakaran" {{ request('jenis') == 'Kebakaran' ? 'selected' : '' }}>Kebakaran</option>
                            <option value="Rescue" {{ request('jenis') == 'Rescue' ? 'selected' : '' }}>Rescue</option>
                        </select>
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" name="search" class="form-control" placeholder="Cari kode/lokasi..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped text-nowrap align-middle">
                    <thead style="background-color: #f8fafc;">
                        <tr>
                            <th class="px-3" style="width: 50px;">No</th>
                            <th>Kode</th>
                            <th>Waktu Kejadian</th>
                            <th>Jenis</th>
                            <th>Detail Kejadian</th>
                            <th>Lokasi</th>
                            <th>Pos / Regu</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataKejadian as $index => $item)
                            <tr>
                                <td class="px-3">{{ $dataKejadian->firstItem() + $index }}</td>
                                <td><span class="badge badge-light border font-weight-bold">{{ $item->kode_kejadian }}</span></td>
                                <td>{{ $item->waktu_kejadian }}</td>
                                <td>
                                    <span class="badge {{ $item->jenis_kejadian == 'Kebakaran' ? 'badge-danger' : 'badge-warning' }}">
                                        {{ $item->jenis_kejadian }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($item->detail_kejadian, 30) }}</td>
                                <td>{{ $item->lokasi }}</td>
                                <td>{{ $item->pos_regu }}</td>
                                <td><span class="badge badge-success">{{ $item->status ?? 'Selesai' }}</span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-info mr-1" title="Detail"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x d-block mb-3 text-secondary"></i>
                                    <h5>Belum ada data kejadian yang dicatat.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($dataKejadian, 'hasPages') && $dataKejadian->hasPages())
                <div class="card-footer bg-white clearfix py-2">
                    <div class="float-right">
                        {{ $dataKejadian->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</section>

<!-- Modal Form Tambah Kejadian -->
<div class="modal fade" id="modalTambahKejadian" tabindex="-1" role="dialog" aria-labelledby="modalTambahKejadianLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.apar.monitoring-kejadian.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="modalTambahKejadianLabel">
                        <i class="fas fa-plus-circle mr-1"></i> Form Input Kejadian Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Kode Kejadian <span class="text-danger">*</span></label>
                            <input type="text" name="kode_kejadian" class="form-control" value="KEJ-{{ date('Ymd') }}-{{ rand(100, 999) }}" readonly>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Waktu Kejadian <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="waktu_kejadian" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Jenis Kejadian <span class="text-danger">*</span></label>
                            <select name="jenis_kejadian" class="form-control" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="Kebakaran">Kebakaran</option>
                                <option value="Rescue">Rescue / Penyelamatan</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Pos / Regu <span class="text-danger">*</span></label>
                            <input type="text" name="pos_regu" class="form-control" placeholder="Contoh: Pos Mako / Regu A" required>
                        </div>
                        <div class="col-md-12 form-group mb-3">
                            <label class="font-weight-bold">Lokasi Kejadian <span class="text-danger">*</span></label>
                            <input type="text" name="lokasi" class="form-control" placeholder="Alamat lengkap lokasi kejadian" required>
                        </div>
                        <div class="col-md-12 form-group mb-3">
                            <label class="font-weight-bold">Detail Kejadian</label>
                            <textarea name="detail_kejadian" class="form-control" rows="3" placeholder="Keterangan singkat kronologi..."></textarea>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Estimasi Kerugian (Rp)</label>
                            <input type="number" name="estimasi_kerugian" class="form-control" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4 font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection