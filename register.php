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
        body { font-family: 'Prompt', 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-zinc-50 text-zinc-900 min-h-screen flex items-center justify-center p-4">
    
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl border border-zinc-100 overflow-hidden">
        <div class="bg-black p-8 text-white text-center">
            <h1 class="text-3xl font-bold tracking-tight flex items-center justify-center gap-3">
                <i class="bi bi-camera-fill"></i>
                MSJ-BOOTH
            </h1>
            <p class="text-zinc-400 mt-2 text-sm font-light">ระบบลงทะเบียนคิวถ่ายภาพ</p>
        </div>
        
        <div class="p-8">
            <form id="regForm" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-2">ชื่อของคุณ (สำหรับเรียกคิว)</label>
                    <div class="relative">
                        <i class="bi bi-person absolute left-4 top-1/2 transform -translate-y-1/2 text-zinc-400"></i>
                        <input type="text" name="name" 
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-zinc-200 focus:border-black focus:ring-1 focus:ring-black outline-none transition bg-zinc-50"
                               placeholder="เช่น: น้องแมว, Sombat" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-2">เบอร์โทรติดต่อ / รหัสนักศึกษา</label>
                    <div class="relative">
                        <i class="bi bi-telephone absolute left-4 top-1/2 transform -translate-y-1/2 text-zinc-400"></i>
                        <input type="text" name="contact" 
                               class="w-full pl-12 pr-4 py-3 rounded-xl border border-zinc-200 focus:border-black focus:ring-1 focus:ring-black outline-none transition bg-zinc-50"
                               placeholder="ระบุหรือไม่ก็ได้">
                    </div>
                </div>

                <button type="submit" id="btnSubmit"
                        class="w-full bg-black text-white font-bold py-4 rounded-xl shadow-lg hover:bg-zinc-800 transform hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2">
                    <i class="bi bi-cloud-upload"></i>
                    <span>ลงทะเบียนเข้าคิว</span>
                </button>
            </form>
        </div>
        
        <div class="bg-zinc-50 p-4 text-center border-t border-zinc-100">
            <p class="text-xs text-zinc-400">© 2026 Msjbooth System</p>
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
                        text: 'รหัสคิวของคุณคือ: ' + data.id,
                        icon: 'success',
                        confirmButtonColor: '#000000',
                        confirmButtonText: 'ตกลง'
                    });
                    this.reset();
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: data.error,
                        icon: 'error',
                        confirmButtonColor: '#000000'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    title: 'เชื่อมต่อล้มเหลว',
                    text: 'กรุณาลองใหม่อีกครั้ง',
                    icon: 'error',
                    confirmButtonColor: '#000000'
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
