// page-dashboard.jsx — Marketplace Dashboard (Page 1)

function MarketStatusCard() {
  return (
    <section className="p2p-market-card market-status">
      <div className="p2p-market-card__header">
        <div>
          <span className="p2p-market-eyebrow">Marketplace status</span>
          <div className="market-status__row">
            <h2 className="market-status__title">Open · Accepting trades</h2>
            <span className="p2p-market-chip p2p-market-chip--success">
              <span className="p2p-market-chip__dot" />
              Live
            </span>
          </div>
          <p className="market-status__sub">
            All fee tiers active · Settlement window normal · Last config update <b>14 min ago</b> by Rasel M.
          </p>
        </div>
        <div className="market-status__chips">
          <div className="fee-chip">
            <span className="fee-chip__label">Maker fee</span>
            <span className="fee-chip__value fb-num">0.15<sub>%</sub></span>
          </div>
          <div className="fee-chip">
            <span className="fee-chip__label">Taker fee</span>
            <span className="fee-chip__value fb-num">0.40<sub>%</sub></span>
          </div>
          <div className="fee-chip">
            <span className="fee-chip__label">Dispute window</span>
            <span className="fee-chip__value fb-num">30<sub>min</sub></span>
          </div>
          <div className="fee-chip">
            <span className="fee-chip__label">Expiry</span>
            <span className="fee-chip__value fb-num">15<sub>min</sub></span>
          </div>
        </div>
      </div>
    </section>
  );
}

function KPIs() {
  const data = [
    { tone: 'primary', icon: 'fa-coins',          label: '24h volume',     value: '৳ 1,42,38,420', delta: '+18.4%', up: true,  sub: 'vs. prev 24h' },
    { tone: 'success', icon: 'fa-handshake',      label: 'Open offers',    value: '847',           delta: '+62',    up: true,  sub: 'last 24h' },
    { tone: 'warning', icon: 'fa-hourglass-half', label: 'Pending escrow', value: '23',            delta: '+4',     up: true,  sub: 'requires attention' },
    { tone: 'danger',  icon: 'fa-gavel',          label: 'Open disputes',  value: '4',             delta: '−2',     up: false, sub: 'resolved today: 7' },
  ];
  return (
    <div className="p2p-market-kpis">
      {data.map((k) => (
        <div className="p2p-market-kpi" key={k.label}>
          <span className={`p2p-market-kpi__icon p2p-market-kpi__icon--${k.tone}`}>
            <i className={`fa-solid ${k.icon}`} aria-hidden="true" />
          </span>
          <div className="p2p-market-kpi__body">
            <span className="p2p-market-kpi__label">{k.label}</span>
            <span className="p2p-market-kpi__value">{k.value}</span>
            <span className="p2p-market-kpi__sub">
              <span className={`p2p-market-kpi__delta--${k.up ? 'up' : 'down'}`}>
                <i className={`fa-solid fa-arrow-${k.up ? 'up' : 'down'}`} aria-hidden="true" /> {k.delta}
              </span>
              <span style={{ color: 'var(--color-text-faint)' }}>· {k.sub}</span>
            </span>
          </div>
        </div>
      ))}
    </div>
  );
}

// Lightweight area chart drawn with inline SVG
function VolumeChart({ values, height = 220, padX = 16, padY = 18 }) {
  const w = 720;
  const h = height;
  const max = Math.max(...values) * 1.15;
  const min = 0;
  const stepX = (w - padX * 2) / (values.length - 1);
  const points = values.map((v, i) => [
    padX + i * stepX,
    h - padY - ((v - min) / (max - min)) * (h - padY * 2),
  ]);
  const path = points.map((p, i) => (i === 0 ? `M ${p[0]} ${p[1]}` : `L ${p[0]} ${p[1]}`)).join(' ');
  const area = `${path} L ${padX + (values.length - 1) * stepX} ${h - padY} L ${padX} ${h - padY} Z`;
  const gridLines = 4;
  return (
    <svg viewBox={`0 0 ${w} ${h}`} className="p2p-chart" preserveAspectRatio="none">
      <defs>
        <linearGradient id="chartFill" x1="0" x2="0" y1="0" y2="1">
          <stop offset="0%"  stopColor="var(--color-primary)" stopOpacity="0.22" />
          <stop offset="100%" stopColor="var(--color-primary)" stopOpacity="0" />
        </linearGradient>
      </defs>
      {Array.from({ length: gridLines }).map((_, i) => {
        const y = padY + (i * (h - padY * 2)) / (gridLines - 1);
        return <line key={i} x1={padX} x2={w - padX} y1={y} y2={y} stroke="var(--color-border-soft)" strokeDasharray="3 4" />;
      })}
      <path d={area} fill="url(#chartFill)" />
      <path d={path} fill="none" stroke="var(--color-primary)" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
      {points.map((p, i) => (
        <circle key={i} cx={p[0]} cy={p[1]} r="2.5" fill="var(--color-card)" stroke="var(--color-primary)" strokeWidth="1.5" />
      ))}
      {/* X-axis day labels (every 3rd) */}
      {values.map((_, i) => i % 3 === 0 && (
        <text key={i} x={padX + i * stepX} y={h - 3}
              textAnchor="middle"
              fontSize="10" fill="var(--color-text-faint)" fontWeight="600">
          {`D${i + 1}`}
        </text>
      ))}
    </svg>
  );
}

