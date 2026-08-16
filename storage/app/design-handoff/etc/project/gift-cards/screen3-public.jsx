/* global React, Icon, GiftCardDesign, formatMoney */
const { useState: useState3, useEffect: useEffect3 } = React;

/* ===========================================================
   SCREEN 3 — Public recipient preview ("You got a gift!")
   Animated reveal: envelope tilts open, confetti drifts up,
   gift card slides up out of envelope.
   =========================================================== */

const Confetti = ({ run = true }) => {
  const pieces = React.useMemo(() => Array.from({ length: 60 }, (_, i) => ({
    left: Math.random() * 100,
    delay: Math.random() * 2.5,
    duration: 3 + Math.random() * 3,
    rotate: Math.random() * 360,
    size: 6 + Math.random() * 8,
    color: ['#FDE68A','#FCA5A5','#A7F3D0','#BFDBFE','#FBCFE8','#FDBA74','#A5B4FC'][i % 7],
    shape: i % 3,
  })), []);
  return (
    <div aria-hidden="true" style={{ position: 'absolute', inset: 0, pointerEvents: 'none', overflow: 'hidden' }}>
      {pieces.map((p, i) => (
        <span key={i} style={{
          position: 'absolute',
          left: `${p.left}%`, top: '-30px',
          width: p.size, height: p.shape === 0 ? p.size * 0.5 : p.size,
          background: p.color,
          borderRadius: p.shape === 2 ? '50%' : 2,
          transform: `rotate(${p.rotate}deg)`,
          animation: run ? `confetti-fall ${p.duration}s ${p.delay}s linear infinite` : 'none',
          opacity: 0.9,
        }}/>
      ))}
    </div>
  );
};

