<!DOCTYPE html>
<html>

<head>
    <title>Capture - Photobooth</title>
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

        .camera-feed {
            transform: scaleX(-1);
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

        /* Photo Capture Flash */
        @keyframes flash {

            0%,
            100% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }
        }

        .camera-flash {
            animation: flash 0.3s ease-out;
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
                        <i class="bi bi-camera-fill text-indigo-400 text-lg"></i>
                        CAPTURE MODE
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
                    <p class="text-sm">ไม่มีคิวที่รอถ่าย</p>
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
                                      'bg-green-800 text-green-400 border border-green-700': c.status === 'ลงทะเบียนแล้ว' || c.status === 'Registered',
                                      'bg-blue-600/80 text-white border border-blue-400/30': c.status === 'กำลังถ่าย' || c.status === 'Shooting',
                                  }">
                                {{ (c.status === 'Registered' || c.status === 'ลงทะเบียนแล้ว') ? 'ลงทะเบียนแล้ว' :
                                'กำลังถ่าย' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-zinc-500 font-mono">
                            <i class="bi bi-hash"></i>
                            {{ c.customer_code.split('-')[1] || c.customer_code }}
                        </div>
                        <div v-if="c.contact" class="flex items-center gap-2 text-xs text-zinc-400 mt-1">
                            <i class="bi bi-telephone"></i>
                            {{ c.contact }}
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
        <div class="flex-1 overflow-hidden p-2 lg:p-4 relative flex flex-col h-screen">
            <div v-if="!selectedCustomer" class="flex items-center justify-center h-full">
                <div class="text-center opacity-30 bg-black/20 p-8 rounded-3xl backdrop-blur-sm border border-white/5">
                    <i class="bi bi-camera text-8xl mb-6 text-white block"></i>
                    <h3 class="text-3xl font-light text-white tracking-wider">เลือกลูกค้าเพื่อถ่ายรูป</h3>
                    <button @click="showSidebar = true"
                        class="lg:hidden mt-8 px-6 py-3 bg-indigo-600 text-white rounded-xl shadow-lg">
                        <i class="bi bi-list mr-2"></i> เปิดรายชื่อคิว
                    </button>
                </div>
            </div>

            <div v-else class="max-w-7xl mx-auto w-full h-full flex flex-col">
                <!-- Mobile Toggle -->
                <button @click="showSidebar = true"
                    class="lg:hidden absolute top-3 left-3 z-20 p-1.5 text-white bg-black/40 backdrop-blur rounded-lg border border-white/10">
                    <i class="bi bi-list text-xl"></i>
                </button>

                <!-- Header -->
                <div
                    class="bg-zinc-900/60 backdrop-blur-xl border border-zinc-800 p-3 mb-3 rounded-2xl shadow-2xl overflow-hidden relative flex-shrink-0">
                    <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-2">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-500/30">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white mb-0.5">{{ selectedCustomer.name }}</h2>
                                <p class="text-zinc-500 font-mono text-xs">{{ selectedCustomer.customer_code }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button @click="resetSession"
                                class="bg-white/5 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-white/10 px-4 h-[38px] rounded-xl transition flex items-center gap-2 text-sm">
                                <i class="bi bi-x-circle"></i>
                                ปิดหน้าต่าง
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 flex-1 min-h-0">
                    <div class="lg:col-span-2 flex flex-col h-full min-h-0">
                        <div
                            class="bg-zinc-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-3 shadow-2xl relative overflow-hidden group flex-1 flex flex-col">
                            <div class="mb-2 flex-shrink-0 flex items-center gap-2">
                                <div
                                    class="bg-black/60 backdrop-blur-md text-white px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-2 shadow-lg w-full lg:w-auto">
                                    <i class="bi bi-camera-video text-indigo-400"></i>
                                    <select v-model="selectedCameraId" @change="startCamera"
                                        class="bg-transparent border-none text-sm focus:ring-0 outline-none cursor-pointer appearance-none w-full font-medium">
                                        <option v-for="cam in cameras" :value="cam.deviceId" class="text-black">
                                            {{ cam.label || 'Camera ' + cam.deviceId }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="relative rounded-xl overflow-hidden bg-black flex-1 shadow-inner w-full">
                                <video ref="video" class="camera-feed w-full h-full object-cover bg-black" autoplay
                                    playsinline muted></video>
                                <div v-if="countdown > 0"
                                    class="absolute inset-0 flex items-center justify-center text-white/90 text-[180px] font-bold tracking-tighter backdrop-blur-sm bg-black/20">
                                    {{ countdown }}
                                </div>
                            </div>

                            <div class="text-center mt-3 flex-shrink-0">
                                <button @click="startCountdown" :disabled="isShooting || shots.length >= 3"
                                    class="group relative inline-flex items-center justify-center px-8 py-3 rounded-full bg-white text-black font-bold text-base tracking-wide disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:scale-105 active:scale-95 shadow-[0_0_40px_rgba(255,255,255,0.3)]">
                                    <i class="bi bi-camera-fill mr-2 opacity-80"></i>
                                    {{ isShooting ? 'กำลังถ่าย...' : 'ถ่ายรูป (' + (shots.length+1) + '/3)' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col h-full min-h-0">
                        <div
                            class="bg-zinc-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-3 h-full flex flex-col shadow-2xl">
                            <h5 class="text-base font-bold text-white mb-2 flex-shrink-0 flex items-center gap-2">
                                <i class="bi bi-images text-indigo-400"></i>
                                รูปที่ถ่าย
                            </h5>
                            <div class="space-y-2 flex-1 overflow-y-auto min-h-0 pr-1 custom-scrollbar">
                                <div v-for="(img, idx) in shots" :key="idx"
                                    class="relative group aspect-[4/3] rounded-xl overflow-hidden border border-white/10 shadow-lg bg-black/50 flex-shrink-0">
                                    <img :src="img"
                                        class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition duration-500">
                                    <div
                                        class="absolute top-2 right-2 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded backdrop-blur-md">
                                        #{{ idx + 1 }}
                                    </div>
                                </div>
                                <div v-if="shots.length === 0"
                                    class="text-center py-12 text-zinc-600 border-2 border-dashed border-white/10 rounded-xl h-32 flex items-center justify-center">
                                    <span class="text-sm">รอถ่ายภาพ...</span>
                                </div>
                            </div>

                            <div v-if="shots.length >= 3" class="space-y-2 pt-3 mt-auto flex-shrink-0">
                                <button @click="finishSession"
                                    class="w-full bg-green-600 hover:bg-green-500 text-white py-2.5 rounded-xl transition flex items-center justify-center gap-2 font-bold shadow-lg shadow-green-500/30 text-sm">
                                    <i class="bi bi-send-check"></i>
                                    ส่งไปห้องปริ้น
                                </button>
                                <button @click="retakeAll"
                                    class="w-full bg-white/5 hover:bg-white/10 border border-white/10 text-white py-2 rounded-xl transition flex items-center justify-center gap-2 font-medium text-xs">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    ถ่ายใหม่ทั้งหมด
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                countdown: 0,
                isShooting: false,
                isLoadingQueue: true,
                toasts: [],
                cameras: [],
                selectedCameraId: null
            },
            computed: {
                filteredQueue() {
                    return this.queue.filter(c =>
                        c.status === 'Registered' ||
                        c.status === 'ลงทะเบียนแล้ว' ||
                        c.status === 'Shooting' ||
                        c.status === 'กำลังถ่าย'
                    );
                }
            },
            mounted() {
                this.fetchQueue();
                this.initCamera();
                setInterval(this.fetchQueue, 5000);
            },
            methods: {
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
                async initCamera() {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                        stream.getTracks().forEach(track => track.stop());

                        const devices = await navigator.mediaDevices.enumerateDevices();
                        this.cameras = devices.filter(d => d.kind === 'videoinput');
                        if (this.cameras.length > 0) {
                            this.selectedCameraId = this.cameras[0].deviceId;
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error', title: 'เข้าถึงกล้องไม่ได้', text: e.message,
                            background: '#1a1a1a', color: '#fff'
                        });
                    }
                },
                startCamera() {
                    if (!this.selectedCameraId || !this.$refs.video) return;
                    const constraints = {
                        video: {
                            deviceId: { exact: this.selectedCameraId },
                            width: { ideal: 1920 }, height: { ideal: 1080 }
                        }
                    };
                    navigator.mediaDevices.getUserMedia(constraints)
                        .then(stream => {
                            this.$refs.video.srcObject = stream;
                            this.$refs.video.play().catch(e => console.error(e));
                        })
                        .catch(e => console.error(e));
                },
                selectCustomer(c) {
                    this.selectedCustomer = c;
                    this.shots = [];

                    // Update status to Shooting immediately when entering the customer room
                    if (c.status === 'ลงทะเบียนแล้ว' || c.status === 'Registered') {
                        this.updateStatus(c.customer_code, 'กำลังถ่าย');
                        c.status = 'กำลังถ่าย';
                    }

                    // Fetch existing shots just in case resuming
                    axios.get(`api.php?action=get_images&id=${c.customer_code}`)
                        .then(res => {
                            if (res.data.images && res.data.images.length > 0) {
                                this.shots = res.data.images;
                            }
                            this.$nextTick(() => { this.startCamera(); });
                        });
                },
                startCountdown() {
                    if (this.isShooting) return;

                    this.isShooting = true;
                    this.countdown = 3;
                    const timer = setInterval(() => {
                        this.countdown--;
                        if (this.countdown === 0) {
                            clearInterval(timer);
                            this.captureShot();
                        }
                    }, 1000);
                },
                captureShot() {
                    const video = this.$refs.video;
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                    ctx.drawImage(video, 0, 0);

                    const shotData = canvas.toDataURL('image/png');
                    axios.post('api.php?action=upload_image', {
                        id: this.selectedCustomer.customer_code,
                        image: shotData,
                        index: this.shots.length + 1
                    }).then(res => {
                        this.shots.push(res.data.url + '?t=' + Date.now());
                        this.isShooting = false;
                    });
                },
                updateStatus(id, status) {
                    axios.post('api.php?action=update_status', { id, status });
                    if (this.selectedCustomer && this.selectedCustomer.customer_code === id) {
                        this.selectedCustomer.status = status;
                    }
                },
                retakeAll() {
                    if (confirm('ต้องการถ่ายใหม่ทั้งหมดใช่หรือไม่? รูปเดิมจะหายไป')) {
                        this.shots = [];
                    }
                },
                finishSession() {
                    this.updateStatus(this.selectedCustomer.customer_code, 'รอปริ้น');
                    this.showToast(`บันทึกรูปของ ${this.selectedCustomer.name} เรียบร้อย`, 'success');
                    this.selectedCustomer = null;
                    this.shots = [];
                    this.fetchQueue();
                },
                resetSession() {
                    // If we are resetting without finishing, revert status to Registered
                    if (this.selectedCustomer && (this.selectedCustomer.status === 'กำลังถ่าย' || this.selectedCustomer.status === 'Shooting')) {
                        // Optimistically update UI
                        this.selectedCustomer.status = 'Registered';
                        // Send update
                        this.updateStatus(this.selectedCustomer.customer_code, 'ลงทะเบียนแล้ว');
                    }
                    this.selectedCustomer = null;
                    this.shots = [];
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