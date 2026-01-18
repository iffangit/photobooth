<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/logo.png">
    <title>MSJ-BOOTH - Photobooth System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Prompt', 'Inter', sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body
    class="bg-[#0f0f11] text-zinc-100 min-h-screen flex items-center justify-center p-4 bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop')] bg-cover bg-center">
    <!-- Dark overlay -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm z-0"></div>

    <div class="relative z-10 w-full max-w-6xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <div
                class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl mx-auto mb-6 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="bi bi-camera-fill text-5xl text-white"></i>
            </div>
            <h1
                class="text-5xl md:text-6xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 mb-4 tracking-tight drop-shadow-2xl">
                MSJ-BOOTH
            </h1>
            <p class="text-zinc-400 text-lg">ระบบถ่ายรูปครบวงจร</p>
        </div>

        <!-- Public Section -->
        <div class="mb-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Register Card -->
                <a href="pages/register.php"
                    class="group relative glass-card p-8 rounded-2xl transition-all duration-500 hover:scale-105 hover:bg-white/10 hover:border-green-500/50 shadow-2xl hover:shadow-[0_0_50px_rgba(34,197,94,0.3)] block overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-green-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div class="relative z-10 flex items-center gap-6">
                        <div
                            class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg group-hover:shadow-green-500/50 transition-all duration-500 group-hover:scale-110 flex-shrink-0">
                            <i class="bi bi-person-plus-fill text-3xl text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-white mb-1">ลงทะเบียน</h3>
                            <p class="text-zinc-400 text-sm">ลงทะเบียนเข้าคิวถ่ายรูป</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-zinc-600 group-hover:text-white transition"></i>
                    </div>
                </a>

                <!-- Queue Card -->
                <a href="pages/queue.php"
                    class="group relative glass-card p-8 rounded-2xl transition-all duration-500 hover:scale-105 hover:bg-white/10 hover:border-blue-500/50 shadow-2xl hover:shadow-[0_0_50px_rgba(59,130,246,0.3)] block overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div class="relative z-10 flex items-center gap-6">
                        <div
                            class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-lg group-hover:shadow-blue-500/50 transition-all duration-500 group-hover:scale-110 flex-shrink-0">
                            <i class="bi bi-list-ol text-3xl text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-white mb-1">ดูคิว</h3>
                            <p class="text-zinc-400 text-sm">ตรวจสอบสถานะคิวแบบเรียลไทม์</p>
                        </div>
                        <i class="bi bi-arrow-right text-2xl text-zinc-600 group-hover:text-white transition"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Admin Section -->
        <div>
            <div class="flex items-center justify-center gap-4">
                <a href="login.php"
                    class="inline-flex items-center gap-2 bg-yellow-500/10 hover:bg-yellow-500/20 text-yellow-400 hover:text-yellow-300 px-6 py-3 rounded-xl border border-yellow-500/30 transition text-sm font-medium">
                    <i class="bi bi-shield-lock"></i>
                    <span>สำหรับเจ้าหน้าที่</span>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center">
            <p class="text-xs text-zinc-600 uppercase tracking-widest">© 2026 MSJ-Booth Photobooth System</p>
        </div>
    </div>
</body>

</html>