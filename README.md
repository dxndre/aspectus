# Aspectus ROI Calculator — Documentation

## 1. Setup & Installation

### Requirements

- WordPress 6.1 or later
- PHP 7.4 or later
- Advanced Custom Fields (ACF) plugin installed and active
- Block editor (Gutenberg) enabled

### Installation

1. Upload the **Aspectus ROI Calculator** plugin folder to `/wp-content/plugins/`.
2. Activate the plugin via the WordPress admin panel (Plugins > Installed Plugins).
3. Ensure ACF fields are configured for your posts or pages as needed.
4. Use the **Aspectus ROI Calculator** block inside the block editor to add the calculator anywhere.

## 2. Block Attributes & Customization

### Attributes

The block supports the following attributes, which can be customized through ACF fields or directly in the block editor:

| Attribute               | Description                                   | Default  |
|-------------------------|-----------------------------------------------|----------|
| `percentageIncrease`    | Percentage increase used for ROI calculation  | 0        |
| `hours`                | Number of hours per day                        | 0        |
| `days`                 | Number of days per week                        | 0        |
| `weeksPerYear`         | Number of working weeks per year               | 0        |
| `unitsPerHour`         | Units produced or handled per hour             | 0        |
| `profitPerUnit`        | Profit amount per unit                          | 0        |
| `profitPerUnitCurrency`| Currency of profit per unit                     | GBP (£)  |
| `backgroundColour`     | Background color of calculator                  | #ffffff |
| `sliderColour`         | Colour of slider track and dot                   | #0073aa |
| `textColour`           | Text colour for labels and inputs                | #000000 |

### Customization

- Colors (background, slider, text) can be set via ACF fields or overridden in the block editor.
- Currency dropdown allows selection among common currencies, with live exchange rate conversion.
- Labels and placeholders for inputs can be set in ACF to suit your business terminology.
- The calculator dynamically updates live results based on user inputs.

## 3. Dependencies & Prerequisites

- **Advanced Custom Fields (ACF):** Used to create and manage custom input fields that feed into the calculator.
- **Block Editor (Gutenberg):** The calculator is implemented as a reusable block.
- **External Exchange Rate API:** Uses [ExchangeRate.host](https://exchangerate.host) for live currency conversions (requires internet access).
- **PHP 7.4+:** For block registration and server-side rendering compatibility.
- **WordPress 6.1+:** For block API and editor features.

## 4. Troubleshooting

- If the slider dot or progress bar appears clipped, ensure no parent container has `overflow: hidden` that could clip it.
- Make sure your PHP environment meets minimum version requirements.
- If exchange rates fail to load, the calculator defaults to GBP (£).
- Customize colors via the block settings or ACF to match your theme styles.

## 5. Support & Contributions

- For bug reports or feature requests, please open an issue on the GitHub repository.
- Contributions and pull requests are welcome.
