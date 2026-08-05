<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISAPRAS — Portal Login Dinas Pemadam Kebakaran</title>

    <!-- Google Fonts Plus Jakarta Sans & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Custom Login Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ file_exists(public_path('css/login.css')) ? filemtime(public_path('css/login.css')) : '3' }}">
</head>
<body>

    <!-- Ambient Glowing Background Effect -->
    <div class="bg-ambient">
        <div class="bg-grid-pattern"></div>
        <div class="bg-orb-red"></div>
        <div class="bg-orb-blue"></div>
    </div>

    <!-- Main Glassmorphism Login Card -->
    <div class="login-card">

        <!-- KIRI: HERO BRAND PANEL (Gradien Merah Damkar + Glowing Logo) -->
        <div class="hero-panel">
            <div class="hero-header">
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    <span>Yudha Brama Jaya</span>
                </div>
            </div>

            <div class="hero-center">
                <div class="hero-logo-wrapper">
                    <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Damkar" class="hero-logo">
                </div>
                <h1 class="hero-title-gradient">SISAPRAS</h1>
                <p class="hero-desc">Sistem Informasi Sarana Prasarana Dinas Pemadam Kebakaran & Penyelamatan</p>
            </div>

            <div class="hero-footer-text">
                <i data-lucide="shield-check" style="width: 14px; height: 14px; color: #FFD700;"></i>
                <span>Portal Keamanan Terintegrasi &copy; {{ date('Y') }}</span>
            </div>
        </div>

        <!-- KANAN: FORM PANEL (Formulir Presisi Modern) -->
        <div class="form-panel">
            <div class="form-title-group">
                <h2 class="form-main-title">Selamat Datang</h2>
                <p class="form-sub-title">Masukkan NIP dan Password Anda untuk mengakses sistem.</p>
            </div>

            <!-- ALERT NOTIFIKASI ERROR -->
            @if ($errors->any())
                <div class="alert-error">
                    <ul style="margin: 0; padding-left: 16px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-error" style="text-align: center; font-weight: 600;">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Form Login -->
            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <!-- Input NIP -->
                <div class="field-block">
                    <label for="nip" class="field-label">NIP / ID Anggota</label>
                    <div class="field-box">
                        <input type="text" name="nip" id="nip"
                               class="field-input"
                               placeholder="Masukkan NIP Anda"
                               value="{{ old('nip') }}"
                               required
                               autocomplete="username">
                        <i data-lucide="user" class="field-icon"></i>
                    </div>
                </div>

                <!-- Input Password -->
                <div class="field-block">
                    <label for="password" class="field-label">Password</label>
                    <div class="field-box">
                        <input type="password" name="password" id="password"
                               class="field-input"
                               placeholder="Masukkan Password Anda"
                               required
                               autocomplete="current-password">
                        <i data-lucide="lock" class="field-icon"></i>
                        <button type="button" class="pass-toggle" onclick="togglePasswordVisibility()" title="Tampilkan/Sembunyikan Password">
                            <i data-lucide="eye" id="eyeIcon" style="width: 18px; height: 18px;"></i>
                        </button>
                    </div>
                </div>

                <!-- Tombol Submit Login -->
                <button type="submit" class="btn-submit-fire">
                    <span>MASUK KE PORTAL</span>
                    <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                </button>
            </form>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        lucide.createIcons();

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwdInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        try { localStorage.removeItem('sisapras_open_submenus'); } catch(e) {}
    </script>

</body>
</html>
