import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

import './editor.scss';

export default function Edit() {
	const blockProps = useBlockProps();

	// Load default value from ACF
	const [percentageIncrease, setPercentageIncrease] = useState('');

	useEffect(() => {
		if (typeof window !== 'undefined' && window.aspectusACFData) {
			const acfDefault = window.aspectusACFData?.percentage_increase;
			if (acfDefault) {
				setPercentageIncrease(acfDefault);
			}
		}
	}, []);

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Calculator Settings', 'aspectus-roi-calculator')}>
					<TextControl
						label={__('Percentage Increase', 'aspectus-roi-calculator')}
						value={percentageIncrease}
						onChange={(val) => setPercentageIncrease(val)}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<p>{__('ROI Calculator Block', 'aspectus-roi-calculator')}</p>
				<p>
					{__('Percentage Increase:', 'aspectus-roi-calculator')} {percentageIncrease}
				</p>
			</div>
		</>
	);
}
