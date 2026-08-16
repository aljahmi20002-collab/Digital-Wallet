/* global React, Sidebar, Topbar, Breadcrumb, Icon, GiftCardDesign, TEMPLATES, formatMoney */
const { useState: useState2, useMemo: useMemo2 } = React;

/* ===========================================================
   SCREEN 2 — Create Gift Card · 4-step wizard
   =========================================================== */

const ALL_TEMPLATES = [
  { id: 'birthday-1',   tpl: 'birthday',    name: 'Confetti Pop',   cat: 'Birthday' },
  { id: 'birthday-2',   tpl: 'congrats',    name: 'Cake & Candles', cat: 'Birthday' },
  { id: 'holiday-1',    tpl: 'holiday',     name: 'Pine & Lights',  cat: 'Holiday' },
  { id: 'holiday-2',    tpl: 'premium',     name: 'Midnight Frost', cat: 'Holiday' },
  { id: 'thanks-1',     tpl: 'thankyou',    name: 'Golden Hour',    cat: 'Thank You' },
  { id: 'thanks-2',     tpl: 'premium',     name: 'Quiet Thanks',   cat: 'Thank You' },
  { id: 'anniv-1',      tpl: 'anniversary', name: 'Eternal Plum',   cat: 'Anniversary' },
  { id: 'anniv-2',      tpl: 'birthday',    name: 'Rose Garden',    cat: 'Anniversary' },
  { id: 'congrats-1',   tpl: 'congrats',    name: 'Sky Sparkle',    cat: 'Congratulations' },
  { id: 'congrats-2',   tpl: 'thankyou',    name: 'Sun Burst',      cat: 'Congratulations' },
  { id: 'general-1',    tpl: 'premium',     name: 'Navy Classic',   cat: 'General' },
  { id: 'general-2',    tpl: 'holiday',     name: 'Evergreen',      cat: 'General' },
];

const CATEGORIES = ['All', 'Birthday', 'Holiday', 'Thank You', 'Anniversary', 'Congratulations'];

const WALLETS = [
  { id: 'usd', name: 'USD Primary',   balance: 14350.00, currency: 'USD' },
  { id: 'eur', name: 'EUR Wallet',    balance:  4820.50, currency: 'EUR' },
  { id: 'gbp', name: 'GBP Wallet',    balance:  1240.00, currency: 'GBP' },
];

const Stepper = ({ step, setStep, steps }) => (
  <div className="stepper">
    {steps.map((s, i) => (
      <React.Fragment key={i}>
        <button
          className={`step ${i+1 < step ? 'done' : ''} ${i+1 === step ? 'current' : ''}`}
          onClick={() => setStep(i+1)}
          style={{ background: 'none', border: 0, padding: 0, cursor: 'pointer', textAlign: 'left' }}
        >
          <div className="n">{i+1 < step ? <Icon name="check" size={14}/> : i+1}</div>
          <div className="lbl">
            <b>Step {i+1}</b>
            {s}
          </div>
        </button>
        {i < steps.length - 1 ? <div className={`bar ${i+1 < step ? 'done' : ''}`}/> : null}
      </React.Fragment>
    ))}
  </div>
);

