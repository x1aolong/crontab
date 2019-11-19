<?php	

	sleep(30);
	$file = fopen('/Users/felix/wow.txt', 'a');
	// $file = fopen('/crontab_test_files/4.txt', 'a');
	
	fwrite($file, date( 'Y-m-d H:i:s', time() ) . "\n");
	fclose($file);

	