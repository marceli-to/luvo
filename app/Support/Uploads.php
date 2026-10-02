<?php

namespace App\Support;

/**
 * Uploaded images in storage/app/public/uploads.
 */
class Uploads
{
	/**
	 * Deletes an image upload and its rendered Glide variants. Each upload
	 * belongs to one image row (unique name), so this runs with the row.
	 */
	public static function deleteImage(string $name): void
	{
		if ($name === '' || $name !== basename($name)) {
			return;
		}

		Glide::server()->deleteCache('uploads/' . $name);

		$path = storage_path('app/public/uploads/' . $name);
		if (is_file($path)) {
			unlink($path);
		}
	}

	/**
	 * Deletes image rows with their uploads.
	 *
	 * @param iterable<\Illuminate\Database\Eloquent\Model> $images
	 */
	public static function deleteImages(iterable $images): void
	{
		foreach ($images as $image) {
			self::deleteImage($image->name);
			$image->delete();
		}
	}
}
