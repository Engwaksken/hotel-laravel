@extends('layouts.app')
@section('page_title', 'Goods Received Notes')
@section('content')
    @php
        $scanItems = $orders->flatMap(fn ($order) => $order->items->map(fn ($line) => [
            'id' => $line->id,
            'orderId' => $order->id,
            'orderNumber' => $order->number,
            'itemName' => $line->inventoryItem?->name,
            'sku' => $line->inventoryItem?->sku,
            'barcode' => $line->inventoryItem?->barcode,
            'unitCost' => (float) $line->unit_cost,
        ]))->values();
    @endphp

    <div class="page-head">
        <div><h1>Goods Received Notes</h1><p>Create a GRN for the items physically delivered against a purchase order.</p></div>
    </div>

    <div class="card">
        <h2>Create GRN</h2>
        <p class="muted">Use a USB/Bluetooth barcode scanner as a keyboard, or scan with your device camera. Scanning selects a matching item on the chosen purchase order and increments its received quantity.</p>

        @if($orders->isEmpty())
            <div class="alert">There are no approved purchase orders ready to receive.</div>
        @elseif($stores->isEmpty())
            <div class="alert">Create an active store before receiving goods.</div>
        @else
            <form method="POST" action="{{ route('procurement.grns.store') }}" id="grn-form">
                @csrf
                <div class="form-grid">
                    <label>Purchase order
                        <select name="purchase_order_id" id="po-select" required>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" @selected(old('purchase_order_id') == $order->id)>{{ $order->number }} · {{ $order->supplier?->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Receive into
                        <select name="store_id" required>
                            @foreach($stores as $store)<option value="{{ $store->id }}">{{ $store->name }}</option>@endforeach
                        </select>
                    </label>
                    <label>Delivery note<input name="delivery_note_number" maxlength="100" value="{{ old('delivery_note_number') }}"></label>
                </div>

                <section class="card scan-panel" aria-label="Barcode scanning">
                    <div class="card-head">
                        <div><h3>Scan items</h3><p class="muted">Focus the field and scan a product barcode. Barcodes must be saved on the inventory item.</p></div>
                        <button class="btn" type="button" id="camera-start">Use camera</button>
                    </div>
                    <div class="inline-form">
                        <label class="scan-input-label">Barcode
                            <input type="text" id="barcode-input" inputmode="none" autocomplete="off" placeholder="Click here, then scan barcode" aria-describedby="scan-status">
                        </label>
                        <label>Qty per scan<input type="number" id="scan-quantity" min="0.0001" step="0.0001" value="1"></label>
                    </div>
                    <p id="scan-status" role="status" aria-live="polite">Ready to scan. A hardware scanner types into this field and usually sends Enter.</p>
                    <div id="camera-area" hidden>
                        <video id="barcode-video" playsinline muted style="width:min(100%,420px);border-radius:10px"></video>
                        <button class="btn small" type="button" id="camera-stop">Stop camera</button>
                    </div>
                </section>

                <div class="card-head"><h3>Received lines</h3><button class="btn" type="button" id="add-grn-line">Add line</button></div>
                <div class="table-wrap">
                    <table id="grn-lines-table">
                        <thead><tr><th>Purchase order item</th><th>Received</th><th>Rejected</th><th>Unit cost</th><th>Batch / lot</th><th>Expiry</th><th></th></tr></thead>
                        <tbody id="grn-lines"></tbody>
                    </table>
                </div>
                <button class="btn primary" type="submit">Create GRN</button>
            </form>
        @endif
    </div>

    <div class="card table-wrap">
        <h2>Recent GRNs</h2>
        <table>
            <thead><tr><th>GRN</th><th>PO</th><th>Supplier</th><th>Store</th><th>Status</th><th>Lines</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->number }}</td><td>{{ $row->order?->number }}</td>
                        <td>{{ $row->order?->supplier?->name }}</td><td>{{ $row->store?->name }}</td>
                        <td>{{ ucfirst($row->status) }}</td><td>{{ $row->items->count() }}</td>
                        <td>
                            @if($row->status === 'draft')
                                <form method="POST" action="{{ route('procurement.grns.post', $row) }}">@csrf<button class="btn small" type="submit">Post GRN</button></form>
                            @else
                                <span class="muted">Posted</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No goods received notes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $rows->links() }}
    </div>

    @push('scripts')
        <script>
            (() => {
                const products = @json($scanItems);
                const form = document.getElementById('grn-form');
                if (!form) return;

                const poSelect = document.getElementById('po-select');
                const lines = document.getElementById('grn-lines');
                const scanInput = document.getElementById('barcode-input');
                const status = document.getElementById('scan-status');
                const barcodeMap = new Map(products.filter(p => p.barcode).map(p => [String(p.barcode).trim().toLowerCase(), p]));
                let cameraStream = null;
                let detectTimer = null;

                function setStatus(message, error = false) {
                    status.textContent = message;
                    status.style.color = error ? '#b42318' : '';
                }

                function addLine(product = null) {
                    const row = document.createElement('tr');
                    const selectCell = document.createElement('td');
                    const select = document.createElement('select');
                    select.name = 'purchase_order_item_id[]';
                    select.required = true;
                    selectCell.append(select);

                    function fillOptions(selectedId = '') {
                        select.replaceChildren();
                        const placeholder = new Option('Select item', '');
                        select.add(placeholder);
                        products.filter(p => String(p.orderId) === poSelect.value).forEach(p => {
                            select.add(new Option(`${p.orderNumber} · ${p.itemName} · ${p.sku ?? ''}`, p.id));
                        });
                        if (selectedId) select.value = String(selectedId);
                    }
                    fillOptions(product?.id ?? '');

                    const cells = [selectCell];
                    const input = (name, type, placeholder, value = '') => {
                        const cell = document.createElement('td');
                        const element = document.createElement('input');
                        element.name = name + '[]';
                        element.type = type;
                        element.placeholder = placeholder;
                        element.value = value;
                        if (type === 'number') {
                            element.min = '0';
                            element.step = name === 'unit_cost' ? '0.01' : '0.0001';
                            if (name === 'quantity_received' || name === 'unit_cost') element.required = true;
                        }
                        cell.append(element);
                        cells.push(cell);
                        return element;
                    };

                    const qty = input('quantity_received', 'number', 'Received', '');
                    input('quantity_rejected', 'number', 'Rejected', '0');
                    const cost = input('unit_cost', 'number', 'Unit cost', product?.unitCost ?? '');
                    input('batch_number', 'text', 'Batch / lot');
                    input('expires_at', 'date', '');
                    const removeCell = document.createElement('td');
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'btn small';
                    remove.textContent = 'Remove';
                    remove.addEventListener('click', () => row.remove());
                    removeCell.append(remove);
                    cells.push(removeCell);
                    cells.forEach(cell => row.append(cell));
                    select.addEventListener('change', () => {
                        const selected = products.find(p => String(p.id) === select.value);
                        if (selected) cost.value = selected.unitCost;
                    });
                    lines.append(row);
                    if (product) qty.focus();
                    return {row, select, qty, fillOptions};
                }

                function scan(code) {
                    const product = barcodeMap.get(String(code).trim().toLowerCase());
                    if (!product) {
                        setStatus(`Barcode “${code}” is not linked to an inventory item. Add it in Inventory, then try again.`, true);
                        return;
                    }
                    if (String(product.orderId) !== poSelect.value) {
                        setStatus(`${product.itemName} (${product.sku}) is on PO ${product.orderNumber}; select that order to receive it.`, true);
                        return;
                    }

                    let row = [...lines.querySelectorAll('tr')].find(el => el.querySelector('select')?.value === String(product.id));
                    if (!row) row = addLine(product).row;
                    const qtyInput = row.querySelector('[name="quantity_received[]"]');
                    const increment = Number(document.getElementById('scan-quantity').value || 1);
                    qtyInput.value = (Number(qtyInput.value || 0) + increment).toFixed(4).replace(/0+$/, '').replace(/\.$/, '');
                    setStatus(`Added ${increment} × ${product.itemName}. Received quantity: ${qtyInput.value}.`);
                    scanInput.value = '';
                    scanInput.focus();
                }

                document.getElementById('add-grn-line').addEventListener('click', () => addLine());
                poSelect.addEventListener('change', () => {
                    lines.replaceChildren();
                    addLine();
                    setStatus('Purchase order changed. Ready to scan its items.');
                });
                scanInput.addEventListener('keydown', event => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        if (scanInput.value.trim()) scan(scanInput.value);
                    }
                });
                scanInput.addEventListener('change', () => {
                    if (scanInput.value.trim()) scan(scanInput.value);
                });
                addLine();

                const stopCamera = () => {
                    if (detectTimer) clearInterval(detectTimer);
                    detectTimer = null;
                    cameraStream?.getTracks().forEach(track => track.stop());
                    cameraStream = null;
                    document.getElementById('barcode-video').srcObject = null;
                    document.getElementById('camera-area').hidden = true;
                };

                document.getElementById('camera-stop').addEventListener('click', stopCamera);
                document.getElementById('camera-start').addEventListener('click', async () => {
                    if (!('BarcodeDetector' in window) || !navigator.mediaDevices?.getUserMedia) {
                        setStatus('Camera barcode scanning is not supported in this browser. Use a USB/Bluetooth scanner or type the barcode.', true);
                        return;
                    }
                    try {
                        const detector = new BarcodeDetector({formats: ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'qr_code']});
                        cameraStream = await navigator.mediaDevices.getUserMedia({video: {facingMode: {ideal: 'environment'}}, audio: false});
                        const video = document.getElementById('barcode-video');
                        video.srcObject = cameraStream;
                        document.getElementById('camera-area').hidden = false;
                        await video.play();
                        setStatus('Camera active. Hold a barcode inside the camera view.');
                        detectTimer = setInterval(async () => {
                            try {
                                const results = await detector.detect(video);
                                if (results.length) {
                                    stopCamera();
                                    scan(results[0].rawValue);
                                }
                            } catch (_) { /* wait for the next camera frame */ }
                        }, 300);
                    } catch (error) {
                        stopCamera();
                        setStatus(`Could not start camera: ${error.message}. Use a hardware scanner or enter the code.`, true);
                    }
                });
                window.addEventListener('pagehide', stopCamera);
            })();
        </script>
    @endpush
@endsection
