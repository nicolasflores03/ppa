<?php
class filterClass{
/*-------START OF TABLE Filter-------*/
	/*Read Details from Table*/
	
    public function filterView($conn,$column,$listView,$filter)
    {
		$content = '';
		$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="Table">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
		$content .= "<th>".$fieldName."</th>";
		}
		$content .= "</tr>";
		
		if(empty($filter)){
			foreach($listView as $views){
				$content .= "<tr class='test'>";
					foreach($column as $fieldName){
						$content .= "<td width='5%'>".$views[$fieldName]."</td>";
					}
				$content .= "</tr>";
			}
		}else{
			foreach($listView as $views){
				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$value = strtoupper($value);
				$listValue = strtoupper($views["$field"]);
				$content .= "<tr class='test'>";
				if ($type == "eq"){
					if ($listValue == $value){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}
				}else if ($type == "co"){
					if (strpos($listValue,$value) !== false){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "sw"){
					if (0 === strpos($listValue, $value)){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "ew"){
					if (stripos(strrev($listValue), strrev($value)) === 0){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}
				$content .= "</tr>";
			}
		}
		$content .= '</table></div>';
		return $content;
	}
	
	//FILTER VIEW WITH ONCLICK EVENT
	public function filterViewURL($conn,$column,$listView,$filter,$code)
    {
		$content = '';
		//$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="TableView">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
			if ($fieldName != "ID"){//-->Remove ID from the table header
				//$content .= "<th>".$fieldName."</th>";
				if ($fieldName == "TOTAL COST" || $fieldName == "UNIT COST"){//-->Remove ID from the table header
					$content .= "<th>".$fieldName."(PHP)</th>";
				}else{
					$content .= "<th>".$fieldName."</th>";
				}
			}
		}
		$content .= "<th>Action</th>";
		$content .= "</tr>";
		$id = 0;
		$hasStats = 0;
		$stats = "";
		if(empty($filter)){
			foreach($listView as $views){
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
					foreach($column as $fieldName){
						if ($fieldName != "id"){
							$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
						}
						if ($fieldName == "status"){
							$hasStats = 1;
							$stats = $views[$fieldName];
						}
					}
				$stats = str_replace(" ","",$stats);
				if ($hasStats > 0){
					if ($stats == "Unfinish" || $stats == "Created"){
					$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
					}else{
					$content .= "<td></td>";
					}
				}else{
				$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
				}
				
				
				$content .= "</tr>";
			}
		}else{
			foreach($listView as $views){
				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
				if ($type == "eq"){
					if (strtolower($views["$field"]) == strtolower($value)){
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
											$stats = str_replace(" ","",$stats);
						if ($hasStats > 0){
							if ($stats == "Unfinish" || $stats == "Created"){
							$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
							}else{
							$content .= "<td></td>";
							}
						}else{
						$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
						}
					}
				}else if ($type == "co"){
					if (strpos(strtolower($views["$field"]),strtolower($value)) !== false){
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
											$stats = str_replace(" ","",$stats);
						if ($hasStats > 0){
							if ($stats == "Unfinish" || $stats == "Created"){
							$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
							}else{
							$content .= "<td></td>";
							}
						}else{
						$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
						}
					}				
				}else if ($type == "sw"){
					if (0 === strpos(strtolower($views["$field"]), strtolower($value))){
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
						$stats = str_replace(" ","",$stats);
						if ($hasStats > 0){
							if ($stats == "Unfinish" || $stats == "Created"){
							$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
							}else{
							$content .= "<td></td>";
							}
						}else{
						$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
						}
					}				
				}else if ($type == "ew"){
					if (stripos(strrev(strtolower($views["$field"])), strrev(strtolower($value))) === 0){
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
						$stats = str_replace(" ","",$stats);
						if ($hasStats > 0){
							if ($stats == "Unfinish" || $stats == "Created"){
							$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
							}else{
							$content .= "<td></td>";
							}
						}else{
						$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
						}
					}				
				}

				$content .= "</tr>";
			}
		}
		$content .= '</table></div>';
		return $content;
	}

	//FILTER VIEW WITH ONCLICK EVENT
	public function recordItemFilterView($conn,$column,$listView,$filter,$code)
    {
		$content = '';
		
		//$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="TableView">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
			if ($fieldName != "ID"){//-->Remove ID from the table header
				//$content .= "<th>".$fieldName."</th>";
				if ($fieldName == "TOTAL COST" || $fieldName == "UNIT COST"){//-->Remove ID from the table header
					$content .= "<th>".$fieldName."(PHP)</th>";
				}else if ($fieldName == "FOREIGN CURRENCY"){//-->Remove ID from the table header
					$content .= "<th>CURRENCY</th>";
				}else{
					$content .= "<th>".$fieldName."</th>";
				}
			}
		}
		$content .= "<th>Action</th>";
		$content .= "</tr>";
		$id = 0;
		$hasStats = 0;
		$stats = "";
		$grand_total = 0;
		$total_available = 0;
		$grand_jan = 0;
		$grand_feb = 0;
		$grand_mar = 0;
		$grand_apr = 0;
		$grand_may = 0;
		$grand_jun = 0;
		$grand_jul = 0;
		$grand_aug = 0;
		$grand_sep = 0;
		$grand_oct = 0;
		$grand_nov = 0;
		$grand_dec = 0;

		if(empty($filter)){
			foreach($listView as $views){
				$grand_total += floatval(str_replace(",", "", $views["total_cost"]));
				$total_available += $views["available"];
				$grand_jan += floatval(str_replace(",", "", $views["Jan"]));
				$grand_feb += floatval(str_replace(",", "", $views["Feb"]));
				$grand_mar += floatval(str_replace(",", "", $views["Mar"]));
				$grand_apr += floatval(str_replace(",", "", $views["Apr"]));
				$grand_may += floatval(str_replace(",", "", $views["may"]));
				$grand_jun += floatval(str_replace(",", "", $views["Jun"]));
				$grand_jul += floatval(str_replace(",", "", $views["Jul"]));
				$grand_aug += floatval(str_replace(",", "", $views["Aug"]));
				$grand_sep += floatval(str_replace(",", "", $views["Sept"]));
				$grand_oct += floatval(str_replace(",", "", $views["Oct"]));
				$grand_nov += floatval(str_replace(",", "", $views["Nov"]));
				$grand_dec += floatval(str_replace(",", "", $views["Dec"]));

				//  $_total_cost ;
				$id = $views[$code];

				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
					foreach($column as $fieldName){
						if ($fieldName != "id"){
							if(in_array($fieldName, array("available",'Jan','Feb','Mar','Apr','may','Jun','Jul','Aug','Sept','Oct','Nov','Dec')) ) {
								$content .= "<td class='$fieldName'>".number_format($views[$fieldName],2,".",",")."</td>";
							} else {
								$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
							}
						}
						if ($fieldName == "status"){
							$hasStats = 1;
							$stats = $views[$fieldName];
						}
					}
				$stats = str_replace(" ","",$stats);
				if ($hasStats > 0){
					if ($stats == "Unfinish" || $stats == "Created"){
					$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
					}else{
					$content .= "<td></td>";
					}
				}else{
				$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
				}
				
				
				$content .= "</tr>";
			}

		}else{
			
			foreach($listView as $views){
				$column_inserted = false;
				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
				if ($type == "eq"){
					if (strtolower($views["$field"]) == strtolower($value)){
						$column_inserted = true;
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								if($fieldName == "available") {
									$content .= "<td class='$fieldName'>".number_format($views[$fieldName],2,".",",")."</td>";
								} else {
									$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
								}
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
						$stats = str_replace(" ","",$stats);
						if ($hasStats > 0){
							if ($stats == "Unfinish" || $stats == "Created"){
							$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
							}else{
							$content .= "<td></td>";
							}
						}else{
						$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
						}
					}
				}else if ($type == "co"){
					if (strpos(strtolower($views["$field"]),strtolower($value)) !== false){
						$column_inserted = true;
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								if($fieldName == "available") {
									$content .= "<td class='$fieldName'>".number_format($views[$fieldName],2,".",",")."</td>";
								} else {
									$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
								}
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
											$stats = str_replace(" ","",$stats);
						if ($hasStats > 0){
							if ($stats == "Unfinish" || $stats == "Created"){
							$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
							}else{
							$content .= "<td></td>";
							}
						}else{
						$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
						}
					}				
				}else if ($type == "sw"){
					if (0 === strpos(strtolower($views["$field"]), strtolower($value))){
						$column_inserted = true;
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								if($fieldName == "available") {
									$content .= "<td class='$fieldName'>".number_format($views[$fieldName],2,".",",")."</td>";
								} else {
									$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
								}
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
						$stats = str_replace(" ","",$stats);
						if ($hasStats > 0){
							if ($stats == "Unfinish" || $stats == "Created"){
							$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
							}else{
							$content .= "<td></td>";
							}
						}else{
						$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
						}
					}				
				}else if ($type == "ew"){
					if (stripos(strrev(strtolower($views["$field"])), strrev(strtolower($value))) === 0){
						$column_inserted = true;
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								if($fieldName == "available") {
									$content .= "<td class='$fieldName'>".number_format($views[$fieldName],2,".",",")."</td>";
								} else {
									$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
								}
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
						$stats = str_replace(" ","",$stats);
						if ($hasStats > 0){
							if ($stats == "Unfinish" || $stats == "Created"){
							$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
							}else{
							$content .= "<td></td>";
							}
						}else{
						$content .= "<td><input type='button' class='deleteButton' onClick='deleteRecord($id)' value='X'></td>";
						}
					}				
				}

				if($column_inserted){
					$grand_total += floatval(str_replace(",", "", $views["total_cost"]));
					$total_available += $views["available"];
					$grand_jan += floatval(str_replace(",", "", $views["Jan"]));
					$grand_feb += floatval(str_replace(",", "", $views["Feb"]));
					$grand_mar += floatval(str_replace(",", "", $views["Mar"]));
					$grand_apr += floatval(str_replace(",", "", $views["Apr"]));
					$grand_may += floatval(str_replace(",", "", $views["may"]));
					$grand_jun += floatval(str_replace(",", "", $views["Jun"]));
					$grand_jul += floatval(str_replace(",", "", $views["Jul"]));
					$grand_aug += floatval(str_replace(",", "", $views["Aug"]));
					$grand_sep += floatval(str_replace(",", "", $views["Sept"]));
					$grand_oct += floatval(str_replace(",", "", $views["Oct"]));
					$grand_nov += floatval(str_replace(",", "", $views["Nov"]));
					$grand_dec += floatval(str_replace(",", "", $views["Dec"]));
				}

				$content .= "</tr>";
			}
		}
		
		$formatted_grand_total = number_format($grand_total,2,".",",");
		$content .= "<tr><td colspan='4' style='text-align: right'>Grand Total</td>";
		$content .= "<td><strong>$formatted_grand_total</strong></td>";
		$content .= "<td><strong>".number_format($total_available,2,".",",")."</td>";
		$content .= "<td><strong>".number_format($grand_jan,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_feb,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_mar,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_apr,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_may,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_jun,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_jul,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_aug,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_sep,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_oct,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_nov,2,".",",")."</strong></td>";
		$content .= "<td><strong>".number_format($grand_dec,2,".",",")."</strong></td>";
		$content .= "<td></td>";
		$content .= "</tr>";

		$content .= '</table></div>';
		return $content;
	}
	
	
	//FILTER VIEW WITH ONCLICK EVENT
	   public function filterViewURLXdelete2($conn,$column,$listView,$filter,$code)
    {
		$content = '';
		//$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="TableView">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
			if ($fieldName != "ID"){//-->Remove ID from the table header
				//$content .= "<th>".$fieldName."</th>";
				if ($fieldName == "TOTAL COST" || $fieldName == "UNIT COST"){//-->Remove ID from the table header
					$content .= "<th>".$fieldName."(PHP)</th>";
				} else {
					$content .= "<th>".$fieldName."</th>";
				}
			}
		}
		$content .= "</tr>";
		$id = 0;
		$hasStats = 0;
		$stats = "";
		if(empty($filter)){
			foreach($listView as $views){
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
					foreach($column as $fieldName){
						if ($fieldName != "id"){
							$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
						}
						if ($fieldName == "status"){
							$hasStats = 1;
							$stats = $views[$fieldName];
						}
					}
				$stats = str_replace(" ","",$stats);		
				$content .= "</tr>";
			}
		}else{
			foreach($listView as $views){
				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
				if ($type == "eq"){
					if (strtolower($views["$field"]) == strtolower($value)){
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
											$stats = str_replace(" ","",$stats);
					}
				}else if ($type == "co"){
					if (strpos(strtolower($views["$field"]),strtolower($value)) !== false){
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
											$stats = str_replace(" ","",$stats);
					}				
				}else if ($type == "sw"){
					if (0 === strpos(strtolower($views["$field"]), strtolower($value))){
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
						$stats = str_replace(" ","",$stats);
					}				
				}else if ($type == "ew"){
					if (stripos(strrev(strtolower($views["$field"])), strrev(strtolower($value))) === 0){
						foreach($column as $fieldName){
							if ($fieldName != "id"){
								$content .= "<td class='$fieldName'>".$views[$fieldName]."</td>";
							}
							if ($fieldName == "status"){
								$hasStats = 1;
								$stats = $views[$fieldName];
							}
						}
						$stats = str_replace(" ","",$stats);

					}				
				}

				$content .= "</tr>";
			}
		}
		$content .= '</table></div>';
		return $content;
	}
	


	//FILTER VIEW WITH ONCLICK EVENT
	   public function filterViewURLReport($conn,$column,$listView,$filter,$code)
    {
		$content = '';
		$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="Table">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
			if ($fieldName != "ID"){//-->Remove ID from the table header
				$content .= "<th>".$fieldName."</th>";
			}
		}
		$content .= "<th>Action</th>";
		$content .= "</tr>";
		$id = 0;
		$hasStats = 0;
		$stats = "";
		if(empty($filter)){
			foreach($listView as $views){
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id'); if(event.stopPropagation) event.stopPropagation();\">";
					foreach($column as $fieldName){
						if ($fieldName != "id"){
							//$fielVal = str_replace(' ', '', $views[$fieldName]);
							$content .= "<td width='5%' class='$fieldName'>".$views[$fieldName]."</td>";
						}
						
						if ($fieldName == "status"){
							$hasStats = 1;
							$stats = $views[$fieldName];
						}	
					}
					
				$stats = str_replace(" ","",$stats);
				if ($hasStats > 0){
					if ($stats == "Unfinish" || $stats == "Created"){
					$content .= "<td width='5%'><input type='button' class='deleteButton' onClick=' event.stopPropagation(); deleteRecord($id); ' value='X'>";
					}else{
					$content .= "<td width='5%'>";
					}
				}else{
				$content .= "<td width='5%'><input type='button' class='deleteButton' onClick='event.stopPropagation(); deleteRecord($id); ' value='X'>";
				}
				
				//$content .= "<td width='5%'><input type='button' onClick='deleteRecord($id)' value='X'>";
				$content .= "<input type='button' id='runReport' onClick='runReport($id);if(event.stopPropagation) event.stopPropagation(); else event.cancelBubble=true;' value='Report'></td>";
				$content .= "</tr>";
			}
		}else{
			foreach($listView as $views){
				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$value = strtoupper($value);
				$listValue = strtoupper($views["$field"]);
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
				if ($type == "eq"){
					if ($listValue == $value){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							if ($fieldName != "id"){
								$content .= "<td width='5%'>".$val."</td>";
							}
						}
					}
				}else if ($type == "co"){
					if (strpos($listValue,$value) !== false){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "sw"){
					if (0 === strpos($listValue, $value)){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "ew"){
					if (stripos(strrev($listValue), strrev($value)) === 0){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}
				$content .= "</tr>";
			}
		}
		$content .= '</table></div>';
		return $content;
	}

	
	//FILTER VIEW WITH ONCLICK EVENT without delete functionality
	   public function filterViewURLXdelete($conn,$column,$listView,$filter,$code)
    {
		$content = '';
		$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="Table">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
			//new: can handle customize field name just make the value an array i.e. array('isactive', 'is active')
			if ($fieldName != "id"){//-->Remove ID from the table header
				if(is_array($fieldName)) {
					$content .= "<th>".$fieldName[1]."</th>";
				} else {
					$fieldName = str_replace('_', ' ', $fieldName);
					$fieldName = strtoupper($fieldName);
					$content .= "<th>".$fieldName."</th>";
				}
			}
		}
		$content .= "</tr>";
		$id = 0;
		if(empty($filter)){
			foreach($listView as $views){
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
					foreach($column as $fieldName){
						$_fieldName = is_array($fieldName) ? $fieldName[0] : $fieldName;	
						if ($_fieldName != "id"){
							//$fielVal = str_replace(' ', '', $views[$fieldName]);
							$content .= "<td width='5%' class='$_fieldName'>".$views[$_fieldName]."</td>";
						}
					}
				$content .= "</tr>";
			}
		}else{
			foreach($listView as $views){

				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$value = strtoupper($value);
				$listValue = strtoupper($views["$field"]);
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
				if ($type == "eq"){
					if ($listValue == $value){
						foreach($column as $fieldName){
							$_fieldName = is_array($fieldName) ? $fieldName[0] : $fieldName;
							$val = $views[$_fieldName];
							if ($_fieldName != "id"){
								$content .= "<td width='5%'>".$val."</td>";
							}
						}
					}
				}else if ($type == "co"){
					if (strpos($listValue,$value) !== false){
						foreach($column as $fieldName){
							$_fieldName = is_array($fieldName) ? $fieldName[0] : $fieldName;
							if ($_fieldName != "id"){
								$val = $views[$_fieldName];
								$content .= "<td width='5%'>".$val."</td>";
							}
						}
					}				
				}else if ($type == "sw"){
					if (0 === strpos($listValue, $value)){
						foreach($column as $fieldName){
							$_fieldName = is_array($fieldName) ? $fieldName[0] : $fieldName;
							if ($_fieldName != "id"){
								$val = $views[$_fieldName];
								$content .= "<td width='5%'>".$val."</td>";
							}
						}
					}				
				}else if ($type == "ew"){
					if (stripos(strrev($listValue), strrev($value)) === 0){
						foreach($column as $fieldName){
							$_fieldName = is_array($fieldName) ? $fieldName[0] : $fieldName;
							if ($_fieldName != "id"){
								$val = $views[$_fieldName];
								$content .= "<td width='5%'>".$val."</td>";
							}
						}
					}				
				}
				$content .= "</tr>";
			}
		}
		$content .= '</table></div>';
		return $content;
	}
	
	
		//FILTER VIEW WITH ONCLICK EVENT without delete functionality with ID
	   public function filterViewURLXdeleteID($conn,$column,$listView,$filter,$code)
    {
		$content = '';
		$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="Table">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
			
				$content .= "<th>".$fieldName."</th>";
			
		}
		$content .= "</tr>";
		$id = 0;
		if(empty($filter)){
			foreach($listView as $views){
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
					foreach($column as $fieldName){
						
							//$fielVal = str_replace(' ', '', $views[$fieldName]);
							$content .= "<td width='5%' class='$fieldName'>".$views[$fieldName]."</td>";
						
					}
				$content .= "</tr>";
			}
		}else{
			foreach($listView as $views){
				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$value = strtoupper($value);
				$listValue = strtoupper($views["$field"]);
				$id = $views[$code];
				$content .= "<tr class='test' onclick=\"onclickEvent('$id');\">";
				if ($type == "eq"){
					if ($listValue == $value){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							
								$content .= "<td width='5%'>".$val."</td>";
							
						}
					}
				}else if ($type == "co"){
					if (strpos($listValue,$value) !== false){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "sw"){
					if (0 === strpos($listValue, $value)){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "ew"){
					if (stripos(strrev($listValue), strrev($value)) === 0){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}
				$content .= "</tr>";
			}
		}
		$content .= '</table></div>';
		return $content;
	}
	
//FILTER VIEW WITH ONCLICK EVENT
	   public function filterViewURL2($conn,$column,$listView,$filter,$code)
    {
		$content = '';
		$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="Table">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
			if ($fieldName != "ID" && $fieldName != "REFERENCE NO"){//-->Remove ID from the table header
				$content .= "<th>".$fieldName."</th>";
			}
		}
		$content .= "</tr>";
		$id = 0;
		if(empty($filter)){
			foreach($listView as $views){
				$id = $views[$code];
				$content .= "<tr class='test' id='test' onclick=\"onclickProjectEvent('$id');\">";
					foreach($column as $fieldName){
						if ($fieldName != "id" && $fieldName != "reference_no"){
							$content .= "<td width='5%' class='$fieldName'>".$views[$fieldName]."</td>";
						}
					}
				$content .= "</tr>";
			}
		}else{
			foreach($listView as $views){
				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$value = strtoupper($value);
				$listValue = strtoupper($views["$field"]);
				$id = $views[$code];
				$content .= "<tr class='test' id='test' onclick=\"onclickProjectEvent('$id');\">";
				if ($type == "eq"){
					if ($listValue == $value){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							if ($fieldName != "id"){
								$content .= "<td width='5%'>".$val."</td>";
							}
						}
					}
				}else if ($type == "co"){
					if (strpos($listValue,$value) !== false){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "sw"){
					if (0 === strpos($listValue, $value)){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "ew"){
					if (stripos(strrev($listValue), strrev($value)) === 0){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}
				$content .= "</tr>";
			}
		}
		$content .= '</table></div>';
		return $content;
	}
	
	//FILTER VIEW WITH ONCLICK EVENT with ID
	   public function filterViewURL2ID($conn,$column,$listView,$filter,$code)
    {
		$content = '';
		$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="Table">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
			if ($fieldName != "REFERENCE NO"){//-->Remove ID from the table header
				$content .= "<th>".$fieldName."</th>";
			}
		}
		$content .= "</tr>";
		$id = 0;
		if(empty($filter)){
			foreach($listView as $views){
				$id = $views[$code];
				$content .= "<tr class='test' id='test' onclick=\"onclickProjectEvent('$id');\">";
					foreach($column as $fieldName){
						if ($fieldName != "reference_no"){
							$content .= "<td width='5%' class='$fieldName'>".$views[$fieldName]."</td>";
						}
					}
				$content .= "</tr>";
			}
		}else{
			foreach($listView as $views){
				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$value = strtoupper($value);
				$listValue = strtoupper($views["$field"]);
				$id = $views[$code];
				$content .= "<tr class='test' id='test' onclick=\"onclickProjectEvent('$id');\">";
				if ($type == "eq"){
					if ($listValue == $value){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							
								$content .= "<td width='5%'>".$val."</td>";
							
						}
					}
				}else if ($type == "co"){
					if (strpos($listValue,$value) !== false){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "sw"){
					if (0 === strpos($listValue, $value)){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "ew"){
					if (stripos(strrev($listValue), strrev($value)) === 0){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}
				$content .= "</tr>";
			}
		}
		$content .= '</table></div>';
		return $content;
	}
	
//FILTER VIEW WITH ONCLICK EVENT
	   public function filterViewURLAPP($conn,$column,$listView,$filter,$code)
    {
		$content = '';
		$content .= '<div class="Headers"><table class="NewHeader2"><tr></tr></table></div>';
		$content .= '<div class="Table">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list2">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
			if ($fieldName != "ID" && $fieldName != "REFERENCE NO"){//-->Remove ID from the table header
				$content .= "<th>".$fieldName."</th>";
			}
		}
		$content .= "<th>Action</th>";
		$content .= "</tr>";
		$id = 0;
		if(empty($filter)){
		$action = "";
			foreach($listView as $views){
				$id = $views[$code];
				$id = str_replace(' ', '', $id);
				$content .= "<tr class='test' onclick=\"onclickAPPEvent('$id');\">";
					foreach($column as $fieldName){
						if ($fieldName != "id" && $fieldName != "reference_no"){
							$content .= "<td width='5%' class='$fieldName'>".$views[$fieldName]."</td>";
						}	
						if ($fieldName == "Status"){
						$stat = str_replace(' ', '', $views['Status']);
						//echo '--'.$stat.'--';
							if($stat == "RevisionRequest" || $stat == "Approved"){
								$action = "<td width='5%' class='action'></td>";
							}else{
								$action = "<td width='5%' class='action'><button onClick='approve($id);if(event.stopPropagation) event.stopPropagation(); else event.cancelBubble=true;'>Approve</button> | <button onClick='reject($id);if(event.stopPropagation) event.stopPropagation(); else event.cancelBubble=true;'>Revision Request</button></td>";
							}
						}
					}
					
				$content .= $action;
				$content .= "</tr>";
			}
		}else{
			foreach($listView as $views){
				$field = $filter[0];
				$type = $filter[1];
				$value = $filter[2];
				$value = strtoupper($value);
				$listValue = strtoupper($views["$field"]);
				$id = $views[$code];
				$id = str_replace(' ', '', $id);
				$content .= "<tr class='test' onclick=\"onclickAPPEvent('$id');\">";
				if ($type == "eq"){
					if ($listValue == $value){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							if ($fieldName != "id"){
								$content .= "<td width='5%'>".$val."</td>";
							}
						}
					}
				}else if ($type == "co"){
					if (strpos($listValue,$value) !== false){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "sw"){
					if (0 === strpos($listValue, $value)){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}else if ($type == "ew"){
					if (stripos(strrev($listValue), strrev($value)) === 0){
						foreach($column as $fieldName){
							$val = $views[$fieldName];
							$content .= "<td width='5%'>".$val."</td>";
						}
					}				
				}
				$content .= "<td width='5%' class='action'><div class='approve'>Approve</div> | <div class='reject'>Revision Request</div></td>";
				$content .= "</tr>";
			}
		}
		$content .= '</table></div>';
		return $content;
	}
/*-------END OF TABLE Filter-------*/

//Filter with Groups
 public function filterViewGroups($conn,$column,$listView,$filter)
    {
		$content = '';
		$content .= '<div class="Headers"><table class="NewHeader"><tr></tr></table></div>';
		$content .= '<div class="Table">';
		$content .= '<table width="100%" cellspacing="0" cellpadding="0" border="1" class="list">';
		$content .= '<tr>';
		foreach($column as $fieldName){
		$fieldName = str_replace('_', ' ', $fieldName);
		$fieldName = strtoupper($fieldName);
		$content .= "<th>".$fieldName."</th>";
		}
		$content .= "</tr>";
		
		$listGroups = array("DEP001","DEP002","DEP003");
		if(empty($filter)){
			foreach($listGroups as $key => $groups){
				$groupName = $groups;
				$content .= "<tr><td>".$groupName."</td></tr>";
				$data = array();
				$data2 = array();
				foreach($listView as $views){
					$content .= "<tr class='test'>";
					if ($views['MRC_CODE'] == $groupName){
						foreach($column as $fieldName){
							$data[$fieldName] = $views[$fieldName];
						}
							array_push($data2,$data);
					}
					foreach($data2 as $dataval){
						foreach($column as $fieldName){
							$content .= "<td width='5%'>".$dataval[$fieldName]."</td>";
						}
					}
					$content .= "</tr>";
				}
			}
		}
		return $content;
	}
}
?>