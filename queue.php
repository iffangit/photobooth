<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คิวถ่ายภาพ - Photobooth Queue</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body { font-family: 'Prompt', 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#0f0f11] text-zinc-100 min-h-screen overflow-hidden bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop')] bg-cover bg-center">
    <!-- Dark overlay -->
    <div class="fixed inset-0 bg-black/75 backdrop-blur-sm z-0"></div>

    <div id="app" class="relative z-10 flex flex-col h-screen p-4 lg:p-6 gap-4">
        
        <!-- Header -->
        <header class="flex justify-between items-center bg-black/40 backdrop-blur-xl p-4 rounded-2xl border border-white/10 shadow-xl flex-shrink-0">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-xl text-white shadow-lg shadow-indigo-500/30">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight mb-0.5">MSJ-BOOTH</h1>
                    <p class="text-zinc-400 text-xs font-light tracking-wide flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        LIVE BOARD
                    </p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-3xl font-mono font-bold text-white tracking-widest">{{ timeText }}</div>
            </div>
        </header>

        <!-- Main Layout: 4 Columns -->
        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 overflow-hidden min-h-0">
            
            <!-- COL 1: QUEUE (WAITING) - 3 COLS -->
            <div class="lg:col-span-3 bg-zinc-900/40 backdrop-blur-xl rounded-2xl border border-white/10 flex flex-col overflow-hidden shadow-xl">
                <div class="p-3 border-b border-white/10 bg-black/20 flex justify-between items-center">
                    <h2 class="text-base font-bold text-zinc-300 flex items-center gap-2 uppercase tracking-tight">
                        <i class="bi bi-people-fill"></i> รอถ่าย
                    </h2>
                    <span class="bg-white/10 text-white text-[10px] font-bold px-2 py-0.5 rounded">{{ waitingQueue.length }}</span>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                    <div v-for="(c, index) in waitingQueue" :key="c.id" 
                         class="bg-white/5 border border-white/5 p-3 rounded-xl flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-zinc-800 text-zinc-400 flex items-center justify-center text-xs font-bold font-mono">
                            {{ index + 1 }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-sm text-white truncate">{{ c.name }}</h3>
                            <div class="text-[10px] text-zinc-500 font-mono">{{ c.customer_code }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COL 2: SHOOTING (ACTIVE) - 3 COLS -->
            <div class="lg:col-span-3 bg-zinc-900/40 backdrop-blur-xl rounded-2xl border-2 border-indigo-500/30 flex flex-col overflow-hidden shadow-[0_0_30px_rgba(99,102,241,0.1)] relative">
                <div class="p-3 border-b border-indigo-500/20 bg-indigo-900/10 text-center">
                    <h2 class="text-lg font-bold text-indigo-300 flex items-center justify-center gap-2 uppercase tracking-wider">
                        <i class="bi bi-camera-reels"></i> กำลังถ่าย
                    </h2>
                </div>
                <div class="flex-1 overflow-y-auto p-3 custom-scrollbar">
                    <div class="flex flex-col gap-3">
                        <div v-for="c in shootingQueue" :key="c.id" 
                             class="bg-gradient-to-r from-indigo-600/20 to-purple-600/20 border border-indigo-500/40 p-3 rounded-xl shadow-lg active-card flex items-center gap-3">
                             <!-- Horizontal Layout: ID -> Name -> Status -->
                             <div class="px-3 py-1.5 rounded-full bg-indigo-500 text-white text-sm font-bold font-mono shadow-sm whitespace-nowrap">
                                {{ c.customer_code.split('-')[1] || 'Q' }}
                             </div>
                             
                             <div class="flex-1 min-w-0">
                                <h3 class="text-base font-bold text-white truncate leading-tight">{{ c.name }}</h3>
                             </div>
                             
                             <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                 <i class="bi bi-record-circle text-red-500 animate-pulse text-2xl" v-if="c.status.includes('ถ่าย')"></i>
                                 <i class="bi bi-printer-fill text-yellow-500 animate-pulse text-2xl" v-if="c.status.includes('ปริ้น')"></i>
                                 
                                 <div class="flex items-center gap-1.5 text-[10px] text-indigo-200 font-bold uppercase tracking-wide bg-indigo-500/20 px-2 py-0.5 rounded-full border border-indigo-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                                    กำลังถ่าย
                                 </div>
                             </div>
                        </div>
                    </div>
                     <div v-if="shootingQueue.length === 0" class="h-full flex flex-col items-center justify-center text-zinc-600/50">
                        <i class="bi bi-camera text-3xl mb-2"></i>
                        <span class="text-xs">ว่าง</span>
                    </div>
                </div>
            </div>

            <!-- COL 3: PROCESSING (PRINT/DELIVER) - 3 COLS -->
            <div class="lg:col-span-3 bg-zinc-900/40 backdrop-blur-xl rounded-2xl border border-yellow-500/20 flex flex-col overflow-hidden shadow-xl">
                <div class="p-3 border-b border-yellow-500/10 bg-yellow-900/10 text-center">
                    <h2 class="text-lg font-bold text-yellow-500 flex items-center justify-center gap-2 uppercase tracking-wider">
                        <i class="bi bi-printer"></i> รอรับรูป
                    </h2>
                </div>
                <div class="flex-1 overflow-y-auto p-3 custom-scrollbar">
                    <div class="flex flex-col gap-3">
                        <div v-for="c in processingQueue" :key="c.id" 
                             class="bg-gradient-to-r from-yellow-600/10 to-orange-600/10 border border-yellow-500/30 p-3 rounded-xl shadow-lg flex items-center gap-3">
                             
                             <div class="px-3 py-1.5 rounded-full bg-yellow-600 text-black text-sm font-bold font-mono shadow-sm whitespace-nowrap">
                                {{ c.customer_code.split('-')[1] || 'Q' }}
                             </div>
                             
                             <div class="flex-1 min-w-0">
                                <h3 class="text-base font-bold text-white truncate leading-tight">{{ c.name }}</h3>
                             </div>
                             
                             <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                 <i class="bi bi-printer-fill text-yellow-500 animate-pulse text-2xl"></i>
                                 <div class="flex items-center gap-1.5 text-[10px] text-yellow-500 font-bold uppercase tracking-wide bg-yellow-500/10 px-2 py-0.5 rounded-full border border-yellow-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                                    {{ c.status === 'รอปริ้น' ? 'กำลังพิมพ์' : 'รอรับรูป' }}
                                 </div>
                             </div>
                        </div>
                    </div>
                     <div v-if="processingQueue.length === 0" class="h-full flex flex-col items-center justify-center text-zinc-600/50">
                        <i class="bi bi-images text-3xl mb-2"></i>
                        <span class="text-xs">ว่าง</span>
                    </div>
                </div>
            </div>

            <!-- COL 4: DONE (FINISHED) - 3 COLS -->
            <div class="lg:col-span-3 bg-zinc-900/40 backdrop-blur-xl rounded-2xl border border-green-500/20 flex flex-col overflow-hidden shadow-xl">
                <div class="p-3 border-b border-green-500/10 bg-green-900/10 flex justify-between items-center">
                    <h2 class="text-base font-bold text-green-300 flex items-center gap-2 uppercase tracking-tight">
                        <i class="bi bi-check-circle-fill"></i> เสร็จสิ้น
                    </h2>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                    <div v-for="c in doneQueue" :key="c.id" 
                         class="bg-green-900/5 border border-green-500/10 p-3 rounded-xl flex items-center gap-3 opacity-75 hover:opacity-100 transition">
                        <div class="w-8 h-8 rounded-full bg-green-500/10 text-green-500 flex items-center justify-center text-sm border border-green-500/20">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-zinc-300 text-sm truncate">{{ c.name }}</h3>
                            <div class="text-[10px] text-green-500/50">รับรูปแล้ว</div>
                        </div>
                    </div>
                    <div v-if="doneQueue.length === 0" class="py-20 text-center text-zinc-600">
                        <span class="text-xs">ไม่มีรายการ</span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                queue: [],
                timeText: '',
                dateText: ''
            },
            computed: {
                waitingQueue() {
                    return this.queue.filter(c => 
                        ['ลงทะเบียนแล้ว', 'Registered'].includes(c.status)
                    ).sort((a, b) => a.id - b.id);
                },
                shootingQueue() {
                    return this.queue.filter(c => 
                        ['กำลังถ่าย', 'Shooting'].includes(c.status)
                    );
                },
                processingQueue() {
                    return this.queue.filter(c => 
                        ['รอปริ้น', 'WaitingPrint', 'รอส่งมอบ', 'WaitingDeliver'].includes(c.status)
                    );
                },
                doneQueue() {
                    return this.queue.filter(c => 
                        ['ส่งมอบแล้ว', 'Delivered'].includes(c.status)
                    ).slice(0, 10);
                }
            },
            mounted() {
                this.fetchQueue();
                setInterval(this.fetchQueue, 3000); 
                this.updateTime();
                setInterval(this.updateTime, 1000);
            },
            methods: {
                fetchQueue() {
                    axios.get('api.php?action=get_queue')
                        .then(res => { this.queue = res.data; })
                        .catch(err => console.error(err));
                },
                updateTime() {
                    const now = new Date();
                    this.timeText = now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
                    this.dateText = now.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
                }
            }
        });
    </script>
    <style>
        .active-card { animation: pulse-border 3s infinite; }
        @keyframes pulse-border {
            0% { border-color: rgba(99, 102, 241, 0.4); box-shadow: 0 0 0 rgba(79, 70, 229, 0); }
            50% { border-color: rgba(99, 102, 241, 0.8); box-shadow: 0 0 30px rgba(79, 70, 229, 0.2); }
            100% { border-color: rgba(99, 102, 241, 0.4); box-shadow: 0 0 0 rgba(79, 70, 229, 0); }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
    </style>
</body>
</html>
