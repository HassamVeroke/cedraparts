# Cedra Parts — Custom WordPress & WooCommerce Theme

Custom frosted-glass WooCommerce catalog and e-commerce theme built for **Cedra Parts Inc.** (`shop.cedraparts.com`), Canada's dedicated aftermarket parts specialist for the Power Generation sector.

## Design System & Highlights

- **Frosted Glassmorphism Aesthetic (Option 2)**: Layered translucent surfaces (`.glass`, `.glass-strong`, `.glass-dark`) over `#F3F2F7` light lavender-grey canvas with ambient radial purple orbs, `#0B1C2C` deep navy ink, and `#7024AB` electric purple accents.
- **Editorial Typography**: `Geist` (UI & display headings), `Geist Mono` (part numbers, SKUs, counts, badges), and `Instrument Serif` (italic display accents).
- **Sticky Floating Glass Pill Navbar**: Brand logo, pill navigation links, My Account shortcut, live cart counter `(0)` triggering a slide-over frosted-glass Mini-Cart drawer, and `Get a Quote ↗` CTA.
- **Interactive Catalog Hero**:
  - 60fps cursor-tracking radial purple spotlight glow (respects `prefers-reduced-motion` and Customizer toggle).
  - Rotating circular seal (`POWER GEN EXCLUSIVE · BEAMSVILLE, ONTARIO ·`).
  - Frosted-glass pill search bar with **SKU / OEM part number + keyword search** and dynamic **Brand (`product_brand` / `pa_brand`)** filter dropdown.
  - Infinite pause-on-hover marquee of **Authorized Canadian Distributor** brand logos (`MaxiForce`, `Dayco`, `KMP Brand`, `SLP`, `IPD`, `Fleetguard`).
- **Instant AJAX Category Rail, Sort & Load More**:
  - Sticky frosted-glass category filter rail populated dynamically from live WooCommerce `product_cat` terms with counts.
  - Instant AJAX filtering, sorting (`Featured`, `Price: low to high`, `Price: high to low`, `Name: A to Z`), and progressive `Load more parts` pagination with progress bar and URL `history.pushState` sync.
- **Smart Artwork Fallback**: Displays real high-resolution product photos seamlessly blended into each card's lavender-white stage, and automatically renders category-matched technical vector illustrations (`Controller`, `Battery Charger`, `Voltage Regulator`, `Communications`, `Filtration`, `Belts`, `Gaskets`, `Engine Parts`) whenever a part uses a generic "image not available" placeholder.
- **Cohesive Full-Store Templates**:
  - Single Product (`woocommerce/single-product.php`) with gallery switcher, SKU/brand/stock badges, quantity stepper, AJAX Add-to-Cart, specifications card, and related parts grid.
  - Slide-Over Frosted-Glass Mini-Cart Drawer (`header.php` + `inc/template-tags.php`).
  - Styled WooCommerce Cart, Checkout (Blocks & classic), My Account portal, and 404 page.
  - Rounded `#0B1C2C` Bento Footer with live client- & server-side **Eastern Time (`America/Toronto`) `Open now / Closed now`** indicator, giant masked watermark, and `Back to top ↑` action.

## Theme Directory Structure

```text
cedraparts/
├── style.css                        # WordPress & WooCommerce theme header metadata
├── screenshot.png                   # 1200x900 Appearance > Themes preview
├── functions.php                    # Theme setup, WooCommerce support, asset loader
├── header.php                       # Ambient orbs, top utility bar, floating glass navbar, mini-cart drawer
├── footer.php                       # Rounded dark bento footer, live ET hours, watermark
├── index.php                        # Main template router & fallback
├── page.php                         # Cart, Checkout, My Account & content pages
├── search.php                       # Catalog search router
├── 404.php                          # Part cross-reference 404 page
├── inc/
│   ├── customizer.php               # WP Customizer controls (accent color, contact, links, hero copy)
│   ├── catalog-query.php            # SKU-inclusive product search & AJAX catalog/cart endpoints
│   ├── svg-illustrations.php        # Category-aware technical vector illustrations
│   └── template-tags.php            # Product card, mini-cart drawer & navigation renderers
├── template-parts/
│   ├── catalog-view.php             # Full Option 2 catalog hero, search, marquee, filter rail & grid
│   └── help-band.php                # Cross-Reference, Volume Pricing & Emergency Support band
├── woocommerce/
│   ├── archive-product.php          # Shop & product archive template
│   ├── taxonomy-product_cat.php     # Category archive template
│   ├── taxonomy-product_brand.php   # Brand archive template
│   └── single-product.php           # Single product detail template
└── assets/
    ├── css/
    │   ├── main.css                 # Core Option 2 frosted-glass design system
    │   └── woocommerce-pages.css    # Single product, Cart, Checkout, My Account & 404 styles
    ├── js/
    │   └── main.js                  # Vanilla JS spotlight, ET clock, AJAX catalog & mini-cart drawer
    └── images/                      # Cedra Parts & Authorized Distributor WebP logos
```

## Customization (`Appearance → Customize`)

All key settings can be updated without editing code under **Appearance → Customize**:
1. **Cedra Glass Design & Brand**: Accent color (`#7024AB`), hero cursor spotlight toggle, vector technical artwork fallback toggle, and card title trailing `(SKU)` cleanup.
2. **Cedra Contact, Links & Hours**: Top bar tagline, sales email, phone number, corporate URLs (`Home`, `About Us`, `Contact`, `Get a Quote`), Beamsville warehouse address, and ET business hours.
3. **Cedra Catalog Hero Content**: Eyebrow badge, hero headline & italic serif line, rotating seal ring text, description copy, and distributor marquee label.