/* ----- Step 1: Template gallery ------------------------- */
const Step1Templates = ({ selected, setSelected }) => {
  const [cat, setCat] = useState2('All');
  const filtered = cat === 'All' ? ALL_TEMPLATES : ALL_TEMPLATES.filter(t => t.cat === cat);
  return (
    <>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16 }}>
        <div>
          <h2 style={{ fontSize: 17, fontWeight: 800, margin: 0, color: 'var(--dk-ink)' }}>Choose a design</h2>
          <p style={{ fontSize: 12.5, color: 'var(--dk-mute)', margin: '3px 0 0' }}>The look the recipient sees when they open the card.</p>
        </div>
        <div style={{ fontSize: 12, color: 'var(--dk-mute)' }}>
          {filtered.length} designs · <a href="#" style={{ color: 'var(--dk-primary-600)', fontWeight: 600 }}>Browse all →</a>
        </div>
      </div>

      <div className="chip-row" style={{ marginBottom: 18 }}>
        {CATEGORIES.map(c => (
          <button key={c} className={`chip ${cat === c ? 'is-active' : ''}`} onClick={() => setCat(c)}>
            {c} {cat === c ? <span className="count">{c === 'All' ? ALL_TEMPLATES.length : ALL_TEMPLATES.filter(t => t.cat === c).length}</span> : null}
          </button>
        ))}
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16 }}>
        {filtered.map(t => {
          const isSel = selected?.id === t.id;
          return (
            <button
              key={t.id}
              onClick={() => setSelected(t)}
              style={{
                position: 'relative',
                padding: 0, border: 0, background: 'none', cursor: 'pointer',
                textAlign: 'left',
              }}
            >
              <div style={{
                background: '#fff',
                padding: 10,
                borderRadius: 18,
                border: `2px solid ${isSel ? 'var(--dk-accent)' : 'var(--dk-card-line)'}`,
                boxShadow: isSel ? '0 8px 24px rgba(59,130,246,.22)' : 'var(--dk-shadow-sm)',
                transition: 'all .15s',
              }}>
                <GiftCardDesign template={t.tpl} width={216} amount={50} recipient="Recipient" sender="You"/>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '10px 4px 2px' }}>
                  <div>
                    <div style={{ fontSize: 12.5, fontWeight: 700, color: 'var(--dk-ink)' }}>{t.name}</div>
                    <div style={{ fontSize: 11, color: 'var(--dk-mute)' }}>{t.cat}</div>
                  </div>
                  {isSel ? (
                    <div style={{
                      width: 24, height: 24, borderRadius: '50%',
                      background: 'var(--dk-accent)', color: '#fff',
                      display: 'grid', placeItems: 'center',
                      boxShadow: '0 4px 10px rgba(59,130,246,.45)',
                    }}>
                      <Icon name="check" size={14} stroke={3}/>
                    </div>
                  ) : null}
                </div>
              </div>
            </button>
          );
        })}
      </div>
    </>
  );
};

/* ----- Step 2: Amount & wallet -------------------------- */
const QUICK = [25, 50, 100, 200];