function ChartCard() {
  const values = [42, 51, 47, 63, 58, 72, 81, 76, 88, 95, 89, 102, 117, 124];
  return (
    <section className="fb-card">
      <div className="fb-card__head">
        <div>
          <span className="fb-hero__eyebrow">Trading activity</span>
          <h5>14-day volume</h5>
        </div>
        <div className="fb-card__meta">
          <div className="fb-segment" role="tablist" style={{ marginRight: 8 }}>
            <button className="fb-segment__item">24h</button>
            <button className="fb-segment__item">7d</button>
            <button className="fb-segment__item is-active" aria-selected="true">14d</button>
            <button className="fb-segment__item">30d</button>
          </div>
          <span className="fb-pill fb-pill--success"><i className="fa-solid fa-arrow-trend-up" /><span>+24.6%</span></span>
        </div>
      </div>
      <div className="fb-card__body">
        <div className="chart-headline">
          <div>
            <span className="chart-headline__eyebrow">TOTAL VOLUME · LAST 14 DAYS</span>
            <span className="chart-headline__value fb-num">৳ 18.42 Cr</span>
          </div>
          <div className="chart-headline__legend">
            <span><i className="fa-solid fa-square" style={{ color: 'var(--color-primary)' }} /> Volume</span>
            <span><i className="fa-solid fa-square" style={{ color: 'var(--color-success)' }} /> Settled</span>
          </div>
        </div>
        <VolumeChart values={values} />
      </div>
    </section>
  );
}

function ActivityFeed() {
  const items = [
    { tone: 'success', icon: 'fa-circle-check',         time: '2 min ago', text: <>Trade <b className="fb-mono">#TX-29481</b> settled · ৳ 24,500</> },
    { tone: 'warning', icon: 'fa-triangle-exclamation', time: '11 min ago', text: <>Escrow held on <b className="fb-mono">#TX-29479</b> · awaiting buyer proof</> },
    { tone: 'primary', icon: 'fa-user-plus',            time: '34 min ago', text: <>New verified trader: <b>Naima Hossain</b></> },
    { tone: 'danger',  icon: 'fa-gavel',                time: '1 hr ago',   text: <>Dispute opened on <b className="fb-mono">#TX-29456</b></> },
    { tone: 'success', icon: 'fa-circle-check',         time: '2 hr ago',   text: <>Promotion plan <b>Verified Premium</b> went live</> },
  ];
  return (
    <section className="fb-card">
      <div className="fb-card__head">
        <div>
          <span className="fb-hero__eyebrow">Live feed</span>
          <h5>Recent activity</h5>
        </div>
        <div className="fb-card__meta">
          <span className="live-pill">Streaming</span>
        </div>
      </div>
      <div className="fb-card__body" style={{ padding: 0 }}>
        <ul className="activity-list">
          {items.map((it, i) => (
            <li key={i} className="activity-item">
              <span className={`activity-item__icon activity-item__icon--${it.tone}`}>
                <i className={`fa-solid ${it.icon}`} aria-hidden="true" />
              </span>
              <span className="activity-item__text">{it.text}</span>
              <span className="activity-item__time">{it.time}</span>
            </li>
          ))}
        </ul>
      </div>
      <div className="fb-card__footer">
        <span>Last refreshed 3 sec ago</span>
        <a href="#" className="fb-btn fb-btn--ghost fb-btn--sm">View all <i className="fa-solid fa-arrow-right" /></a>
      </div>
    </section>
  );
}

function TopMethods() {
  const methods = [
    { name: 'bKash',     code: 'BD',  vol: 6420000, share: 38, tone: 'rose' },
    { name: 'Nagad',     code: 'BD',  vol: 4180000, share: 24, tone: 'amber' },
    { name: 'Rocket',    code: 'BD',  vol: 2750000, share: 16, tone: 'violet' },
    { name: 'Bank wire', code: 'BD',  vol: 2100000, share: 12, tone: 'blue' },
    { name: 'Card',      code: 'INT', vol: 1620000, share: 10, tone: 'teal' },
  ];
  return (
    <section className="fb-card">
      <div className="fb-card__head">
        <div>
          <span className="fb-hero__eyebrow">Distribution</span>
          <h5>Top payment methods · 7d</h5>
        </div>
        <div className="fb-card__meta">
          <span className="fb-pill fb-pill--neutral"><i className="fa-solid fa-credit-card" /><span>5 active</span></span>
        </div>
      </div>
      <div className="fb-card__body" style={{ padding: 0 }}>
        <ul className="method-list">
          {methods.map((m) => (
            <li key={m.name} className="method-row">
              <span className={`fb-avatar fb-avatar--sm fb-avatar--${m.tone}`}>{m.name.slice(0, 1)}</span>
              <span className="method-row__name">
                <span>{m.name}</span>
                <span className="method-row__code">{m.code}</span>
              </span>
              <span className="method-row__bar">
                <span style={{ width: `${m.share}%` }} />
              </span>
              <span className="method-row__share fb-num">{m.share}%</span>
            </li>
          ))}
        </ul>
      </div>
    </section>
  );
}

function DashboardPage({ mobile }) {
  return (
    <P2PPage
      titleIcon="fa-gauge-high"
      title="P2P Dashboard"
      actions={
        <>
          <button className="fb-btn fb-btn--ghost fb-btn--sm"><i className="fa-solid fa-arrow-rotate-right" /><span>Refresh</span></button>
          <button className="fb-btn fb-btn--primary fb-btn--sm"><i className="fa-solid fa-gear" /><span>Configure market</span></button>
        </>
      }
    >
      <MarketStatusCard />
      <KPIs />
      <ChartCard />
    </P2PPage>
  );
}

Object.assign(window, { DashboardPage });
