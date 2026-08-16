// page-traders.jsx — Traders list (Page 4)

const TRADER_ROWS = [
  { name: 'Naima Hossain',     handle: 'naima.h',    tone: 'rose',   tier: 'gold',     kyc: 'verified', completion: 98.4, orders: 1247, country: 'BD', avg: 3.1 },
  { name: 'Faisal Karim',      handle: 'faisalk',    tone: 'blue',   tier: 'merchant', kyc: 'verified', completion: 96.1, orders: 982,  country: 'BD', avg: 4.8 },
  { name: 'Tahmina Akter',     handle: 'tahmina_a',  tone: 'violet', tier: 'silver',   kyc: 'pending',  completion: 92.7, orders: 461,  country: 'BD', avg: 6.4 },
  { name: 'Ridwan Hossain',    handle: 'ridwan_h',   tone: 'amber',  tier: 'gold',     kyc: 'verified', completion: 97.8, orders: 873,  country: 'BD', avg: 2.9 },
  { name: 'Mahbub Rahman',     handle: 'mahbub.r',   tone: 'green',  tier: 'merchant', kyc: 'verified', completion: 99.1, orders: 1583, country: 'BD', avg: 1.8 },
  { name: 'Sumaiya Chowdhury', handle: 'sumi.c',     tone: 'teal',   tier: 'bronze',   kyc: 'verified', completion: 87.3, orders: 142,  country: 'BD', avg: 11.5 },
  { name: 'Imran Hossain',     handle: 'imranhs',    tone: 'blue',   tier: 'silver',   kyc: 'rejected', completion: 71.2, orders: 39,   country: 'BD', avg: 28.4, suspended: true },
  { name: 'Anwar Hussain',     handle: 'anwarh',     tone: 'rose',   tier: 'gold',     kyc: 'verified', completion: 95.6, orders: 711,  country: 'BD', avg: 4.2 },
];

const SEGMENTS = [
  { id: 'all',       label: 'All traders',  count: 1247 },
  { id: 'verified',  label: 'Verified',     count: 982  },
  { id: 'merchant',  label: 'Merchants',    count: 64   },
  { id: 'pending',   label: 'KYC pending',  count: 38   },
  { id: 'suspended', label: 'Suspended',    count: 12   },
];

function KycPill({ status }) {
  if (status === 'verified') return <Pill tone="success">Verified</Pill>;
  if (status === 'pending')  return <Pill tone="warning">KYC pending</Pill>;
  if (status === 'rejected') return <Pill tone="danger">KYC rejected</Pill>;
  return <Pill tone="neutral">{status}</Pill>;
}

function TradersToolbar({ activeSegment, onSegment }) {
  return (
    <div className="fb-toolbar">
      <div className="fb-search">
        <i className="fa-solid fa-magnifying-glass fb-search__icon" />
        <input type="search" placeholder="Search name, handle, phone…" />
      </div>
      <div className="fb-segment" role="tablist">
        {SEGMENTS.map((s) => (
          <button
            key={s.id}
            className={`fb-segment__item ${s.id === activeSegment ? 'is-active' : ''}`}
            role="tab"
            aria-selected={s.id === activeSegment}
            aria-pressed={s.id === activeSegment}
            onClick={() => onSegment && onSegment(s.id)}
          >
            <span>{s.label}</span>
            <span className="fb-segment__count">{s.count}</span>
          </button>
        ))}
      </div>
      <div className="fb-toolbar__spacer" />
      <button className="fb-btn fb-btn--ghost fb-btn--sm">
        <i className="fa-solid fa-filter" />
        <span>Filters</span>
      </button>
      <button className="fb-btn fb-btn--ghost fb-btn--sm">
        <i className="fa-solid fa-download" />
        <span>Export</span>
      </button>
    </div>
  );
}

function TradersPage({ mobile }) {
  const [seg, setSeg] = React.useState('all');
  return (
    <P2PPage
      titleIcon="fa-users"
      title="Traders"
      actions={
        <>
          <button className="fb-btn fb-btn--ghost fb-btn--sm"><i className="fa-solid fa-arrow-rotate-right" /><span>Sync KYC</span></button>
          <button className="fb-btn fb-btn--primary fb-btn--sm"><i className="fa-solid fa-user-plus" /><span>Invite trader</span></button>
        </>
      }
    >
      <section className="fb-card">
        <div className="fb-card__head">
          <div>
            <span className="fb-hero__eyebrow">Directory</span>
            <h5>{SEGMENTS.find((s) => s.id === seg).label}</h5>
          </div>
          <div className="fb-card__meta">
            <span className="fb-pill fb-pill--neutral">Total <b>{SEGMENTS.find((s) => s.id === seg).count.toLocaleString()}</b></span>
            <span className="fb-pill fb-pill--success"><i className="fa-solid fa-circle-check" /><span>62 new this week</span></span>
          </div>
        </div>

        <TradersToolbar activeSegment={seg} onSegment={setSeg} />

        <div className="fb-card__body fb-card__body--flush">
          <div className="fb-table table-responsive">
            <table className="pa-table">
              <thead>
                <tr>
                  <th>Trader</th>
                  <th>Tier</th>
                  <th>KYC</th>
                  <th>Orders</th>
                  <th>Completion</th>
                  <th>Avg release</th>
                  <th aria-label="Actions" />
                </tr>
              </thead>
              <tbody>
                {TRADER_ROWS.map((r) => (
                  <tr key={r.handle} style={r.suspended ? { opacity: 0.65 } : null}>
                    <td className="col-trader" data-label="Trader">
                      <User name={r.name} handle={r.handle} tone={r.tone} meta={r.suspended ? 'Suspended · 3d ago' : null} />
                    </td>
                    <td data-label="Tier"><Tier kind={r.tier} /></td>
                    <td data-label="KYC"><KycPill status={r.kyc} /></td>
                    <td className="num fb-num" data-label="Orders">{r.orders.toLocaleString()}</td>
                    <td data-label="Completion">
                      <Progress value={r.completion} />
                    </td>
                    <td className="num fb-num" data-label="Avg release">{r.avg.toFixed(1)} min</td>
                    <td className="actions">
                      <span className="fb-btn-group">
                        <button className="fb-btn fb-btn--ghost fb-btn--sm">View</button>
                        {r.suspended
                          ? <button className="fb-btn fb-btn--success fb-btn--sm"><i className="fa-solid fa-rotate-left" /><span>Reactivate</span></button>
                          : <button className="fb-btn fb-btn--ghost fb-btn--sm" style={{ color: 'var(--color-danger-ink)' }}><i className="fa-solid fa-ban" /><span>Suspend</span></button>
                        }
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        <div className="fb-card__footer pa-table__foot">
          <Pagination shown="1–8" total={42} totalItems={1247} />
        </div>
      </section>
    </P2PPage>
  );
}

Object.assign(window, { TradersPage });
