(() => {
    'use strict';

    const qsa = (selector, root = document) => Array.from(root.querySelectorAll(selector));
    const clean = value => String(value ?? '').replace(/\s+/g, ' ').trim();

    function initPasswordToggles() {
        qsa('input[type="password"]').forEach(input => {
            if (input.dataset.eyeReady === '1') return;
            input.dataset.eyeReady = '1';

            const parent = input.parentElement;
            const wrap = document.createElement('div');
            wrap.className = 'password-field-wrap';
            parent.insertBefore(wrap, input);
            wrap.appendChild(input);

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'password-toggle';
            button.setAttribute('aria-label', 'Show password');
            button.innerHTML = '<i class="fas fa-eye"></i>';
            button.addEventListener('click', () => {
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                button.innerHTML = showing
                    ? '<i class="fas fa-eye"></i>'
                    : '<i class="fas fa-eye-slash"></i>';
            });
            wrap.appendChild(button);
        });
    }

    function headerIndex(headers, patterns) {
        return headers.findIndex(h => patterns.some(p => h.includes(p)));
    }

    function parseDate(value) {
        const raw = clean(value);
        if (!raw) return null;
        let date = new Date(raw);
        if (!Number.isNaN(date.getTime())) return date;

        // Common dd/mm/yyyy or dd-mm-yyyy formats.
        const m = raw.match(/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/);
        if (m) {
            date = new Date(Number(m[3]), Number(m[2]) - 1, Number(m[1]));
            if (!Number.isNaN(date.getTime())) return date;
        }
        return null;
    }

    function createField(labelText, input) {
        const field = document.createElement('div');
        field.className = 'hms-field';
        const label = document.createElement('label');
        label.textContent = labelText;
        label.appendChild(input);
        field.appendChild(label);
        return field;
    }

    function makeStat(icon, label, value = '0') {
        const card = document.createElement('div');
        card.className = 'hms-page-stat';
        card.innerHTML = `<i class="fas ${icon}"></i><div><span>${label}</span><b>${value}</b></div>`;
        return card;
    }

    function enhanceTable(table, tableNumber) {
        if (table.dataset.hmsEnhanced === '1' || table.dataset.noEnhance === '1') return;
        const tbody = table.tBodies[0];
        const thead = table.tHead;
        if (!tbody || !thead || !thead.rows.length) return;

        let rows = Array.from(tbody.rows).filter(row => !row.classList.contains('hms-no-results'));
        if (!rows.length) return;
        table.dataset.hmsEnhanced = '1';

        // Add multi-select column.
        const headRow = thead.rows[0];
        let selectAll = headRow.querySelector('.hms-select-all');
        if (!selectAll) {
            const th = document.createElement('th');
            th.className = 'hms-select-col';
            selectAll = document.createElement('input');
            selectAll.type = 'checkbox';
            selectAll.className = 'hms-select-all';
            selectAll.setAttribute('aria-label', 'Select all visible rows');
            th.appendChild(selectAll);
            headRow.insertBefore(th, headRow.firstChild);

            rows.forEach(row => {
                const td = document.createElement('td');
                td.className = 'hms-select-col';
                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.className = 'hms-row-select';
                cb.setAttribute('aria-label', 'Select row');
                td.appendChild(cb);
                row.insertBefore(td, row.firstChild);
            });
        }

        const headers = Array.from(headRow.cells).map(cell => clean(cell.textContent).toLowerCase());
        const dateIndex = headerIndex(headers, ['date', 'created', 'updated', 'arrival', 'departure', 'check-in', 'check in', 'check-out', 'check out', 'start', 'issued', 'paid at']);
        const branchIndex = headerIndex(headers, ['branch', 'property', 'hotel']);
        const statusIndex = headerIndex(headers, ['status']);

        const wrap = table.closest('.table-wrap') || table.parentElement;
        const suite = document.createElement('div');
        suite.className = 'hms-data-suite';
        wrap.parentNode.insertBefore(suite, wrap);

        const isPrimary = !document.querySelector('.hms-page-stats');
        let totalStat, visibleStat, selectedStat, pageStat;
        if (isPrimary) {
            const stats = document.createElement('div');
            stats.className = 'hms-page-stats';
            totalStat = makeStat('fa-database', 'Total Records', rows.length);
            visibleStat = makeStat('fa-filter', 'Visible', rows.length);
            selectedStat = makeStat('fa-square-check', 'Selected', 0);
            pageStat = makeStat('fa-file-lines', 'Page', '1 / 1');
            stats.append(totalStat, visibleStat, selectedStat, pageStat);
            suite.appendChild(stats);
        }

        const toolbar = document.createElement('div');
        toolbar.className = 'hms-data-toolbar';

        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'hms-search';
        search.placeholder = 'Search records…';
        search.setAttribute('aria-label', 'Search table');
        toolbar.appendChild(createField('Search', search));

        let fromDate = null, toDate = null;
        if (dateIndex >= 0) {
            fromDate = document.createElement('input');
            fromDate.type = 'date';
            toDate = document.createElement('input');
            toDate.type = 'date';
            toolbar.appendChild(createField('From', fromDate));
            toolbar.appendChild(createField('To', toDate));
        }

        let branchSelect = null;
        if (branchIndex >= 0) {
            branchSelect = document.createElement('select');
            branchSelect.className = 'hms-branch-filter';
            branchSelect.innerHTML = '<option value="">All branches</option>';
            const branches = [...new Set(rows.map(row => clean(row.cells[branchIndex]?.textContent)).filter(Boolean))].sort();
            branches.forEach(branch => {
                const option = document.createElement('option');
                option.value = branch.toLowerCase();
                option.textContent = branch;
                branchSelect.appendChild(option);
            });
            toolbar.appendChild(createField('Branch', branchSelect));
        }

        const pageSize = document.createElement('select');
        [10, 25, 50, 100].forEach(n => {
            const o = document.createElement('option');
            o.value = n;
            o.textContent = `${n} rows`;
            if (n === 10) o.selected = true;
            pageSize.appendChild(o);
        });
        toolbar.appendChild(createField('Per page', pageSize));

        const selectionInfo = document.createElement('span');
        selectionInfo.className = 'hms-selection-info';
        selectionInfo.innerHTML = '<i class="fas fa-square-check"></i> <span>0 selected</span>';
        toolbar.appendChild(selectionInfo);

        const actions = document.createElement('div');
        actions.className = 'hms-data-actions';
        const clearSelected = document.createElement('button');
        clearSelected.type = 'button';
        clearSelected.className = 'btn small';
        clearSelected.innerHTML = '<i class="fas fa-xmark"></i> Clear selection';
        const reset = document.createElement('button');
        reset.type = 'button';
        reset.className = 'btn small hms-filter-reset';
        reset.innerHTML = '<i class="fas fa-rotate-left"></i> Reset';
        actions.append(clearSelected, reset);
        toolbar.appendChild(actions);
        suite.appendChild(toolbar);

        const pager = document.createElement('div');
        pager.className = 'hms-pagination';
        const pagerInfo = document.createElement('div');
        pagerInfo.className = 'hms-pagination-info';
        const pagerButtons = document.createElement('div');
        pagerButtons.className = 'hms-pagination-buttons';
        pager.append(pagerInfo, pagerButtons);
        wrap.parentNode.insertBefore(pager, wrap.nextSibling);

        let currentPage = 1;
        let filtered = rows.slice();

        const selectedRows = () => rows.filter(row => row.querySelector('.hms-row-select')?.checked);

        function updateSelection() {
            const count = selectedRows().length;
            selectionInfo.querySelector('span').textContent = `${count} selected`;
            if (selectedStat) selectedStat.querySelector('b').textContent = count;
            rows.forEach(row => row.classList.toggle('hms-selected-row', !!row.querySelector('.hms-row-select')?.checked));
            const visibleChecks = filtered.map(r => r.querySelector('.hms-row-select')).filter(Boolean);
            const visibleSelected = visibleChecks.filter(c => c.checked).length;
            selectAll.checked = visibleChecks.length > 0 && visibleSelected === visibleChecks.length;
            selectAll.indeterminate = visibleSelected > 0 && visibleSelected < visibleChecks.length;
            document.dispatchEvent(new CustomEvent('hms:selectionchange', { detail: { table, rows: selectedRows() } }));
        }

        function matches(row) {
            const needle = clean(search.value).toLowerCase();
            if (needle && !clean(row.textContent).toLowerCase().includes(needle)) return false;

            if (branchSelect && branchSelect.value) {
                const branch = clean(row.cells[branchIndex]?.textContent).toLowerCase();
                if (branch !== branchSelect.value) return false;
            }

            if (dateIndex >= 0 && (fromDate?.value || toDate?.value)) {
                const d = parseDate(row.cells[dateIndex]?.textContent);
                if (!d) return false;
                d.setHours(0,0,0,0);
                if (fromDate.value) {
                    const from = new Date(`${fromDate.value}T00:00:00`);
                    if (d < from) return false;
                }
                if (toDate.value) {
                    const to = new Date(`${toDate.value}T23:59:59`);
                    if (d > to) return false;
                }
            }
            return true;
        }

        function renderPager(pageCount) {
            pagerButtons.innerHTML = '';
            const add = (label, page, disabled = false, active = false, icon = false) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = `hms-page-btn${active ? ' active' : ''}`;
                b.disabled = disabled;
                b.innerHTML = icon ? `<i class="fas ${label}"></i>` : label;
                b.addEventListener('click', () => { currentPage = page; apply(false); });
                pagerButtons.appendChild(b);
            };
            add('fa-chevron-left', Math.max(1, currentPage - 1), currentPage <= 1, false, true);
            const start = Math.max(1, currentPage - 2);
            const end = Math.min(pageCount, start + 4);
            for (let p = start; p <= end; p++) add(String(p), p, false, p === currentPage);
            add('fa-chevron-right', Math.min(pageCount, currentPage + 1), currentPage >= pageCount, false, true);
        }

        function apply(resetPage = true) {
            if (resetPage) currentPage = 1;
            filtered = rows.filter(matches);
            const size = Number(pageSize.value) || 10;
            const pageCount = Math.max(1, Math.ceil(filtered.length / size));
            currentPage = Math.min(currentPage, pageCount);
            const start = (currentPage - 1) * size;
            const end = start + size;
            rows.forEach(row => row.style.display = 'none');
            filtered.slice(start, end).forEach(row => row.style.display = '');

            let noResults = tbody.querySelector('.hms-no-results');
            if (!filtered.length) {
                if (!noResults) {
                    noResults = document.createElement('tr');
                    noResults.className = 'hms-no-results';
                    const td = document.createElement('td');
                    td.colSpan = headRow.cells.length;
                    td.innerHTML = '<i class="fas fa-magnifying-glass"></i> No matching records found.';
                    noResults.appendChild(td);
                    tbody.appendChild(noResults);
                }
                noResults.style.display = '';
            } else if (noResults) {
                noResults.style.display = 'none';
            }

            const shownStart = filtered.length ? start + 1 : 0;
            const shownEnd = Math.min(end, filtered.length);
            pagerInfo.textContent = `Showing ${shownStart}–${shownEnd} of ${filtered.length} records`;
            renderPager(pageCount);
            if (visibleStat) visibleStat.querySelector('b').textContent = filtered.length;
            if (pageStat) pageStat.querySelector('b').textContent = `${currentPage} / ${pageCount}`;
            updateSelection();
        }

        search.addEventListener('input', () => apply(true));
        fromDate?.addEventListener('change', () => apply(true));
        toDate?.addEventListener('change', () => apply(true));
        branchSelect?.addEventListener('change', () => apply(true));
        pageSize.addEventListener('change', () => apply(true));

        selectAll.addEventListener('change', () => {
            const size = Number(pageSize.value) || 10;
            const start = (currentPage - 1) * size;
            filtered.slice(start, start + size).forEach(row => {
                const cb = row.querySelector('.hms-row-select');
                if (cb) cb.checked = selectAll.checked;
            });
            updateSelection();
        });

        rows.forEach(row => row.querySelector('.hms-row-select')?.addEventListener('change', updateSelection));

        clearSelected.addEventListener('click', () => {
            rows.forEach(row => { const cb = row.querySelector('.hms-row-select'); if (cb) cb.checked = false; });
            updateSelection();
        });

        reset.addEventListener('click', () => {
            search.value = '';
            if (fromDate) fromDate.value = '';
            if (toDate) toDate.value = '';
            if (branchSelect) branchSelect.value = '';
            pageSize.value = '10';
            currentPage = 1;
            apply(false);
        });

        // Useful status metadata for pages wanting custom statistics later.
        if (statusIndex >= 0) table.dataset.statusColumn = String(statusIndex);
        apply(false);
    }

    function initTables() {
        qsa('.table-wrap table').forEach((table, i) => enhanceTable(table, i));
    }

    function initModals() {
        document.addEventListener('click', e => {
            const trigger = e.target.closest('[data-modal]');
            if (trigger) document.getElementById(trigger.dataset.modal)?.classList.add('open');
            if (e.target.matches('.modal,.modal-close')) e.target.closest('.modal')?.classList.remove('open');
        });
    }

    function initTabs() {
        document.addEventListener('click', e => {
            const tab = e.target.closest('[data-tab-target]');
            if (!tab) return;
            const scope = tab.closest('.content') || document;
            scope.querySelectorAll('[data-tab-target]').forEach(x => x.classList.remove('active'));
            scope.querySelectorAll('[data-tab-panel]').forEach(x => x.classList.remove('active'));
            tab.classList.add('active');
            scope.querySelector(`[data-tab-panel="${CSS.escape(tab.dataset.tabTarget)}"]`)?.classList.add('active');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initPasswordToggles();
        initTables();
        initModals();
        initTabs();
    });
})();

