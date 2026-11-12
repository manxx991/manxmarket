<!-- Modal Quick Tambah Role dari Sidebar -->
<div id="quickAddRoleModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="quickAddRoleTitle">
    <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[24px]" aria-hidden="true">person_add</span>
                <h2 class="modal-title font-bold text-sm text-text-main dark:text-white" id="quickAddRoleTitle">Tambah Akun / Role Baru</h2>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('quickAddRoleModal')" aria-label="Tutup Dialog">&times;</button>
        </div>
        <form id="quickAddRoleForm" onsubmit="handleQuickAddRole(event)">
            <div class="modal-body flex flex-col gap-space-md p-4">
                <div class="bg-background-canvas dark:bg-slate-800 p-2.5 rounded-xl border border-border-subtle dark:border-slate-700 text-xs text-text-muted dark:text-slate-300">
                    {{ Auth::user()->role === 'owner' 
                        ? 'Sebagai Owner, Anda dapat menambahkan Role Kasir, Pembeli, Gudang, atau Owner.' 
                        : 'Sebagai Kasir, Anda dapat mendaftarkan Role Kasir (rekan shift) dan Role Pembeli (pelanggan grosir).' }}
                </div>

                <div class="form-group">
                    <label class="form-label" for="quickRoleSelect">Pilih Role Akses *</label>
                    <div class="relative">
                        <select id="quickRoleSelect" name="role" class="form-control pr-8 appearance-none cursor-pointer text-xs" required onchange="handleQuickRoleChange(this.value)">
                            @if(Auth::user()->role === 'owner')
                                <option value="owner">Role Owner (Akses Penuh)</option>
                            @endif
                            <option value="kasir" selected>Role Kasir (POS, Pembayaran &amp; Role)</option>
                            <option value="pembeli">Role Pembeli (Pelanggan Grosir / Beli Tempo)</option>
                            @if(Auth::user()->role === 'owner')
                                <option value="gudang">Role Gudang (Stok &amp; Opname)</option>
                            @endif
                        </select>
                        <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="quickName">Nama Lengkap / Nama Toko Pelanggan *</label>
                    <input type="text" id="quickName" name="name" class="form-control" required placeholder="Contoh: Rina Kasir / Toko Maju Jaya">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="form-group">
                        <label class="form-label" for="quickUsername">Username *</label>
                        <input type="text" id="quickUsername" name="username" class="form-control font-mono" required placeholder="huruf kecil">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="quickPassword">Password *</label>
                        <input type="password" id="quickPassword" name="password" class="form-control" required placeholder="Minimal 6 char">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="quickPhone">No. WhatsApp / HP</label>
                    <input type="text" id="quickPhone" name="phone" class="form-control font-mono" placeholder="08xxxxxxxxxx">
                </div>

                <div class="form-group" id="quickCreditLimitGroup" style="display: none;">
                    <label class="form-label" for="quickCreditLimit">Plafon Kredit Belanja Tempo (Rp)</label>
                    <input type="number" id="quickCreditLimit" name="credit_limit" class="form-control font-mono" value="0" step="50000">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('quickAddRoleModal')">Batal</button>
                <button type="submit" id="quickAddRoleSubmitBtn" class="btn btn-primary">Simpan Role Baru</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddRoleModalQuick() {
    if (typeof toggleSidebar === 'function' && window.innerWidth < 768) {
        toggleSidebar(false);
    }
    openModal('quickAddRoleModal');
}

function handleQuickRoleChange(val) {
    const creditBox = document.getElementById('quickCreditLimitGroup');
    if (creditBox) {
        creditBox.style.display = (val === 'pembeli') ? 'block' : 'none';
    }
}

async function handleQuickAddRole(e) {
    e.preventDefault();
    const form = document.getElementById('quickAddRoleForm');
    const formData = new FormData(form);
    const payload = {
        role: formData.get('role'),
        name: formData.get('name'),
        username: formData.get('username'),
        password: formData.get('password'),
        phone: formData.get('phone'),
        credit_limit: formData.get('credit_limit') || 0
    };

    const submitBtn = document.getElementById('quickAddRoleSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';

    const csrf = window.csrfToken || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');

    try {
        const resp = await fetch('{{ route("roles.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (res.success) {
            closeModal('quickAddRoleModal');

            if (typeof showUserCreatedToast === 'function') {
                showUserCreatedToast(res.user);
            } else if (typeof showToast === 'function') {
                showToast(res.message, 'success');
            }

            if (res.user && res.user.role === 'pembeli') {
                const sel = document.getElementById('customerSelect');
                if (sel) {
                    const opt = document.createElement('option');
                    opt.value = res.user.id;
                    opt.textContent = `${res.user.name} (${res.user.phone || 'No Telp -'})`;
                    sel.appendChild(opt);
                    sel.value = res.user.id;
                }
                if (typeof posApp !== 'undefined' && posApp) {
                    posApp.customers.push(res.user);
                    posApp.selectedCustomerId = res.user.id;
                    if (typeof posApp.updateCustomerDebtInfo === 'function') {
                        posApp.updateCustomerDebtInfo();
                    }
                }
            }
            form.reset();
            // Kembalikan form select role ke default
            const roleSelect = document.getElementById('quickRoleSelect');
            if (roleSelect) {
                roleSelect.value = 'kasir';
                handleQuickRoleChange('kasir');
            }
            // Jika berada di halaman manajemen role (/roles), refresh data pengguna
            if (window.location.pathname.includes('/roles')) {
                setTimeout(() => window.location.reload(), 2500);
            }
        } else {
            if (typeof showToast === 'function') {
                showToast(res.message || 'Gagal menambahkan role.', 'error');
            }
        }
    } catch (err) {
        if (typeof showToast === 'function') {
            showToast('Terjadi kesalahan: ' + err.message, 'error');
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Role Baru';
    }
}
</script>
