@extends('layouts.app')

@section('title', 'Portal Pembeli - MANXMARKET')
@section('page_title', 'Portal Pembeli')

@section('content')
<div class="flex flex-col gap-6 max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-5 border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-bold text-xl text-text-main dark:text-white tracking-tight">{{ $customer->name ?? 'Portal Pembeli' }}</h1>
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-200 dark:border-blue-900/40 font-mono font-semibold">
                    {{ '@' . ($customer->username ?? 'user') }}
                </span>
            </div>
            <p class="text-xs text-text-muted mt-1">Informasi akun pembeli, saldo plafon kredit, riwayat faktur kasbon, dan katalog harga produk.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if(Auth::user()->role === 'owner' && count($allCustomers) > 0)
                <div class="relative min-w-[220px]">
                    <label for="portalCustomerSelect" class="sr-only">Pilih Profil Akun Pembeli</label>
                    <select id="portalCustomerSelect" aria-label="Pilih Profil Akun Pembeli" onchange="window.location.href='{{ route('portal.index') }}?customer_id=' + this.value" class="w-full pl-3 pr-8 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 transition-all shadow-xs">
                        @foreach($allCustomers as $ac)
                            <option value="{{ $ac->id }}" {{ $customer->id === $ac->id ? 'selected' : '' }}>
                                {{ $ac->name }} (@ {{ $ac->username }})
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                </div>
            @endif
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold">
                <span class="material-symbols-outlined text-[16px]">verified</span>
                Pelanggan Terdaftar
            </span>
        </div>
    </div>

    @if(Auth::user()->role === 'owner' && count($allCustomers) > 0)
        <!-- Box Data Seluruh Pembeli -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
            <div class="p-4 border-b border-border-subtle dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-primary dark:text-blue-400">group</span>
                    <h2 class="font-bold text-sm text-text-main dark:text-white">Daftar Semua Pembeli Toko ({{ count($allCustomers) }})</h2>
                </div>
                <span class="text-xs text-text-muted">Gunakan dropdown pemilih akun di atas untuk mengganti profil aktif</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                            <th class="p-3">Nama Pembeli</th>
                            <th class="p-3 font-mono">Username / Kontak</th>
                            <th class="p-3 text-center">Total Transaksi</th>
                            <th class="p-3 text-right">Plafon Kredit</th>
                            <th class="p-3 text-right">Kasbon Aktif</th>
                            <th class="p-3 text-center">Status Akun</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                        @foreach($allCustomers as $c)
                            @php
                                $isSelected = $customer->id === $c->id;
                            @endphp
                            <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors {{ $isSelected ? 'bg-blue-50/60 dark:bg-blue-950/30' : '' }}">
                                <td class="p-3">
                                    <div class="font-semibold text-text-main dark:text-white flex items-center gap-1.5">
                                        @if($isSelected)
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        @endif
                                        <span>{{ $c->name }}</span>
                                    </div>
                                </td>
                                <td class="p-3 font-mono">
                                    <div class="text-text-main dark:text-slate-300">@ {{ $c->username }}</div>
                                    <div class="text-[11px] text-text-muted">{{ $c->phone ?: '-' }}</div>
                                </td>
                                <td class="p-3 text-center font-mono font-semibold text-text-main dark:text-white">
                                    {{ number_format($c->customer_transactions_count ?? 0) }} kali
                                </td>
                                <td class="p-3 text-right font-mono font-semibold text-text-main dark:text-white">
                                    {{ formatRupiah($c->credit_limit) }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold {{ ($c->total_active_debt ?? 0) > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                                    {{ formatRupiah($c->total_active_debt ?? 0) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $c->is_active ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40' : 'bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-900/40' }}">
                                        {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- 3 Bento KPI Cards: Plafon & Debt -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Plafon Kredit Belanja</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">credit_card</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-text-main dark:text-white font-mono">{{ formatRupiah($creditLimit) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Batas maksimal hutang tempo toko</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Total Kasbon Belum Lunas</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold font-mono {{ $totalUnpaid > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400' }}">{{ formatRupiah($totalUnpaid) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">{{ $totalUnpaid > 0 ? 'Tagihan aktif perlu pelunasan' : 'Tidak ada tagihan tertunggak' }}</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Sisa Saldo Plafon</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-emerald-700 dark:text-emerald-400 font-mono">{{ formatRupiah($remainingCredit) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Dapat digunakan untuk belanja tempo</div>
            </div>
        </div>
    </div>

    <!-- Active Receivables / Debt Invoices -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="p-4 border-b border-border-subtle dark:border-slate-800">
            <h2 class="font-bold text-sm text-text-main dark:text-white">Daftar Tagihan Kasbon &amp; Jatuh Tempo Akun Ini</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                        <th class="p-3">No. Nota Faktur</th>
                        <th class="p-3 font-mono">Batas Jatuh Tempo</th>
                        <th class="p-3 text-right">Nilai Belanja</th>
                        <th class="p-3 text-right">Telah Dicicil</th>
                        <th class="p-3 text-right">Sisa Tagihan</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse($myReceivables as $r)
                        @php
                            $isOverdue = $r->status === 'belum_lunas' && $r->due_date && \Carbon\Carbon::parse($r->due_date)->isPast();
                        @endphp
                        <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors">
                            <td class="p-3 font-mono font-bold text-text-main dark:text-white">{{ $r->transaction->invoice_number ?? '-' }}</td>
                            <td class="p-3 font-mono {{ $isOverdue ? 'text-red-700 dark:text-red-400 font-bold' : 'text-text-muted' }}">
                                {{ $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('d/m/Y') : '-' }}
                                @if($isOverdue)
                                    <span class="block text-[10px] text-red-600 dark:text-red-400 font-semibold">Melewati batas!</span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-mono text-text-main dark:text-white">{{ formatRupiah($r->total_debt) }}</td>
                            <td class="p-3 text-right font-mono text-emerald-700 dark:text-emerald-400">{{ formatRupiah($r->total_paid) }}</td>
                            <td class="p-3 text-right font-mono font-bold {{ $r->remaining_debt > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-text-muted' }}">{{ formatRupiah($r->remaining_debt) }}</td>
                            <td class="p-3 text-center">
                                @if($r->status === 'lunas')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40">LUNAS</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">BELUM LUNAS</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-text-muted">
                                Tidak ada catatan tagihan kasbon pada akun pembeli ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Transaksi Belanja -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="p-4 border-b border-border-subtle dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-primary dark:text-blue-400">shopping_cart_checkout</span>
                <h2 class="font-bold text-sm text-text-main dark:text-white">Riwayat Belanja</h2>
            </div>
            <span class="text-xs text-text-muted">10 transaksi belanja terakhir akun ini</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                        <th class="p-3">No. Nota</th>
                        <th class="p-3 font-mono">Waktu Transaksi</th>
                        <th class="p-3">Kasir</th>
                        <th class="p-3 text-center">Banyak Item</th>
                        <th class="p-3">Metode Bayar</th>
                        <th class="p-3 text-right">Total Belanja</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                    @forelse($myTransactions as $t)
                        @php
                            $totalQty = $t->items ? $t->items->sum('quantity') : 0;
                        @endphp
                        <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors {{ $t->payment_status === 'void' ? 'opacity-60 bg-red-50/30 dark:bg-red-950/20' : '' }}">
                            <td class="p-3 font-mono font-bold text-text-main dark:text-white whitespace-nowrap">
                                {{ $t->invoice_number }}
                            </td>
                            <td class="p-3 font-mono text-text-muted dark:text-slate-400 whitespace-nowrap">
                                {{ $t->transaction_date ? $t->transaction_date->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="p-3 text-text-main dark:text-slate-200">
                                {{ $t->cashier->name ?? 'Kasir' }}
                            </td>
                            <td class="p-3 text-center font-mono font-semibold text-text-main dark:text-white">
                                {{ $totalQty > 0 ? $totalQty . ' pcs' : '-' }}
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                @if($t->payment_method === 'tunai')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-[10px] font-bold">
                                        <span class="material-symbols-outlined text-[13px] text-emerald-600 dark:text-emerald-400">payments</span>
                                        Tunai
                                    </span>
                                @elseif($t->payment_method === 'qris')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 text-[10px] font-bold">
                                        <span class="material-symbols-outlined text-[13px] text-blue-600 dark:text-blue-400">qr_code_2</span>
                                        QRIS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 text-[10px] font-bold">
                                        <span class="material-symbols-outlined text-[13px] text-amber-600 dark:text-amber-400">event_note</span>
                                        Tempo
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-mono font-bold text-text-main dark:text-white whitespace-nowrap">
                                {{ formatRupiah($t->grand_total) }}
                            </td>
                            <td class="p-3 text-center whitespace-nowrap">
                                @if($t->payment_status === 'lunas')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40">LUNAS</span>
                                @elseif($t->payment_status === 'belum_lunas')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">BELUM LUNAS</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-900/40">BATAL / VOID</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-text-muted">
                                Belum ada riwayat transaksi belanja pada akun pembeli ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Katalog Harga Produk & Grosir -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="p-4 border-b border-border-subtle dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-primary dark:text-blue-400">storefront</span>
                <h2 class="font-bold text-sm text-text-main dark:text-white">Katalog Harga Produk &amp; Grosir ({{ count($catalog) }} SKU)</h2>
            </div>
            <span class="text-xs text-text-muted">Daftar harga eceran dan ketentuan diskon grosir aktif</span>
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($catalog as $cp)
                <div class="bg-background-canvas dark:bg-slate-800/50 rounded-2xl p-3.5 border border-border-subtle dark:border-slate-700/60 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-600 transition-colors">
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-slate-200/80 dark:border-slate-700/60">
                                {{ $cp->unit }}
                            </span>
                            <span class="text-[10px] font-mono text-text-muted dark:text-slate-400 font-semibold">
                                {{ $cp->sku }}
                            </span>
                        </div>
                        <div class="w-full h-28 rounded-xl bg-white p-2 border border-border-subtle dark:border-slate-700/60 flex items-center justify-center mb-2.5 overflow-hidden">
                            <img src="{{ $cp->image_url }}" alt="{{ $cp->name }}" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                            <span class="material-symbols-outlined text-[32px] text-slate-400 hidden">inventory_2</span>
                        </div>
                        <h3 class="font-bold text-xs text-text-main dark:text-white line-clamp-2 leading-snug">{{ $cp->name }}</h3>
                        <p class="text-[11px] text-text-muted mt-0.5">{{ $cp->category->name ?? 'Umum' }}</p>

                        @if($cp->activeWholesaleRule)
                            <div class="bg-wholesale-badge-bg dark:bg-red-950/40 border border-red-200/80 dark:border-red-900/50 px-2 py-1 rounded-xl mt-2 flex items-center justify-between text-[10px] text-wholesale-badge-text dark:text-red-300 font-bold">
                                <span>&ge; {{ $cp->activeWholesaleRule->min_qty }} {{ $cp->unit }}</span>
                                <span class="font-mono">-{{ formatRupiah($cp->activeWholesaleRule->discount_value) }}/{{ $cp->unit }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-border-subtle dark:border-slate-700/60 flex items-baseline justify-between">
                        <span class="text-[10px] uppercase font-bold text-text-muted">Harga Ecer</span>
                        <span class="font-mono font-extrabold text-sm text-slate-900 dark:text-emerald-400">{{ formatRupiah($cp->retail_price) }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-text-muted">Katalog produk belum tersedia.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
