<style>
    .st-toolbar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:14px}
    .st-range{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
    .st-range .form-control{width:auto}
    .st-tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:16px}
    .st-tile{border:1px solid #eee;border-radius:6px;padding:12px 14px}
    .st-tile__v{font-size:24px;font-weight:700;color:#19346c;line-height:1.1}
    .st-tile__l{color:#6c757d;font-size:13px}
    .st-table th,.st-table td{vertical-align:middle;padding:.45rem .5rem}
    .st-table th a{color:inherit}
    .st-table thead th{font-size:13px;line-height:1.25}
    .st-table th a.is-sorted{color:#19346c;font-weight:700}
    .st-table .st-filter th{background:#f8f9fa;padding:.3rem .4rem}
    .st-table .st-filter input,.st-table .st-filter select{width:100%;min-width:60px;height:28px;padding:2px 6px;font-size:12px}
    .st-table tfoot td{font-weight:700;background:#f8f9fa}
    .st-name{min-width:180px;word-break:break-word}
    .st-pager{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin-top:10px}
    .st-pager .pagination{margin:0}
</style>
<script>
// Не відправляти порожні фільтри — коротші й читабельні посилання
document.addEventListener('submit', function (e) {
    if (!e.target.closest('.card-body')) return;
    e.target.querySelectorAll('input, select').forEach(function (el) {
        if (el.name && el.type !== 'checkbox' && el.value === '') el.disabled = true;
    });
}, true);
</script>
