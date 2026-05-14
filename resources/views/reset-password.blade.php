<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AudioQuest — Reset password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-[#080808] bg-cover bg-center bg-no-repeat"
      style="background-image: url('{{ asset('background.png') }}');">

<nav class="flex items-center justify-between px-8 py-5 border-b border-white/10 backdrop-blur-md">
    <a href="/" class="text-white font-semibold tracking-tight text-lg">AudioQuest</a>
</nav>

<main class="flex-1 flex items-center justify-center px-4 py-16">
    <div class="relative backdrop-blur-2xl bg-white/10 border border-white/20 rounded-[2.5rem] p-10 w-full max-w-sm shadow-2xl text-center">

        <h1 class="text-2xl font-semibold text-white tracking-tight mb-2">Reset password</h1>
        <p class="text-gray-300 text-sm mb-8">Choose a new password for your account.</p>

        @if ($errors->any())
            <div class="mb-6 px-4 py-3 rounded-2xl bg-red-500/20 border border-red-500/50 text-red-200 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <input
                type="password"
                name="password"
                placeholder="New password"
                required
                autocomplete="new-password"
                class="w-full px-6 py-3.5 rounded-full bg-white/15 border border-white/10 text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-teal-400/50 transition-all text-sm"
            >
            <input
                type="password"
                name="password_confirmation"
                placeholder="Confirm new password"
                required
                autocomplete="new-password"
                class="w-full px-6 py-3.5 rounded-full bg-white/15 border border-white/10 text-white placeholder-gray-400 outline-none focus:ring-2 focus:ring-teal-400/50 transition-all text-sm"
            >

            <button
                type="submit"
                class="w-full py-3.5 rounded-full bg-[#063C50] hover:bg-[#0a4d66] text-white font-bold tracking-widest transition-all active:scale-95 shadow-lg mt-2 uppercase text-xs"
            >
                Reset password
            </button>
        </form>

    </div>
</main>

</body>
</html>