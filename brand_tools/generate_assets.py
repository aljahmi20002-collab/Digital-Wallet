#!/usr/bin/env python3
"""Generate DigitalWallet brand assets (PNG icons, favicon, login banner, preview)."""
import numpy as np
from PIL import Image, ImageDraw, ImageFilter, ImageFont

# ── Palette ────────────────────────────────────────────────────────────
SKY    = np.array([56, 189, 248], dtype=np.float64)   # #38BDF8
ROYAL  = np.array([37, 99, 235], dtype=np.float64)    # #2563EB
DEEP   = np.array([29, 78, 216], dtype=np.float64)    # #1D4ED8
NAVY   = np.array([11, 31, 92], dtype=np.float64)     # #0B1F5C
WHITE  = np.array([255, 255, 255], dtype=np.float64)
SLATE  = np.array([15, 23, 42], dtype=np.float64)     # #0F172A

def diag_gradient(w, h, stops):
    """Diagonal (top-left → bottom-right) gradient from color stops [(pos, rgb), ...]."""
    yy, xx = np.mgrid[0:h, 0:w]
    t = (xx + yy) / (w + h - 2)          # 0 → 1 along the diagonal
    grad = np.zeros((h, w, 3), dtype=np.float64)
    for i in range(len(stops) - 1):
        p0, c0 = stops[i]
        p1, c1 = stops[i + 1]
        mask = (t >= p0) & (t <= p1)
        f = np.clip((t - p0) / (p1 - p0), 0, 1)[mask]
        grad[mask] = c0[None, :] * (1 - f)[:, None] + c1[None, :] * f[:, None]
    grad[t < stops[0][0]] = stops[0][1]
    grad[t > stops[-1][0]] = stops[-1][1]
    return Image.fromarray(grad.astype(np.uint8), 'RGB')

def rounded_mask(w, h, box, radius):
    m = Image.new('L', (w, h), 0)
    ImageDraw.Draw(m).rounded_rectangle(box, radius=radius, fill=255)
    return m

def draw_mark(size, margin_ratio=0.08, shadow=True, square=False):
    """Draw the brand mark at `size` px. Tile occupies (1 - 2*margin_ratio) of canvas."""
    s = size * (1 - 2 * margin_ratio) / 56.0       # scale: 56-unit tile
    off = (size - 56 * s) / 2.0
    im = Image.new('RGBA', (size, size), (0, 0, 0, 0))

    # shadow
    if shadow:
        sh = Image.new('RGBA', (size, size), (0, 0, 0, 0))
        d = ImageDraw.Draw(sh)
        d.rounded_rectangle([off, off + 5 * s, off + 56 * s, off + 61 * s],
                            radius=16 * s, fill=(29, 78, 216, 90))
        sh = sh.filter(ImageFilter.GaussianBlur(5 * s))
        im.alpha_composite(sh)

    # gradient tile (clipped to rounded rect; square=True → full bleed)
    tile = diag_gradient(size, size, [(0.0, SKY), (0.48, ROYAL), (1.0, DEEP)]).convert('RGBA')
    radius = 0 if square else round(16 * s)
    box = [0, 0, size - 1, size - 1] if square else [off, off, off + 56 * s, off + 56 * s]
    mask = rounded_mask(size, size, box, radius)
    im.alpha_composite(Image.composite(tile, Image.new('RGBA', (size, size), (0, 0, 0, 0)), mask))

    # soft sheen (clipped to tile)
    sheen = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    ds = ImageDraw.Draw(sheen)
    ds.ellipse([off - 8 * s, off - 14 * s, off + 34 * s, off + 20 * s], fill=(255, 255, 255, 26))
    im.alpha_composite(Image.composite(sheen, Image.new('RGBA', (size, size), (0, 0, 0, 0)), mask))

    d = ImageDraw.Draw(im)
    # wallet body
    d.rounded_rectangle([16 * s + off, 30 * s + off, 48 * s + off, 44 * s + off],
                        radius=6 * s, fill=(255, 255, 255, 255))
    # flap slit (top arc, stroke with mid gradient tone)
    bbox = [11.18 * s + off, 22.5 * s + off, 52.82 * s + off, 64.15 * s + off]
    d.arc(bbox, start=219.8, end=320.2, fill=(37, 99, 235, 255), width=max(2, round(3 * s)))
    # clasp
    d.ellipse([32 * s + off - 2.6 * s, 22.4 * s + off - 2.6 * s,
               32 * s + off + 2.6 * s, 22.4 * s + off + 2.6 * s], fill=(255, 255, 255, 255))
    # coin in flight
    d.ellipse([45.5 * s + off - 4.6 * s, 15.5 * s + off - 4.6 * s,
               45.5 * s + off + 4.6 * s, 15.5 * s + off + 4.6 * s], fill=(255, 255, 255, 255))
    d.ellipse([45.5 * s + off - 1.8 * s, 15.5 * s + off - 1.8 * s,
               45.5 * s + off + 1.8 * s, 15.5 * s + off + 1.8 * s], fill=(37, 99, 235, 255))
    return im

# ── 1. PWA icons + apple touch icon ────────────────────────────────────
for path, size, square in [('public/pwa/icons/icon-512.png', 512, False),
                           ('public/pwa/icons/icon-192.png', 192, False),
                           ('public/pwa/icons/apple-touch-icon.png', 180, True)]:
    draw_mark(size, square=square).save(path)
    print('saved', path)

