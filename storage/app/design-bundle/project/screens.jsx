/* global React */
// Screens render at natural device resolution; frames CSS-scale them.

const DASH_W = 1280, DASH_H = 794;   // dashboard native
const PHONE_W = 390, PHONE_H = 800;  // phone native

const sIcon = (name, color = "currentColor", size = 16) => {
  const props = { width: size, height: size, viewBox: "0 0 24 24", fill: "none", stroke: color, strokeWidth: 2, strokeLinecap: "round", strokeLinejoin: "round" };
  const paths = {
    grid: <><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></>,
    wallet: <><path d="M3 7h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/><path d="M16 12h2"/></>,
    users: <><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></>,
    card: <><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></>,
    star: <polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9"/>,
    trending: <><polyline points="22 7 13 16 9 12 2 19"/><polyline points="16 7 22 7 22 13"/></>,
    swap: <><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></>,
    ticket: <><path d="M3 7v4a2 2 0 0 1 0 4v4h18v-4a2 2 0 0 1 0-4V7z"/><path d="M13 7v12"/></>,
    phone: <><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/></>,
    check: <polyline points="20 6 9 17 4 12"/>,
    arrow: <><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></>,
    bell: <><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></>,
    deposit: <><path d="M12 5v14"/><polyline points="5 12 12 19 19 12"/></>,
    withdraw: <><path d="M12 19V5"/><polyline points="19 12 12 5 5 12"/></>,
    request: <><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></>,
    reward: <><circle cx="12" cy="8" r="6"/><polyline points="8.21 13.89 7 22 12 19 17 22 15.79 13.88"/></>,
    ticket2: <path d="M3 7v4a2 2 0 0 1 0 4v4h18v-4a2 2 0 0 1 0-4V7z"/>,
    send: <><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></>,
    receive: <><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></>,
    p2p: <><circle cx="9" cy="7" r="3"/><circle cx="17" cy="11" r="2"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></>,
    support: <><circle cx="12" cy="12" r="9"/><path d="M9 9a3 3 0 0 1 6 0c0 2-3 2-3 4"/><line x1="12" y1="17" x2="12" y2="17.01"/></>,
    more: <><circle cx="6" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="18" cy="12" r="1.5"/></>,
    qr: <><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zm4 4h3v3h-3z"/></>,
    home: <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>,
    history: <><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></>,
    menu: <><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></>,
    shield: <path d="M12 2l8 4v6c0 5-4 9-8 10-4-1-8-5-8-10V6z"/>,
    cards: <><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></>,
    settings: <><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></>,
  };
  return <svg {...props}>{paths[name]}</svg>;
};

