/* global React */
/* Shared icons + app chrome for Digikash dashboard screens */

const Icon = ({ name, size = 18, stroke = 2, color = 'currentColor', fill = 'none', style }) => {
  const s = { width: size, height: size, color, ...style };
  const p = { fill, stroke: 'currentColor', strokeWidth: stroke, strokeLinecap: 'round', strokeLinejoin: 'round' };
  switch (name) {
    case 'dashboard': return <svg viewBox="0 0 24 24" style={s} {...p}><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>;
    case 'wallet':    return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M3 7h15a3 3 0 0 1 3 3v7a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7z"/><path d="M3 7V5a2 2 0 0 1 2-2h11"/><circle cx="17" cy="14" r="1.5" fill="currentColor"/></svg>;
    case 'send':      return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>;
    case 'qr':        return <svg viewBox="0 0 24 24" style={s} {...p}><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3z M20 14v3 M14 20h3 M20 20v1"/></svg>;
    case 'gift':      return <svg viewBox="0 0 24 24" style={s} {...p}><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M5 12v9h14v-9"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C10 3 12 5.5 12 8c0-2.5 2-5 4.5-5a2.5 2.5 0 0 1 0 5"/></svg>;
    case 'voucher':   return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V8z"/><path d="M9 8v8" strokeDasharray="2 2"/></svg>;
    case 'recharge':  return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z" fill="currentColor"/></svg>;
    case 'history':   return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/><path d="M12 7v5l3 2"/></svg>;
    case 'settings':  return <svg viewBox="0 0 24 24" style={s} {...p}><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.13.32.4.6.74.74H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>;
    case 'search':    return <svg viewBox="0 0 24 24" style={s} {...p}><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>;
    case 'bell':      return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>;
    case 'plus':      return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M12 5v14M5 12h14"/></svg>;
    case 'chevron-right': return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M9 18l6-6-6-6"/></svg>;
    case 'chevron-down':  return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M6 9l6 6 6-6"/></svg>;
    case 'check':     return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M5 12l5 5L20 7"/></svg>;
    case 'sparkles':  return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M12 3l1.6 4.4L18 9l-4.4 1.6L12 15l-1.6-4.4L6 9l4.4-1.6L12 3z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14z" fill="currentColor"/></svg>;
    case 'envelope':  return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1z"/><path d="M3 7l9 7 9-7"/></svg>;
    case 'copy':      return <svg viewBox="0 0 24 24" style={s} {...p}><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>;
    case 'dots-v':    return <svg viewBox="0 0 24 24" style={s} {...p}><circle cx="12" cy="5" r="1.6" fill="currentColor"/><circle cx="12" cy="12" r="1.6" fill="currentColor"/><circle cx="12" cy="19" r="1.6" fill="currentColor"/></svg>;
    case 'eye':       return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>;
    case 'edit':      return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>;
    case 'trash':     return <svg viewBox="0 0 24 24" style={s} {...p}><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>;
    case 'calendar':  return <svg viewBox="0 0 24 24" style={s} {...p}><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>;
    case 'user':      return <svg viewBox="0 0 24 24" style={s} {...p}><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>;
    case 'filter':    return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M3 4h18l-7 9v6l-4 2v-8L3 4z"/></svg>;
    case 'image':     return <svg viewBox="0 0 24 24" style={s} {...p}><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>;
    case 'upload':    return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><path d="M12 3v12"/></svg>;
    case 'paint':     return <svg viewBox="0 0 24 24" style={s} {...p}><circle cx="13.5" cy="6.5" r="2.5" fill="currentColor"/><circle cx="19" cy="13" r="2.5" fill="currentColor"/><circle cx="6" cy="12" r="2.5" fill="currentColor"/><circle cx="10" cy="20" r="2.5" fill="currentColor"/><path d="M12 22a10 10 0 1 1 10-10c0 2.76-3 5-5 5h-2a2 2 0 0 0-2 2 4 4 0 0 1-1 3z"/></svg>;
    case 'mail':      return <svg viewBox="0 0 24 24" style={s} {...p}><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 7 9-7"/></svg>;
    case 'arrow-right': return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M5 12h14M13 6l6 6-6 6"/></svg>;
    case 'arrow-left':  return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M19 12H5M11 18l-6-6 6-6"/></svg>;
    case 'shield':    return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg>;
    case 'clock':     return <svg viewBox="0 0 24 24" style={s} {...p}><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>;
    case 'lock':      return <svg viewBox="0 0 24 24" style={s} {...p}><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>;
    case 'x':         return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M18 6L6 18M6 6l12 12"/></svg>;
    case 'box':       return <svg viewBox="0 0 24 24" style={s} {...p}><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7L12 12l8.7-5M12 22V12"/></svg>;
    default: return null;
  }
};
window.Icon = Icon;

