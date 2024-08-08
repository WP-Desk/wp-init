<?php
if ( ! ( PHP_VERSION_ID >= 70200 ) ) {
	add_action(
		'admin_notices',
		function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				__( 'The plugin cannot run on PHP versions older than 7.2. Please, contact your host and ask them to upgrade.', 'wp-init' )
			);
		}
	);

	return false;
}

return true;
