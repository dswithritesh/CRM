<?php
/**
 * Runtime constants that require WordPress context (upload paths, etc.).
 *
 * @package AyurCRM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AyurCRM_Constants
 *
 * Defines upload-path constants and ensures directory structure exists.
 * Must be called on the `init` hook so that `wp_upload_dir()` is available.
 */
class AyurCRM_Constants {

	/**
	 * Define upload-related constants and create the required directories.
	 *
	 * @return void
	 */
	public static function define_upload_constants(): void {
		$upload = wp_upload_dir();

		if ( ! defined( 'AYURCRM_UPLOAD_DIR' ) ) {
			define( 'AYURCRM_UPLOAD_DIR', trailingslashit( $upload['basedir'] ) . 'ayurcrm/' );
		}

		if ( ! defined( 'AYURCRM_UPLOAD_URL' ) ) {
			define( 'AYURCRM_UPLOAD_URL', trailingslashit( $upload['baseurl'] ) . 'ayurcrm/' );
		}

		self::create_directory_structure();
	}

	/**
	 * Create the required upload directory structure and security files.
	 *
	 * @return void
	 */
	private static function create_directory_structure(): void {
		if ( ! defined( 'AYURCRM_UPLOAD_DIR' ) ) {
			return;
		}

		$root_dir    = AYURCRM_UPLOAD_DIR;
		$sub_dirs    = array(
			$root_dir . 'imports/',
			$root_dir . 'exports/',
			$root_dir . 'temp/',
		);

		// Create root directory.
		if ( ! wp_mkdir_p( $root_dir ) ) {
			return;
		}

		// Create .htaccess in root to deny PHP execution and directory listing.
		self::write_htaccess( $root_dir );

		// Create index.php silence file in root.
		self::write_index_silence( $root_dir );

		// Create sub-directories and place silence files.
		foreach ( $sub_dirs as $dir ) {
			if ( wp_mkdir_p( $dir ) ) {
				self::write_index_silence( $dir );
			}
		}
	}

	/**
	 * Write a .htaccess file that denies PHP execution and directory listing.
	 *
	 * @param string $dir Absolute path to directory (with trailing slash).
	 * @return void
	 */
	private static function write_htaccess( string $dir ): void {
		$htaccess = $dir . '.htaccess';

		if ( file_exists( $htaccess ) ) {
			return;
		}

		$content  = "# AyurCRM — deny direct access\n";
		$content .= "Options -Indexes\n";
		$content .= "<Files *.php>\n";
		$content .= "    deny from all\n";
		$content .= "</Files>\n";

		self::safe_write_file( $htaccess, $content );
	}

	/**
	 * Write an empty silence index.php file to prevent directory browsing.
	 *
	 * @param string $dir Absolute path to directory (with trailing slash).
	 * @return void
	 */
	private static function write_index_silence( string $dir ): void {
		$index = $dir . 'index.php';

		if ( file_exists( $index ) ) {
			return;
		}

		self::safe_write_file( $index, "<?php\n// Silence is golden.\n" );
	}

	/**
	 * Write a file using WP_Filesystem when available, falling back to
	 * file_put_contents only when the filesystem abstraction is unavailable.
	 *
	 * @param string $path    Full path to the target file.
	 * @param string $content File content to write.
	 * @return void
	 */
	private static function safe_write_file( string $path, string $content ): void {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ( ! empty( $wp_filesystem ) ) {
			$wp_filesystem->put_contents( $path, $content, FS_CHMOD_FILE );
		} else {
			// Last-resort fallback — no direct WP_Filesystem available.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $path, $content );
		}
	}
}
