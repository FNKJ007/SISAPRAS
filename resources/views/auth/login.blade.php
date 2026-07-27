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
    
    <!-- Konfigurasi Custom Warna & Font Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        damkar: {
                            blue: '#213B63',
                            red: '#B71C1C',
                            'red-hover': '#b01d22'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #0B1F3A; /* Warna dasar biru tua */
            margin: 0;
            overflow-x: hidden; 
        }

        .login-layout {
            display: flex;
            min-height: 100vh;
            width: 100vw;
            position: relative;
        }

        /* Area Merah Kiri dengan Kurva Mulus (Clip-Path) */
        .panel-merah {
            width: 55%; 
            background-color: #B71C1C;
            /* Membuat lengkungan sempurna menggunakan ellipse */
            clip-path: ellipse(100% 120% at 0% 50%);
            display: flex;
            justify-content: center;
            align-items: center;
            position: absolute;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 10;
        }

        /* Area Form Kanan */
        .panel-biru {
            width: 50%;
            margin-left: 50%; /* Menggeser ke kanan */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 20;
            position: relative;
        }

        /* Input Custom Styles */
        .input-field {
            transition: all 0.3s ease;
        }
        .input-field:focus {
            background-color: #ffffff;
            border-color: #B71C1C;
            box-shadow: 0 0 0 4px rgba(204, 35, 40, 0.1);
        }

        /* --- FITUR RESPONSIF (HP/Tablet) --- */
        @media (max-width: 992px) {
            body { overflow-y: auto; }
            .login-layout { flex-direction: column; }
            .panel-merah {
                width: 100%;
                height: 45vh;
                position: relative;
                /* Ubah arah lengkungan ke bawah saat di HP */
                clip-path: ellipse(150% 100% at 50% 0%);
            }
            .panel-biru {
                width: 100%;
                margin-left: 0;
                padding: 40px 20px;
                min-height: 55vh;
            }
        }
    </style>
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