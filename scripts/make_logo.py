"""Generate 120x120 PNG logo for Google OAuth consent screen.
Blue rounded square + white "SA" monogram."""
from PIL import Image, ImageDraw, ImageFont
import os, sys

SIZE = 120
BG = (26, 115, 232)   # #1a73e8 (SEOKRU blue)
FG = (255, 255, 255)
RADIUS = 22

# Supersample 4x for crisp anti-aliasing, then downscale
SS = 4
W = SIZE * SS

img = Image.new('RGBA', (W, W), (0, 0, 0, 0))
d = ImageDraw.Draw(img)

# Rounded rect background
d.rounded_rectangle([(0, 0), (W - 1, W - 1)], radius=RADIUS * SS, fill=BG)

# Try to find a bold font
font = None
for path in [
    'C:/Windows/Fonts/arialbd.ttf',
    'C:/Windows/Fonts/segoeuib.ttf',
    'C:/Windows/Fonts/calibrib.ttf',
    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
]:
    if os.path.exists(path):
        font = ImageFont.truetype(path, 60 * SS)
        break
if font is None:
    font = ImageFont.load_default()

text = 'SA'
# Measure
bbox = d.textbbox((0, 0), text, font=font)
tw, th = bbox[2] - bbox[0], bbox[3] - bbox[1]
x = (W - tw) // 2 - bbox[0]
y = (W - th) // 2 - bbox[1] - int(4 * SS)  # nudge up a hair
d.text((x, y), text, font=font, fill=FG)

# Downscale
final = img.resize((SIZE, SIZE), Image.LANCZOS)
# Flatten to RGB for standard PNG (Google accepts either, but RGB is safer)
bg = Image.new('RGB', (SIZE, SIZE), BG)
bg.paste(final, (0, 0), final)

out = os.path.join(os.path.dirname(__file__), '..', 'public', 'logo-120.png')
os.makedirs(os.path.dirname(out), exist_ok=True)
bg.save(out, 'PNG', optimize=True)
print(f'wrote {out}')
print(f'size: {os.path.getsize(out)} bytes')
