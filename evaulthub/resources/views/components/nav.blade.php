<nav class="sticky top-0 z-50 border-b border-zinc-800 bg-[#080810]/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 md:px-6">
        <a href="{{ route('home') }}" class="text-2xl font-bold">
            <span class="text-[#e94560]">⚡</span> EVaultHub
        </a>
        <button id="mobile-nav-toggle" class="rounded border border-zinc-700 px-3 py-2 text-sm md:hidden">Menu</button>
        <div id="nav-links" class="hidden items-center gap-5 md:flex">
            <a href="{{ route('home') }}" class="text-sm text-zinc-200 hover:text-white">Home</a>
            <a href="{{ route('schedule') }}" class="text-sm text-zinc-200 hover:text-white">Schedule</a>
            <form action="{{ route('schedule') }}" method="GET" class="relative">
                <input type="text" name="search" placeholder="Search match" class="w-40 rounded-full border border-zinc-700 bg-zinc-900 px-9 py-1.5 text-xs text-white placeholder-zinc-500 focus:border-zinc-500 focus:outline-none">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs">🔍</span>
            </form>
        </div>
    </div>
    <div id="mobile-nav-panel" class="hidden border-t border-zinc-800 px-4 py-3 md:hidden">
        <div class="space-y-2">
            <a href="{{ route('home') }}" class="block rounded bg-zinc-900 px-3 py-2 text-sm">Home</a>
            <a href="{{ route('schedule') }}" class="block rounded bg-zinc-900 px-3 py-2 text-sm">Schedule</a>
        </div>
    </div>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('mobile-nav-toggle');
        const panel = document.getElementById('mobile-nav-panel');
        if (button && panel) {
            button.addEventListener('click', function () {
                panel.classList.toggle('hidden');
            });
        }
    });
</script>
