/* global React, Sidebar, Topbar, Breadcrumb, Icon, GiftCardDesign, TEMPLATES */
const { useState: useState5 } = React;

/* ===========================================================
   SCREEN 5 — Admin · Gift Card Template Manager
   =========================================================== */

const ADMIN_ROWS = [
  { id: 1, tpl: 'birthday',    name: 'Confetti Pop',   cat: 'Birthday',         status: 'active',   sort: 1,  used: 248 },
  { id: 2, tpl: 'congrats',    name: 'Sky Sparkle',    cat: 'Congratulations',  status: 'active',   sort: 2,  used: 162 },
  { id: 3, tpl: 'holiday',     name: 'Pine & Lights',  cat: 'Holiday',          status: 'active',   sort: 3,  used: 540 },
  { id: 4, tpl: 'thankyou',    name: 'Golden Hour',    cat: 'Thank You',        status: 'active',   sort: 4,  used: 89 },
  { id: 5, tpl: 'anniversary', name: 'Eternal Plum',   cat: 'Anniversary',      status: 'draft',    sort: 5,  used: 0 },
  { id: 6, tpl: 'premium',     name: 'Navy Classic',   cat: 'General',          status: 'active',   sort: 6,  used: 412 },
  { id: 7, tpl: 'birthday',    name: 'Rose Garden',    cat: 'Anniversary',      status: 'inactive', sort: 7,  used: 32 },
];

const ADMIN_STATUS = {
  active:   { label: 'Active',   cls: 'badge-success' },
  draft:    { label: 'Draft',    cls: 'badge-warning' },
  inactive: { label: 'Inactive', cls: 'badge-muted' },
};