const Screen3Public = () => {
  const [opened, setOpened] = useState3(true);
  const [copied, setCopied] = useState3(false);

  const recipient = 'Tahsin Ahmed';
  const sender = 'Ayesha Rahman';
  const amount = 100;
  const message = "Happy birthday Tahsin! Treat yourself to something nice — coffee, books, or the keyboard you’ve been eyeing. Have an incredible year ahead. — Ayesha";
  const code = 'DKGC-4F2A-9X7K';

  return (
    <div
      data-screen-label="03 Gift Card — Recipient Preview"
      style={{
        width: '100%', height: '100%',
        background: 'radial-gradient(120% 80% at 20% 0%, #3B82F6 0%, transparent 55%), radial-gradient(80% 80% at 100% 100%, #8B5CF6 0%, transparent 55%), linear-gradient(135deg, #0F172A 0%, #1E3A8A 60%, #4C1D95 100%)',
        fontFamily: "'Plus Jakarta Sans', sans-serif",
        color: '#fff',
        overflow: 'auto',
        position: 'relative',
        padding: '40px 24px 60px',
      }}
    >
      <style>{`
        @keyframes confetti-fall {
          0% { transform: translateY(-30px) rotate(0deg); opacity: 0; }
          10% { opacity: 1; }
          100% { transform: translateY(120vh) rotate(720deg); opacity: 0.7; }
        }
        @keyframes card-rise {
          0% { transform: translateY(60px) scale(0.92); opacity: 0; }
          100% { transform: translateY(0) scale(1); opacity: 1; }
        }
        @keyframes flap-open {
          0% { transform: rotateX(0deg); }
          100% { transform: rotateX(-180deg); }
        }
        @keyframes float-soft {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-6px); }
        }
      `}</style>

      <Confetti run={opened}/>

      {/* Header */}
      <header style={{
        position: 'relative', zIndex: 2,
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        maxWidth: 1080, margin: '0 auto 32px',
      }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          <div style={{
            width: 36, height: 36, borderRadius: 10,
            background: 'linear-gradient(135deg, #3B82F6, #1D4ED8)',
            display: 'grid', placeItems: 'center',
            boxShadow: '0 6px 14px rgba(59,130,246,.45), inset 0 0 0 1px rgba(255,255,255,.18)',
          }}>
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none">
              <path d="M4 7h12a4 4 0 0 1 0 8H4V7z" fill="#fff"/>
              <circle cx="15" cy="11" r="1.6" fill="#1d4ed8"/>
            </svg>
          </div>
          <div style={{ fontWeight: 800, fontSize: 18, letterSpacing: '-.02em' }}>Digi<span style={{ color: '#60A5FA' }}>Kash</span></div>
        </div>
        <div style={{ fontSize: 12.5, color: '#B8C4E8' }}>
          Trouble viewing? <a href="#" style={{ color: '#FBBF24', fontWeight: 600 }}>Open in browser</a>
        </div>
      </header>

      {/* Centered content */}
      <div style={{
        position: 'relative', zIndex: 2,
        maxWidth: 560, margin: '0 auto',
        textAlign: 'center',
      }}>
        {/* Pill */}
        <div style={{
          display: 'inline-flex', alignItems: 'center', gap: 6,
          padding: '5px 12px',
          background: 'rgba(251,191,36,.16)', border: '1px solid rgba(251,191,36,.40)',
          color: '#FCD34D', borderRadius: 999,
          fontSize: 11, fontWeight: 800, letterSpacing: '.10em', textTransform: 'uppercase',
        }}>
          <Icon name="sparkles" size={12}/> Just for you
        </div>

        <h1 style={{
          fontSize: 38, fontWeight: 800, letterSpacing: '-.025em',
          margin: '14px 0 8px', lineHeight: 1.05, textWrap: 'balance',
        }}>
          {sender.split(' ')[0]} sent you a gift
        </h1>
        <p style={{ fontSize: 15, color: '#B8C4E8', margin: '0 0 32px', lineHeight: 1.55 }}>
          Open your gift card and add it to your DigiKash wallet in seconds.
        </p>

        {/* Envelope + card */}
        <div style={{
          position: 'relative',
          padding: '40px 20px 60px',
          animation: opened ? 'none' : 'float-soft 3s ease-in-out infinite',
        }}>
          {/* Sender chip */}
          <div style={{
            display: 'inline-flex', alignItems: 'center', gap: 10,
            padding: '8px 14px 8px 8px',
            background: 'rgba(255,255,255,.08)', border: '1px solid rgba(255,255,255,.16)',
            borderRadius: 999, marginBottom: 22, backdropFilter: 'blur(8px)',
          }}>
            <div style={{ width: 30, height: 30, borderRadius: '50%', background: 'linear-gradient(135deg,#fbbf24,#f97316)', color: '#fff', fontWeight: 800, fontSize: 12, display: 'grid', placeItems: 'center' }}>AR</div>
            <div style={{ textAlign: 'left' }}>
              <div style={{ fontSize: 11, color: '#B8C4E8' }}>From</div>
              <div style={{ fontSize: 13, fontWeight: 700 }}>{sender}</div>
            </div>
          </div>

          {opened ? (
            <div style={{ animation: 'card-rise .9s cubic-bezier(.2,.8,.2,1) both' }}>
              <GiftCardDesign template="birthday" width={460} amount={amount} recipient={recipient} sender={sender} code={code} showCode/>
            </div>
          ) : (
            <button onClick={() => setOpened(true)} aria-label="Open the gift" style={{
              position: 'relative',
              width: 320, height: 220,
              border: 0, padding: 0, cursor: 'pointer',
              background: 'linear-gradient(180deg, #FB7185, #BE123C)',
              borderRadius: 12,
              boxShadow: '0 30px 60px rgba(0,0,0,.40)',
              margin: '0 auto', display: 'block',
              overflow: 'visible',
            }}>
              {/* envelope body */}
              <div style={{
                position: 'absolute', inset: 0,
                background: 'linear-gradient(180deg, #DC2626 0%, #991B1B 100%)',
                borderRadius: 12,
                boxShadow: 'inset 0 0 0 3px rgba(0,0,0,.18)',
              }}/>
              {/* flap */}
              <div style={{
                position: 'absolute', top: 0, left: 0, right: 0, height: 130,
                background: 'linear-gradient(180deg, #FB7185 0%, #DC2626 100%)',
                clipPath: 'polygon(0 0, 100% 0, 50% 100%)',
                transformOrigin: 'top center',
                transition: 'transform 1s cubic-bezier(.4,1.4,.5,1)',
              }}/>
              {/* wax seal */}
              <div style={{
                position: 'absolute', top: 75, left: '50%', transform: 'translateX(-50%)',
                width: 60, height: 60, borderRadius: '50%',
                background: 'radial-gradient(circle at 35% 30%, #FCD34D, #B45309 70%, #78350F 100%)',
                display: 'grid', placeItems: 'center',
                boxShadow: '0 6px 14px rgba(0,0,0,.40), inset 0 0 0 2px rgba(255,255,255,.18)',
                color: '#7C2D12', fontWeight: 800, fontSize: 22,
                fontFamily: 'serif',
              }}>DK</div>
              <div style={{ position: 'absolute', bottom: 16, left: 0, right: 0, color: '#fff', fontWeight: 800, fontSize: 14, letterSpacing: '.10em', textTransform: 'uppercase' }}>
                Tap to open
              </div>
            </button>
          )}
        </div>

        {/* Message */}
        <div style={{
          background: 'rgba(255,255,255,.06)', border: '1px solid rgba(255,255,255,.12)',
          borderRadius: 18, padding: '20px 22px', textAlign: 'left',
          backdropFilter: 'blur(10px)', marginTop: -10, marginBottom: 18,
        }}>
          <div style={{ fontSize: 11, fontWeight: 700, color: '#FCD34D', letterSpacing: '.10em', textTransform: 'uppercase', marginBottom: 8 }}>
            ✦ A message for you
          </div>
          <p style={{ fontSize: 15.5, lineHeight: 1.6, color: '#fff', margin: 0, fontStyle: 'italic', textWrap: 'pretty' }}>
            “{message}”
          </p>
        </div>

        {/* Code box */}
        <div style={{
          background: 'rgba(255,255,255,.06)', border: '1px solid rgba(255,255,255,.12)',
          borderRadius: 14, padding: 14, marginBottom: 18,
          display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 10,
        }}>
          <div style={{ textAlign: 'left', minWidth: 0 }}>
            <div style={{ fontSize: 11, color: '#B8C4E8', textTransform: 'uppercase', letterSpacing: '.08em', fontWeight: 700 }}>Gift code</div>
            <div style={{ fontFamily: 'ui-monospace, "SF Mono", Menlo, monospace', fontSize: 18, fontWeight: 800, letterSpacing: '.16em', marginTop: 3 }}>{code}</div>
          </div>
          <button onClick={() => { setCopied(true); setTimeout(() => setCopied(false), 1500); }} className="btn" style={{ background: 'rgba(255,255,255,.10)', color: '#fff', border: '1px solid rgba(255,255,255,.20)' }}>
            <Icon name={copied ? 'check' : 'copy'} size={14}/> {copied ? 'Copied' : 'Copy'}
          </button>
        </div>

        {/* CTAs */}
        <button className="btn btn-lg" style={{
          width: '100%',
          background: 'linear-gradient(135deg, #FBBF24, #F59E0B)',
          color: '#1A1206',
          fontWeight: 800,
          fontSize: 15,
          boxShadow: '0 14px 30px rgba(251,191,36,.35), inset 0 1px 0 rgba(255,255,255,.4)',
          border: 0,
          justifyContent: 'center',
        }}>
          <Icon name="wallet" size={17}/> Redeem {formatMoney(amount)} to my wallet
        </button>
        <button className="btn btn-light" style={{ width: '100%', justifyContent: 'center', marginTop: 10, background: 'rgba(255,255,255,.06)', border: '1px solid rgba(255,255,255,.16)', color: '#fff', boxShadow: 'none' }}>
          New to DigiKash? <span style={{ color: '#FCD34D', fontWeight: 700, marginLeft: 4 }}>Sign up to redeem →</span>
        </button>

        {/* Footer */}
        <div style={{ marginTop: 28, fontSize: 11.5, color: '#7A87B8', display: 'flex', justifyContent: 'center', gap: 14, flexWrap: 'wrap' }}>
          <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5 }}><Icon name="clock" size={12}/> Expires May 18, 2027</span>
          <span>·</span>
          <a href="#" style={{ color: '#B8C4E8' }}>Terms</a>
          <span>·</span>
          <a href="#" style={{ color: '#B8C4E8' }}>Report this gift</a>
          <span>·</span>
          <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5 }}><Icon name="shield" size={12}/> Secured by DigiKash escrow</span>
        </div>
      </div>
    </div>
  );
};
window.Screen3Public = Screen3Public;
