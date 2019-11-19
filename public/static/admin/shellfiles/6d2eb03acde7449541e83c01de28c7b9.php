<?php	
	$file = fopen('/Users/felix/wow1.txt', 'a');
	// $file = fopen('/crontab_test_files/4.txt', 'a');
	fwrite($file, date( 'Y-m-d H:i:s', time() ) . "\n");
	fclose($file);

	