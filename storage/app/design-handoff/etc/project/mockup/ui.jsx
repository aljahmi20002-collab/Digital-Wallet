// ui.jsx — small shared primitives used across all pages

// Pill with mandatory icon (matches the brief's a11y rule)
function Pill({ tone = 'neutral', icon, children, lg = false, ...rest }) {
  const iconMap = {
    success: 'fa-circle-check',
    warning: 'fa-clock',
    danger:  'fa-triangle-exclamation',
    info:    'fa-circle-info',
    neutral: 'fa-circle-minus',
    primary: 'fa-circle-dot',
  };
  const cls = `fb-pill fb-pill--${tone}${lg ? ' fb-pill--lg' : ''}`;
  return (
    <span className={cls} {...rest}>
      <i className={`fa-solid ${icon || iconMap[tone]}`} aria-hidden="true" />
      <span>{children}</span>
    </span>
  );
}

function Btn({ variant = 'ghost', size = 'sm', icon, children, iconOnly, ...rest }) {
  const cls = [
    'fb-btn',
    `fb-btn--${variant}`,
    size === 'sm' ? 'fb-btn--sm' : size === 'lg' ? 'fb-btn--lg' : '',
    iconOnly ? 'fb-btn--icon' : '',
  ].filter(Boolean).join(' ');
  return (
    <button className={cls} {...rest}>
      {icon && <i className={`fa-solid ${icon}`} aria-hidden="true" />}
      {children && <span>{children}</span>}
    </button>
  );
}

function Avatar({ name, size = 'md', tone = 'blue', src }) {
  const cls = `fb-avatar${size !== 'md' ? ` fb-avatar--${size}` : ''} fb-avatar--${tone}`;
  if (src) return <img src={src} alt={name} className={cls} />;
  const initials = (name || '?')
    .split(/\s+/).slice(0, 2).map((p) => p[0] || '').join('').toUpperCase();
  return <span className={cls}>{initials}</span>;
}

function User({ name, handle, tone = 'blue', meta }) {
  return (
    <span className="fb-user">
      <Avatar name={name} tone={tone} />
      <span className="fb-user__meta">
        <span className="fb-user__name">{name}</span>
        <span className="fb-user__handle">@{handle}</span>
        {meta && <span className="fb-user__handle" style={{ color: 'var(--color-text-muted)' }}>{meta}</span>}
      </span>
    </span>
  );
}

function Stars({ value = 5, max = 5 }) {
  return (
    <span className="fb-stars" aria-label={`${value} of ${max} stars`}>
      {Array.from({ length: max }).map((_, i) => (
        <i
          key={i}
          className={`fa-solid fa-star${i >= value ? ' empty' : ''}`}
          aria-hidden="true"
        />
      ))}
    </span>
  );
}

function Progress({ value, tone }) {
  const t = tone || (value >= 90 ? '' : value >= 70 ? 'warn' : 'low');
  return (
    <span className="fb-progress">
      <span className="fb-progress__track">
        <span className={`fb-progress__fill${t ? ` fb-progress__fill--${t}` : ''}`} style={{ width: `${value}%` }} />
      </span>
      <span className="fb-progress__label">
        <span>completion</span>
        <span className="fb-num">{value.toFixed(1)}%</span>
      </span>
    </span>
  );
}

function Tier({ kind }) {
  const label = { gold: 'Gold', silver: 'Silver', bronze: 'Bronze', merchant: 'Merchant' }[kind] || kind;
  return <span className={`fb-tier fb-tier--${kind}`}>{label}</span>;
}

function EmptyRow({ colSpan = 6, icon = 'fa-folder-open', title = 'No items', message = 'Nothing to show here yet.', cta }) {
  return (
    <tr>
      <td colSpan={colSpan}>
        <div className="pa-table-empty">
          <div className="pa-table-empty__icon"><i className={`fa-solid ${icon}`} /></div>
          <div className="pa-table-empty__title">{title}</div>
          <div>{message}</div>
          {cta && <div style={{ marginTop: 12 }}>{cta}</div>}
        </div>
      </td>
    </tr>
  );
}

function Pagination({ current = 1, total = 1, shown = '1–10', totalItems = 10 }) {
  const pages = [];
  for (let i = 1; i <= Math.min(total, 5); i++) pages.push(i);
  return (
    <>
      <span>Showing {shown} of <b className="fb-num">{totalItems}</b></span>
      <span className="pages">
        <button aria-label="Previous"><i className="fa-solid fa-chevron-left" /></button>
        {pages.map((p) => (
          <button key={p} className={p === current ? 'is-current' : ''}>{p}</button>
        ))}
        {total > 5 && <button>…</button>}
        <button aria-label="Next"><i className="fa-solid fa-chevron-right" /></button>
      </span>
    </>
  );
}

// Currency display: small prefix + tabular number
function Amount({ value, ccy = 'BDT', tone, large }) {
  const formatted = typeof value === 'number'
    ? value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    : value;
  const style = large ? { fontSize: 'var(--font-md)', fontWeight: 700 } : null;
  return (
    <span className="fb-num" style={{ display: 'inline-flex', alignItems: 'baseline', gap: 4, color: tone === 'muted' ? 'var(--color-text-muted)' : 'var(--color-text)', ...style }}>
      <span style={{ fontSize: '0.78em', fontWeight: 600, color: 'var(--color-text-faint)' }}>{ccy}</span>
      <span>{formatted}</span>
    </span>
  );
}

Object.assign(window, { Pill, Btn, Avatar, User, Stars, Progress, Tier, EmptyRow, Pagination, Amount });
