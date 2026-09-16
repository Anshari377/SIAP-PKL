const fs = require('fs');
const path = require('path');

// ---- Minimal browser shims so @inertiajs/core loads & runs in Node ----
let captured = null;

class FakeXMLHttpRequest {
    setRequestHeader(k, v) { (this.headers ||= {})[k] = v; }
    open(method, url) { this.method = method; this.url = url; }
    send(body) {
        captured = { method: this.method, url: this.url, body, headers: this.headers || {} };
        // fake success response -> Inertia page (JSON with component/props/url)
        const payload = JSON.stringify({ component: 'X', props: {}, url: this.url, version: '1' });
        this.readyState = 4;
        this.status = 200;
        this.statusText = 'OK';
        this.responseText = payload;
        this.response = payload;
        if (typeof this.onreadystatechange === 'function') this.onreadystatechange();
        else if (typeof this.onload === 'function') this.onload();
    }
    getResponseHeader(name) {
        if (name.toLowerCase() === 'x-inertia') return '1';
        return null;
    }
}
global.XMLHttpRequest = FakeXMLHttpRequest;
global.window = {
    location: { href: 'http://localhost/admin/pengajuan/1' },
    history: { state: null, pushState() {}, replaceState() {}, },
    addEventListener() {},
    removeEventListener() {},
};
global.document = { createElement: () => ({}), getElementById: () => null };
global.navigator = { userAgent: 'node' };
global.location = global.window.location {
    href: 'http://localhost/admin/pengajuan/1',
};

const Inertia = require(path.join(__dirname, 'node_modules/@inertiajs/core/dist/index.js'));

const file = new File(['%PDF-1.4 repro test'], 'surat.pdf', { type: 'application/pdf' });

Inertia.router.patch('/admin/pengajuan/1/status', {
    status: 'accepted',
    surat_balasan: file,
}, { forceFormData: true });

setTimeout(() => {
    console.log('METHOD :', captured?.method);
    console.log('URL    :', captured?.url);
    console.log('HEADERS:', JSON.stringify(captured?.headers, null, 2));
    if (captured?.body instanceof FormData) {
        console.log('BODY   : FormData entries:');
        for (const [k, v] of captured.body.entries()) {
            console.log('   -', k, '=>', typeof v === 'object' && v.constructor.name, v.name ? `(${v.name},${v.type},${v.size})` : `(${String(v).slice(0,40)})`);
        }
    } else {
        console.log('BODY   :', typeof captured?.body);
    }
}, 50);
