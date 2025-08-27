<!-- ================= THEME: Crimson Red + Peach (shared) ================= -->
<style>
  :root{
    --bg: var(--bs-body-bg);
    --ink: var(--bs-body-color);
    --muted: var(--bs-secondary-color);

    --brand: #A4193D;   /* Crimson Red */
    --brand-2: #8C1433;
    --peach: #FFDFB9;   /* Peach */

    --thead-soft: #F6EEF2;

    --radius: 16px;
    --radius-md: 12px;
    --shadow: 0 10px 30px rgba(2, 6, 23, .08);
    --ring: 0 0 0 1px rgba(2,6,23,.06) inset;

    --kpi-h: 116px;

    --table-row-h: 56px;
    --crit-row-h: 56px;

    --scroll-thumb: #D9A4B3;
    --scroll-track: transparent;
  }

  .fw-700{font-weight:700}
  .muted{color:var(--muted)}
  .text-brand{ color: var(--brand) !important; }

  .neo-hero{ position: relative; padding: 28px 0 10px; overflow: clip; }
  .neo-hero__bg{
    position:absolute; inset:0; pointer-events:none;
    background:
      radial-gradient(60% 100% at 85% 0%, rgba(164,25,61,.16), transparent 60%),
      radial-gradient(50% 80% at 5% 10%, rgba(255,223,185,.40), transparent 60%),
      linear-gradient(180deg, rgba(164,25,61,.06), transparent 35%);
    filter: saturate(106%); opacity:.9;
  }
  .eyebrow{ text-transform: uppercase; letter-spacing:.12em; font-size:.72rem; color:var(--muted); }

  .neo-main{ margin-top: 6px; padding-bottom: 2.75rem; }

  .neo-card{
    background: var(--bg);
    border-radius: var(--radius);
    border: 1px solid rgba(2,6,23,.06);
    box-shadow: var(--shadow);
    overflow: hidden;
    animation: fadeUp .35s ease both;
    height: auto;
  }
  .neo-card__head{ padding: 1rem 1rem; border-bottom: 1px solid rgba(2,6,23,.06); }
  .neo-card__body{ padding: 1rem; }

  .pair-row .pair-card{ display:flex; flex-direction:column; height:100%; min-height: clamp(360px, 42vh, 520px); }
  @media (max-width: 1199.98px){ .pair-row .pair-card{ min-height: unset; } }

  .kpi-strip > [class*="col-"]{ display:flex; }
  .neo-kpi{ display:flex; gap:.9rem; align-items:center; padding:.9rem 1rem; min-height: var(--kpi-h); }
  .neo-kpi__icon{
    width: 48px; height: 48px; display:grid; place-items:center; border-radius:14px;
    background: linear-gradient(135deg, rgba(164,25,61,.10), rgba(164,25,61,.02));
    box-shadow: var(--ring); flex:0 0 48px;
  }
  .neo-kpi__body .label{ font-size:.8rem; color:var(--muted); margin:0;}
  .neo-kpi__body .value{ font-weight:800; font-size:1.2rem; letter-spacing:.2px;}
  .neo-kpi__body .sub{ font-size:.76rem; color:var(--muted); }

  .btn-neo{
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.6rem .9rem; border-radius:.8rem;
    background: linear-gradient(135deg, var(--brand), var(--brand-2)) !important;
    color:#fff !important; border:1px solid rgba(164,25,61,.45) !important;
    box-shadow: 0 6px 18px rgba(164,25,61,.28);
    text-decoration:none; transition: transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease;
  }
  .btn-neo:hover{ filter:brightness(1.05); transform:translateY(-1px); box-shadow:0 10px 26px rgba(164,25,61,.36);}
  .btn-neo:active{ transform:translateY(0); filter:brightness(.98);}
  .btn-neo:focus-visible{ outline:3px solid rgba(164,25,61,.35); outline-offset:2px; }

  .btn-ghost{
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.55rem .85rem; border-radius:.8rem; background:transparent !important;
    color:var(--ink) !important; border:1px solid rgba(2,6,23,.12) !important;
    text-decoration:none; transition:.16s;
  }
  .btn-ghost:hover{ background:rgba(255,223,185,.45) !important; border-color:rgba(164,25,61,.35) !important; color:var(--brand) !important; transform:translateY(-1px); }

  /* Chip size untuk aksi kecil (header & meta) */
  .btn-xxs{ font-size:.74rem; padding:.35rem .6rem; border-radius:.8rem; line-height:1; }

  .badge{
    display:inline-flex; align-items:center; justify-content:center;
    min-width:28px; height:22px; border-radius:999px;
    font-weight:800; font-size:.78rem; padding:0 .5rem;
  }
  /* Badge peach: kontras teks agar angka terlihat */
  .badge-peach{
    background: var(--peach);
    color: var(--brand) !important;
    border:1px solid rgba(164,25,61,.25);
  }

  .table-shell{ background:var(--bg); border:1px solid rgba(2,6,23,.07); border-radius:12px; padding:.4rem; box-shadow:0 10px 26px rgba(164,25,61,.10); }
  .table-head-soft th{ background:#F6EEF2; border-bottom:1px solid rgba(164,25,61,.12); font-weight:700; }
  .table td, .table th{ padding:.9rem 1rem; }
  .table-fixed-rows tbody tr{ height: var(--table-row-h); }
  .table tbody tr:nth-of-type(odd){ background-color: rgba(2,6,23,.02); }

  /* SCROLLBAR PINK */
  .dash-neo .nice-scroll{ scrollbar-color: var(--scroll-thumb) var(--scroll-track); scrollbar-width: thin; }
  .dash-neo .nice-scroll::-webkit-scrollbar{ width:10px; height:10px; }
  .dash-neo .nice-scroll::-webkit-scrollbar-thumb{ background-color: var(--scroll-thumb); border-radius:999px; border:3px solid transparent; background-clip:content-box; }
  .dash-neo .nice-scroll::-webkit-scrollbar-track{ background: var(--scroll-track); }

  @keyframes fadeUp { from { opacity:0; transform: translateY(6px);} to { opacity:1; transform:none; } }

  @media (prefers-color-scheme: dark){
    :root{
      --shadow: 0 12px 28px rgba(0,0,0,.35);
      --ring: 0 0 0 1px rgba(255,255,255,.10) inset;
      --thead-soft: #24161B;
      --scroll-thumb: #C98598;
    }
    .neo-card{ border-color: rgba(255,255,255,.08); }
    .neo-card__head{ border-color: rgba(255,255,255,.08); }
    .table-shell{ border-color: rgba(255,255,255,.10); box-shadow: 0 8px 22px rgba(0,0,0,.35); }
  }

  @media (max-width: 991.98px){ .neo-hero{ padding-top:22px; } .neo-card__body{ padding:.9rem; } }
  @media (max-width: 576px){ .btn-neo, .btn-ghost{ padding:.5rem .75rem; } }
</style>

