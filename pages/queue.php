<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>คิวถ่ายภาพ - Photobooth Queue</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../assets/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body {
            font-family: 'Prompt', 'Inter', sans-serif;
        }
    </style>
</head>

<body
    class="bg-[#0f0f11] text-zinc-100 bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop')] bg-cover bg-center">
    <!-- Dark overlay -->
    <div class="fixed inset-0 bg-black/75 backdrop-blur-sm z-0"></div>

    <div id="app"
        class="h-screen flex flex-col bg-gradient-to-br from-zinc-950 via-black to-zinc-900 text-white relative overflow-hidden">
        <!-- Background Gradient Orbs -->
        <div
            class="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-purple-600/10 rounded-full blur-[100px] pointer-events-none">
        </div>

        <!-- Header -->
        <div class="bg-black/60 backdrop-blur-xl border-b border-white/5 p-4 md:p-6 shadow-2xl z-10">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-center md:text-left">
                    <h1
                        class="text-2xl md:text-4xl font-extrabold tracking-tight bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent flex items-center gap-3 justify-center md:justify-start">
                        <i class="bi bi-camera-fill text-indigo-400"></i>
                        MSJ-BOOTH
                    </h1>
                    <p class="text-xs md:text-sm text-zinc-600 mt-1">สถานะคิวถ่ายภาพ - Real-time Updates</p>
                </div>
                <div class="flex items-center gap-4">
                    <div
                        class="flex items-center gap-2 bg-white/5 px-4 md:px-6 py-2 md:py-3 rounded-full border border-white/10">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs md:text-sm font-semibold text-zinc-400">LIVE</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content - Responsive Grid -->
        <div class="flex-1 p-2 md:p-8 overflow-y-auto md:overflow-hidden">
            <div
                class="h-full w-full max-w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 md:gap-6 pb-20 md:pb-0">

                <!-- COL 1: WAITING (REGISTERED) -->
                <div
                    class="lg:col-span-3 bg-zinc-900/40 backdrop-blur-xl rounded-2xl border border-white/10 flex flex-col overflow-hidden shadow-xl lg:h-[calc(100vh-180px)] h-[400px]">
                    <div
                        class="p-3 border-b border-white/10 bg-black/20 flex justify-between items-center flex-shrink-0">
                        <h2 class="text-base font-bold text-zinc-300 flex items-center gap-2 uppercase tracking-tight">
                            <i class="bi bi-people-fill"></i> รอถ่าย
                        </h2>
                        <span class="bg-white/10 text-white text-[10px] font-bold px-2 py-0.5 rounded">{{
                            waitingQueue.length }}</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar min-h-0">
                        <div v-for="(c, index) in waitingQueue" :key="c.id"
                            class="bg-white/5 border border-white/5 p-3 rounded-xl flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-zinc-800 text-zinc-400 flex items-center justify-center text-xs font-bold font-mono">
                                {{ index + 1 }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-sm text-white truncate">{{ c.name }}</h3>
                                <div class="text-[10px] text-zinc-500 font-mono">{{ c.customer_code }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COL 2: SHOOTING (ACTIVE) -->
                <div
                    class="lg:col-span-3 bg-zinc-900/40 backdrop-blur-xl rounded-2xl border-2 border-indigo-500/30 flex flex-col overflow-hidden shadow-[0_0_30px_rgba(99,102,241,0.1)] relative lg:h-[calc(100vh-180px)] h-[400px]">
                    <div class="p-3 border-b border-indigo-500/20 bg-indigo-900/10 text-center flex-shrink-0">
                        <h2
                            class="text-lg font-bold text-indigo-300 flex items-center justify-center gap-2 uppercase tracking-wider">
                            <i class="bi bi-camera-reels"></i> กำลังถ่าย
                        </h2>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 custom-scrollbar">
                        <div class="flex flex-col gap-3">
                            <div v-for="c in shootingQueue" :key="c.id"
                                class="bg-gradient-to-r from-indigo-600/20 to-purple-600/20 border border-indigo-500/40 p-3 rounded-xl shadow-lg active-card flex items-center gap-3">
                                <!-- Horizontal Layout: ID -> Name -> Status -->
                                <div
                                    class="px-3 py-1.5 rounded-full bg-indigo-500 text-white text-sm font-bold font-mono shadow-sm whitespace-nowrap">
                                    {{ c.customer_code.split('-')[1] || 'Q' }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-bold text-white truncate leading-tight">{{ c.name }}</h3>
                                </div>

                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                    <i class="bi bi-record-circle text-red-500 animate-pulse text-2xl"
                                        v-if="c.status.includes('ถ่าย')"></i>
                                    <i class="bi bi-printer-fill text-yellow-500 animate-pulse text-2xl"
                                        v-if="c.status.includes('ปริ้น')"></i>

                                    <div
                                        class="flex items-center gap-1.5 text-[10px] text-indigo-200 font-bold uppercase tracking-wide bg-indigo-500/20 px-2 py-0.5 rounded-full border border-indigo-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                                        กำลังถ่าย
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="shootingQueue.length === 0"
                            class="h-full flex flex-col items-center justify-center text-zinc-600/50">
                            <i class="bi bi-camera text-3xl mb-2"></i>
                            <span class="text-xs">ว่าง</span>
                        </div>
                    </div>
                </div>

                <!-- COL 3: PROCESSING (PRINT/DELIVER) -->
                <div
                    class="lg:col-span-3 bg-zinc-900/40 backdrop-blur-xl rounded-2xl border border-yellow-500/20 flex flex-col overflow-hidden shadow-xl lg:h-[calc(100vh-180px)] h-[400px]">
                    <div class="p-3 border-b border-yellow-500/10 bg-yellow-900/10 text-center flex-shrink-0">
                        <h2
                            class="text-lg font-bold text-yellow-500 flex items-center justify-center gap-2 uppercase tracking-wider">
                            <i class="bi bi-printer"></i> รอรับรูป
                        </h2>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 custom-scrollbar">
                        <div class="flex flex-col gap-3">
                            <div v-for="c in processingQueue" :key="c.id"
                                :class="c.status === 'รอส่งมอบ' || c.status === 'WaitingDeliver' 
                                    ? 'bg-gradient-to-r from-pink-600/10 to-rose-600/10 border border-pink-500/30' 
                                    : 'bg-gradient-to-r from-yellow-600/10 to-orange-600/10 border border-yellow-500/30'"
                                class="p-3 rounded-xl shadow-lg flex items-center gap-3 transition-colors duration-300">

                                <div :class="c.status === 'รอส่งมอบ' || c.status === 'WaitingDeliver' 
                                        ? 'bg-pink-600 text-white' 
                                        : 'bg-yellow-600 text-black'"
                                    class="px-3 py-1.5 rounded-full text-sm font-bold font-mono shadow-sm whitespace-nowrap">
                                    {{ c.customer_code.split('-')[1] || 'Q' }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-bold text-white truncate leading-tight">{{ c.name }}</h3>
                                </div>

                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                    <!-- Icon: Print for Printing, Box for Deliver -->
                                    <i v-if="c.status === 'รอปริ้น' || c.status === 'WaitingPrint'"
                                        class="bi bi-printer-fill text-yellow-500 animate-pulse text-2xl"></i>
                                    <i v-else class="bi bi-box-seam-fill text-pink-500 animate-bounce text-2xl"></i>

                                    <!-- Status Badge -->
                                    <div :class="c.status === 'รอส่งมอบ' || c.status === 'WaitingDeliver'
                                            ? 'text-pink-500 bg-pink-500/10 border-pink-500/20'
                                            : 'text-yellow-500 bg-yellow-500/10 border-yellow-500/20'"
                                        class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full border">

                                        <span
                                            :class="c.status === 'รอส่งมอบ' || c.status === 'WaitingDeliver' ? 'bg-pink-500' : 'bg-yellow-500'"
                                            class="w-1.5 h-1.5 rounded-full animate-pulse"></span>
                                        {{ c.status === 'รอปริ้น' || c.status === 'WaitingPrint' ? 'กำลังพิมพ์' :
                                        'รอรับรูป' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="processingQueue.length === 0"
                            class="h-full flex flex-col items-center justify-center text-zinc-600/50">
                            <i class="bi bi-images text-3xl mb-2"></i>
                            <span class="text-xs">ว่าง</span>
                        </div>
                    </div>
                </div>

                <!-- COL 4: DONE (RECENT) -->
                <div
                    class="lg:col-span-3 bg-zinc-900/40 backdrop-blur-xl rounded-2xl border border-green-500/20 flex flex-col overflow-hidden shadow-xl lg:h-[calc(100vh-180px)] h-[400px]">
                    <div
                        class="p-3 border-b border-green-500/10 bg-green-900/10 flex justify-between items-center flex-shrink-0">
                        <h2 class="text-base font-bold text-green-300 flex items-center gap-2 uppercase tracking-tight">
                            <i class="bi bi-check-circle-fill"></i> เสร็จสิ้น
                        </h2>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar min-h-0">
                        <div v-for="c in doneQueue" :key="c.id"
                            class="bg-green-900/5 border border-green-500/10 p-3 rounded-xl flex items-center gap-3 opacity-75 hover:opacity-100 transition">
                            <div
                                class="w-8 h-8 rounded-full bg-green-500/10 text-green-500 flex items-center justify-center text-sm border border-green-500/20">
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
                        ).sort((a, b) => a.id - b.id);
                    },
                    processingQueue() {
                        return this.queue.filter(c =>
                            ['รอปริ้น', 'WaitingPrint', 'รอส่งมอบ', 'WaitingDeliver'].includes(c.status)
                        ).sort((a, b) => a.id - b.id);
                    },
                    doneQueue() {
                        return this.queue.filter(c =>
                            ['ส่งมอบแล้ว', 'Delivered'].includes(c.status)
                        ).sort((a, b) => b.id - a.id);
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
                        axios.get('../api/api.php?action=get_queue')
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
            .active-card {
                animation: pulse-border 3s infinite;
            }

            @keyframes pulse-border {
                0% {
                    border-color: rgba(99, 102, 241, 0.4);
                    box-shadow: 0 0 0 rgba(79, 70, 229, 0);
                }

                50% {
                    border-color: rgba(99, 102, 241, 0.8);
                    box-shadow: 0 0 30px rgba(79, 70, 229, 0.2);
                }

                100% {
                    border-color: rgba(99, 102, 241, 0.4);
                    box-shadow: 0 0 0 rgba(79, 70, 229, 0);
                }
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.1);
                border-radius: 4px;
            }
        </style>
</body>

</html>