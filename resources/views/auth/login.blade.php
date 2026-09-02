<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRAMA — Berkala Rawat Armada, Alat, dan Sarana DAMKAR</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/fav_icon.jpeg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/fav_icon.jpeg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/fav_icon.jpeg') }}">

    <!-- Google Fonts Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Custom Login Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ file_exists(public_path('css/login.css')) ? filemtime(public_path('css/login.css')) : '5' }}">
</head>
<body>

    <div class="login-container">

        <!-- SISI KIRI: HERO BRAND PANEL (Merah Damkar Gradient) -->
        <div class="login-hero">
            <!-- Header Institusi: Logo Kabupaten Bandung & Logo Damkar -->
            <div class="hero-brand">
                <div class="hero-institutional-logos">
                    <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Kabupaten Bandung" class="hero-inst-logo" onerror="this.style.display='none'">
                    <span class="hero-inst-text">Dinas Pemadam Kebakaran<br>Kabupaten Bandung</span>
                </div>
            </div>

            <!-- Konten Tengah: Logo BRAMA & Deskripsi -->
            <div class="hero-content">
                <img src="{{ asset('images/brama.png') }}" alt="Logo BRAMA" class="hero-brama-logo" onerror="this.style.display='none'">
                <h1 class="hero-title-hidden" style="display:none;">BRAMA</h1>
                <p class="hero-subtitle">Berkala Rawat Armada, Alat, dan Sarana DAMKAR</p>
            </div>

            <!-- Footer Panel -->
            <div class="hero-footer">
                &copy; {{ date('Y') }} Dinas Pemadam Kebakaran dan Penyelamatan.
            </div>
        </div>

        <!-- SISI KANAN: FORM LOGIN CONTAINER -->
        <div class="login-form-wrapper">
            <div class="form-header">
                <h2 class="form-header-title">Selamat Datang</h2>
                <p class="form-header-sub">Masukkan NIP dan Password Anda untuk masuk ke sistem.</p>
            </div>

            <!-- NOTIFIKASI ERROR -->
            @if ($errors->any())
                <div style="background-color: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; padding: 10px 14px; border-radius: 10px; margin-bottom: 16px; font-size: 12.5px;">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div style="background-color: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; padding: 10px 14px; border-radius: 10px; margin-bottom: 16px; font-size: 12.5px; font-weight: 600; text-align: center;">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Form Login -->
            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <!-- Input NIP -->
                <div class="input-group-custom">
                    <label for="nip">NIP (Nomor Induk Pegawai)</label>
                    <div class="input-wrapper">
                        <input type="text" name="nip" id="nip"
                               class="input-custom"
                               placeholder="Masukkan NIP Anda"
                               value="{{ old('nip') }}"
                               required
                               autocomplete="username">
                        <i data-lucide="user" class="input-icon-left"></i>
                    </div>
                </div>

                <!-- Input Password -->
                <div class="input-group-custom">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password"
                               class="input-custom"
                               placeholder="Masukkan Password Anda"
                               required
                               autocomplete="current-password">
                        <i data-lucide="lock" class="input-icon-left"></i>
                        <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility()" aria-label="Tampilkan Password">
                            <i data-lucide="eye" id="eyeIcon" style="width: 18px; height: 18px;"></i>
                        </button>
                    </div>
                </div>

                <!-- Tombol Submit Login -->
                <button type="submit" class="btn-login-submit">
                    <span>Masuk ke Sistem</span>
                    <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                </button>
            </form>

        </div>
    </div>

    <!-- Script Init Lucide Icons & Toggle Password -->
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

        try { 
            sessionStorage.removeItem('API_open_submenus');
            localStorage.removeItem('API_open_submenus');
            localStorage.removeItem('api_open_submenus');
        } catch(e) {}
    </script>

</body>
</html>
