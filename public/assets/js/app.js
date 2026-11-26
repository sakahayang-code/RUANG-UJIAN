/**
 * RUANG UJIAN - Frontend Scripting
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Toggle for Mobile & Responsive
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            if (sidebar) sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // 2. Active Exam Event Switcher
    const eventSelect = document.getElementById('activeEventSelector');
    if (eventSelect) {
        eventSelect.addEventListener('change', function () {
            const eventId = this.value;
            window.location.href = (window.APP_BASE_URL || '') + '/events/switch?id=' + encodeURIComponent(eventId);
        });
    }

    // 3. Quick Table Search Filter
    document.querySelectorAll('[data-table-search]').forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        if (!table) return;

        input.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    // 4. Modal Triggers
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-modal-open');
            openModal(targetId);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-modal-close');
            closeModal(targetId);
        });
    });

    // Close modal on click outside (backdrop)
    document.addEventListener('click', function (e) {
        if (e.target && (e.target.classList.contains('modal-overlay') || e.target.classList.contains('modal'))) {
            closeModal(e.target.id);
        }
    });

    // Close active modal on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-overlay.active, .modal.active');
            if (activeModal) {
                closeModal(activeModal.id);
            }
        }
    });

    // 5. Work Program & RAB Dynamic Calculations
    initWorkProgramBudgetCalc();

    // 6. PWA Android Service Worker Registration
    initPwaServiceWorker();

    // 7. PWA Android Install Prompt
    initPwaInstallPrompt();

    // 8. Android Native Bottom Sheet Drawer
    initAndroidBottomSheet();
});

// Modal Global Functions
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Focus first input field if exists
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) firstInput.focus();
        }, 120);
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        // Only restore body overflow if no other modals/sheets remain active
        if (!document.querySelector('.modal-overlay.active, .modal.active, .android-sheet-backdrop.active')) {
            document.body.style.overflow = '';
        }
    }
}

// Work Program Dynamic Rows & Budget
function addWorkProgramActivityRow() {
    const tbody = document.getElementById('workProgramActivitiesTableBody');
    if (!tbody) return;
    const index = tbody.children.length + 1;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="text-center">${index}</td>
        <td><input type="text" name="activities[${index}][name]" class="form-control" placeholder="Nama kegiatan..." required></td>
        <td><input type="date" name="activities[${index}][start_date]" class="form-control"></td>
        <td><input type="date" name="activities[${index}][end_date]" class="form-control"></td>
        <td><input type="text" name="activities[${index}][pic]" class="form-control" placeholder="Penanggung Jawab..."></td>
        <td><input type="text" name="activities[${index}][notes]" class="form-control" placeholder="Keterangan..."></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); renumberRows('workProgramActivitiesTableBody');">Hapus</button>
        </td>
    `;
    tbody.appendChild(tr);
}

function addWorkProgramBudgetRow() {
    const tbody = document.getElementById('workProgramBudgetTableBody');
    if (!tbody) return;
    const index = tbody.children.length + 1;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="text-center">${index}</td>
        <td><input type="text" name="budgets[${index}][description]" class="form-control" placeholder="Uraian kebutuhan anggaran..." required></td>
        <td><input type="number" step="0.1" name="budgets[${index}][volume]" class="form-control rab-volume" value="1" min="0" required oninput="calculateRabRow(this)"></td>
        <td><input type="text" name="budgets[${index}][unit]" class="form-control" placeholder="Eks/Pkt/Rim" value="Pkt" required></td>
        <td><input type="number" step="100" name="budgets[${index}][unit_price]" class="form-control rab-price" value="0" min="0" required oninput="calculateRabRow(this)"></td>
        <td class="text-right"><span class="rab-subtotal font-bold">Rp 0</span></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); renumberRows('workProgramBudgetTableBody'); calculateRabGrandTotal();">Hapus</button>
        </td>
    `;
    tbody.appendChild(tr);
    calculateRabGrandTotal();
}

function calculateRabRow(element) {
    const row = element.closest('tr');
    const volume = parseFloat(row.querySelector('.rab-volume')?.value || 0);
    const price = parseFloat(row.querySelector('.rab-price')?.value || 0);
    const subtotal = volume * price;
    const subtotalSpan = row.querySelector('.rab-subtotal');
    if (subtotalSpan) {
        subtotalSpan.textContent = formatRupiah(subtotal);
    }
    calculateRabGrandTotal();
}

function calculateRabGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('#workProgramBudgetTableBody tr').forEach(row => {
        const volume = parseFloat(row.querySelector('.rab-volume')?.value || 0);
        const price = parseFloat(row.querySelector('.rab-price')?.value || 0);
        grandTotal += (volume * price);
    });
    const grandSpan = document.getElementById('rabGrandTotalDisplay');
    if (grandSpan) {
        grandSpan.textContent = formatRupiah(grandTotal);
    }
}

function initWorkProgramBudgetCalc() {
    document.querySelectorAll('#workProgramBudgetTableBody tr').forEach(row => {
        calculateRabRow(row.querySelector('.rab-volume') || row);
    });
}

function renumberRows(tableBodyId) {
    const tbody = document.getElementById(tableBodyId);
    if (!tbody) return;
    Array.from(tbody.children).forEach((tr, i) => {
        const firstCol = tr.querySelector('td:first-child');
        if (firstCol) firstCol.textContent = i + 1;
    });
}

function formatRupiah(number) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(number));
}

function confirmAction(message, form) {
    if (confirm(message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?')) {
        form.submit();
    }
    return false;
}

// ==========================================
// PWA & ANDROID NATIVE INTERFACE LOGIC
// ==========================================

let deferredInstallPrompt = null;

function initPwaServiceWorker() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            const swUrl = (window.APP_BASE_URL || '') + '/sw.js';
            navigator.serviceWorker.register(swUrl)
                .then(function (registration) {
                    // Registration successful
                })
                .catch(function (error) {
                    // Silent fail if offline or unsupported
                });
        });
    }
}

function initPwaInstallPrompt() {
    const banner = document.getElementById('androidInstallBanner');
    const installBtn = document.getElementById('btnInstallPwa');
    const dismissBtn = document.getElementById('btnDismissPwa');

    // Check if already in standalone / APK mode
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                         window.navigator.standalone === true;

    if (isStandalone) {
        if (banner) banner.style.display = 'none';
        return;
    }

    // Check if user dismissed banner recently
    const isDismissed = sessionStorage.getItem('ruang_ujian_pwa_dismissed');

    window.addEventListener('beforeinstallprompt', function (e) {
        // Prevent default browser mini-infobar
        e.preventDefault();
        deferredInstallPrompt = e;

        if (banner && !isDismissed) {
            banner.style.display = 'flex';
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', async function () {
            if (!deferredInstallPrompt) return;

            deferredInstallPrompt.prompt();
            const { outcome } = await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;

            if (banner) {
                banner.style.display = 'none';
            }
        });
    }

    if (dismissBtn) {
        dismissBtn.addEventListener('click', function () {
            if (banner) {
                banner.style.display = 'none';
                sessionStorage.setItem('ruang_ujian_pwa_dismissed', '1');
            }
        });
    }

    window.addEventListener('appinstalled', function () {
        if (banner) banner.style.display = 'none';
        deferredInstallPrompt = null;
    });
}

function initAndroidBottomSheet() {
    const backdrop = document.getElementById('androidSheetBackdrop');
    const sheet = document.getElementById('androidBottomSheet');
    const toggleBtn = document.getElementById('androidMenuToggle');
    const closeBtn = document.getElementById('androidSheetClose');

    if (!backdrop || !sheet) return;

    function openSheet() {
        backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSheet() {
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openSheet();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            closeSheet();
        });
    }

    // Backdrop click outside sheet
    backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) {
            closeSheet();
        }
    });

    // ESC key closes bottom sheet
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && backdrop.classList.contains('active')) {
            closeSheet();
        }
    });

    // Touch swipe down on handle to close sheet
    let touchStartY = 0;
    let touchEndY = 0;

    sheet.addEventListener('touchstart', function (e) {
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    sheet.addEventListener('touchmove', function (e) {
        touchEndY = e.touches[0].clientY;
    }, { passive: true });

    sheet.addEventListener('touchend', function () {
        const deltaY = touchEndY - touchStartY;
        // If swiped down more than 60px from the top of the sheet
        if (touchStartY < 120 && deltaY > 60) {
            closeSheet();
        }
        touchStartY = 0;
        touchEndY = 0;
    });
}

