<?php
session_start();
require_once 'includes/csrf.php';

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: pages/admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/logo.png">
    <title>Login - MSJ-BOOTH</title>
    <meta name="csrf-token" content="<?php echo CSRF::generate(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Prompt', 'Inter', sans-serif;
        }
    </style>
</head>

<body
    class="bg-[#0f0f11] text-zinc-100 min-h-screen flex items-center justify-center p-4 bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop')] bg-cover bg-center">
    <!-- Dark overlay -->
    <div class="fixed inset-0 bg-black/75 backdrop-blur-sm z-0"></div>

    <div
        class="relative z-10 max-w-md w-full bg-black/40 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 overflow-hidden transform hover:scale-[1.01] transition duration-500">
        <!-- Decorative Glow -->
        <div
            class="absolute top-0 right-0 -mt-20 -mr-20 w-60 h-60 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 -mb-20 -ml-20 w-60 h-60 bg-purple-500/20 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="p-8 pb-4 text-center relative">
            <div
                class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl mx-auto mb-6 flex items-center justify-center text-4xl text-white shadow-lg shadow-indigo-500/30">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-white mb-2">
                Admin Login
            </h1>
            <p class="text-zinc-400 text-sm font-light tracking-wide uppercase">เจ้าหน้าที่เท่านั้น</p>
        </div>

        <div class="p-8 pt-4">
            <form id="loginForm" class="space-y-6">
                <div id="errorMessage"
                    class="hidden bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">
                    <i class="bi bi-exclamation-triangle mr-2"></i>
                    <span id="errorText"></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-400 mb-2 uppercase tracking-wider">Username</label>
                    <div class="relative group">
                        <i
                            class="bi bi-person absolute left-4 top-1/2 transform -translate-y-1/2 text-zinc-500 group-focus-within:text-white transition"></i>
                        <input type="text" name="username" id="username"
                            class="w-full pl-12 pr-4 py-4 rounded-xl bg-white/5 border border-white/10 focus:border-indigo-500 focus:bg-white/10 focus:ring-1 focus:ring-indigo-500 outline-none transition text-white placeholder-zinc-600"
                            placeholder="ชื่อผู้ใช้" required autofocus>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-400 mb-2 uppercase tracking-wider">Password</label>
                    <div class="relative group">
                        <i
                            class="bi bi-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-zinc-500 group-focus-within:text-white transition"></i>
                        <input type="password" name="password" id="password"
                            class="w-full pl-12 pr-4 py-4 rounded-xl bg-white/5 border border-white/10 focus:border-indigo-500 focus:bg-white/10 focus:ring-1 focus:ring-indigo-500 outline-none transition text-white placeholder-zinc-600"
                            placeholder="รหัสผ่าน" required>
                    </div>
                </div>

                <button type="submit" id="btnSubmit"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-600/30 transform hover:scale-[1.02] active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 mt-4">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>เข้าสู่ระบบ</span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="index.php" class="text-zinc-500 hover:text-white text-sm transition">
                    <i class="bi bi-arrow-left mr-1"></i>
                    กลับหน้าหลัก
                </a>
            </div>
        </div>

        <div class="bg-black/20 p-4 text-center border-t border-white/5 backdrop-blur-md">
            <p class="text-[10px] text-zinc-500 uppercase tracking-widest">© 2026 MSJ-Booth Admin Portal</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const originalText = btn.innerHTML;
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');

            // Hide previous errors
            errorDiv.classList.add('hidden');

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> กำลังตรวจสอบ...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');

            const formData = new FormData(this);
            // Append CSRF Token
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch('api/api.php?action=login', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="bi bi-check-circle"></i> เข้าสู่ระบบสำเร็จ';
                        btn.classList.remove('opacity-75');
                        btn.classList.add('bg-green-600');
                        setTimeout(() => {
                            window.location.href = 'pages/admin.php';
                        }, 500);
                    } else {
                        errorText.textContent = data.error || 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
                        errorDiv.classList.remove('hidden');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        btn.classList.remove('opacity-75', 'cursor-not-allowed');
                    }
                })
                .catch(err => {
                    errorText.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่';
                    errorDiv.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    btn.classList.remove('opacity-75', 'cursor-not-allowed');
                });
        });
    </script>
</body>

</html>