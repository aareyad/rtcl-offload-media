<?php

namespace Rtcl\OffloadMedia\Clients;

abstract class AbstractClient {
	protected string $accessKey;
	protected string $secretKey;
	protected string $bucket;
	protected string $endpoint;
	protected string $domain;

	public function __construct( $config ) {
		$this->accessKey = $config['access_key'] ?? '';
		$this->secretKey = $config['secret_key'] ?? '';
		$this->bucket    = $config['bucket'] ?? '';
		$this->endpoint  = $config['endpoint'] ?? '';
		$this->domain    = $config['domain'] ?? '';
	}

	abstract public function upload( $filePath, $key );

	abstract public function head( $key );

	abstract public function delete( $key );

	abstract public function checkConnection();

	/**
	 * Get the URL for the given key
	 *
	 * @param $key
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