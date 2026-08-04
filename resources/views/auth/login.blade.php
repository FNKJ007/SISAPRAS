<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dinas Pemadam Kebakaran</title>

    <!-- Menggunakan Font Inter untuk tampilan lebih modern dan profesional -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Memanggil Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Konfigurasi Custom Warna & Font Tailwind (dipisah ke file sendiri) -->
    <script src="{{ asset('js/tailwind-config.js') }}"></script>

    <!-- CSS khusus halaman login (dipisah dari inline <style>) -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ file_exists(public_path('css/login.css')) ? filemtime(public_path('css/login.css')) : '1' }}">
</head>
<body>

    <div class="login-layout">

        <!-- Sisi Kiri (Area Merah + Logo) -->
        <div class="panel-merah shadow-2xl">
            <!-- Tambahkan efek hover scale pada logo agar interaktif -->
            <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Yudha Brama Jaya"
                 class="w-28 sm:w-40 md:w-64 lg:w-80 xl:w-96 drop-shadow-2xl transition-transform duration-500 hover:scale-105">
        </div>

        <!-- Sisi Kanan (Area Biru + Form) -->
        <div class="panel-biru">

            <!-- Kotak Form dengan Efek Transparan (Glassmorphism) -->
            <div class="w-full max-w-md p-5 sm:p-6 md:p-8 lg:p-10 rounded-2xl border-4 border-damkar-red bg-white/5 backdrop-blur-sm shadow-2xl">

                <div class="text-center mb-4 sm:mb-6 md:mb-8">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white tracking-wide mb-2">SISAPRAS</h1>
                    <p class="text-gray-300 text-xs sm:text-sm">Please sign in to continue.</p>
                </div>

                <!-- NOTIFIKASI ERROR (KEAMANAN UX) -->
                <!-- Menampilkan pesan jika Username/Password salah atau kosong -->
                @if ($errors->any())
                    <div class="bg-red-500/20 border border-red-500 text-red-100 px-4 py-3 rounded-lg mb-6 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-500/20 border border-red-500 text-red-100 px-4 py-3 rounded-lg mb-6 text-sm text-center font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Form Login -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-3 sm:space-y-4 md:space-y-5">
                    <!-- Token CSRF wajib untuk keamanan dari serangan CSRF -->
                    @csrf

                    <!-- Input NIP -->
                    <div>
                        <!-- value old('nip') mencegah user mengetik ulang NIP jika password salah -->
                        <input type="text" name="nip" id="nip"
                               class="input-field w-full px-4 sm:px-5 py-2.5 sm:py-3 md:py-3.5 rounded-xl bg-gray-200 border-2 border-transparent text-gray-900 placeholder-gray-500 font-medium outline-none"
                               placeholder="NIP"
                               value="{{ old('nip') }}"
                               required
                               autocomplete="username">
                    </div>

                    <!-- Input Password -->
                    <div>
                        <input type="password" name="password" id="password"
                               class="input-field w-full px-4 sm:px-5 py-2.5 sm:py-3 md:py-3.5 rounded-xl bg-gray-200 border-2 border-transparent text-gray-900 placeholder-gray-500 font-medium outline-none"
                               placeholder="Password"
                               required
                               autocomplete="current-password">
                    </div>

                    <!-- Tombol Login -->
                    <div class="pt-2 sm:pt-3">
                        <button type="submit"
                                class="w-full py-2.5 sm:py-3 md:py-3.5 bg-damkar-red hover:bg-damkar-red-hover text-white font-bold text-base sm:text-lg rounded-xl shadow-lg hover:shadow-red-500/40 transition-all duration-300 transform active:scale-95">
                            LOG IN
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

{{-- Bersihkan status submenu yang tersimpan agar sidebar selalu mulai tertutup setelah login --}}
<script>
    try { localStorage.removeItem('sisapras_open_submenus'); } catch(e) {}
</script>

</body>
</html>
