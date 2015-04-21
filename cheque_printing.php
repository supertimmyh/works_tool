<?php
    //include class
    require("Numbers/Words.php");
	require("fpdf17/fpdf.php");
		
    //phpinfo();
	
	$date_fmt = "M jS, Y";
	$chq_print_dt = "Date:      ".date($date_fmt);    // Set the cheque printing date
	
	$bounce_pecent = "1%";                            // Bounce percent as 1%

	//Create begging and end quarter date
	$n = (date('n') - 1);
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
	
	$days = daysDiff($begQtrDt, $endQtrDt);
	
	//  Create the date difference function for the current quarter
	function daysDiff ($begDt, $endDt) {
		$dStart = new DateTime($begDt);
		$dEnd  = new DateTime($endDt);
		$dDiff = $dStart->diff($dEnd);
		$numbsDays = $dDiff->format('%a');
		return $numbsDays;	
	}
	
	//   Create a functon for comments
	function cmtCreate ($arrValue) {
			global $intQtr, $bounce_pecent, $days, $endQtrDt;
			$_commenceDt = $arrValue[2];              //   Dividend commenct date             
			$_units = $arrValue[6];                   //   Total units per unit holder
			$_divAmt = $arrValue[7];                  //   Dividend amount
			$_bonus = $arrValue[5];                   //   Bonus indicator
			$_bonusAmt = $arrValue[8];                //   Bonus amount 
	
			//   Create divident period and amount detail function
			if (empty($_commenceDt)) {             //   Full quarter dividends
				$_divComment = "$".$intQtr."/u*".$_units."u=$".$_divAmt;
				} else {                          //   Partial period dividends
					$_divPeriod = daysDiff($_commenceDt, $endQtrDt);
					$_divComment = "$".$intQtr."/u*".$_units."u divided by " .$days." days *".$_divPeriod." days=$".$_divAmt;
				}
			
			//   Create bonus detail
			if (!empty($_bonus)) {
				$_bonusComment = $bounce_pecent."$".$_bonusAmt;
				$rsltArr = array($_divComment, $_bonusComment);
			} else {$rsltArr = array($_divComment);}
			
			return $rsltArr;
	}

	$formattedArr = array();
	$filename = "DX-Investment Changes.csv";
	//  CSV to multidimensional array in php
	if (($handle = fopen($filename, "r")) !== FALSE) {
    	$key = 0;    // Set the array key.
    	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
       		$count = count($data);  //  Get the total keys in row
        	//  Insert data to our array
        	for ($i=0; $i < $count; $i++) {
            	$formattedArr[$key][$i] = $data[$i];     //  Without count the head row
        	}
        	$key++;
    	}
    	$headArr = array_shift($formattedArr);
    	fclose($handle);    //  Close file handle
	}
		
	//  Check if the second row has now divident receiver's name, if not, term the progam and issue an error
	if (empty($formattedArr[0][0])) {
			exit("The first row of data file has blanked receiver's name. Please review the file");
		}
	
	$intQtr = $formattedArr[0][4] * $days;           //  Quarterly Interest
		
	$count = 0;
	foreach ($formattedArr as $value) {
		$commentArr = cmtCreate($value);
		$recipient = $value[0];                //   The dividens receiver's name
		
		if (!empty($recipient)) {
			$commenceDt = $value[2];               //   Dividend commenct date 
			//   If a unit holder's investment didn't start at the quarter beginning
			if (!empty($commenceDt)) {             
				$begQtr = date($date_fmt,strtotime($commenceDt));
				}
			$distbPeriod = $begQtr." to ".$endQtr;  //  Current distribution period
			
			$grandTot = $value[9];
			if (!empty($grandTot)) {
				$grandTot = round($grandTot,2);
				
				$grandTotWords = Numbers_Words::toCurrency($grandTot);
				$grandTotWords = strtoupper ($grandTotWords);
			
				//   Create a final array for each unit holder only one row of entry
				$intemdArr = array($grandTotWords, $grandTot, $recipient, $distbPeriod);
				${"chqArr".$count}= array_merge($intemdArr, $commentArr);
				$count++;
			} else {	
				//   Create and keep an array for each unit holder with more than one row of entry
				$intemdArr = array($recipient, $distbPeriod);
				${"chqArr".$count}= array_merge($intemdArr, $commentArr);
		 	} 
		} else {
			$grandTot = $value[9];
			if (!empty($grandTot)) {
				$grandTot = round($grandTot,2);
				
				$grandTotWords = Numbers_Words::toCurrency($grandTot);
				$grandTotWords = strtoupper ($grandTotWords);
			
				//   Create a final array for each unit holder with more than one row of entry
				$intemdArr2 = array($grandTotWords, $grandTot);
				${"chqArr".$count}= array_merge($intemdArr2, ${"chqArr".$count}, $commentArr);
				$count++;
			} else {	
				//   Create and keep an array for each unit holder with more than one row of entry
				${"chqArr".$count}= array_merge(${"chqArr".$count}, $commentArr);
		 	} 
		}
	}
						
			 /* 
			     If an unit holder has more than one row for one's dividend payout 
			     it will continue the reading to next line until the grand total is found
				 Otherwise report directly
			 */ 

	
	//   Create the dividend interest total adding and total amount comment
	for ($i=0; $i<=$count; $i++) {
		if (!empty(${"chqArr".$i})) {
			$total = ("$".${"chqArr".$i}[1]);
			$name = ${"chqArr".$i}[2];
			$totBreak = (count(${"chqArr".$i}) - 4);
			if ($totBreak == 1) {
				$totComments = array(("Total ".$total." paid to ".$name." on ".date($date_fmt)."."));
			} else {
				$totCalL = "1";
				$totCalR = "=".$total;
				for ($j=2; $j<=$totBreak; $j++) {
					$totCalL = $totCalL."+".$j;
				}
				$totCal = $totCalL.$totCalR;
				$totComments = array($totCal, ("Total ".$total." paid to ".$name." on ".date($date_fmt)."."));
			}
			${"chqArr".$i} = array_merge(${"chqArr".$i}, $totComments);
		}
	}
	
	//print_r($chqArr8);
	
	//   Create PDF file
	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->SetMargins(2.54, 2.54);
	$pdf->SetFont('Times','',11);
	$pdf->Cell(130,4,'');
	$pdf->Cell(40,4,'Date: Jan 6th, 2015');
	$pdf->Output('C:\Users\Tim\Documents\Aptana Studio 3 Workspace\works_tool\doc.pdf','F');
?> 