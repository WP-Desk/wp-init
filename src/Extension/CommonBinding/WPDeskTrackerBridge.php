<?php

namespace WPDesk\Init\Extension\CommonBinding;

use WPDesk\Init\Binding\Hookable;
use WPDesk\Init\Plugin\Plugin;

class WPDeskTrackerBridge implements Hookable {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function hooks(): void {
		$tracker_factory = new \WPDesk_Tracker_Factory_Prefixed();
		$tracker_factory->create_tracker( $this->plugin->get_basename() );
	}
}
