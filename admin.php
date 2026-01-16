<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photobooth Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap');

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
    class="bg-[#0f0f11] text-zinc-100 flex items-center justify-center min-h-screen bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop')] bg-cover bg-center">
    <!-- Dark overlay -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm z-0"></div>

    <div class="relative z-10 w-full max-w-4xl p-6">
        <div class="text-center mb-12">
            <h1
                class="text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 mb-4 tracking-tight drop-shadow-2xl">
                PHOTOBOOTH MANAGER
            </h1>
            <p class="text-zinc-400 text-lg">เลือกโหมดการทำงานของคุณ</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Capture Mode -->
            <a href="capture.php"
                class="group relative glass-card p-10 rounded-3xl transition-all duration-500 hover:scale-105 hover:bg-white/10 hover:border-indigo-500/50 shadow-2xl hover:shadow-[0_0_50px_rgba(99,102,241,0.3)] block overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>

                <div class="relative z-10 flex flex-col items-center text-center space-y-6">
                    <div
                        class="w-24 h-24 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg group-hover:shadow-indigo-500/50 transition-all duration-500 group-hover:scale-110">
                        <i class="bi bi-camera-fill text-4xl text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-white mb-2">Capture Mode</h2>
                        <h3 class="text-xl text-indigo-300 font-medium">โหมดถ่ายภาพ</h3>
                    </div>
                    <p class="text-zinc-400 text-sm leading-relaxed">
                        สำหรับช่างภาพ<br>
                        จัดการคิวถ่ายรูป • ควบคุมกล้อง • ถ่ายภาพ
                    </p>
                    <div
                        class="mt-4 px-6 py-2 rounded-full border border-white/20 text-sm text-zinc-300 group-hover:bg-white group-hover:text-black font-semibold transition-all">
                        เข้าสู่ระบบถ่ายภาพ <i class="bi bi-arrow-right ml-1"></i>
                    </div>
                </div>
            </a>

            <!-- Print Mode -->
            <a href="print.php"
                class="group relative glass-card p-10 rounded-3xl transition-all duration-500 hover:scale-105 hover:bg-white/10 hover:border-pink-500/50 shadow-2xl hover:shadow-[0_0_50px_rgba(236,72,153,0.3)] block overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-pink-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>

                <div class="relative z-10 flex flex-col items-center text-center space-y-6">
                    <div
                        class="w-24 h-24 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center shadow-lg group-hover:shadow-pink-500/50 transition-all duration-500 group-hover:scale-110">
                        <i class="bi bi-printer-fill text-4xl text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-white mb-2">Print Mode</h2>
                        <h3 class="text-xl text-pink-300 font-medium">โหมดพิมพ์รูป</h3>
                    </div>
                    <p class="text-zinc-400 text-sm leading-relaxed">
                        สำหรับพนักงานพิมพ์<br>
                        ตกแต่งรูป • จัดการงานพิมพ์ • ส่งมอบลูกค้า
                    </p>
                    <div
                        class="mt-4 px-6 py-2 rounded-full border border-white/20 text-sm text-zinc-300 group-hover:bg-white group-hover:text-black font-semibold transition-all">
                        เข้าสู่ระบบพิมพ์รูป <i class="bi bi-arrow-right ml-1"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="mt-12 text-center">
            <a href="queue.php" target="_blank"
                class="inline-flex items-center gap-2 text-zinc-500 hover:text-white transition-colors text-sm">
                <i class="bi bi-display"></i> เปิดหน้าจอคิว (Queue Display)
            </a>
        </div>
    </div>
</body>

</html>