<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Vibe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f0f0f; color: white; font-family: 'Inter', sans-serif; }
        .sidebar-item:hover { background-color: #1a1a1a; }
        .card-bg { background-color: #1a1a1a; }
        .input-bg { background-color: #121212; border: 1px solid #2a2a2a; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

<aside class="w-64 flex flex-col justify-between p-6 border-r border-gray-800">
    <nav class="space-y-6">
        <div class="space-y-4">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 text-lg font-medium sidebar-item p-2 rounded-lg transition text-gray-400 hover:text-white">
                <span>New Chat</span>
            </a>
            <a href="#" class="flex items-center space-x-3 text-lg font-medium sidebar-item p-2 rounded-lg transition text-gray-400 hover:text-white">
                <span>Library</span>
            </a>
            <a href="#" class="flex items-center space-x-3 text-lg font-medium sidebar-item p-2 rounded-lg transition text-gray-400 hover:text-white">
                <span>Search</span>
            </a>
        </div>
    </nav>
    <div class="mt-auto">
        <a href="{{ route('settings') }}" class="flex items-center space-x-3 text-lg font-medium sidebar-item p-2 rounded-lg transition text-white">
            <span>Account</span>
        </a>
    </div>
</aside>

<main class="flex-1 overflow-y-auto p-10">
    <div class="max-w-xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Settings</h1>

        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-xl bg-teal-900/40 border border-teal-600 text-teal-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Profile --}}
        <section class="card-bg rounded-2xl p-6 mb-6 border border-gray-800">
            <h2 class="text-lg font-semibold mb-5">Profile</h2>
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                        class="w-full input-bg py-2.5 px-4 rounded-xl focus:outline-none focus:ring-1 focus:ring-gray-600 text-white placeholder-gray-500">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                        class="w-full input-bg py-2.5 px-4 rounded-xl focus:outline-none focus:ring-1 focus:ring-gray-600 text-white placeholder-gray-500">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="bg-[#5eead4] hover:bg-teal-300 text-black font-semibold px-5 py-2 rounded-full text-sm transition">
                    Save changes
                </button>
            </form>
        </section>

        {{-- Change password --}}
        <section class="card-bg rounded-2xl p-6 mb-6 border border-gray-800">
            <h2 class="text-lg font-semibold mb-5">Change password</h2>
            <form action="{{ route('settings.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Current password</label>
                    <input type="password" name="current_password"
                        class="w-full input-bg py-2.5 px-4 rounded-xl focus:outline-none focus:ring-1 focus:ring-gray-600 text-white placeholder-gray-500">
                    @error('current_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">New password</label>
                    <input type="password" name="password"
                        class="w-full input-bg py-2.5 px-4 rounded-xl focus:outline-none focus:ring-1 focus:ring-gray-600 text-white placeholder-gray-500">
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Confirm new password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full input-bg py-2.5 px-4 rounded-xl focus:outline-none focus:ring-1 focus:ring-gray-600 text-white placeholder-gray-500">
                </div>
                <button type="submit" class="bg-[#5eead4] hover:bg-teal-300 text-black font-semibold px-5 py-2 rounded-full text-sm transition">
                    Update password
                </button>
            </form>
        </section>

        {{-- Danger zone --}}
        <section class="card-bg rounded-2xl p-6 border border-red-900/50">
            <h2 class="text-lg font-semibold mb-2 text-red-400">Danger zone</h2>
            <p class="text-gray-500 text-sm mb-4">Once you delete your account, there is no going back.</p>
            <form action="{{ route('settings.destroy') }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete your account?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-900/40 hover:bg-red-800/60 text-red-400 border border-red-800 font-semibold px-5 py-2 rounded-full text-sm transition">
                    Delete account
                </button>
            </form>
        </section>
    </div>
</main>

</body>
</html>