/* ----- Sidebar ------------------------------------------- */
const Sidebar = ({ active = 'gift' }) => {
  const items = [
    { id: 'dashboard', label: 'Dashboard', icon: 'dashboard' },
    { id: 'wallet',    label: 'Wallets',   icon: 'wallet' },
    { id: 'send',      label: 'Transfers', icon: 'send' },
    { id: 'qr',        label: 'QR & Pay',  icon: 'qr' },
  ];
  const money = [
    { id: 'recharge',  label: 'Recharge',  icon: 'recharge' },
    { id: 'voucher',   label: 'Vouchers',  icon: 'voucher' },
    { id: 'gift',      label: 'Gift Cards', icon: 'gift', badge: 'NEW' },
    { id: 'history',   label: 'History',   icon: 'history' },
  ];
  const item = (it) => (
    <div key={it.id} className={`dk-side-item ${active === it.id ? 'active' : ''}`}>
      <Icon name={it.icon} size={17} />
      {it.label}
      {it.badge ? <span className="badge">{it.badge}</span> : null}
    </div>
  );
  return (
    <aside className="dk-side">
      <div className="dk-side-brand">
        <div className="mark">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none">
            <path d="M4 7h12a4 4 0 0 1 0 8H4V7z" fill="#fff"/>
            <circle cx="15" cy="11" r="1.6" fill="#1d4ed8"/>
          </svg>
        </div>
        <div>
          <div className="name">Digi<b>Kash</b></div>
          <div className="tag">v2.0 · Business</div>
        </div>
      </div>
      <div className="dk-side-group">Main</div>
      {items.map(item)}
      <div className="dk-side-group">Money</div>
      {money.map(item)}
      <div style={{ flex: 1 }} />
      <div className="dk-side-group">Account</div>
      <div className="dk-side-item"><Icon name="settings" size={17}/>Settings</div>
      <div className="dk-side-item"><Icon name="shield" size={17}/>Security</div>
    </aside>
  );
};
window.Sidebar = Sidebar;

/* ----- Topbar -------------------------------------------- */
const Topbar = ({ title = 'Gift Cards' }) => (
  <header className="dk-topbar">
    <div className="search">
      <Icon name="search" size={16} />
      Search transactions, recipients…
    </div>
    <div className="spacer" />
    <div className="iconbtn"><Icon name="bell" size={17}/><span className="pip"/></div>
    <div className="iconbtn"><Icon name="envelope" size={17}/></div>
    <div className="me">
      <div className="who" style={{ textAlign: 'right' }}>
        <div className="nm">Ayesha Rahman</div>
        <div className="rl">Premium · USD</div>
      </div>
      <div className="av">AR</div>
    </div>
  </header>
);
window.Topbar = Topbar;

/* Shared lightweight components ---------------------------- */
const Breadcrumb = ({ items }) => (
  <nav className="breadcrumb" aria-label="Breadcrumb">
    {items.map((it, i) => (
      <React.Fragment key={i}>
        {i > 0 ? <Icon name="chevron-right" size={12} style={{ color: '#94A3B8' }}/> : null}
        {i === items.length - 1 ? <span className="cur">{it}</span> : <a href="#">{it}</a>}
      </React.Fragment>
    ))}
  </nav>
);
window.Breadcrumb = Breadcrumb;

/* Sample gift-card data ----------------------------------- */
const SAMPLE_CARDS_SENT = [
  { tpl: 'birthday',    name: 'Tahsin Ahmed',   email: 'tahsin@gmail.com',     amount: 100, status: 'delivered', date: 'May 18, 2026', code: 'DKGC-4F2A-9X7K' },
  { tpl: 'congrats',    name: 'Marketing Team', email: 'team@digikash.io',     amount: 250, status: 'pending',   date: 'May 17, 2026', code: 'DKGC-7T3R-2P8N' },
  { tpl: 'thankyou',    name: 'Priya Kapoor',   email: 'priya.k@gmail.com',    amount: 50,  status: 'redeemed',  date: 'May 12, 2026', code: 'DKGC-1B9M-5L4Q' },
  { tpl: 'anniversary', name: 'Daniel Wilson',  email: 'd.wilson@outlook.com', amount: 200, status: 'delivered', date: 'May 09, 2026', code: 'DKGC-8K2J-7H3D' },
  { tpl: 'holiday',     name: 'Mom',            email: 'rashida@yahoo.com',    amount: 75,  status: 'expired',   date: 'Apr 24, 2026', code: 'DKGC-3W1V-6Y9C' },
];
window.SAMPLE_CARDS_SENT = SAMPLE_CARDS_SENT;

const STATUS_META = {
  pending:   { label: 'Pending',   cls: 'badge-warning' },
  delivered: { label: 'Delivered', cls: 'badge-info' },
  redeemed:  { label: 'Redeemed',  cls: 'badge-success' },
  expired:   { label: 'Expired',   cls: 'badge-muted' },
  cancelled: { label: 'Cancelled', cls: 'badge-danger' },
};
window.STATUS_META = STATUS_META;
