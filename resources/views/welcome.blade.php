<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AudioQuest — AI Music Discovery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=Space+Mono&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Bebas Neue', 'sans-serif'],
                        body: ['DM Sans', 'sans-serif'],
                        mono: ['Space Mono', 'monospace'],
                    },
                    colors: { teal: '#00d4b8', ink: '#060c10' },
                    keyframes: {
                        'fade-up': {
                            '0%':   { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        'waveform': {
                            '0%, 100%': { transform: 'scaleY(0.4)' },
                            '50%':      { transform: 'scaleY(1)' },
                        },
                    },
                    animation: {
                        'fade-up':  'fade-up 0.9s ease forwards',
                        'waveform': 'waveform 1.2s ease-in-out infinite',
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #060c10; }
        .mesh-bg {
            background:
                radial-gradient(ellipse 55% 60% at 12% 55%, rgba(0,110,120,0.45) 0%, transparent 60%),
                radial-gradient(ellipse 45% 50% at 88% 35%, rgba(0,70,110,0.35) 0%, transparent 60%),
                radial-gradient(ellipse 35% 40% at 55% 80%, rgba(0,90,100,0.20) 0%, transparent 55%),
                #060c10;
        }
        .grain::after {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none; z-index: 999;
        }
        .stroke-text { color: transparent; -webkit-text-stroke: 2px rgba(255,255,255,0.35); }
        .bar-1 { animation-delay: 0.00s; } .bar-2 { animation-delay: 0.15s; } .bar-3 { animation-delay: 0.30s; } .bar-4 { animation-delay: 0.45s; } .bar-5 { animation-delay: 0.60s; }
        .d1 { animation-delay: 0.1s; opacity: 0; } .d2 { animation-delay: 0.3s; opacity: 0; } .d3 { animation-delay: 0.5s; opacity: 0; } .d4 { animation-delay: 0.7s; opacity: 0; }
        .glass { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); backdrop-filter: blur(12px); transition: all 0.3s; }
        .glass:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.13); transform: translateY(-4px); }
        .step-num { font-family: 'Bebas Neue', sans-serif; font-size: 5rem; line-height: 1; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,0.08); }
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-track { background: #060c10; } ::-webkit-scrollbar-thumb { background: #00d4b8; border-radius: 2px; }
    </style>
</head>
<body class="mesh-bg grain text-white overflow-x-hidden">

<nav class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-8 py-5"
     style="background: rgba(6,12,16,0.75); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,0.06);">
    <div class="flex items-center gap-3">
        <div class="flex items-end gap-[3px] h-5">
            <div class="w-[3px] bg-teal rounded-full animate-waveform bar-1" style="height:40%"></div>
            <div class="w-[3px] bg-teal rounded-full animate-waveform bar-2" style="height:70%"></div>
            <div class="w-[3px] bg-teal rounded-full animate-waveform bar-3" style="height:100%"></div>
            <div class="w-[3px] bg-teal rounded-full animate-waveform bar-4" style="height:60%"></div>
            <div class="w-[3px] bg-teal rounded-full animate-waveform bar-5" style="height:35%"></div>
        </div>
        <span style="font-family:'Bebas Neue',sans-serif; letter-spacing:0.15em; font-size:1.25rem;">AudioQuest</span>
    </div>

    <div class="flex items-center gap-4">
        @auth
            <span class="text-white/45 text-sm font-mono">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-white/65 hover:text-white transition-colors px-4 py-2 rounded-full border border-white/10">
                    Log out
                </button>
            </form>
        @else
            <a href="/login" class="text-sm text-white/65 hover:text-white transition-colors px-4 py-2">Log in</a>
            <a href="/signup" class="text-sm font-medium text-ink bg-white px-5 py-2 rounded-full hover:bg-white/90 transition-all">Sign up</a>
        @endauth
    </div>
</nav>

<section class="relative min-h-screen flex flex-col items-center justify-center text-center px-6 pt-24 pb-32">
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full pointer-events-none"
         style="background: radial-gradient(circle, rgba(0,140,130,0.1) 0%, transparent 65%); filter: blur(70px);"></div>

    @auth
        <div class="animate-fade-up d1 font-mono text-[0.65rem] tracking-[0.32em] uppercase text-white/35 mb-8 flex items-center gap-4">
            <span class="w-10 h-px bg-white/15 block"></span> WELCOME BACK <span class="w-10 h-px bg-white/15 block"></span>
        </div>
        <h1 class="animate-fade-up d2 font-display leading-[0.9] mb-7" style="font-size: clamp(3.5rem, 10vw, 8rem);">
            {{ Auth::user()->name }}
        </h1>
        <p class="animate-fade-up d3 text-white/45 text-base max-w-md mb-12">
            Your audio journey continues here. Explore your latest discoveries or track what you're listening to right now.
        </p>
    @else
        <div class="animate-fade-up d1 font-mono text-[0.65rem] tracking-[0.32em] uppercase text-white/35 mb-8 flex items-center gap-4">
            <span class="w-10 h-px bg-white/15 block"></span> INTRODUCING AUDIOQUEST <span class="w-10 h-px bg-white/15 block"></span>
        </div>
        <h1 class="animate-fade-up d2 font-display leading-[0.9] mb-7" style="font-size: clamp(3rem, 11vw, 8.5rem); letter-spacing: 0.02em;">
            Music that<br><span class="stroke-text">feels you</span>
        </h1>
        <p class="animate-fade-up d3 text-white/45 text-base max-w-lg mb-12">
            Explore, rate and share everything you listen to — music, podcasts, audiobooks and more. Our AI finds the perfect match for your moment.
        </p>
        <div class="animate-fade-up d4 flex gap-4">
            <a href="/signup" class="bg-white text-ink font-medium text-sm tracking-widest uppercase px-9 py-4 rounded-full hover:bg-white/90 hover:-translate-y-0.5 transition-all duration-200" style="box-shadow: 0 0 30px rgba(255,255,255,0.15);">
                Get started
            </a>
            <a href="/login" class="text-white/65 text-sm tracking-widest uppercase px-9 py-4 rounded-full border border-white/14 backdrop-blur-md hover:text-white transition-all">
                Log in
            </a>
        </div>
    @endauth

    <canvas id="waveCanvas" class="absolute bottom-0 left-0 w-full opacity-20 pointer-events-none" height="80"></canvas>
</section>

<section class="relative z-10 py-32 px-6 border-t border-white/5">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-20 reveal">
            <div class="font-mono text-[0.6rem] tracking-[0.3em] uppercase text-white/30 mb-4">// How it works</div>
            <h2 class="font-display text-white text-6xl md:text-8xl leading-[0.95]">
                Three steps to<br><span class="stroke-text">perfect sound</span>
            </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
            <div class="glass rounded p-8 reveal" style="transition-delay:0.1s">
                <div class="step-num mb-2">01</div>
                <h3 class="font-display text-2xl tracking-wide mb-3">Describe the scene</h3>
                <p class="text-white/38 text-sm leading-relaxed font-light">Tell the AI what's happening. It understands context, whether it's a calm study session or an intense workout.</p>
            </div>
            <div class="glass rounded p-8 reveal" style="transition-delay:0.2s">
                <div class="step-num mb-2">02</div>
                <h3 class="font-display text-2xl tracking-wide mb-3">Choose the feeling</h3>
                <p class="text-white/38 text-sm leading-relaxed font-light">Set the emotional tone. From deep melancholy to high-energy hype, we match the frequency of your mood.</p>
            </div>
            <div class="glass rounded p-8 reveal" style="transition-delay:0.3s">
                <div class="step-num mb-2">03</div>
                <h3 class="font-display text-2xl tracking-wide mb-3">Track & Share</h3>
                <p class="text-white/38 text-sm leading-relaxed font-light">Keep a log of everything you discover. Share your unique audio journey with your friends and the community.</p>
            </div>
        </div>
    </div>
</section>

<section class="relative z-10 py-32 px-6 border-t border-white/5">
    <div class="max-w-5xl mx-auto text-center">
        <div class="mb-16 reveal">
            <div class="font-mono text-[0.6rem] tracking-[0.3em] uppercase text-white/30 mb-4">// Explore moods</div>
            <h2 class="font-display text-5xl md:text-7xl leading-[0.95]">Every feeling has a soundtrack</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="glass rounded p-6 cursor-pointer reveal" style="transition-delay:0.05s"><div class="text-3xl mb-4">😌</div><div class="font-display text-xl tracking-wide mb-1">Chill</div></div>
            <div class="glass rounded p-6 cursor-pointer reveal" style="transition-delay:0.10s"><div class="text-3xl mb-4">⚡</div><div class="font-display text-xl tracking-wide mb-1">Energy</div></div>
            <div class="glass rounded p-6 cursor-pointer reveal" style="transition-delay:0.15s"><div class="text-3xl mb-4">💭</div><div class="font-display text-xl tracking-wide mb-1">Focus</div></div>
            <div class="glass rounded p-6 cursor-pointer reveal" style="transition-delay:0.20s"><div class="text-3xl mb-4">💪</div><div class="font-display text-xl tracking-wide mb-1">Workout</div></div>
        </div>
    </div>
</section>

<footer class="relative z-10 flex justify-between items-center px-8 py-6 border-t border-white/5">
    <div class="flex items-center gap-3">
        <div class="flex items-end gap-[3px] h-4">
            <div class="w-[2px] bg-teal/40 rounded-full" style="height:40%"></div>
            <div class="w-[2px] bg-teal/40 rounded-full" style="height:70%"></div>
            <div class="w-[2px] bg-teal/40 rounded-full" style="height:100%"></div>
            <div class="w-[2px] bg-teal/40 rounded-full" style="height:35%"></div>
        </div>
        <span style="font-family:'Bebas Neue',sans-serif; letter-spacing:0.15em; font-size:1rem; opacity:0.3;">AudioQuest</span>
    </div>
    <span class="font-mono text-[0.55rem] tracking-[0.15em] text-white/20">© {{ date('Y') }} AudioQuest</span>
</footer>

<script>
    // Waveform Canvas
    const canvas = document.getElementById('waveCanvas');
    const ctx = canvas.getContext('2d');
    function resize() { canvas.width = window.innerWidth; canvas.height = 80; }
    resize(); window.addEventListener('resize', resize);
    let t = 0;
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const waves = [
            { amp: 22, freq: 0.018, speed: 0.025, alpha: 1.0 },
            { amp: 14, freq: 0.030, speed: 0.040, alpha: 0.5 },
            { amp: 8,  freq: 0.048, speed: 0.060, alpha: 0.25 },
        ];
        for (const w of waves) {
            ctx.beginPath();
            for (let x = 0; x <= canvas.width; x += 2) {
                const y = canvas.height - 10 - Math.abs(Math.sin(x * w.freq + t * w.speed)) * w.amp;
                x === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            }
            ctx.strokeStyle = `rgba(0,212,184,${w.alpha})`; ctx.stroke();
        }
        t++; requestAnimationFrame(draw);
    }
    draw();

    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>
