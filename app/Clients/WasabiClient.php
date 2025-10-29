<?php

namespace Rtcl\OffloadMedia\Clients;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class WasabiClient extends AbstractClient {
	private object $client;

	private string $region;

	/**
	 * @param array $config CDN configuration.
	 */
	public function __construct( $config ) {
		parent::__construct( $config );

		$this->region = $config['region'] ?? 'us-east-1';

		// Wasabi S3-compatible client
		$this->client = new S3Client(
			[
				'version'                 => 'latest',
				'region'                  => $this->region,
				'endpoint'                => $this->domain, // e.g. https://s3.ap-southeast-1.wasabisys.com
				'use_path_style_endpoint' => true, // Required for Wasabi
				'credentials'             => [
					'key'    => $this->accessKey,
					'secret' => $this->secretKey,
				],
			]
		);
	}

	/**
	 * Upload file to Wasabi bucket
	 *
	 * @param string $filePath Local path to file.
	 * @param string $key      Key (path in bucket).
	 *
	 * @return string|false Public URL on success, false on failure
	 */
	public function upload( $filePath, $key ): bool|string {
		try {
			$this->client->putObject(
				[
					'Bucket' => $this->bucket,
					'Key'    => $key,
					'Body'   => file_get_contents( $filePath ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					'ACL'    => 'public-read', // Ensure the file is publicly accessible
				]
			);

			return $this->getUrl( $key );
		} catch ( AwsException $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Wasabi Upload Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Check if object exists
	 *
	 * @param string $key bucket key.
	 *
	 * @return bool
	 */
	public function head( $key ): bool {
		try {
			$this->client->headObject(
				[
					'Bucket' => $this->bucket,
					'Key'    => $key,
				]
			);
			return true;
		} catch ( AwsException $e ) {
			if ( $e->getAwsErrorCode() === 'NotFound' ) {
				return false;
			}
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Wasabi Head Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Delete file from Wasabi
	 *
	 * @param string $key bucket key.
	 *
	 * @return bool
	 */
	public function delete( $key ): bool {
		try {
			$this->client->deleteObject(
				[
					'Bucket' => $this->bucket,
					'Key'    => $key,
				]
			);
			return true;
		} catch ( AwsException $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Wasabi Delete Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Verify connection to Wasabi bucket
	 *
	 * @return bool|string True if OK, or error message if failed
	 */
	public function checkConnection(): bool|string {
		try {
			$this->client->listObjectsV2(
				[
					'Bucket'  => $this->bucket,
					'MaxKeys' => 1,
				]
			);
			return true;
		} catch ( AwsException $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Wasabi Connection Error: ' . $e->getMessage() );
			return $e->getMessage();
		}
	}
}
