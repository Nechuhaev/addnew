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
    @media (max-width:767px){
        .prices-tiles{grid-template-columns:repeat(2,1fr);gap:8px}
        .prices-tile{padding:10px}
        .prices-tile__value{font-size:16px}
        .prices-table{font-size:14px}
        .prices-table th,.prices-table td{padding:7px 6px}
    }
</style>
