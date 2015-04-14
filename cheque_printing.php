<?php
    //include class
    include("Numbers/Words.php");
	
    phpinfo();
	
	$date_fmt = "M jS, Y";
	$chq_print_dt = date($date_fmt);    // Set the cheque printing date

	//Create begging and end quarter date
	$n = date('n');
	$curr_year = date('Y');
    if($n < 4){
    	$begQtr = date($date_fmt,strtotime($curr_year.'-01-01'));
		$endQtr = date($date_fmt,strtotime($curr_year.'-03-31'));
    } elseif($n > 3 && $n <7){
    	$begQtr = date($date_fmt,strtotime($curr_year.'-04-01'));
		$endQtr = date($date_fmt,strtotime($curr_year.'-06-30'));
	} elseif($n >6 && $n < 10){
		$begQtr = date($date_fmt,strtotime($curr_year.'-07-01'));
		$endQtr = date($date_fmt,strtotime($curr_year.'-09-30'));
	} elseif($n >9){
		$begQtr = date($date_fmt,strtotime($curr_year.'-10-01'));
		$endQtr = date($date_fmt,strtotime($curr_year.'-12-31'));
    }
	
	$formattedArr = array();
	$filename = "DX-Investment Changes.csv";
	
	if (($handle = fopen($filename, "r")) !== FALSE) {
    	$key = 0;    // Set the array key.
    	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
       		$count = count($data);  //  Get the total keys in row
        	//  Insert data to our array
        	for ($i=0; $i < $count; $i++) {
            	$formattedArr[$key+1][$i] = $data[$i];
        	}
        	$key++;
    	}
    	fclose($handle);    //  Close file handle
	}
	//  CSV to multidimensional array in php
	
	$count = 0;
	foreach ($formattedArr as $value) {
		if ($formattedArr[$count][0] == 'Name') {
			continue;
		}
		else {
			if (!empty($formattedArr[$count][0]) and !empty($formattedArr[$count+1][0])) {
				$amtNumbs = $formattedArr[$count][9];
				$amtWords = toWords($amtNumbs);
				$recipient = $formattedArr[$count][0];
				if (empty($formattedArr[$count][2])) {
					$distbBegDt = $begQtr;
				} else {
					$distbBegDt = date($date_fmt,strtotime($formattedArr[$count][2]));
				}
				$distbEndDt = $endQtr;
			}
		}
	}
	echo "";
?>