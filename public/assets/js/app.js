function setYearInputsFromPreset() {
    const preset = document.getElementById('yearPreset');
    const from = document.getElementById('yearFrom');
    const to = document.getElementById('yearTo');

    if (!preset || !from || !to) return;

    const v = preset.value;
    const y = new Date().getFullYear();

    if (v === '') return;
    if (v === 'last5') {
        from.value = y - 5;
        to.value = y;
    }
    if (v === 'last10') {
        from.value = y - 10;
        to.value = y;
    }
    if (v === 'classic') {
        from.value = 1900;
        to.value = 1999;
    }
}

function toInt(v) {
    const n = parseInt(String(v).trim(), 10);
    return Number.isFinite(n) ? n : null;
}

function updateRangeCart() {
    const fromEl = document.getElementById('rangeFrom');
    const toEl = document.getElementById('rangeTo');
    const priceEl = document.getElementById('pricePerPage');
    const maxEl = document.getElementById('maxPages');
    const bookPagesEl = document.getElementById('bookPages');

    const countEl = document.getElementById('rangeCount');
    const totalEl = document.getElementById('rangeTotal');
    const msgEl = document.getElementById('rangeMsg');
    const summaryEl = document.getElementById('rangeSummary');
    const btnEl = document.getElementById('btnPayRange');

    const formFrom = document.getElementById('formFrom');
    const formTo = document.getElementById('formTo');

    if (!fromEl || !toEl || !priceEl || !maxEl || !bookPagesEl || !countEl || !totalEl || !msgEl || !summaryEl) {
        return;
    }

    const from = toInt(fromEl.value);
    const to = toInt(toEl.value);
    const price = parseFloat(priceEl.value) || 0;
    const maxPages = toInt(maxEl.value) ?? 15;
    const bookPages = toInt(bookPagesEl.value) ?? 0;

    msgEl.classList.add('d-none');
    msgEl.textContent = '';
    countEl.textContent = '0';
    totalEl.textContent = '0 сом';
    summaryEl.textContent = '—';

    if (btnEl) {
        btnEl.disabled = true;
    }

    if (formFrom) formFrom.value = '';
    if (formTo) formTo.value = '';

    if (from === null || to === null) {
        return;
    }

    if (from <= 0 || to <= 0) {
        msgEl.textContent = 'Номера страниц должны быть больше 0.';
        msgEl.classList.remove('d-none');
        return;
    }

    if (from > bookPages || to > bookPages) {
        msgEl.textContent = `В этой книге только ${bookPages} страниц.`;
        msgEl.classList.remove('d-none');
        return;
    }

    if (to < from) {
        msgEl.textContent = 'Конечная страница не может быть меньше начальной.';
        msgEl.classList.remove('d-none');
        return;
    }

    const count = (to - from) + 1;
    const total = count * price;

    countEl.textContent = String(count);
    totalEl.textContent = total.toFixed(2) + ' сом';
    summaryEl.textContent = `${from}–${to}`;

    if (count > maxPages) {
        msgEl.textContent = `Можно выбрать максимум ${maxPages} страниц за один заказ. Сейчас выбрано: ${count}.`;
        msgEl.classList.remove('d-none');
        return;
    }

    if (btnEl) {
        btnEl.disabled = false;
    }

    if (formFrom) formFrom.value = from;
    if (formTo) formTo.value = to;
}

document.addEventListener('DOMContentLoaded', function () {
    const preset = document.getElementById('yearPreset');
    if (preset) {
        preset.addEventListener('change', setYearInputsFromPreset);
    }

    const fromEl = document.getElementById('rangeFrom');
    const toEl = document.getElementById('rangeTo');

    if (fromEl && toEl) {
        fromEl.addEventListener('input', updateRangeCart);
        toEl.addEventListener('input', updateRangeCart);
        updateRangeCart();
    }
});