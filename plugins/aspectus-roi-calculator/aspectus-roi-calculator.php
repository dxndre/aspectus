<?php
/**
 * Plugin Name:       Aspectus ROI Calculator
 * Description:       A reusable, dynamic ROI calculator Gutenberg block with editable inputs and live results.
 * Version:           1.0.0
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Author:            D’André Phillips
 * Author URI:        https://www.dxndre.co.uk
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aspectus-roi-calculator
 *
 * @package           aspectus-roi-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */

 // Initialising ROI Calculator Block
function aspectus_aspectus_roi_calculator_block_init() {
	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}

add_action( 'init', 'aspectus_aspectus_roi_calculator_block_init' );


// Localising ACF Field data for use in the CMS
function aspectus_roi_calculator_localize_acf_data() {
	if ( ! function_exists( 'get_field_object' ) || ! is_admin() ) return;

	global $post;
	if ( ! $post ) return;

	// Helper to get field data with fallback
	function get_field_data($key, $post_id) {
		$field = get_field_object($key, $post_id);
		return [
			'value' => $field['value'] ?? '',
			'label' => $field['label'] ?? '',
			'placeholder' => $field['placeholder'] ?? '',
			'min' => $field['min'] ?? 0,
			'max' => $field['max'] ?? 100,
		];
	}

	$acf_data = [
		'percentage_increase' => [
			'value' => get_field( 'percentage_increase', $post->ID ),
			'min'   => get_field( 'percentage_increase_min', $post->ID ),
			'max'   => get_field( 'percentage_increase_max', $post->ID ),
			'label' => get_field( 'percentage_increase_label', $post->ID ),
			'placeholder' => get_field( 'percentage_increase_placeholder', $post->ID ),
		],

		'hours'               => get_field_data('hours', $post->ID),
		'days'                => get_field_data('days', $post->ID),
		'weeks_per_year'      => get_field_data('weeks_per_year', $post->ID),
		'units_per_hour'      => get_field_data('units_per_hour', $post->ID),
		'profit_per_unit'     => get_field_data('profit_per_unit', $post->ID),

		// Appearance settings
		'appearance' => [
			'background_colour' => get_field('background_colour', $post->ID),
			'slider_colour'     => get_field('slider_colour', $post->ID),
			'text_colour'       => get_field('text_colour', $post->ID),
		],

		// New: Result labels
		'results_labels' => [
			'profit_per_year'       => get_field('profit_per_year_label', $post->ID) ?: 'Profit per year',
			'units_per_year'        => get_field('units_per_year_label', $post->ID) ?: 'Units per year',
			'hours_in_week'         => get_field('hours_in_week_label', $post->ID) ?: 'Hours in a Week 24/7',
			'extra_hours'           => get_field('extra_hours_label', $post->ID) ?: 'Extra Hours',
			'extra_units_per_week'  => get_field('extra_units_per_week_label', $post->ID) ?: 'Extra Units Per Week',
		],
	];

	wp_localize_script(
		'aspectus-roi-calculator-editor-script',
		'aspectusACFData',
		$acf_data
	);
}

add_action( 'enqueue_block_editor_assets', 'aspectus_roi_calculator_localize_acf_data' );


// Rendering the Calculator on the frontend

