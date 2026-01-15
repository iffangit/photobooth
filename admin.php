<!DOCTYPE html>
<html>
<head>
    <title>Admin - Photobooth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Prompt', 'Inter', sans-serif; }
        .camera-feed { transform: scaleX(-1); }
        #stripCanvas { display: none; }
        
        /* A4 Preview Styles */
        .print-layout { 
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
        }
        .print-item { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            overflow: hidden; 
            border: 1px dashed #e5e7eb; 
            width: 100%;
            height: 100%;
        }
        .print-item img { 
            height: 100%; 
            width: auto; 
            max-width: 100%;
            object-fit: contain; 
        }
        
        /* Container for preview */
        #print-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <div id="app" class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-80 bg-gradient-to-b from-indigo-600 to-purple-700 text-white shadow-2xl flex flex-col">
            <div class="p-6 border-b border-white/20">
                <h1 class="text-2xl font-bold flex items-center gap-2">
                    <i class="bi bi-camera-fill text-yellow-300"></i>
                    คิวถ่ายรูป
                </h1>
                <button @click="fetchQueue" class="mt-3 w-full bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i>
                    รีเฟรชคิว
                </button>
            </div>
            
            <div class="flex-1 overflow-auto p-4 space-y-2">
                <div v-for="c in queue" :key="c.id" 
                     @click="selectCustomer(c)"
                     class="p-4 rounded-xl cursor-pointer transition-all duration-200 hover:scale-105"
                     :class="selectedCustomer?.customer_code === c.customer_code ? 'bg-white text-indigo-700 shadow-lg' : 'bg-white/10 hover:bg-white/20'">
                    <div class="flex justify-between items-center">
                        <strong class="font-semibold">{{ c.name }}</strong>
                        <span class="px-3 py-1 rounded-full text-xs font-medium"
                              :class="{
                                  'bg-yellow-400 text-black': c.status.startsWith('Registered'),
                                  'bg-blue-400 text-white': c.status.startsWith('Shooting'),
                                  'bg-green-400 text-white': c.status.startsWith('Ready')
                              }">
                            {{ c.status }}
                        </span>
                    </div>
                    <small class="opacity-70 text-xs">{{ c.customer_code }}</small>
                </div>
            </div>
        </div>

        <!-- Main Area -->
        <div class="flex-1 overflow-auto p-8">
            <div v-if="!selectedCustomer" class="flex items-center justify-center h-full">
                <div class="text-center">
                    <i class="bi bi-person-plus text-6xl text-indigo-300 mb-4"></i>
                    <h3 class="text-2xl font-light text-gray-600">เลือกลูกค้าเพื่อเริ่มถ่ายรูป</h3>
                </div>
            </div>

            <div v-else>
                <!-- Header -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                            <i class="bi bi-person-circle text-indigo-600"></i>
                            {{ selectedCustomer.name }}
                        </h2>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-3 bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-3 rounded-xl">
                                <button @click="decreaseQty" 
                                        class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 text-white rounded-lg hover:scale-110 transition flex items-center justify-center shadow-md">
                                    <i class="bi bi-dash text-xl"></i>
                                </button>
                                <span class="text-2xl font-bold text-gray-800 min-w-[50px] text-center">{{ selectedCustomer.quantity }}</span>
                                <button @click="increaseQty" 
                                        class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-lg hover:scale-110 transition flex items-center justify-center shadow-md">
                                    <i class="bi bi-plus text-xl"></i>
                                </button>
                                <span class="text-gray-600 font-medium">แท่ง</span>
                            </div>
                            <button @click="resetSession" 
                                    class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-6 py-3 rounded-xl hover:shadow-xl transition flex items-center gap-2">
                                <i class="bi bi-x-circle"></i>
                                จบเซสชัน
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STAGE 1: CAPTURE -->
                <div v-show="currentStage === 'Capture'" class="grid grid-cols-3 gap-6">
                    <div class="col-span-2 space-y-4">
                        <div class="bg-white rounded-2xl shadow-lg p-4">
                            <div class="mb-3 flex items-center gap-2">
                                <i class="bi bi-camera-video text-indigo-600"></i>
                                <label class="text-sm text-gray-600 font-medium">เลือกกล้อง:</label>
                                <select v-model="selectedCameraId" @change="startCamera"
                                        class="px-4 py-2 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                                    <option v-for="cam in cameras" :value="cam.deviceId">
                                        {{ cam.label || 'Camera ' + cam.deviceId }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="relative rounded-xl overflow-hidden bg-black shadow-2xl">
                                <video ref="video" class="camera-feed w-full h-[500px] object-cover" 
                                       autoplay playsinline muted width="1280" height="720"></video>
                                <div v-if="countdown > 0" 
                                     class="absolute inset-0 flex items-center justify-center text-white text-9xl font-bold animate-pulse">
                                    {{ countdown }}
                                </div>
                            </div>
                            
                            <div class="text-center mt-6">
                                <button @click="startCountdown" 
                                        :disabled="isShooting || shots.length >= 3"
                                        class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-12 py-4 rounded-full text-lg font-semibold hover:shadow-2xl hover:scale-105 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-3 mx-auto">
                                    <i class="bi bi-camera text-2xl"></i>
                                    {{ isShooting ? 'กำลังถ่าย...' : 'ถ่ายรูป (' + (shots.length+1) + '/3)' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h5 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="bi bi-images text-indigo-600"></i>
                                รูปที่ถ่าย
                            </h5>
                            <div class="space-y-3">
                                <div v-for="(img, idx) in shots" :key="idx" 
                                     class="border-2 border-indigo-100 rounded-xl p-1 bg-gradient-to-br from-indigo-50 to-purple-50">
                                    <img :src="img" class="w-full rounded-lg shadow">
                                </div>
                                
                                <div v-if="shots.length === 3" class="space-y-2 pt-4">
                                    <button @click="processStrip" 
                                            class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-3 rounded-xl hover:shadow-xl transition flex items-center justify-center gap-2 font-semibold">
                                        <i class="bi bi-check-circle"></i>
                                        ดำเนินการพิมพ์
                                    </button>
                                    <button @click="retakeAll" 
                                            class="w-full bg-gray-100 text-gray-700 py-3 rounded-xl hover:bg-gray-200 transition flex items-center justify-center gap-2">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        ถ่ายใหม่ทั้งหมด
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STAGE 2: PREVIEW & PRINT -->
                <div v-show="currentStage === 'Print'" class="grid grid-cols-3 gap-6">
                    <div class="space-y-4">
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h5 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="bi bi-palette text-indigo-600"></i>
                                การตกแต่ง
                            </h5>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">พื้นหลัง</label>
                                    <select v-model="backgroundType" @change="regenerateStrip"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                                        <option value="color">สีพื้น</option>
                                        <option value="image">รูปภาพ</option>
                                    </select>
                                </div>
                                
                                <div v-if="backgroundType === 'color'">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">เลือกสี</label>
                                    <input type="color" v-model="backgroundColor" @change="regenerateStrip"
                                           class="w-full h-12 rounded-lg cursor-pointer">
                                </div>
                                
                                <div v-if="backgroundType === 'image'">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">อัปโหลดรูปพื้นหลัง</label>
                                    <input type="file" accept="image/*" @change="handleBackgroundUpload"
                                           class="w-full px-4 py-2 border border-gray-200 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">ข้อความวันที่</label>
                                    <input type="text" v-model="dateText" @input="regenerateStrip"
                                           class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                                </div>
                                
                                <button @click="regenerateStrip" 
                                        class="w-full bg-indigo-100 text-indigo-700 py-3 rounded-xl hover:bg-indigo-200 transition flex items-center justify-center gap-2 font-semibold">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    รีเฟรชตัวอย่าง
                                </button>
                                
                                <hr class="border-gray-200">
                                
                                <button @click="printA4" 
                                        class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 text-white py-4 rounded-xl hover:shadow-2xl transition flex items-center justify-center gap-2 font-bold text-lg">
                                    <i class="bi bi-printer text-xl"></i>
                                    พิมพ์
                                </button>
                                
                                <button @click="currentStage = 'Capture'" 
                                        class="w-full bg-gray-100 text-gray-700 py-3 rounded-xl hover:bg-gray-200 transition flex items-center justify-center gap-2">
                                    <i class="bi bi-arrow-left"></i>
                                    ถ่ายใหม่
                                </button>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h6 class="text-sm font-semibold text-gray-600 mb-3 flex items-center gap-2">
                                <i class="bi bi-eye"></i>
                                ตัวอย่างขนาดเล็ก
                            </h6>
                            <img :src="finalStripUrl" class="w-full shadow-lg rounded-lg">
                        </div>
                    </div>

                    <div class="col-span-2">
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h5 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="bi bi-file-earmark-text text-indigo-600"></i>
                                ตัวอย่าง A4
                            </h5>
                            <div class="bg-gray-100 p-4 rounded-xl">
                                <div id="print-wrapper">
                                    <div class="print-layout" 
                                         v-for="p in Math.ceil((parseInt(selectedCustomer.quantity) || 1) / 6)" 
                                         :key="p">
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
    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                queue: [],
                selectedCustomer: null,
                currentStage: 'Capture',
                shots: [],
                countdown: 0,
                isShooting: false,
                dateText: new Date().toLocaleDateString('en-GB'),
                backgroundType: 'color', 
                backgroundColor: '#ffeaa7',
                customBgImage: null,
                finalStripUrl: null,
                cameras: [],
                selectedCameraId: null
            },
            mounted() {
                this.fetchQueue();
                this.initCamera();
                setInterval(this.fetchQueue, 5000);
            },
            methods: {
                getPageCount(page) {
                    const total = parseInt(this.selectedCustomer.quantity) || 1;
                    const remaining = total - ((page - 1) * 6);
                    return Math.min(6, remaining);
                },
                fetchQueue() {
                    axios.get('api.php?action=get_queue')
                        .then(res => { 
                            // Sort by ID descending (newest first)
                            this.queue = res.data.sort((a, b) => b.id - a.id); 
                        })
                        .catch(err => console.error(err));
                },
                async initCamera() {
                    try {
                        await navigator.mediaDevices.getUserMedia({ video: true });
                        const devices = await navigator.mediaDevices.enumerateDevices();
                        this.cameras = devices.filter(d => d.kind === 'videoinput');
                        if (this.cameras.length > 0) {
                            this.selectedCameraId = this.cameras[0].deviceId;
                        } else {
                            alert('No cameras found!');
                        }
                    } catch (e) {
                         alert('Camera Error: ' + e.message + '. Please allow camera access.');
                    }
                },
                startCamera() {
                    if (!this.selectedCameraId || !this.$refs.video) {
                        return;
                    }
                    
                    const constraints = { 
                        video: { 
                            deviceId: { exact: this.selectedCameraId },
                            width: { ideal: 1280 }, 
                            height: { ideal: 720 } 
                        } 
                    };

                    navigator.mediaDevices.getUserMedia(constraints)
                        .then(stream => { 
                            this.$refs.video.srcObject = stream;
                            this.$refs.video.play()
                                .catch(e => console.error('Play error:', e));
                        })
                        .catch(e => {
                            alert('Could not start this camera: ' + e.message);
                        });
                },
                selectCustomer(c) {
                    this.selectedCustomer = c;
                    this.shots = [];
                    axios.get(`api.php?action=get_images&id=${c.customer_code}`)
                        .then(res => {
                            if (res.data.images && res.data.images.length > 0) {
                                this.shots = res.data.images;
                                this.currentStage = 'Print';
                                setTimeout(() => this.drawStrip(), 500);
                            } else {
                                this.currentStage = 'Capture';
                                this.updateStatus(c.customer_code, 'Shooting');
                                this.$nextTick(() => {
                                    this.startCamera();
                                });
                            }
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
                    });
                    
                    this.isShooting = false;
                },
                updateStatus(id, status) {
                    axios.post('api.php?action=update_status', { id, status });
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
                retakeAll() { this.shots = []; },
                processStrip() {
                    this.currentStage = 'Print';
                    setTimeout(() => this.drawStrip(), 100);
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
                        if (this.backgroundType === 'color') {
                             ctx.fillStyle = 'rgba(255,255,255,0.3)';
                             for(let i=0; i<10; i++) {
                                ctx.beginPath();
                                ctx.arc(Math.random()*600, Math.random()*1800, 20 + Math.random()*30, 0, 2*Math.PI);
                                ctx.fill();
                             }
                        }
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
                            ctx.save();
                            ctx.fillStyle = 'white';
                            ctx.fillRect(x-10, y-10, w+20, h+20);
                            
                            // Calculate aspect ratios
                            const sourceRatio = img.width / img.height;
                            const targetRatio = w / h;
                            
                            let sx = 0, sy = 0, sw = img.width, sh = img.height;
                            
                            // Center crop (cover)
                            if (sourceRatio > targetRatio) {
                                // Source is wider than target: Crop width
                                sw = img.height * targetRatio;
                                sx = (img.width - sw) / 2;
                            } else {
                                // Source is taller than target: Crop height
                                sh = img.width / targetRatio;
                                sy = (img.height - sh) / 2;
                            }
                            
                            ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
                            ctx.restore();
                        });
                        
                        ctx.shadowColor="transparent";
                        ctx.shadowBlur=0;
                        ctx.fillStyle = 'white';
                        
                        ctx.font = '500 32px "Prompt", sans-serif';
                        ctx.textAlign = 'left';
                        ctx.fillText(this.dateText, 40, 1720);
                        
                        if(this.selectedCustomer) {
                            ctx.font = '600 36px "Prompt", sans-serif';
                            ctx.textAlign = 'right';
                            ctx.fillText(this.selectedCustomer.name, 560, 1720);
                        }
                        this.finalStripUrl = canvas.toDataURL('image/png');
                    });
                },
                regenerateStrip() { this.drawStrip(); },
                printA4() {
                    this.updateStatus(this.selectedCustomer.customer_code, 'Completed');
                    const element = document.getElementById('print-wrapper');
                    this.printWindow(element.outerHTML);
                },
                printWindow(html) {
                    try {
                         const printWindow = window.open('', '', 'height=600,width=800');
                         if (!printWindow) {
                             alert('Pop-up blocked! Please allow pop-ups for this site.');
                             return;
                         }
                         
                         const css = '<style>' +
                            '@page { size: A4; margin: 0; }' +
                            'body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; }' +
                            '.print-layout { ' +
                                'width: 210mm; ' +
                                'height: 297mm; ' +
                                'padding: 10mm;' +
                                'display: grid; ' +
                                'grid-template-columns: repeat(3, 1fr); ' +
                                'grid-template-rows: repeat(2, 1fr); ' +
                                'gap: 5mm; ' +
                                'box-sizing: border-box;' +
                                'page-break-after: always;' +
                                'break-after: page;' +
                            '}' +
                            '.print-layout:last-child { page-break-after: auto; break-after: auto; }' +
                            '.print-item { ' +
                                'display: flex; ' +
                                'justify-content: center; ' +
                                'align-items: center; ' +
                                'overflow: hidden; ' +
                            '}' +
                            '.print-item img { ' +
                                'height: 100%; ' +
                                'width: auto; ' +
                                'max-width: 100%;' +
                                'object-fit: contain; ' +
                            '}' +
                        '</style>';

                         printWindow.document.write('<html><head><title>Print</title>');
                         printWindow.document.write(css);
                         printWindow.document.write('</head><body>');
                         printWindow.document.write(html);
                         printWindow.document.close();
                         printWindow.focus();
                         
                         setTimeout(() => {
                             printWindow.print();
                             printWindow.close();
                         }, 500);
                    } catch (e) {
                        console.error('Print Error:', e);
                        alert('Print failed: ' + e.message);
                    }
                },
                resetSession() { this.selectedCustomer = null; this.shots = []; }
            }
        });
    </script>
</body>
</html>