const Step2Amount = ({ amount, setAmount, wallet, setWallet }) => (
  <>
    <h2 style={{ fontSize: 17, fontWeight: 800, margin: '0 0 4px', color: 'var(--dk-ink)' }}>Amount & wallet</h2>
    <p style={{ fontSize: 12.5, color: 'var(--dk-mute)', margin: '0 0 22px' }}>Funds are reserved from your wallet and released when the recipient redeems.</p>

    <div style={{ marginBottom: 22 }}>
      <label className="form-label">Pay from wallet</label>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10 }}>
        {WALLETS.map(w => {
          const sel = wallet.id === w.id;
          return (
            <button key={w.id} onClick={() => setWallet(w)} style={{
              padding: '14px 14px',
              background: sel ? 'var(--dk-primary-50)' : '#fff',
              border: `1.5px solid ${sel ? 'var(--dk-accent)' : 'var(--dk-card-line)'}`,
              borderRadius: 12, textAlign: 'left', cursor: 'pointer',
              boxShadow: sel ? '0 4px 12px rgba(59,130,246,.15)' : 'none',
            }}>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--dk-ink)' }}>{w.name}</div>
                {sel ? <div style={{ width: 18, height: 18, borderRadius: '50%', background: 'var(--dk-accent)', display: 'grid', placeItems: 'center' }}><Icon name="check" size={11} stroke={3} color="#fff"/></div> : null}
              </div>
              <div style={{ fontSize: 11.5, color: 'var(--dk-mute)', marginTop: 3 }}>Available balance</div>
              <div className="money" style={{ fontSize: 17, fontWeight: 800, color: 'var(--dk-ink)', marginTop: 2 }}>
                {formatMoney(w.balance, w.currency)}
              </div>
            </button>
          );
        })}
      </div>
    </div>

    <div style={{ marginBottom: 18 }}>
      <label className="form-label">Gift card amount</label>
      <div style={{ position: 'relative' }}>
        <span style={{
          position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)',
          fontSize: 22, fontWeight: 800, color: 'var(--dk-mute)',
        }}>$</span>
        <input
          className="form-control money"
          value={amount}
          onChange={e => setAmount(e.target.value.replace(/[^0-9.]/g, ''))}
          style={{ paddingLeft: 32, fontSize: 26, fontWeight: 800, padding: '16px 16px 16px 34px', letterSpacing: '-.02em' }}
        />
        <span style={{ position: 'absolute', right: 14, top: '50%', transform: 'translateY(-50%)', fontSize: 12, fontWeight: 700, color: 'var(--dk-mute)' }}>{wallet.currency}</span>
      </div>
      <div className="form-hint">Min $5 · Max $1,000 per gift card. Funds reserved from {wallet.name}.</div>
    </div>

    <div>
      <label className="form-label">Quick amounts</label>
      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
        {QUICK.map(v => (
          <button key={v} className={`chip ${Number(amount) === v ? 'is-active' : ''}`} onClick={() => setAmount(String(v))}>${v}</button>
        ))}
        <button className="chip">Custom…</button>
      </div>
    </div>
  </>
);

