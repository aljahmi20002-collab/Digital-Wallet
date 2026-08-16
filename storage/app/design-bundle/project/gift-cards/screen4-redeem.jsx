/* global React, Sidebar, Topbar, Breadcrumb, Icon, GiftCardDesign, formatMoney */
const { useState: useState4, useRef: useRef4 } = React;

/* ===========================================================
   SCREEN 4 — Redeem a gift card (in-app, logged in)
   States: input | verified | success
   =========================================================== */

const formatCode = (raw) => {
  const s = raw.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 12);
  return s.replace(/(.{4})/g, '$1-').replace(/-$/, '');
};

const REDEEM_WALLETS = [
  { id: 'usd', name: 'USD Primary',   balance: 14350.00, currency: 'USD', icon: 'wallet' },
  { id: 'eur', name: 'EUR Wallet',    balance:  4820.50, currency: 'EUR', icon: 'wallet' },
  { id: 'gbp', name: 'GBP Wallet',    balance:  1240.00, currency: 'GBP', icon: 'wallet' },
];

const Screen4Redeem = () => {
  const [stage, setStage] = useState4('verified');   // 'input' | 'verified' | 'success'
  const [code, setCode] = useState4('DKGC-4F2A-9X7K');
  const [wallet, setWallet] = useState4(REDEEM_WALLETS[0]);

  const reset = () => { setStage('input'); setCode(''); };

  return (
    <div className="dk-app" data-screen-label="04 Gift Cards — Redeem">
        <div className="dk-content" style={{ background: 'linear-gradient(180deg, #F4F6FB 0%, #EEF2FF 50%, #F4F6FB 100%)' }}>
          {/* state-demo toggler (helper) */}
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 18 }}>
            <Breadcrumb items={['Dashboard', 'Gift Cards', 'Redeem']}/>
            <div style={{ display: 'inline-flex', padding: 3, background: '#fff', border: '1px solid var(--dk-card-line)', borderRadius: 8, fontSize: 11 }}>
              {['input','verified','success'].map(s => (
                <button key={s} onClick={() => setStage(s)} style={{
                  padding: '5px 10px', border: 0, borderRadius: 6, cursor: 'pointer',
                  background: stage === s ? 'var(--dk-primary-50)' : 'transparent',
                  color: stage === s ? 'var(--dk-primary-600)' : 'var(--dk-mute)',
                  fontWeight: 700, fontSize: 11, textTransform: 'capitalize',
                }}>{s} state</button>
              ))}
            </div>
          </div>

          <div style={{ maxWidth: 560, margin: '20px auto 0', textAlign: 'center' }}>
            {/* Icon mark */}
            <div style={{
              width: 64, height: 64, borderRadius: 18,
              background: 'linear-gradient(135deg, #3B82F6, #1D4ED8)',
              display: 'grid', placeItems: 'center', margin: '0 auto 16px',
              boxShadow: '0 12px 28px rgba(59,130,246,.32), inset 0 0 0 1px rgba(255,255,255,.20)',
              color: '#fff',
            }}>
              {stage === 'success' ? <Icon name="check" size={32} stroke={3}/> : <Icon name="gift" size={28}/>}
            </div>

            {stage === 'input' && (
              <>
                <h1 className="page-title" style={{ fontSize: 26 }}>Redeem a gift card</h1>
                <p className="page-sub">Paste your 12-character gift code and we’ll add the value to your wallet.</p>

                <div className="single-form-card" style={{ padding: 28, marginTop: 24, textAlign: 'left' }}>
                  <label className="form-label" style={{ textAlign: 'left' }}>Gift code</label>
                  <div style={{ position: 'relative' }}>
                    <input
                      className="form-control money"
                      autoFocus
                      value={code}
                      onChange={e => setCode(formatCode(e.target.value))}
                      placeholder="XXXX-XXXX-XXXX"
                      style={{
                        fontFamily: 'ui-monospace, "SF Mono", Menlo, monospace',
                        fontSize: 22, fontWeight: 800,
                        letterSpacing: '.16em',
                        textAlign: 'center',
                        padding: '18px 14px',
                        textTransform: 'uppercase',
                      }}
                    />
                  </div>
                  <div className="form-hint" style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                    <Icon name="shield" size={11}/> Your code is verified securely. Never share it with anyone you don’t trust.
                  </div>

                  <button
                    className="btn btn-base btn-lg"
                    style={{ width: '100%', justifyContent: 'center', marginTop: 18 }}
                    onClick={() => setStage('verified')}
                  >
                    <Icon name="check" size={16}/> Verify Code
                  </button>
                </div>

                <div style={{ marginTop: 18, fontSize: 12.5, color: 'var(--dk-mute)' }}>
                  Don’t have a code? <a href="#" style={{ color: 'var(--dk-primary-600)', fontWeight: 600 }}>Ask someone to send you a gift card</a>
                </div>
              </>
            )}

            {stage === 'verified' && (
              <>
                <h1 className="page-title" style={{ fontSize: 26 }}>Your gift card is valid</h1>
                <p className="page-sub">Choose where to deposit the value. The card will be marked as redeemed.</p>

                {/* Card preview */}
                <div style={{ display: 'grid', placeItems: 'center', margin: '24px auto 0' }}>
                  <GiftCardDesign template="birthday" width={360} amount={100} recipient="You" sender="Ayesha R."/>
                </div>

                <div className="single-form-card" style={{ padding: 22, marginTop: 22, textAlign: 'left' }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 }}>
                    <div>
                      <div style={{ fontSize: 11, color: 'var(--dk-mute)', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '.08em' }}>Code</div>
                      <code style={{
                        fontFamily: 'ui-monospace, "SF Mono", Menlo, monospace',
                        fontSize: 14, fontWeight: 700, letterSpacing: '.12em',
                        color: 'var(--dk-ink)',
                      }}>{code}</code>
                    </div>
                    <span className="badge badge-success"><span className="dot"/> Valid · Expires May 2027</span>
                  </div>

                  <label className="form-label">Deposit into wallet</label>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {REDEEM_WALLETS.map(w => {
                      const sel = wallet.id === w.id;
                      return (
                        <button key={w.id} onClick={() => setWallet(w)} style={{
                          display: 'flex', alignItems: 'center', gap: 12,
                          padding: '12px 14px',
                          background: sel ? 'var(--dk-primary-50)' : '#fff',
                          border: `1.5px solid ${sel ? 'var(--dk-accent)' : 'var(--dk-card-line)'}`,
                          borderRadius: 12, textAlign: 'left', cursor: 'pointer',
                        }}>
                          <div style={{
                            width: 36, height: 36, borderRadius: 10,
                            background: sel ? '#fff' : '#F1F5F9',
                            color: 'var(--dk-primary-600)',
                            display: 'grid', placeItems: 'center',
                          }}>
                            <Icon name="wallet" size={17}/>
                          </div>
                          <div style={{ flex: 1, minWidth: 0 }}>
                            <div style={{ fontSize: 13.5, fontWeight: 700, color: 'var(--dk-ink)' }}>{w.name}</div>
                            <div style={{ fontSize: 11.5, color: 'var(--dk-mute)' }}>Balance <span className="money">{formatMoney(w.balance, w.currency)}</span></div>
                          </div>
                          <div style={{ textAlign: 'right' }}>
                            <div style={{ fontSize: 10.5, color: 'var(--dk-mute)', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '.06em' }}>After redeem</div>
                            <div className="money" style={{ fontSize: 14, fontWeight: 800, color: 'var(--dk-success)' }}>
                              {formatMoney(w.balance + (w.currency === 'USD' ? 100 : (w.currency === 'EUR' ? 92.40 : 80.10)), w.currency)}
                            </div>
                          </div>
                          <div style={{
                            width: 20, height: 20, borderRadius: '50%',
                            border: `2px solid ${sel ? 'var(--dk-accent)' : '#CBD5E1'}`,
                            background: sel ? 'var(--dk-accent)' : '#fff',
                            display: 'grid', placeItems: 'center',
                          }}>
                            {sel ? <Icon name="check" size={11} stroke={3} color="#fff"/> : null}
                          </div>
                        </button>
                      );
                    })}
                  </div>

                  <button
                    className="btn btn-base btn-lg"
                    style={{ width: '100%', justifyContent: 'center', marginTop: 16 }}
                    onClick={() => setStage('success')}
                  >
                    <Icon name="check" size={16}/> Redeem {formatMoney(100)} to {wallet.name}
                  </button>
                  <button className="btn btn-ghost" style={{ width: '100%', justifyContent: 'center', marginTop: 6 }} onClick={reset}>
                    Use a different code
                  </button>
                </div>
              </>
            )}

            {stage === 'success' && (
              <>
                <style>{`
                  @keyframes pop-in { 0% { transform: scale(.6); opacity: 0 } 60% { transform: scale(1.08); opacity: 1 } 100% { transform: scale(1); } }
                  @keyframes confetti-fall {
                    0% { transform: translateY(-30px) rotate(0); opacity: 0 }
                    10% { opacity: 1 }
                    100% { transform: translateY(80vh) rotate(720deg); opacity: .7 }
                  }
                `}</style>
                {/* mini confetti */}
                <div aria-hidden="true" style={{ position: 'absolute', inset: 0, pointerEvents: 'none', overflow: 'hidden' }}>
                  {Array.from({ length: 40 }, (_, i) => {
                    const colors = ['#FDE68A','#FCA5A5','#A7F3D0','#BFDBFE','#FBCFE8'];
                    return <span key={i} style={{
                      position: 'absolute',
                      left: `${(i * 13) % 100}%`,
                      top: 0,
                      width: 7 + (i % 3) * 3,
                      height: (i % 2) === 0 ? 4 : 9,
                      background: colors[i % 5],
                      borderRadius: i % 3 === 2 ? '50%' : 1.5,
                      animation: `confetti-fall ${3 + (i % 4)}s ${(i % 10) * 0.2}s linear infinite`,
                    }}/>;
                  })}
                </div>

                <div style={{ animation: 'pop-in .6s cubic-bezier(.2,1.4,.4,1) both' }}>
                  <h1 className="page-title" style={{ fontSize: 30 }}>You’re $100 richer 🎉</h1>
                  <p className="page-sub">Your USD Primary wallet has been topped up. The deposit shows in your transaction history.</p>
                </div>

                <div className="single-form-card" style={{ padding: 24, marginTop: 24, textAlign: 'left' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 14, padding: 14, background: 'linear-gradient(135deg, #DCFCE7, #BBF7D0)', borderRadius: 14, border: '1px solid #86EFAC' }}>
                    <div style={{
                      width: 44, height: 44, borderRadius: 12, background: '#fff',
                      display: 'grid', placeItems: 'center', color: 'var(--dk-success)',
                      boxShadow: '0 4px 10px rgba(22,163,74,.20)',
                    }}>
                      <Icon name="wallet" size={22}/>
                    </div>
                    <div style={{ flex: 1 }}>
                      <div style={{ fontSize: 11, color: '#166534', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '.06em' }}>New USD Primary balance</div>
                      <div className="money" style={{ fontSize: 28, fontWeight: 800, color: '#14532D', letterSpacing: '-.02em' }}>
                        $14,450.00
                      </div>
                    </div>
                    <div style={{ textAlign: 'right' }}>
                      <div style={{ fontSize: 11, color: '#166534', fontWeight: 700 }}>Added</div>
                      <div className="money" style={{ fontSize: 17, fontWeight: 800, color: '#14532D' }}>+$100.00</div>
                    </div>
                  </div>

                  <div className="summery-list" style={{ marginTop: 14 }}>
                    <div className="row"><span className="l">From</span><span className="r">Ayesha Rahman</span></div>
                    <div className="row"><span className="l">Gift code</span><span className="r" style={{ fontFamily: 'ui-monospace, "SF Mono", Menlo, monospace', letterSpacing: '.12em' }}>{code}</span></div>
                    <div className="row"><span className="l">Reference</span><span className="r">TX-202605-04F2A9X7K</span></div>
                  </div>

                  <div style={{ display: 'flex', gap: 10, marginTop: 16 }}>
                    <button className="btn btn-base" style={{ flex: 1, justifyContent: 'center' }}>
                      <Icon name="wallet" size={15}/> Go to wallet
                    </button>
                    <button className="btn btn-light" style={{ flex: 1, justifyContent: 'center' }} onClick={reset}>
                      <Icon name="plus" size={15}/> Redeem another
                    </button>
                  </div>
                </div>
              </>
            )}
          </div>
        </div>
    </div>
  );
};
window.Screen4Redeem = Screen4Redeem;
