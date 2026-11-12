<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Receivable;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\WholesaleRule;
use Exception;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function processCheckout(array $data, User $cashier): array
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            throw new Exception("Keranjang belanja kosong atau data tidak valid.");
        }

        $paymentMethod = $data['payment_method'] ?? 'tunai';
        $customerId = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
        $customerNameManual = trim($data['customer_name_manual'] ?? '');
        $paidAmount = (float)($data['paid_amount'] ?? 0);
        $dueDate = !empty($data['due_date']) ? $data['due_date'] : date('Y-m-d', strtotime('+14 days'));

        if (!in_array($paymentMethod, ['tunai', 'qris', 'hutang'], true)) {
            throw new Exception("Metode pembayaran tidak valid.");
        }

        if ($paymentMethod === 'hutang' && !$customerId) {
            throw new Exception("Transaksi tempo/hutang wajib memilih Pembeli Terdaftar.");
        }

        return DB::transaction(function () use ($items, $paymentMethod, $customerId, $customerNameManual, $paidAmount, $dueDate, $cashier) {
            $customer = null;
            if ($customerId) {
                $customer = User::where('id', $customerId)
                    ->where('role', 'pembeli')
                    ->where('is_active', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$customer) {
                    throw new Exception("Data pelanggan tidak ditemukan atau berstatus nonaktif.");
                }
            }

            $processedItems = [];
            $subtotal = 0.00;
            $totalDiscount = 0.00;

            foreach ($items as $it) {
                $pId = (int)($it['product_id'] ?? ($it['id'] ?? 0));
                $qty = (int)($it['qty'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $product = Product::where('id', $pId)->lockForUpdate()->first();
                if (!$product) {
                    throw new Exception("Produk dengan ID {$pId} tidak ditemukan di sistem.");
                }

                if ($product->stock < $qty) {
                    throw new Exception("Stok produk '{$product->name}' tidak mencukupi. Sisa stok: {$product->stock} {$product->unit}, diminta: {$qty}.");
                }

                $unitPrice = (float)$product->retail_price;
                $costPrice = (float)$product->cost_price;
                $discountPerItem = 0.00;

                $rules = WholesaleRule::where('product_id', $pId)
                    ->where('is_active', 1)
                    ->orderBy('min_qty', 'desc')
                    ->get();

                foreach ($rules as $r) {
                    if ($r->rule_type === 'per_item_qty' && $qty >= (int)$r->min_qty) {
                        if ($r->discount_type === 'fixed_amount') {
                            $discountPerItem = (float)$r->discount_value;
                        } elseif ($r->discount_type === 'percentage') {
                            $discountPerItem = ($unitPrice * (float)$r->discount_value) / 100.0;
                        } elseif ($r->discount_type === 'special_price') {
                            $discountPerItem = max(0, $unitPrice - (float)$r->discount_value);
                        }
                        break;
                    }
                }

                $itemSubtotal = ($unitPrice - $discountPerItem) * $qty;
                $subtotal += ($unitPrice * $qty);
                $totalDiscount += ($discountPerItem * $qty);

                $processedItems[] = [
                    'product_model' => $product,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'cost_price' => $costPrice,
                    'unit_price' => $unitPrice,
                    'discount_per_item' => $discountPerItem,
                    'qty' => $qty,
                    'subtotal_item' => $itemSubtotal,
                    'current_stock' => (int)$product->stock,
                ];
            }

            if (empty($processedItems)) {
                throw new Exception("Tidak ada item yang valid untuk diproses.");
            }

            $grandTotal = $subtotal - $totalDiscount;

            $changeAmount = 0.00;
            $paymentStatus = 'lunas';
            $receivableDebt = 0.00;

            if ($paymentMethod === 'tunai') {
                if ($paidAmount < $grandTotal) {
                    throw new Exception("Uang tunai diterima (Rp " . number_format($paidAmount, 0, ',', '.') . ") kurang dari total belanja (Rp " . number_format($grandTotal, 0, ',', '.') . ").");
                }
                $changeAmount = $paidAmount - $grandTotal;
                $paymentStatus = 'lunas';
            } elseif ($paymentMethod === 'qris') {
                $paidAmount = $grandTotal;
                $changeAmount = 0.00;
                $paymentStatus = 'lunas';
            } elseif ($paymentMethod === 'hutang') {
                if ($paidAmount >= $grandTotal) {
                    $paymentStatus = 'lunas';
                    $changeAmount = $paidAmount - $grandTotal;
                } else {
                    $paymentStatus = 'belum_lunas';
                    $receivableDebt = $grandTotal - $paidAmount;

                    $creditLimit = (float)$customer->credit_limit;
                    if ($creditLimit <= 0) {
                        throw new Exception("Pelanggan '{$customer->name}' tidak memiliki limit plafon kredit kasbon.");
                    }

                    $currentDebt = (float)Receivable::where('customer_id', $customerId)
                        ->where('status', 'belum_lunas')
                        ->sum('remaining_debt');

                    if (($currentDebt + $receivableDebt) > $creditLimit) {
                        $plafonStr = 'Rp ' . number_format($creditLimit, 0, ',', '.');
                        $currDebtStr = 'Rp ' . number_format($currentDebt, 0, ',', '.');
                        $reqStr = 'Rp ' . number_format($receivableDebt, 0, ',', '.');
                        throw new Exception("Batas kredit pelanggan terlampaui! Plafon: {$plafonStr}, Hutang Aktif: {$currDebtStr}, Tambahan Hutang: {$reqStr}.");
                    }
                }
            }

            $datePrefix = date('Ymd');
            $lastInv = Transaction::where('invoice_number', 'like', 'INV-' . $datePrefix . '-%')
                ->orderBy('id', 'desc')
                ->value('invoice_number');

            $seq = 1;
            if ($lastInv && preg_match('/INV-\d+-(\d+)/', $lastInv, $matches)) {
                $seq = (int)$matches[1] + 1;
            }
            $invoiceNumber = 'INV-' . $datePrefix . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);

            while (Transaction::where('invoice_number', $invoiceNumber)->exists()) {
                $seq++;
                $invoiceNumber = 'INV-' . $datePrefix . '-' . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
            }

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'cashier_id' => $cashier->id,
                'customer_id' => $customerId,
                'customer_name_manual' => $customerNameManual ?: ($customer->name ?? 'Pelanggan Umum'),
                'transaction_date' => now(),
                'subtotal' => $subtotal,
                'total_discount' => $totalDiscount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'created_at' => now(),
            ]);

            foreach ($processedItems as $pItem) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $pItem['product_id'],
                    'product_name_snapshot' => $pItem['name'],
                    'sku_snapshot' => $pItem['sku'],
                    'qty' => $pItem['qty'],
                    'cost_price_snapshot' => $pItem['cost_price'],
                    'unit_price' => $pItem['unit_price'],
                    'discount_per_item' => $pItem['discount_per_item'],
                    'subtotal_item' => $pItem['subtotal_item'],
                ]);

                $product = $pItem['product_model'];
                $stockBefore = (int)$product->stock;
                $stockAfter = $stockBefore - $pItem['qty'];

                $product->update(['stock' => $stockAfter]);

                StockMovement::create([
                    'product_id' => $pItem['product_id'],
                    'user_id' => $cashier->id,
                    'reference_id' => $transaction->id,
                    'movement_type' => 'sale',
                    'qty_change' => -$pItem['qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => "Penjualan Kasir Nota " . $invoiceNumber,
                    'created_at' => now(),
                ]);
            }

            if ($paymentMethod === 'hutang' && $receivableDebt > 0) {
                Receivable::create([
                    'transaction_id' => $transaction->id,
                    'customer_id' => $customerId,
                    'total_debt' => $grandTotal,
                    'total_paid' => $paidAmount,
                    'remaining_debt' => $receivableDebt,
                    'due_date' => $dueDate,
                    'status' => 'belum_lunas',
                    'created_at' => now(),
                ]);
            }

            $store = \App\Models\StoreSetting::allSettings();

            $transPayload = [
                'id' => $transaction->id,
                'invoice_number' => $invoiceNumber,
                'date' => $transaction->transaction_date->format('d/m/Y H:i'),
                'cashier' => $cashier->name,
                'customer' => $transaction->customer_name_manual,
                'payment_method' => strtoupper($paymentMethod),
                'payment_status' => strtoupper($paymentStatus),
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'due_date' => ($paymentMethod === 'hutang' && $receivableDebt > 0) ? date('d/m/Y', strtotime($dueDate)) : null,
                'remaining_debt' => $receivableDebt,
                'items' => $processedItems,
                'store' => $store,
            ];

            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'invoice_number' => $invoiceNumber,
                'transaction' => $transPayload,
            ];
        });
    }
}
