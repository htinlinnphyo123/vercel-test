<?php

namespace  App\Helpers;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

/**
 * @property S3Client $s3Client
 */
class Tebi
{
	CONST KEY = "sehoQ7W5bFzDSV21";
	CONST SECRET = "QLQy6DQJ0b3975YsVVZVVnwwnv9AEHIXih7FqDql";

	protected static $s3Client;

	public static function init()  : void
	{
		self::$s3Client = new S3Client([
			"credentials" => [
				"key" => self::KEY,
				"secret" => self::SECRET
			],
			"endpoint" => "https://s3.tebi.io",
			"region" => "de",
			"version" => "2006-03-01"
		]);
	}


	public static function upload($file,$path=null) : string
	{

		try {
			self::init();
			$fileName =  \App\Helpers\GeneralHelper::generateRandomString(20) . '.' . $file->extension();
			$result = self::$s3Client->putObject(array(
				'Bucket'=>'blood-donor',
				'Key' => $path . '/' .$fileName,
				'SourceFile' => $file,
				'ACL' => 'public-read',
			));
			$result_arr = $result->toArray();

			if(!empty($result_arr['ObjectURL'])) {
				return $result_arr['ObjectURL'];
			} else {
				return false;
			}
		} catch (s3Exception $e) {
			return false;
		}


	}



	public static function delete($fileLink) : bool
	{
		self::init();
		$urlParts = parse_url($fileLink);
		$bucketName = explode('.', $urlParts['host'])[0]; // Extracting bucket name
		$objectKey = ltrim($urlParts['path'], '/');

		self::$s3Client->deleteObject(array(
			'Bucket'=>$bucketName,
			'Key' => $objectKey,
		));
		return true;
	}

}


