import csv
from colorsys import rgb_to_hsv

def rgb_to_cmyk(r, g, b):
    """Convert RGB to CMYK."""
    if (r, g, b) == (0, 0, 0):
        return 0, 0, 0, 100  # Black

    c = 1 - r / 255
    m = 1 - g / 255
    y = 1 - b / 255

    min_cmy = min(c, m, y)
    c = (c - min_cmy) / (1 - min_cmy) * 100
    m = (m - min_cmy) / (1 - min_cmy) * 100
    y = (y - min_cmy) / (1 - min_cmy) * 100
    k = min_cmy * 100

    return round(c), round(m), round(y), round(k)

def generate_colors(num_colors):
    """Generate a list of unique colors in RGB, HEX, and CMYK formats."""
    colors = []
    step = 256 // (int(num_colors ** (1/3)) + 1)  # Distribute colors evenly

    for r in range(0, 256, step):
        for g in range(0, 256, step):
            for b in range(0, 256, step):
                hex_code = f"#{r:02X}{g:02X}{b:02X}"
                rgb_code = f"{r},{g},{b}"
                cmyk_code = ",".join(map(str, rgb_to_cmyk(r, g, b)))
                colors.append({
                    "name": f"Color {len(colors) + 1}",
                    "hex_code": hex_code,
                    "rgb_code": rgb_code,
                    "cmyk_code": cmyk_code
                })
                if len(colors) >= num_colors:
                    return colors
    return colors

def create_csv(filename, colors):
    """Create a CSV file with the generated colors."""
    with open(filename, mode="w", newline="") as file:
        writer = csv.DictWriter(file, fieldnames=["name", "hex_code", "rgb_code", "cmyk_code"])
        writer.writeheader()
        writer.writerows(colors)

if __name__ == "__main__":
    num_colors = 1000  # Number of colors to generate
    colors = generate_colors(num_colors)
    create_csv("colors.csv", colors)
    print(f"Generated {len(colors)} colors and saved to 'colors.csv'.")