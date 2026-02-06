<?php

namespace Allignol\CacheBuster;

use Kirby\Filesystem\F;
use Kirby\Cms\App as Kirby;

class CacheBuster
{
	/**
	 * Available hash methods
	 */
	const HASH_METHODS = [
		'md5',
		'sha1',
		'sha256',
		'xxh3',
		'timestamp'
	];

	/**
	 * Allowed extensions for cache busting
	 */
	const ALLOWED_EXTENSIONS = ['css', 'js', 'woff', 'woff2', 'ttf', 'svg', 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];

	/**
	 * Kirby instance
	 */
	protected Kirby $kirby;

	/**
	 * Plugin options
	 */
	protected array $options;

	/**
	 * Constructor
	 */
	public function __construct(?Kirby $kirby = null)
	{
		$this->kirby = $kirby ?? kirby();
		$this->options = [
			'active' => $this->kirby->option('allignol.cache-buster.active', true),
			'method' => $this->kirby->option('allignol.cache-buster.method', 'xxh3'),
			'prefix' => $this->sanitizeAffix($this->kirby->option('allignol.cache-buster.prefix', '')),
			'suffix' => $this->sanitizeAffix($this->kirby->option('allignol.cache-buster.suffix', '')),
		];
	}

	/**
	 * Sanitizes a prefix or suffix (allows only a-z, A-Z, 0-9, - and _)
	 */
	protected function sanitizeAffix(string $affix): string
	{
		return preg_replace('/[^a-zA-Z0-9_-]/', '', $affix);
	}

	/**
	 * Normalizes a URL (relative or absolute) to a relative path
	 */
	protected function normalizeToRelativePath(string $url): string
	{
		$siteUrl = $this->kirby->url();
		
		// Strip the site URL from absolute URLs
		if (str_starts_with($url, $siteUrl)) {
			$url = substr($url, strlen($siteUrl));
		}
		
		return ltrim($url, '/');
	}

	/**
	 * Converts a URL (relative or absolute) to a file system path
	 */
	protected function urlToFilePath(string $url): string
	{
		$relativePath = $this->normalizeToRelativePath($url);
		return $this->kirby->roots()->index() . '/' . $relativePath;
	}

	/**
	 * Generates the hash for a file from a file system path
	 * 
	 * Returns null if the file doesn't exist or if its modification time cannot be determined.
	 * In such cases, the original URL will be returned unchanged.
	 */
	public function generateHash(string $file): ?string
	{
		if (!F::exists($file)) {
			return null;
		}

		$method = $this->options['method'];
		$timestamp = F::modified($file);

		// If modification time cannot be determined, return null (no cache busting)
		if (!$timestamp) {
			return null;
		}

		// Simple timestamp method
		if ($method === 'timestamp') {
			$hash = (string) $timestamp;
		}
		// Hash methods
		elseif (in_array($method, ['xxh3', 'md5', 'sha1', 'sha256'])) {
			$hash = hash($method, $timestamp);
		} else {
			return null; // Invalid method, return null
		}

		return $hash;
	}

	/**
	 * Generates the hash for a file from a URL (relative or absolute)
	 * 
	 * Returns null if the file doesn't exist or if its modification time cannot be determined.
	 */
	public function getHashFromUrl(string $url): ?string
	{
		$file = $this->urlToFilePath($url);
		return $this->generateHash($file);
	}

	/**
	 * Generates the cache-busted URL for a file
	 */
	public function bust(string $url): string
	{
		// If the plugin is not active, return the original URL
		if (!$this->options['active']) {
			return $url;
		}

		// Detect if URL is absolute
		$siteUrl = $this->kirby->url();
		$isAbsolute = str_starts_with($url, $siteUrl);
		
		// Normalize to relative path
		$relativePath = $this->normalizeToRelativePath($url);

		// Automatically detect the extension
		$extension = F::extension($relativePath);

		// Check if the extension is allowed
		if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
			return $url;
		}

		// Build the full file path
		$file = $this->kirby->roots()->index() . '/' . $relativePath;

		// Generate the hash
		$hash = $this->generateHash($file);

		if (!$hash) {
			return $url;
		}

		// Apply prefix and suffix
		$hash = $this->options['prefix'] . $hash . $this->options['suffix'];

		// Build the new URL
		$dirname = dirname($relativePath);
		$filename = F::name($relativePath);

		if ($dirname === '.') {
			$bustedUrl = $filename . '.' . $hash . '.' . $extension;
		} else {
			$bustedUrl = $dirname . '/' . $filename . '.' . $hash . '.' . $extension;
		}

		// Return absolute URL if input was absolute
		return $isAbsolute ? $siteUrl . '/' . $bustedUrl : $bustedUrl;
	}

	/**
	 * Returns the current options
	 */
	public function options(): array
	{
		return $this->options;
	}

	/**
	 * Checks if the plugin is active
	 */
	public function isActive(): bool
	{
		return $this->options['active'];
	}

	/**
	 * Returns the hash method used
	 */
	public function method(): string
	{
		return $this->options['method'];
	}
}
