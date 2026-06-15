<!DOCTYPE html>
<html lang="id" class="h-full bg-se-soft">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ASISTEN SE2026</title>
    <meta name="theme-color" content="#f68b24">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="ASISTEN SE2026">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="ASISTEN SE2026">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="msapplication-TileColor" content="#f68b24">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/asisten-se2026-icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/asisten-se2026-icon-512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/asisten-se2026-icon-180.png') }}">
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', 'Inter', sans-serif; }

        .login-shell {
            background: rgba(180, 83, 9, 0.05);
        }

        .login-shell::before,
        .login-shell::after {
            content: "";
            position: fixed;
            z-index: 0;
            pointer-events: none;
            border-radius: 9999px;
            filter: blur(56px);
        }

        .login-shell::before {
            top: -10%;
            right: -10%;
            width: 50vw;
            height: 50vh;
            background: linear-gradient(135deg, rgba(255, 237, 213, 0.72), rgba(255, 255, 255, 0));
            animation: login-pulse 8s ease-in-out infinite;
        }

        .login-shell::after {
            top: 20%;
            left: -10%;
            width: 40vw;
            height: 40vh;
            background: linear-gradient(45deg, rgba(254, 249, 195, 0.58), rgba(255, 255, 255, 0));
        }

        .login-watermark {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .login-watermark img {
            position: absolute;
            width: min(48rem, 55vw);
            height: auto;
            user-select: none;
        }

        .login-watermark__top {
            top: -12%;
            right: -19%;
            opacity: 0.07;
            transform: rotate(15deg);
        }

        .login-watermark__bottom {
            bottom: -10%;
            left: -10%;
            opacity: 0.085;
            transform: rotate(-10deg);
        }

        .login-watermark__center {
            top: 19%;
            left: 18%;
            width: min(16rem, 26vw) !important;
            opacity: 0.065;
            filter: blur(1px);
            transform: rotate(45deg);
        }

        @media (max-width: 639px) {
            .login-watermark img {
                width: 24rem;
            }

            .login-watermark__top {
                top: -1.5rem;
                right: -11.25rem;
                opacity: 0.14;
            }

            .login-watermark__bottom {
                bottom: -2.75rem;
                left: -10.75rem;
                opacity: 0.16;
            }

            .login-watermark__center {
                top: 11.5rem;
                left: -8.75rem;
                width: 17rem !important;
                opacity: 0.06;
            }
        }

        @keyframes login-pulse {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.64; }
        }
    </style>