function aspectus_roi_calculator_render_callback( $attributes, $content = '', $block = null ) {
    $post_id = get_the_ID();

    // Helper function to get ACF field value or fallback to default value in field settings
    if ( ! function_exists( 'get_acf_value_with_default' ) ) {
        function get_acf_value_with_default( $field_name, $post_id, $fallback = '' ) {
            $value = get_field( $field_name, $post_id );
            if ( ! $value ) {
                $field_object = get_field_object( $field_name, $post_id );
                if ( $field_object && ! empty( $field_object['default_value'] ) ) {
                    $value = $field_object['default_value'];
                }
            }
            if ( ! $value ) {
                $value = $fallback;
            }
            return $value;
        }
    }

    $background_colour = get_acf_value_with_default( 'background_colour', $post_id, '#ffffff' );
    $slider_colour     = get_acf_value_with_default( 'slider_colour', $post_id, '#0073aa' );
    $text_colour       = get_acf_value_with_default( 'text_colour', $post_id, '#000000' );

    $percentage_increase = intval( $attributes['percentageIncrease'] ?? 0 );
    $hours              = intval( $attributes['hours'] ?? 0 );
    $days               = intval( $attributes['days'] ?? 0 );
    $weeks_per_year     = intval( $attributes['weeksPerYear'] ?? 0 );
    $units_per_hour     = intval( $attributes['unitsPerHour'] ?? 0 );
    $profit_per_unit_value = floatval( $attributes['profitPerUnit'] ?? 0 );

    // Default currency for dropdown selection
    $currency = 'gbp';

    // Labels
    $acf = $block->context['acf_data'] ?? []; // fallback if needed

    $labels = [
        'percentage_increase' => get_field('percentage_increase_label', $post_id),
        'hours'               => get_field('hours_label', $post_id),
        'days'                => get_field('days_label', $post_id),
        'weeks_per_year'      => get_field('weeks_per_year_label', $post_id),
        'units_per_hour'      => get_field('units_per_hour_label', $post_id),
        'profit_per_unit'     => get_field('profit_per_unit_label', $post_id),
        'profit_per_year'     => get_field('profit_per_year_label', $post_id),
    ];
    
    $placeholders = [
        'percentage_increase' => get_field('percentage_increase_placeholder', $post_id),
        'hours'               => get_field('hours_placeholder', $post_id),
        'days'                => get_field('days_placeholder', $post_id),
        'weeks_per_year'      => get_field('weeks_per_year_placeholder', $post_id),
        'units_per_hour'      => get_field('units_per_hour_placeholder', $post_id),
        'profit_per_unit'     => get_field('profit_per_unit_placeholder', $post_id),
    ];

    ob_start();
    ?>

	<div id="calculator" class="aspectus-roi-calculator" style="background-color: <?php echo esc_attr( $background_colour ); ?>; color: <?php echo esc_attr( $text_colour ); ?>;">
        <?php

        // Helper to output sliders
        if ( ! function_exists( 'slider_field' ) ) {
            function slider_field( $id, $label, $value, $min = 0, $max = 100, $slider_colour ) {
                ?>
                <div class="input-group">
                    <label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
                    <input
                        type="range"
                        id="<?php echo esc_attr( $id ); ?>"
                        min="<?php echo esc_attr( $min ); ?>"
                        max="<?php echo esc_attr( $max ); ?>"
                        value="<?php echo esc_attr( $value ); ?>"
                        style="width: 100%; accent-color: <?php echo esc_attr( $slider_colour ); ?>;"
                    />
                    <div class="output-value">
                        <span id="<?php echo esc_attr( $id ); ?>_value"><?php echo esc_html( $value ); ?></span>
                    </div>
                </div>
                <?php
            }
        }
        
        ?>

        <div class="inputs">
		<div class="input-group">
            <label for="percentage_increase_slider"><strong><?php echo esc_html($labels['percentage_increase']); ?></strong></label>
                <div class="slider" style="position: relative;">
                    <input
                    type="range"
                    id="percentage_increase_slider"
                    min="0"
                    max="100"
                    value="<?php echo esc_attr($percentage_increase); ?>"
                    placeholder="<?php echo esc_attr($placeholders['percentage_increase']); ?>"
                    style="width: 100%; accent-color: <?php echo esc_attr($slider_colour); ?>;"
                    />
                    <div class="slider-dot"></div> 
                    <div class="output-value">
                        <span id="percentage_increase_value"><?php echo esc_html( $percentage_increase ); ?></span>
                    </div>
                </div>
            </div>

            <div class="input-group">
                <label for="hours_slider"><strong><?php echo esc_html( $labels['hours'] ); ?></strong></label>
                <div class="slider" style="position: relative;">
                    <input
                        type="range"
                        id="hours_slider"
                        min="0"
                        max="24"
                        value="<?php echo esc_attr( $hours ); ?>"
                        style="width: 100%; accent-color: <?php echo esc_attr( $slider_colour ); ?>;"
                    />
                    <div class="slider-dot"></div> 
                    <div class="output-value">
                        <span id="hours_value"><?php echo esc_html( $hours ); ?></span>
                    </div>
                </div>
            </div>

            <div class="input-group">
                <label for="days_slider">
                    <strong><?php echo esc_html( $labels['days'] ?? __('Days', 'aspectus-roi-calculator') ); ?></strong>
                </label>
                <div class="slider" style="position: relative;">
                    <input
                        type="range"
                        id="days_slider"
                        min="0"
                        max="7"
                        value="<?php echo esc_attr( $days ); ?>"
                        placeholder="<?php echo esc_attr( $placeholders['days'] ?? '' ); ?>"
                        style="width: 100%; accent-color: <?php echo esc_attr( $slider_colour ); ?>;"
                    />
                    <div class="slider-dot"></div> 
                    <div class="output-value">
                        <span id="days_value"><?php echo esc_html( $days ); ?></span>
                    </div>
                </div>
            </div>

            <div class="input-group">
                <label for="weeks_per_year_slider">
                    <strong><?php echo esc_html( $labels['weeks_per_year'] ?? __('Weeks Per Year', 'aspectus-roi-calculator') ); ?></strong>
                </label>
                <div class="slider" style="position: relative;">
                    <input
                        type="range"
                        id="weeks_per_year_slider"
                        min="0"
                        max="52"
                        value="<?php echo esc_attr( $weeks_per_year ); ?>"
                        placeholder="<?php echo esc_attr( $placeholders['weeks_per_year'] ?? '' ); ?>"
                        style="width: 100%; accent-color: <?php echo esc_attr( $slider_colour ); ?>;"
                    />
                    <div class="slider-dot"></div> 
                    <div class="output-value">
                        <span id="weeks_per_year_value"><?php echo esc_html( $weeks_per_year ); ?></span>
                    </div>
                </div>
            </div>

            <div class="input-group">
                <label for="units_per_hour_input"><strong><?php echo esc_html($labels['units_per_hour']); ?></strong></label>
                <input
                type="number"
                id="units_per_hour_input"
                value="<?php echo esc_attr($units_per_hour); ?>"
                placeholder="<?php echo esc_attr($placeholders['units_per_hour']); ?>"
                style="width: 100%; margin-bottom: 0.5rem;"
                />
            </div>

            <div class="input-group">
                <label for="profit_per_unit_input">
                    <strong><?php echo esc_html( $labels['profit_per_unit'] ?? __('Profit Per Unit', 'aspectus-roi-calculator') ); ?></strong>
                </label>

                <div style="display: flex; gap: 0.5rem;">
                    <select
                        id="profit_per_unit_currency"
                        name="profit_per_unit_currency"
                        style="width: 40%;"
                    >
                        <?php
                        $currency_choices = [
                            'gbp' => '£ GBP',
                            'usd' => '$ USD',
                            'eur' => '€ EUR',
                            'cad' => '$ CAD',
                            'aud' => '$ AUD',
                            'jpy' => '¥ JPY',
                            'cny' => '¥ CNY',
                            'inr' => '₹ INR',
                            'chf' => 'CHF',
                            'zar' => 'R ZAR',
                        ];

                        foreach ( $currency_choices as $code => $label ) {
                            $selected = $currency === $code ? 'selected' : '';
                            echo "<option value='{$code}' {$selected}>{$label}</option>";
                        }
                        ?>
                    </select>

                    <input
                        type="number"
                        step="0.01"
                        id="profit_per_unit_input"
                        value="<?php echo esc_attr( $profit_per_unit_value ); ?>"
                        placeholder="<?php echo esc_attr( $placeholders['profit_per_unit'] ?? '' ); ?>"
                        style="width: 60%;"
                    />
                </div>
            </div>

            <hr>

            <div class="results-table">
                <div class="row">
                    <div class="cell">
                    <span class="results-label"><?php echo esc_html( $labels['profit_per_year'] ?? 'Profit per year' ); ?></span>
                        <span class="results-value" id="profit_per_year_value">£0.00</span>
                    </div>
                    <div class="cell">
                        <span class="results-label">Units per year</span>
                        <span class="results-value" id="units_per_year_value">0</span>
                    </div>
                </div>
                <div class="row">
                    <div class="cell">
                        <span class="results-label">Hours In a Week 24/7</span>
                        <span class="results-value" id="hours_in_week_value">0</span>
                    </div>
                    <div class="cell">
                        <span class="results-label">Extra Hours</span>
                        <span class="results-value" id="extra_hours_value">0</span>
                    </div>
                    <div class="cell">
                        <span class="results-label">Extra Units Per Week</span>
                        <span class="results-value" id="extra_units_per_week_value">0</span>
                    </div>
                </div>
            </div>
        </div>

		<style>
			#calculator .inputs .input-group label,
			#calculator .results-table .row .cell span.results-label,
			#calculator .results-table .row .cell span.results-value,
			#calculator .inputs .input-group input,
			#calculator .inputs .input-group select#profit_per_unit_currency {
				color: <?php echo esc_attr( $text_colour ); ?>;
			}

			#calculator input[type="range"] {
				background: white; /* base track color */
			}

			/* WebKit track with progress as gradient */
			#calculator input[type="range"]::-webkit-slider-runnable-track {
				height: 6px;
				border-radius: 5px;
				background: linear-gradient(
				to right,
				<?php echo esc_attr($slider_colour); ?> 0%,
				<?php echo esc_attr($slider_colour); ?> var(--percent),
				white var(--percent),
				white 100%
				);
			}

			/* WebKit thumb */
			#calculator input[type="range"]::-webkit-slider-thumb {
				background-color: <?php echo esc_attr($slider_colour); ?>;
			}

			/* Firefox progress */
			#calculator input[type="range"]::-moz-range-progress {
				background-color: <?php echo esc_attr($slider_colour); ?>;
			}

			/* Firefox thumb */
			#calculator input[type="range"]::-moz-range-thumb {
				background-color: <?php echo esc_attr($slider_colour); ?>;
			}

			#calculator input[type="range"]::before {
				background-color: <?php echo esc_attr( $slider_colour ); ?>;
				border-color: <?php echo esc_attr( $slider_colour ); ?>;
			}

			#calculator .slider-dot {
				background-color: <?php echo esc_attr( $slider_colour ); ?>;
			}
		</style>

    </div>

    <script>
		(() => {
			const getVal = id => Number(document.getElementById(id)?.value || 0);
			const setText = (id, val) => {
				const el = document.getElementById(id);
				if (el) el.textContent = val;
			};

			let exchangeRates = { gbp: 1.0 };

			const fetchRates = async () => {
				try {
					const res = await fetch('https://api.exchangerate.host/live?access_key=6b1c770b421134b4c417d984c8759a85&currencies=GBP,USD,EUR,CAD,AUD,JPY,CNY,INR,CHF,ZAR&source=GBP');
					const data = await res.json();

					if (data.success && data.quotes) {
						exchangeRates = Object.entries(data.quotes).reduce((acc, [key, rate]) => {
							const currencyCode = key.replace('GBP', '').toLowerCase(); // e.g., 'GBPUSD' -> 'usd'
							acc[currencyCode] = rate;
							return acc;
						}, { gbp: 1.0 });

						console.log('✅ Exchange rates loaded:', exchangeRates);
					} else {
						console.warn('⚠️ API responded with error or no data:', data);
					}
				} catch (err) {
					console.error('❌ Failed to fetch rates:', err);
					exchangeRates = { gbp: 1.0 };
				}
			};

			const updateROI = () => {
				const percentage = getVal('percentage_increase_slider');
				const hours = getVal('hours_slider');
				const days = getVal('days_slider');
				const weeks = getVal('weeks_per_year_slider');
				const unitsPerHour = getVal('units_per_hour_input');
				const profitPerUnit = getVal('profit_per_unit_input');

				const currencySelect = document.getElementById('profit_per_unit_currency');
				const selectedCurrency = currencySelect?.value?.toLowerCase() || 'gbp';

				const unitsPerYear = hours * days * weeks * unitsPerHour;
				const profitPerYearGBP = profitPerUnit * unitsPerYear / 100;

				const rate = exchangeRates[selectedCurrency] || 1;
				const convertedProfit = profitPerYearGBP * rate;

				console.log(`Selected currency: ${selectedCurrency}, rate: ${rate}`);
				console.log(`Profit per year in GBP: ${profitPerYearGBP}, converted profit: ${convertedProfit}`);

				const currencySymbols = {
					gbp: '£', usd: '$', eur: '€', cad: '$', aud: '$', jpy: '¥',
					cny: '¥', inr: '₹', chf: 'CHF', zar: 'R'
				};

				const currencySymbol = currencySymbols[selectedCurrency] || selectedCurrency.toUpperCase();

				// Output results
				setText('units_per_year_value', unitsPerYear.toLocaleString());
				setText('profit_per_year_value',
					currencySymbol + convertedProfit.toLocaleString(undefined, {
						minimumFractionDigits: 2,
						maximumFractionDigits: 2,
					})
				);

				const hoursInWeek = 24 * 7;
				const extraHours = (percentage / 100) * hoursInWeek;
				const extraUnitsPerWeek = unitsPerHour * extraHours;

				setText('hours_in_week_value', hoursInWeek.toFixed(2));
				setText('extra_hours_value', extraHours.toFixed(2));
				setText('extra_units_per_week_value', extraUnitsPerWeek.toLocaleString());
			};

			// New helper: update CSS --percent variable for slider progress
			const updateSliderPercent = (slider) => {
				if (!slider) return;
				const percent = (slider.value / slider.max) * 100;
				slider.style.setProperty('--percent', percent + '%');
			};

			// Position dot on slider
			function updateSliderDotPosition(slider) {
				if (!slider) return;
				const dot = slider.parentElement.querySelector('.slider-dot');
				if (!dot) return;

				const sliderWidth = slider.offsetWidth;
				const min = Number(slider.min) || 0;
				const max = Number(slider.max) || 100;
				const val = Number(slider.value);

				const percent = (val - min) / (max - min);

				// Calculate pixel position relative to slider container
				const dotX = percent * sliderWidth;

				// Clamp inside container
				const clampedX = Math.min(Math.max(dotX, 0), sliderWidth);

				// Set left in pixels (no calc with % for now, easier to debug)
				dot.style.left = clampedX + 'px';
			}

			const sliders = [
			{ id: 'percentage_increase_slider', output: 'percentage_increase_value', suffix: '%' },
			{ id: 'hours_slider', output: 'hours_value' },
			{ id: 'days_slider', output: 'days_value' },
			{ id: 'weeks_per_year_slider', output: 'weeks_per_year_value' },
			];

			sliders.forEach(({ id, output, suffix = '' }) => {
			const slider = document.getElementById(id);
			const value = document.getElementById(output);
			if (slider && value) {
				slider.addEventListener('input', () => {
				// Update output text
				value.textContent = slider.value + suffix;

				// Update slider progress (your existing code)
				updateSliderPercent(slider);

				// Update the dot position
				updateSliderDotPosition(slider);
				});

				// Initialize dot position on page load
				updateSliderDotPosition(slider);
			}
			});

			const attachListeners = () => {
				const inputs = [
					'percentage_increase_slider',
					'hours_slider',
					'days_slider',
					'weeks_per_year_slider',
					'units_per_hour_input',
					'profit_per_unit_input',
					'profit_per_unit_currency'
				];

				inputs.forEach(id => {
					const el = document.getElementById(id);
					if (el) {
						el.addEventListener('input', updateROI);
					}
				});

				sliders.forEach(({ id, output, suffix = '' }) => {
					const slider = document.getElementById(id);
					const value = document.getElementById(output);
					if (slider && value) {
						// Update output text AND slider progress on input
						slider.addEventListener('input', () => {
							value.textContent = slider.value + suffix;
							updateSliderPercent(slider);
						});

						// Initialize progress on page load
						updateSliderPercent(slider);
					}
				});
			};

			// Start
			fetchRates().then(() => {
				attachListeners();
				updateROI();
			});
		})();
	</script>

    <?php
    return ob_get_clean();
}


// Register block type with the render callback
register_block_type(
	__DIR__ . '/build/aspectus-roi-calculator',
	[
		'render_callback' => 'aspectus_roi_calculator_render_callback',
	]
);
