<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Panel' }} | EVaultHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0a0a0f] text-white">
<div class="min-h-screen md:flex">
    <aside class="w-full border-b border-zinc-800 bg-[#080810] p-4 md:min-h-screen md:w-64 md:border-b-0 md:border-r">
        <a href="{{ route('admin.dashboard') }}" class="block text-2xl font-bold">
            <span class="text-[#e94560]">⚡</span> EVaultHub
        </a>
        <p class="mt-1 text-sm text-zinc-400">Admin Control Panel</p>
        <nav class="mt-6 space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="block rounded px-3 py-2 hover:bg-zinc-800">Dashboard</a>
            <a href="{{ route('admin.import') }}" class="block rounded px-3 py-2 hover:bg-zinc-800">Import Matches</a>
            <a href="{{ route('admin.matches') }}" class="block rounded px-3 py-2 hover:bg-zinc-800">Manage Matches</a>
            <a href="{{ route('admin.analytics') }}" class="block rounded px-3 py-2 hover:bg-zinc-800">Analytics</a>
            <a href="{{ route('admin.logout') }}" class="block rounded px-3 py-2 text-red-300 hover:bg-zinc-800">Logout</a>
        </nav>
    </aside>
    <div class="flex-1">
        <header class="border-b border-zinc-800 bg-[#12121e] px-6 py-4">
            <p class="text-sm text-zinc-400">Logged in as</p>
            <p class="text-lg font-semibold">{{ session('admin_username') }}</p>
        </header>
        <main class="p-5 md:p-7">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-600 bg-green-900/20 px-4 py-3 text-green-200">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-600 bg-red-900/20 px-4 py-3 text-red-200">{{ session('error') }}</div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
