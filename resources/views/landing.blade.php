@extends('layouts.landing')

@section('title', 'SINFAS - Sistem Informasi Fasilitas & Sarana Prasarana SMK Negeri 11 Bandung')

@section('content')
<div class="landing-page">
    {{-- ========================================================
         HERO SECTION WITH COVER IMAGE & GLASSMORPHIC NAVBAR
         ======================================================== --}}
    <section class="landing-hero-cover">
        {{-- Cover Background Image with Ambient Gradient Overlay --}}
        <div class="landing-cover-bg" aria-hidden="true">
            <img src="{{ asset('assets/pictures/bg_login_register.jpg') }}" alt="Gedung SMK Negeri 11 Bandung" class="landing-cover-img">
            <div class="landing-cover-overlay"></div>
        </div>

        {{-- Top Sticky/Fixed Header --}}
        <header class="landing-header">
            <div class="landing-container landing-header-inner">
                <a class="landing-brand" href="{{ route('home') }}" aria-label="SINFAS Beranda">
                    <img src="{{ asset('assets/pictures/logo_smkn11.png') }}" alt="Logo SMKN 11 Bandung" class="landing-brand-logo landing-brand-logo--school">
                    <span class="landing-brand-divider" aria-hidden="true"></span>
                    <img src="{{ asset('assets/logo-sinfas.png') }}" alt="Logo SINFAS" class="landing-brand-logo">
                    <div class="landing-brand-meta">
                        <span class="landing-brand-name">SINFAS</span>
                        <span class="landing-brand-sub">SMKN 11 BANDUNG</span>
                    </div>
                </a>

                <nav class="landing-nav-links">
                    <a href="#katalog" class="landing-nav-link">Katalog Sarana</a>
                    <a href="#panduan" class="landing-nav-link">Alur Peminjaman</a>
                    <a href="#layanan" class="landing-nav-link">Layanan Sarpras</a>
                </nav>

                <div class="landing-header-actions">
                    <a class="btn-landing-login" href="{{ route('login') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        <span>Masuk ke Akun</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- Hero Text, Prominent Search Bar, and Quick Filter Chips --}}
        <div class="landing-container landing-hero-main">
            <div class="landing-hero-badge">
                <span class="landing-badge-dot"></span>
                <span>Portal Resmi Sarana &amp; Prasarana SMK Negeri 11 Bandung</span>
            </div>

            <h1 class="landing-hero-title">
                Pinjam Fasilitas &amp; Alat Sekolah <br>
                <span class="landing-title-highlight">Lebih Cepat, Praktis, &amp; Transparan</span>
            </h1>

            <p class="landing-hero-desc">
                Cek ketersediaan proyektor, kamera, sound system, lab, dan peralatan sekolah secara realtime tanpa perlu datang hanya untuk mengecek ketersediaan fisik.
            </p>

            {{-- Dual CTA Buttons (Aksi Mulai Pinjam & Jelajahi Katalog) --}}
            <div class="landing-hero-actions">
                <a href="{{ route('login') }}" class="btn-hero-primary">
                    <span>Mulai Pinjam Sarana</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>

                <a href="#katalog" class="btn-hero-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                    <span>Lihat Katalog Sarana</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </a>
            </div>

            {{-- Indikator Gulir ke Bawah --}}
            <div class="landing-scroll-cue">
                <a href="#katalog" class="landing-scroll-link" aria-label="Gulir ke bawah untuk melihat katalog sarana">
                    <span class="landing-scroll-mouse">
                        <span class="landing-scroll-wheel"></span>
                    </span>
                    <span class="landing-scroll-text">Gulir ke bawah untuk eksplorasi</span>
                </a>
            </div>
        </div>
    </section>

    {{-- ========================================================
         OVERLAPPING SECTION: KATALOG SARANA SIAP DIPINJAM
         ======================================================== --}}
    <section class="landing-overlap-section" id="katalog">
        <div class="landing-container">
            <div class="landing-section-card">
                <div class="landing-section-header">
                    <div>
                        <span class="landing-section-eyebrow">INVENTARIS SEKOLAH</span>
                        <h2 class="landing-section-title">Sarana &amp; Fasilitas Siap Dipinjam</h2>
                        <p class="landing-section-subtitle">Peralatan dalam kondisi prima yang siap diajukan untuk kebutuhan pembelajaran dan kegiatan praktik.</p>
                    </div>
                    <a href="{{ route('login') }}" class="landing-link-more">
                        <span>Lihat Semua Sarana</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>

                {{-- Grid Kartu Sarana --}}
                <div class="landing-items-grid">
                    @if(isset($featuredItems) && $featuredItems->isNotEmpty())
                        @foreach($featuredItems as $item)
                            <article class="landing-item-card">
                                <div class="landing-card-thumb-wrap">
                                    <img 
                                        src="{{ $item->foto_url ?? asset('assets/pictures/projector_sample.jpg') }}" 
                                        alt="{{ $item->nama_barang }}" 
                                        class="landing-card-thumb"
                                        onerror="this.src='{{ asset('assets/pictures/projector_sample.jpg') }}';"
                                    >
                                    <span class="landing-card-badge-cat">
                                        {{ $item->kategori->nama_kategori ?? 'Umum' }}
                                    </span>
                                </div>
                                <div class="landing-card-body">
                                    <div class="landing-card-stock-pill">
                                        <span class="landing-stock-indicator"></span>
                                        <span>{{ $item->jumlah_baik }} Unit Tersedia</span>
                                    </div>
                                    <h3 class="landing-card-title">{{ $item->nama_barang }}</h3>
                                    <p class="landing-card-desc">{{ $item->merk_model ?? 'Standar Fasilitas SMKN 11' }}</p>
                                    <div class="landing-card-footer">
                                        <a href="{{ route('login') }}" class="btn-card-borrow">
                                            <span>Ajukan Pinjam</span>
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    @else
                        {{-- Fallback Mockup Data --}}
                        @php
                            $mockItems = [
                                ['nama' => 'Proyektor Epson EB-E500', 'kategori' => 'Proyektor', 'stok' => '5 Unit Tersedia', 'desc' => '3300 Lumens XGA, Port HDMI & VGA'],
                                ['nama' => 'Kamera Sony Alpha A6400', 'kategori' => 'Multimedia', 'stok' => '3 Unit Tersedia', 'desc' => 'Lensa Kit 16-50mm, 4K Video, Baterai Cadangan'],
                                ['nama' => 'Kabel Roll Terminal 15m', 'kategori' => 'Kabel & Daya', 'stok' => '8 Unit Tersedia', 'desc' => '4 Lubang Stop Kontak dengan Pengaman Sekring'],
                                ['nama' => 'Speaker Portable Wireless', 'kategori' => 'Audio System', 'stok' => '4 Unit Tersedia', 'desc' => 'Include 2 Mic Wireless, Bluetooth & Aux Input'],
                                ['nama' => 'Pointer Laser Presenter', 'kategori' => 'Aksesoris', 'stok' => '6 Unit Tersedia', 'desc' => 'Wireless 2.4GHz dengan Dongle USB'],
                                ['nama' => 'Kabel HDMI High Speed 10m', 'kategori' => 'Kabel & Daya', 'stok' => '10 Unit Tersedia', 'desc' => 'Support Full HD 1080p & 4K Output'],
                            ];
                        @endphp
                        @foreach($mockItems as $mock)
                            <article class="landing-item-card">
                                <div class="landing-card-thumb-wrap">
                                    <img src="{{ asset('assets/pictures/projector_sample.jpg') }}" alt="{{ $mock['nama'] }}" class="landing-card-thumb">
                                    <span class="landing-card-badge-cat">{{ $mock['kategori'] }}</span>
                                </div>
                                <div class="landing-card-body">
                                    <div class="landing-card-stock-pill">
                                        <span class="landing-stock-indicator"></span>
                                        <span>{{ $mock['stok'] }}</span>
                                    </div>
                                    <h3 class="landing-card-title">{{ $mock['nama'] }}</h3>
                                    <p class="landing-card-desc">{{ $mock['desc'] }}</p>
                                    <div class="landing-card-footer">
                                        <a href="{{ route('login') }}" class="btn-card-borrow">
                                            <span>Ajukan Pinjam</span>
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================
         ALUR PEMINJAMAN 3 LANGKAH
         ======================================================== --}}
    <section class="landing-guide-section" id="panduan">
        <div class="landing-container">
            <div class="landing-section-intro text-center">
                <span class="landing-section-eyebrow">PANDUAN PENGGUNA</span>
                <h2 class="landing-section-title">3 Langkah Mudah Peminjaman Sarana</h2>
                <p class="landing-section-subtitle">Alur pengajuan yang transparan dan dapat dipantau langsung dari gawai Anda.</p>
            </div>

            <div class="landing-steps-grid">
                <div class="landing-step-card">
                    <div class="landing-step-header">
                        <span class="landing-step-num">01</span>
                        <div class="landing-step-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                    </div>
                    <h3 class="landing-step-title">Pilih &amp; Cek Ketersediaan</h3>
                    <p class="landing-step-desc">
                        Cari alat yang dibutuhkan pada katalog digital, cek jumlah unit yang masih tersedia pada tanggal yang Anda inginkan.
                    </p>
                </div>

                <div class="landing-step-card">
                    <div class="landing-step-header">
                        <span class="landing-step-num">02</span>
                        <div class="landing-step-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        </div>
                    </div>
                    <h3 class="landing-step-title">Ajukan Permohonan Online</h3>
                    <p class="landing-step-desc">
                        Masuk dengan akun siswa atau pegawai, tentukan durasi dan lokasi penggunaan, lalu tunggu verifikasi dari Admin Sarana.
                    </p>
                </div>

                <div class="landing-step-card">
                    <div class="landing-step-header">
                        <span class="landing-step-num">03</span>
                        <div class="landing-step-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                    </div>
                    <h3 class="landing-step-title">Ambil &amp; Kembalikan</h3>
                    <p class="landing-step-desc">
                        Setelah disetujui, ambil barang fisik di Ruang Sarpras SMKN 11 Bandung. Ajukan pengembalian tepat waktu setelah selesai.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================
         INFORMASI LAYANAN & LOKASI RUANG SARPRAS
         ======================================================== --}}
    <section class="landing-info-section" id="layanan">
        <div class="landing-container">
            <div class="landing-info-card">
                <div class="landing-info-item">
                    <div class="landing-info-icon-wrap">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <h4 class="landing-info-heading">Jam Pelayanan Sarpras</h4>
                        <p class="landing-info-text">Senin &ndash; Jumat: 07.00 &ndash; 16.00 WIB<br><small style="color: #64748b;">Istirahat: 12.00 &ndash; 13.00 WIB</small></p>
                    </div>
                </div>

                <div class="landing-info-divider"></div>

                <div class="landing-info-item">
                    <div class="landing-info-icon-wrap">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <h4 class="landing-info-heading">Lokasi Ruang Sarana</h4>
                        <p class="landing-info-text">Gedung Utama Lantai 1<br><small style="color: #64748b;">SMK Negeri 11 Bandung</small></p>
                    </div>
                </div>

                <div class="landing-info-divider"></div>

                <div class="landing-info-item">
                    <div class="landing-info-icon-wrap">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <h4 class="landing-info-heading">Ketentuan Peminjam</h4>
                        <p class="landing-info-text">Wajib menjaga keutuhan &amp; kebersihan sarana<br><small style="color: #64748b;">Bertanggung jawab atas fasilitas yang dipinjam</small></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================
         FOOTER NATURAL & PROFESIONAL
         ======================================================== --}}
    <footer class="landing-footer">
        <div class="landing-container">
            <div class="landing-footer-grid">
                {{-- Kolom 1: Profil & Identitas --}}
                <div class="landing-footer-col landing-footer-col--main">
                    <div class="landing-footer-brand">
                        <img src="{{ asset('assets/logo-sinfas.png') }}" alt="SINFAS Logo" class="landing-footer-logo">
                        <span class="landing-footer-brand-title">SINFAS</span>
                    </div>
                    <p class="landing-footer-desc">
                        Sistem Informasi Fasilitas (SINFAS) adalah platform resmi manajemen dan sirkulasi peminjaman sarana prasarana pembelajaran di lingkungan SMK Negeri 11 Bandung.
                    </p>
                </div>

                {{-- Kolom 2: Navigasi Cepat --}}
                <div class="landing-footer-col">
                    <h4 class="landing-footer-heading">Navigasi</h4>
                    <ul class="landing-footer-links">
                        <li><a href="#katalog">Katalog Sarana</a></li>
                        <li><a href="#panduan">Alur Peminjaman</a></li>
                        <li><a href="#layanan">Layanan Sarpras</a></li>
                        <li><a href="{{ route('login') }}">Masuk ke Sistem</a></li>
                    </ul>
                </div>

                {{-- Kolom 3: Kontak & Alamat Sekolah --}}
                <div class="landing-footer-col">
                    <h4 class="landing-footer-heading">Kontak &amp; Lokasi</h4>
                    <p class="landing-footer-contact">
                        <strong>SMK Negeri 11 Bandung</strong><br>
                        Jl. Budhi, Cilember, Kec. Cicendo<br>
                        Kota Bandung, Jawa Barat 40175<br>
                        <span style="color: #94a3b8; font-size: 0.85rem;">Email: sarpras@smkn11bdg.sch.id</span>
                    </p>
                </div>
            </div>

            {{-- Bottom Bar: Copyright Resmi --}}
            <div class="landing-footer-bottom">
                <p>&copy; {{ date('Y') }} SINFAS &mdash; Sistem Informasi Fasilitas SMK Negeri 11 Bandung. Hak Cipta Dilindungi Undang-Undang.</p>
            </div>
        </div>
    </footer>
</div>
@endsection
