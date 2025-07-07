import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextControl,
	ColorPicker,
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();

	// Destructure props with defaults
	const {
		percentageIncrease = 0,
		hours = 0,
		days = 0,
		weeksPerYear = 0,
		unitsPerHour = 0,
		profitPerUnit = 0,
		backgroundColour = '#fff',
		sliderColour = '#0073aa',
		textColour = '#000',
	} = attributes;

	// Set up ACF data fallback
	const acf = window.aspectusACFData || {};

	// Helper: fallback with labels/placeholders
	const getLabel = (key, defaultText) =>
		acf.labels?.[key] || __(defaultText, 'aspectus-roi-calculator');

	const getPlaceholder = (key, defaultText) =>
		acf.labels?.[`${key}_placeholder`] || defaultText;

	// Render
	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Calculator Settings')}>
					<RangeControl
						label={getLabel('percentage_increase', 'Percentage Increase')}
						value={percentageIncrease}
						onChange={(val) => setAttributes({ percentageIncrease: val })}
						min={0}
						max={100}
					/>
					<RangeControl
						label={getLabel('hours', 'Hours')}
						value={hours}
						onChange={(val) => setAttributes({ hours: val })}
						min={0}
						max={24}
					/>
					<RangeControl
						label={getLabel('days', 'Days')}
						value={days}
						onChange={(val) => setAttributes({ days: val })}
						min={0}
						max={7}
					/>
					<RangeControl
						label={getLabel('weeks_per_year', 'Weeks Per Year')}
						value={weeksPerYear}
						onChange={(val) => setAttributes({ weeksPerYear: val })}
						min={0}
						max={52}
					/>
					<TextControl
						label={getLabel('units_per_hour', 'Units Per Hour')}
						placeholder={getPlaceholder('units_per_hour', 'e.g. 5')}
						value={unitsPerHour}
						onChange={(val) => setAttributes({ unitsPerHour: parseFloat(val) || 0 })}
					/>
					<TextControl
						label={getLabel('profit_per_unit', 'Profit Per Unit (£)')}
						placeholder={getPlaceholder('profit_per_unit', 'e.g. 12.50')}
						value={profitPerUnit}
						onChange={(val) => setAttributes({ profitPerUnit: parseFloat(val) || 0 })}
					/>
				</PanelBody>

				<PanelBody title={__('Appearance Settings')}>
					<p>{__('Background Colour', 'aspectus-roi-calculator')}</p>
					<ColorPicker
						color={backgroundColour}
						onChangeComplete={(color) => setAttributes({ backgroundColour: color.hex })}
					/>

					<p>{__('Slider Colour', 'aspectus-roi-calculator')}</p>
					<ColorPicker
						color={sliderColour}
						onChangeComplete={(color) => setAttributes({ sliderColour: color.hex })}
					/>

					<p>{__('Text Colour', 'aspectus-roi-calculator')}</p>
					<ColorPicker
						color={textColour}
						onChangeComplete={(color) => setAttributes({ textColour: color.hex })}
					/>
				</PanelBody>
			</InspectorControls>

			<div
				{...blockProps}
				style={{
					backgroundColor: backgroundColour,
					color: textColour,
					border: '1px solid #ccc',
					padding: '1rem',
					borderRadius: '0.5rem',
				}}
			>
				<h4>{__('ROI Calculator Preview', 'aspectus-roi-calculator')}</h4>
				<p>
					{getLabel('percentage_increase', 'Percentage Increase')}: {percentageIncrease}%
				</p>
				<p>
					{getLabel('hours', 'Hours')}: {hours}
				</p>
				<p>
					{getLabel('days', 'Days')}: {days}
				</p>
				<p>
					{getLabel('weeks_per_year', 'Weeks/Year')}: {weeksPerYear}
				</p>
				<p>
					{getLabel('units_per_hour', 'Units/Hour')}: {unitsPerHour}
				</p>
				<p>
					{getLabel('profit_per_unit', 'Profit/Unit')}: £{profitPerUnit}
				</p>
			</div>
		</>
	);
}
