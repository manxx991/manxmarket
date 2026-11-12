@extends('layouts.app')

@section('title', 'Buku Kasbon & Piutang Pelanggan - MANXMARKET')
@section('page_title', 'Buku Hutang Piutang & Tempo')

@section('content')
<div class="flex flex-col gap-6 max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-5 border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <h1 class="font-bold text-xl text-text-main dark:text-white tracking-tight">Buku Hutang Piutang &amp; Tempo</h1>
        <p class="text-xs text-text-muted mt-1">Audit transaksi tempo pelanggan grosir, jatuh tempo, dan pencatatan cicilan kasbon.</p>
    </div>

    <!-- 4 Bento KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Total Piutang Beredar</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-amber-700 dark:text-amber-400 font-mono">{{ formatRupiah($stats['total_active_debt']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">{{ number_format($stats['unpaid_count']) }} tagihan belum lunas</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Pelanggan Memiliki Kasbon</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">group</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-text-main dark:text-white font-mono">{{ number_format($stats['total_customers_with_debt']) }} <span class="text-xs text-text-muted font-sans font-normal">Toko</span></div>
                <div class="text-[11px] text-text-muted mt-0.5">Memiliki saldo piutang aktif</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-emerald-700 dark:text-emerald-400 tracking-wider">Total Cicilan Terkumpul</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-emerald-700 dark:text-emerald-400 font-mono">{{ formatRupiah($stats['total_collected']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Kas masuk dari pelunasan kasbon</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Faktur Telah Lunas</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-text-main dark:text-white font-mono">{{ number_format($stats['paid_count']) }} <span class="text-xs text-text-muted font-sans font-normal">Nota</span></div>
                <div class="text-[11px] text-text-muted mt-0.5">Selesai terbayar penuh</div>
            </div>
        </div>
    </div>

    <!-- Search Toolbar -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-4 border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <form method="GET" action="{{ route('receivables.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs font-mono outline-none focus:border-blue-500" placeholder="Cari nomor nota atau nama pelanggan grosir..." value="{{ $search }}">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">search</span>
                <span>Cari</span>
            </button>
        </form>
    </div>

    <!-- Receivables Table -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                        <th class="p-3">No. Nota</th>
                        <th class="p-3">Pelanggan Grosir</th>
                        <th class="p-3 font-mono">Jatuh Tempo</th>
                        <th class="p-3 text-right">Total Hutang</th>
                        <th class="p-3 text-right">Sudah Dibayar</th>
                        <th class="p-3 text-right">Sisa Tagihan</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse($receivables as $r)
                        @php
                            $isOverdue = $r->status === 'belum_lunas' && $r->due_date && \Carbon\Carbon::parse($r->due_date)->isPast();
                        @endphp
                        <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors">
                            <td class="p-3 font-mono font-bold text-text-main dark:text-white">
                                {{ $r->transaction->invoice_number ?? '-' }}
                            </td>
                            <td class="p-3">
                                <div class="font-semibold text-text-main dark:text-white">{{ $r->customer->name ?? 'Pelanggan' }}</div>
                                <div class="text-[11px] text-text-muted font-mono">{{ $r->customer->phone ?? '-' }}</div>
                            </td>
                            <td class="p-3 font-mono {{ $isOverdue ? 'text-red-700 dark:text-red-400 font-bold' : 'text-text-muted' }}">
                                {{ $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('d/m/Y') : '-' }}
                                @if($isOverdue)
                                    <span class="block text-[10px] text-red-600 dark:text-red-400">Terlambat!</span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-mono font-semibold text-text-main dark:text-white">{{ formatRupiah($r->total_debt) }}</td>
                            <td class="p-3 text-right font-mono text-emerald-700 dark:text-emerald-400">{{ formatRupiah($r->total_paid) }}</td>
                            <td class="p-3 text-right font-mono font-bold {{ $r->remaining_debt > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-text-muted' }}">{{ formatRupiah($r->remaining_debt) }}</td>
                            <td class="p-3 text-center">
                                @if($r->status === 'lunas')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40">LUNAS</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">BELUM LUNAS</span>
                                @endif
                            </td>
                            <td class="p-3 text-right whitespace-nowrap">
                                @if($r->status !== 'lunas')
                                    <button type="button" onclick="openPaymentModal({{ $r->id }}, '{{ addslashes($r->transaction->invoice_number ?? '') }}', '{{ addslashes($r->customer->name ?? '') }}', {{ $r->remaining_debt }})" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition-colors mr-1 cursor-pointer">
                                        Bayar Cicilan
                                    </button>
                                @endif
                                <button type="button" onclick="openPaymentHistory({{ $r->id }}, '{{ addslashes($r->transaction->invoice_number ?? '') }}')" class="px-2.5 py-1 rounded-lg bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 hover:bg-background-subtle dark:hover:bg-slate-700 text-text-main dark:text-white text-xs transition-colors cursor-pointer">
                                    Riwayat
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-text-muted">
                                Tidak ada data catatan piutang kasbon aktif saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-border-subtle dark:border-slate-800">
            {{ $receivables->links() }}
        </div>
    </div>
</div>

<!-- Modal: Bayar Cicilan -->
<div id="paymentModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-surface-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-border-subtle dark:border-slate-800 my-auto max-h-[calc(100vh-2rem)] flex flex-col">
        <!-- Modal Header (Fixed) -->
        <div class="flex items-center justify-between pb-3 border-b border-border-subtle dark:border-slate-800 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">payments</span>
                </div>
                <div>
                    <h2 class="font-bold text-base text-text-main dark:text-white leading-tight">Input Pembayaran Cicilan</h2>
                    <p class="text-[11px] text-text-muted mt-0.5">Catat penerimaan cicilan kasbon piutang</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('paymentModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Tutup">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Scrollable Modal Body -->
        <form onsubmit="submitPayment(event)" class="flex flex-col flex-1 overflow-y-auto pt-3">
            @csrf
            <input type="hidden" id="payRecId">
            <div class="flex flex-col gap-3 text-xs overflow-y-auto pr-1">
                <div class="p-3 bg-background-canvas dark:bg-slate-800/60 rounded-xl border border-border-subtle dark:border-slate-700 text-xs flex flex-col gap-1.5" id="payModalInfo"></div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold block text-text-main dark:text-white" for="payAmount">Nominal Pembayaran (Rp) *</label>
                        <button type="button" onclick="setFullPayAmount()" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer">Lunasi Sisa</button>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-mono font-bold text-sm text-text-muted">Rp</span>
                        <input type="number" id="payAmount" step="1" min="1" class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono text-base font-bold outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20" oninput="handlePayAmountChange()" required>
                    </div>
                    <span class="text-[10px] text-text-muted dark:text-slate-400 mt-1 block">Mendukung pecahan satuan rupiah bebas (contoh: 100, 250, 500, dsb.)</span>
                </div>

                <div>
                    <label class="font-bold block text-text-main dark:text-white mb-1.5">Metode Penerimaan *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" id="payTabTunai" onclick="setPayMethod('tunai')" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/40 text-emerald-950 dark:text-emerald-100 font-bold text-xs shadow-xs cursor-pointer select-none transition-all">
                            <span class="material-symbols-outlined text-[18px] text-emerald-600 dark:text-emerald-400">payments</span>
                            <span>Kas Tunai</span>
                        </button>
                        <button type="button" id="payTabQris" onclick="setPayMethod('qris')" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 text-text-muted dark:text-slate-400 hover:text-text-main dark:hover:text-white hover:border-blue-400 font-semibold text-xs cursor-pointer select-none transition-all">
                            <span class="material-symbols-outlined text-[18px] text-blue-600 dark:text-blue-400">qr_code_2</span>
                            <span>QRIS Dinamis</span>
                        </button>
                    </div>
                    <input type="hidden" id="payMethod" value="tunai">
                </div>

                <!-- Dynamic QRIS Display Container -->
                <div id="recQrisSection" class="hidden p-3 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/60 flex flex-col items-center gap-2 text-center transition-all">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-100/90 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 text-[10px] font-bold border border-blue-200 dark:border-blue-800/60">
                        <span class="material-symbols-outlined text-[14px]">qr_code_2</span>
                        <span>QRIS Dinamis Otomatis</span>
                    </div>
                    <div class="p-2 bg-white rounded-xl shadow-xs border border-slate-200 flex justify-center items-center my-0.5" id="recQrisCanvasWrapper">
                        <div id="recQrisCanvas" class="flex items-center justify-center"></div>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[10px] text-text-muted dark:text-slate-400">Total Nominal Pembayaran:</span>
                        <span id="recQrisAmountDisplay" class="font-mono font-extrabold text-lg text-blue-950 dark:text-blue-100">Rp 0</span>
                        <span class="text-[10px] text-text-muted mt-0.5" id="recQrisMerchantNameDisplay"></span>
                    </div>
                    <div class="text-[10px] text-emerald-800 dark:text-emerald-300 font-bold bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[12px]">lock</span>
                        <span>Nominal cicilan otomatis terkunci pada QRIS</span>
                    </div>
                </div>

                <div id="refNumberGroup" class="hidden">
                    <label class="font-bold block text-text-main dark:text-white mb-1" for="payReferenceNote">Nomor Referensi Transaksi (Opsional)</label>
                    <input type="text" id="payReferenceNote" placeholder="Contoh: No. Ref QRIS / Transfer Bank" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono text-xs outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="font-bold block text-text-main dark:text-white mb-1" for="payNotes">Catatan / Keterangan (Opsional)</label>
                    <input type="text" id="payNotes" placeholder="Misal: Cicilan ke-2 transfer BCA / titip kasir" class="w-full px-3.5 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Modal Action Buttons (Fixed Bottom) -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-border-subtle dark:border-slate-800 mt-3 shrink-0">
                <button type="button" onclick="closeModal('paymentModal')" class="px-4 py-2.5 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main font-semibold cursor-pointer">Batal</button>
                <button type="submit" id="paySubmitBtn" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs transition-all cursor-pointer flex items-center gap-1.5 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]" id="paySubmitBtnIcon">payments</span>
                    <span id="paySubmitBtnText">Konfirmasi Bayar Tunai</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Riwayat Pembayaran -->
<div id="historyModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-border-subtle dark:border-slate-800">
        <div class="flex items-center justify-between pb-3 border-b border-border-subtle dark:border-slate-800">
            <h2 class="font-bold text-base text-text-main dark:text-white" id="histModalTitle">Riwayat Cicilan</h2>
            <button type="button" onclick="closeModal('historyModal')" class="text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <div id="histModalContent" class="py-4 text-xs max-h-80 overflow-y-auto text-text-main dark:text-slate-200">
            Memuat riwayat...
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const QRIS_STATIC_STRING = @json($store['qris_static_string'] ?? '');
const QRIS_MERCHANT_NAME = @json($store['qris_merchant_name'] ?? 'MANXMARKET POS');

let currentRemainingDebt = 0;

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openPaymentModal(id, invoice, customer, remaining) {
    currentRemainingDebt = Number(remaining);
    document.getElementById('payRecId').value = id;
    document.getElementById('payModalInfo').innerHTML = `
        <div class="flex items-center justify-between font-bold text-text-main dark:text-white">
            <span class="truncate">${customer}</span>
            <span class="font-mono text-xs text-secondary dark:text-blue-400 shrink-0 font-bold">${invoice}</span>
        </div>
        <div class="flex items-center justify-between text-[11px] text-text-muted dark:text-slate-400 pt-1 border-t border-border-subtle dark:border-slate-700/50">
            <span>Sisa Tagihan Piutang:</span>
            <span class="font-mono font-extrabold text-amber-700 dark:text-amber-400 text-xs">Rp ${Number(remaining).toLocaleString('id-ID')}</span>
        </div>
    `;
    const payInput = document.getElementById('payAmount');
    payInput.value = remaining;
    payInput.max = remaining;
    document.getElementById('payNotes').value = '';
    const refInput = document.getElementById('payReferenceNote');
    if (refInput) refInput.value = '';

    setPayMethod('tunai');
    openModal('paymentModal');
}

function setFullPayAmount() {
    if (currentRemainingDebt > 0) {
        const input = document.getElementById('payAmount');
        input.value = currentRemainingDebt;
        handlePayAmountChange();
    }
}

function setPayMethod(method) {
    document.getElementById('payMethod').value = method;
    const tabTunai = document.getElementById('payTabTunai');
    const tabQris = document.getElementById('payTabQris');
    const qrisSection = document.getElementById('recQrisSection');
    const refGroup = document.getElementById('refNumberGroup');
    const submitBtn = document.getElementById('paySubmitBtn');
    const submitIcon = document.getElementById('paySubmitBtnIcon');
    const submitText = document.getElementById('paySubmitBtnText');

    if (method === 'qris') {
        tabQris.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-blue-500 bg-blue-50/90 dark:bg-blue-950/40 text-blue-950 dark:text-blue-100 font-bold text-xs shadow-xs cursor-pointer select-none transition-all';
        tabTunai.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 text-text-muted dark:text-slate-400 hover:text-text-main dark:hover:text-white hover:border-emerald-400 font-semibold text-xs cursor-pointer select-none transition-all';

        if (qrisSection) qrisSection.classList.remove('hidden');
        if (refGroup) refGroup.classList.remove('hidden');

        if (submitBtn) {
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-xs transition-all cursor-pointer flex items-center gap-1.5 active:scale-95';
            if (submitIcon) submitIcon.textContent = 'qr_code_scanner';
            if (submitText) submitText.textContent = 'Konfirmasi Bayar QRIS';
        }

        renderRecQris();
    } else {
        tabTunai.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/40 text-emerald-950 dark:text-emerald-100 font-bold text-xs shadow-xs cursor-pointer select-none transition-all';
        tabQris.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border border-border-subtle dark:border-slate-700 bg-background-canvas dark:bg-slate-800 text-text-muted dark:text-slate-400 hover:text-text-main dark:hover:text-white hover:border-blue-400 font-semibold text-xs cursor-pointer select-none transition-all';

        if (qrisSection) qrisSection.classList.add('hidden');
        if (refGroup) refGroup.classList.add('hidden');

        if (submitBtn) {
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs transition-all cursor-pointer flex items-center gap-1.5 active:scale-95';
            if (submitIcon) submitIcon.textContent = 'payments';
            if (submitText) submitText.textContent = 'Konfirmasi Bayar Tunai';
        }
    }
}

function handlePayAmountChange() {
    const payInput = document.getElementById('payAmount');
    let amount = Number(payInput.value || 0);

    if (currentRemainingDebt > 0 && amount > currentRemainingDebt) {
        amount = currentRemainingDebt;
        payInput.value = currentRemainingDebt;
        if (typeof showToast === 'function') {
            showToast('Nominal disesuaikan ke sisa hutang: Rp ' + currentRemainingDebt.toLocaleString('id-ID'), 'info');
        }
    }

    const currentMethod = document.getElementById('payMethod').value;
    if (currentMethod === 'qris') {
        renderRecQris();
    }
}

function renderRecQris() {
    const amount = Number(document.getElementById('payAmount')?.value || 0);
    const canvas = document.getElementById('recQrisCanvas');
    const amtDisplay = document.getElementById('recQrisAmountDisplay');
    const merchDisplay = document.getElementById('recQrisMerchantNameDisplay');

    if (amtDisplay) {
        amtDisplay.textContent = 'Rp ' + amount.toLocaleString('id-ID');
    }
    if (merchDisplay) {
        merchDisplay.textContent = QRIS_MERCHANT_NAME;
    }
    if (!canvas) return;

    canvas.innerHTML = '';
    if (amount <= 0 || !QRIS_STATIC_STRING) {
        canvas.innerHTML = '<div class="text-[11px] text-text-muted py-6">Masukkan nominal pembayaran untuk memunculkan QRIS.</div>';
        return;
    }

    try {
        const dynamicPayload = (typeof makeDynamicQris === 'function') 
            ? makeDynamicQris(QRIS_STATIC_STRING, amount) 
            : QRIS_STATIC_STRING;

        new QRCode(canvas, {
            text: dynamicPayload,
            width: 135,
            height: 135,
            colorDark: "#0b1c30",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    } catch (e) {
        canvas.innerHTML = '<div class="text-xs text-red-600 py-4">Gagal menghasilkan QRIS: ' + e.message + '</div>';
    }
}

async function openPaymentHistory(id, invoice) {
    document.getElementById('histModalTitle').textContent = 'Riwayat Cicilan: ' + invoice;
    document.getElementById('histModalContent').innerHTML = '<div class="text-center py-4 text-text-muted">Memuat...</div>';
    openModal('historyModal');

    try {
        const resp = await fetch('{{ url("api/receivables") }}/' + id + '/history');
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);

        if (res.payments.length === 0) {
            document.getElementById('histModalContent').innerHTML = '<div class="text-center py-4 text-text-muted">Belum ada riwayat cicilan untuk nota ini.</div>';
            return;
        }

        let html = '<table class="w-full text-left border-collapse text-xs"><thead><tr class="text-text-muted uppercase text-[10px] border-b border-border-subtle"><th class="pb-2">Waktu</th><th class="pb-2">Metode</th><th class="pb-2 text-right">Nominal</th><th class="pb-2">Penerima</th></tr></thead><tbody class="divide-y divide-border-subtle">';
        res.payments.forEach(p => {
            html += `<tr>
                <td class="py-2 text-text-muted font-mono">${p.payment_date}</td>
                <td class="py-2 uppercase font-semibold text-[10px]">${p.payment_method}</td>
                <td class="py-2 text-right font-mono font-bold text-emerald-700">Rp ${Number(p.amount_paid).toLocaleString('id-ID')}</td>
                <td class="py-2 text-text-muted">${p.received_by_name || 'Kasir'}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('histModalContent').innerHTML = html;
    } catch (e) {
        document.getElementById('histModalContent').innerHTML = '<div class="text-red-600 text-center py-4">Gagal memuat: ' + e.message + '</div>';
    }
}

async function submitPayment(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const amount = Number(document.getElementById('payAmount').value || 0);

    if (amount <= 0) {
        if (typeof showToast === 'function') {
            showToast('Nominal pembayaran harus lebih besar dari Rp 0.', 'warning');
        }
        return;
    }

    if (currentRemainingDebt > 0 && amount > currentRemainingDebt) {
        if (typeof showToast === 'function') {
            showToast('Nominal melebihi sisa tagihan piutang (Rp ' + currentRemainingDebt.toLocaleString('id-ID') + ').', 'warning');
        }
        return;
    }

    const payload = {
        receivable_id: document.getElementById('payRecId').value,
        amount_paid: amount,
        payment_method: document.getElementById('payMethod').value,
        reference_note: document.getElementById('payReferenceNote')?.value || '',
        notes: document.getElementById('payNotes')?.value || ''
    };

    const btn = document.getElementById('paySubmitBtn');
    btn.disabled = true;
    const oldBtnText = document.getElementById('paySubmitBtnText').textContent;
    document.getElementById('paySubmitBtnText').textContent = 'Memproses...';

    try {
        const resp = await fetch('{{ route("receivables.pay") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);
        closeModal('paymentModal');
        if (typeof showToast === 'function') {
            showToast(res.message, 'success');
        }
        setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        if (typeof showToast === 'function') {
            showToast('Error: ' + err.message, 'error');
        }
    } finally {
        btn.disabled = false;
        document.getElementById('paySubmitBtnText').textContent = oldBtnText;
    }
}
</script>
@endpush
