<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AudioQuest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-[#080808] text-[#fafafa]">

    <nav class="flex items-center justify-between px-8 py-5 border-b border-[#1a1a1a]">
        <span class="text-white font-semibold tracking-tight text-lg">AudioQuest</span>

        <div class="flex items-center gap-3">
            @auth
                <span class="text-[#737373] text-sm">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-1.5 rounded-md border border-[#262626] text-[#a3a3a3] text-sm hover:border-[#404040] hover:text-white transition-colors">
                        Log out
                    </button>
                </form>
            @else
                <a href="/login"
                   class="px-4 py-1.5 text-[#a3a3a3] text-sm hover:text-white transition-colors">
                    Log in
                </a>
                <a href="/signup"
                   class="px-4 py-1.5 rounded-md bg-white text-black text-sm font-medium hover:bg-[#e5e5e5] transition-colors">
                    Sign up
                </a>
            @endauth
        </div>
    </nav>

    <main class="flex-1 flex flex-col items-center justify-center text-center px-4 relative overflow-hidden"
          style="background-image: url('/background.png'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-[#080808]/50"></div>
        <div class="relative z-10 flex flex-col items-center">
        @auth
            <p class="text-[#737373] text-sm uppercase tracking-widest mb-4">Welcome back</p>
            <h1 class="text-4xl font-semibold tracking-tight mb-3">{{ Auth::user()->name }}</h1>
            <p class="text-[#737373] max-w-sm">Your audio journey continues here.</p>
        @else
            <p class="text-[#737373] text-sm uppercase tracking-widest mb-5">Introducing AudioQuest</p>
            <h1 class="text-5xl font-semibold tracking-tight mb-5 max-w-lg leading-tight">
                Discover and track your audio world
            </h1>
            <p class="text-[#737373] text-lg mb-10 max-w-md">
                Explore, rate and share everything you listen to — music, podcasts, audiobooks and more.
            </p>
            <div class="flex items-center gap-4">
                <a href="/signup"
                   class="px-6 py-2.5 rounded-md bg-white text-black font-medium hover:bg-[#e5e5e5] transition-colors">
                    Get started
                </a>
                <a href="/login"
                   class="px-6 py-2.5 rounded-md border border-[#262626] text-[#a3a3a3] hover:border-[#404040] hover:text-white transition-colors">
                    Log in
                </a>
            </div>
        @endauth
        </div>
    </main>

    <footer class="px-8 py-5 border-t border-[#1a1a1a] text-center text-[#404040] text-xs">
        &copy; {{ date('Y') }} AudioQuest
    </footer>

</body>
</html>