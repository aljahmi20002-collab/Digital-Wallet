// page-dispute.jsx — Dispute detail (Page 8)

function OrderSummaryCard() {
  const steps = [
    { id: 'created', label: 'Created',  time: '13:24', done: true },
    { id: 'paid',    label: 'Marked paid', time: '13:31', done: true },
    { id: 'disp',    label: 'Disputed', time: '13:47', active: true },
    { id: 'res',     label: 'Resolution', time: 'pending' },
  ];
  return (
    <section className="fb-card">
      <div className="fb-card__head">
        <div>
          <span className="fb-hero__eyebrow">Order · escrow held</span>
          <h5 style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <span className="fb-mono">#TX-29456</span>
            <Pill tone="warning">Disputed · 22m ago</Pill>
          </h5>
        </div>
        <div className="fb-card__meta">
          <span className="fb-pill fb-pill--neutral"><i className="fa-solid fa-clock" /><span>Opened by buyer</span></span>
        </div>
      </div>

      <div className="order-summary">
        <div className="order-summary__cell">
          <span className="order-summary__label">Amount</span>
          <span className="order-summary__value fb-num">৳ 42,500.00</span>
          <span className="order-summary__sub">≈ 0.00461802 BTC</span>
        </div>
        <div className="order-summary__cell">
          <span className="order-summary__label">Platform fee</span>
          <span className="order-summary__value fb-num">৳ 170.00</span>
          <span className="order-summary__sub">0.40% taker</span>
        </div>
        <div className="order-summary__cell">
          <span className="order-summary__label">Payment method</span>
          <span className="order-summary__value">bKash</span>
          <span className="order-summary__sub fb-mono">+880 1712-***-456</span>
        </div>
        <div className="order-summary__cell">
          <span className="order-summary__label">Opened</span>
          <span className="order-summary__value">22 min ago</span>
          <span className="order-summary__sub">May 19, 13:47 UTC+6</span>
        </div>
      </div>

      <div className="fb-card__body" style={{ borderTop: '1px solid var(--color-border-soft)' }}>
        <div className="fb-timeline">
          {steps.map((s) => (
            <div key={s.id} className={`fb-timeline__step ${s.done ? 'is-done' : ''} ${s.active ? 'is-active' : ''}`}>
              <span className="fb-timeline__dot">
                <i className={`fa-solid ${s.done ? 'fa-check' : s.active ? 'fa-circle-exclamation' : 'fa-circle'}`} />
              </span>
              <span className="fb-timeline__label">{s.label}</span>
              <span className="fb-timeline__time fb-num">{s.time}</span>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

function PaymentSnapshot() {
  return (
    <section className="fb-card">
      <div className="fb-card__head">
        <div>
          <span className="fb-hero__eyebrow">Payment snapshot</span>
          <h5>Buyer-attested receipt</h5>
        </div>
        <div className="fb-card__meta">
          <button className="fb-btn fb-btn--ghost fb-btn--sm"><i className="fa-solid fa-receipt" /><span>View proof image</span></button>
        </div>
      </div>
      <div className="fb-card__body">
        <div className="payment-snapshot">
          <span className="payment-snapshot__logo">bK</span>
          <div className="payment-snapshot__body">
            <div className="payment-snapshot__title">bKash · Personal</div>
            <div className="payment-snapshot__sub">+880 1712-***-456 · Naima Hossain</div>
          </div>
          <div style={{ textAlign: 'right' }}>
            <div className="fb-num" style={{ fontWeight: 800, fontSize: 'var(--font-md)' }}>৳ 42,500.00</div>
            <div style={{ fontSize: 11, color: 'var(--color-text-faint)' }}>TrxID <span className="fb-mono">7F3K4LP19A</span></div>
          </div>
        </div>
      </div>
    </section>
  );
}

function PartyCard({ role, name, handle, tone, tier, kyc, completion, orders, disputes }) {
  return (
    <div className="party-card">
      <div className="party-card__head">
        <Avatar name={name} tone={tone} size="lg" />
        <div style={{ minWidth: 0 }}>
          <div className="party-card__role">{role}</div>
          <div style={{ fontSize: 'var(--font-md)', fontWeight: 700, color: 'var(--color-text)' }}>{name}</div>
          <div style={{ fontSize: 'var(--font-xs)', color: 'var(--color-text-faint)', fontFamily: 'var(--font-mono)' }}>@{handle}</div>
        </div>
      </div>
      <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
        <Tier kind={tier} />
        {kyc === 'verified' ? <Pill tone="success">Verified</Pill> : <Pill tone="warning">KYC pending</Pill>}
      </div>
      <div className="party-card__stats">
        <div className="party-card__stat">
          <span className="party-card__stat-label">Orders</span>
          <span className="party-card__stat-value">{orders.toLocaleString()}</span>
        </div>
        <div className="party-card__stat">
          <span className="party-card__stat-label">Completion</span>
          <span className="party-card__stat-value">{completion.toFixed(1)}%</span>
        </div>
        <div className="party-card__stat">
          <span className="party-card__stat-label">Disputes</span>
          <span className="party-card__stat-value" style={{ color: disputes > 1 ? 'var(--color-danger)' : 'var(--color-text)' }}>{disputes}</span>
        </div>
      </div>
    </div>
  );
}

function PartiesCard() {
  return (
    <section className="fb-card">
      <div className="fb-card__head">
        <div>
          <span className="fb-hero__eyebrow">Parties</span>
          <h5>Maker · Taker</h5>
        </div>
      </div>
      <div className="fb-card__body">
        <div className="parties-grid">
          <PartyCard role="Maker · Seller"
            name="Naima Hossain" handle="naima.h" tone="rose"
            tier="gold" kyc="verified" completion={98.4} orders={1247} disputes={1} />
          <div className="parties-grid__divider">VS</div>
          <PartyCard role="Taker · Buyer"
            name="Imran Hossain" handle="imranhs" tone="blue"
            tier="silver" kyc="verified" completion={86.2} orders={39} disputes={3} />
        </div>
      </div>
    </section>
  );
}

function DisputeReasonCard() {
  return (
    <section className="fb-card">
      <div className="fb-card__head">
        <div>
          <span className="fb-hero__eyebrow">Buyer's claim</span>
          <h5>Dispute reason</h5>
        </div>
        <div className="fb-card__meta">
          <span className="fb-pill fb-pill--warning"><i className="fa-solid fa-clock" /><span>Awaiting your review</span></span>
        </div>
      </div>
      <div className="fb-card__body">
        <div className="dispute-reason">
          <span className="dispute-reason__icon"><i className="fa-solid fa-triangle-exclamation" /></span>
          <div style={{ flex: 1 }}>
            <div className="dispute-reason__meta">Reason · Payment not received</div>
            <p className="dispute-reason__text">
              "I sent ৳42,500 to the seller's bKash number 17 minutes ago and the trade was marked as paid,
              but the seller still hasn't released the crypto. Transaction ID 7F3K4LP19A — please verify and release."
            </p>
            <div className="dispute-reason__by">
              Raised by <b style={{ color: 'var(--color-text)' }}>Imran Hossain</b> · @imranhs · May 19, 13:47 (5 min after marking paid)
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

function ResolutionCard() {
  const [choice, setChoice] = React.useState('release');
  const [notes, setNotes] = React.useState('Buyer proof verified against bKash log. Releasing escrow to buyer.');
  const max = 280;
  return (
    <aside className="resolution-card">
      <div className="resolution-card__head">
        <span className="fb-hero__eyebrow">Resolve dispute</span>
        <h5>Choose an outcome</h5>
      </div>
      <div className="resolution-card__body">
        <div
          className={`resolution-card__choice ${choice === 'release' ? 'is-selected--release' : ''}`}
          onClick={() => setChoice('release')}
          role="radio" aria-checked={choice === 'release'} tabIndex={0}
        >
          <span className="resolution-card__choice-icon resolution-card__choice-icon--success">
            <i className="fa-solid fa-arrow-up-from-bracket" />
          </span>
          <div style={{ flex: 1, minWidth: 0 }}>
            <div className="resolution-card__choice-title">Release escrow to buyer</div>
            <div className="resolution-card__choice-sub">
              Crypto sent to <b>@imranhs</b>. Maker forfeits trade. Settles in ~15s.
            </div>
          </div>
          <span className="resolution-card__choice-radio" />
        </div>

        <div
          className={`resolution-card__choice ${choice === 'refund' ? 'is-selected--refund' : ''}`}
          onClick={() => setChoice('refund')}
          role="radio" aria-checked={choice === 'refund'} tabIndex={0}
        >
          <span className="resolution-card__choice-icon resolution-card__choice-icon--danger">
            <i className="fa-solid fa-arrow-rotate-left" />
          </span>
          <div style={{ flex: 1, minWidth: 0 }}>
            <div className="resolution-card__choice-title">Refund to maker</div>
            <div className="resolution-card__choice-sub">
              Crypto returns to <b>@naima.h</b>. Buyer is debited. Logs payment failure.
            </div>
          </div>
          <span className="resolution-card__choice-radio" />
        </div>

        <div className="resolution-card__form">
          <label htmlFor="res-notes">Admin notes (visible to both parties)</label>
          <textarea
            id="res-notes"
            className="p2p-settings-input form-control"
            rows="4"
            value={notes}
            onChange={(e) => setNotes(e.target.value.slice(0, max))}
            style={{ height: 'auto', minHeight: 84 }}
          />
          <div className="resolution-card__counter">
            <span>Required · be specific</span>
            <span><span className="fb-num">{notes.length}</span> / {max}</span>
          </div>
        </div>
      </div>
      <div className="resolution-card__foot">
        <button className="fb-btn fb-btn--ghost fb-btn--sm">Cancel</button>
        <button className={`fb-btn fb-btn--${choice === 'release' ? 'success' : 'danger'} fb-btn--sm`}>
          <i className={`fa-solid ${choice === 'release' ? 'fa-check' : 'fa-rotate-left'}`} />
          <span>{choice === 'release' ? 'Release escrow' : 'Refund maker'}</span>
        </button>
      </div>
    </aside>
  );
}

function DisputePage({ mobile }) {
  return (
    <P2PPage
      titleIcon="fa-gavel"
      title={<>Dispute <span className="fb-mono" style={{ fontWeight: 600, color: 'var(--color-text-muted)' }}>#TX-29456</span></>}
      actions={
        <>
          <button className="fb-btn fb-btn--ghost fb-btn--sm"><i className="fa-solid fa-chevron-left" /><span>Back to queue</span></button>
          <button className="fb-btn fb-btn--ghost fb-btn--sm"><i className="fa-solid fa-file-export" /><span>Export case</span></button>
        </>
      }
    >
      <div className={`dispute-detail-grid ${mobile ? 'is-mobile' : ''}`}>
        <div>
          <OrderSummaryCard />
          <PartiesCard />
          <DisputeReasonCard />
          <PaymentSnapshot />
        </div>
        <ResolutionCard />
      </div>
    </P2PPage>
  );
}

Object.assign(window, { DisputePage });