/* ----- Step 3: Recipient -------------------------------- */
const Step3Recipient = ({ recipient, setRecipient, schedule, setSchedule }) => (
  <>
    <h2 style={{ fontSize: 17, fontWeight: 800, margin: '0 0 4px', color: 'var(--dk-ink)' }}>Who is it for?</h2>
    <p style={{ fontSize: 12.5, color: 'var(--dk-mute)', margin: '0 0 22px' }}>We’ll deliver the card by email, with a private redeem link.</p>

    {/* Toggle: existing user / anyone */}
    <div style={{ display: 'inline-flex', padding: 4, background: '#F1F5F9', borderRadius: 10, marginBottom: 20 }}>
      <button onClick={() => setRecipient({ ...recipient, mode: 'user' })} style={{
        padding: '8px 14px', borderRadius: 7, border: 0, fontSize: 12.5, fontWeight: 700, cursor: 'pointer',
        background: recipient.mode === 'user' ? '#fff' : 'transparent',
        color: recipient.mode === 'user' ? 'var(--dk-ink)' : 'var(--dk-mute)',
        boxShadow: recipient.mode === 'user' ? 'var(--dk-shadow-sm)' : 'none',
        display: 'inline-flex', alignItems: 'center', gap: 6,
      }}>
        <Icon name="user" size={13}/> Existing Digikash user
      </button>
      <button onClick={() => setRecipient({ ...recipient, mode: 'email' })} style={{
        padding: '8px 14px', borderRadius: 7, border: 0, fontSize: 12.5, fontWeight: 700, cursor: 'pointer',
        background: recipient.mode === 'email' ? '#fff' : 'transparent',
        color: recipient.mode === 'email' ? 'var(--dk-ink)' : 'var(--dk-mute)',
        boxShadow: recipient.mode === 'email' ? 'var(--dk-shadow-sm)' : 'none',
        display: 'inline-flex', alignItems: 'center', gap: 6,
      }}>
        <Icon name="mail" size={13}/> Send via email
      </button>
    </div>

    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14, marginBottom: 14 }}>
      <div>
        <label className="form-label">Recipient name</label>
        <input className="form-control" value={recipient.name} onChange={e => setRecipient({ ...recipient, name: e.target.value })} placeholder="Tahsin Ahmed"/>
      </div>
      <div>
        <label className="form-label">Recipient email</label>
        <div style={{ position: 'relative' }}>
          <input className="form-control" value={recipient.email} onChange={e => setRecipient({ ...recipient, email: e.target.value })} placeholder="tahsin@gmail.com" style={{ paddingRight: 78 }}/>
          {recipient.mode === 'user' && recipient.email ? (
            <span style={{
              position: 'absolute', right: 8, top: '50%', transform: 'translateY(-50%)',
              background: 'var(--dk-success-50)', color: '#166534',
              padding: '3px 7px', borderRadius: 999, fontSize: 10.5, fontWeight: 700,
              display: 'inline-flex', alignItems: 'center', gap: 4,
            }}>
              <Icon name="check" size={11} stroke={3}/> Verified
            </span>
          ) : null}
        </div>
        {recipient.mode === 'user' ? (
          <div className="form-hint">Found in Digikash · @tahsin_a</div>
        ) : null}
      </div>
    </div>

    <div style={{ marginBottom: 14 }}>
      <label className="form-label">Personal message <span className="opt">{recipient.message.length}/200</span></label>
      <textarea
        className="form-control"
        rows={3}
        value={recipient.message}
        onChange={e => setRecipient({ ...recipient, message: e.target.value.slice(0, 200) })}
        placeholder="Happy birthday Tahsin! Treat yourself to something nice. — Ayesha"
        style={{ resize: 'none', lineHeight: 1.5 }}
      />
    </div>

    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14, marginBottom: 14 }}>
      <div>
        <label className="form-label">Sender name (shown on card)</label>
        <input className="form-control" value={recipient.sender} onChange={e => setRecipient({ ...recipient, sender: e.target.value })}/>
      </div>
      <div>
        <label className="form-label">Delivery <span className="opt">optional</span></label>
        <div style={{ display: 'flex', gap: 8 }}>
          <button onClick={() => setSchedule(!schedule)} className={`chip ${!schedule ? 'is-active' : ''}`}>
            <Icon name="send" size={12}/> Send now
          </button>
          <button onClick={() => setSchedule(!schedule)} className={`chip ${schedule ? 'is-active' : ''}`}>
            <Icon name="calendar" size={12}/> Schedule
          </button>
        </div>
        {schedule ? (
          <input className="form-control" type="text" defaultValue="May 22, 2026 — 9:00 AM" style={{ marginTop: 8 }}/>
        ) : null}
      </div>
    </div>
  </>
);

/* ----- Live preview sidebar (visible all steps) --------- */
const PreviewSidebar = ({ template, amount, recipient, wallet, step, onSend }) => {
  const fee = +(amount * 0.015).toFixed(2);
  const total = +amount + fee;
  return (
    <aside style={{
      position: 'sticky', top: 0,
      width: 380, flex: 'none',
      display: 'flex', flexDirection: 'column', gap: 16,
    }}>
      <div className="single-form-card" style={{ padding: 20 }}>
        <div style={{ fontSize: 11, fontWeight: 700, color: 'var(--dk-mute)', letterSpacing: '.10em', textTransform: 'uppercase', marginBottom: 12 }}>
          Live preview
        </div>
        <div style={{ display: 'grid', placeItems: 'center', padding: '4px 0 12px' }}>
          <GiftCardDesign
            template={template?.tpl || 'premium'}
            width={320}
            amount={amount}
            recipient={recipient.name || 'Recipient'}
            sender={recipient.sender || 'You'}
          />
        </div>
        {recipient.message ? (
          <div style={{
            marginTop: 10, padding: '12px 14px',
            background: '#FAFBFE', border: '1px solid var(--dk-card-line)', borderRadius: 12,
            fontSize: 13, color: 'var(--dk-ink-2)', lineHeight: 1.5,
            fontStyle: 'italic',
          }}>
            “{recipient.message}”
          </div>
        ) : null}
      </div>

      <div className="single-form-card" style={{ padding: 20 }}>
        <div style={{ fontSize: 14, fontWeight: 800, color: 'var(--dk-ink)', marginBottom: 4 }}>Order summary</div>
        <div className="summery-list">
          <div className="row"><span className="l">Gift card value</span><span className="r money">{formatMoney(amount, wallet.currency)}</span></div>
          <div className="row"><span className="l">Service fee (1.5%)</span><span className="r money">{formatMoney(fee, wallet.currency)}</span></div>
          <div className="row"><span className="l">Conversion rate</span><span className="r">1.00 {wallet.currency}</span></div>
          <div className="row"><span className="l">Delivery</span><span className="r">Email</span></div>
          <div className="row total">
            <span className="l">Payable from {wallet.name}</span>
            <span className="r money">{formatMoney(total, wallet.currency)}</span>
          </div>
        </div>
        <button className="btn btn-base btn-lg" style={{ width: '100%', justifyContent: 'center', marginTop: 14 }} onClick={onSend}>
          <Icon name="gift" size={16}/> Send Gift Card
        </button>
        <div style={{ display: 'flex', alignItems: 'center', gap: 6, justifyContent: 'center', marginTop: 10, fontSize: 11, color: 'var(--dk-mute)' }}>
          <Icon name="lock" size={11}/> Funds held in escrow until redeemed
        </div>
      </div>
    </aside>
  );
};

