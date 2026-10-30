# Cedra Parts — WordPress & WooCommerce Theme (Option 2)

Custom clean-industrial WordPress & WooCommerce catalog theme crafted for **Cedra Parts Inc.** — Canada's dedicated power generation aftermarket parts specialist (`shop.cedraparts.com` / `stg.shop.cedraparts.com`).

## Key Features

- **Dark Navy Utility Bar & Sticky Header**:
  - `36px` `#0B1C2C` utility bar with tagline, `sales@cedraparts.com`, `+1 (289) 337-2541`, and `#support` Emergency Support jump link.
  - `72px` sticky `#FFFFFF` header (`1px solid #E3E3DF` border) with Cedra Parts logo, `#7024AB`-underlined active nav state, quick search jump, My Account link, slide-over Mini-Cart trigger with live item badge, and `Get a Quote` CTA.
- **4-Slide Hero Carousel (`#top`)**:
  1. **Power Gen Exclusive (`#0B1C2C`)**: Primary catalog CTA + `DSE8610 MKII` featured product card.
  2. **Part Cross-Reference (`#F3EDF9`)**: Inline OEM part number cross-reference search form + 3-step cross-reference workflow.
  3. **Volume Pricing (`#F5F5F3`)**: Custom PM kits & bulk orders CTA + 3-card product showcase (`DSE9476-01`, `DSE7320 MKII`, `DSE A109`).
  4. **Authorized Canadian Distributor (`#FFFFFF`)**: `Shop by brand` trigger + 6 distributor logo tiles (`MaxiForce`, `Dayco`, `KMP Brand`, `SLP`, `IPD`, `Fleetguard`).
  - Adaptive dark/light bottom bar indicators, `01 / 04` counter, circular Prev/Next controls, 7-second autoplay (pauses on hover/focus, respects `prefers-reduced-motion`), and touch swipe support.
- **Tabbed Visual Browser (`#shop`)**:
  - Switch between **Categories** (10 visual tiles with live catalog product photography and part counts) and **Brands** (WooCommerce `product_brand` wordmark tiles + 6 Authorized Distributor logo tiles + `Other brands` cross-reference CTA tile).
- **Live Multi-Facet AJAX Catalog (`#results`)**:
  - Instant search by OEM part number (`_sku`), model, or keyword (`q`), plus sorting (`Featured`, `Price: low to high`, `Price: high to low`, `Part number A–Z`).
  - Removable `#F3EDF9` active filter chips + `Clear all`.
  - Sticky `252px` left sidebar (converting into a slide-in right drawer with scrim and sticky `Show X results` bar at `<= 1024px`) with collapsible `<details>` facets for **Category** (multi-checkbox), **Brand** (multi-checkbox), **Price** (`Any price`, `Under $250`, `$250 – $500`, `$500 – $1,000`, `$1,000 and up`), and **DC voltage** (`12 V` / `24 V` toggle buttons).
  - 4-column `.pcard` product grid with AJAX Add-to-Cart and numbered pagination (`Page 1 of 264`).
- **3-Column Services Strip (`#quote` / `#support`) & 4-Column Dark Navy Footer (`#contact`)**.
- **Full WooCommerce Inner Pages**: Harmonized Single Product (PDP), Slide-Over Mini-Cart Drawer, Cart, Checkout, My Account, and 404 templates.

## Theme Structure

```text
.
├── style.css
├── functions.php
├── header.php
├── footer.php
├── index.php
├── page.php
├── 404.php
├── screenshot.png
├── inc/
│   ├── customizer.php
│   ├── catalog-query.php
│   ├── svg-illustrations.php
│   └── template-tags.php
├── template-parts/
│   ├── catalog-view.php
│   └── help-band.php
├── woocommerce/
│   ├── archive-product.php
│   └── single-product.php
└── assets/
    ├── css/
    │   ├── main.css
    │   └── woocommerce-pages.css
    ├── js/
    │   └── main.js
    └── images/
```

## Customization

Navigate to **Appearance → Customize** in WordPress Admin:
- **Cedra Theme & Catalog Options**: Configure primary accent color (`#7024AB`), hero carousel autoplay, technical vector illustration fallback for missing product photos, and trailing `(SKU)` title cleanup.
- **Cedra Contact, Links & Socials**: Configure utility bar tagline, email, phone, corporate URLs, Beamsville address, business hours, and social links.
