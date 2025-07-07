import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ColorPicker } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { percentageIncrease = 0, backgroundColour = '#fff' } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Calculator Settings')}>
					<RangeControl
						label={__('Percentage Increase')}
						value={percentageIncrease}
						onChange={(value) => setAttributes({ percentageIncrease: value })}
						min={0}
						max={100}
					/>
				</PanelBody>
				<PanelBody title={__('Appearance Settings')}>
					<ColorPicker
						color={backgroundColour}
						onChangeComplete={(color) => setAttributes({ backgroundColour: color.hex })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...useBlockProps()} style={{ backgroundColor: backgroundColour, padding: '1rem' }}>
				<p>{__('Percentage Increase:', 'aspectus-roi-calculator')} {percentageIncrease}</p>
			</div>
		</>
	);
}
