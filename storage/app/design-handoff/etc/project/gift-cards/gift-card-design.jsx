/* global React */
const { useState, useEffect, useMemo } = React;

/* ===========================================================
   <GiftCardDesign> — the premium visual card.
   Used in: index list, wizard preview, recipient view, admin.
   Props:
     template: birthday | holiday | thankyou | anniversary | congrats | premium
     amount, currency, recipient, sender, message, code
     size: 'sm' | 'md' | 'lg'  (governs absolute scale)
     showCode: bool  (recipient view)
   The component is built around a fixed *aspect* (1.586:1, like a credit
   card) and scales via `--gc-w`. Don't pass width via style on consumers.
   =========================================================== */

const TEMPLATES = {
  birthday: {
    label: 'Birthday',
    category: 'Birthday',
    bg: 'linear-gradient(135deg, #FB7185 0%, #F472B6 45%, #C026D3 100%)',
    ink: '#FFFFFF',
    chip: 'rgba(255,255,255,.22)',
    motif: 'confetti',
    accent: '#FDE68A',
    ribbon: 'Happy Birthday',
  },
  holiday: {
    label: 'Holiday',
    category: 'Holiday',
    bg: 'linear-gradient(135deg, #064E3B 0%, #065F46 40%, #0F766E 100%)',
    ink: '#FFFFFF',
    chip: 'rgba(255,255,255,.18)',
    motif: 'snow',
    accent: '#FCA5A5',
    ribbon: 'Season’s Greetings',
  },
  thankyou: {
    label: 'Thank You',
    category: 'Thank You',
    bg: 'linear-gradient(135deg, #FDE68A 0%, #FBBF24 55%, #D97706 100%)',
    ink: '#3F2A05',
    chip: 'rgba(63,42,5,.10)',
    motif: 'rays',
    accent: '#7C2D12',
    ribbon: 'With Gratitude',
  },
  anniversary: {
    label: 'Anniversary',
    category: 'Anniversary',
    bg: 'linear-gradient(135deg, #4C1D95 0%, #7E22CE 45%, #BE185D 100%)',
    ink: '#FFFFFF',
    chip: 'rgba(255,255,255,.20)',
    motif: 'hearts',
    accent: '#FBBF24',
    ribbon: 'Happy Anniversary',
  },
  congrats: {
    label: 'Congratulations',
    category: 'Congratulations',
    bg: 'linear-gradient(135deg, #1E3A8A 0%, #3B82F6 45%, #06B6D4 100%)',
    ink: '#FFFFFF',
    chip: 'rgba(255,255,255,.20)',
    motif: 'sparkles',
    accent: '#FBBF24',
    ribbon: 'Congratulations',
  },
  premium: {
    label: 'Premium',
    category: 'General',
    bg: 'linear-gradient(135deg, #0B1330 0%, #14245F 45%, #1E3A8A 100%)',
    ink: '#FFFFFF',
    chip: 'rgba(255,255,255,.14)',
    motif: 'mesh',
    accent: '#FBBF24',
    ribbon: 'A Gift For You',
  },
};

window.TEMPLATES = TEMPLATES;

