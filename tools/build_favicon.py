"""Build bold tab-friendly favicons for Southdev Home Depot."""
from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageChops, ImageDraw

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "assets" / "uploads" / "favicon"
LOGO = ROOT / "assets" / "uploads" / "images" / "logosouthdev2.png"

BRAND_ORANGE = (249, 115, 22, 255)
FILL = 0.86


def content_bbox(image: Image.Image) -> tuple[int, int, int, int]:
    alpha = image.split()[3]
    background = Image.new("L", image.size, 0)
    bbox = ImageChops.lighter(alpha, background).getbbox()
    if bbox:
        return bbox

    rgb = image.convert("RGB")
    black_bg = Image.new("RGB", image.size, (0, 0, 0))
    return ImageChops.difference(rgb, black_bg).getbbox() or (0, 0, image.width, image.height)


def load_roof_mark() -> Image.Image:
    logo = Image.open(LOGO).convert("RGBA")
    top = int(logo.height * 0.38)
    section = logo.crop((0, 0, logo.width, top))
    bbox = content_bbox(section)
    return section.crop(bbox)


def circular_mask(size: int) -> Image.Image:
    mask = Image.new("L", (size, size), 0)
    draw = ImageDraw.Draw(mask)
    draw.ellipse((0, 0, size - 1, size - 1), fill=255)
    return mask


def roof_to_orange_icon(source: Image.Image, size: int, fill: float = FILL) -> Image.Image:
    """White roof mark on solid brand orange inside a circle."""
    pixels = source.load()
    mark = Image.new("RGBA", source.size, (0, 0, 0, 0))
    mark_pixels = mark.load()

    for y in range(source.height):
        for x in range(source.width):
            r, g, b, a = pixels[x, y]
            if a < 20:
                continue
            if r < 45 and g < 45 and b < 45:
                continue
            mark_pixels[x, y] = (255, 255, 255, 255)

    w, h = mark.size
    side = max(w, h)
    canvas = max(1, int(round(side / fill)))
    square = Image.new("RGBA", (canvas, canvas), BRAND_ORANGE)
    square.paste(mark, ((canvas - w) // 2, (canvas - h) // 2), mark)

    master = square.resize((1024, 1024), Image.Resampling.LANCZOS)
    icon = master.resize((size, size), Image.Resampling.LANCZOS)

    output = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    output.paste(icon, (0, 0), circular_mask(size))
    return output


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    roof = load_roof_mark()

    outputs = {
        "favicon-16.png": 16,
        "favicon-32.png": 32,
        "favicon-48.png": 48,
        "favicon.png": 32,
        "apple-touch-icon.png": 180,
    }

    icons = {name: roof_to_orange_icon(roof, px) for name, px in outputs.items()}
    icons["favicon-16.png"] = roof_to_orange_icon(roof, 16, fill=0.94)

    for name, image in icons.items():
        image.save(OUT / name, optimize=True)
        print(f"Wrote {name} ({image.size[0]}x{image.size[1]})")

    icons["favicon-32.png"].save(
        OUT / "favicon.ico",
        format="ICO",
        sizes=[(16, 16), (32, 32), (48, 48)],
    )
    print("Wrote favicon.ico")


if __name__ == "__main__":
    main()
