<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dinas Pemadam Kebakaran</title>
    <!-- Memanggil Tailwind CSS via CDN untuk kemudahan testing -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #ffffff; /* Warna dasar putih */
            font-family: Arial, sans-serif; /* Gunakan font yang bersih */
        }

        /* Container utama dengan layout split-screen */
        .login-container {
            position: relative;
            width: 100vw;
            height: 100vh;
            display: flex;
            justify-content: space-between;
            overflow: hidden;
        }

        /* Elemen latar belakang aksen biru di pojok kiri atas */
        .bg-accent-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 200px;
            height: 200px;
            background-color: #213B63;
            border-bottom-right-radius: 200px;
            z-index: 0;
        }

        /* Elemen latar belakang aksen biru di pojok kanan bawah */
        .bg-accent-right {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background-color: #213B63;
            border-top-left-radius: 200px;
            z-index: 0;
        }

        /* Bentuk melengkung besar berwarna merah */
        .bg-red-curve {
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background-color: #CC2328;
            border-bottom-right-radius: 100%;
            z-index: 1;
        }

        /* Sisi Kiri (Area Merah dengan Logo) */
        .login-left {
            width: 50%;
            height: 100%;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2; /* Di atas bentuk melengkung */
        }

        .login-logo {
            width: 200px; /* Ukuran logo lebih besar seperti di target */
            height: auto;
        }

        /* Sisi Kanan (Area Putih dengan Form) */
        .login-right {
            width: 50%;
            height: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #ffffff;
            z-index: 2; /* Di atas aksen latar belakang */
        }

        .login-form-container {
            width: 320px; /* Ukuran container form */
            text-align: center;
        }

        .login-title {
            font-size: 40px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
        }

        .login-subtitle {
            font-size: 16px;
            color: #000000;
            margin-bottom: 40px;
        }

        .login-input {
            width: 100%;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px; /* Border radius yang lembut */
            background-color: #E0E3E7; /* Warna latar belakang abu-abu terang */
            border: none;
            font-size: 16px;
            color: #000000;
            outline: none;
        }

        .login-input::placeholder {
            color: #999999;
        }

        .forgot-password {
            font-size: 14px;
            color: #000000;
            margin-top: -10px;
            margin-bottom: 30px;
            display: block;
            text-decoration: none;
        }

        .login-button {
            width: 100%;
            padding: 15px 0;
            background-color: #213B63; /* Warna tombol biru tua */
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            outline: none;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Latar belakang aksen biru -->
        <div class="bg-accent-left"></div>
        <div class="bg-accent-right"></div>
        
        <!-- Bentuk melengkung besar merah -->
        <div class="bg-red-curve"></div>
        <div class="bg-blue-curve"></div>
        <!-- Sisi Kiri (Logo) -->
        <div class="login-left">
            <!-- Pastikan Anda meletakkan file gambar logo di dalam folder public/images/ -->
            <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Yudha Brama Jaya" class="login-logo">
        </div>

        <!-- Sisi Kanan (Form) -->
        <div class="login-right">
            <div class="login-form-container">
                <h1 class="login-title">Welcome</h1>
                <p class="login-subtitle">Please sign in to continue.</p>
                
                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    
                    <div>
                        <input type="email" name="email" id="email" class="login-input" placeholder="example@email.com" required>
                    </div>

                    <div>
                        <input type="password" name="password" id="password" class="login-input" required>
                    </div>

                    <div>
                        <a href="#" class="forgot-password">Forgot your password?</a>
                    </div>

                    <div>
                        <button type="submit" class="login-button">LOG IN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>