/* ----- motifs (inline SVG overlays) ---------------------- */
const Motif = ({ kind, color = '#fff' }) => {
  const o = { position: 'absolute', inset: 0, width: '100%', height: '100%', pointerEvents: 'none' };
  if (kind === 'confetti') return (
    <svg style={o} viewBox="0 0 400 252" preserveAspectRatio="none">
      {[...Array(28)].map((_, i) => {
        const x = (i * 47) % 400, y = (i * 31) % 252;
        const r = (i % 3) * 4 + 6;
        const rot = (i * 23) % 360;
        const c = ['#FDE68A','#FCA5A5','#A7F3D0','#BFDBFE','#FBCFE8'][i % 5];
        return <rect key={i} x={x} y={y} width={r} height={r*0.5} fill={c} opacity={.55} transform={`rotate(${rot} ${x} ${y})`} rx="1" />;
      })}
    </svg>
  );
  if (kind === 'snow') return (
    <svg style={o} viewBox="0 0 400 252" preserveAspectRatio="none">
      {[...Array(40)].map((_, i) => {
        const x = (i * 41) % 400, y = (i * 19) % 252;
        return <circle key={i} cx={x} cy={y} r={(i%3)+1.2} fill="#fff" opacity={0.25 + (i%3)*0.15} />;
      })}
    </svg>
  );
  if (kind === 'rays') return (
    <svg style={o} viewBox="0 0 400 252" preserveAspectRatio="none">
      <defs>
        <radialGradient id="ray" cx="20%" cy="0%" r="80%">
          <stop offset="0%" stopColor="#fff" stopOpacity=".55"/>
          <stop offset="100%" stopColor="#fff" stopOpacity="0"/>
        </radialGradient>
      </defs>
      <rect width="400" height="252" fill="url(#ray)"/>
      {[...Array(8)].map((_, i) => (
        <line key={i} x1="80" y1="0" x2={i*60} y2="252" stroke="#fff" strokeOpacity={0.06} strokeWidth="1"/>
      ))}
    </svg>
  );
  if (kind === 'hearts') return (
    <svg style={o} viewBox="0 0 400 252" preserveAspectRatio="none">
      {[...Array(14)].map((_, i) => {
        const x = (i * 53) % 400, y = (i * 37) % 252;
        const s = 8 + (i % 3) * 4;
        return <path key={i} d={`M${x} ${y+s*.3} c0,-${s*.5} ${s*.8},-${s*.5} ${s*.8},0 c0,${s*.5} -${s*.8},${s*.8} -${s*.8},${s*.8} c0,0 -${s*.8},-${s*.3} -${s*.8},-${s*.8} c0,-${s*.5} ${s*.8},-${s*.5} ${s*.8},0 z`} fill="#fff" opacity=".10"/>;
      })}
    </svg>
  );
  if (kind === 'sparkles') return (
    <svg style={o} viewBox="0 0 400 252" preserveAspectRatio="none">
      {[...Array(18)].map((_, i) => {
        const x = (i * 41) % 400, y = (i * 23) % 252;
        const s = 4 + (i % 3) * 3;
        return (
          <g key={i} opacity={0.4} transform={`translate(${x} ${y})`}>
            <path d={`M0 -${s} L${s*.3} -${s*.3} L${s} 0 L${s*.3} ${s*.3} L0 ${s} L-${s*.3} ${s*.3} L-${s} 0 L-${s*.3} -${s*.3} Z`} fill="#FDE68A"/>
          </g>
        );
      })}
    </svg>
  );
  if (kind === 'mesh') return (
    <svg style={o} viewBox="0 0 400 252" preserveAspectRatio="none">
      <defs>
        <radialGradient id="m1" cx="85%" cy="20%" r="50%">
          <stop offset="0%" stopColor="#60A5FA" stopOpacity=".55"/>
          <stop offset="100%" stopColor="#60A5FA" stopOpacity="0"/>
        </radialGradient>
        <radialGradient id="m2" cx="10%" cy="90%" r="55%">
          <stop offset="0%" stopColor="#FBBF24" stopOpacity=".30"/>
          <stop offset="100%" stopColor="#FBBF24" stopOpacity="0"/>
        </radialGradient>
        <pattern id="dots" width="14" height="14" patternUnits="userSpaceOnUse">
          <circle cx="1" cy="1" r="1" fill="#fff" opacity="0.08"/>
        </pattern>
      </defs>
      <rect width="400" height="252" fill="url(#m1)"/>
      <rect width="400" height="252" fill="url(#m2)"/>
      <rect width="400" height="252" fill="url(#dots)"/>
    </svg>
  );
  return null;
};

