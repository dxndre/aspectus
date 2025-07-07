<?php
// This file is generated. Do not modify it manually.
return array(
	'aspectus-roi-calculator' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aspectus/roi-calculator',
		'version' => '1.0.0',
		'title' => 'Aspectus ROI Calculator',
		'category' => 'widgets',
		'icon' => 'chart-line',
		'description' => 'A reusable, dynamic ROI calculator block for WordPress with editable inputs and live results.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'aspectus-roi-calculator',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'percentageIncrease' => array(
				'type' => 'number',
				'default' => 0
			),
			'hours' => array(
				'type' => 'number',
				'default' => 0
			),
			'days' => array(
				'type' => 'number',
				'default' => 0
			),
			'weeksPerYear' => array(
				'type' => 'number',
				'default' => 0
			),
			'unitsPerHour' => array(
				'type' => 'number',
				'default' => 0
			),
			'profitPerUnit' => array(
				'type' => 'number',
				'default' => 0
			),
			'backgroundColour' => array(
				'type' => 'string',
				'default' => '#fff'
			),
			'sliderColour' => array(
				'type' => 'string',
				'default' => '#0073aa'
			),
			'textColour' => array(
				'type' => 'string',
				'default' => '#000'
			)
		)
	)
);
