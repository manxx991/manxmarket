<!-- Thermal Receipt Modal & Isolated Iframe Print Engine -->
<div id="receiptModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="receiptModalTitle">
    <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]" aria-hidden="true">receipt_long</span>
                <h2 class="modal-title font-bold text-base text-text-main dark:text-white" id="receiptModalTitle">Struk Pembayaran Kasir</h2>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('receiptModal')" aria-label="Tutup Struk">&times;</button>
        </div>
        <div class="modal-body p-4 bg-slate-100 dark:bg-slate-900/80 flex justify-center">
            <div id="receiptContent" class="w-full max-w-[340px] bg-white text-slate-950 rounded-xl p-3 shadow-md border border-slate-200 dark:border-slate-800">
                <!-- Injected dynamically by JS -->
            </div>
        </div>
        <div class="modal-footer flex items-center justify-between">
            <button type="button" class="btn btn-secondary text-xs" onclick="closeModal('receiptModal')">Tutup</button>
            <div class="flex items-center gap-2">
                <button type="button" class="btn btn-primary text-xs flex items-center gap-1.5" onclick="printThermalReceipt()">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span>Cetak Struk Thermal (58/80mm)</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function printThermalReceipt() {
    const content = document.getElementById('receiptContent');
    if (!content) { window.print(); return; }

    let frame = document.getElementById('thermalPrintIframe');
    if (frame) frame.remove();

    frame = document.createElement('iframe');
    frame.id = 'thermalPrintIframe';
    frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden;';
    document.body.appendChild(frame);

    const doc = frame.contentWindow.document;
    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Struk Pembayaran</title>
            <style>
                @page { size: 80mm auto; margin: 0; }
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { width: 72mm; margin: 0; padding: 4mm 2mm; font-family: 'JetBrains Mono', Consolas, monospace; font-size: 11px; line-height: 1.35; color: #000; background: #fff; }
                .thermal-receipt { width: 100%; border: none; padding: 0; margin: 0; }
                .receipt-header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; }
                .receipt-title { font-size: 13px; font-weight: bold; }
                .receipt-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
                .receipt-divider { border-top: 1px dashed #000; margin: 6px 0; }
                .receipt-footer { text-align: center; border-top: 1px dashed #000; padding-top: 6px; margin-top: 8px; font-size: 10px; }
            </style>
        </head>
        <body>${content.innerHTML}</body>
        </html>
    `);
    doc.close();

    setTimeout(() => {
        frame.contentWindow.focus();
        frame.contentWindow.print();
        setTimeout(() => frame.remove(), 2000);
    }, 250);
}
</script>
