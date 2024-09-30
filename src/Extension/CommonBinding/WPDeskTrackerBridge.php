<?php
declare(strict_types=1);

namespace WPDesk\Init\Extension\CommonBinding;

use Psr\Log\LoggerInterface;
use WPDesk\Init\Binding\Hookable;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Tracker\OptInOptOut;

class WPDeskTrackerBridge implements Hookable {

	private Plugin $plugin;

	private LoggerInterface $logger;

	private Configuration $config;

	public function __construct( Plugin $plugin, Configuration $config, LoggerInterface $logger ) {
		$this->plugin = $plugin;
		$this->config = $config;
		$this->logger = $logger;
	}

	public function hooks(): void {
		$tracker_factory = new \WPDesk_Tracker_Factory_Prefixed();
		$tracker_factory->create_tracker( $this->plugin->get_basename(), $this->logger );

		$shops      = $this->config->get( 'shops', [] );
		$shop_url   = $shops[ get_user_locale() ] ?? ( $shops['default'] ?? 'https://wpdesk.net' );
		$tracker_ui = new OptInOptOut(
			$this->plugin->get_file(),
			$this->plugin->get_slug(),
			$shop_url,
			$this->plugin->get_name()
		);
		$tracker_ui->create_objects();
		$tracker_ui->hooks();
	}
}
