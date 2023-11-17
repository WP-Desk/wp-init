<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookDriver;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\HookDriver\Legacy\HooksRegistry;
use WPDesk\Init\Plugin;

class LegacyHookableDriver implements HookDriver {

	public function register_hooks( ReadableConfig $config, array $bundles, ContainerInterface $container ): void {
		$info = $this->as_plugin_info($container->get(Plugin::class), $config);

		$class_name = $info->get_class_name();

		$p = new $class_name($info);
		$reg = HooksRegistry::instance();
		$reg->inject_container($container);
		add_action('plugins_loaded', [$p, 'init'], -45);
	}

	private function as_plugin_info( Plugin $plugin, ReadableConfig $config ): \WPDesk_Plugin_Info {
		$plugin_info = new \WPDesk_Plugin_Info();
		$plugin_info->set_plugin_file_name($plugin->get_basename());
		$plugin_info->set_plugin_name($plugin->get_name());
		$plugin_info->set_plugin_dir($plugin->get_path());
		$plugin_info->set_version( $plugin->get_version() );
		$plugin_info->set_text_domain( $plugin->get_slug() );
		$plugin_info->set_plugin_url($plugin->get_url());

		$plugin_info->set_class_name($config->get('plugin_class_name'));
		// 		$plugin_info->set_product_id( $this->product_id );
		// 		$plugin_info->set_plugin_shops( $this->plugin_shops );

		return $plugin_info;
	}

}
