import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	TextControl,
	ColorPicker,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();

	// Destructure attributes
	const {
		percentageIncrease,
		hours,
		days,
		weeksPerYear,
		unitsPerHour,
		profitPerUnit,
		backgroundColour,
		sliderColour,
		textColour,
	} = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Calculator Settings')}>
					<RangeControl
						label={__('Percentage Increase')}
						value={percentageIncrease}
						onChange={(val) => setAttributes({ percentageIncrease: val })}
						min={0}
						max={100}
					/>
					<RangeControl
						label={__('Hours')}
						value={hours}
						onChange={(val) => setAttributes({ hours: val })}
						min={0}
						max={24}
					/>
					<RangeControl
						label={__('Days')}
						value={days}
						onChange={(val) => setAttributes({ days: val })}
						min={0}
						max={7}
					/>
					<RangeControl
						label={__('Weeks Per Year')}
						value={weeksPerYear}
						onChange={(val) => setAttributes({ weeksPerYear: val })}
						min={0}
						max={52}
					/>
					<TextControl
						label={__('Units Per Hour')}
						value={unitsPerHour}
						onChange={(val) =>
							setAttributes({ unitsPerHour: parseFloat(val) || 0 })
						}
					/>
					<TextControl
						label={__('Profit Per Unit (£)')}
						value={profitPerUnit}
						onChange={(val) =>
							setAttributes({ profitPerUnit: parseFloat(val) || 0 })
						}
					/>
				</PanelBody>

				<PanelBody title={__('Appearance Settings')} initialOpen={false}>
					<p>{__('Background Colour')}</p>
					<ColorPicker
						color={backgroundColour}
						onChange={(color) =>
							setAttributes({ backgroundColour: color.hex })
						}
						disableAlpha
					/>

					<p>{__('Slider Colour')}</p>
					<ColorPicker
					color={sliderColour}
					onChange={(color) => setAttributes({ sliderColour: color.hex })}
					/>

					<p>{__('Text Colour')}</p>
					<ColorPicker
						color={textColour}
						onChange={(color) =>
							setAttributes({ textColour: color.hex })
						}
						disableAlpha
					/>
				</PanelBody>
			</InspectorControls>

			<ServerSideRender
				block="aspectus/roi-calculator"
				attributes={{
					...attributes,
					__forceRefresh: Date.now(), // ✅ This forces live refresh
				}}
			/>

			<div
				{...blockProps}
				style={{
					backgroundColor: backgroundColour,
					color: textColour,
					border: '1px solid #ccc',
					padding: '1rem',
					marginTop: '1rem',
				}}
			>
				<p>
					<em>{__('This is a live server-rendered preview.')}</em>
				</p>
			</div>
		</>
	);
}