// ============== Dashboard Screen — 1280 × 794 native ==============
const DashboardScreen = () => {
  const navItems = [
    { label: "Dashboard Overview", icon: "grid", active: true },
    { label: "Add & Withdraw Funds", icon: "wallet" },
    { label: "P2P Marketplace", icon: "users" },
    { label: "My Virtual Cards", icon: "card" },
    { label: "Subscriptions", icon: "star" },
    { label: "Wallet Earn", icon: "trending" },
    { label: "Transfer & Exchange", icon: "swap" },
    { label: "My Vouchers", icon: "ticket" },
    { label: "Mobile Recharge", icon: "phone" },
  ];

  // smooth chart path generator
  const makePath = (points, w, h, pad) => {
    const max = Math.max(...points), min = Math.min(...points);
    const range = max - min || 1;
    const step = (w - pad * 2) / (points.length - 1);
    let d = "";
    for (let i = 0; i < points.length; i++) {
      const x = pad + i * step;
      const y = h - ((points[i] - min) / range) * (h - pad * 2) - pad;
      if (i === 0) d += `M ${x} ${y}`;
      else {
        const prevX = pad + (i - 1) * step;
        const cx = (prevX + x) / 2;
        d += ` Q ${cx} ${y} ${x} ${y}`;
      }
    }
    return d;
  };
  const depositData = [38, 52, 47, 60, 78, 92, 80, 95, 72, 88, 82, 105, 118, 95, 82, 90, 78];
  const withdrawData = [42, 35, 50, 45, 58, 52, 68, 60, 75, 62, 80, 72, 88, 75, 92, 80, 95];
  const chartW = 540, chartH = 165, chartPad = 12;
  const depositPath = makePath(depositData, chartW, chartH, chartPad);
  const withdrawPath = makePath(withdrawData, chartW, chartH, chartPad);

  return (
    <div style={{
      width: DASH_W, height: DASH_H, background: "#f5f7fb",
      display: "grid", gridTemplateColumns: "280px 1fr",
      fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#0f172a", overflow: "hidden",
    }}>
      {/* Sidebar */}
      <aside style={{ background: "#fff", borderRight: "1px solid #eef0f5", padding: "22px 16px", display: "flex", flexDirection: "column", gap: 18 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 11, padding: "0 6px 6px" }}>
          <div style={{ width: 36, height: 36, borderRadius: 10, background: "linear-gradient(135deg,#3b82f6,#8b5cf6)", display: "grid", placeItems: "center", color: "#fff", fontWeight: 800, fontSize: 16 }}>D</div>
          <div>
            <div style={{ fontSize: 17, fontWeight: 800, letterSpacing: "-0.01em" }}>DigiKash</div>
            <div style={{ fontSize: 9, color: "#94a3b8", letterSpacing: "0.14em", fontWeight: 600 }}>WALLET · WALLET</div>
          </div>
        </div>

        {/* Wallet card */}
        <div style={{ background: "linear-gradient(135deg,#2563eb 0%, #1e3a8a 100%)", borderRadius: 16, padding: 16, color: "#fff", position: "relative", overflow: "hidden", boxShadow: "0 10px 30px rgba(37,99,235,0.25)" }}>
          <div style={{ position: "absolute", right: -30, top: -30, width: 110, height: 110, borderRadius: "50%", background: "rgba(255,255,255,0.08)" }} />
          <div style={{ position: "absolute", right: 20, bottom: -40, width: 90, height: 90, borderRadius: "50%", background: "rgba(255,255,255,0.05)" }} />
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", position: "relative" }}>
            <div style={{ fontSize: 11, opacity: 0.9, display: "flex", alignItems: "center", gap: 6, fontWeight: 600 }}>
              <span style={{ width: 7, height: 7, borderRadius: "50%", background: "#fff" }} />USD Personal Wallet
            </div>
            <div style={{ display: "flex", gap: 4 }}>
              <span style={{ width: 18, height: 18, borderRadius: 5, background: "rgba(255,255,255,0.15)", display: "grid", placeItems: "center", fontSize: 11, fontWeight: 600 }}>‹</span>
              <span style={{ width: 18, height: 18, borderRadius: 5, background: "rgba(255,255,255,0.15)", display: "grid", placeItems: "center", fontSize: 11, fontWeight: 600 }}>›</span>
            </div>
          </div>
          <div style={{ fontSize: 10, opacity: 0.75, marginTop: 9, position: "relative" }}>Wallet ID: DK*129x21</div>
          <div style={{ fontSize: 32, fontWeight: 800, marginTop: 2, letterSpacing: "-0.025em", position: "relative" }}>$18,500.<span style={{ fontSize: 21 }}>00</span></div>
          <div style={{ display: "flex", gap: 8, marginTop: 14, position: "relative" }}>
            <button style={{ flex: 1, background: "rgba(255,255,255,0.18)", border: "1px solid rgba(255,255,255,0.28)", color: "#fff", padding: "8px 0", borderRadius: 9, fontSize: 11, fontWeight: 700 }}>+ Deposit</button>
            <button style={{ flex: 1, background: "rgba(255,255,255,0.18)", border: "1px solid rgba(255,255,255,0.28)", color: "#fff", padding: "8px 0", borderRadius: 9, fontSize: 11, fontWeight: 700 }}>≡ My Wallets</button>
          </div>
        </div>

        {/* Nav */}
        <nav style={{ display: "flex", flexDirection: "column", gap: 2 }}>
          {navItems.map((n, i) => (
            <div key={i} style={{
              display: "flex", alignItems: "center", gap: 11, padding: "9px 11px", borderRadius: 9,
              background: n.active ? "#eff4ff" : "transparent",
              color: n.active ? "#2563eb" : "#475569",
              fontSize: 12, fontWeight: n.active ? 700 : 500,
            }}>
              {sIcon(n.icon, n.active ? "#2563eb" : "#94a3b8", 15)}
              <span style={{ flex: 1 }}>{n.label}</span>
              <span style={{ fontSize: 12, opacity: 0.4 }}>›</span>
            </div>
          ))}
        </nav>

        {/* Referral */}
        <div style={{ background: "linear-gradient(135deg,#ecfdf5,#d1fae5)", borderRadius: 12, padding: 13, marginTop: "auto" }}>
          <div style={{ display: "inline-block", background: "#10b981", color: "#fff", fontSize: 8, padding: "3px 7px", borderRadius: 4, fontWeight: 800, letterSpacing: "0.06em" }}>★ REFERRAL BOOST</div>
          <div style={{ fontSize: 12, fontWeight: 800, marginTop: 7, color: "#064e3b" }}>Invite friends & earn</div>
          <div style={{ fontSize: 9, color: "#047857", marginTop: 2, lineHeight: 1.4 }}>Share your link and earn rewards.</div>
        </div>
      </aside>

      {/* Main */}
      <main style={{ padding: "20px 24px", display: "flex", flexDirection: "column", gap: 14, overflow: "hidden" }}>
        {/* Top bar */}
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
          <div>
            <div style={{ fontSize: 11, color: "#94a3b8", fontWeight: 500 }}>User Dashboard › <span style={{ color: "#475569" }}>Dashboard Overview</span></div>
            <div style={{ fontSize: 19, fontWeight: 800, marginTop: 2, letterSpacing: "-0.015em" }}>Dashboard Overview</div>
          </div>
          <div style={{ display: "flex", gap: 9, alignItems: "center" }}>
            <div style={{ width: 28, height: 28, borderRadius: 8, background: "#fff", border: "1px solid #e2e8f0", display: "grid", placeItems: "center" }}>{sIcon("grid", "#475569", 13)}</div>
            <div style={{ display: "flex", alignItems: "center", gap: 5, padding: "6px 10px", border: "1px solid #e2e8f0", borderRadius: 8, fontSize: 11, fontWeight: 600, background: "#fff" }}>
              <span style={{ width: 14, height: 10, background: "linear-gradient(#3c3b6e 50%, #b22234 50%)", borderRadius: 1.5 }} />English ▾
            </div>
            <div style={{ width: 28, height: 28, borderRadius: 8, background: "#fff", border: "1px solid #e2e8f0", display: "grid", placeItems: "center", position: "relative" }}>
              {sIcon("bell", "#475569", 13)}
              <span style={{ position: "absolute", top: -2, right: -2, width: 9, height: 9, borderRadius: "50%", background: "#ef4444", border: "2px solid #fff" }} />
            </div>
            <div style={{ width: 28, height: 28, borderRadius: 8, background: "#fff", border: "1px solid #e2e8f0", display: "grid", placeItems: "center" }}>{sIcon("settings", "#475569", 13)}</div>
            <div style={{ display: "flex", alignItems: "center", gap: 7, padding: "4px 11px 4px 4px", borderRadius: 100, border: "1px solid #e2e8f0", background: "#fff" }}>
              <div style={{ width: 24, height: 24, borderRadius: "50%", background: "linear-gradient(135deg,#f472b6,#ec4899)", display: "grid", placeItems: "center", color: "#fff", fontSize: 10, fontWeight: 700 }}>AR</div>
              <div style={{ lineHeight: 1.2 }}>
                <div style={{ fontSize: 11, fontWeight: 700 }}>Ayesha Rahman</div>
                <div style={{ fontSize: 9, color: "#10b981", display: "flex", alignItems: "center", gap: 3 }}><span style={{ width: 5, height: 5, borderRadius: "50%", background: "#10b981" }} /> Online · Verified</div>
              </div>
            </div>
          </div>
        </div>

        {/* Welcome card */}
        <div style={{ background: "#fff", borderRadius: 14, padding: "14px 18px", display: "flex", justifyContent: "space-between", alignItems: "center", border: "1px solid #eef0f5" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 13 }}>
            <div style={{ width: 40, height: 40, borderRadius: "50%", background: "linear-gradient(135deg,#f472b6,#ec4899)", display: "grid", placeItems: "center", color: "#fff", fontSize: 14, fontWeight: 700 }}>AR</div>
            <div>
              <div style={{ fontSize: 9, color: "#94a3b8", letterSpacing: "0.1em", fontWeight: 700 }}>NEW FEATURES</div>
              <div style={{ fontSize: 16, fontWeight: 800, letterSpacing: "-0.015em" }}>Welcome back, Ayesha</div>
              <div style={{ fontSize: 11, color: "#64748b" }}>Here's how your wallet is doing today.</div>
            </div>
          </div>
          <div style={{ display: "flex", gap: 7 }}>
            <button style={{ padding: "8px 12px", border: "1px solid #e2e8f0", borderRadius: 9, fontSize: 11, fontWeight: 600, background: "#fff" }}>📅 Tue, 12 May 2026</button>
            <button style={{ padding: "8px 12px", border: "1px solid #e2e8f0", borderRadius: 9, fontSize: 11, fontWeight: 600, background: "#fff" }}>👛 Wallets</button>
            <button style={{ padding: "8px 12px", background: "#2563eb", color: "#fff", borderRadius: 9, fontSize: 11, fontWeight: 700, border: "none" }}>⇄ Transactions</button>
          </div>
        </div>

        {/* KYC bar */}
        <div style={{ background: "#fff", borderRadius: 14, padding: "12px 18px", display: "flex", justifyContent: "space-between", alignItems: "center", border: "1px solid #eef0f5" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 11 }}>
            <div style={{ width: 26, height: 26, borderRadius: "50%", background: "#dcfce7", display: "grid", placeItems: "center" }}>{sIcon("check", "#16a34a", 14)}</div>
            <div>
              <div style={{ fontSize: 12, fontWeight: 800 }}>KYC verified</div>
              <div style={{ fontSize: 10, color: "#64748b" }}>Your identity verification is complete. No further KYC action is required right now.</div>
            </div>
          </div>
          <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
            <span style={{ background: "#dcfce7", color: "#15803d", padding: "5px 11px", borderRadius: 100, fontSize: 10, fontWeight: 800, display: "inline-flex", alignItems: "center", gap: 4 }}><span style={{ width: 5, height: 5, borderRadius: "50%", background: "#15803d" }} /> KYC Complete</span>
            <button style={{ padding: "6px 12px", border: "1.5px solid #2563eb", color: "#2563eb", borderRadius: 8, fontSize: 10, fontWeight: 700, background: "#fff" }}>+ View Status</button>
            <span style={{ color: "#94a3b8", fontSize: 14 }}>×</span>
          </div>
        </div>

        {/* Stat cards grid */}
        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 11 }}>
          {[
            { label: "Deposit", value: "$11,000.00", sub: "€100.00", icon: "deposit", tint: "#dbeafe", color: "#2563eb" },
            { label: "Withdraw", value: "$0", icon: "withdraw", tint: "#fee2e2", color: "#ef4444" },
            { label: "Exchange Money", value: "$0", icon: "swap", tint: "#fef3c7", color: "#d97706" },
            { label: "Request Money", value: "$0", icon: "request", tint: "#fce7f3", color: "#ec4899" },
            { label: "Reward", value: "€10.00", icon: "reward", tint: "#ede9fe", color: "#7c3aed" },
            { label: "Total Tickets", value: "0", icon: "ticket2", tint: "#cffafe", color: "#0891b2" },
          ].map((s, i) => (
            <div key={i} style={{ background: "#fff", border: "1px solid #eef0f5", borderRadius: 12, padding: "10px 13px", display: "flex", alignItems: "center", gap: 10 }}>
              <div style={{ width: 32, height: 32, borderRadius: 9, background: s.tint, display: "grid", placeItems: "center" }}>{sIcon(s.icon, s.color, 15)}</div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 13, fontWeight: 800, letterSpacing: "-0.01em" }}>{s.value}{s.sub && <span style={{ fontSize: 10, color: "#94a3b8", marginLeft: 4, fontWeight: 600 }}>, {s.sub}</span>}</div>
                <div style={{ fontSize: 10, color: "#64748b" }}>{s.label}</div>
              </div>
              <span style={{ color: "#cbd5e1", fontSize: 14 }}>›</span>
            </div>
          ))}
        </div>

        {/* Chart row */}
        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 11, flex: 1, minHeight: 0 }}>
          {[
            { title: "Deposits", value: "$10,000.00", color: "#10b981", path: depositPath, badge: "▲" },
            { title: "Withdrawals", value: "$0.00", color: "#ef4444", path: withdrawPath, badge: "▼" },
          ].map((c, i) => (
            <div key={i} style={{ background: "#fff", border: "1px solid #eef0f5", borderRadius: 12, padding: "12px 14px", display: "flex", flexDirection: "column" }}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start" }}>
                <div style={{ display: "flex", alignItems: "center", gap: 7 }}>
                  <div style={{ width: 22, height: 22, borderRadius: 6, background: c.color + "22", color: c.color, fontSize: 9, fontWeight: 800, display: "grid", placeItems: "center" }}>{c.badge}</div>
                  <div>
                    <div style={{ fontSize: 12, fontWeight: 800 }}>{c.title}</div>
                    <div style={{ fontSize: 9, color: "#94a3b8" }}>Last 7 days</div>
                  </div>
                </div>
                <div style={{ fontSize: 14, fontWeight: 800, color: c.color }}>{c.value}</div>
              </div>
              <div style={{ flex: 1, position: "relative", marginTop: 6, display: "flex" }}>
                <div style={{ display: "flex", flexDirection: "column", justifyContent: "space-between", paddingRight: 6, fontSize: 8, color: "#cbd5e1" }}>
                  <span>11201</span><span>8960.8</span><span>6720.6</span><span>4480.4</span><span>2240.2</span><span>0</span>
                </div>
                <svg viewBox={`0 0 ${chartW} ${chartH}`} preserveAspectRatio="none" style={{ flex: 1, width: "100%", height: "100%" }}>
                  <defs>
                    <linearGradient id={`grad-${i}`} x1="0" x2="0" y1="0" y2="1">
                      <stop offset="0%" stopColor={c.color} stopOpacity="0.35"/>
                      <stop offset="100%" stopColor={c.color} stopOpacity="0"/>
                    </linearGradient>
                  </defs>
                  {[0, 1, 2, 3, 4, 5].map(y => <line key={y} x1="0" x2={chartW} y1={chartPad + y * ((chartH - chartPad * 2) / 5)} y2={chartPad + y * ((chartH - chartPad * 2) / 5)} stroke="#f1f5f9" strokeWidth="1"/>)}
                  <path d={`${c.path} L ${chartW - chartPad} ${chartH - chartPad} L ${chartPad} ${chartH - chartPad} Z`} fill={`url(#grad-${i})`} />
                  <path d={c.path} stroke={c.color} strokeWidth="2" fill="none" />
                </svg>
              </div>
              <div style={{ display: "flex", justifyContent: "space-between", fontSize: 9, color: "#94a3b8", marginTop: 4, paddingLeft: 30 }}>
                <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
              </div>
            </div>
          ))}
        </div>

        {/* Recent transactions */}
        <div style={{ background: "#fff", border: "1px solid #eef0f5", borderRadius: 12, padding: "11px 14px" }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
            <div>
              <div style={{ fontSize: 12, fontWeight: 800 }}>Recent Transactions</div>
              <div style={{ fontSize: 9, color: "#94a3b8" }}>Latest wallet activity and status updates</div>
            </div>
          </div>
          <div style={{ display: "flex", alignItems: "center", gap: 11, marginTop: 8, paddingTop: 8, borderTop: "1px solid #f1f5f9" }}>
            <div style={{ width: 26, height: 26, borderRadius: "50%", background: "#dbeafe", display: "grid", placeItems: "center", color: "#2563eb" }}>{sIcon("arrow", "#2563eb", 13)}</div>
            <div style={{ flex: 1 }}>
              <div style={{ fontSize: 11, fontWeight: 700 }}>Demo send money to family contact.</div>
              <div style={{ fontSize: 9, color: "#94a3b8", marginTop: 1, display: "flex", gap: 6, alignItems: "center" }}>
                <span style={{ color: "#2563eb", fontWeight: 800 }}>$100 MONEY</span>
                <span style={{ background: "#dcfce7", color: "#15803d", padding: "2px 6px", borderRadius: 3, fontWeight: 800 }}>COMPLETED</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
};

