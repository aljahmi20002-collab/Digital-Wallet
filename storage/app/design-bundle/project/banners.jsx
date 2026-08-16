/* global React, DashboardScreen, PhoneScreen */

// ============== Shared icons used in banner chrome ==============
const BIcon = ({ name, color = "currentColor", size = 24 }) => {
  const props = { width: size, height: size, viewBox: "0 0 24 24", fill: "none", stroke: color, strokeWidth: 2, strokeLinecap: "round", strokeLinejoin: "round" };
  const paths = {
    p2p: <><circle cx="9" cy="8" r="3"/><circle cx="17" cy="11" r="2.5"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M14 21v-1a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3v1"/></>,
    mobile: <><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M10 18h4"/></>,
    wallet: <><path d="M3 7h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/><path d="M3 7l2-3h12l2 3"/><circle cx="17" cy="13" r="1.5"/></>,
    qr: <><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h2v2h-2zm4 0h3v3h-3zm-4 4h2v3h-2zm4 4h3"/></>,
    crown: <><path d="M3 6l4 6 5-8 5 8 4-6v12H3z"/></>,
    link: <><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 1 0-7-7l-1 1"/><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 1 0 7 7l1-1"/></>,
    role: <><circle cx="12" cy="12" r="3"/><path d="M12 1v3M12 20v3M3 12H1M23 12h-2M5.6 5.6L4 4M20 20l-1.6-1.6M5.6 18.4L4 20M20 4l-1.6 1.6"/></>,
    users: <><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></>,
    card: <><rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20M6 15h4"/></>,
    pwa: <><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></>,
    shield: <><path d="M12 2l8 4v6c0 5-4 9-8 10-4-1-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/></>,
    star: <polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9"/>,
    arrow: <><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></>,
    sparkle: <><path d="M12 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2z"/></>,
  };
  return <svg {...props}>{paths[name]}</svg>;
};

