<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gunakan Komputer - SINFAS</title>
    <style>
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; padding: 24px; display: grid; place-items: center; background: #f1f5f9; color: #172554; font-family: Inter, system-ui, sans-serif; }
        main { width: min(100%, 440px); padding: 36px; border: 1px solid #dbeafe; border-radius: 18px; background: #fff; text-align: center; box-shadow: 0 18px 50px rgba(30, 64, 175, .1); }
        .mark { width: 54px; height: 54px; margin: 0 auto 20px; display: grid; place-items: center; border-radius: 16px; background: #1e40af; color: white; font-size: 24px; font-weight: 800; }
        h1 { margin: 0; font-size: 1.35rem; }
        p { margin: 12px 0 24px; color: #64748b; line-height: 1.65; }
        a, button { display: inline-flex; min-height: 42px; align-items: center; justify-content: center; padding: 0 16px; border: 0; border-radius: 9px; background: #1e40af; color: #fff; font: inherit; font-weight: 650; text-decoration: none; cursor: pointer; }
        form { display: inline; }
    </style>
</head>
<body>
    <main>
        <div class="mark" aria-hidden="true">S</div>
        <h1>Panel admin tersedia di komputer</h1>
        <p>Tampilan dan pengelolaan admin SINFAS ditujukan untuk layar desktop. Silakan buka kembali aplikasi melalui komputer.</p>
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Keluar dari akun</button>
            </form>
        @else
            <a href="{{ route('login') }}">Kembali ke login</a>
        @endauth
    </main>
</body>
</html>
