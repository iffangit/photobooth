<!DOCTYPE html>
<html>

<head>
    <title>Print & Deliver - Photobooth</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Prompt', 'Inter', sans-serif;
        }

        #stripCanvas {
            display: none;
        }

        .fade-enter-active,
        .fade-leave-active {
            transition: opacity 0.3s;
        }

        .fade-enter,
        .fade-leave-to {
            opacity: 0;
        }

        /* Skeleton Loader */
        .skeleton {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.05) 25%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.05) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* A4 Preview Styles */
        .print-layout {
            max-width: 100%;
            width: 100%;
            aspect-ratio: 210/297;
            padding: 4%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 2%;
            box-sizing: border-box;
            background: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            color: black;
        }

        .print-item {
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            width: 100%;
            height: 100%;
        }

        .print-item img {
            height: 100%;
            width: auto;
            max-width: 100%;
            object-fit: contain;
        }

        #print-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body
    class="bg-[#0f0f11] text-zinc-100 overflow-hidden bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop')] bg-cover bg-center">
    <!-- Dark overlay -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm z-0"></div>

    <div id="app" class="relative z-10 flex h-screen overflow-hidden">
        <!-- Mobile Sidebar Toggle Overlay -->
        <div v-if="showSidebar" @click="showSidebar = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden"></div>

        <!-- Sidebar -->
        <div :class="showSidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:static inset-y-0 left-0 w-80 bg-black/80 backdrop-blur-xl border-r border-white/10 flex flex-col z-40 transition-transform duration-300 ease-in-out">
            <div class="p-4 border-b border-white/10 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold flex items-center gap-2 tracking-tight">
                        <i class="bi bi-printer-fill text-indigo-400 text-lg"></i>
                        PRINT MODE
                    </h1>
                </div>
                <button @click="showSidebar = false" class="lg:hidden text-zinc-400 hover:text-white">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <div class="p-3">
                <button @click="fetchQueue"
                    class="w-full bg-white/5 hover:bg-white/10 border border-white/10 text-white font-medium px-3 py-2 rounded-lg transition flex items-center justify-center gap-2 text-sm">
                    <i class="bi bi-arrow-clockwise text-xs"></i>
                    รีเฟรชคิว
                </button>
            </div>

            <div class="flex-1 overflow-auto p-4 space-y-3">
                <!-- Skeleton Loader -->
                <div v-if="isLoadingQueue" class="space-y-3">
                    <div v-for="n in 3" :key="'skeleton-'+n" class="p-4 rounded-xl border border-white/5 bg-white/5">
                        <div class="flex justify-between items-center mb-2">
                            <div class="skeleton h-5 w-24 rounded"></div>
                            <div class="skeleton h-5 w-16 rounded"></div>
                        </div>
                        <div class="skeleton h-3 w-32 rounded"></div>
                    </div>
                </div>

                <!-- Actual Queue -->
                <div v-else-if="filteredQueue.length === 0" class="text-center py-12 text-zinc-600">
                    <i class="bi bi-inbox text-4xl mb-2 block"></i>
                    <p class="text-sm">ไม่มีคิวที่รอปริ้น</p>
                </div>

                <div v-else v-for="c in filteredQueue" :key="c.id" @click="selectCustomer(c); showSidebar = false"
                    class="group p-3 rounded-lg cursor-pointer transition-all duration-300 border relative overflow-hidden"
                    :class="(selectedCustomer && selectedCustomer.customer_code === c.customer_code) ? 'bg-indigo-600/20 border-indigo-500/50 shadow-[0_0_20px_rgba(99,102,241,0.3)]' : 'bg-white/5 border-white/5 hover:bg-white/10 hover:border-white/20'">

                    <div v-if="selectedCustomer && selectedCustomer.customer_code === c.customer_code"
                        class="absolute inset-0 bg-indigo-500/10 blur-xl"></div>

                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-1.5">
                            <strong class="font-semibold text-white tracking-wide text-sm">{{ c.name }}</strong>
                            <span class="px-1.5 py-0.5 rounded text-[9px] uppercase font-bold" :class="{
                                      'bg-yellow-500/80 text-white border border-yellow-400/30': c.status === 'รอปริ้น' || c.status === 'WaitingPrint',
                                      'bg-orange-500/80 text-white border border-orange-400/30': c.status === 'รอส่งมอบ' || c.status === 'WaitingDeliver',
                                  }">
                                {{ (c.status === 'WaitingPrint' || c.status === 'รอปริ้น') ? 'รอปริ้น' : 'รอส่งมอบ' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-zinc-500 font-mono">
                            <i class="bi bi-hash"></i>
                            {{ c.customer_code.split('-')[1] || c.customer_code }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to Menu -->
            <div class="p-3 border-t border-white/10">
                <a href="admin.php"
                    class="w-full bg-white/5 hover:bg-white/10 text-zinc-400 hover:text-white py-2 rounded-lg transition flex items-center justify-center gap-2 text-sm">
                    <i class="bi bi-grid"></i>
                    กลับหน้าหลัก
                </a>
            </div>
        </div>

        <!-- Main Area -->
        <div class="flex-1 overflow-auto p-3 lg:p-6 relative">
            <div v-if="!selectedCustomer" class="flex items-center justify-center h-full">
                <div class="text-center opacity-30 bg-black/20 p-8 rounded-3xl backdrop-blur-sm border border-white/5">
                    <i class="bi bi-printer text-8xl mb-6 text-white block"></i>
                    <h3 class="text-3xl font-light text-white tracking-wider">เลือกลูกค้าเพื่อปริ้นรูป</h3>
                    <button @click="showSidebar = true"
                        class="lg:hidden mt-8 px-6 py-3 bg-indigo-600 text-white rounded-xl shadow-lg">
                        <i class="bi bi-list mr-2"></i> เปิดรายชื่อคิว
                    </button>
                </div>
            </div>

            <div v-else class="max-w-7xl mx-auto pt-8 lg:pt-0">
                <!-- Mobile Toggle -->
                <button @click="showSidebar = true"
                    class="lg:hidden absolute top-3 left-3 z-20 p-1.5 text-white bg-black/40 backdrop-blur rounded-lg border border-white/10">
                    <i class="bi bi-list text-xl"></i>
                </button>

                <!-- Header -->
                <div
                    class="bg-zinc-900/60 backdrop-blur-xl border border-zinc-800 p-4 mb-6 rounded-2xl shadow-2xl overflow-hidden relative">
                    <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-indigo-500/30">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-white mb-0.5">{{ selectedCustomer.name }}</h2>
                                <p class="text-zinc-500 font-mono text-xs">{{ selectedCustomer.customer_code }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div
                                class="flex items-center gap-2 bg-black/40 border border-white/10 px-3 rounded-xl backdrop-blur-md h-[40px]">
                                <button @click="decreaseQty"
                                    class="w-7 h-7 bg-white/5 text-white rounded-lg hover:bg-white/20 transition flex items-center justify-center border border-white/10 flex-shrink-0">
                                    <i class="bi bi-dash text-lg"></i>
                                </button>
                                <div class="flex items-center justify-center min-w-[30px]">
                                    <span class="text-xl font-bold text-white">{{ selectedCustomer.quantity }}</span>
                                </div>
                                <button @click="increaseQty"
                                    class="w-7 h-7 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 transition flex items-center justify-center border border-indigo-600 flex-shrink-0">
                                    <i class="bi bi-plus text-lg"></i>
                                </button>
                                <span class="text-zinc-500 font-medium text-xs pl-1">แท่ง</span>
                            </div>

                            <button
                                v-if="selectedCustomer.status === 'รอส่งมอบ' || selectedCustomer.status === 'WaitingDeliver'"
                                @click="markDelivered"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 h-[38px] rounded-xl transition flex items-center gap-2 font-semibold shadow-lg text-sm">
                                <i class="bi bi-check-lg"></i>
                                ส่งมอบแล้ว
                            </button>

                            <button @click="resetSession"
                                class="bg-white/5 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-white/10 px-4 h-[38px] rounded-xl transition flex items-center gap-2 text-sm">
                                <i class="bi bi-x-circle"></i>
                                ปิดหน้าต่าง
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="space-y-4">
                        <div class="bg-zinc-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-4 shadow-2xl">
                            <h5 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <i class="bi bi-palette text-pink-400 text-base"></i>
                                การตกแต่ง
                            </h5>

                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="text-[10px] font-bold text-zinc-500 mb-1.5 block uppercase tracking-wider">พื้นหลัง</label>
                                    <div class="grid grid-cols-2 gap-1.5 bg-black/30 p-1 rounded-lg">
                                        <button @click="backgroundType = 'color'; regenerateStrip()"
                                            :class="backgroundType === 'color' ? 'bg-indigo-600 text-white shadow-lg' : 'text-zinc-500 hover:text-zinc-300'"
                                            class="py-2 rounded-md transition text-xs font-medium flex items-center justify-center gap-1.5">
                                            <i class="bi bi-paint-bucket text-xs"></i> สีพื้น
                                        </button>
                                        <button @click="backgroundType = 'image'; regenerateStrip()"
                                            :class="backgroundType === 'image' ? 'bg-indigo-600 text-white shadow-lg' : 'text-zinc-500 hover:text-zinc-300'"
                                            class="py-2 rounded-md transition text-xs font-medium flex items-center justify-center gap-1.5">
                                            <i class="bi bi-image text-xs"></i> รูปภาพ
                                        </button>
                                    </div>
                                </div>

                                <div v-if="backgroundType === 'color'">
                                    <label
                                        class="text-[10px] font-bold text-zinc-500 mb-1.5 block uppercase tracking-wider">เลือกสี</label>
                                    <div
                                        class="flex gap-1.5 items-center bg-black/20 p-1.5 rounded-lg border border-white/5">
                                        <input type="color" v-model="backgroundColor" @change="regenerateStrip"
                                            class="w-8 h-8 rounded-md cursor-pointer border py-0 px-0 overflow-hidden bg-transparent">
                                        <span class="text-xs font-mono text-zinc-300">{{ backgroundColor }}</span>
                                    </div>
                                </div>

                                <div v-if="backgroundType === 'image'">
                                    <label
                                        class="text-[10px] font-bold text-zinc-500 mb-1.5 block uppercase tracking-wider">อัปโหลดรูปพื้นหลัง</label>
                                    <input type="file" accept="image/*" @change="handleBackgroundUpload"
                                        class="w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 bg-black/20 rounded-lg border border-white/10 p-0.5">
                                </div>

                                <div>
                                    <label
                                        class="text-[10px] font-bold text-zinc-500 mb-1.5 block uppercase tracking-wider">ข้อความวันที่</label>
                                    <input type="text" v-model="dateText" @input="regenerateStrip"
                                        class="w-full px-3 py-2 rounded-lg bg-black/30 border border-white/10 text-white focus:border-indigo-500 outline-none text-sm">
                                </div>

                                <hr class="border-white/10 my-3">

                                <button @click="printA4"
                                    class="w-full bg-white text-black hover:bg-zinc-200 py-3 rounded-xl transition flex items-center justify-center gap-2 font-bold text-base shadow-lg">
                                    <i class="bi bi-printer text-lg"></i>
                                    สั่งพิมพ์ (A4)
                                </button>
                            </div>
                        </div>

                        <div class="bg-zinc-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-4 shadow-xl">
                            <h6
                                class="text-[10px] font-bold text-zinc-500 mb-2 flex items-center gap-1.5 uppercase tracking-wider">
                                <i class="bi bi-eye text-xs"></i>
                                Strip Preview
                            </h6>
                            <img :src="finalStripUrl" class="w-full shadow-2xl rounded-lg border border-white/20">
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div
                            class="bg-zinc-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-4 h-full flex flex-col shadow-2xl overflow-hidden relative">
                            <h5 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <i class="bi bi-file-earmark-break text-indigo-400 text-base"></i>
                                ตัวอย่าง Print Layout (A4)
                            </h5>

                            <div
                                class="bg-zinc-950/50 rounded-2xl flex-1 flex items-start justify-center overflow-y-auto border-2 border-dashed border-white/5 relative">
                                <div id="print-wrapper" class="relative z-10">
                                    <div class="print-layout shadow-2xl"
                                        v-for="p in Math.ceil((parseInt(selectedCustomer.quantity) || 1) / 6)" :key="p">
                                        <div class="print-item" v-for="n in getPageCount(p)" :key="n">
                                            <img :src="finalStripUrl" v-if="finalStripUrl">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <canvas id="stripCanvas" width="600" height="1800"></canvas>

        <!-- Delivery Confirmation Modal -->
        <transition name="fade">
            <div v-if="showDeliverModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-lg" @click="showDeliverModal = false"></div>
                <div
                    class="bg-zinc-900 rounded-3xl shadow-[0_0_50px_rgba(0,0,0,0.5)] w-full max-w-md relative z-10 overflow-hidden text-center border border-white/10">
                    <div class="absolute top-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-green-500">
                    </div>
                    <div class="p-8">
                        <div
                            class="w-20 h-20 bg-green-500/20 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl shadow-[0_0_30px_rgba(34,197,94,0.3)]">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-2 text-white">ยืนยันการส่งมอบ</h3>
                        <p class="text-zinc-400 mb-8 leading-relaxed">
                            คุณต้องการยืนยันว่าได้ส่งมอบรูปภาพให้ลูกค้า<br>
                            <strong class="text-white text-lg">{{ (selectedCustomer ? selectedCustomer.name : "")
                                }}</strong> แล้วใช่หรือไม่?
                        </p>
                        <div class="flex gap-4">
                            <button @click="showDeliverModal = false"
                                class="flex-1 py-3.5 rounded-xl border border-zinc-700 text-zinc-400 hover:text-white hover:bg-white/5 font-medium transition">
                                ยกเลิก
                            </button>
                            <button @click="confirmDeliver"
                                class="flex-1 py-3.5 rounded-xl bg-green-600 hover:bg-green-500 text-white font-bold shadow-lg shadow-green-600/30 transition">
                                ยืนยัน (จบงาน)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Toast Notifications -->
        <div class="fixed top-4 right-4 z-50 space-y-2">
            <transition-group name="fade">
                <div v-for="toast in toasts" :key="toast.id"
                    class="bg-zinc-900/95 backdrop-blur-xl border rounded-2xl shadow-2xl px-6 py-4 min-w-[300px] flex items-center gap-3"
                    :class="{
                     'border-blue-500/50': toast.type === 'info',
                     'border-green-500/50': toast.type === 'success',
                     'border-red-500/50': toast.type === 'error'
                 }">
                    <i class="text-2xl" :class="{
                    'bi bi-info-circle text-blue-400': toast.type === 'info',
                    'bi bi-check-circle text-green-400': toast.type === 'success',
                    'bi bi-x-circle text-red-400': toast.type === 'error'
                }"></i>
                    <span class="text-white font-medium">{{ toast.message }}</span>
                </div>
            </transition-group>
        </div>
    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                queue: [],
                selectedCustomer: null,
                showSidebar: false,
                shots: [],
                isLoadingQueue: true,
                showDeliverModal: false,
                toasts: [],
                dateText: new Date().toLocaleDateString('en-GB'),
                backgroundType: 'color',
                backgroundColor: '#ffeaa7',
                customBgImage: null,
                finalStripUrl: null
            },
            computed: {
                filteredQueue() {
                    return this.queue.filter(c =>
                        c.status === 'WaitingPrint' ||
                        c.status === 'รอปริ้น' ||
                        c.status === 'WaitingDeliver' ||
                        c.status === 'รอส่งมอบ'
                    );
                }
            },
            mounted() {
                this.fetchQueue();
                setInterval(this.fetchQueue, 5000);
            },
            methods: {
                getPageCount(page) {
                    if (!this.selectedCustomer) return 0;
                    const total = parseInt(this.selectedCustomer.quantity) || 1;
                    const remaining = total - ((page - 1) * 6);
                    return Math.min(6, remaining);
                },
                fetchQueue() {
                    axios.get('api.php?action=get_queue')
                        .then(res => {
                            this.queue = res.data.sort((a, b) => a.id - b.id);
                            this.isLoadingQueue = false;
                        })
                        .catch(err => {
                            console.error(err);
                            this.isLoadingQueue = false;
                            this.showToast('ไม่สามารถโหลดคิวได้', 'error');
                        });
                },
                selectCustomer(c) {
                    this.selectedCustomer = c;
                    this.shots = [];
                    // Fetch images to print
                    axios.get(`api.php?action=get_images&id=${c.customer_code}`)
                        .then(res => {
                            if (res.data.images && res.data.images.length > 0) {
                                this.shots = res.data.images;
                                setTimeout(() => this.drawStrip(), 500);
                            }
                        });
                },
                updateStatus(id, status) {
                    axios.post('api.php?action=update_status', { id, status });
                    if (this.selectedCustomer && this.selectedCustomer.customer_code === id) {
                        this.selectedCustomer.status = status;
                    }
                },
                updateQty() {
                    axios.post('api.php?action=update_qty', { id: this.selectedCustomer.customer_code, qty: this.selectedCustomer.quantity });
                },
                increaseQty() {
                    this.selectedCustomer.quantity = parseInt(this.selectedCustomer.quantity) + 1;
                    this.updateQty();
                },
                decreaseQty() {
                    if (this.selectedCustomer.quantity > 1) {
                        this.selectedCustomer.quantity = parseInt(this.selectedCustomer.quantity) - 1;
                        this.updateQty();
                    }
                },
                handleBackgroundUpload(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const img = new Image();
                        img.onload = () => {
                            this.customBgImage = img;
                            this.drawStrip();
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                },
                drawStrip() {
                    const canvas = document.getElementById('stripCanvas');
                    const ctx = canvas.getContext('2d');

                    if (this.backgroundType === 'image' && this.customBgImage) {
                        ctx.drawImage(this.customBgImage, 0, 0, 600, 1800);
                    } else {
                        ctx.fillStyle = this.backgroundColor;
                        ctx.fillRect(0, 0, 600, 1800);
                    }

                    const photoPromises = this.shots.map(src => {
                        return new Promise(resolve => {
                            const img = new Image();
                            img.crossOrigin = "Anonymous";
                            img.onload = () => resolve(img);
                            img.src = src;
                        });
                    });

                    Promise.all(photoPromises).then(imgs => {
                        const x = 60, w = 480, h = 360;
                        imgs.forEach((img, i) => {
                            let y = 50 + (i * 420);
                            ctx.fillStyle = 'white';
                            ctx.fillRect(x - 10, y - 10, w + 20, h + 20);

                            const sourceRatio = img.width / img.height;
                            const targetRatio = w / h;
                            let sx = 0, sy = 0, sw = img.width, sh = img.height;

                            if (sourceRatio > targetRatio) {
                                sw = img.height * targetRatio;
                                sx = (img.width - sw) / 2;
                            } else {
                                sh = img.width / targetRatio;
                                sy = (img.height - sh) / 2;
                            }

                            ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
                        });

                        ctx.fillStyle = 'white';
                        ctx.font = '500 32px "Prompt", sans-serif';
                        ctx.textAlign = 'left';
                        ctx.fillText(this.dateText, 40, 1720);

                        if (this.selectedCustomer) {
                            ctx.font = '600 36px "Prompt", sans-serif';
                            ctx.textAlign = 'right';
                            ctx.fillText(this.selectedCustomer.name, 560, 1720);
                        }
                        this.finalStripUrl = canvas.toDataURL('image/png');
                    });
                },
                regenerateStrip() { this.drawStrip(); },
                printA4() {
                    this.updateStatus(this.selectedCustomer.customer_code, 'รอส่งมอบ');
                    const element = document.getElementById('print-wrapper');
                    this.printWindow(element.outerHTML);
                },
                markDelivered() { this.showDeliverModal = true; },
                confirmDeliver() {
                    this.updateStatus(this.selectedCustomer.customer_code, 'ส่งมอบแล้ว');
                    // Optimistically remove from queue to avoid confusion
                    this.queue = this.queue.filter(c => c.customer_code !== this.selectedCustomer.customer_code);
                    this.showDeliverModal = false;
                    this.resetSession();
                },
                resetSession() { this.selectedCustomer = null; this.shots = []; },
                printWindow(html) {
                    try {
                        const printWindow = window.open('', '', 'height=600,width=800');
                        if (!printWindow) {
                            Swal.fire({ icon: 'error', title: 'Popup Blocked', text: 'กรุณาอนุญาต Pop-up สำหรับเว็บไซต์นี้', background: '#1a1a1a', color: '#fff' });
                            return;
                        }

                        const css = '<style>' +
                            '@page { size: A4; margin: 0; }' +
                            'body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; }' +
                            '.print-layout { width: 210mm; height: 297mm; padding: 10mm; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(2, 1fr); gap: 5mm; box-sizing: border-box; page-break-after: always; break-after: page; }' +
                            '.print-layout:last-child { page-break-after: auto; break-after: auto; }' +
                            '.print-item { display: flex; justify-content: center; align-items: center; overflow: hidden; }' +
                            '.print-item img { height: 100%; width: auto; max-width: 100%; object-fit: contain; }' +
                            '</style>';

                        printWindow.document.write('<html><head><title>Print</title>' + css + '</head><body>' + html + '</body></html>');
                        printWindow.document.close();
                        printWindow.focus();
                        setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Print Error', text: e.message, background: '#1a1a1a', color: '#fff' });
                    }
                },
                showToast(message, type = 'info') {
                    const id = Date.now();
                    this.toasts.push({ id, message, type });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 3000);
                }
            }
        });
    </script>
</body>

</html>