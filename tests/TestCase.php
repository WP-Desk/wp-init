<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use Symfony\Component\Filesystem\Filesystem;

class TestCase extends \PHPUnit\Framework\TestCase {
	/** @var string|null */
	private ?string $prevCwd = null;

	/** @var array */
	private array $tempPluginDirs = [];

	public static function getUniqueTmpDirectory( string $suffix = '' ): string {
		$attempts = 5;
		$root     = sys_get_temp_dir();

		do {
			$unique = $root . DIRECTORY_SEPARATOR . uniqid( 'wpdesk-test-' . random_int( 1000,
						9000 ), false ) . DIRECTORY_SEPARATOR . $suffix;

			if ( ! file_exists( $unique ) && mkdir( $unique, 0777, true ) ) {
				return realpath( $unique );
			}
		} while ( -- $attempts );

		throw new \RuntimeException( 'Failed to create a unique temporary directory.' );
	}

	public function initTempPlugin( string $plugin = 'simple-plugin' ): string {
		$dir = self::getUniqueTmpDirectory( $plugin );

		$this->tempPluginDirs[] = dirname( $dir );

		$this->prevCwd = self::getCwd();
		$fs            = new Filesystem();
		$fs->mirror( __DIR__ . "/Fixtures/$plugin", $dir );

		chdir( $dir );

		return $dir;
	}

	protected function tearDown(): void {
		parent::tearDown();
		if ( null !== $this->prevCwd ) {
			chdir( $this->prevCwd );
			$this->prevCwd = null;
		}
		$fs = new Filesystem();
		$fs->remove( $this->tempPluginDirs );
	}

	public static function getCwd( bool $allowEmpty = false ): string {
		$cwd = getcwd();

		// fallback to realpath('') just in case this works but odds are it would break as well if we are in a case where getcwd fails
		if ( false === $cwd ) {
			$cwd = realpath( '' );
		}

		// crappy state, assume '' and hopefully relative paths allow things to continue
		if ( false === $cwd ) {
			if ( $allowEmpty ) {
				return '';
			}

			throw new \RuntimeException( 'Could not determine the current working directory' );
		}

		return $cwd;
	}


}