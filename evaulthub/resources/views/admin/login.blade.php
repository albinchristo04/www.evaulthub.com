<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | EVaultHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-[#0a0a0f] px-4 text-white">
<div class="w-full max-w-md rounded-2xl border border-zinc-800 bg-[#12121e] p-6">
    <h1 class="text-2xl font-bold"><span class="text-[#e94560]">⚡</span> EVaultHub Admin</h1>
    <p class="mt-2 text-sm text-zinc-400">Login to manage streams, imports, and analytics.</p>

    @if($errors->any())
        <div class="mt-4 rounded-lg border border-red-600 bg-red-900/20 px-4 py-3 text-sm text-red-200">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-5 space-y-4">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-medium">Username</label>
            <input type="text" name="username" required class="w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-white focus:border-zinc-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Password</label>
            <input type="password" name="password" required class="w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-white focus:border-zinc-500 focus:outline-none">
        </div>
        <button type="submit" class="w-full rounded-lg bg-[#e94560] px-4 py-2.5 font-semibold">Login</button>
    </form>
</div>
</body>
</html>
