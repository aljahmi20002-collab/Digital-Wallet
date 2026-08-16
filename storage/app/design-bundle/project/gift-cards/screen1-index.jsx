/* global React, Sidebar, Topbar, Breadcrumb, Icon, GiftCardDesign, SAMPLE_CARDS_SENT, STATUS_META, formatMoney */
const { useState } = React;

/* ===========================================================
   SCREEN 1 — Gift Cards · Index (list of sent/received/redeemed)
   =========================================================== */

const HeroBanner = () => (
  <div style={{
    position: 'relative',
    borderRadius: 24,
    overflow: 'hidden',
    padding: '28px 32px',
    background: 'linear-gradient(120deg, #0F1B3F 0%, #1E3A8A 50%, #3B82F6 100%)',
    color: '#fff',
    boxShadow: '0 20px 40px rgba(15,23,42,.18)',
    display: 'flex', alignItems: 'center', justifyContent: 'space-between',
    gap: 32, minHeight: 188,
  }}>
    {/* mesh decoration */}
    <svg style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', opacity: 0.4 }} viewBox="0 0 600 200" preserveAspectRatio="none">
      <defs>
        <radialGradient id="hb1" cx="85%" cy="20%" r="40%">
          <stop offset="0%" stopColor="#60A5FA" stopOpacity=".7"/>
          <stop offset="100%" stopColor="#60A5FA" stopOpacity="0"/>
        </radialGradient>
        <radialGradient id="hb2" cx="10%" cy="100%" r="50%">
          <stop offset="0%" stopColor="#FBBF24" stopOpacity=".4"/>
          <stop offset="100%" stopColor="#FBBF24" stopOpacity="0"/>
        </radialGradient>
        <pattern id="hbd" width="14" height="14" patternUnits="userSpaceOnUse">
          <circle cx="1" cy="1" r="1" fill="#fff" opacity="0.10"/>
        </pattern>
      </defs>
      <rect width="600" height="200" fill="url(#hb1)"/>
      <rect width="600" height="200" fill="url(#hb2)"/>
      <rect width="600" height="200" fill="url(#hbd)"/>
    </svg>

    <div style={{ position: 'relative', zIndex: 1, maxWidth: 480 }}>
      <div style={{
        display: 'inline-flex', alignItems: 'center', gap: 6,
        padding: '5px 10px',
        background: 'rgba(251,191,36,.16)', border: '1px solid rgba(251,191,36,.40)',
        color: '#FCD34D', borderRadius: 999,
        fontSize: 10.5, fontWeight: 800, letterSpacing: '.10em', textTransform: 'uppercase',
        marginBottom: 12,
      }}>
        <Icon name="sparkles" size={12}/> New · Gift Cards
      </div>
      <h1 style={{ fontSize: 30, fontWeight: 800, letterSpacing: '-.025em', margin: 0, lineHeight: 1.05 }}>
        Send the perfect gift,<br/>
        <em style={{ fontStyle: 'normal', background: 'linear-gradient(90deg,#FDE68A,#FBBF24)', WebkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent' }}>instantly</em>
      </h1>
      <p style={{ fontSize: 13.5, color: '#B8C4E8', margin: '8px 0 16px', maxWidth: 380 }}>
        Designed templates, personal messages, scheduled delivery. Funded straight from your DigiKash wallet.
      </p>
      <div style={{ display: 'flex', gap: 10 }}>
        <button className="btn btn-base">
          <Icon name="plus" size={15}/> Create Gift Card
        </button>
        <button className="btn btn-light" style={{ background: 'rgba(255,255,255,.10)', borderColor: 'rgba(255,255,255,.20)', color: '#fff', boxShadow: 'none' }}>
          <Icon name="qr" size={15}/> Redeem Code
        </button>
      </div>
    </div>

    <div style={{ position: 'relative', zIndex: 1, display: 'flex', gap: -34, alignItems: 'center' }}>
      <div style={{ transform: 'rotate(-7deg) translateX(34px)', filter: 'drop-shadow(0 20px 30px rgba(0,0,0,.35))' }}>
        <GiftCardDesign template="anniversary" width={220} amount={100} recipient="Daniel" sender="Ayesha"/>
      </div>
      <div style={{ transform: 'rotate(6deg)', position: 'relative', zIndex: 2, filter: 'drop-shadow(0 20px 30px rgba(0,0,0,.35))' }}>
        <GiftCardDesign template="birthday" width={230} amount={50} recipient="Tahsin" sender="Ayesha"/>
      </div>
    </div>
  </div>
);

const StatCard = ({ icon, iconBg, iconColor, label, value, sub }) => (
  <div className="stat-card">
    <div className="ic" style={{ background: iconBg, color: iconColor }}><Icon name={icon} size={22}/></div>
    <div className="body">
      <div className="lbl">{label}</div>
      <div className="val money">{value}</div>
      <div className="pct">{sub}</div>
    </div>
  </div>
);

const GiftCardRow = ({ card, kind = 'sent' }) => {
  const meta = STATUS_META[card.status];
  return (
    <div style={{
      display: 'grid',
      gridTemplateColumns: '116px 1fr auto auto',
      gap: 18,
      alignItems: 'center',
      background: '#fff',
      border: '1px solid var(--dk-card-line)',
      borderRadius: 16,
      padding: 14,
      boxShadow: 'var(--dk-shadow-sm)',
    }}>
      <div style={{ width: 102, height: 64, display: 'grid', placeItems: 'center' }}>
        <GiftCardDesign template={card.tpl} width={102} amount={card.amount} recipient={card.name} sender="You"/>
      </div>
      <div style={{ minWidth: 0 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <div style={{ fontWeight: 700, fontSize: 14, color: 'var(--dk-ink)' }}>{kind === 'received' ? `From ${card.name}` : `To ${card.name}`}</div>
          <span className={`badge ${meta.cls}`}><span className="dot"/>{meta.label}</span>
        </div>
        <div style={{ fontSize: 12, color: 'var(--dk-mute)', marginTop: 3 }}>
          {card.email} · Sent {card.date}
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 8 }}>
          <code style={{
            fontFamily: 'ui-monospace, "SF Mono", Menlo, monospace',
            fontSize: 11.5, fontWeight: 700,
            padding: '3px 7px',
            background: '#F1F5F9', color: '#475569',
            borderRadius: 6, letterSpacing: '.08em',
          }}>{card.code}</code>
          <span style={{ fontSize: 11.5, color: 'var(--dk-mute)' }}>· {TEMPLATES[card.tpl].label} template</span>
        </div>
      </div>
      <div style={{ textAlign: 'right' }}>
        <div className="money" style={{ fontSize: 20, fontWeight: 800, letterSpacing: '-.01em', color: 'var(--dk-ink)' }}>
          {formatMoney(card.amount)}
        </div>
        <div style={{ fontSize: 11.5, color: 'var(--dk-mute)', marginTop: 2 }}>USD</div>
      </div>
      <div style={{ display: 'flex', gap: 6 }}>
        <button className="btn btn-light btn-sm" aria-label="View"><Icon name="eye" size={14}/> View</button>
        <button className="btn btn-light btn-sm" aria-label="More" style={{ padding: '7px 9px' }}><Icon name="dots-v" size={14}/></button>
      </div>
    </div>
  );
};

const Screen1Index = () => {
  const [tab, setTab] = useState('sent');
  return (
    <div className="dk-app" data-screen-label="01 Gift Cards — Index">
      <div className="dk-content">
          {/* Page head */}
          <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 20 }}>
            <div>
              <Breadcrumb items={['Dashboard', 'Money', 'Gift Cards']}/>
              <h1 className="page-title">Gift Cards</h1>
              <p className="page-sub">Send a designed gift card from your wallet, or redeem one you’ve received.</p>
            </div>
            <div style={{ display: 'flex', gap: 10 }}>
              <button className="btn btn-light-success"><Icon name="qr" size={15}/> Redeem Code</button>
              <button className="btn btn-base"><Icon name="plus" size={15}/> Create Gift Card</button>
            </div>
          </div>

          {/* Hero */}
          <HeroBanner/>

          {/* Stats */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16, marginTop: 20 }}>
            <StatCard icon="send"   iconBg="#DBEAFE" iconColor="#1D4ED8" label="Sent"        value="12"       sub="+3 this month"/>
            <StatCard icon="gift"   iconBg="#FCE7F3" iconColor="#BE185D" label="Received"    value="5"        sub="2 unredeemed"/>
            <StatCard icon="wallet" iconBg="#DCFCE7" iconColor="#15803D" label="Total Value" value="$450.00"  sub="USD across 17 cards"/>
          </div>

          {/* Tabs + filters */}
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 24, marginBottom: 14 }}>
            <div className="tabs" role="tablist">
              <button className={`tab ${tab==='sent' ? 'is-active' : ''}`}     onClick={() => setTab('sent')}     role="tab">Sent <span className="count">12</span></button>
              <button className={`tab ${tab==='received' ? 'is-active' : ''}`} onClick={() => setTab('received')} role="tab">Received <span className="count">5</span></button>
              <button className={`tab ${tab==='redeemed' ? 'is-active' : ''}`} onClick={() => setTab('redeemed')} role="tab">Redeemed <span className="count">8</span></button>
            </div>
            <div style={{ display: 'flex', gap: 8 }}>
              <button className="btn btn-light btn-sm"><Icon name="filter" size={13}/> All status</button>
              <button className="btn btn-light btn-sm"><Icon name="calendar" size={13}/> Last 30 days</button>
            </div>
          </div>

          {/* Card list */}
          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {SAMPLE_CARDS_SENT.map((c, i) => <GiftCardRow key={i} card={c} kind={tab}/>)}
          </div>

          {/* Footer pager */}
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 18 }}>
            <div style={{ fontSize: 12.5, color: 'var(--dk-mute)' }}>Showing 1–5 of 12 sent gift cards</div>
            <div style={{ display: 'flex', gap: 6 }}>
              <button className="btn btn-light btn-sm"><Icon name="arrow-left" size={13}/> Prev</button>
              <button className="btn btn-light btn-sm">Next <Icon name="arrow-right" size={13}/></button>
            </div>
        </div>
      </div>
    </div>
  );
};
window.Screen1Index = Screen1Index;