// ============== Phone Screen — 390 × 800 native ==============
const PhoneScreen = () => (
  <div style={{ width: PHONE_W, height: PHONE_H, background: "#f5f7fb", display: "flex", flexDirection: "column", fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#0f172a", overflow: "hidden" }}>
    {/* Status bar */}
    <div style={{ padding: "12px 28px 0", display: "flex", justifyContent: "space-between", fontSize: 14, fontWeight: 700, color: "#0f172a" }}>
      <span>9:41</span>
      <div style={{ display: "flex", gap: 6, alignItems: "center" }}>
        <span style={{ fontSize: 11 }}>●●●●</span>
        <svg width="15" height="11" viewBox="0 0 15 11" fill="none"><path d="M7.5 1.5C9.5 1.5 11.3 2.3 12.7 3.6M4.2 5.5C5.1 4.6 6.3 4 7.5 4C8.7 4 9.9 4.6 10.8 5.5M7.5 8.5L7.5 8.6" stroke="#0f172a" strokeWidth="1.4" strokeLinecap="round"/></svg>
        <span style={{ width: 22, height: 11, border: "1.2px solid #0f172a", borderRadius: 3, position: "relative", display: "inline-block" }}>
          <span style={{ position: "absolute", inset: 1.5, background: "#0f172a", borderRadius: 1.5, width: "75%" }} />
        </span>
      </div>
    </div>

    {/* Header */}
    <div style={{ padding: "16px 18px 12px", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
      <div style={{ display: "flex", gap: 11, alignItems: "center" }}>
        <div style={{ width: 44, height: 44, borderRadius: "50%", background: "linear-gradient(135deg,#cbd5e1,#94a3b8)", display: "grid", placeItems: "center", color: "#fff", fontSize: 16, fontWeight: 800 }}>Md</div>
        <div>
          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 500 }}>Welcome back</div>
          <div style={{ fontSize: 17, fontWeight: 800, display: "flex", alignItems: "center", gap: 7 }}>Md <span style={{ background: "#dcfce7", color: "#15803d", fontSize: 9, padding: "2px 7px", borderRadius: 100, fontWeight: 800 }}>✓ VERIFIED</span></div>
        </div>
      </div>
      <div style={{ display: "flex", gap: 8 }}>
        {["menu", "qr", "shield", "bell"].map(i => (
          <div key={i} style={{ width: 32, height: 32, borderRadius: 9, background: "#fff", display: "grid", placeItems: "center", border: "1px solid #eef0f5" }}>{sIcon(i, "#475569", 16)}</div>
        ))}
      </div>
    </div>

    {/* Wallet card */}
    <div style={{ margin: "0 18px", background: "linear-gradient(135deg,#10b981 0%, #0891b2 100%)", borderRadius: 20, padding: 18, color: "#fff", position: "relative", overflow: "hidden", boxShadow: "0 12px 30px rgba(16,185,129,0.3)" }}>
      <div style={{ position: "absolute", right: -40, top: -40, width: 130, height: 130, borderRadius: "50%", background: "rgba(255,255,255,0.1)" }} />
      <div style={{ position: "absolute", right: 30, bottom: -50, width: 100, height: 100, borderRadius: "50%", background: "rgba(255,255,255,0.06)" }} />
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", position: "relative" }}>
        <div style={{ fontSize: 13, opacity: 0.95, display: "flex", alignItems: "center", gap: 6, fontWeight: 600 }}>
          <span style={{ width: 8, height: 8, borderRadius: "50%", background: "#fff" }} />USD Personal Wallet
        </div>
        <div style={{ display: "flex", gap: 5 }}>
          <span style={{ width: 24, height: 24, borderRadius: 7, background: "rgba(255,255,255,0.18)", display: "grid", placeItems: "center", fontSize: 13 }}>⌂</span>
          <span style={{ width: 24, height: 24, borderRadius: 7, background: "rgba(255,255,255,0.18)", display: "grid", placeItems: "center", fontSize: 13 }}>⋯</span>
        </div>
      </div>
      <div style={{ fontSize: 11, opacity: 0.85, marginTop: 8, position: "relative" }}>Wallet ID: ZK*1227132Z</div>
      <div style={{ fontSize: 38, fontWeight: 800, marginTop: 2, letterSpacing: "-0.025em", position: "relative" }}>$0.<span style={{ fontSize: 24 }}>00</span></div>
      <div style={{ display: "flex", gap: 10, marginTop: 14, position: "relative" }}>
        <button style={{ flex: 1, background: "rgba(255,255,255,0.2)", border: "1px solid rgba(255,255,255,0.3)", color: "#fff", padding: "10px 0", borderRadius: 11, fontSize: 13, fontWeight: 700 }}>+ Deposit</button>
        <button style={{ flex: 1, background: "rgba(255,255,255,0.2)", border: "1px solid rgba(255,255,255,0.3)", color: "#fff", padding: "10px 0", borderRadius: 11, fontSize: 13, fontWeight: 700 }}>≡ My Wallets</button>
      </div>
    </div>

    {/* Verify identity */}
    <div style={{ margin: "14px 18px 0", background: "#fff", border: "1px solid #eef0f5", borderRadius: 14, padding: "12px 14px", display: "flex", alignItems: "center", gap: 11 }}>
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 13, fontWeight: 800 }}>Verify your identity</div>
        <div style={{ fontSize: 10, color: "#94a3b8", marginTop: 1 }}>Submit your details and...</div>
      </div>
      <button style={{ background: "#2563eb", color: "#fff", border: "none", padding: "7px 12px", borderRadius: 8, fontSize: 11, fontWeight: 700 }}>Start verification</button>
      <span style={{ color: "#cbd5e1", fontSize: 16 }}>×</span>
    </div>

    {/* Wallet actions */}
    <div style={{ margin: "16px 18px 0" }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 10 }}>
        <div style={{ fontSize: 14, fontWeight: 800 }}>Wallet actions</div>
        <div style={{ fontSize: 11, color: "#2563eb", fontWeight: 700 }}>All ›</div>
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 9 }}>
        {[
          { label: "Deposit", icon: "deposit", color: "#2563eb", bg: "#dbeafe" },
          { label: "Withdraw", icon: "withdraw", color: "#f59e0b", bg: "#fef3c7" },
          { label: "Send", icon: "send", color: "#10b981", bg: "#dcfce7" },
          { label: "Receive", icon: "receive", color: "#8b5cf6", bg: "#ede9fe" },
        ].map((a, i) => (
          <div key={i} style={{ background: "#fff", border: "1px solid #eef0f5", borderRadius: 12, padding: "12px 4px", textAlign: "center" }}>
            <div style={{ width: 34, height: 34, borderRadius: 10, background: a.bg, display: "grid", placeItems: "center", margin: "0 auto 6px" }}>{sIcon(a.icon, a.color, 17)}</div>
            <div style={{ fontSize: 11, fontWeight: 700 }}>{a.label}</div>
          </div>
        ))}
      </div>
    </div>

    {/* Quick actions */}
    <div style={{ margin: "16px 18px 0", flex: 1, minHeight: 0 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 10 }}>
        <div style={{ fontSize: 14, fontWeight: 800 }}>Quick actions</div>
        <div style={{ fontSize: 11, color: "#2563eb", fontWeight: 700 }}>All ›</div>
      </div>
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", rowGap: 12, columnGap: 9 }}>
        {[
          { label: "Request", icon: "request", color: "#ec4899" },
          { label: "Exchange", icon: "swap", color: "#f59e0b" },
          { label: "Cards", icon: "cards", color: "#2563eb" },
          { label: "Recharge", icon: "phone", color: "#10b981" },
          { label: "Voucher", icon: "ticket2", color: "#8b5cf6" },
          { label: "P2P", icon: "p2p", color: "#06b6d4" },
          { label: "Support", icon: "support", color: "#f43f5e" },
          { label: "More", icon: "more", color: "#64748b" },
        ].map((a, i) => (
          <div key={i} style={{ textAlign: "center" }}>
            <div style={{ width: 38, height: 38, borderRadius: 11, background: "#fff", border: "1px solid #eef0f5", display: "grid", placeItems: "center", margin: "0 auto 5px" }}>{sIcon(a.icon, a.color, 17)}</div>
            <div style={{ fontSize: 10, color: "#475569", fontWeight: 700 }}>{a.label}</div>
          </div>
        ))}
      </div>
    </div>

    {/* Bottom nav */}
    <div style={{ background: "#fff", borderTop: "1px solid #eef0f5", padding: "10px 22px 18px", display: "flex", justifyContent: "space-around", alignItems: "center" }}>
      {[
        { label: "Wallet", icon: "wallet" },
        { label: "QR Scan", icon: "qr" },
        { label: "home", icon: "home", center: true },
        { label: "History", icon: "history" },
        { label: "More", icon: "more" },
      ].map((n, i) => n.center ? (
        <div key={i} style={{ width: 54, height: 54, borderRadius: "50%", background: "linear-gradient(135deg,#3b82f6,#1e40af)", display: "grid", placeItems: "center", marginTop: -22, boxShadow: "0 8px 20px rgba(59,130,246,0.45)" }}>{sIcon("home", "#fff", 22)}</div>
      ) : (
        <div key={i} style={{ textAlign: "center" }}>
          {sIcon(n.icon, "#94a3b8", 18)}
          <div style={{ fontSize: 10, color: "#94a3b8", marginTop: 2, fontWeight: 600 }}>{n.label}</div>
        </div>
      ))}
    </div>
  </div>
);

// ============== Scaled wrapper ==============
const ScaledScreen = ({ width, height, nativeWidth, nativeHeight, children }) => {
  const scale = Math.min(width / nativeWidth, height / nativeHeight);
  return (
    <div style={{ width, height, overflow: "hidden", position: "relative" }}>
      <div style={{ width: nativeWidth, height: nativeHeight, transform: `scale(${scale})`, transformOrigin: "0 0" }}>
        {children}
      </div>
    </div>
  );
};

Object.assign(window, { DashboardScreen, PhoneScreen, ScaledScreen, DASH_W, DASH_H, PHONE_W, PHONE_H });
