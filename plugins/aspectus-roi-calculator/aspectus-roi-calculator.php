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
	if ( ! function_exists( 'get_field' ) || ! is_admin() ) {
		return;
	}

	global $post;
	if ( ! $post ) {
		return;
	}

	// Collect all the ACF field data you want
	$acf_data = array(
		'percentage_increase'     => get_field( 'percentage_increase', $post->ID ),
		'hours'                   => get_field( 'hours', $post->ID ),
		'days'                    => get_field( 'days', $post->ID ),
		'weeks_per_year'          => get_field( 'weeks_per_year', $post->ID ),
		'units_per_hour'          => get_field( 'units_per_hour', $post->ID ),
		'profit_per_unit'         => get_field( 'profit_per_unit', $post->ID ), // Group field

		'labels' => array(
			'percentage_increase'           => get_field( 'percentage_increase_label', $post->ID ),
			'percentage_increase_placeholder' => get_field( 'percentage_increase_placeholder', $post->ID ),
			'hours'                         => get_field( 'hours_label', $post->ID ),
			'hours_placeholder'             => get_field( 'hours_placeholder', $post->ID ),
			'days'                          => get_field( 'days_label', $post->ID ),
			'days_placeholder'              => get_field( 'days_placeholder', $post->ID ),
			'weeks_per_year'                => get_field( 'weeks_per_year_label', $post->ID ),
			'weeks_per_year_placeholder'    => get_field( 'weeks_per_year_placeholder', $post->ID ),
			'units_per_hour'                => get_field( 'units_per_hour_label', $post->ID ),
			'units_per_hour_placeholder'    => get_field( 'units_per_hour_placeholder', $post->ID ),
			'profit_per_unit'               => get_field( 'profit_per_unit_label', $post->ID ),
			'profit_per_unit_placeholder'   => get_field( 'profit_per_unit_placeholder', $post->ID ),
		),

		'appearance' => array(
			'background_colour' => get_field( 'background_colour', $post->ID ),
			'slider_colour'     => get_field( 'slider_colour', $post->ID ),
			'text_colour'       => get_field( 'text_colour', $post->ID ),
		),
	);

	// This handle MUST match the one generated in your /build/index.asset.php
	wp_localize_script(
		'aspectus-roi-calculator-editor-script',
		'aspectusACFData',
		$acf_data
	);
}
add_action( 'enqueue_block_editor_assets', 'aspectus_roi_calculator_localize_acf_data' );


// Rendering the Calculator on the frontend

function aspectus_roi_calculator_render_callback( $attributes ) {
    $post_id = get_the_ID();
    $acf_fields = get_fields( $post_id );

    $percentage_increase = isset( $acf_fields['percentage_increase'] ) && $acf_fields['percentage_increase'] !== '' ? intval( $acf_fields['percentage_increase'] ) : 0;
    $hours = isset( $acf_fields['hours'] ) ? intval( $acf_fields['hours'] ) : 0;
    $days = isset( $acf_fields['days'] ) ? intval( $acf_fields['days'] ) : 0;
    $weeks_per_year = isset( $acf_fields['weeks_per_year'] ) ? intval( $acf_fields['weeks_per_year'] ) : 0;
    $units_per_hour = isset( $acf_fields['units_per_hour'] ) ? intval( $acf_fields['units_per_hour'] ) : 0;

    $profit_per_unit = isset( $acf_fields['profit_per_unit'] ) ? $acf_fields['profit_per_unit'] : [];
    $profit_per_unit_value = isset( $profit_per_unit['value'] ) ? floatval( $profit_per_unit['value'] ) : 0;

    $background_colour = isset( $acf_fields['background_colour'] ) ? $acf_fields['background_colour'] : '#fff';
    $slider_colour = isset( $acf_fields['slider_colour'] ) ? $acf_fields['slider_colour'] : '#0073aa';
    $text_colour = isset( $acf_fields['text_colour'] ) ? $acf_fields['text_colour'] : '#000';

    $percentage_label = isset( $acf_fields['percentage_increase_label'] ) ? $acf_fields['percentage_increase_label'] : 'Percentage Increase';

    ob_start();
    ?>
    <div id="calculator" class="aspectus-roi-calculator" style="background-color: <?php echo esc_attr( $background_colour ); ?>; color: <?php echo esc_attr( $text_colour ); ?>;">
        <label for="percentage_increase_slider"><?php echo esc_html( $percentage_label ); ?>:</label>
        <input
            type="range"
            id="percentage_increase_slider"
            min="0"
            max="100"
            value="<?php echo esc_attr( $percentage_increase ); ?>"
            style="width: 100%; accent-color: <?php echo esc_attr( $slider_colour ); ?>"
        />
        <div>
            <span id="percentage_increase_value"><?php echo esc_html( $percentage_increase ); ?></span>%
        </div>

        <div id="roi_result" style="margin-top: 1rem; font-weight: bold;">
            <?php
            echo sprintf(
                esc_html__( 'Estimated ROI increase: %d%%', 'aspectus-roi-calculator' ),
                $percentage_increase
            );
            ?>
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
