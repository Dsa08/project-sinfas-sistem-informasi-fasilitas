@extends('layouts.landing')

@section('title', 'SINFAS - Peminjaman Sarana Sekolah')

@section('content')
<div class="landing-shell">
    <header class="landing-nav">
        <a class="landing-brand" href="{{ route('home') }}" aria-label="SINFAS Beranda">
            <span class="landing-brand-mark">S</span>
            <span>SINFAS</span>
        </a>
        <a class="landing-nav-login" href="{{ route('login') }}">Masuk</a>
    </header>

    <main>
        <section class="landing-hero">
            <div class="landing-hero-copy">
                <span class="landing-eyebrow">Sistem Informasi Fasilitas</span>
                <h1>Peminjaman sarana sekolah, lebih mudah dan terpantau.</h1>
                <p>Lihat ketersediaan barang secara online, ajukan peminjaman, lalu pantau prosesnya tanpa perlu datang hanya untuk mengecek stok.</p>
                <a class="landing-primary-button" href="{{ route('login') }}">
                    Masuk untuk mulai
                    <span aria-hidden="true">&#8594;</span>
                </a>
                <p class="landing-login-hint">Gunakan NIS, NIP, atau username dan password akun Anda.</p>
            </div>
            <div class="landing-hero-card" aria-label="Ringkasan alur peminjaman">
                <div class="landing-card-icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none"><rect x="7" y="10" width="34" height="29" rx="5" stroke="currentColor" stroke-width="2.5"/><path d="M16 10V7m16 3V7M7 19h34M17 27h6m-6 6h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                </div>
                <span class="landing-card-label">Alur peminjaman</span>
                <strong>Ajukan secara online</strong>
                <p>Staf sarana akan meninjau ketersediaan dan memproses pengajuan Anda.</p>
                <div class="landing-card-status"><span></span> Status dapat dipantau di akun Anda</div>
            </div>
        </section>

        <section class="landing-guide" aria-labelledby="landing-guide-title">
            <div class="landing-section-heading">
                <span class="landing-eyebrow">Panduan pengguna</span>
                <h2 id="landing-guide-title">Tiga langkah peminjaman</h2>
                <p>Ikuti alur berikut untuk menggunakan sarana sekolah.</p>
            </div>
            <div class="landing-steps">
                <article class="landing-step">
                    <span class="landing-step-number">01</span>
                    <h3>Pilih dan ajukan</h3>
                    <p>Cari barang yang dibutuhkan, cek stok yang tersedia, lalu isi tanggal, lokasi, dan keperluan penggunaan.</p>
                </article>
                <article class="landing-step">
                    <span class="landing-step-number">02</span>
                    <h3>Tunggu verifikasi</h3>
                    <p>Staf sarana meninjau ketersediaan barang dan memproses pengajuan Anda secara online.</p>
                </article>
                <article class="landing-step">
                    <span class="landing-step-number">03</span>
                    <h3>Ambil dan kembalikan</h3>
                    <p>Setelah disetujui, ambil barang di ruang Sarpras. Ajukan pengembalian setelah selesai digunakan.</p>
                </article>
            </div>
        </section>

        <section class="landing-help">
            <div>
                <strong>Perlu bantuan?</strong>
                <p>Kunjungi Ruang Sarana Prasarana (Sarpras) Gedung A Lt. 1 atau hubungi petugas piket fasilitas sekolah.</p>
            </div>
            <a href="{{ route('login') }}">Masuk ke SINFAS <span aria-hidden="true">&#8594;</span></a>
        </section>
    </main>

    <footer class="landing-footer">SINFAS <span>Sistem Informasi Fasilitas Sekolah</span></footer>
</div>
@endsection