const Screen5Admin = () => {
  const [editing, setEditing] = useState5(ADMIN_ROWS[0]);
  const [bg, setBg] = useState5('#FB7185');
  const [ink, setInk] = useState5('#FFFFFF');

  return (
    <div className="dk-app" data-screen-label="05 Admin — Template Manager">
      <div className="dk-content">
          <Breadcrumb items={['Admin', 'Gift Cards', 'Templates']}/>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginTop: 4, marginBottom: 22 }}>
            <div>
              <h1 className="page-title">Gift Card Templates</h1>
              <p className="page-sub">Manage the designs available to users when creating a gift card.</p>
            </div>
            <div style={{ display: 'flex', gap: 10 }}>
              <button className="btn btn-light"><Icon name="upload" size={15}/> Import</button>
              <button className="btn btn-base"><Icon name="plus" size={15}/> Add Template</button>
            </div>
          </div>

          {/* Stat strip */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 14, marginBottom: 20 }}>
            <AdminStat icon="paint"    bg="#DBEAFE" color="#1D4ED8" label="Total templates"     value="24"/>
            <AdminStat icon="check"    bg="#DCFCE7" color="#15803D" label="Active"              value="19"/>
            <AdminStat icon="edit"     bg="#FEF3C7" color="#92400E" label="Drafts"              value="3"/>
            <AdminStat icon="gift"     bg="#FCE7F3" color="#BE185D" label="Cards sent this mo." value="1,483"/>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 460px', gap: 18, alignItems: 'flex-start' }}>
            {/* TABLE */}
            <div className="card-main" style={{ overflow: 'hidden' }}>
              {/* table toolbar */}
              <div style={{ padding: 14, display: 'flex', gap: 10, alignItems: 'center', borderBottom: '1px solid var(--dk-card-line)' }}>
                <div className="search" style={{
                  display: 'flex', alignItems: 'center', gap: 8,
                  background: 'var(--dk-bg-soft)', border: '1px solid var(--dk-card-line)',
                  borderRadius: 8, padding: '7px 10px', flex: 1, color: 'var(--dk-mute)', fontSize: 12.5,
                }}>
                  <Icon name="search" size={14}/> Search templates by name
                </div>
                <button className="btn btn-light btn-sm"><Icon name="filter" size={13}/> Category: All</button>
                <button className="btn btn-light btn-sm">Status: All <Icon name="chevron-down" size={13}/></button>
              </div>

              <table className="tbl">
                <thead>
                  <tr>
                    <th style={{ width: 96 }}>Thumb</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th style={{ width: 90 }}>Sort</th>
                    <th>Used</th>
                    <th style={{ width: 130 }}>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {ADMIN_ROWS.map(r => {
                    const isEditing = editing?.id === r.id;
                    return (
                      <tr key={r.id} style={{ background: isEditing ? '#FAFBFE' : undefined, cursor: 'pointer' }} onClick={() => setEditing(r)}>
                        <td>
                          <div style={{ width: 76, height: 48, borderRadius: 8, overflow: 'hidden', boxShadow: 'var(--dk-shadow-sm)' }}>
                            <GiftCardDesign template={r.tpl} width={76} amount={50} recipient="—" sender="—"/>
                          </div>
                        </td>
                        <td>
                          <div style={{ fontWeight: 700, color: 'var(--dk-ink)' }}>{r.name}</div>
                          <div style={{ fontSize: 11.5, color: 'var(--dk-mute)' }}>ID #GC-{String(r.id).padStart(3,'0')}</div>
                        </td>
                        <td><span className="badge badge-info"><span className="dot"/>{r.cat}</span></td>
                        <td><span className={`badge ${ADMIN_STATUS[r.status].cls}`}><span className="dot"/>{ADMIN_STATUS[r.status].label}</span></td>
                        <td style={{ fontVariantNumeric: 'tabular-nums' }}>{r.sort}</td>
                        <td><span className="money" style={{ fontWeight: 700 }}>{r.used.toLocaleString()}</span> <span style={{ color: 'var(--dk-mute)', fontSize: 11.5 }}>cards</span></td>
                        <td>
                          <div style={{ display: 'flex', gap: 4 }}>
                            <button className="btn btn-light btn-sm" style={{ padding: '6px 8px' }} aria-label="Preview"><Icon name="eye" size={13}/></button>
                            <button className="btn btn-light btn-sm" style={{ padding: '6px 8px', background: isEditing ? 'var(--dk-primary-50)' : '#fff', color: isEditing ? 'var(--dk-primary-600)' : undefined, borderColor: isEditing ? '#BFDBFE' : undefined }} aria-label="Edit"><Icon name="edit" size={13}/></button>
                            <button className="btn btn-light btn-sm" style={{ padding: '6px 8px', color: 'var(--dk-danger)' }} aria-label="Delete"><Icon name="trash" size={13}/></button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>

              <div style={{ padding: '12px 16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: '1px solid var(--dk-card-line)' }}>
                <div style={{ fontSize: 12, color: 'var(--dk-mute)' }}>Showing 1–7 of 24 templates</div>
                <div style={{ display: 'flex', gap: 4 }}>
                  <button className="btn btn-light btn-sm" style={{ padding: '6px 9px' }}><Icon name="arrow-left" size={12}/></button>
                  <button className="btn btn-light btn-sm" style={{ background: 'var(--dk-primary-50)', color: 'var(--dk-primary-600)', borderColor: '#BFDBFE' }}>1</button>
                  <button className="btn btn-light btn-sm">2</button>
                  <button className="btn btn-light btn-sm">3</button>
                  <button className="btn btn-light btn-sm" style={{ padding: '6px 9px' }}><Icon name="arrow-right" size={12}/></button>
                </div>
              </div>
            </div>

            {/* EDIT DRAWER */}
            <aside className="card-main" style={{ padding: 22, position: 'sticky', top: 0 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 4 }}>
                <div>
                  <div style={{ fontSize: 11, fontWeight: 700, color: 'var(--dk-primary-600)', letterSpacing: '.10em', textTransform: 'uppercase' }}>Editing template</div>
                  <h2 style={{ fontSize: 18, fontWeight: 800, margin: '2px 0 0', color: 'var(--dk-ink)' }}>{editing.name}</h2>
                </div>
                <button className="btn btn-ghost btn-sm" aria-label="Close"><Icon name="x" size={15}/></button>
              </div>

              {/* Live preview */}
              <div style={{
                background: 'linear-gradient(180deg, #F4F6FB 0%, #EEF2FF 100%)',
                borderRadius: 14, padding: 18, marginTop: 14, marginBottom: 18,
                border: '1px solid var(--dk-card-line)',
                display: 'grid', placeItems: 'center',
              }}>
                <GiftCardDesign template={editing.tpl} width={320} amount={50} recipient="Sample recipient" sender="Sample sender"/>
              </div>

              {/* Form */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                <div style={{ gridColumn: '1 / -1' }}>
                  <label className="form-label">Background image</label>
                  <div style={{
                    border: '2px dashed var(--dk-card-line)',
                    borderRadius: 12, padding: '14px 16px',
                    display: 'flex', alignItems: 'center', gap: 12,
                    background: 'var(--dk-bg-soft)',
                  }}>
                    <div style={{ width: 48, height: 48, borderRadius: 10, background: '#fff', display: 'grid', placeItems: 'center', color: 'var(--dk-primary-600)', border: '1px solid var(--dk-card-line)' }}>
                      <Icon name="image" size={20}/>
                    </div>
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--dk-ink)' }}>card-birthday-confetti.jpg</div>
                      <div style={{ fontSize: 11.5, color: 'var(--dk-mute)' }}>1600×1008 · 240 KB</div>
                    </div>
                    <button className="btn btn-light btn-sm"><Icon name="upload" size={13}/> Replace</button>
                  </div>
                </div>

                <div>
                  <label className="form-label">Name</label>
                  <input className="form-control" value={editing.name} readOnly/>
                </div>
                <div>
                  <label className="form-label">Category</label>
                  <select className="form-control" defaultValue={editing.cat}>
                    <option>Birthday</option>
                    <option>Holiday</option>
                    <option>Thank You</option>
                    <option>Anniversary</option>
                    <option>Congratulations</option>
                    <option>General</option>
                  </select>
                </div>

                <div>
                  <label className="form-label">Background color</label>
                  <ColorPicker value={bg} onChange={setBg} swatches={['#FB7185','#3B82F6','#10B981','#FBBF24','#8B5CF6','#0F172A']}/>
                </div>
                <div>
                  <label className="form-label">Text color</label>
                  <ColorPicker value={ink} onChange={setInk} swatches={['#FFFFFF','#0F172A','#FCD34D','#FECDD3']}/>
                </div>

                <div>
                  <label className="form-label">Status</label>
                  <div style={{ display: 'flex', gap: 6 }}>
                    {['active','draft','inactive'].map(s => (
                      <button key={s} className={`chip ${editing.status === s ? 'is-active' : ''}`} onClick={() => setEditing({ ...editing, status: s })} style={{ textTransform: 'capitalize', fontSize: 12 }}>{s}</button>
                    ))}
                  </div>
                </div>
                <div>
                  <label className="form-label">Sort order</label>
                  <input className="form-control" type="number" defaultValue={editing.sort}/>
                </div>
              </div>

              <div style={{ display: 'flex', gap: 8, marginTop: 20, paddingTop: 18, borderTop: '1px solid var(--dk-card-line)' }}>
                <button className="btn btn-light" style={{ flex: 1, justifyContent: 'center' }}>Cancel</button>
                <button className="btn btn-base" style={{ flex: 2, justifyContent: 'center' }}><Icon name="check" size={15}/> Save changes</button>
              </div>
            </aside>
          </div>
        </div>
    </div>
  );
};

const AdminStat = ({ icon, bg, color, label, value }) => (
  <div className="stat-card" style={{ padding: '14px 16px' }}>
    <div className="ic" style={{ background: bg, color, width: 36, height: 36 }}><Icon name={icon} size={18}/></div>
    <div className="body">
      <div className="lbl">{label}</div>
      <div className="val money" style={{ fontSize: 20 }}>{value}</div>
    </div>
  </div>
);

const ColorPicker = ({ value, onChange, swatches }) => (
  <div style={{
    display: 'flex', alignItems: 'center', gap: 8,
    padding: '6px 10px',
    background: '#fff', border: '1px solid var(--dk-card-line)', borderRadius: 10,
  }}>
    <div style={{ width: 24, height: 24, borderRadius: 6, background: value, border: '1px solid rgba(15,23,42,.10)' }}/>
    <code style={{ fontSize: 12, color: 'var(--dk-mute)', fontFamily: 'ui-monospace, Menlo, monospace', flex: 1 }}>{value}</code>
    <div style={{ display: 'flex', gap: 4 }}>
      {swatches.map(s => (
        <button key={s} onClick={() => onChange(s)} style={{
          width: 18, height: 18, borderRadius: 5, background: s,
          border: value === s ? '2px solid var(--dk-accent)' : '1px solid rgba(15,23,42,.12)',
          cursor: 'pointer', padding: 0,
        }} aria-label={`Color ${s}`}/>
      ))}
    </div>
  </div>
);

window.Screen5Admin = Screen5Admin;
