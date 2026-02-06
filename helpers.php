<?php

use Allignol\CacheBuster\CacheBuster;

if (!function_exists('cacheBust')) {
	/**
	 * Generates a cache-busted URL for a file
	 * 
	 * @param string $url File URL
	 * @return string Cache-busted URL
	 */
	function cacheBust(string $url): string
	{
		$cacheBuster = new CacheBuster();
		return $cacheBuster->bust($url);
	}
}

if (!function_exists('cacheBustHash')) {
	/**
	 * Generates only the hash for a file
	 * 
	 * @param string $url File URL (relative or absolute)
	 * @return string|null Generated hash or null if the file doesn't exist
	 */
	function cacheBustHash(string $url): ?string
	{
		$cacheBuster = new CacheBuster();
		return $cacheBuster->getHashFromUrl($url);
	}
}
