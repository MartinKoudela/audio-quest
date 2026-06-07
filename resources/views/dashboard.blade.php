<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vibe Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f0f0f; color: white; font-family: 'Inter', sans-serif; }
        .sidebar-item:hover { background-color: #1a1a1a; }
        .card-bg { background-color: #1a1a1a; }
        .input-bg { background-color: #121212; border: 1px solid #2a2a2a; }
        .chip-active { background-color: #5eead4; color: #0f0f0f; border-color: #5eead4; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

<aside class="w-64 flex flex-col justify-between p-6 border-r border-gray-800">
    <nav class="space-y-6">
        <div class="space-y-4">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 text-lg font-medium sidebar-item p-2 rounded-lg transition">
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

<main class="flex-1 flex flex-col overflow-hidden">
    <div id="chat" class="flex-1 overflow-y-auto px-8">
        <div id="chat-inner" class="w-full max-w-2xl mx-auto flex flex-col gap-6 pt-16 pb-10">
            <div id="welcome-text" class="text-center mb-4">
                <h1 class="text-5xl font-bold mb-4">What's the vibe?</h1>
                <p class="text-gray-400 text-lg">Describe the moment, and we'll help you match the best songs to it.</p>
            </div>
        </div>
    </div>

    <div class="border-t border-gray-800 px-4 py-4">
        <div class="w-full max-w-2xl mx-auto">
            <div class="flex items-center justify-center mb-3">
                <button
                    id="royalty-toggle"
                    type="button"
                    onclick="toggleRoyaltyFree()"
                    class="px-4 py-1.5 rounded-full text-xs font-medium border border-gray-700 text-gray-400 hover:text-white transition"
                >
                    <i class="fa-solid fa-shield-halved mr-1"></i> Royalty-free only
                </button>
            </div>
            <div class="relative flex items-center">
                <input
                    type="text"
                    id="vibe-input"
                    placeholder="Describe your moment"
                    class="w-full input-bg py-3.5 pl-6 pr-14 rounded-2xl focus:outline-none focus:ring-1 focus:ring-gray-600 text-white placeholder-gray-500"
                >
                <button
                    id="search-button"
                    onclick="searchVibe()"
                    class="absolute right-3 bg-[#5eead4] hover:bg-teal-300 text-black p-1.5 rounded-full transition disabled:opacity-50"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</main>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const chatEl = document.getElementById('chat');
    const chatInner = document.getElementById('chat-inner');
    const inputEl = document.getElementById('vibe-input');
    const searchButton = document.getElementById('search-button');

    let royaltyFreeOnly = false;
    let pollTimer = null;

    function toggleRoyaltyFree() {
        royaltyFreeOnly = !royaltyFreeOnly;
        document.getElementById('royalty-toggle').classList.toggle('chip-active', royaltyFreeOnly);
    }

    function searchVibe() {
        const sceneDescription = inputEl.value.trim();
        if (sceneDescription === "" || searchButton.disabled) return;

        document.getElementById('welcome-text')?.remove();
        appendUserMessage(sceneDescription);
        const turn = appendAssistantTurn();
        renderLoading(turn);
        scrollToBottom();

        inputEl.value = '';
        searchButton.disabled = true;

        fetch('/scans', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                scene_description: sceneDescription,
                royalty_free_only: royaltyFreeOnly,
            }),
        })
            .then(response => {
                if (!response.ok) throw new Error('Request failed');
                return response.json();
            })
            .then(scan => pollScan(scan.id, turn))
            .catch(() => renderError(turn));
    }

    function pollScan(id, turn) {
        clearInterval(pollTimer);
        pollTimer = setInterval(() => {
            fetch(`/scans/${id}`, { headers: { 'Accept': 'application/json' } })
                .then(response => response.json())
                .then(scan => {
                    if (scan.status === 'done') {
                        clearInterval(pollTimer);
                        renderResults(turn, scan.result);
                    } else if (scan.status === 'failed') {
                        clearInterval(pollTimer);
                        renderError(turn);
                    }
                })
                .catch(() => {
                    clearInterval(pollTimer);
                    renderError(turn);
                });
        }, 2000);
    }

    // Each submission becomes a chat turn — the user's message stays visible
    // in the history (right-aligned bubble) with the assistant's reply
    // (loading → results/error) appended below it, so earlier vibes remain
    // readable and the user can just keep typing instead of starting over.
    function appendUserMessage(text) {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-end';
        wrapper.innerHTML = `<div class="bg-[#5eead4] text-[#0f0f0f] px-5 py-3 rounded-2xl rounded-br-md max-w-[85%] text-sm font-medium whitespace-pre-wrap break-words">${escapeHtml(text)}</div>`;
        chatInner.appendChild(wrapper);
    }

    function appendAssistantTurn() {
        const turn = document.createElement('div');
        turn.className = 'flex flex-col gap-4 w-full';
        chatInner.appendChild(turn);
        return turn;
    }

    function renderLoading(turn) {
        turn.innerHTML = `
            <div class="flex items-center gap-3 text-gray-400 py-2">
                <i class="fa-solid fa-circle-notch fa-spin text-[#5eead4]"></i>
                <p>Looking for tracks that match your vibe&hellip;</p>
            </div>
        `;
    }

    function renderError(turn) {
        turn.innerHTML = `<p class="text-rose-400 text-sm py-2">Something went wrong while finding tracks. Please try again.</p>`;
        searchButton.disabled = false;
    }

    function renderResults(turn, result) {
        const suggestions = (result && result.music_suggestions) || [];

        turn.innerHTML = suggestions.map(track => `
            <div class="card-bg p-5 rounded-2xl flex flex-col gap-2 border border-gray-800">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold uppercase tracking-tight">${escapeHtml(track.track_name || 'Untitled')}</h2>
                        <p class="text-gray-400 text-sm">${escapeHtml(track.creator || 'Unknown artist')} &middot; ${escapeHtml(track.style_or_genre || '')}</p>
                    </div>
                    ${licenseBadge(track)}
                </div>
                <p class="text-gray-400 text-sm">${escapeHtml(track.description || '')}</p>
                <div class="flex items-center justify-between mt-2 gap-4">
                    <span class="text-gray-500 text-xs">${escapeHtml(track.instrumentation || '')}${track.estimated_bpm ? ' &middot; ~' + escapeHtml(String(track.estimated_bpm)) + ' BPM' : ''}</span>
                    ${trackLink(track)}
                </div>
            </div>
        `).join('');

        searchButton.disabled = false;
        scrollToBottom();
    }

    function scrollToBottom() {
        chatEl.scrollTop = chatEl.scrollHeight;
    }

    function licenseBadge(track) {
        const label = escapeHtml(track.license_type || (track.royalty_free ? 'Royalty-free' : 'License unknown'))
            + (track.license_unconfirmed ? ' &middot; unconfirmed' : '');

        const tone = track.royalty_free
            ? 'bg-[#5eead4]/10 text-[#5eead4] border-[#5eead4]/40'
            : 'bg-gray-700/30 text-gray-400 border-gray-700';

        return `<span class="text-[10px] uppercase tracking-wide ${tone} border px-2 py-1 rounded-full whitespace-nowrap">${label}</span>`;
    }

    function platformFromLink(link) {
        try {
            const host = new URL(link).hostname.replace(/^www\./, '');

            return host === 'deezer.com' ? 'Deezer' : host;
        } catch {
            return 'source';
        }
    }

    function trackLink(track) {
        if (!track.link) return '';

        // The button always opens `track.link` — label it after that link's
        // actual destination (a verified, playable track), not the platform
        // the AI guessed (`source_platform`), which the link no longer points to.
        return `<a href="${escapeAttr(track.link)}" target="_blank" rel="noopener" class="bg-black text-white px-4 py-2 rounded-full flex items-center space-x-2 border border-gray-700 hover:bg-gray-900 transition text-xs font-semibold whitespace-nowrap">
            <span>Listen on ${escapeHtml(platformFromLink(track.link))}</span>
            <i class="fa-solid fa-arrow-up-right-from-square text-[#5eead4]"></i>
        </a>`;
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function escapeAttr(value) {
        return escapeHtml(value).replaceAll('"', '&quot;');
    }

    inputEl.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') searchVibe();
    });
</script>
</body>
</html>
