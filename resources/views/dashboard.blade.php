<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vibe Dashboard</title>
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
            <a href="#" class="flex items-center space-x-3 text-lg font-medium sidebar-item p-2 rounded-lg transition">
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
        <a href="#" class="flex items-center space-x-3 text-lg font-medium sidebar-item p-2 rounded-lg transition text-white">
            <span>Account</span>
        </a>
    </div>
</aside>

<main class="flex-1 flex flex-col items-center justify-center p-8 relative">
    <div id="content-area" class="w-full max-w-2xl flex flex-col items-center">

        <div id="welcome-text" class="text-center mb-12">
            <h1 class="text-5xl font-bold mb-4">What’s the vibe?</h1>
            <p class="text-gray-400 text-lg">Describe the moment, and we'll help you match the best song to it. You can also drop in a photo or a video.</p>
        </div>

        <div id="music-card" class="hidden w-full card-bg p-6 rounded-3xl flex flex-col md:flex-row gap-6 items-center shadow-2xl border border-gray-800">
            <div class="w-48 h-48 bg-white rounded-xl flex items-center justify-center p-4 text-black text-center font-bold text-xl leading-tight">
                sorry no cover art<br><br>we stuck in trafik
            </div>
            <div class="flex-1 flex flex-col justify-between h-48 py-2">
                <div>
                    <h2 class="text-4xl font-bold uppercase tracking-tight">TRAFIK!</h2>
                    <p class="text-gray-400 text-lg">Joost Klein & Käärijä</p>
                </div>
                <div class="space-y-4">
                    <p class="text-gray-500">Upbeat and energetic</p>
                    <button class="bg-black text-white px-5 py-2.5 rounded-full flex items-center space-x-2 border border-gray-700 hover:bg-gray-900 transition w-fit text-sm font-semibold">
                        <span>Listen on Spotify</span>
                        <i class="fab fa-spotify text-green-500"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="action-buttons" class="hidden flex space-x-3 mt-8">
            <button onclick="resetView()" class="bg-[#1a1a1a] px-5 py-2 rounded-full text-xs font-medium hover:bg-gray-800 transition border border-gray-700">Try again</button>
            <button class="bg-[#1a1a1a] px-5 py-2 rounded-full text-xs font-medium hover:bg-gray-800 transition border border-gray-700">License Free</button>
        </div>
    </div>

    <div class="absolute bottom-10 w-full max-w-2xl px-4">
        <div class="relative flex items-center">
            <input
                type="text"
                id="vibe-input"
                placeholder="Describe your moment"
                class="w-full input-bg py-3.5 pl-6 pr-14 rounded-2xl focus:outline-none focus:ring-1 focus:ring-gray-600 text-white placeholder-gray-500"
            >
            <button
                onclick="searchVibe()"
                class="absolute right-3 bg-[#5eead4] hover:bg-teal-300 text-black p-1.5 rounded-full transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
            </button>
        </div>
    </div>
</main>

<script>
    function searchVibe() {
        const input = document.getElementById('vibe-input');
        if(input.value.trim() === "") return;
        document.getElementById('welcome-text').classList.add('hidden');
        document.getElementById('music-card').classList.remove('hidden');
        document.getElementById('action-buttons').classList.remove('hidden');
        const val = input.value;
        input.value = "";
        input.placeholder = val;
    }

    function resetView() {
        document.getElementById('welcome-text').classList.remove('hidden');
        document.getElementById('music-card').classList.add('hidden');
        document.getElementById('action-buttons').classList.add('hidden');
        document.getElementById('vibe-input').placeholder = "Describe your moment";
    }

    document.getElementById('vibe-input').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') searchVibe();
    });
</script>
</body>
</html>