/* =========================================================
   Flash message auto-dismiss + safe delete confirmation
========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.auto-dismiss').forEach((alert) => {
        let timer;
        const dismiss = () => {
            if (alert.classList.contains('fade-out')) return;
            alert.classList.add('fade-out');
            window.setTimeout(() => alert.remove(), 500);
        };
        timer = window.setTimeout(dismiss, 5000);
        alert.addEventListener('mouseenter', () => window.clearTimeout(timer));
        alert.addEventListener('mouseleave', () => { timer = window.setTimeout(dismiss, 1800); });
        alert.querySelector('.alert-close')?.addEventListener('click', dismiss);
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm || 'Are you sure you want to continue?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });
});

/* =========================================================
   MODAL CRUD: EDIT + DELETE WITHOUT NAVIGATING AWAY
========================================================= */
(() => {
    'use strict';

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    function ensureCrudModal() {
        let modal = document.getElementById('hmsCrudModal');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'hmsCrudModal';
        modal.className = 'modal hms-crud-modal';
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
            <div class="modal-box hms-crud-modal-box" role="dialog" aria-modal="true" aria-labelledby="hmsCrudModalTitle">
                <button type="button" class="modal-close hms-crud-close" aria-label="Close"><i class="fas fa-xmark"></i></button>
                <div class="hms-modal-heading">
                    <span class="hms-modal-icon"><i class="fas fa-pen-to-square"></i></span>
                    <div>
                        <h2 id="hmsCrudModalTitle">Edit Entry</h2>
                        <p id="hmsCrudModalSubtitle">Update the selected record.</p>
                    </div>
                </div>
                <div class="hms-modal-message" hidden></div>
                <div class="hms-crud-body"></div>
            </div>`;
        document.body.appendChild(modal);

        const close = () => {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        };

        modal.querySelector('.hms-crud-close').addEventListener('click', close);
        modal.addEventListener('click', event => {
            if (event.target === modal) close();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && modal.classList.contains('open')) close();
        });
        modal._hmsClose = close;
        return modal;
    }

    function openModal(modal) {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    function showModalMessage(modal, message, type = 'error') {
        const box = modal.querySelector('.hms-modal-message');
        box.hidden = false;
        box.className = `hms-modal-message ${type}`;
        box.innerHTML = `<i class="fas ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i><span>${escapeHtml(message)}</span>`;
    }

    function fieldHtml(field) {
        const name = escapeHtml(field.name);
        const label = escapeHtml(field.label);
        const value = field.value ?? '';

        if (field.type === 'checkbox') {
            const checked = value === true || value === 1 || value === '1' ? ' checked' : '';
            return `
                <label class="hms-modal-check">
                    <input type="hidden" name="${name}" value="0">
                    <input type="checkbox" name="${name}" value="1"${checked}>
                    <span>${label}</span>
                </label>`;
        }

        if (field.type === 'textarea') {
            return `<label>${label}<textarea name="${name}">${escapeHtml(value)}</textarea></label>`;
        }

        const step = field.type === 'number' ? ' step="any"' : '';
        return `<label>${label}<input type="${escapeHtml(field.type || 'text')}" name="${name}" value="${escapeHtml(value)}"${step}></label>`;
    }

    async function openEdit(button) {
        const modal = ensureCrudModal();
        const body = modal.querySelector('.hms-crud-body');
        const title = modal.querySelector('#hmsCrudModalTitle');
        const subtitle = modal.querySelector('#hmsCrudModalSubtitle');
        const icon = modal.querySelector('.hms-modal-icon');
        modal.querySelector('.hms-modal-message').hidden = true;

        title.textContent = `Edit ${button.dataset.label || 'Entry'}`;
        subtitle.textContent = 'Make changes and save without leaving this page.';
        icon.innerHTML = '<i class="fas fa-pen-to-square"></i>';
        icon.className = 'hms-modal-icon edit';
        body.innerHTML = '<div class="hms-modal-loading"><i class="fas fa-spinner fa-spin"></i> Loading record…</div>';
        openModal(modal);

        try {
            const response = await fetch(button.dataset.editUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error(`Unable to load this entry (${response.status}).`);
            const data = await response.json();

            body.innerHTML = `
                <form class="hms-modal-edit-form" action="${escapeHtml(button.dataset.updateUrl)}" method="POST">
                    <div class="form-grid hms-modal-form-grid">
                        ${data.fields.map(fieldHtml).join('')}
                    </div>
                    <div class="actions end hms-modal-actions">
                        <button type="button" class="btn hms-modal-cancel"><i class="fas fa-xmark"></i> Cancel</button>
                        <button type="submit" class="btn primary"><i class="fas fa-floppy-disk"></i> Save Changes</button>
                    </div>
                </form>`;

            body.querySelector('.hms-modal-cancel').addEventListener('click', () => modal._hmsClose());

            const form = body.querySelector('.hms-modal-edit-form');
            form.addEventListener('submit', async event => {
                event.preventDefault();
                const submit = form.querySelector('button[type="submit"]');
                const old = submit.innerHTML;
                submit.disabled = true;
                submit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
                modal.querySelector('.hms-modal-message').hidden = true;

                const formData = new FormData(form);
                formData.append('_method', 'PUT');

                try {
                    const save = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf()
                        },
                        credentials: 'same-origin',
                        body: formData
                    });
                    const payload = await save.json().catch(() => ({}));
                    if (!save.ok) throw new Error(payload.message || 'The entry could not be updated.');
                    showModalMessage(modal, payload.message || 'Entry updated successfully.', 'success');
                    setTimeout(() => window.location.reload(), 650);
                } catch (error) {
                    showModalMessage(modal, error.message || 'The entry could not be updated.');
                    submit.disabled = false;
                    submit.innerHTML = old;
                }
            });
        } catch (error) {
            body.innerHTML = '';
            showModalMessage(modal, error.message || 'The entry could not be loaded.');
        }
    }

    function openDelete(button) {
        const modal = ensureCrudModal();
        const body = modal.querySelector('.hms-crud-body');
        const title = modal.querySelector('#hmsCrudModalTitle');
        const subtitle = modal.querySelector('#hmsCrudModalSubtitle');
        const icon = modal.querySelector('.hms-modal-icon');
        modal.querySelector('.hms-modal-message').hidden = true;

        const label = button.dataset.label || 'entry';
        title.textContent = `Delete ${label}`;
        subtitle.textContent = 'Confirm this action without leaving the current page.';
        icon.innerHTML = '<i class="fas fa-trash-can"></i>';
        icon.className = 'hms-modal-icon delete';

        body.innerHTML = `
            <div class="hms-delete-confirmation">
                <div class="hms-delete-warning"><i class="fas fa-triangle-exclamation"></i></div>
                <h3>Are you sure?</h3>
                <p>This ${escapeHtml(label)} will be deleted if it is not protected by related hotel records. Financial and audit records remain protected.</p>
            </div>
            <div class="actions end hms-modal-actions">
                <button type="button" class="btn hms-modal-cancel"><i class="fas fa-xmark"></i> Cancel</button>
                <button type="button" class="btn danger hms-confirm-delete"><i class="fas fa-trash-can"></i> Yes, Delete</button>
            </div>`;

        openModal(modal);
        body.querySelector('.hms-modal-cancel').addEventListener('click', () => modal._hmsClose());
        body.querySelector('.hms-confirm-delete').addEventListener('click', async event => {
            const confirm = event.currentTarget;
            const old = confirm.innerHTML;
            confirm.disabled = true;
            confirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting…';

            try {
                const response = await fetch(button.dataset.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf()
                    },
                    credentials: 'same-origin'
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(payload.message || 'The entry could not be deleted.');
                showModalMessage(modal, payload.message || 'Entry deleted successfully.', 'success');
                setTimeout(() => window.location.reload(), 650);
            } catch (error) {
                showModalMessage(modal, error.message || 'The entry could not be deleted.');
                confirm.disabled = false;
                confirm.innerHTML = old;
            }
        });
    }

    document.addEventListener('click', event => {
        const edit = event.target.closest('.hms-edit-entry');
        if (edit) {
            event.preventDefault();
            openEdit(edit);
            return;
        }

        const del = event.target.closest('.hms-delete-entry');
        if (del) {
            event.preventDefault();
            openDelete(del);
        }
    });
})();
