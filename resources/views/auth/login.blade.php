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
                 class="w-64 md:w-80 lg:w-96 drop-shadow-2xl transition-transform duration-500 hover:scale-105">
        </div>

        <!-- Sisi Kanan (Area Biru + Form) -->
        <div class="panel-biru">

            <!-- Kotak Form dengan Efek Transparan (Glassmorphism) -->
            <div class="w-full max-w-md p-8 md:p-10 rounded-2xl border-4 border-damkar-red bg-white/5 backdrop-blur-sm shadow-2xl">

                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-white tracking-wide mb-2">SISAPRAS</h1>
                    <p class="text-gray-300 text-sm">Please sign in to continue.</p>
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
                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    <!-- Token CSRF wajib untuk keamanan dari serangan CSRF -->
                    @csrf

                    <!-- Input Username / ID -->
                    <div>
                        <!-- value old('username') mencegah user mengetik ulang ID jika password salah -->
                        <input type="text" name="username" id="username"
                               class="input-field w-full px-5 py-3.5 rounded-xl bg-gray-200 border-2 border-transparent text-gray-900 placeholder-gray-500 font-medium outline-none"
                               placeholder="Username / ID"
                               value="{{ old('username') }}"
                               required
                               autocomplete="username">
                    </div>

                    <!-- Input Password -->
                    <div>
                        <input type="password" name="password" id="password"
                               class="input-field w-full px-5 py-3.5 rounded-xl bg-gray-200 border-2 border-transparent text-gray-900 placeholder-gray-500 font-medium outline-none"
                               placeholder="Password"
                               required
                               autocomplete="current-password">
                    </div>

                    <!-- Tombol Login -->
                    <div class="pt-3">
                        <button type="submit"
                                class="w-full py-3.5 bg-damkar-red hover:bg-damkar-red-hover text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-red-500/40 transition-all duration-300 transform active:scale-95">
                            LOG IN
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</body>
</html>
