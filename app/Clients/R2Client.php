<?php

namespace Rtcl\OffloadMedia\Clients;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class R2Client extends AbstractClient {
	private object $client;

	public function __construct( $config ) {
		parent::__construct( $config );

		$this->client = new S3Client( array(
			'version'     => 'latest',
			'region'      => 'auto',
			'endpoint'    => $this->endpoint,
			'credentials' => array(
				'key'    => $this->accessKey,
				'secret' => $this->secretKey,
			),
		) );
	}

	/**
	 * Upload file to R2
	 *
	 * @param $filePath
	 * @param $key
	 *
	 * @return false|string
	 */
	public function upload( $filePath, $key ): bool|string {
		try {
			$this->client->putObject( array(
				'Bucket' => $this->bucket,
				'Key'    => $key,
				'Body'   => file_get_contents( $filePath ),
				'ACL'    => 'public-read',
			) );

			return $this->getUrl( $key );
		} catch ( AwsException $e ) {
			error_log( 'R2 Upload Error: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * Delete a file from R2
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function delete( $key ): bool {
		try {
			$this->client->deleteObject( array(
				'Bucket' => $this->bucket,
				'Key'    => $key,
			) );

			return true;
		} catch ( AwsException $e ) {
			error_log( 'R2 Delete Error: ' . $e->getMessage() );

			return false;
		}
	}

	/**
	 * Check connection to R2 bucket
	 *
	 * @return true|string Returns true if OK, or error message if failed
	 */
	public function checkConnection(): bool|string {
		try {
			// Try listing 1 object to validate access
			$this->client->listObjectsV2( [
				'Bucket'  => $this->bucket,
				'MaxKeys' => 1,
			] );

			return true;
		} catch ( AwsException $e ) {
			error_log( 'R2 Connection Error: ' . $e->getMessage() );

			return $e->getMessage();
		}
	}
}