<?php

namespace App\Support;

use Imagick;

class ImageSupport
{
	protected static ?array $supportedFormats = null;

	public static function supportedFormats(): array
	{
		if (self::$supportedFormats !== null) {
			return self::$supportedFormats;
		}

		$formats = ['jpg', 'jpeg', 'png', 'gif'];

		if (class_exists(Imagick::class)) {
			if (Imagick::queryFormats('WEBP')) {
				$formats[] = 'webp';
			}
			if (Imagick::queryFormats('AVIF')) {
				$formats[] = 'avif';
			}
		}

		return self::$supportedFormats = $formats;
	}

	/**
	 * Modern formats offered next to the JPEG/PNG original, best first,
	 * limited to what this server's Imagick can write.
	 *
	 * @return array<int, string>
	 */
	public static function modernFormats(): array
	{
		return array_values(array_filter(['avif', 'webp'], fn (string $format) => self::supports($format)));
	}

	public static function supports(string $format): bool
	{
		return in_array(strtolower($format), self::supportedFormats(), true);
	}
}
