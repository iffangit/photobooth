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
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            padding: 2rem;
        }
    </style>
</head>
<body class="bg-zinc-50 text-zinc-900 overflow-hidden">
    <div id="app" class="flex h-screen bg-zinc-50">
        <!-- Sidebar -->
        <div class="w-80 bg-black text-white shadow-2xl flex flex-col z-10">
            <div class="p-6 border-b border-white/10">
                <h1 class="text-2xl font-bold flex items-center gap-2 tracking-tight">
                    <i class="bi bi-camera-fill"></i>
                    PHOTODASH
                </h1>
                <button @click="fetchQueue" class="mt-4 w-full bg-white text-black font-semibold hover:bg-zinc-200 px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="bi bi-arrow-clockwise"></i>
                    รีเฟรชคิว
                </button>
            </div>
            
            <div class="flex-1 overflow-auto p-4 space-y-2">
                <div v-for="c in queue" :key="c.id" 
                     @click="selectCustomer(c)"
                     class="p-4 rounded-xl cursor-pointer transition-all duration-200 border"
                     :class="selectedCustomer?.customer_code === c.customer_code ? 'bg-white text-black border-transparent shadow-lg transform scale-102' : 'bg-transparent border-zinc-800 hover:bg-zinc-900 hover:border-zinc-700 text-zinc-400 hover:text-white'">
                    <div class="flex justify-between items-center mb-1">
                        <strong class="font-semibold">{{ c.name }}</strong>
                        <span class="px-2 py-0.5 rounded text-[10px] uppercase tracking-wider font-bold"
                              :class="{
                                  'bg-zinc-800 text-zinc-300 border border-zinc-700': c.status.startsWith('Registered'),
                                  'bg-white text-black': c.status.startsWith('Shooting'),
                                  'bg-zinc-400 text-black': c.status.startsWith('Ready')
                              }">
                            {{ c.status }}
                        </span>
                    </div>
                    <small class="text-xs font-mono opacity-60">{{ c.customer_code }}</small>
                </div>
            </div>
        </div>

        <!-- Main Area -->
        <div class="flex-1 overflow-auto p-8 bg-zinc-50">
            <div v-if="!selectedCustomer" class="flex items-center justify-center h-full">
                <div class="text-center opacity-40">
                    <i class="bi bi-person-plus text-6xl mb-4 text-black"></i>
                    <h3 class="text-2xl font-light">เลือกลูกค้าเพื่อเริ่มถ่ายรูป</h3>
                </div>
            </div>

            <div v-else>
                <!-- Header -->
                <div class="bg-white rounded-xl border border-zinc-200 p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-3xl font-bold text-black flex items-center gap-3">
                            <i class="bi bi-person-circle"></i>
                            {{ selectedCustomer.name }}
                        </h2>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-3 bg-zinc-50 border border-zinc-100 px-6 py-3 rounded-xl">
                                <button @click="decreaseQty" 
                                        class="w-10 h-10 bg-white border border-zinc-300 text-black rounded-lg hover:border-black transition flex items-center justify-center">
                                    <i class="bi bi-dash text-xl"></i>
                                </button>
                                <span class="text-2xl font-bold text-black min-w-[50px] text-center">{{ selectedCustomer.quantity }}</span>
                                <button @click="increaseQty" 
                                        class="w-10 h-10 bg-black text-white rounded-lg hover:bg-zinc-800 transition flex items-center justify-center shadow-lg shadow-zinc-200">
                                    <i class="bi bi-plus text-xl"></i>
                                </button>
                                <span class="text-zinc-500 font-medium">แท่ง</span>
                            </div>
                            <button @click="resetSession" 
                                    class="border border-red-200 text-red-500 hover:bg-red-50 px-6 py-3 rounded-xl transition flex items-center gap-2">
                                <i class="bi bi-x-circle"></i>
                                จบเซสชัน
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STAGE 1: CAPTURE -->
                <div v-show="currentStage === 'Capture'" class="grid grid-cols-3 gap-6">
                    <div class="col-span-2 space-y-4">
                        <div class="bg-white rounded-xl border border-zinc-200 p-4">
                            <div class="mb-3 flex items-center gap-2">
                                <i class="bi bi-camera-video text-black"></i>
                                <label class="text-sm text-zinc-600 font-medium">เลือกกล้อง:</label>
                                <select v-model="selectedCameraId" @change="startCamera"
                                        class="px-4 py-2 rounded-lg border border-zinc-200 focus:border-black focus:ring-0 outline-none bg-zinc-50">
                                    <option v-for="cam in cameras" :value="cam.deviceId">
                                        {{ cam.label || 'Camera ' + cam.deviceId }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="relative rounded-lg overflow-hidden bg-black aspect-video">
                                <video ref="video" class="camera-feed w-full h-full object-cover" 
                                       autoplay playsinline muted width="1280" height="720"></video>
                                <div v-if="countdown > 0" 
                                     class="absolute inset-0 flex items-center justify-center text-white text-[150px] font-bold tracking-tighter">
                                    {{ countdown }}
                                </div>
                            </div>
                            
                            <div class="text-center mt-6">
                                <button @click="startCountdown" 
                                        :disabled="isShooting || shots.length >= 3"
                                        class="bg-black text-white px-12 py-4 rounded-full text-lg font-semibold hover:bg-zinc-800 hover:scale-105 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-3 mx-auto shadow-xl shadow-zinc-200">
                                    <i class="bi bi-camera text-2xl"></i>
                                    {{ isShooting ? 'กำลังถ่าย...' : 'ถ่ายรูป (' + (shots.length+1) + '/3)' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-white rounded-xl border border-zinc-200 p-6 h-full">
                            <h5 class="text-lg font-bold text-black mb-4 flex items-center gap-2">
                                <i class="bi bi-images"></i>
                                รูปที่ถ่าย
                            </h5>
                            <div class="space-y-3">
                                <div v-for="(img, idx) in shots" :key="idx" 
                                     class="border border-zinc-200 rounded-lg p-1 bg-zinc-50">
                                    <img :src="img" class="w-full rounded shadow-sm">
                                </div>
                                
                                <div v-if="shots.length === 3" class="space-y-3 pt-4">
                                    <button @click="processStrip" 
                                            class="w-full bg-black text-white py-3 rounded-xl hover:bg-zinc-800 transition flex items-center justify-center gap-2 font-semibold shadow-lg shadow-zinc-200">
                                        <i class="bi bi-check-circle"></i>
                                        ดำเนินการพิมพ์
                                    </button>
                                    <button @click="retakeAll" 
                                            class="w-full bg-white border border-zinc-200 text-zinc-700 py-3 rounded-xl hover:bg-zinc-50 transition flex items-center justify-center gap-2">
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
                        <div class="bg-white rounded-xl border border-zinc-200 p-6">
                            <h5 class="text-xl font-bold text-black mb-4 flex items-center gap-2">
                                <i class="bi bi-palette"></i>
                                การตกแต่ง
                            </h5>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-zinc-500 mb-2 block">พื้นหลัง</label>
                                    <select v-model="backgroundType" @change="regenerateStrip"
                                            class="w-full px-4 py-2 rounded-lg border border-zinc-200 focus:border-black focus:ring-0 outline-none bg-zinc-50">
                                        <option value="color">สีพื้น</option>
                                        <option value="image">รูปภาพ</option>
                                    </select>
                                </div>
                                
                                <div v-if="backgroundType === 'color'">
                                    <label class="text-sm font-medium text-zinc-500 mb-2 block">เลือกสี</label>
                                    <input type="color" v-model="backgroundColor" @change="regenerateStrip"
                                           class="w-full h-10 rounded cursor-pointer border-0 p-0">
                                </div>
                                
                                <div v-if="backgroundType === 'image'">
                                    <label class="text-sm font-medium text-zinc-500 mb-2 block">อัปโหลดรูปพื้นหลัง</label>
                                    <input type="file" accept="image/*" @change="handleBackgroundUpload"
                                           class="w-full px-4 py-2 border border-zinc-200 rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:bg-black file:text-white file:text-xs hover:file:bg-zinc-800">
                                </div>
                                
                                <div>
                                    <label class="text-sm font-medium text-zinc-500 mb-2 block">ข้อความวันที่</label>
                                    <input type="text" v-model="dateText" @input="regenerateStrip"
                                           class="w-full px-4 py-2 rounded-lg border border-zinc-200 focus:border-black focus:ring-0 outline-none bg-zinc-50">
                                </div>
                                
                                <button @click="regenerateStrip" 
                                        class="w-full bg-zinc-100 text-zinc-800 py-3 rounded-xl hover:bg-zinc-200 transition flex items-center justify-center gap-2 font-medium">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    รีเฟรชตัวอย่าง
                                </button>
                                
                                <hr class="border-zinc-100">
                                
                                <button @click="printA4" 
                                        class="w-full bg-black text-white py-4 rounded-xl hover:bg-zinc-800 transition flex items-center justify-center gap-2 font-bold text-lg shadow-xl shadow-zinc-200">
                                    <i class="bi bi-printer text-xl"></i>
                                    พิมพ์
                                </button>
                                
                                <button @click="currentStage = 'Capture'" 
                                        class="w-full text-zinc-400 hover:text-black py-2 text-sm transition flex items-center justify-center gap-2">
                                    <i class="bi bi-arrow-left"></i>
                                    ย้อนกลับไปถ่ายใหม่
                                </button>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl border border-zinc-200 p-6">
                            <h6 class="text-sm font-semibold text-zinc-400 mb-3 flex items-center gap-2 uppercase tracking-wider">
                                <i class="bi bi-eye"></i>
                                Preview
                            </h6>
                            <img :src="finalStripUrl" class="w-full shadow-lg rounded-lg border border-zinc-100">
                        </div>
                    </div>

                    <div class="col-span-2">
                        <div class="bg-white rounded-xl border border-zinc-200 p-6 h-full flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h5 class="text-xl font-bold text-black flex items-center gap-2">
                                    <i class="bi bi-file-earmark-text"></i>
                                    ตัวอย่าง A4
                                </h5>
                                <span class="bg-zinc-100 text-zinc-500 text-xs px-2 py-1 rounded">Print Layout</span>
                            </div>
                            
                            <div class="bg-zinc-100 rounded-xl flex-1 flex items-start justify-center overflow-y-auto border border-zinc-200 border-dashed">
                                <div id="print-wrapper">
                                    <div class="print-layout shadow-2xl" 
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