</head>
<body class="login-shell min-h-full text-se-ink">
    <div class="login-watermark" aria-hidden="true">
        <img src="{{ asset('images/logo-se2026-ribbon-only-v3.png') }}" alt="" class="login-watermark__top">
        <img src="{{ asset('images/logo-se2026-ribbon-only-v3.png') }}" alt="" class="login-watermark__bottom">
        <img src="{{ asset('images/logo-se2026-ribbon-only-v3.png') }}" alt="" class="login-watermark__center">
    </div>
    <main class="relative z-10 flex min-h-screen flex-col items-center justify-center px-5 py-6 sm:px-6 lg:py-10">
        <div class="grid w-full max-w-[21rem] overflow-hidden rounded-[2rem] bg-white/95 shadow-2xl shadow-slate-950/10 ring-1 ring-slate-200/80 backdrop-blur sm:max-w-md lg:max-w-4xl lg:grid-cols-[1fr_0.92fr] lg:rounded-3xl">
            <section class="relative hidden min-h-[32rem] overflow-hidden border-r border-slate-200 bg-gradient-to-br from-slate-50 via-white to-orange-50/40 p-10 lg:flex lg:flex-col lg:justify-start">
                <div class="absolute -left-16 top-10 h-44 w-44 rounded-full border border-orange-200/60"></div>
                <div class="absolute -right-12 bottom-10 h-36 w-36 rounded-full bg-slate-100/80"></div>
                <div class="absolute right-10 top-10 grid grid-cols-5 gap-2 opacity-30">
                    @for($i = 0; $i < 20; $i++)
                        <span class="h-1.5 w-1.5 rounded-full bg-orange-300"></span>
                    @endfor
                </div>

                <div class="relative lg:pt-2">
                    <div class="flex w-fit items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm shadow-slate-950/5">
                        <img src="{{ asset('images/logo-bps.svg') }}" alt="Logo BPS" class="h-11 w-11 object-contain">
                        <div class="h-10 w-px bg-slate-200"></div>
                        <img src="{{ asset('images/logo-se2026-ribbon-only-v3.png') }}" alt="Logo SE2026" class="h-12 w-14 object-contain">
                    </div>

                    <div class="mt-7 max-w-md">
                        <p class="text-sm font-medium uppercase tracking-wide text-se-rust">Sensus Ekonomi 2026</p>
                        <h1 class="mt-3 text-4xl font-semibold tracking-tight text-se-ink">ASISTEN SE2026</h1>
                        <p class="mt-4 text-base leading-7 text-slate-600">Aplikasi Simpan, Informasi progres, dan Tanya Kondefnya Sensus Ekonomi 2026</p>
                    </div>

                </div>
            </section>

            <section class="p-6 sm:p-9 lg:flex lg:items-stretch lg:p-10">
                <div class="mx-auto flex w-full max-w-md flex-col lg:min-h-[26rem]">
                    <div class="text-center lg:hidden">
                        <div class="mx-auto flex w-fit items-center gap-4 px-2 py-1">
                            <img src="{{ asset('images/logo-bps.svg') }}" alt="Logo BPS" class="h-12 w-12 object-contain">
                            <div class="h-10 w-px bg-slate-300"></div>
                            <img src="{{ asset('images/logo-se2026-ribbon-only-v3.png') }}" alt="Logo SE2026" class="h-12 w-14 object-contain">
                        </div>
                        <h1 class="mt-7 text-[1.8rem] font-semibold leading-tight tracking-wide text-slate-900">ASISTEN SE2026</h1>
                        <p class="mx-auto mt-3 max-w-xs text-[0.95rem] font-normal leading-6 text-slate-500">Aplikasi Simpan, Informasi progres, dan Tanya Kondefnya Sensus Ekonomi 2026</p>
                    </div>

                    <div class="mt-8 lg:mt-[5.25rem]">
                        @if(session('error'))
                            <div id="login-error-toast" class="fixed right-4 top-4 z-50 flex max-w-sm items-start gap-3.5 rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-xl shadow-rose-950/10 transition-all duration-300 animate-slide-in">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <h3 class="text-sm font-semibold text-rose-900 leading-none">Login Gagal</h3>
                                    <p class="mt-1.5 text-xs text-rose-700 leading-normal">{{ session('error') }}</p>
                                </div>
                                <button type="button" onclick="const toast = document.getElementById('login-error-toast'); toast.classList.add('opacity-0', 'translate-y-[-10px]'); setTimeout(() => toast.remove(), 300);" class="shrink-0 rounded-lg p-1 text-rose-400 hover:bg-rose-100 hover:text-rose-600 transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <style>
                                @keyframes slideIn {
                                    from { transform: translateY(-20px); opacity: 0; }
                                    to { transform: translateY(0); opacity: 1; }
                                }
                                .animate-slide-in {
                                    animation: slideIn 0.3s ease-out forwards;
                                }
                            </style>
                            <script>
                                setTimeout(() => {
                                    const toast = document.getElementById('login-error-toast');
                                    if (toast) {
                                        toast.classList.add('opacity-0', 'translate-y-[-10px]');
                                        setTimeout(() => toast.remove(), 300);
                                    }
                                }, 8000);
                            </script>
                        @endif

                        <div class="mb-5 flex items-center gap-4 text-center lg:justify-start">
                            <div class="h-px flex-1 bg-slate-200"></div>
                            <p class="text-sm font-medium uppercase tracking-wide text-se-primary">Login</p>
                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>

                        <a href="{{ route('auth.google', (session('force_google_consent') || request()->boolean('consent')) ? ['consent' => 1] : []) }}"
                           class="group flex min-h-[3rem] w-full items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-slate-950/8 transition-all duration-200 hover:border-amber-300 hover:bg-amber-50 hover:text-se-primary hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-amber-500/20 lg:text-sm">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span>Masuk dengan Google</span>
                        </a>

                        <div id="install-app-block">
                            <div class="my-5 flex items-center gap-4 text-center">
                                <div class="h-px flex-1 bg-slate-200"></div>
                                <p class="text-sm font-normal text-slate-500">atau</p>
                                <div class="h-px flex-1 bg-slate-200"></div>
                            </div>

                            <button id="install-app-button" type="button" class="group flex min-h-[3rem] w-full items-center justify-center gap-3 rounded-2xl border border-se-primary bg-white px-4 py-3 text-sm font-semibold text-se-primary transition hover:bg-amber-50/70 focus:outline-none focus:ring-4 focus:ring-amber-500/10 lg:text-sm">
                                <svg class="h-4 w-4 transition group-hover:text-se-rust" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M5 19h14"></path>
                                </svg>
                                <span id="install-app-label">Instal Aplikasi</span>
                            </button>
                            <p id="install-app-help" class="mt-2 hidden text-center text-xs leading-5 text-slate-500">Jika prompt instal belum muncul, buka menu browser lalu pilih Tambahkan ke layar utama.</p>
                        </div>

                        <div id="installed-app-note" class="mt-5 hidden items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-center text-xs font-medium text-emerald-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Aplikasi sudah berjalan dalam mode instal.</span>
                        </div>

                        <noscript>
                            <div class="my-5 flex items-center gap-4 text-center">
                                <div class="h-px flex-1 bg-slate-200"></div>
                                <p class="text-sm font-normal text-slate-500">atau</p>
                                <div class="h-px flex-1 bg-slate-200"></div>
                            </div>
                            <a href="{{ asset('manifest.webmanifest') }}" class="flex min-h-[3rem] w-full items-center justify-center gap-3 rounded-2xl border border-se-primary bg-white px-4 py-3 text-sm font-semibold text-se-primary">
                                Instal Aplikasi
                            </a>
                        </noscript>

                    </div>

                    <div class="mt-7 h-px bg-slate-200"></div>
                    <p class="mt-5 text-center text-xs font-normal leading-5 text-slate-500 lg:mt-auto lg:pt-16">
                        &copy; {{ date('Y') }} ASISTEN SE2026. Hak Cipta Dilindungi.
                    </p>
                </div>
            </section>
        </div>
    </main>
    <script>
        let deferredInstallPrompt = null;
        const installBlock = document.getElementById('install-app-block');
        const installButton = document.getElementById('install-app-button');
        const installLabel = document.getElementById('install-app-label');
        const installHelp = document.getElementById('install-app-help');
        const installedNote = document.getElementById('installed-app-note');
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        const isIOS = /iphone|ipad|ipod/i.test(window.navigator.userAgent);

        const showInstallBlock = () => {
            installBlock.classList.remove('hidden');
        };

        const hideInstallBlock = () => {
            installBlock.classList.add('hidden');
        };

        const runInstallFlow = async () => {
            if (isIOS) {
                installHelp.classList.remove('hidden');
                showInstallBlock();
                return;
            }

            if (!deferredInstallPrompt) {
                installHelp.classList.remove('hidden');
                showInstallBlock();
                return;
            }

            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
        };

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        if (isStandalone) {
            hideInstallBlock();
            installedNote.classList.remove('hidden');
            installedNote.classList.add('flex');
        } else if (isIOS) {
            showInstallBlock();
            installLabel.textContent = 'Cara Instal di iPhone';
            installHelp.textContent = 'Buka tombol Bagikan di Safari, lalu pilih Tambahkan ke Layar Utama.';
        } else {
            showInstallBlock();
        }

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredInstallPrompt = event;
            installHelp.classList.add('hidden');
            installLabel.textContent = 'Instal Aplikasi';
            showInstallBlock();
        });

        installButton.addEventListener('click', async () => {
            await runInstallFlow();
        });

        window.addEventListener('appinstalled', () => {
            hideInstallBlock();
            installHelp.classList.add('hidden');
            installedNote.classList.remove('hidden');
            installedNote.classList.add('flex');
        });
    </script>
</body>
</html>