/* ----- Wizard wrapper ----------------------------------- */
const Screen2Create = () => {
  const [step, setStep] = useState2(1);
  const [tpl, setTpl] = useState2(ALL_TEMPLATES[0]);
  const [amount, setAmount] = useState2('100');
  const [wallet, setWallet] = useState2(WALLETS[0]);
  const [schedule, setSchedule] = useState2(false);
  const [recipient, setRecipient] = useState2({
    mode: 'user',
    name: 'Tahsin Ahmed',
    email: 'tahsin@gmail.com',
    message: 'Happy birthday Tahsin! Treat yourself to something nice. — Ayesha',
    sender: 'Ayesha Rahman',
  });

  return (
    <div className="dk-app" data-screen-label="02 Gift Cards — Create (Wizard)">
      <div className="dk-content">
          <Breadcrumb items={['Dashboard', 'Gift Cards', 'Create']}/>
          <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', marginTop: 4, marginBottom: 24 }}>
            <div>
              <h1 className="page-title">Create a gift card</h1>
              <p className="page-sub">Pick a design, set the amount, personalize, and send.</p>
            </div>
            <button className="btn btn-ghost"><Icon name="x" size={15}/> Cancel</button>
          </div>

          {/* Stepper */}
          <div className="single-form-card" style={{ padding: 18, marginBottom: 20 }}>
            <Stepper step={step} setStep={setStep} steps={['Design', 'Amount', 'Recipient', 'Review']}/>
          </div>

          {/* Wizard body + sticky preview */}
          <div style={{ display: 'flex', gap: 20, alignItems: 'flex-start' }}>
            <div className="single-form-card" style={{ flex: 1, padding: 26, minWidth: 0 }}>
              {step === 1 ? <Step1Templates selected={tpl} setSelected={setTpl}/> : null}
              {step === 2 ? <Step2Amount amount={amount} setAmount={setAmount} wallet={wallet} setWallet={setWallet}/> : null}
              {step === 3 ? <Step3Recipient recipient={recipient} setRecipient={setRecipient} schedule={schedule} setSchedule={setSchedule}/> : null}
              {step === 4 ? <Step4Review tpl={tpl} amount={amount} wallet={wallet} recipient={recipient} schedule={schedule}/> : null}

              {/* footer nav */}
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 26, paddingTop: 18, borderTop: '1px solid var(--dk-card-line)' }}>
                <button className="btn btn-light" disabled={step === 1} onClick={() => setStep(s => Math.max(1, s - 1))} style={{ opacity: step === 1 ? 0.5 : 1 }}>
                  <Icon name="arrow-left" size={14}/> Back
                </button>
                {step < 4 ? (
                  <button className="btn btn-base" onClick={() => setStep(s => Math.min(4, s + 1))}>
                    Continue <Icon name="arrow-right" size={14}/>
                  </button>
                ) : (
                  <button className="btn btn-base"><Icon name="gift" size={15}/> Send Gift Card</button>
                )}
              </div>
            </div>

            <PreviewSidebar template={tpl} amount={amount} recipient={recipient} wallet={wallet} step={step}/>
          </div>
        </div>
    </div>
  );
};

