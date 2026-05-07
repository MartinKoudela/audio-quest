<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AudioQuest — Sign up</title>
    @vite('resources/css/app.css', 'resources/js/app.js')
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-[#080808] text-[#fafafa]">

    <nav class="flex items-center justify-between px-8 py-5 border-b border-[#1a1a1a]">
        <a href="/" class="text-white font-semibold tracking-tight text-lg">AudioQuest</a>
        <a href="/login" class="text-[#737373] text-sm hover:text-white transition-colors">
            Already have an account?
        </a>
    </nav>

    <main class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-sm">

            <h1 class="text-2xl font-semibold tracking-tight mb-1">Create an account</h1>
            <p class="text-[#737373] text-sm mb-8">Join AudioQuest and start your journey.</p>

            @if ($errors->any())
                <div class="mb-6 px-4 py-3 rounded-md bg-red-950 border border-red-900 text-red-400 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="/signup" class="space-y-5">
                @csrf

                <div class="space-y-1.5">
                    <label class="text-sm text-[#a3a3a3]">Name</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
                        class="w-full px-3.5 py-2.5 rounded-md bg-[#111111] border border-[#262626] text-white placeholder-[#404040] text-sm outline-none focus:border-[#525252] transition-colors"
                    >
                </div>

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
                        autocomplete="new-password"
                        class="w-full px-3.5 py-2.5 rounded-md bg-[#111111] border border-[#262626] text-white placeholder-[#404040] text-sm outline-none focus:border-[#525252] transition-colors"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm text-[#a3a3a3]">Confirm password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="w-full px-3.5 py-2.5 rounded-md bg-[#111111] border border-[#262626] text-white placeholder-[#404040] text-sm outline-none focus:border-[#525252] transition-colors"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 rounded-md bg-white text-black font-medium text-sm hover:bg-[#e5e5e5] transition-colors mt-2"
                >
                    Create account
                </button>

            </form>

            <p class="text-center text-sm text-[#737373] mt-6">
                Already have an account?
                <a href="/login" class="text-white hover:underline ml-1">Log in</a>
            </p>

        </div>
    </main>

</body>
</html>
