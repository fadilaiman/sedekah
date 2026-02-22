# PWA Icons

Place the following PNG files here for full PWA installability:

- `icon-192.png` — 192×192 px, used on Android home screen
- `icon-512.png` — 512×512 px, used on splash screen and app store listings

## Generating Icons

Use [Favicon.io](https://favicon.io/favicon-generator/) or run this one-liner
(requires ImageMagick):

```bash
convert -background "#355E3B" -fill white -font "DejaVu-Sans-Bold" \
  -gravity Center -size 512x512 label:"S" public/icons/icon-512.png

convert public/icons/icon-512.png -resize 192x192 public/icons/icon-192.png
```

Or with Node.js + sharp:

```bash
npm install --save-dev sharp
node -e "
  const sharp = require('sharp');
  const svg = Buffer.from('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"512\" height=\"512\"><rect width=\"512\" height=\"512\" fill=\"#355E3B\" rx=\"80\"/><text x=\"256\" y=\"320\" font-size=\"280\" font-family=\"sans-serif\" fill=\"white\" text-anchor=\"middle\">S</text></svg>');
  sharp(svg).resize(512, 512).png().toFile('public/icons/icon-512.png');
  sharp(svg).resize(192, 192).png().toFile('public/icons/icon-192.png');
"
```

These files are excluded from git (see `.gitignore`). Generate them during the
Docker build or deployment step.
