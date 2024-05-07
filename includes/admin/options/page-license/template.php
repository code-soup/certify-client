<?php

namespace CodeSoup\CertifyClient\Admin;

// Exit if accessed directly
defined( 'WPINC' ) || die; ?>

<div class="wrap">
	<h1>Certify Client</h1>
	<form action="options.php" method="post">
		<?php settings_fields('certify_client'); ?>
		<?php do_settings_sections('certify_client'); ?>
		<?php submit_button('Activate'); ?>
	</form>
</div>