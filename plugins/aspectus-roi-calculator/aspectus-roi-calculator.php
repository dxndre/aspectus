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
		'percentage_increase' => array(
		'value' => get_field( 'percentage_increase', $post->ID ),
		'min'   => get_field( 'percentage_increase_min', $post->ID ),
		'max'   => get_field( 'percentage_increase_max', $post->ID ),
		'label' => get_field( 'percentage_increase_label', $post->ID ),
		'placeholder' => get_field( 'percentage_increase_placeholder', $post->ID ),
		),

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
	];

	wp_localize_script(
		'aspectus-roi-calculator-editor-script',
		'aspectusACFData',
		$acf_data
	);
}

add_action( 'enqueue_block_editor_assets', 'aspectus_roi_calculator_localize_acf_data' );


// Rendering the Calculator on the frontend

function aspectus_roi_calculator_render_callback( $attributes, $content, $block ) {
	$post_id = get_the_ID();
	$acf_fields = get_fields( $post_id );

	// Helper to merge ACF with attributes
	$val = fn($key, $default = 0) =>
		$acf_fields[$key] ?? ($attributes[$key] ?? $default);

	$percentage = $val('percentage_increase');
	$hours = $val('hours');
	$days = $val('days');
	$weeks = $val('weeks_per_year');
	$units = $val('units_per_hour');
	$profit = is_array($acf_fields['profit_per_unit'] ?? null)
		? floatval($acf_fields['profit_per_unit']['value'] ?? 0)
		: floatval($val('profit_per_unit'));

	$bg = $val('background_colour', '#fff');
	$text = $val('text_colour', '#000');
	$slider = $val('slider_colour', '#0073aa');

	ob_start();
	?>
	<div id="calculator" class="aspectus-roi-calculator" style="background-color: <?= esc_attr($bg) ?>; color: <?= esc_attr($text) ?>; padding: 1rem;">
		<label for="percentage_increase_slider">Percentage Increase:</label>
		<input
			type="range"
			id="percentage_increase_slider"
			min="0"
			max="100"
			value="<?= esc_attr($percentage) ?>"
			style="width: 100%; accent-color: <?= esc_attr($slider) ?>"
		/>
		<div class="output-value">
			<span id="percentage_increase_value"><?= esc_html($percentage) ?></span>%
		</div>

		<div style="margin-top: 1rem;">
			<p><strong>Hours:</strong> <?= esc_html($hours) ?></p>
			<p><strong>Days:</strong> <?= esc_html($days) ?></p>
			<p><strong>Weeks/Year:</strong> <?= esc_html($weeks) ?></p>
			<p><strong>Units/Hour:</strong> <?= esc_html($units) ?></p>
			<p><strong>Profit/Unit:</strong> £<?= esc_html($profit) ?></p>
		</div>

		<div id="roi_result" style="margin-top: 1rem; font-weight: bold;">
			Estimated ROI increase: <?= esc_html($percentage) ?>%
		</div>
	</div>

	<script>
		(() => {
			const slider = document.getElementById('percentage_increase_slider');
			const output = document.getElementById('percentage_increase_value');
			const roiResult = document.getElementById('roi_result');

			slider.addEventListener('input', (e) => {
				const val = e.target.value;
				output.textContent = val;
				roiResult.textContent = `Estimated ROI increase: ${val}%`;
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
