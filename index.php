<?php
use Kirby\Filesystem\F;

Kirby::plugin('allignol/kirby-cache-buster', [
	'components' => [
		'css' => function ($kirby, $url) {
			$file = $kirby->roots()->index() . '/' . $url;
			if (!$timestamp = F::modified($file)) {
				return $url;
			}
			return dirname($url) . '/' . F::name($url) . '.' . hash('xxh3', $timestamp) . '.css';
		},
		'js' => function ($kirby, $url) {
			$file = $kirby->roots()->index() . '/' . $url;
			if (!$timestamp = F::modified($file)) {
				return $url;
			}
			return dirname($url) . '/' . F::name($url) . '.' . hash('xxh3', $timestamp) . '.js';
		}
	]
]);
