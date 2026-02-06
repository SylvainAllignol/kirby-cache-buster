<?php

use Allignol\CacheBuster\CacheBuster;

@include_once __DIR__ . '/classes/CacheBuster.php';
@include_once __DIR__ . '/helpers.php';

Kirby::plugin('allignol/cache-buster', [
	'options' => [
		'active' => true,
		'method' => 'xxh3',
		'prefix' => '',
		'suffix' => '',
	],
	'components' => [
		'css' => function ($kirby, $url) {
			$cacheBuster = new CacheBuster($kirby);
			return $cacheBuster->bust($url);
		},
		'js' => function ($kirby, $url) {
			$cacheBuster = new CacheBuster($kirby);
			return $cacheBuster->bust($url);
		}
	],
	'fieldMethods' => [
		'cacheBust' => function ($field) {
			$cacheBuster = new CacheBuster();
			return $cacheBuster->bust($field->value());
		}
	]
]);
