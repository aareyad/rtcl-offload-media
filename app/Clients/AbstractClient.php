<?php

namespace Rtcl\OffloadMedia\Clients;

abstract class AbstractClient {
	protected string $accessKey;
	protected string $secretKey;
	protected string $bucket;
	protected string $endpoint;
	protected string $domain;

	/**
	 * @param array $config object configuration.
	 */
	public function __construct( $config ) {
		$this->accessKey = $config['access_key'] ?? '';
		$this->secretKey = $config['secret_key'] ?? '';
		$this->bucket    = $config['bucket'] ?? '';
		$this->endpoint  = $config['endpoint'] ?? '';
		$this->domain    = $config['domain'] ?? '';
	}

	/**
	 * Upload file to bucket
	 *
	 * @param string $filePath Local path to file.
	 * @param string $key      Key (path in bucket).
	 *
	 * @return string|false Public URL on success, false on failure
	 */
	abstract public function upload( $filePath, $key );

	/**
	 * Check if object exists
	 *
	 * @param string $key bucket key.
	 *
	 * @return bool
	 */
	abstract public function head( $key );

	/**
	 * Delete file from bucket
	 *
	 * @param string $key bucket key.
	 *
	 * @return bool
	 */
	abstract public function delete( $key );
	/**
	 * Verify connection to bucket
	 *
	 * @return bool|string True if OK, or error message if failed
	 */
	abstract public function checkConnection();

	/**
	 * Get the URL for the given key
	 *
	 * @param string $key bucket key.
	 *
	 * @return string
	 */
	public function getUrl( $key ): string {
		if ( ! empty( $this->domain ) ) {
			return rtrim( $this->domain, '/' ) . '/' . ltrim( $key, '/' );
		}

		return rtrim( $this->endpoint, '/' ) . '/' . rtrim( $this->bucket, '/' ) . '/' . ltrim( $key, '/' );
	}
}
