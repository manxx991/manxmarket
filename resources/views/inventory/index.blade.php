@extends('layouts.app')

@section('title', 'Manajemen Stok & Mutasi Gudang - MANXMARKET')
@section('page_title', 'Stok & Mutasi Gudang')

@section('content')
<div class="flex flex-col gap-6 max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-5 border border-border-subtle dark:border-slate-800 shadow-sm transition-colors">
        <h1 class="font-bold text-xl text-text-main dark:text-white tracking-tight">Katalog Stok &amp; Mutasi Fisik Gudang</h1>
        <p class="text-xs text-text-muted mt-1">Audit stok masuk/keluar, opname fisik barang, dan kelola katalog produk grosir.</p>
    </div>

    <!-- Action Toolbar Box -->
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl p-4 border border-border-subtle dark:border-slate-800 shadow-sm flex flex-wrap items-center justify-between gap-3 transition-colors">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-primary dark:text-blue-400">tune</span>
            <span class="font-bold text-xs text-text-main dark:text-white">Aksi Manajemen Inventaris</span>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="openProductModal()" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">add_box</span>
                <span>Tambah Produk</span>
            </button>
            <button type="button" onclick="openAdjustStockModal()" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 hover:bg-background-subtle dark:hover:bg-slate-700 text-xs font-semibold text-text-main dark:text-white transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[16px] text-secondary dark:text-blue-400">swap_horiz</span>
                <span>Input Mutasi (Masuk/Keluar)</span>
            </button>
            <button type="button" onclick="openCategoryModal()" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 hover:bg-background-subtle dark:hover:bg-slate-700 text-xs font-semibold text-text-main dark:text-white transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[16px] text-emerald-600 dark:text-emerald-400">category</span>
                <span>Tambah Kategori</span>
            </button>
        </div>
    </div>

    <!-- 4 Bento KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Total Item Produk</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-secondary dark:text-blue-400 border border-blue-100 dark:border-blue-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-text-main dark:text-white font-mono">{{ number_format($stats['total_sku']) }} <span class="text-xs text-text-muted font-sans font-normal">SKU</span></div>
                <div class="text-[11px] text-text-muted mt-0.5">Total fisik: {{ number_format($stats['total_stock_physical']) }} unit</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-amber-700 dark:text-amber-400 tracking-wider">Stok Kritis / Habis</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">warning</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-amber-700 dark:text-amber-400 font-mono">{{ number_format($stats['low_stock_count']) }} <span class="text-xs text-text-muted font-sans font-normal">SKU</span></div>
                <div class="text-[11px] {{ $stats['low_stock_count'] > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-text-muted' }} mt-0.5">
                    {{ $stats['low_stock_count'] > 0 ? 'Perlu pengadaan ulang segera' : 'Seluruh level stok aman' }}
                </div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-emerald-700 dark:text-emerald-400 tracking-wider">Total Masuk (Restock)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">arrow_downward</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-emerald-700 dark:text-emerald-400 font-mono">+{{ number_format($stats['total_in_qty']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Akumulasi barang restock masuk</div>
            </div>
        </div>

        <div class="bg-surface-white dark:bg-slate-900 p-4 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm flex flex-col justify-between transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-bold text-text-muted tracking-wider">Total Keluar (Terjual)</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">arrow_upward</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl font-bold text-text-main dark:text-white font-mono">-{{ number_format($stats['total_out_qty']) }}</div>
                <div class="text-[11px] text-text-muted mt-0.5">Barang keluar transaksi kasir</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-border-subtle dark:border-slate-800 pb-1">
        <a href="{{ route('inventory.index', ['tab' => 'products']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'products' ? 'bg-primary dark:bg-blue-600 text-white shadow-xs' : 'bg-surface-white dark:bg-slate-800 text-text-muted hover:text-text-main dark:hover:text-white border border-border-subtle dark:border-slate-700' }}">
            Katalog Produk
        </a>
        <a href="{{ route('inventory.index', ['tab' => 'movements']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'movements' ? 'bg-primary dark:bg-blue-600 text-white shadow-xs' : 'bg-surface-white dark:bg-slate-800 text-text-muted hover:text-text-main dark:hover:text-white border border-border-subtle dark:border-slate-700' }}">
            Riwayat Mutasi Stok
        </a>
        <a href="{{ route('inventory.index', ['tab' => 'categories']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'categories' ? 'bg-primary dark:bg-blue-600 text-white shadow-xs' : 'bg-surface-white dark:bg-slate-800 text-text-muted hover:text-text-main dark:hover:text-white border border-border-subtle dark:border-slate-700' }}">
            Kategori ({{ count($categories) }})
        </a>
    </div>

    @if($tab === 'products')
        <!-- Tab 1: Products List -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm p-4 flex flex-col gap-4 transition-colors">
            <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="products">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs font-mono outline-none focus:border-blue-500" placeholder="Cari nama barang, SKU, barcode..." value="{{ $search }}">
                </div>
                <div class="w-48 relative">
                    <label for="inventoryCatSelect" class="sr-only">Filter Kategori Produk</label>
                    <select id="inventoryCatSelect" name="category" aria-label="Filter Kategori Produk" class="w-full pl-3 pr-8 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs">
                        <option value="all">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ $categorySlug === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                </div>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer">Cari</button>
                <a href="{{ route('inventory.index', ['tab' => 'products']) }}" class="px-3.5 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main dark:hover:text-white text-xs font-semibold transition-colors cursor-pointer">Reset</a>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                            <th class="p-3">SKU / Barcode</th>
                            <th class="p-3">Nama Produk</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3 text-right">Modal (HPP)</th>
                            <th class="p-3 text-right">Harga Jual</th>
                            <th class="p-3 text-center">Sisa Stok</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                        @forelse($products as $p)
                            @php
                                $isLowStock = $p->stock <= $p->min_stock;
                            @endphp
                            <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3 font-mono">
                                    <div class="font-bold text-text-main dark:text-white">{{ $p->sku }}</div>
                                    <div class="text-[11px] text-text-muted">{{ $p->barcode ?: '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-white p-1 shrink-0 border border-border-subtle dark:border-slate-700 overflow-hidden flex items-center justify-center shadow-2xs">
                                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                            <span class="material-symbols-outlined text-[20px] text-slate-400 hidden">inventory_2</span>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-text-main dark:text-white">{{ $p->name }}</div>
                                            <div class="text-text-muted text-[11px]">{{ $p->unit }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-text-muted">{{ $p->category->name ?? 'Umum' }}</td>
                                <td class="p-3 text-right font-mono text-text-muted">{{ formatRupiah($p->cost_price) }}</td>
                                <td class="p-3 text-right font-mono font-bold text-secondary dark:text-blue-400">{{ formatRupiah($p->retail_price) }}</td>
                                <td class="p-3 text-center font-mono">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $isLowStock ? 'bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/40' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40' }}">
                                        {{ $p->stock }} {{ $p->unit }}
                                    </span>
                                    @if($isLowStock)
                                        <div class="text-[10px] text-red-600 dark:text-red-400 mt-0.5">Min: {{ $p->min_stock }}</div>
                                    @endif
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <button type="button" onclick="openPhysicalStockModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->stock }}, '{{ $p->unit }}')" class="px-2 py-1 rounded-lg bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 hover:bg-background-subtle dark:hover:bg-slate-700 text-xs text-text-main dark:text-white transition-colors mr-1 cursor-pointer" title="Opname Fisik">
                                        Opname
                                    </button>
                                    <button type="button" onclick="openStockHistoryModal({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ $p->unit }}')" class="px-2 py-1 rounded-lg bg-surface-white dark:bg-slate-800 border border-border-subtle dark:border-slate-700 hover:bg-background-subtle dark:hover:bg-slate-700 text-xs text-secondary dark:text-blue-400 transition-colors cursor-pointer" title="Riwayat Mutasi">
                                        Riwayat
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-text-muted">
                                    Belum ada data produk atau tidak ditemukan sesuai pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                {{ $products->links() }}
            </div>
        </div>

    @elseif($tab === 'movements')
        <!-- Tab 2: Stock Movements History -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm p-4 flex flex-col gap-4 transition-colors">
            <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="movements">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="move_search" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs font-mono outline-none focus:border-blue-500" placeholder="Cari nama produk, SKU, atau catatan..." value="{{ $moveSearch }}">
                </div>
                <div class="w-44 relative">
                    <label for="inventoryMoveSelect" class="sr-only">Filter Jenis Mutasi</label>
                    <select id="inventoryMoveSelect" name="move_type" aria-label="Filter Jenis Mutasi" class="w-full pl-3 pr-8 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs">
                        <option value="all">Semua Jenis</option>
                        <option value="in" {{ $moveType === 'in' ? 'selected' : '' }}>Masuk (Restock)</option>
                        <option value="sale" {{ $moveType === 'sale' ? 'selected' : '' }}>Keluar (Kasir)</option>
                        <option value="adjustment" {{ $moveType === 'adjustment' ? 'selected' : '' }}>Penyesuaian (Opname)</option>
                        <option value="void_return" {{ $moveType === 'void_return' ? 'selected' : '' }}>Retur Void</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                </div>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer">Filter</button>
                <a href="{{ route('inventory.index', ['tab' => 'movements']) }}" class="px-3.5 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main dark:hover:text-white text-xs font-semibold transition-colors cursor-pointer">Reset</a>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-background-canvas dark:bg-slate-800/80 text-text-muted dark:text-slate-400 uppercase text-[11px] font-semibold border-b border-border-subtle dark:border-slate-800">
                            <th class="p-3">Waktu</th>
                            <th class="p-3">Produk</th>
                            <th class="p-3">Tipe Mutasi</th>
                            <th class="p-3 text-right">Perubahan Qty</th>
                            <th class="p-3 text-right">Stok Awal</th>
                            <th class="p-3 text-right">Stok Akhir</th>
                            <th class="p-3">Catatan / Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle dark:divide-slate-800">
                        @forelse($movements as $m)
                            <tr class="hover:bg-background-subtle dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3 font-mono text-text-muted">{{ $m->created_at ? \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                <td class="p-3 font-semibold text-text-main dark:text-white">{{ $m->product->name ?? '-' }}</td>
                                <td class="p-3 uppercase text-[11px] font-bold">
                                    @if(in_array($m->movement_type, ['in', 'void_return']))
                                        <span class="text-emerald-700 dark:text-emerald-400">{{ $m->movement_type }}</span>
                                    @elseif($m->movement_type === 'sale')
                                        <span class="text-blue-700 dark:text-blue-400">Penjualan</span>
                                    @else
                                        <span class="text-amber-700 dark:text-amber-400">{{ $m->movement_type }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right font-mono font-bold {{ $m->qty_change > 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                    {{ $m->qty_change > 0 ? '+' : '' }}{{ $m->qty_change }}
                                </td>
                                <td class="p-3 text-right font-mono text-text-muted">{{ $m->stock_before }}</td>
                                <td class="p-3 text-right font-mono font-bold text-text-main dark:text-white">{{ $m->stock_after }}</td>
                                <td class="p-3 text-text-muted">
                                    <div class="text-text-main dark:text-slate-300">{{ $m->notes ?: '-' }}</div>
                                    <div class="text-[10px] text-text-muted mt-0.5">Oleh: {{ $m->user->name ?? 'Sistem' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-text-muted">
                                    Belum ada catatan mutasi stok pada log.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                {{ $movements->links() }}
            </div>
        </div>

    @else
        <!-- Tab 3: Categories List -->
        <div class="bg-surface-white dark:bg-slate-900 rounded-2xl border border-border-subtle dark:border-slate-800 shadow-sm p-4 flex flex-col gap-4 transition-colors">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-sm text-text-main dark:text-white">Daftar Kategori Produk</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categories as $cat)
                    <div class="p-4 rounded-xl border border-border-subtle dark:border-slate-800 bg-background-canvas dark:bg-slate-800/60 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-sm text-text-main dark:text-white">{{ $cat->name }}</h3>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 font-bold font-mono border border-blue-200 dark:border-blue-900/50">{{ $cat->products_count }} Item</span>
                            </div>
                            <p class="text-xs text-text-muted mt-1">{{ $cat->description ?: 'Tidak ada deskripsi.' }}</p>
                        </div>
                        <div class="text-[11px] text-text-muted font-mono mt-3">Slug: {{ $cat->slug }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Modal: Tambah/Edit Produk -->
<div id="productModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-border-subtle dark:border-slate-800">
        <div class="flex items-center justify-between pb-3 border-b border-border-subtle dark:border-slate-800">
            <h2 class="font-bold text-base text-text-main dark:text-white" id="prodModalTitle">Tambah Produk Baru</h2>
            <button type="button" onclick="closeModal('productModal')" class="text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <form onsubmit="submitProductForm(event)" class="flex flex-col gap-3 mt-4 text-xs">
            @csrf
            <input type="hidden" id="prodId" name="id">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1">SKU (Kode Unik) *</label>
                    <input type="text" id="prodSku" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500" required>
                </div>
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1">Barcode (Opsional)</label>
                    <input type="text" id="prodBarcode" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500">
                </div>
            </div>
            <div>
                <label class="font-semibold block text-text-main dark:text-white mb-1">Nama Produk *</label>
                <input type="text" id="prodName" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-blue-500" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1" for="prodCategory">Kategori</label>
                    <div class="relative">
                        <select id="prodCategory" class="w-full pl-3 pr-8 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                    </div>
                </div>
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1">Satuan Unit</label>
                    <input type="text" id="prodUnit" value="pcs" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1">Harga Modal (HPP)</label>
                    <input type="number" id="prodCost" step="100" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1">Harga Jual Eceran *</label>
                    <input type="number" id="prodRetail" step="100" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3" id="prodStockGroup">
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1">Stok Awal</label>
                    <input type="number" id="prodStock" value="0" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1">Batas Minimal Stok</label>
                    <input type="number" id="prodMinStock" value="5" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-border-subtle dark:border-slate-800 mt-2">
                <button type="button" onclick="closeModal('productModal')" class="px-3.5 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main dark:hover:text-white font-semibold cursor-pointer">Batal</button>
                <button type="submit" id="saveProdBtn" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold shadow-xs cursor-pointer">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Input Mutasi (Masuk/Keluar) -->
<div id="adjustStockModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-xl border border-border-subtle dark:border-slate-800">
        <div class="flex items-center justify-between pb-3 border-b border-border-subtle dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-secondary dark:text-blue-400">swap_horiz</span>
                <h3 class="font-bold text-base text-text-main dark:text-white">Input Mutasi Stok (Masuk / Keluar)</h3>
            </div>
            <button type="button" onclick="closeModal('adjustStockModal')" class="text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <form id="adjustStockForm" onsubmit="submitAdjustStock(event)" class="flex flex-col gap-3 mt-4 text-xs">
            @csrf
            <div>
                <label class="font-semibold block text-text-main dark:text-white mb-1" for="adjustProdSelect">Pilih Produk *</label>
                <div class="relative">
                    <select id="adjustProdSelect" class="w-full pl-3 pr-8 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach(($allProducts ?? $products) as $ap)
                            <option value="{{ $ap->id }}">{{ $ap->name }} ({{ $ap->sku }} — Sisa: {{ $ap->stock }} {{ $ap->unit }})</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1" for="adjustType">Jenis Mutasi *</label>
                    <div class="relative">
                        <select id="adjustType" class="w-full pl-3 pr-8 py-2.5 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white text-xs appearance-none cursor-pointer outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs" onchange="handleAdjustTypeChange(this.value)">
                            <option value="in">Barang Masuk / Restok (+)</option>
                            <option value="out">Barang Keluar / Rusak (-)</option>
                            <option value="adjustment">Koreksi Opname (+/-)</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">unfold_more</span>
                    </div>
                </div>
                <div>
                    <label class="font-semibold block text-text-main dark:text-white mb-1" for="adjustQty" id="adjustQtyLabel">Jumlah Qty Masuk *</label>
                    <input type="number" id="adjustQty" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500" placeholder="Contoh: 10" required>
                </div>
            </div>
            <div>
                <label class="font-semibold block text-text-main dark:text-white mb-1" for="adjustNotes">Catatan / Alasan Mutasi *</label>
                <input type="text" id="adjustNotes" placeholder="Contoh: Faktur Supplier #PO-102 / Barang rusak" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-blue-500" required>
                <div class="flex flex-wrap gap-1.5 mt-1.5">
                    <button type="button" onclick="document.getElementById('adjustNotes').value='Restok Barang dari Supplier'" class="px-2 py-0.5 rounded-lg bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 hover:bg-background-subtle dark:hover:bg-slate-700 text-[10px] font-semibold text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer">Restok Supplier</button>
                    <button type="button" onclick="document.getElementById('adjustNotes').value='Barang Keluar / Rusak Gudang'" class="px-2 py-0.5 rounded-lg bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 hover:bg-background-subtle dark:hover:bg-slate-700 text-[10px] font-semibold text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer">Barang Rusak</button>
                    <button type="button" onclick="document.getElementById('adjustNotes').value='Retur ke Supplier'" class="px-2 py-0.5 rounded-lg bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 hover:bg-background-subtle dark:hover:bg-slate-700 text-[10px] font-semibold text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer">Retur Supplier</button>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-border-subtle dark:border-slate-800 mt-2">
                <button type="button" onclick="closeModal('adjustStockModal')" class="px-3.5 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main dark:hover:text-white font-semibold cursor-pointer">Batal</button>
                <button type="submit" id="saveAdjustBtn" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold shadow-xs cursor-pointer">Simpan Mutasi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Opname Fisik -->
<div id="physicalStockModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl max-w-sm w-full p-6 shadow-xl border border-border-subtle dark:border-slate-800">
        <h3 class="font-bold text-base text-text-main dark:text-white mb-2">Penyesuaian Fisik (Opname)</h3>
        <p class="text-xs text-text-muted mb-4" id="physicalProdName"></p>
        <form onsubmit="submitPhysicalStock(event)" class="flex flex-col gap-3 text-xs">
            @csrf
            <input type="hidden" id="physProdId">
            <div>
                <label class="font-semibold block text-text-main dark:text-white mb-1">Stok Fisik Sebenarnya</label>
                <input type="number" id="physActualStock" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white font-mono outline-none focus:border-blue-500" required>
            </div>
            <div>
                <label class="font-semibold block text-text-main dark:text-white mb-1">Alasan / Catatan</label>
                <input type="text" id="physNotes" placeholder="Misal: Hasil audit mingguan / barang rusak" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-blue-500" required>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-border-subtle dark:border-slate-800 mt-2">
                <button type="button" onclick="closeModal('physicalStockModal')" class="px-3.5 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main dark:hover:text-white font-semibold cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold cursor-pointer">Update Stok</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Tambah Kategori -->
<div id="categoryModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl max-w-sm w-full p-6 shadow-xl border border-border-subtle dark:border-slate-800">
        <h3 class="font-bold text-base text-text-main dark:text-white mb-3">Tambah Kategori Baru</h3>
        <form onsubmit="submitCategory(event)" class="flex flex-col gap-3 text-xs">
            @csrf
            <div>
                <label class="font-semibold block text-text-main dark:text-white mb-1">Nama Kategori *</label>
                <input type="text" id="catName" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-blue-500" required>
            </div>
            <div>
                <label class="font-semibold block text-text-main dark:text-white mb-1">Deskripsi</label>
                <textarea id="catDesc" rows="2" class="w-full px-3 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-main dark:text-white outline-none focus:border-blue-500"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-border-subtle dark:border-slate-800 mt-2">
                <button type="button" onclick="closeModal('categoryModal')" class="px-3.5 py-2 rounded-xl bg-background-canvas dark:bg-slate-800 border border-border-subtle dark:border-slate-700 text-text-muted hover:text-text-main dark:hover:text-white font-semibold cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Riwayat Mutasi Item -->
<div id="stockHistoryModal" class="modal-backdrop hidden fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-surface-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-border-subtle dark:border-slate-800">
        <div class="flex items-center justify-between pb-3 border-b border-border-subtle dark:border-slate-800">
            <h3 class="font-bold text-base text-text-main dark:text-white" id="historyModalTitle">Riwayat Mutasi</h3>
            <button type="button" onclick="closeModal('stockHistoryModal')" class="text-text-muted hover:text-text-main dark:hover:text-white cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <div id="historyModalContent" class="py-4 text-xs max-h-80 overflow-y-auto">
            Memuat riwayat...
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openProductModal() {
    document.getElementById('prodId').value = '';
    document.getElementById('prodSku').value = '';
    document.getElementById('prodBarcode').value = '';
    document.getElementById('prodName').value = '';
    document.getElementById('prodCategory').value = '';
    document.getElementById('prodUnit').value = 'pcs';
    document.getElementById('prodCost').value = '0';
    document.getElementById('prodRetail').value = '0';
    document.getElementById('prodStock').value = '0';
    document.getElementById('prodMinStock').value = '5';
    document.getElementById('prodModalTitle').textContent = 'Tambah Produk Baru';
    openModal('productModal');
}

function openAdjustStockModal() {
    const form = document.getElementById('adjustStockForm');
    if (form) form.reset();
    handleAdjustTypeChange('in');
    openModal('adjustStockModal');
}

function handleAdjustTypeChange(type) {
    const label = document.getElementById('adjustQtyLabel');
    const input = document.getElementById('adjustQty');
    if (!label || !input) return;
    if (type === 'in') {
        label.textContent = 'Jumlah Qty Masuk (+) *';
        input.placeholder = 'Contoh: 10';
    } else if (type === 'out') {
        label.textContent = 'Jumlah Qty Keluar (-) *';
        input.placeholder = 'Contoh: 5';
    } else {
        label.textContent = 'Perubahan Qty (+/-) *';
        input.placeholder = 'Contoh: 5 atau -3';
    }
}

async function submitAdjustStock(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const type = document.getElementById('adjustType').value;
    let qty = parseInt(document.getElementById('adjustQty').value, 10);
    if (!qty || isNaN(qty)) {
        showToast('Jumlah perubahan stok tidak boleh 0.', 'warning');
        return;
    }
    if (type === 'in') qty = Math.abs(qty);
    else if (type === 'out') qty = -Math.abs(qty);

    const payload = {
        product_id: document.getElementById('adjustProdSelect').value,
        movement_type: type,
        qty_change: qty,
        notes: document.getElementById('adjustNotes').value
    };

    const btn = document.getElementById('saveAdjustBtn');
    if (btn) btn.disabled = true;

    try {
        const resp = await fetch('{{ route("inventory.adjustStock") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);
        showToast(res.message, 'success');
        closeModal('adjustStockModal');
        setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

function openPhysicalStockModal(id, name, stock, unit) {
    document.getElementById('physProdId').value = id;
    document.getElementById('physicalProdName').textContent = name + ' (Tercatat: ' + stock + ' ' + unit + ')';
    document.getElementById('physActualStock').value = stock;
    document.getElementById('physNotes').value = '';
    openModal('physicalStockModal');
}

function openCategoryModal() {
    document.getElementById('catName').value = '';
    document.getElementById('catDesc').value = '';
    openModal('categoryModal');
}

async function openStockHistoryModal(id, name, unit) {
    document.getElementById('historyModalTitle').textContent = 'Riwayat: ' + name;
    document.getElementById('historyModalContent').innerHTML = '<div class="text-center py-4 text-text-muted">Memuat...</div>';
    openModal('stockHistoryModal');

    try {
        const resp = await fetch('{{ url("api/inventory") }}/' + id + '/history');
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);

        const items = res.movements || res.history || [];
        if (items.length === 0) {
            document.getElementById('historyModalContent').innerHTML = '<div class="text-center py-4 text-text-muted">Belum ada riwayat mutasi untuk produk ini.</div>';
            return;
        }

        let html = '<table class="w-full text-left border-collapse text-xs"><thead><tr class="text-text-muted uppercase text-[10px] border-b border-border-subtle"><th class="pb-2">Waktu</th><th class="pb-2">Tipe</th><th class="pb-2 text-right">Perubahan</th><th class="pb-2 text-right">Akhir</th></tr></thead><tbody class="divide-y divide-border-subtle">';
        items.forEach(h => {
            const qty = h.qty_change !== undefined ? h.qty_change : h.quantity;
            const mType = h.type || h.movement_type;
            html += `<tr>
                <td class="py-2 text-text-muted font-mono">${h.created_at}</td>
                <td class="py-2 uppercase font-semibold text-[10px]">${mType}</td>
                <td class="py-2 text-right font-mono font-bold ${qty > 0 ? 'text-emerald-700' : 'text-red-700'}">${qty > 0 ? '+' : ''}${qty} ${unit}</td>
                <td class="py-2 text-right font-mono text-text-main">${h.stock_after}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('historyModalContent').innerHTML = html;
    } catch (e) {
        document.getElementById('historyModalContent').innerHTML = '<div class="text-red-600 text-center py-4">Gagal memuat: ' + e.message + '</div>';
    }
}

async function submitProductForm(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const payload = {
        id: document.getElementById('prodId').value,
        sku: document.getElementById('prodSku').value,
        barcode: document.getElementById('prodBarcode').value,
        name: document.getElementById('prodName').value,
        category_id: document.getElementById('prodCategory').value,
        unit: document.getElementById('prodUnit').value,
        cost_price: document.getElementById('prodCost').value,
        retail_price: document.getElementById('prodRetail').value,
        stock: document.getElementById('prodStock').value,
        min_stock: document.getElementById('prodMinStock').value,
    };

    try {
        const resp = await fetch('{{ route("inventory.storeProduct") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);
        showToast(res.message, 'success');
        closeModal('productModal');
        setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
}

async function submitPhysicalStock(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const payload = {
        product_id: document.getElementById('physProdId').value,
        new_stock: document.getElementById('physActualStock').value,
        actual_stock: document.getElementById('physActualStock').value,
        notes: document.getElementById('physNotes').value
    };

    try {
        const resp = await fetch('{{ route("inventory.setPhysicalStock") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);
        showToast(res.message, 'success');
        closeModal('physicalStockModal');
        setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
}

async function submitCategory(e) {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const payload = {
        name: document.getElementById('catName').value,
        description: document.getElementById('catDesc').value
    };

    try {
        const resp = await fetch('{{ route("inventory.storeCategory") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (!res.success) throw new Error(res.message);
        showToast(res.message, 'success');
        closeModal('categoryModal');
        setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
}
</script>
@endpush