const formatMoney = (n, cur = 'USD') => {
  const sym = { USD: '$', EUR: '€', GBP: '£', BDT: '৳', INR: '₹' }[cur] || '$';
  const v = Number(n || 0).toFixed(2);
  return `${sym}${v.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
};
window.formatMoney = formatMoney;

const GiftCardDesign = ({
  template = 'premium',
  amount = 100,
  currency = 'USD',
  recipient = 'Recipient',
  sender = 'You',
  message = '',
  code = 'DKGC-XXXX-XXXX',
  width = 360,
  showCode = false,
  flatTop = false,
}) => {
  const t = TEMPLATES[template] || TEMPLATES.premium;
  const height = Math.round(width / 1.586);

  return (
    <div
      className="gift-card-design"
      style={{
        width, height,
        position: 'relative',
        background: t.bg,
        color: t.ink,
        borderRadius: width >= 320 ? 18 : 12,
        overflow: 'hidden',
        boxShadow: width >= 320
          ? '0 26px 50px -12px rgba(15,23,42,.45), 0 12px 24px -8px rgba(15,23,42,.25), inset 0 0 0 1px rgba(255,255,255,.10)'
          : '0 6px 14px rgba(15,23,42,.20), inset 0 0 0 1px rgba(255,255,255,.10)',
        fontFamily: "'Plus Jakarta Sans', sans-serif",
        flex: 'none',
      }}
    >
      <Motif kind={t.motif} />

      {/* DigiKash mark */}
      <div style={{
        position: 'absolute', top: width*0.045, left: width*0.045,
        display: 'flex', alignItems: 'center', gap: width*0.018,
        fontWeight: 800, letterSpacing: '-.02em',
        fontSize: width*0.044, lineHeight: 1,
        color: t.ink,
      }}>
        <div style={{
          width: width*0.062, height: width*0.062, borderRadius: width*0.014,
          background: 'rgba(255,255,255,.22)',
          border: '1px solid rgba(255,255,255,.35)',
          display: 'grid', placeItems: 'center',
        }}>
          <svg viewBox="0 0 24 24" width={width*0.04} height={width*0.04} fill="none" stroke={t.ink} strokeWidth="2.4">
            <path d="M4 7h12a4 4 0 0 1 0 8H4V7z" fill={t.ink} stroke="none"/>
          </svg>
        </div>
        DigiKash
      </div>

      {/* Ribbon */}
      <div style={{
        position: 'absolute', top: width*0.05, right: width*0.05,
        background: t.chip,
        border: `1px solid ${t.ink === '#FFFFFF' ? 'rgba(255,255,255,.30)' : 'rgba(63,42,5,.18)'}`,
        padding: `${width*0.014}px ${width*0.028}px`,
        borderRadius: 999,
        fontSize: width*0.028,
        fontWeight: 700,
        letterSpacing: '.08em',
        textTransform: 'uppercase',
      }}>
        {t.ribbon}
      </div>

      {/* Amount */}
      <div style={{
        position: 'absolute',
        left: width*0.05, right: width*0.05,
        top: '38%',
        textAlign: 'left',
      }}>
        <div style={{ fontSize: width*0.028, letterSpacing: '.16em', textTransform: 'uppercase', fontWeight: 700, opacity: .78, marginBottom: width*0.01 }}>
          Gift Card Value
        </div>
        <div className="money" style={{
          fontSize: width*0.16,
          fontWeight: 800,
          letterSpacing: '-.03em',
          lineHeight: .95,
          textShadow: t.ink === '#FFFFFF' ? '0 2px 12px rgba(0,0,0,.18)' : 'none',
        }}>
          {formatMoney(amount, currency)}
        </div>
      </div>

      {/* Footer: To / From */}
      <div style={{
        position: 'absolute',
        left: width*0.05, right: width*0.05, bottom: width*0.05,
        display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end',
        gap: width*0.04,
      }}>
        <div style={{ minWidth: 0, flex: 1 }}>
          <div style={{ fontSize: width*0.028, letterSpacing: '.12em', textTransform: 'uppercase', fontWeight: 700, opacity: .72 }}>To</div>
          <div style={{ fontSize: width*0.045, fontWeight: 700, marginTop: width*0.005, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
            {recipient || '—'}
          </div>
        </div>
        <div style={{ minWidth: 0, flex: 1, textAlign: 'right' }}>
          <div style={{ fontSize: width*0.028, letterSpacing: '.12em', textTransform: 'uppercase', fontWeight: 700, opacity: .72 }}>From</div>
          <div style={{ fontSize: width*0.045, fontWeight: 700, marginTop: width*0.005, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
            {sender || '—'}
          </div>
        </div>
      </div>

      {/* Code stripe (recipient view only) */}
      {showCode && (
        <div style={{
          position: 'absolute',
          left: width*0.05, right: width*0.05,
          bottom: width*0.20,
          background: t.ink === '#FFFFFF' ? 'rgba(0,0,0,.22)' : 'rgba(255,255,255,.55)',
          backdropFilter: 'blur(8px)',
          border: `1px dashed ${t.ink === '#FFFFFF' ? 'rgba(255,255,255,.35)' : 'rgba(63,42,5,.25)'}`,
          padding: `${width*0.018}px ${width*0.032}px`,
          borderRadius: 8,
          fontFamily: 'ui-monospace, "SF Mono", Menlo, monospace',
          fontSize: width*0.036,
          fontWeight: 700,
          letterSpacing: '.18em',
          textAlign: 'center',
        }}>{code}</div>
      )}
    </div>
  );
};

window.GiftCardDesign = GiftCardDesign;