# ── 2. favicon.ico ─────────────────────────────────────────────────────
big = draw_mark(256)
big.save('public/favicon.ico', sizes=[(16, 16), (24, 24), (32, 32), (48, 48), (64, 64)])
print('saved public/favicon.ico')

# ── 3. login banner (welcome-vector.png) ───────────────────────────────
W, H = 1200, 900
bg = diag_gradient(W, H, [(0.0, SKY), (0.42, ROYAL), (1.0, DEEP)]).convert('RGBA')
art = bg.copy()
d = ImageDraw.Draw(art, 'RGBA')
# decorative translucent circles
for cx, cy, r, a in [(980, 130, 260, 26), (1150, 620, 330, 22), (60, 820, 240, 20),
                     (1030, 380, 120, 30), (240, 120, 90, 24)]:
    d.ellipse([cx - r, cy - r, cx + r, cy + r], fill=(255, 255, 255, a))
# speed lines
for x0, y0, ln in [(700, 200, 120), (760, 250, 90), (640, 300, 70)]:
    d.rounded_rectangle([x0, y0, x0 + ln, y0 + 10], radius=5, fill=(255, 255, 255, 70))
# centered mark with white ring glow
ms = 300
mark = draw_mark(ms, margin_ratio=0.0, shadow=True)
glow = Image.new('RGBA', (ms, ms), (0, 0, 0, 0))
ImageDraw.Draw(glow).ellipse([10, 10, ms - 10, ms - 10], outline=(255, 255, 255, 90), width=4)
mark = Image.alpha_composite(mark, glow)
art.alpha_composite(mark, ((W - ms) // 2, (H - ms) // 2 - 40))
# wordmark text
try:
    f = ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', 64)
    tw = d.textlength('DigitalWallet', font=f)
    d.text(((W - tw) / 2, (H - ms) // 2 + ms + 10), 'DigitalWallet', font=f,
           fill=(255, 255, 255, 255))
    f2 = ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', 26)
    tw2 = d.textlength('Secure digital payments', font=f2)
    d.text(((W - tw2) / 2, (H - ms) // 2 + ms + 84), 'Secure digital payments', font=f2,
           fill=(255, 255, 255, 170))
except Exception as e:
    print('text skipped:', e)
art.convert('RGB').save('public/img/welcome-vector.png', quality=95)
print('saved public/img/welcome-vector.png')

# ── 4. brand preview mockup ────────────────────────────────────────────
PW, PH = 1600, 1080
prev = Image.new('RGBA', (PW, PH), (244, 248, 255, 255))
dp = ImageDraw.Draw(prev, 'RGBA')
# soft background wash
wash = diag_gradient(PW, PH, [(0.0, WHITE), (1.0, np.array([226, 236, 255], dtype=np.float64))]).convert('RGBA')
prev = Image.alpha_composite(prev, wash)
dp = ImageDraw.Draw(prev, 'RGBA')

f_big = ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', 58)
f_mid = ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', 34)
f_sm  = ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', 26)

dp.text((PW // 2 - 460, 70), 'DigitalWallet — New Brand Identity', font=f_big, fill=(15, 23, 42, 255))
dp.text((PW // 2 - 460, 148), 'Blue financial identity · mark, lockup, palette', font=f_sm, fill=(100, 116, 139, 255))

# hero mark
hero = draw_mark(420)
prev.alpha_composite(hero, (PW // 2 - 210, 200))
dp.text((PW // 2 - 120, 640), 'DigitalWallet', font=f_mid, fill=(15, 23, 42, 255))
dp.text((PW // 2 - 210, 688), 'open wallet · coin in flight · momentum', font=f_sm, fill=(100, 116, 139, 255))

# palette swatches
swatches = [('#38BDF8', 'Sky'), ('#2563EB', 'Royal'), ('#1D4ED8', 'Deep'),
            ('#0B1F5C', 'Navy'), ('#F4F8FF', 'Cloud')]
x0 = PW // 2 - (len(swatches) * 190 - 10) // 2
for i, (hexc, name) in enumerate(swatches):
    x = x0 + i * 190
    col = tuple(int(hexc[j:j + 2], 16) for j in (1, 3, 5)) + (255,)
    dp.rounded_rectangle([x, 780, x + 170, 900], radius=22, fill=col,
                         outline=(203, 213, 225, 255), width=2)
    dp.text((x + 24, 912), name, font=f_sm, fill=(71, 85, 105, 255))
    dp.text((x + 24, 946), hexc, font=f_sm, fill=(148, 163, 184, 255))

# app icon + favicon samples
app_icon = draw_mark(160)
prev.alpha_composite(app_icon, (140, 240))
dp.text((128, 430), 'App icon', font=f_sm, fill=(100, 116, 139, 255))
fav = draw_mark(64)
prev.alpha_composite(fav, (PW - 220, 250))
dp.text((PW - 240, 340), 'Favicon', font=f_sm, fill=(100, 116, 139, 255))
wide = draw_mark(56)
prev.alpha_composite(wide, (PW - 260, 430))
dp.text((PW - 300, 500), 'Mark', font=f_sm, fill=(100, 116, 139, 255))

prev.convert('RGB').save('/home/user/branding-preview.png', quality=95)
print('saved /home/user/branding-preview.png')