// ============== Logo lockup ==============
const Logo = ({ scale = 1 }) => (
  <div style={{ display: "flex", alignItems: "center", gap: 14 * scale }}>
    <div style={{
      width: 64 * scale, height: 64 * scale, borderRadius: 16 * scale,
      background: "linear-gradient(135deg,#3b82f6 0%, #6366f1 50%, #8b5cf6 100%)",
      display: "grid", placeItems: "center", position: "relative",
      boxShadow: `0 ${10 * scale}px ${30 * scale}px rgba(99,102,241,0.45), inset 0 1px 0 rgba(255,255,255,0.25)`,
      flexShrink: 0,
    }}>
      <svg width={36 * scale} height={36 * scale} viewBox="0 0 36 36" fill="none">
        <path d="M5 11l5-5h16a3 3 0 0 1 3 3v18a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3z" fill="#fff" fillOpacity="0.95"/>
        <path d="M5 11l5-5h16a3 3 0 0 1 3 3v3H8a3 3 0 0 0-3 3z" fill="#fff" fillOpacity="0.7"/>
        <circle cx="24" cy="22" r="3" fill="#6366f1"/>
        <rect x="11" y="20" width="6" height="2" rx="1" fill="#6366f1" opacity="0.5"/>
        <rect x="11" y="24" width="4" height="2" rx="1" fill="#6366f1" opacity="0.3"/>
      </svg>
    </div>
    <div style={{ whiteSpace: "nowrap" }}>
      <div style={{ fontSize: 38 * scale, fontWeight: 800, letterSpacing: "-0.02em", lineHeight: 1, color: "#fff", whiteSpace: "nowrap" }}>
        <span>Digi</span><span style={{ background: "linear-gradient(135deg,#60a5fa,#a78bfa)", WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent", paddingRight: 2 * scale }}>Kash</span>
      </div>
      <div style={{ fontSize: 10 * scale, color: "rgba(255,255,255,0.55)", letterSpacing: "0.22em", marginTop: 6 * scale, fontWeight: 600, whiteSpace: "nowrap" }}>
        WALLET · PAYMENT · QR SYSTEM
      </div>
    </div>
  </div>
);

// ============== Realistic MacBook frame ==============
// Screen area aspect 1280:794 (matches DashboardScreen native dims).
const MacBookFrame = ({ width, children }) => {
  const bezel = Math.max(8, width * 0.018);
  // inner screen area
  const innerW = width - bezel * 2;
  const innerH = innerW * (DASH_H / DASH_W);
  const screenHeight = innerH + bezel * 2;
  const baseHeight = Math.max(12, width * 0.024);
  return (
    <div style={{ width, position: "relative" }}>
      {/* Screen */}
      <div style={{
        width: "100%", height: screenHeight,
        background: "linear-gradient(180deg,#0d0d10 0%, #1a1a1f 100%)",
        borderRadius: `${bezel * 1.6}px ${bezel * 1.6}px 4px 4px`,
        padding: bezel,
        position: "relative",
        boxShadow: "0 30px 80px rgba(0,0,0,0.45), inset 0 0 0 1px rgba(255,255,255,0.05)",
      }}>
        {/* Camera notch */}
        <div style={{ position: "absolute", top: bezel / 2.5, left: "50%", transform: "translateX(-50%)", width: Math.max(4, bezel*0.5), height: Math.max(4, bezel*0.5), borderRadius: "50%", background: "#222", border: "1px solid #333" }} />
        {/* Screen content */}
        <div style={{ width: innerW, height: innerH, borderRadius: bezel * 0.4, overflow: "hidden", background: "#fff", position: "relative" }}>
          <ScaledScreen width={innerW} height={innerH} nativeWidth={DASH_W} nativeHeight={DASH_H}>
            {children}
          </ScaledScreen>
          <div style={{ position: "absolute", inset: 0, background: "linear-gradient(180deg,rgba(255,255,255,0.07) 0%,transparent 25%,transparent 75%,rgba(0,0,0,0.05) 100%)", pointerEvents: "none" }} />
        </div>
      </div>
      {/* Hinge / chin */}
      <div style={{
        width: "104%", height: baseHeight, marginLeft: "-2%",
        background: "linear-gradient(180deg,#2a2a30 0%, #1a1a1f 30%, #d4d6dc 32%, #b8bbc4 70%, #8a8e98 100%)",
        borderRadius: `0 0 ${baseHeight * 0.4}px ${baseHeight * 0.4}px`,
        position: "relative",
      }}>
        <div style={{
          position: "absolute", top: "32%", left: "50%", transform: "translateX(-50%)",
          width: "14%", height: "30%", background: "linear-gradient(180deg,#1a1a1f,#2a2a30)",
          borderRadius: "0 0 8px 8px",
        }} />
      </div>
    </div>
  );
};

// ============== Realistic iPhone frame ==============
const PhoneFrame = ({ width, children }) => {
  const bezel = Math.max(6, width * 0.05);
  const innerW = width - bezel * 2;
  const innerH = innerW * (PHONE_H / PHONE_W);
  const height = innerH + bezel * 2;
  return (
    <div style={{
      width, height,
      background: "linear-gradient(135deg,#2a2a30 0%, #1a1a1f 50%, #0a0a0d 100%)",
      borderRadius: width * 0.16,
      padding: bezel,
      boxShadow: "0 30px 80px rgba(0,0,0,0.55), 0 0 0 1.5px rgba(80,80,90,0.6), inset 0 0 0 1px rgba(255,255,255,0.08)",
      position: "relative",
    }}>
      {/* Side buttons */}
      <div style={{ position: "absolute", left: -2, top: "16%", width: 3, height: "5%", background: "#2a2a30", borderRadius: "2px 0 0 2px" }} />
      <div style={{ position: "absolute", left: -2, top: "24%", width: 3, height: "8%", background: "#2a2a30", borderRadius: "2px 0 0 2px" }} />
      <div style={{ position: "absolute", left: -2, top: "34%", width: 3, height: "8%", background: "#2a2a30", borderRadius: "2px 0 0 2px" }} />
      <div style={{ position: "absolute", right: -2, top: "26%", width: 3, height: "12%", background: "#2a2a30", borderRadius: "0 2px 2px 0" }} />
      {/* Screen */}
      <div style={{ width: innerW, height: innerH, borderRadius: width * 0.12, overflow: "hidden", background: "#fff", position: "relative" }}>
        {/* Dynamic island */}
        <div style={{ position: "absolute", top: bezel * 0.6, left: "50%", transform: "translateX(-50%)", width: "32%", height: Math.max(14, bezel * 1.4), background: "#0a0a0d", borderRadius: 100, zIndex: 10 }} />
        <ScaledScreen width={innerW} height={innerH} nativeWidth={PHONE_W} nativeHeight={PHONE_H}>
          {children}
        </ScaledScreen>
      </div>
    </div>
  );
};

// ============== Hero Banner: 1540 × 780 ==============
const HeroBanner = () => {
  const features = [
    { label: "P2P", sub: "Module", icon: "p2p", color: "#3b82f6", tint: "#1e3a8a" },
    { label: "Mobile", sub: "Recharge", icon: "mobile", color: "#22d3ee", tint: "#155e75" },
    { label: "Wallet", sub: "Earn", icon: "wallet", color: "#10b981", tint: "#064e3b" },
    { label: "QR Code", sub: "System", icon: "qr", color: "#6366f1", tint: "#312e81" },
    { label: "Subscription", sub: "System", icon: "crown", color: "#f59e0b", tint: "#78350f" },
    { label: "Payment", sub: "Link", icon: "link", color: "#ec4899", tint: "#831843" },
  ];

  const bottomFeats = [
    { label: "Full QR System", icon: "qr" },
    { label: "Any User", icon: "users" },
    { label: "All Payment Methods", icon: "card" },
    { label: "PWA Ready", icon: "pwa" },
    { label: "Secure & Reliable", icon: "shield" },
  ];

  return (
    <div style={{
      width: 1540, height: 780, position: "relative", overflow: "hidden",
      fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#fff",
      background: "radial-gradient(ellipse 80% 60% at 20% 30%, #1e1b6e 0%, #0a0a2e 40%, #050516 100%)",
    }}>
      {/* Ambient color glows */}
      <div style={{ position: "absolute", top: -200, left: -100, width: 700, height: 700, background: "radial-gradient(circle, rgba(99,102,241,0.5) 0%, transparent 60%)", filter: "blur(20px)", pointerEvents: "none" }} />
      <div style={{ position: "absolute", top: 200, right: -200, width: 800, height: 800, background: "radial-gradient(circle, rgba(139,92,246,0.35) 0%, transparent 60%)", filter: "blur(30px)", pointerEvents: "none" }} />
      <div style={{ position: "absolute", bottom: -300, left: 400, width: 700, height: 700, background: "radial-gradient(circle, rgba(59,130,246,0.25) 0%, transparent 60%)", filter: "blur(40px)", pointerEvents: "none" }} />

      {/* Grid pattern */}
      <div style={{
        position: "absolute", inset: 0, opacity: 0.15,
        backgroundImage: "linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px)",
        backgroundSize: "60px 60px",
        maskImage: "radial-gradient(ellipse 60% 50% at 50% 50%, black 30%, transparent 80%)",
      }} />

      {/* Tiny stars */}
      <svg style={{ position: "absolute", inset: 0, opacity: 0.4 }} width="100%" height="100%">
        {Array.from({ length: 40 }).map((_, i) => {
          const x = (i * 137.5) % 1540;
          const y = (i * 91.3) % 780;
          const r = (i % 3) * 0.4 + 0.5;
          return <circle key={i} cx={x} cy={y} r={r} fill="#fff" opacity={(i % 5) * 0.15 + 0.2} />;
        })}
      </svg>

      {/* Content container */}
      <div style={{ position: "relative", zIndex: 2, padding: "44px 56px 0", height: "100%", display: "flex", flexDirection: "column" }}>
        {/* Top: logo + v2.0 */}
        <div style={{ display: "flex", alignItems: "center", gap: 28 }}>
          <Logo scale={1} />
          <div style={{
            padding: "8px 18px",
            background: "linear-gradient(135deg,#6366f1,#8b5cf6)",
            borderRadius: 12,
            fontSize: 22, fontWeight: 800, letterSpacing: "-0.01em",
            boxShadow: "0 8px 24px rgba(139,92,246,0.45), inset 0 1px 0 rgba(255,255,255,0.25)",
          }}>v2.0</div>
        </div>

        {/* Main row */}
        <div style={{ flex: 1, display: "grid", gridTemplateColumns: "1fr 1fr", gap: 40, marginTop: 32, position: "relative" }}>
          {/* Left content */}
          <div style={{ display: "flex", flexDirection: "column", gap: 22 }}>
            {/* BIG UPDATE badge */}
            <div style={{ display: "inline-flex", alignSelf: "flex-start", alignItems: "center", gap: 8, padding: "8px 20px 8px 16px", borderRadius: 100, background: "rgba(99,102,241,0.15)", border: "1px solid rgba(139,92,246,0.4)", backdropFilter: "blur(10px)", whiteSpace: "nowrap" }}>
              <BIcon name="star" color="#a78bfa" size={14} />
              <span style={{ fontSize: 13, fontWeight: 800, letterSpacing: "0.12em", color: "#c4b5fd", whiteSpace: "nowrap" }}>BIG UPDATE</span>
            </div>

            {/* Headline */}
            <h1 style={{ fontSize: 64, fontWeight: 800, lineHeight: 1.02, letterSpacing: "-0.035em", margin: 0 }}>
              <span style={{ color: "#fff" }}>All-in-One Payment</span><br/>
              <span style={{ background: "linear-gradient(135deg,#60a5fa 0%, #818cf8 60%, #a78bfa 100%)", WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent" }}>Gateway, Wallet &amp;</span><br/>
              <span style={{ background: "linear-gradient(135deg,#a78bfa 0%, #c084fc 50%, #e879f9 100%)", WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent" }}>QR System</span>
            </h1>

            <p style={{ fontSize: 17, color: "rgba(255,255,255,0.65)", margin: 0, lineHeight: 1.5, maxWidth: 480, fontWeight: 500 }}>
              Modern, Secure &amp; Reliable Digital Payment Solution<br/>for Every Business.
            </p>

            {/* Feature pills 3x2 */}
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 10, maxWidth: 580, marginTop: 6 }}>
              {features.map((f, i) => (
                <div key={i} style={{
                  display: "flex", alignItems: "center", gap: 10,
                  padding: "12px 14px", borderRadius: 12,
                  background: "rgba(255,255,255,0.04)",
                  border: "1px solid rgba(255,255,255,0.08)",
                  backdropFilter: "blur(10px)",
                }}>
                  <div style={{ width: 38, height: 38, borderRadius: 10, background: `linear-gradient(135deg, ${f.color}, ${f.color}dd)`, display: "grid", placeItems: "center", flexShrink: 0, boxShadow: `0 4px 14px ${f.color}55` }}>
                    <BIcon name={f.icon} color="#fff" size={18} />
                  </div>
                  <div style={{ lineHeight: 1.1 }}>
                    <div style={{ fontSize: 14, fontWeight: 700, color: "#fff" }}>{f.label}</div>
                    <div style={{ fontSize: 12, fontWeight: 600, color: "rgba(255,255,255,0.6)" }}>{f.sub}</div>
                  </div>
                </div>
              ))}
              {/* Role-Based Modules wide tile */}
              <div style={{
                gridColumn: "span 3",
                display: "flex", alignItems: "center", gap: 14,
                padding: "12px 16px", borderRadius: 12,
                background: "linear-gradient(90deg, rgba(59,130,246,0.2), rgba(139,92,246,0.15))",
                border: "1px solid rgba(99,102,241,0.4)",
                backdropFilter: "blur(10px)",
              }}>
                <div style={{ width: 40, height: 40, borderRadius: 10, background: "linear-gradient(135deg,#3b82f6,#6366f1)", display: "grid", placeItems: "center", boxShadow: "0 4px 14px rgba(59,130,246,0.45)" }}>
                  <BIcon name="role" color="#fff" size={20} />
                </div>
                <div style={{ flex: 1, fontSize: 16, fontWeight: 700 }}>Role-Based Modules</div>
                <div style={{ padding: "4px 11px", background: "linear-gradient(135deg,#22d3ee,#3b82f6)", borderRadius: 100, fontSize: 10, fontWeight: 800, letterSpacing: "0.08em", boxShadow: "0 4px 12px rgba(34,211,238,0.4)" }}>NEW</div>
              </div>
            </div>
          </div>

          {/* Right: devices */}
          <div style={{ position: "relative" }}>
            {/* Aura behind devices */}
            <div style={{ position: "absolute", top: 40, right: -50, width: 600, height: 500, background: "radial-gradient(circle, rgba(139,92,246,0.4) 0%, transparent 60%)", filter: "blur(40px)" }} />
            <div style={{ position: "absolute", top: 100, right: 200, width: 400, height: 400, background: "radial-gradient(circle, rgba(59,130,246,0.35) 0%, transparent 60%)", filter: "blur(30px)" }} />

            {/* MacBook */}
            <div style={{ position: "absolute", top: 10, left: -20, transform: "perspective(1500px) rotateY(-3deg)" }}>
              <MacBookFrame width={680}>
                <DashboardScreen />
              </MacBookFrame>
            </div>

            {/* Phone overlapping */}
            <div style={{ position: "absolute", top: 35, right: -30, transform: "perspective(1500px) rotateY(4deg)" }}>
              <PhoneFrame width={195}>
                <PhoneScreen />
              </PhoneFrame>
            </div>
          </div>
        </div>

        {/* Bottom strip */}
        <div style={{
          marginLeft: -56, marginRight: -56, marginTop: "auto",
          padding: "20px 56px",
          background: "linear-gradient(90deg, rgba(10,10,30,0.6), rgba(20,20,50,0.4))",
          borderTop: "1px solid rgba(255,255,255,0.08)",
          backdropFilter: "blur(10px)",
          display: "flex", alignItems: "center", justifyContent: "space-between", gap: 24,
        }}>
          {bottomFeats.map((f, i) => (
            <div key={i} style={{ display: "flex", alignItems: "center", gap: 10, flex: 1 }}>
              <div style={{ width: 36, height: 36, borderRadius: 10, background: "rgba(255,255,255,0.06)", border: "1px solid rgba(255,255,255,0.1)", display: "grid", placeItems: "center" }}>
                <BIcon name={f.icon} color="#fff" size={18} />
              </div>
              <span style={{ fontSize: 14, fontWeight: 600, color: "rgba(255,255,255,0.85)" }}>{f.label}</span>
            </div>
          ))}
          <button style={{
            display: "flex", alignItems: "center", gap: 10,
            padding: "14px 26px",
            background: "linear-gradient(135deg,#3b82f6,#8b5cf6)",
            border: "none", borderRadius: 100, color: "#fff",
            fontSize: 16, fontWeight: 700, cursor: "pointer",
            fontFamily: "inherit",
            boxShadow: "0 10px 30px rgba(99,102,241,0.5), inset 0 1px 0 rgba(255,255,255,0.25)",
          }}>
            Explore v2.0 <BIcon name="arrow" color="#fff" size={18} />
          </button>
        </div>
      </div>
    </div>
  );
};

// ============== 590 × 300 Inline Preview Banner ==============
const InlinePreview = () => {
  const features = [
    { label: "P2P Module", icon: "p2p", color: "#3b82f6" },
    { label: "Wallet Earn", icon: "wallet", color: "#10b981" },
    { label: "QR System", icon: "qr", color: "#8b5cf6" },
    { label: "Payment Link", icon: "link", color: "#ec4899" },
  ];

  return (
    <div style={{
      width: 590, height: 300, position: "relative", overflow: "hidden",
      fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#fff",
      background: "radial-gradient(ellipse 70% 60% at 25% 35%, #1e1b6e 0%, #0a0a2e 50%, #050516 100%)",
    }}>
      {/* Glows */}
      <div style={{ position: "absolute", top: -100, left: -50, width: 350, height: 350, background: "radial-gradient(circle, rgba(99,102,241,0.55) 0%, transparent 65%)", filter: "blur(15px)" }} />
      <div style={{ position: "absolute", top: 60, right: -100, width: 380, height: 380, background: "radial-gradient(circle, rgba(139,92,246,0.4) 0%, transparent 65%)", filter: "blur(20px)" }} />
      <div style={{ position: "absolute", bottom: -150, left: 200, width: 350, height: 350, background: "radial-gradient(circle, rgba(59,130,246,0.3) 0%, transparent 60%)", filter: "blur(25px)" }} />

      {/* Grid */}
      <div style={{
        position: "absolute", inset: 0, opacity: 0.18,
        backgroundImage: "linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)",
        backgroundSize: "28px 28px",
        maskImage: "radial-gradient(ellipse 70% 60% at 50% 50%, black 30%, transparent 90%)",
      }} />

      {/* Stars */}
      <svg style={{ position: "absolute", inset: 0, opacity: 0.5 }} width="100%" height="100%">
        {Array.from({ length: 18 }).map((_, i) => {
          const x = (i * 47.7) % 590;
          const y = (i * 31.3) % 300;
          const r = (i % 3) * 0.3 + 0.4;
          return <circle key={i} cx={x} cy={y} r={r} fill="#fff" opacity={(i % 5) * 0.15 + 0.25} />;
        })}
      </svg>

      <div style={{ position: "relative", zIndex: 2, padding: "14px 20px 10px", height: "100%", display: "flex", flexDirection: "column" }}>
        {/* Top: logo + v2 */}
        <div style={{ display: "flex", alignItems: "center", gap: 10, justifyContent: "space-between" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
            <div style={{
              width: 34, height: 34, borderRadius: 9,
              background: "linear-gradient(135deg,#3b82f6,#6366f1,#8b5cf6)",
              display: "grid", placeItems: "center",
              boxShadow: "0 4px 14px rgba(99,102,241,0.5), inset 0 1px 0 rgba(255,255,255,0.3)",
              flexShrink: 0,
            }}>
              <svg width="22" height="22" viewBox="0 0 36 36" fill="none">
                <path d="M5 11l5-5h16a3 3 0 0 1 3 3v18a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3z" fill="#fff" fillOpacity="0.95"/>
                <circle cx="24" cy="22" r="3" fill="#6366f1"/>
              </svg>
            </div>
            <div style={{ whiteSpace: "nowrap" }}>
              <div style={{ fontSize: 20, fontWeight: 800, lineHeight: 1, letterSpacing: "-0.02em", whiteSpace: "nowrap" }}>
                <span style={{ color: "#fff" }}>Digi</span><span style={{ background: "linear-gradient(135deg,#60a5fa,#a78bfa)", WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent", paddingRight: 2 }}>Kash</span>
              </div>
              <div style={{ fontSize: 7, color: "rgba(255,255,255,0.55)", letterSpacing: "0.18em", marginTop: 3, fontWeight: 700, whiteSpace: "nowrap" }}>WALLET · PAYMENT · QR</div>
            </div>
          </div>
          <div style={{ padding: "4px 9px", background: "linear-gradient(135deg,#6366f1,#8b5cf6)", borderRadius: 6, fontSize: 11, fontWeight: 800, letterSpacing: "-0.01em", boxShadow: "0 4px 12px rgba(139,92,246,0.45)" }}>v2.0</div>
        </div>

        {/* Main */}
        <div style={{ flex: 1, display: "grid", gridTemplateColumns: "1fr 240px", gap: 14, marginTop: 9 }}>
          {/* Left */}
          <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
            <div style={{ display: "inline-flex", alignSelf: "flex-start", alignItems: "center", gap: 5, padding: "3px 12px 3px 10px", borderRadius: 100, background: "rgba(99,102,241,0.18)", border: "1px solid rgba(139,92,246,0.4)", whiteSpace: "nowrap", width: "fit-content" }}>
              <BIcon name="star" color="#a78bfa" size={9} />
              <span style={{ fontSize: 9, fontWeight: 800, letterSpacing: "0.1em", color: "#c4b5fd", whiteSpace: "nowrap" }}>BIG UPDATE</span>
            </div>

            <h1 style={{ fontSize: 23, fontWeight: 800, lineHeight: 1.02, letterSpacing: "-0.035em", margin: 0 }}>
              <span style={{ color: "#fff" }}>All-in-One Payment</span><br/>
              <span style={{ background: "linear-gradient(135deg,#60a5fa,#818cf8,#a78bfa)", WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent" }}>Gateway, Wallet &amp;</span><br/>
              <span style={{ background: "linear-gradient(135deg,#a78bfa,#c084fc,#e879f9)", WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent" }}>QR System</span>
            </h1>

            <div style={{ fontSize: 9, color: "rgba(255,255,255,0.6)", lineHeight: 1.4, fontWeight: 500, marginTop: 1 }}>
              Modern, Secure &amp; Reliable Digital Payment Solution.
            </div>

            {/* feature chips */}
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 5, marginTop: 5 }}>
              {features.map((f, i) => (
                <div key={i} style={{ display: "flex", alignItems: "center", gap: 6, padding: "5px 8px", borderRadius: 7, background: "rgba(255,255,255,0.05)", border: "1px solid rgba(255,255,255,0.09)" }}>
                  <div style={{ width: 18, height: 18, borderRadius: 5, background: `linear-gradient(135deg, ${f.color}, ${f.color}cc)`, display: "grid", placeItems: "center", flexShrink: 0, boxShadow: `0 2px 8px ${f.color}55` }}>
                    <BIcon name={f.icon} color="#fff" size={10} />
                  </div>
                  <span style={{ fontSize: 9.5, fontWeight: 700, whiteSpace: "nowrap" }}>{f.label}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Right: devices compact */}
          <div style={{ position: "relative" }}>
            <div style={{ position: "absolute", top: -10, right: -30, width: 260, height: 240, background: "radial-gradient(circle, rgba(139,92,246,0.5) 0%, transparent 60%)", filter: "blur(20px)" }} />
            <div style={{ position: "absolute", top: -8, left: -20, transform: "perspective(800px) rotateY(-4deg)" }}>
              <MacBookFrame width={235}>
                <DashboardScreen />
              </MacBookFrame>
            </div>
            <div style={{ position: "absolute", top: -3, right: -16, transform: "perspective(800px) rotateY(5deg)" }}>
              <PhoneFrame width={72}>
                <PhoneScreen />
              </PhoneFrame>
            </div>
          </div>
        </div>

        {/* Bottom: chips + CTA */}
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: 8, marginTop: 6, paddingTop: 8, borderTop: "1px solid rgba(255,255,255,0.12)", background: "linear-gradient(90deg, rgba(10,10,30,0.4), rgba(20,20,50,0.2))", marginLeft: -20, marginRight: -20, paddingLeft: 20, paddingRight: 20 }}>
          <div style={{ display: "flex", gap: 12, alignItems: "center" }}>
            {[
              { icon: "qr", label: "QR" },
              { icon: "card", label: "Pay" },
              { icon: "pwa", label: "PWA" },
              { icon: "shield", label: "Secure" },
            ].map((f, i) => (
              <div key={i} style={{ display: "flex", alignItems: "center", gap: 4 }}>
                <div style={{ width: 18, height: 18, borderRadius: 5, background: "rgba(255,255,255,0.07)", border: "1px solid rgba(255,255,255,0.12)", display: "grid", placeItems: "center" }}>
                  <BIcon name={f.icon} color="#fff" size={10} />
                </div>
                <span style={{ fontSize: 9, fontWeight: 600, color: "rgba(255,255,255,0.8)" }}>{f.label}</span>
              </div>
            ))}
          </div>
          <button style={{
            display: "flex", alignItems: "center", gap: 5,
            padding: "6px 13px",
            background: "linear-gradient(135deg,#3b82f6,#8b5cf6)",
            border: "none", borderRadius: 100, color: "#fff",
            fontSize: 10, fontWeight: 700, cursor: "pointer",
            fontFamily: "inherit",
            boxShadow: "0 6px 18px rgba(99,102,241,0.55), inset 0 1px 0 rgba(255,255,255,0.25)",
          }}>
            Explore v2.0 <BIcon name="arrow" color="#fff" size={11} />
          </button>
        </div>
      </div>
    </div>
  );
};

window.HeroBanner = HeroBanner;
window.InlinePreview = InlinePreview;
