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
    	$begQtrDt = $curr_year.'-01-01';
		$endQtrDt = $curr_year.'-03-31';
    } elseif($n > 3 && $n <7){
    	$begQtrDt = $curr_year.'-04-01';
		$endQtrDt = $curr_year.'-06-30';
	} elseif($n >6 && $n < 10){
		$begQtrDt = $curr_year.'-07-01';
		$endQtrDt = $curr_year.'-09-30';
	} elseif($n >9){
		$begQtrDt = $curr_year.'-10-01';
		$endQtrDt = $curr_year.'-12-31';
    }
	
	//  Create current quarter beg and end date in the cheque format
	$begQtr = date($date_fmt,strtotime($begQtrDt));
	$endQtr = date($date_fmt,strtotime($endQtrDt));
	
	//  Create the date difference for the current quarter
	$dStart = new DateTime($begQtrDt);
	$dEnd  = new DateTime($endQtrDt);
	$dDiff = $dStart->diff($dEnd);
	$days = $dDiff->format('%a');

	
	$formattedArr = array();
	$filename = "DX-Investment Changes.csv";
	//  CSV to multidimensional array in php
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
	
	$count = 0;
	foreach ($formattedArr as $value) {
		if ($formattedArr[$count][0] == 'Name') {
			continue;
		}
		else {
			//  Single row for one unit holder
			if (!empty($formattedArr[$count][0]) and !empty($formattedArr[$count+1][0])) {
				$amtNumbs = $formattedArr[$count][9];           //  Total distributied amount
				$amtWords = toWords($amtNumbs);                 //  Total distributied amount in words
				$recipient = $formattedArr[$count][0];          //  The dividens receiver's name
				
				if (empty($formattedArr[$count][2])) {
					$distbBegDt = $begQtr;
				} else {
					$distbBegDt = date($date_fmt,strtotime($formattedArr[$count][2]));
				}
				$distbEndDt = $endQtr;
				$distbPeriod = $distbBegDt." to ".$distbEndDt;     //  Current distribution period
				
				$intQtr = $formattedArr[$count][3] * $days;        //  Quarterly Interest
				//  Create comments
				$comment_1 = "1. ".$intQtr."/u*".$formattedArr[$count][6]."u=".$formattedArr[$count][7];
				if (!empty($formattedArr[$count][5])) {
					$comment_2 = "2. ".$formattedArr[$count][5].$formattedArr[$count][8];
				} else {$comment_2 = NULL;}
				
			}
		}
	}
	echo "";
?>