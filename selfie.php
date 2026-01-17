<!DOCTYPE html>
<html>

<head>
    <title>ถ่ายรูปด้วยตนเอง - Photobooth</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Prompt', 'Inter', sans-serif;
            touch-action: pan-y;
            -webkit-user-select: none;
            user-select: none;
        }

        .camera-feed {
            transform: scaleX(-1);
        }

        /* Countdown Animation */
        .countdown {
            font-size: 160px;
            font-weight: bold;
            animation: pulse 0.8s ease-in-out;
            color: white;
            text-shadow: 0 0 60px rgba(99, 102, 241, 1);
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.8;
            }

            50% {
                transform: scale(1.3);
                opacity: 1;
            }
        }

        /* Camera Flash */
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
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        /* Fade transitions */
        .fade-enter-active,
        .fade-leave-active {
            transition: opacity 0.3s;
        }

        .fade-enter,
        .fade-leave-to {
            opacity: 0;
        }
    </style>
</head>

<body
    class="bg-[#0f0f11] text-zinc-100 overflow-hidden bg-[url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop')] bg-cover bg-center">
    <!-- Dark overlay -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm z-0"></div>

    <div id="app" class="relative z-10 h-screen flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="bg-black/60 backdrop-blur-xl border-b border-white/10 p-4 flex-shrink-0">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <i class="bi bi-camera-fill text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">ถ่ายรูปด้วยตนเอง</h1>
                        <p class="text-xs text-zinc-400">Self-Service Photo Booth</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden relative">
            <!-- Screen 1: Queue Selection -->
            <transition name="fade">
                <div v-if="currentScreen === 'queue'" class="absolute inset-0 overflow-y-auto p-6">
                    <div class="max-w-6xl mx-auto">
                        <div class="text-center mb-8">
                            <h2 class="text-4xl font-bold text-white mb-2">เลือกคิวของคุณ</h2>
                            <p class="text-zinc-400">กรุณาเลือกชื่อของคุณเพื่อเริ่มถ่ายรูป</p>
                        </div>

                        <!-- Queue Grid -->
                        <div v-if="queue.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="c in queue" :key="c.id" @click="selectCustomer(c)"
                                class="bg-zinc-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-6 cursor-pointer hover:bg-white/10 hover:border-indigo-500/50 transition-all hover:scale-105 active:scale-95 shadow-xl">
                                <div class="flex flex-col gap-3">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-2xl font-bold text-white">{{ c.name }}</h3>
                                        <div
                                            class="flex items-center gap-2 px-3 py-1 rounded-full bg-green-900/50 border border-green-700/50">
                                            <i class="bi bi-person-check-fill text-green-400"></i>
                                            <span class="text-xs uppercase font-bold text-green-400">พร้อมถ่าย</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-zinc-400">
                                    <i class="bi bi-hash"></i>
                                    <span class="font-mono text-lg">{{ c.customer_code.split('-')[1] ||
                                        c.customer_code }}</span>
                                </div>
                                <div v-if="c.contact" class="flex items-center gap-2 text-zinc-400 text-sm">
                                    <i class="bi bi-telephone"></i>
                                    {{ c.contact }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-20">
                        <i class="bi bi-inbox text-8xl text-zinc-600 mb-4 block"></i>
                        <p class="text-xl text-zinc-500">ไม่มีคิวที่รอถ่ายรูป</p>
                        <p class="text-sm text-zinc-600 mt-2">กรุณาลงทะเบียนก่อนเข้าใช้งาน</p>
                    </div>
                </div>

            </transition>

            <!-- Screen 1.5: Instructions -->
            <transition name="fade">
                <div v-if="currentScreen === 'instructions'"
                    class="absolute inset-0 z-20 flex items-center justify-center bg-black/90 backdrop-blur-xl p-6">
                    <div class="max-w-md w-full text-center">
                        <div
                            class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full mx-auto mb-8 flex items-center justify-center shadow-lg shadow-indigo-500/30 animate-bounce">
                            <i class="bi bi-camera-fill text-5xl text-white"></i>
                        </div>

                        <h2 class="text-4xl font-bold text-white mb-8">คำแนะนำก่อนถ่าย</h2>

                        <div class="space-y-6 mb-10 text-left bg-white/5 p-6 rounded-3xl border border-white/10">
                            <div class="flex items-center gap-4">
                                <span
                                    class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold shrink-0">1</span>
                                <p class="text-xl text-zinc-200">เตรียมตัวให้พร้อม</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span
                                    class="w-8 h-8 rounded-full bg-purple-500 flex items-center justify-center text-white font-bold shrink-0">2</span>
                                <p
                                    class="text-2xl text-white font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                                    ระบบจะถ่ายอัตโนมัติ 3 รูป</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span
                                    class="w-8 h-8 rounded-full bg-pink-500 flex items-center justify-center text-white font-bold shrink-0">3</span>
                                <p class="text-xl text-zinc-200">เปลี่ยนท่าโพสต์ได้เลย!</p>
                            </div>
                        </div>

                        <button @click="startSession"
                            class="w-full bg-white text-black hover:bg-zinc-200 py-4 rounded-2xl transition flex items-center justify-center gap-3 text-xl font-bold shadow-xl hover:scale-105 active:scale-95">
                            <i class="bi bi-play-fill text-3xl"></i>
                            พร้อมแล้ว! เริ่มเลย
                        </button>

                        <button @click="currentScreen = 'queue'; selectedCustomer = null"
                            class="mt-4 text-zinc-500 hover:text-zinc-300 text-sm py-2">
                            ยกเลิก
                        </button>
                    </div>
                </div>
            </transition>

            <!-- Screen 2: Camera -->
            <transition name="fade">
                <div v-if="currentScreen === 'camera'" class="absolute inset-0 flex flex-col bg-black">
                    <!-- Camera Preview (4:3 Landscape - matching print size) -->
                    <div class="flex-1 relative bg-black flex items-center justify-center min-h-0">
                        <div class="relative bg-black max-w-full max-h-full" style="aspect-ratio: 4/3;">
                            <video ref="video" class="camera-feed w-full h-full object-cover" autoplay playsinline
                                muted></video>
                            <canvas ref="canvas" class="hidden"></canvas>

                            <!-- Flash Overlay -->
                            <div v-if="showFlash" class="absolute inset-0 bg-white camera-flash"></div>

                            <!-- Countdown Overlay -->
                            <div v-if="countdown > 0"
                                class="absolute inset-0 flex items-center justify-center bg-black/70 backdrop-blur-sm">
                                <div class="countdown">{{ countdown }}</div>
                            </div>

                            <!-- Photo Counter -->
                            <div
                                class="absolute top-3 left-3 bg-black/80 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/20">
                                <span class="text-lg font-bold flex items-center gap-2">
                                    <i class="bi bi-images text-indigo-400"></i>
                                    {{ photosTaken }}/3
                                </span>
                            </div>

                            <!-- Customer Info -->
                            <div
                                class="absolute top-3 right-3 bg-black/80 backdrop-blur-md px-3 py-1.5 rounded-lg border border-white/20">
                                <p class="text-xs text-zinc-400">กำลังถ่ายรูป</p>
                                <p class="text-sm font-bold">{{ selectedCustomer.name }}</p>
                            </div>

                            <!-- Thumbnails -->
                            <div v-if="shots.length > 0" class="absolute bottom-3 left-3 flex gap-2">
                                <img v-for="(shot, i) in shots" :key="i" :src="shot"
                                    class="w-16 h-16 object-cover rounded-lg border-2 border-white/30 shadow-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Compact Controls -->
                    <div class="bg-black/90 backdrop-blur-xl border-t border-white/10 p-3 flex-shrink-0">
                        <div class="max-w-4xl mx-auto flex items-center justify-center gap-6">
                            <button @click="cancelSession"
                                class="bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl transition flex items-center gap-2">
                                <i class="bi bi-arrow-left"></i>
                                <span class="text-sm">ยกเลิก</span>
                            </button>

                            <!-- Capture Button -->
                            <button @click="startCapture" :disabled="isCapturing || photosTaken >= 3"
                                class="w-20 h-20 rounded-full flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="isCapturing ? 'bg-indigo-600 animate-pulse' : 'bg-gradient-to-br from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-600/50 hover:scale-110 active:scale-95'">
                                <i class="bi bi-camera-fill text-3xl text-white"></i>
                            </button>

                            <div class="w-24 text-center">
                                <p class="text-xs text-zinc-500">กดเพื่อถ่าย</p>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Screen 3: Review -->
            <transition name="fade">
                <div v-if="currentScreen === 'review'" class="absolute inset-0 overflow-y-auto p-6 bg-black/90">
                    <div class="max-w-6xl mx-auto">
                        <div class="text-center mb-8">
                            <h2 class="text-4xl font-bold text-white mb-2">ตรวจสอบรูปถ่าย</h2>
                            <p class="text-zinc-400">กรุณาตรวจสอบรูปถ่ายของคุณ</p>
                        </div>

                        <!-- Photos Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div v-for="(shot, i) in shots" :key="i" class="relative">
                                <img :src="shot"
                                    class="w-full h-auto object-cover rounded-2xl border-2 border-white/20 shadow-2xl">
                                <div class="absolute top-3 left-3 bg-black/80 backdrop-blur-md px-3 py-1 rounded-full">
                                    <span class="text-sm font-bold">รูปที่ {{ i + 1 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-4 justify-center">
                            <button @click="retakePhotos"
                                class="bg-white/10 hover:bg-white/20 text-white px-8 py-4 rounded-xl transition flex items-center gap-3 text-lg font-semibold">
                                <i class="bi bi-arrow-clockwise text-2xl"></i>
                                ถ่ายใหม่
                            </button>
                            <button @click="confirmPhotos"
                                class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white px-12 py-4 rounded-xl transition flex items-center gap-3 text-lg font-semibold shadow-lg shadow-indigo-600/50">
                                <i class="bi bi-check-circle text-2xl"></i>
                                ยืนยันรูปถ่าย
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Success Modal -->
        <div v-if="showSuccessModal"
            class="fixed inset-0 bg-black/90 backdrop-blur-md flex items-center justify-center z-50">
            <div
                class="bg-zinc-900/90 backdrop-blur-xl border border-white/10 rounded-3xl p-12 max-w-2xl mx-4 text-center">
                <div
                    class="w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full mx-auto mb-6 flex items-center justify-center">
                    <i class="bi bi-check-lg text-6xl text-white"></i>
                </div>
                <h2 class="text-5xl font-bold text-white mb-4">เสร็จสิ้น!</h2>
                <p class="text-2xl text-zinc-300 mb-2">กรุณารอรับรูปที่จุดปริ้น</p>
                <p class="text-xl text-zinc-500 font-mono mb-8">รหัสคิว: {{ displayCustomerCode }}</p>
                <button @click="closeSuccessModal"
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white px-12 py-4 rounded-xl transition text-lg font-semibold shadow-lg">
                    ตกลง
                </button>
            </div>
        </div>
    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                currentScreen: 'queue',  // 'queue' | 'camera' | 'review'
                queue: [],
                selectedCustomer: null,
                shots: [],
                isCapturing: false,
                countdown: 0,
                photosTaken: 0,
                showSuccessModal: false,
                stream: null,
                showFlash: false
            },
            computed: {
                displayCustomerCode() {
                    if (!this.selectedCustomer || !this.selectedCustomer.customer_code) return '';
                    const parts = this.selectedCustomer.customer_code.split('-');
                    return parts.length > 1 ? parts[1] : this.selectedCustomer.customer_code;
                }
            },
            mounted() {
                this.fetchQueue();
                setInterval(this.fetchQueue, 5000);
            },
            methods: {
                fetchQueue() {
                    axios.get('api.php?action=get_queue')
                        .then(res => {
                            this.queue = res.data
                                .filter(c => c.status === 'Registered' || c.status === 'ลงทะเบียนแล้ว')
                                .sort((a, b) => a.id - b.id);
                        })
                        .catch(err => console.error('Error fetching queue:', err));
                },

                selectCustomer(customer) {
                    this.selectedCustomer = customer;
                    this.shots = [];
                    this.photosTaken = 0;
                    this.currentScreen = 'instructions';

                    // Update status to Shooting
                    axios.post('api.php?action=update_status', {
                        id: customer.customer_code,
                        status: 'กำลังถ่าย'
                    }).catch(console.error);
                },

                startSession() {
                    this.currentScreen = 'camera';
                    this.$nextTick(() => {
                        this.startCamera();
                    });
                },

                async startCamera() {
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'user', width: 1280, height: 720 }
                        });
                        this.$refs.video.srcObject = this.stream;
                    } catch (err) {
                        Swal.fire({
                            title: 'ไม่สามารถเข้าถึงกล้อง',
                            text: 'กรุณาอนุญาตการใช้งานกล้องในการตั้งค่า',
                            icon: 'error',
                            confirmButtonColor: '#4f46e5'
                        });
                        this.resetSession();
                    }
                },

                stopCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                        this.stream = null;
                    }
                },

                async startCapture() {
                    if (this.isCapturing || this.photosTaken >= 3) return;

                    this.isCapturing = true;
                    this.shots = [];
                    this.photosTaken = 0;

                    // Auto-capture 3 photos
                    for (let i = 0; i < 3; i++) {
                        // Countdown for EVERY shot
                        for (let c = 3; c > 0; c--) {
                            this.countdown = c;
                            await this.sleep(1000);
                        }
                        this.countdown = 0;

                        await this.capturePhoto();
                        this.photosTaken++;

                        if (i < 2) {
                            await this.sleep(2500); // Brief pause before next countdown starts
                        }
                    }

                    this.isCapturing = false;
                    this.stopCamera();
                    this.currentScreen = 'review';
                },

                async capturePhoto() {
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    const ctx = canvas.getContext('2d');

                    // Target size for print (4:3 landscape)
                    const targetWidth = 520;
                    const targetHeight = 390;

                    canvas.width = targetWidth;
                    canvas.height = targetHeight;

                    // Calculate crop dimensions to fit 4:3 aspect ratio
                    const videoAspect = video.videoWidth / video.videoHeight;
                    const targetAspect = targetWidth / targetHeight; // 4:3

                    let sw, sh, sx, sy;
                    if (videoAspect > targetAspect) {
                        // Video is wider, crop width
                        sh = video.videoHeight;
                        sw = sh * targetAspect;
                        sx = (video.videoWidth - sw) / 2;
                        sy = 0;
                    } else {
                        // Video is taller, crop height  
                        sw = video.videoWidth;
                        sh = sw / targetAspect;
                        sx = 0;
                        sy = (video.videoHeight - sh) / 2;
                    }

                    // Mirror the image and draw cropped
                    ctx.translate(targetWidth, 0);
                    ctx.scale(-1, 1);
                    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, targetWidth, targetHeight);
                    ctx.setTransform(1, 0, 0, 1, 0, 0);

                    // Flash effect
                    this.showFlash = true;
                    await this.sleep(300);
                    this.showFlash = false;

                    const dataUrl = canvas.toDataURL('image/png');
                    this.shots.push(dataUrl);
                },

                async confirmPhotos() {
                    const customerId = this.selectedCustomer.customer_code;

                    try {
                        // Show loading
                        Swal.fire({
                            title: 'กำลังบันทึกรูปภาพ...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        // Upload all 3 photos
                        for (let i = 0; i < 3; i++) {
                            await axios.post('api.php?action=upload_image', {
                                image: this.shots[i],
                                id: customerId,
                                index: i + 1
                            });
                        }

                        // Update status to WaitingPrint
                        await axios.post('api.php?action=update_status', {
                            id: customerId,
                            status: 'รอปริ้น'
                        });

                        Swal.close();
                        this.showSuccessModal = true;
                    } catch (err) {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถบันทึกรูปภาพได้ กรุณาลองใหม่อีกครั้ง',
                            icon: 'error',
                            confirmButtonColor: '#d33'
                        });
                    }
                },

                retakePhotos() {
                    this.shots = [];
                    this.photosTaken = 0;
                    this.currentScreen = 'camera';
                    this.$nextTick(() => {
                        this.startCamera();
                    });
                },

                cancelSession() {
                    // Revert status to Registered if valid customer selected
                    if (this.selectedCustomer) {
                        axios.post('api.php?action=update_status', {
                            id: this.selectedCustomer.customer_code,
                            status: 'ลงทะเบียนแล้ว'
                        }).catch(console.error);
                    }
                    this.resetSession();
                },

                resetSession() {
                    this.stopCamera();
                    this.selectedCustomer = null;
                    this.shots = [];
                    this.photosTaken = 0;
                    this.countdown = 0;
                    this.isCapturing = false;
                    this.currentScreen = 'queue';
                    this.fetchQueue();
                },

                closeSuccessModal() {
                    this.showSuccessModal = false;
                    this.resetSession();
                },

                sleep(ms) {
                    return new Promise(resolve => setTimeout(resolve, ms));
                }
            }
        });
    </script>
</body>

</html>