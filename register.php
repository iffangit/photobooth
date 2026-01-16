<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลงทะเบียน - Photobooth</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Prompt', 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#0f0f11] text-zinc-100 min-h-screen flex items-center justify-center p-4 bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop')] bg-cover bg-center">
    <!-- Dark overlay -->
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-0"></div>
    
    <div class="max-w-md w-full bg-black/40 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 overflow-hidden relative z-10 transform hover:scale-[1.01] transition duration-500">
        <!-- Decorative Glow -->
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-60 h-60 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-60 h-60 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="p-8 pb-4 text-center relative">
            <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl mx-auto mb-6 flex items-center justify-center text-4xl text-white shadow-lg shadow-indigo-500/30">
                <i class="bi bi-camera-fill"></i>
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-white mb-2">
                MSJ-BOOTH
            </h1>
            <p class="text-zinc-400 text-sm font-light tracking-wide uppercase">Queue Registration System</p>
        </div>
        
        <div class="p-8 pt-4">
            <form id="regForm" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-zinc-400 mb-2 uppercase tracking-wider">ชื่อของคุณ (Name)</label>
                    <div class="relative group">
                        <i class="bi bi-person absolute left-4 top-1/2 transform -translate-y-1/2 text-zinc-500 group-focus-within:text-white transition"></i>
                        <input type="text" name="name" 
                               class="w-full pl-12 pr-4 py-4 rounded-xl bg-white/5 border border-white/10 focus:border-indigo-500 focus:bg-white/10 focus:ring-1 focus:ring-indigo-500 outline-none transition text-white placeholder-zinc-600"
                               placeholder="เช่น: น้องแมว, Sombat" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-zinc-400 mb-2 uppercase tracking-wider">เบอร์โทร / รหัสนักศึกษา (Optional)</label>
                    <div class="relative group">
                        <i class="bi bi-telephone absolute left-4 top-1/2 transform -translate-y-1/2 text-zinc-500 group-focus-within:text-white transition"></i>
                        <input type="text" name="contact" 
                               class="w-full pl-12 pr-4 py-4 rounded-xl bg-white/5 border border-white/10 focus:border-indigo-500 focus:bg-white/10 focus:ring-1 focus:ring-indigo-500 outline-none transition text-white placeholder-zinc-600"
                               placeholder="ระบุหรือไม่ก็ได้">
                    </div>
                </div>

                <button type="submit" id="btnSubmit"
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-600/30 transform hover:scale-[1.02] active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 mt-4">
                    <i class="bi bi-cloud-upload"></i>
                    <span>ลงทะเบียนเข้าคิว</span>
                </button>
            </form>
        </div>
        
        <div class="bg-black/20 p-4 text-center border-t border-white/5 backdrop-blur-md">
            <p class="text-[10px] text-zinc-500 uppercase tracking-widest">© 2026 Msjbooth Glass Edition</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('regForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> กำลังบันทึก...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');

            const formData = new FormData(this);

            fetch('api.php?action=register', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'ลงทะเบียนสำเร็จ!',
                        html: 'รหัสคิวของคุณคือ:<br><strong class="text-3xl text-indigo-500">' + data.id + '</strong>',
                        icon: 'success',
                        background: '#1a1a1a',
                        color: '#ffffff',
                        confirmButtonColor: '#4f46e5',
                        confirmButtonText: 'ตกลง'
                    });
                    this.reset();
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: data.error,
                        icon: 'error',
                        background: '#1a1a1a',
                        color: '#ffffff',
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    title: 'เชื่อมต่อล้มเหลว',
                    text: 'กรุณาลองใหม่อีกครั้ง',
                    icon: 'error',
                    background: '#1a1a1a',
                    color: '#ffffff',
                    confirmButtonColor: '#d33'
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            });
        });
    </script>
</body>
</html>
