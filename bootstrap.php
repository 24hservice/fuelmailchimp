<?php

Autoloader::add_classes(array(
	'FuelMailChimp\\MailChimp'          => __DIR__.'/classes/mailchimp.php',
	'FuelMailChimp\\Lists'              => __DIR__.'/classes/lists.php',

	'FuelMailChimp\\Observer_Subscribe' => __DIR__.'/classes/observer/subscribe.php',
));
