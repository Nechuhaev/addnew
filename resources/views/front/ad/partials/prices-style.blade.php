<style>
    .prices-page h2{margin:28px 0 10px}
    .prices-summary{font-size:16px;line-height:1.55;margin:8px 0 16px}
    .prices-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:0 0 10px}
    .prices-tile{border:1px solid #e3e4e7;border-radius:6px;padding:12px 14px;background:#fff}
    .prices-tile--main{border-color:#3b5998}
    .prices-tile__label{display:block;font-size:13px;color:#6b6f76;margin-bottom:4px}
    .prices-tile__value{display:block;font-size:18px;font-weight:700;color:#19346c}
    .prices-note{font-size:14px;color:#6b6f76;margin:6px 0 0}
    .prices-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
    .prices-table{width:100%;border-collapse:collapse;font-size:15px}
    .prices-table th,.prices-table td{text-align:left;padding:8px 10px;border-bottom:1px solid #e3e4e7;white-space:nowrap}
    .prices-table td:first-child{white-space:normal}
    .prices-table th{font-weight:600;color:#6b6f76;font-size:13px}
    /* Однакові колонки в усіх таблицях сторінки; числа праворуч — розряди одне під одним */
    .prices-table--4,.prices-table--3{table-layout:fixed}
    .prices-table--4 .c-count{width:13%}.prices-table--4 .c-median{width:15%}.prices-table--4 .c-range{width:26%}
    .prices-table--3 .c-count{width:18%}.prices-table--3 .c-median{width:22%}
    .prices-table .num{text-align:right;font-variant-numeric:tabular-nums}
    .prices-table td:first-child{overflow-wrap:anywhere}
    @media (max-width:767px){
        .prices-tiles{grid-template-columns:repeat(2,1fr);gap:8px}
        .prices-tile{padding:10px}
        .prices-tile__value{font-size:16px}
        .prices-table{font-size:14px}
        .prices-table th,.prices-table td{padding:7px 6px}
        /* Мобільний: назва на весь рядок, під нею «оголошень · медіана · діапазон» */
        .prices-table--4,.prices-table--3{table-layout:auto}
        .prices-table--4 thead,.prices-table--3 thead,.prices-table--4 colgroup,.prices-table--3 colgroup{display:none}
        .prices-table--4 tr,.prices-table--3 tr{display:flex;flex-wrap:wrap;align-items:baseline;gap:2px 14px;padding:9px 2px;border-bottom:1px solid #e3e4e7}
        .prices-table--4 td,.prices-table--3 td{display:block;padding:0;border:0;text-align:left}
        .prices-table--4 td:first-child,.prices-table--3 td:first-child{flex:0 0 100%;font-size:15px;margin-bottom:2px}
        .prices-table--4 td.num,.prices-table--3 td.num{text-align:left}
        .prices-table--4 td.num::before,.prices-table--3 td.num::before{content:attr(data-label) " ";color:#6b6f76;font-size:12px}
    }
</style>
