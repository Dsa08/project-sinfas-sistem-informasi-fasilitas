@extends('layouts.landing')

@section('title', 'SINFAS - Aplikasi Peminjaman Barang SMK Negeri 11 Bandung')

@section('content')
<div class="landing-shell">
    <header class="landing-nav">
        <a class="landing-brand" href="{{ route('home') }}" aria-label="SINFAS Beranda">
            <img src="{{ asset('assets/logo-sinfas.png') }}" alt="SINFAS Logo" class="landing-brand-logo">
            <div class="landing-brand-meta">
                <span class="landing-brand-name">SINFAS</span>
                <span class="landing-brand-sub">SMKN 11 Bandung</span>
            </div>
        </a>
        <a class="landing-nav-login" href="{{ route('login') }}">Masuk</a>
    </header>

    <main>
        <section class="landing-hero">
            <div class="landing-hero-copy">
                <div class="landing-badge-school">
                    <span class="landing-badge-dot"></span>
                    Khusus Warga SMK Negeri 11 Bandung
                </div>
                <h1>Aplikasi Peminjaman Barang <span class="landing-highlight">SMK Negeri 11 Bandung</span></h1>
                <p>Lihat ketersediaan barang secara online, ajukan peminjaman sarana prasarana sekolah, lalu pantau prosesnya secara realtime tanpa perlu datang hanya untuk mengecek ketersediaan stok.</p>
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
                <span class="landing-card-label">Alur Sarpras SMKN 11</span>
                <strong>Ajukan secara online</strong>
                <p>Staf sarana prasarana akan meninjau ketersediaan barang dan memproses pengajuan Anda.</p>
                <div class="landing-card-status"><span></span> Status dapat dipantau langsung di akun Anda</div>
            </div>
        </section>

        <section class="landing-guide" aria-labelledby="landing-guide-title">
            <div class="landing-section-heading">
                <span class="landing-eyebrow">Panduan pengguna</span>
                <h2 id="landing-guide-title">Tiga langkah peminjaman</h2>
                <p>Ikuti alur berikut untuk meminjam sarana prasarana di SMK Negeri 11 Bandung.</p>
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
                    <p>Staf sarana meninjau ketersediaan barang dan memproses permohonan pengajuan Anda secara online.</p>
                </article>
                <article class="landing-step">
                    <span class="landing-step-number">03</span>
                    <h3>Ambil dan kembalikan</h3>
                    <p>Setelah disetujui, ambil barang di ruang Sarpras SMKN 11 Bandung. Ajukan pengembalian setelah selesai digunakan.</p>
                </article>
            </div>
        </section>

        <section class="landing-help">
            <div>
                <strong>Perlu bantuan?</strong>
                <p>Kunjungi Ruang Sarana Prasarana (Sarpras) SMK Negeri 11 Bandung atau hubungi petugas piket fasilitas sekolah.</p>
            </div>
            <a href="{{ route('login') }}">Masuk ke SINFAS <span aria-hidden="true">&#8594;</span></a>
        </section>
    </main>

    <footer class="landing-footer">
        <span class="landing-footer-brand"><strong>SINFAS</strong> &mdash; Sistem Informasi Fasilitas</span>
        <span class="landing-footer-sub">Aplikasi Peminjaman Barang SMK Negeri 11 Bandung</span>
    </footer>
</div>
@endsection
