class MSJModal {
    static init() {
        if (document.getElementById('msj-modal-container')) return;

        const container = document.createElement('div');
        container.id = 'msj-modal-container';
        container.className = 'fixed inset-0 z-[9999] hidden flex items-center justify-center p-4';
        container.innerHTML = `
            <!-- Backdrop -->
            <div id="msj-modal-backdrop" class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity duration-300 opacity-0"></div>
            
            <!-- Modal Card -->
            <div id="msj-modal-card" class="relative bg-zinc-900/90 backdrop-blur-xl border border-white/10 rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl transform scale-95 opacity-0 transition-all duration-300">
                <!-- Icon -->
                <div id="msj-modal-icon" class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center text-4xl shadow-lg transition-transform duration-500 hover:scale-110">
                    <i id="msj-icon-class" class="bi"></i>
                </div>

                <!-- Title -->
                <h3 id="msj-modal-title" class="text-2xl font-bold text-white mb-2"></h3>
                
                <!-- Text -->
                <div id="msj-modal-text" class="text-zinc-400 mb-8 text-sm leading-relaxed"></div>

                <!-- Buttons -->
                <div id="msj-modal-buttons" class="flex flex-col gap-3">
                    <button id="msj-btn-confirm" class="w-full py-3.5 rounded-xl font-bold text-white shadow-lg transition-all active:scale-95"></button>
                    <button id="msj-btn-cancel" class="hidden w-full py-3.5 rounded-xl border border-white/10 text-zinc-400 hover:text-white hover:bg-white/5 font-medium transition-all"></button>
                </div>
            </div>
        `;
        document.body.appendChild(container);

        // Bind clicks
        document.getElementById('msj-btn-cancel').onclick = () => MSJModal.close();
    }

    static show({ title, text, icon = 'info', confirmText = 'ตกลง', showCancel = false, cancelText = 'ยกเลิก', onConfirm = null }) {
        this.init();

        const container = document.getElementById('msj-modal-container');
        const backdrop = document.getElementById('msj-modal-backdrop');
        const card = document.getElementById('msj-modal-card');

        // Setup Content
        document.getElementById('msj-modal-title').textContent = title;
        document.getElementById('msj-modal-text').innerHTML = text; // Allow HTML for bold text etc.
        document.getElementById('msj-btn-confirm').textContent = confirmText;

        // Setup Icon & Colors
        const iconContainer = document.getElementById('msj-modal-icon');
        const iconEl = document.getElementById('msj-icon-class');
        const confirmBtn = document.getElementById('msj-btn-confirm');

        // Reset classes
        iconContainer.className = 'w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center text-4xl shadow-lg transition-transform duration-500 hover:scale-110';
        confirmBtn.className = 'w-full py-3.5 rounded-xl font-bold text-white shadow-lg transition-all active:scale-95 hover:brightness-110';

        if (icon === 'success') {
            iconContainer.classList.add('bg-gradient-to-br', 'from-green-500', 'to-emerald-600', 'shadow-green-500/30');
            iconEl.className = 'bi bi-check-lg text-white';
            confirmBtn.classList.add('bg-gradient-to-r', 'from-green-600', 'to-emerald-600', 'shadow-green-600/30');
        } else if (icon === 'error') {
            iconContainer.classList.add('bg-gradient-to-br', 'from-red-500', 'to-pink-600', 'shadow-red-500/30');
            iconEl.className = 'bi bi-x-lg text-white';
            confirmBtn.classList.add('bg-gradient-to-r', 'from-red-600', 'to-pink-600', 'shadow-red-600/30');
        } else if (icon === 'warning') {
            iconContainer.classList.add('bg-gradient-to-br', 'from-amber-500', 'to-orange-600', 'shadow-amber-500/30');
            iconEl.className = 'bi bi-exclamation-lg text-white';
            confirmBtn.classList.add('bg-gradient-to-r', 'from-amber-600', 'to-orange-600', 'shadow-amber-600/30');
        } else {
            // Info / Default
            iconContainer.classList.add('bg-gradient-to-br', 'from-indigo-500', 'to-purple-600', 'shadow-indigo-500/30');
            iconEl.className = 'bi bi-info-lg text-white';
            confirmBtn.classList.add('bg-gradient-to-r', 'from-indigo-600', 'to-purple-600', 'shadow-indigo-600/30');
        }

        // Setup Buttons
        const cancelBtn = document.getElementById('msj-btn-cancel');
        if (showCancel) {
            cancelBtn.textContent = cancelText;
            cancelBtn.classList.remove('hidden');
        } else {
            cancelBtn.classList.add('hidden');
        }

        // Bind Confirm
        confirmBtn.onclick = () => {
            if (onConfirm) onConfirm();
            MSJModal.close();
        };

        // Show Animation
        container.classList.remove('hidden');
        // Small timeout to allow display:block to apply before opacity transition
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    static close() {
        const container = document.getElementById('msj-modal-container');
        if (!container) return;

        const backdrop = document.getElementById('msj-modal-backdrop');
        const card = document.getElementById('msj-modal-card');

        backdrop.classList.add('opacity-0');
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            container.classList.add('hidden');
        }, 300);
    }

    static showLoading(text = 'กำลังประมวลผล...') {
        // Simple reusable loading state - implies non-dismissible until closed
        this.show({
            title: 'Please Wait',
            text: text,
            icon: 'info',
            confirmText: '', // Hide button
        });
        // Hide button manually for this special case
        document.getElementById('msj-btn-confirm').classList.add('hidden');
    }
}
