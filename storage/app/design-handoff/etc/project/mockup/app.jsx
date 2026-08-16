// app.jsx — Composes the three pages into the design canvas.

const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "density": "comfy",
  "accent": "blue"
}/*EDITMODE-END*/;

const ACCENT_MAP = { blue: 'blue', emerald: 'emerald', amber: 'amber' };
const ACCENT_SWATCHES = ['#3B51CC', '#0F8A5B', '#B46A0A'];

// Each artboard hosts an isolated copy of the admin shell at a specific
// viewport. The wrapper div carries [data-density] and [data-accent] so
// every artboard updates in lockstep when tweaks change.
function PageArtboard({ width, height, viewport, activeId, children }) {
  return (
    <div
      style={{ width, height, overflow: 'hidden', background: 'var(--color-app-bg)' }}
    >
      <AdminShell activeId={activeId} viewport={viewport}>
        {children}
      </AdminShell>
    </div>
  );
}

function App() {
  const [t, setTweak] = useTweaks(TWEAK_DEFAULTS);
  const accent = ACCENT_MAP[t.accent] || 'blue';

  // Apply tweaks to a wrapper div that contains everything (not <html>) so
  // tokens stay scoped to our mockup.
  return (
    <div data-density={t.density} data-accent={accent}>
      <DesignCanvas>
        <DCSection
          id="dashboard"
          title="P2P Dashboard"
          subtitle="Page 1 of 11 · Market status card with live pulse, 4 fintech KPI cards (hover-lift), 14-day volume chart"
        >
          <DCArtboard id="dash-desktop" label="Desktop · 1440" width={1440} height={920}>
            <PageArtboard width={1440} height={920} viewport="desktop" activeId="p2pdash">
              <DashboardPage mobile={false} />
            </PageArtboard>
          </DCArtboard>
          <DCArtboard id="dash-mobile" label="Mobile · 375" width={375} height={1180}>
            <PageArtboard width={375} height={1180} viewport="mobile" activeId="p2pdash">
              <DashboardPage mobile={true} />
            </PageArtboard>
          </DCArtboard>
        </DCSection>

        <DCSection
          id="traders"
          title="Traders list"
          subtitle="Page 4 of 11 · Segmented filter with slide indicator, tier badges, completion bars, mobile card-mode table"
        >
          <DCArtboard id="trad-desktop" label="Desktop · 1440" width={1440} height={1000}>
            <PageArtboard width={1440} height={1000} viewport="desktop" activeId="traders">
              <TradersPage mobile={false} />
            </PageArtboard>
          </DCArtboard>
          <DCArtboard id="trad-mobile" label="Mobile · 375" width={375} height={1480}>
            <PageArtboard width={375} height={1480} viewport="mobile" activeId="traders">
              <TradersPage mobile={true} />
            </PageArtboard>
          </DCArtboard>
        </DCSection>

        <DCSection
          id="dispute"
          title="Dispute detail"
          subtitle="Page 8 of 11 · Order timeline strip, parties VS card, sticky two-choice resolution panel, character-counted notes"
        >
          <DCArtboard id="disp-desktop" label="Desktop · 1440" width={1440} height={1220}>
            <PageArtboard width={1440} height={1220} viewport="desktop" activeId="disputes">
              <DisputePage mobile={false} />
            </PageArtboard>
          </DCArtboard>
          <DCArtboard id="disp-mobile" label="Mobile · 375" width={375} height={2080}>
            <PageArtboard width={375} height={2080} viewport="mobile" activeId="disputes">
              <DisputePage mobile={true} />
            </PageArtboard>
          </DCArtboard>
        </DCSection>
      </DesignCanvas>

      <TweaksPanel>
        <TweakSection label="Density" />
        <TweakRadio
          label="Spacing"
          value={t.density}
          options={['compact', 'comfy', 'spacious']}
          onChange={(v) => setTweak('density', v)}
        />
        <TweakSection label="Accent" />
        <TweakColor
          label="Brand color"
          value={ACCENT_SWATCHES[['blue', 'emerald', 'amber'].indexOf(t.accent)] || ACCENT_SWATCHES[0]}
          options={ACCENT_SWATCHES}
          onChange={(v) => {
            const idx = ACCENT_SWATCHES.indexOf(v);
            const next = ['blue', 'emerald', 'amber'][idx >= 0 ? idx : 0];
            setTweak('accent', next);
          }}
        />
      </TweaksPanel>
    </div>
  );
}

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);
