import assert from 'node:assert/strict';
import { spawn, execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { mkdtemp, readFile, rm } from 'node:fs/promises';
import http from 'node:http';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { after, before, test } from 'node:test';

const root = fileURLToPath(new URL('../../', import.meta.url));
const scratch = process.env.TMPDIR || os.tmpdir();
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
const payload = label => `${label} O'Brien & "besar" <img src=x onerror="window.__xss=(window.__xss||0)+1"><svg onload="window.__xss=(window.__xss||0)+1"></svg>`;
const receipt = {
    invoice_number: payload('Nota'), date: payload('Tanggal'), cashier: payload('Kasir'),
    customer: payload('Pelanggan'), payment_method: payload('Metode'), payment_status: payload('Status'),
    due_date: payload('Tempo'), remaining_debt: 9000, subtotal: 20000, discount: 1000,
    grand_total: 19000, paid_amount: 10000, change_amount: 0,
    store: {
        store_name: payload('Toko'), store_address: payload('Alamat'),
        store_phone: payload('Telepon'), receipt_footer: payload('Footer'),
    },
    items: [
        { product_id: 17, name: payload('Produk'), qty: 2, unit_price: 10000, discount_per_item: 500, subtotal_item: 19000 },
        { product_id: 18, product_name_snapshot: payload('Snapshot'), qty: payload('Qty'), unit_price: 1000, subtotal_item: 1000 },
    ],
};
const historyReceipt = { ...receipt, date: undefined, cashier: undefined, customer: undefined, discount: undefined,
    transaction_date: payload('Tanggal'), cashier_name: payload('Kasir'), customer_name: payload('Pelanggan'), total_discount: 1000 };

function renderBlade(fragment, variables = '') {
    return execFileSync(process.env.PHP_BINARY || 'php', ['-r', `
        require 'vendor/autoload.php';
        $app = require 'bootstrap/app.php';
        $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
        ${variables}
        $source = base64_decode('${Buffer.from(fragment).toString('base64')}');
        ob_start();
        eval('?>' . $app->make('blade.compiler')->compileString($source));
        echo ob_get_clean();
    `], { cwd: root, encoding: 'utf8', env: { ...process.env, APP_ENV: 'testing', APP_URL: 'http://127.0.0.1', DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:', SESSION_DRIVER: 'array', CACHE_STORE: 'array' } });
}

let server, browser, profile, socket, port;
let sequence = 0;
const pending = new Map();
let runtimeErrors = [];
function command(method, params = {}) {
    return new Promise((resolve, reject) => {
        const id = ++sequence;
        pending.set(id, { resolve, reject });
        socket.send(JSON.stringify({ id, method, params }));
    });
}
async function evaluate(expression) {
    const result = await command('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true });
    if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text);
    return result.result.value;
}
async function loadPage() {
    runtimeErrors = [];
    await evaluate('window.__fixtureReady = false');
    await command('Page.navigate', { url: `http://127.0.0.1:${port}/` });
    for (let i = 0; i < 100; i++) {
        try { if (await evaluate('window.__fixtureReady === true')) return; } catch {}
        await delay(20);
    }
    throw new Error('Frontend fixture did not load');
}

before(async () => {
    const component = await readFile(path.join(root, 'resources/views/components/thermal-receipt.blade.php'), 'utf8');
    const transactionView = await readFile(path.join(root, 'resources/views/transactions/index.blade.php'), 'utf8');
    const reprintScript = renderBlade(transactionView.slice(transactionView.indexOf('async function openReprintModal'), transactionView.indexOf('@if', transactionView.indexOf('async function openReprintModal'))));
    const fixture = `<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="csrf-token" content="test"></head><body>
        ${component}<div id="paymentModal" class="modal-backdrop hidden"></div>
        <script>const nativeFetch=window.fetch.bind(window);window.fetch=(input,init)=>{const url=new URL(input,location.href);return nativeFetch(url.pathname+url.search,init)};</script>
        <script src="/app.js"></script><script>${reprintScript}</script><script>window.__fixtureReady = true;</script></body></html>`;
    server = http.createServer(async (req, res) => {
        if (req.url === '/app.js') {
            res.setHeader('Content-Type', 'application/javascript');
            res.end(await readFile(path.join(root, process.env.APP_BUNDLE || 'public/assets/js/app.js')));
        } else if (req.url === '/api/pos/checkout' || req.url === '/api/transactions/77') {
            res.setHeader('Content-Type', 'application/json');
            res.end(JSON.stringify({ success: true, transaction: req.url.includes('/checkout') ? receipt : historyReceipt }));
        } else if (req.url === '/') {
            res.setHeader('Content-Type', 'text/html; charset=utf-8');
            res.end(fixture);
        } else { res.writeHead(404); res.end(); }
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    port = server.address().port;
    const executable = process.env.CHROME_BINARY || [
        'C:/Program Files/Google/Chrome/Application/chrome.exe',
        'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
        '/usr/bin/chromium', '/usr/bin/google-chrome',
    ].find(existsSync);
    assert.ok(executable, 'Set CHROME_BINARY to a Chromium executable');
    profile = await mkdtemp(path.join(scratch, 'manxmarket-xss-'));
    browser = spawn(executable, ['--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check', '--remote-debugging-port=0', `--user-data-dir=${profile}`, 'about:blank'], { stdio: 'ignore' });
    let debugPort;
    for (let i = 0; i < 150; i++) {
        try { debugPort = (await readFile(path.join(profile, 'DevToolsActivePort'), 'utf8')).split('\n')[0]; break; } catch { await delay(100); }
    }
    assert.ok(debugPort, 'Chromium did not expose its debugging port');
    const targets = await (await fetch(`http://127.0.0.1:${debugPort}/json/list`)).json();
    socket = new WebSocket(targets.find(target => target.type === 'page').webSocketDebuggerUrl);
    await new Promise((resolve, reject) => { socket.addEventListener('open', resolve, { once: true }); socket.addEventListener('error', reject, { once: true }); });
    socket.addEventListener('message', event => {
        const message = JSON.parse(event.data);
        if (message.method === 'Runtime.exceptionThrown') runtimeErrors.push(message.params.exceptionDetails.exception?.description || message.params.exceptionDetails.text);
        const request = pending.get(message.id);
        if (!request) return;
        pending.delete(message.id);
        if (message.error) request.reject(new Error(message.error.message)); else request.resolve(message.result);
    });
    await command('Runtime.enable');
    await command('Page.enable');
});
after(async () => {
    socket?.close();
    if (browser && browser.exitCode === null) {
        await new Promise(resolve => { browser.once('exit', resolve); browser.kill(); });
    }
    server?.closeAllConnections();
    if (server) await new Promise(resolve => server.close(resolve));
    if (profile) await rm(profile, { recursive: true, force: true, maxRetries: 10, retryDelay: 100 });
});

for (const route of ['checkout', 'history']) {
    test(`receipt ${route}: literal preview and isolated 58mm/80mm print`, async () => {
        await loadPage();
        await evaluate(`(async () => {
            window.__xss = 0;
            if (${JSON.stringify(route)} === 'history') {
                await openReprintModal(77);
            } else {
                window.posApp = new PosEngine([], []);
                posApp.cart = [{ product_id: 17, name: 'Produk', unit_price: 10000, qty: 2 }];
                posApp.activePaymentMethod = 'qris';
                await posApp.submitCheckout();
            }
        })()`);
        await delay(50);
        const expected = [receipt.invoice_number, receipt.date, receipt.cashier, receipt.customer, receipt.payment_method,
            receipt.payment_status, receipt.due_date, ...Object.values(receipt.store), receipt.items[0].name,
            receipt.items[1].product_name_snapshot, receipt.items[1].qty];
        const problems = [];
        async function inspect(label, documentExpression) {
            const state = await evaluate(`(() => {
                const doc = ${documentExpression};
                const content = doc.querySelector('.thermal-receipt');
                return { text: content?.textContent || '', active: document.getElementById('receiptModal').classList.contains('active'),
                    dangerous: content?.querySelectorAll('img, svg, script, [onerror], [onload]').length,
                    executed: (doc.defaultView.__xss || 0) + (doc === document ? 0 : window.__xss || 0) };
            })()`);
            if (!state.active) problems.push(`${label}: receipt modal did not open`);
            for (const value of expected) if (!state.text.includes(value)) problems.push(`${label}: missing literal ${value.split(' ')[0]}`);
            for (const value of ['2 x Rp 10.000 (disc -Rp 500)', '-Rp 1.000', 'Rp 19.000', 'Rp 9.000']) if (!state.text.includes(value)) problems.push(`${label}: missing amount ${value}`);
            if (state.dangerous !== 0) problems.push(`${label}: ${state.dangerous} executable markup nodes`);
            if (state.executed !== 0) problems.push(`${label}: XSS sentinel executed ${state.executed} times`);
        }
        await inspect('preview', 'document');
        for (const width of ['58mm', '80mm']) {
            await evaluate(`(() => {
                printThermalReceipt(${JSON.stringify(width)});
                const frame = document.getElementById('thermalPrintIframe');
                frame.contentWindow.print = () => { window.__printCalls = (window.__printCalls || 0) + 1; };
            })()`);
            await delay(300);
            await inspect(`print ${width}`, "document.getElementById('thermalPrintIframe').contentDocument");
            const style = await evaluate("document.getElementById('thermalPrintIframe').contentDocument.querySelector('style').textContent");
            assert.ok(style.includes(`size: ${width} auto`));
        }
        assert.equal(await evaluate('window.__printCalls'), 2, 'Both paper sizes must reach iframe.print');
        await evaluate("document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape'}))");
        assert.equal(await evaluate("document.getElementById('receiptModal').classList.contains('active')"), false, 'Escape closes receipt');
        assert.deepEqual(runtimeErrors, [], 'No uncaught frontend errors');
        assert.deepEqual(problems, []);
    });
}
