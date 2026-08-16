// shell.jsx — Admin chrome (sidebar + outer header) and per-page AdminFrame
// In production these are provided by the Laravel admin layout — we mock
// them here so the redesigned content area renders inside a believable shell.

// Mirrors config/admin_menus.php (P2P section, exact items, exact order).
// Six items, no fabricated sections.
const NAV_GROUPS = [
  {
    label: 'P2P',
    items: [
      { id: 'p2pdash',  label: 'P2P Dashboard',    icon: 'fa-gauge-high' },
      { id: 'settings', label: 'Settings',         icon: 'fa-sliders' },
      { id: 'pm',       label: 'Payment Methods',  icon: 'fa-credit-card' },
      { id: 'traders',  label: 'Traders',          icon: 'fa-users' },
      { id: 'disputes', label: 'Disputes',         icon: 'fa-gavel', badge: 4 },
      { id: 'promos',   label: 'Promotions',       icon: 'fa-star' },
    ],
  },
];

function AdminShell({ activeId, children, viewport = 'desktop' }) {
  const isMobile = viewport === 'mobile';
  return (
    <div className={`adm ${isMobile ? 'adm--mobile' : ''}`}>
      {!isMobile && (
        <aside className="adm__sidebar">
          <div className="adm__brand">
            <div className="adm__brand-mark"><i className="fa-solid fa-bolt" /></div>
            <div>
              <div className="adm__brand-name">Digikash</div>
              <div className="adm__brand-sub">P2P · Admin</div>
            </div>
          </div>
          <nav className="adm__nav">
            {NAV_GROUPS.map((g) => (
              <div key={g.label} className="adm__nav-group">
                <div className="adm__nav-label">{g.label}</div>
                {g.items.map((it) => (
                  <a
                    key={it.id}
                    className={`adm__nav-item ${activeId === it.id ? 'is-active' : ''}`}
                    href="#"
                    onClick={(e) => e.preventDefault()}
                  >
                    <i className={`fa-solid ${it.icon}`} aria-hidden="true" />
                    <span>{it.label}</span>
                    {it.badge && <span className="adm__nav-badge">{it.badge}</span>}
                  </a>
                ))}
              </div>
            ))}
          </nav>
          <div className="adm__sidebar-foot">
            <div className="fb-avatar fb-avatar--sm fb-avatar--violet">RM</div>
            <div style={{ minWidth: 0 }}>
              <div className="adm__sidebar-foot-name">Rasel M.</div>
              <div className="adm__sidebar-foot-role">Super admin</div>
            </div>
          </div>
        </aside>
      )}
      <main className="adm__main">
        <header className={`adm__header ${isMobile ? 'adm__header--mobile' : ''}`}>
          {isMobile && (
            <button className="adm__menu" aria-label="Menu">
              <i className="fa-solid fa-bars" />
            </button>
          )}
          <div className="adm__crumb">
            <span className="adm__crumb-root">Admin</span>
            <i className="fa-solid fa-chevron-right adm__crumb-sep" />
            <span className="adm__crumb-section">P2P</span>
          </div>
          <div className="adm__header-actions">
            <button className="adm__icon-btn" aria-label="Search"><i className="fa-solid fa-magnifying-glass" /></button>
            <button className="adm__icon-btn" aria-label="Notifications">
              <i className="fa-regular fa-bell" />
              <span className="adm__icon-btn-dot" />
            </button>
            {!isMobile && <div className="fb-avatar fb-avatar--sm fb-avatar--violet">RM</div>}
          </div>
        </header>
        {children}
      </main>
    </div>
  );
}

// Per-page wrapper that mirrors the Laravel layout slots
// (.p2p-admin-topbar with title + actions, optional .p2p-nav, then content)
function P2PPage({ titleIcon, title, actions, tabs, activeTab, children }) {
  return (
    <div className="p2p-admin">
      <div className="p2p-admin-topbar">
        <div className="p2p-admin-topbar__title">
          {titleIcon && (
            <span className="pa-title-icon">
              <i className={`fa-solid ${titleIcon}`} aria-hidden="true" />
            </span>
          )}
          <h1>{title}</h1>
        </div>
        {actions && <div className="p2p-admin-topbar__actions">{actions}</div>}
      </div>
      {tabs && tabs.length > 0 && (
        <div className="p2p-nav" role="tablist">
          {tabs.map((t) => (
            <span
              key={t.id}
              className={`p2p-nav__item ${activeTab === t.id ? 'is-active' : ''}`}
              role="tab"
              aria-selected={activeTab === t.id}
            >
              {t.label}
              {t.count != null && <span className="count">{t.count}</span>}
            </span>
          ))}
        </div>
      )}
      {children}
    </div>
  );
}

Object.assign(window, { AdminShell, P2PPage });