const Step4Review = ({ tpl, amount, wallet, recipient, schedule }) => {
  const fee = +(amount * 0.015).toFixed(2);
  return (
    <>
      <h2 style={{ fontSize: 17, fontWeight: 800, margin: '0 0 4px', color: 'var(--dk-ink)' }}>Review & send</h2>
      <p style={{ fontSize: 12.5, color: 'var(--dk-mute)', margin: '0 0 22px' }}>Confirm everything below — once sent, the recipient gets an email immediately.</p>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
        <ReviewBlock label="Design"      value={tpl?.name || '—'}                  meta={tpl?.cat} icon="paint"/>
        <ReviewBlock label="Amount"      value={formatMoney(amount, wallet.currency)} meta={`From ${wallet.name}`} icon="wallet"/>
        <ReviewBlock label="Recipient"   value={recipient.name}                    meta={recipient.email} icon="user"/>
        <ReviewBlock label="Delivery"    value={schedule ? 'May 22, 9:00 AM' : 'Send immediately'} meta="By email" icon={schedule ? 'calendar' : 'send'}/>
      </div>

      <div style={{ marginTop: 16, padding: 16, background: '#FAFBFE', border: '1px solid var(--dk-card-line)', borderRadius: 12 }}>
        <div className="form-label" style={{ marginBottom: 6 }}>Personal message</div>
        <div style={{ fontSize: 13, color: 'var(--dk-ink-2)', fontStyle: 'italic', lineHeight: 1.55 }}>
          “{recipient.message || 'No message added.'}”
        </div>
      </div>

      <label style={{ display: 'flex', alignItems: 'flex-start', gap: 10, marginTop: 18, padding: '12px 14px', background: 'var(--dk-primary-50)', border: '1px solid #BFDBFE', borderRadius: 12, cursor: 'pointer' }}>
        <input type="checkbox" defaultChecked style={{ marginTop: 3 }}/>
        <div>
          <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--dk-primary-600)' }}>I agree to the Gift Card terms</div>
          <div style={{ fontSize: 11.5, color: 'var(--dk-mute)', marginTop: 2 }}>Cards expire after 12 months. Unredeemed funds return to your wallet.</div>
        </div>
      </label>
    </>
  );
};

const ReviewBlock = ({ label, value, meta, icon }) => (
  <div style={{ padding: 14, background: '#fff', border: '1px solid var(--dk-card-line)', borderRadius: 12, display: 'flex', gap: 12, alignItems: 'flex-start' }}>
    <div style={{ width: 36, height: 36, borderRadius: 10, background: 'var(--dk-primary-50)', color: 'var(--dk-primary-600)', display: 'grid', placeItems: 'center' }}>
      <Icon name={icon} size={17}/>
    </div>
    <div style={{ minWidth: 0 }}>
      <div style={{ fontSize: 11, color: 'var(--dk-mute)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '.06em' }}>{label}</div>
      <div style={{ fontSize: 14, fontWeight: 700, color: 'var(--dk-ink)', marginTop: 1 }}>{value}</div>
      <div style={{ fontSize: 11.5, color: 'var(--dk-mute)', marginTop: 1 }}>{meta}</div>
    </div>
  </div>
);

window.Screen2Create = Screen2Create;
