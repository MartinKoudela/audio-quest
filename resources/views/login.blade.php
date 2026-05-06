<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AudioQuest — Log in</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-[#080808] text-[#fafafa]">

    <nav class="flex items-center justify-between px-8 py-5 border-b border-[#1a1a1a]">
        <a href="/" class="text-white font-semibold tracking-tight text-lg">AudioQuest</a>
        <a href="/signup" class="text-[#737373] text-sm hover:text-white transition-colors">
            Create an account
        </a>
    </nav>

    <main class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-sm">

            <h1 class="text-2xl font-semibold tracking-tight mb-1">Log in</h1>
            <p class="text-[#737373] text-sm mb-8">Enter your credentials to continue.</p>

            @if ($errors->any())
                <div class="mb-6 px-4 py-3 rounded-md bg-red-950 border border-red-900 text-red-400 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-5">
                @csrf

                <div class="space-y-1.5">
                    <label class="text-sm text-[#a3a3a3]">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        class="w-full px-3.5 py-2.5 rounded-md bg-[#111111] border border-[#262626] text-white placeholder-[#404040] text-sm outline-none focus:border-[#525252] transition-colors"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm text-[#a3a3a3]">Password</label>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-3.5 py-2.5 rounded-md bg-[#111111] border border-[#262626] text-white placeholder-[#404040] text-sm outline-none focus:border-[#525252] transition-colors"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 rounded-md bg-white text-black font-medium text-sm hover:bg-[#e5e5e5] transition-colors mt-2"
                >
                    Log in
                </button>

            </form>

            <p class="text-center text-sm text-[#737373] mt-6">
                Don't have an account?
                <a href="/signup" class="text-white hover:underline ml-1">Sign up</a>
            </p>

        </div>
    </main>

</body>
</html>