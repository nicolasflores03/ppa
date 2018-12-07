var aesPW = '1nter@ctiv3f0rm$';	
function replaceAll(token, newToken, ignoreCase) {
    var str, i = -1, _token;
    if((str = this.toString()) && typeof token === "string") {
        _token = ignoreCase === true? token.toLowerCase() : undefined;
        while((i = (
            _token !== undefined? 
                str.toLowerCase().indexOf(
                            _token, 
                            i >= 0? i + newToken.length : 0
                ) : str.indexOf(
                            token,
                            i >= 0? i + newToken.length : 0
                )
        )) !== -1 ) {
            str = str.substring(0, i)
                    .concat(newToken)
                    .concat(str.substring(i + token.length));
        }
    }
	alert('about to exit replaceAll()');
return str;
};

$(document).ready(function() {
$('#frmMain input[type=text]').focus(function() {
$(this).css('background','#FFC');
this.select();
}).blur(function(){
$(this).css('background','#FFF');
});
});

function qs(search_for) {
		var query = window.location.search.substring(1);
		var parms = query.split('&');
		for (var i=0; i<parms.length; i++) {
			var pos = parms[i].indexOf('=');
			if (pos > 0  && search_for == parms[i].substring(0,pos)) {
				return parms[i].substring(pos+1);;
			}
		}
		return "";
}	

// ONLY DIGITS ARE ALLOWED TO BE ENTERED, ELSE WILL NOT BE DISPLAYED
function numbersonly(myfield, e, dec)
{
var key;
var keychar;
if(window.event)
key = window.event.keyCode;
else if(e)
key = e.which;
else
return true;
keychar = String.fromCharCode(key);

// control keys
if ((key==null) || (key==0) || (key==8) || (key==9) || (key==13) || (key==27) )
return true;
// numbers
else if ((("0123456789-").indexOf(keychar) > -1))
return true;
// decimal point jump
else if (dec && (keychar == "."))
{
myfield.form.elements[dec].focus();
return false;
}
else
return false;
}


function numbersonly(e, decimal) {
		var key;
		var keychar;
		if (window.event) {
		   key = window.event.keyCode;
		}
		else if (e) {
		   key = e.which;
		}
		else {
		   return true;
		}
		keychar = String.fromCharCode(key);

		if ((key==null) || (key==0) || (key==8) ||  (key==9) || (key==13) || (key==27) ) {
		   return true;
		}
		else if ((("0123456789.-").indexOf(keychar) > -1)) {
		   return true;
		}
		else if (decimal && (keychar == ".")) { 
		  return true;
		}
		else if (decimal && (keychar == "-")) { 
		  return true;
		}
		else
		   return false;
}

function numbersonly1702Q(e, decimal) {
		var key;
		var keychar;

		if (window.event) {
		   key = window.event.keyCode;
		}
		else if (e) {
		   key = e.which;
		}
		else {
		   return true;
		}
		keychar = String.fromCharCode(key);

		if ((key==null) || (key==0) || (key==8) ||  (key==9) || (key==13) || (key==27) ) {
		   return true;
		}
		else if ((("0123456789.-").indexOf(keychar) > -1)) {
		   return true;
		}
		else if (decimal && (keychar == ".")) { 
		  return true;
		}
		else
		   return false;
}

function dateOnly(e, decimal) {
		var key;
		var keychar;

		if (window.event) {
		   key = window.event.keyCode;
		}
		else if (e) {
		   key = e.which;
		}
		else {
		   return true;
		}
		keychar = String.fromCharCode(key);

		if ((key==null) || (key==0) || (key==8) ||  (key==9) || (key==13) || (key==27) ) {
		   return true;
		}
		else if ((("0123456789/").indexOf(keychar) > -1)) {
		   return true;
		}
		else if (decimal && (keychar == ".")) { 
		  return true;
		}
		else
		   return false;
}

function capital (a)
{
	a.value = a.value.toUpperCase();
}

function capital()
{
	var elem = document.getElementById('frmMain').elements;
	for(var i = 0; i < elem.length; i++)
	
	if (elem[i].type == 'text') 
	{
		elem[i].value = elem[i].value.toUpperCase(); //all select-one and text values
	}


}

				

function replaceHtml(el, html) {
	var oldEl = typeof el === "string" ? document.getElementById(el) : el;
	/*@cc_on // Pure innerHTML is slightly faster in IE
		oldEl.innerHTML = html;
		return oldEl;
	@*/
	var newEl = oldEl.cloneNode(false);
	newEl.innerHTML = html;
	oldEl.parentNode.replaceChild(newEl, oldEl);
	/* Since we just removed the old element from the DOM, return a reference
	to the new element, which can be used to restore variable references. */
	return newEl;
};

function isAmountWithinAllowedPrecision(number) {

		num = number.value.toString().replace(/\$|\,/g,'');
	
		if (num.indexOf('.') > 0) {
			var parts = num.toString().split('.');
			parts[0].length; 		
			if (parts[0].length > 12) {
				return false;
			} else {
				return true;
			}
		} else {
			if (num.length > 12) {
				return false;
			} else {
				return true;
			}
		}	
}

function round(number,dec) {

    if (isAmountWithinAllowedPrecision(number)) {
	
		num = number.value.toString().replace(/\$|\,/g,'');
		
		if (num.indexOf('.') > 0) {
			var parts = num.toString().split('.');
			parts[0].length; 		
			if (parts[0].length > 12) {
				num = parts[0].substring(0,12) +'.'+ parts[1];
			} 
		} else {
			if (num.length > 12) {
				num = num.substring(0,12);
			}
		}
		
		if(isNaN(num))
		num = "0";
		sign = (num == (num = Math.abs(num)));
		num = Math.floor(num*100+0.50000000001);
		cents = num%100;
		num = Math.floor(num/100).toString();
		if(cents<10)
		cents = "0" + cents;
		for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
		num = num.substring(0,num.length-(4*i+3))+','+
		num.substring(num.length-(4*i+3));
		number.value = (((sign)?'':'-')+ num + '.' + cents);	
	} else {
		number.value = "0.00";
	}	
}

function formatCurrency(number) {

		num = number.toString().replace(/\$|\,/g,'');
		if (num.indexOf('.') > 0 && num>0) {
			var parts = num.toString().split('.');
			parts[0].length; 		
			if (parts[0].length > 12) {
				num = parts[0].substring(0,12) +'.'+ parts[1];
			} 
		} else if(num.indexOf('.') > 0 && num<0) {
			var parts = num.toString().split('.');
			parts[0].length; 		
			if (parts[0].length > 13) {
				num = parts[0].substring(0,12) +'.'+ parts[1];
			} 
		}
		else {
			if (num>0 && num.length > 12) {
				num = num.substring(0,12);
			}
		}
		
		if(isNaN(num))
		num = "0";
		sign = (num == (num = Math.abs(num)));
		num = Math.floor(num*100+0.50000000001);
		cents = num%100;
		num = Math.floor(num/100).toString();
		if(cents<10)
		cents = "0" + cents;
		for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
		num = num.substring(0,num.length-(4*i+3))+','+
		num.substring(num.length-(4*i+3));
		return (((sign)?'':'-')+ num + '.' + cents);	

}

function NumWithComma(num){
	if (num != 0)
		return parseFloat(num.replace(/,/g,''));
	else { return parseFloat(num) }
} 

function wholenumber(e, decimal) {
		var key;
		var keychar;

		if (window.event) {
		   key = window.event.keyCode;
		}
		else if (e) {
		   key = e.which;
		}
		else {
		   return true;
		}
		keychar = String.fromCharCode(key);

		if ((key==null) || (key==0) || (key==8) ||  (key==9) || (key==13) || (key==27) ) {
		   return true;
		}
		else if ((("0123456789").indexOf(keychar) > -1)) {
		   return true;
		}
		else
		   return false;
}

function letternumber(e)
{
var key;
var keychar;

if (window.event)
   key = window.event.keyCode;
else if (e)
   key = e.which;
else
   return true;
keychar = String.fromCharCode(key);
keychar = keychar.toLowerCase();

// control keys
if ((key==null) || (key==0) || (key==8) || 
    (key==9) || (key==13) || (key==27) )
   return true;

// alphas and numbers
else if ((("abcdefghijklmnopqrstuvwxyz0123456789").indexOf(keychar) > -1))
   return true;
else
   return false;
}

function getHHMMSS() {
  now = new Date();
  hour = "" + now.getHours(); if (hour.length == 1) { hour = "0" + hour; }
  minute = "" + now.getMinutes(); if (minute.length == 1) { minute = "0" + minute; }
  second = "" + now.getSeconds(); if (second.length == 1) { second = "0" + second; }
  return hour + "" + minute + "" + second;
}

function negative(str){
   var fvalue = parseFloat(str);
   return !isNaN(fvalue) && fvalue != 0;
   }

function number(e, decimal)
{
	var key;
			var keychar;

			if (window.event) {
			   key = window.event.keyCode;
			}
			else if (e) {
			   key = e.which;
			}
			else {
			   return true;
			}
			keychar = String.fromCharCode(key);

			if ((key==null) || (key==0) || (key==8) ||  (key==9) || (key==13) || (key==27) ) {
			   return true;
			}
			else if ((("0123456789-.").indexOf(keychar) > -1)) {
			   return true;
			}
			else if (decimal && (keychar == ".")) { 
			  return true;
			}
			else
			   return false;
}

function getMMDDYYYYHHmmSS () {
  now = new Date();
  year = "" + now.getFullYear();
  month = "" + (now.getMonth() + 1); if (month.length == 1) { month = "0" + month; }
  day = "" + now.getDate(); if (day.length == 1) { day = "0" + day; }
  hour = "" + now.getHours(); if (hour.length == 1) { hour = "0" + hour; }
  minute = "" + now.getMinutes(); if (minute.length == 1) { minute = "0" + minute; }
  second = "" + now.getSeconds(); if (second.length == 1) { second = "0" + second; }
  return month + "" + day + "" + year + "" + hour + "" + minute + "" + second;
}


function getMMDDYYYYHHmmSSsss () {
  now = new Date();
  year = "" + now.getFullYear();
  month = "" + (now.getMonth() + 1); if (month.length == 1) { month = "0" + month; }
  day = "" + now.getDate(); if (day.length == 1) { day = "0" + day; }
  hour = "" + now.getHours(); if (hour.length == 1) { hour = "0" + hour; }
  minute = "" + now.getMinutes(); if (minute.length == 1) { minute = "0" + minute; }
  second = "" + now.getSeconds(); if (second.length == 1) { second = "0" + second; }
  millis = "" + now.getMilliseconds();
  if (millis.length == 1) { millis = "00" + millis; }
  if (millis.length == 2) { millis = "0" + millis; }
  return month + "/" + day + "/" + year + " " + hour + ":" + minute + ":" + second + "." + millis;
}

function drivesAvailable() {
    var driveList = new Array();
	try {
	  var fso = new ActiveXObject("Scripting.FileSystemObject");
	  var Enum = new Enumerator(fso.Drives);
	  var driveInfo; 
	  var index = 0;
	  for (Enum.moveFirst(); !Enum.atEnd(); Enum.moveNext()) {
	   driveInfo = Enum.item();
		 if (driveInfo.IsReady) {
			//alert('Drive : '+driveInfo);
			driveList[index]=driveInfo;
			index++;
		 }	
	  }
	} catch(e) {
	  alert('drive locator exception : '+e.message);
	}
	return driveList;
}


function getDrives() {
		  
        var data = "<option value='0'> - </option>";
        var driveList = drivesAvailable();
        
		for(var i = 0; i < driveList.length; i++) {
		
            var drive = driveList[i];
            data = data + "<option value='" + drive + "/'>" + drive + "</option>";
			
        }
		
		$('#driveSelectTPExport').html(data);
		$('#driveSelect').html(data);		
		$('#driveSelectX').html(data);
}

	
function fastTrim (str) {
		var	str = str.replace(/^\s\s*/, ''),
			ws = /\s/,
			i = str.length;
		while (ws.test(str.charAt(--i)));
		return str.slice(0, i + 1);
}



function formIDFromWFXML(content) {
	//<FormName>0605</FormName>
	if (content.indexOf("<FormName>") > -1 && content.indexOf("</FormName>") > -1) {
		return content.substring(content.indexOf("<FormName>")+10, content.indexOf("</FormName>"));
	}
}

/*function formIDFromWFXML(lineContentID, lineContent) {
	//lineContent = <ValidatedFormId>xxxx0605xxxx</ValidatedFormId>
    var formIDs = new Array("0605","1600","1600WP","1601C","1601E","1601F","1602","1603","1604CF","1604E","1606","1701Q","1702Q","1704","1706","1707","1800","1801","2000","2000OT","2200A","2200AN","2200M","2200P","2200T","2550M","2550Q","2551","2551M","2552","2553");
	if (lineContentID == "<ValidatedFormId>") {
		for(var i=0; i<formIDs.length; i++) {
			var formId = formIDs[i];		
			if (lineContent.indexOf(formId) > -1) {		
alert('js formId = '+formId);			
				return formId;
			}
		}	
	}
}*/

function isLineValidForProcessing(lineContentID) {

		//var exemptedIDArray = new Array("<?xml version='1.0'?>","<BIRForm>","</BIRForm>","<FormName>","</FormName>","<ValidatedFormId>","</ValidatedFormId>","<TSPInfo>","</TSPInfo>"
		//					  ,"<TSPId>","</TSPId>","<TSPName>","</TSPName>","<Main>","</Main>","<PartI>","</PartI>","<PartII>","</PartII>"
		//					  ,"<PartIII>","</PartIII>","<Modals>","</Modals>","<Schedule>","</Schedule>","All Rights Reserved BIR 2012.");
		
		var strExemptedIDs = "<?xml version='1.0'?>,<BIRForm>,</BIRForm>,<FormName>,</FormName>,<ValidatedFormId>,</ValidatedFormId>,<TSPInfo>,</TSPInfo>," +
							 "<TSPId>,</TSPId>,<TSPName>,</TSPName>,<Main>,</Main>,<PartI>,</PartI>,<PartII>,</PartII>,<PartIII>,</PartIII>,<Modals>,</Modals>," +
							 "<TaxTypeCode>,</TaxTypeCode>,<Atc>,</Atc>,<AtcCode>,</AtcCode>,<SectionA>,</SectionA>,<Schedule1_1>,</Schedule1_1>," +
							 "<Schedule1>,</Schedule1>,<Schedule2>,</Schedule2>,<Schedule3>,</Schedule3>,<Schedule4>,</Schedule4>,<Schedule5>,</Schedule5>," +
							 "<Schedule6>,</Schedule6>,<Schedule7>,</Schedule7>,<Schedule8>,</Schedule8>,<SchedI>,</SchedI>,<SchedII>,</SchedII>" +
							 "<Schedule>,</Schedule>,All Rights Reserved BIR 2012."; 
							 
		if (strExemptedIDs.indexOf(lineContentID) >= 0) {
			return false;
		} else if (strExemptedIDs.indexOf(lineContentID) == -1) {
			return true;
		}
		
}	

function disableAllElementIDs() {
			var elem = document.getElementById('frmMain').elements;
			for(var i = 0; i < elem.length; i++)
			{
				if (elem[i].type != 'hidden' && elem[i].type != 'undefined') {	//elem[i].type != 'button' && 	
					//var elemId = elem[i].id;
					if (elem[i] != null && elem[i].id != '') {
				//alert('elem[i].id = '+elem[i].id);	
						document.getElementById(elem[i].id).disabled = true; 					
					}	
				}
			}
			
			$('a').each(function(){
				if (this.id.length > 1) {
					document.getElementById(this.id).disabled = true;
				}
			});		
			
			document.getElementById('btnPrint').disabled = false;				
}

function getHHMMSSWithParam(dtInput) {
  now = new Date(dtInput);
  hour = "" + now.getHours(); if (hour.length == 1) { hour = "0" + hour; }
  minute = "" + now.getMinutes(); if (minute.length == 1) { minute = "0" + minute; }
  second = "" + now.getSeconds(); if (second.length == 1) { second = "0" + second; }
  return hour + ":" + minute + ":" + second;
}

/****************************************************************************************/

function calculateCheckDigit(someData, format) {
	var evenSum = 0;
    var oddSum = 0;
	var len;
    
	if (format == 'SSCC') {
		len = 17;
	} else {
		len = 11;
	}
	
    if(someData.toString().length != len) {
		alert("Data length must be "+len+" to calculate "+format+" check digit.");
	} else {               
		//Loop through all the data, summing up the evens and odds
		for(var i = 0; i < someData.toString().length; i++) {
			//Offset since the SSCC standard starts it's index at 1
			if((i + 1) % 2 == 0) {
				evenSum += parseInt(someData.toString().charAt(i));
			} else {
				oddSum += parseInt(someData.toString().charAt(i));
			}
		}
		var oddsTotal = oddSum * 3;
		var bothTotal = oddsTotal + evenSum;
		var remainder = bothTotal % 10;
        var checksum = 10 - remainder;
		// alert("Checksum calculation: " + oddsTotal + " + " + evenSum + " = " + bothTotal + " % 10 = " + remainder + ", 10" + " - " + remainder + " = " + checksum);
		if(checksum == 10) {
			// alert("Trimming checksum of " + checksum + " to 0 so it can fit into one digit.");
			checksum = 0;
		}                       
		// alert("Calculated SSCC checksum of " + checksum + " for data '" + someData + "'");
		return checksum;
	}
} //end

//----- Global functions to determine MM/DD/YYYY return period end across all forms ------
function getLastDateOfMonthAndReturnMMDDYYYY(Year, Month){
	if (Month.substring(0,1) == '0') {
		Month = Month.substring(1,2);		
	} else {
		Month = Month.toString();
	}
	
	var dMod = new Date((new Date(parseInt(Year), parseInt(Month),1))-1); 
	return Month + "/" + dMod.getDate() + "/" + Year;	
} 

function getQuarterMonth(Year, Month, Qtr) {
	var dDay = new Date((new Date(Year, parseInt(Month),1))-1);  
	//var yearEndedMMDDYYYY = new Date(Year, parseInt(Month)-1, dDay.getDate());
	var yearEndedMMDDYYYY = new Date(Year, parseInt(Month)-1, 15);

	var lessMonthCount = 0;
	if (Qtr == 'Q1') {
		lessMonthCount = 9;
	} else if (Qtr == 'Q2') {
		lessMonthCount = 6;
	} else if (Qtr == 'Q3') {
		lessMonthCount = 3;
	} else if (Qtr == 'Q4') {
		lessMonthCount = 0;
	}

	var newDate = new Date(new Date(yearEndedMMDDYYYY).setMonth( (yearEndedMMDDYYYY.getMonth())-lessMonthCount));	// no +1 prior	

	var mm = (newDate.getMonth()+1).toString(); //+1 prior
	if (mm.length == 1) {
		mm = "0" + mm; 	
	}
	
	return mm + "/" + newDate.getFullYear();	//month and year of the Quarter ex: 05/2011
}

function getRetunPeriodOnGivenDays(Year, Month, Day, AddDays) { //1800, 1801
	var baseDate = new Date(Year, parseInt(Month)-1, Day);

	var newDate = new Date(new Date(baseDate).setDate(baseDate.getDate()+AddDays));		
	
	var mm = (newDate.getMonth()+1).toString();
	if (mm.length == 1) {
		mm = "0" + mm; 	
	}
	
	var dd = (newDate.getDate()).toString();
	if (dd.length == 1) {
		dd = "0" + dd; 	
	}
	
	return mm + "/" + dd + "/" + newDate.getFullYear();	//ex: 04/28/2012
}
function getRetunPeriodOnGivenMonths(Year, Month, Day, AddMonths) { //1801
	var baseDate = new Date(Year, parseInt(Month)-1, Day);

	var newDate = new Date(new Date(baseDate).setMonth(baseDate.getMonth()+AddMonths));		
	
	var mm = (newDate.getMonth()+1).toString();
	if (mm.length == 1) {
		mm = "0" + mm; 	
	}
	
	var dd = (newDate.getDate()).toString();
	if (dd.length == 1) {
		dd = "0" + dd; 	
	}
	
	return mm + "/" + dd + "/" + newDate.getFullYear();	//ex: 04/28/2012
}

function calculateCheckDigit(someData, format) {
	var evenSum = 0;
    var oddSum = 0;
	var len;
    
	if (format == 'SSCC') {
		len = 17;
	} else {
		len = 11;
	}
	
    if(someData.toString().length != len) {
		alert("Data length must be "+len+" to calculate "+format+" check digit.");
	} else {               
		//Loop through all the data, summing up the evens and odds
		for(var i = 0; i < someData.toString().length; i++) {
			//Offset since the SSCC standard starts it's index at 1
			if((i + 1) % 2 == 0) {
				evenSum += parseInt(someData.toString().charAt(i));
			} else {
				oddSum += parseInt(someData.toString().charAt(i));
			}
		}
		var oddsTotal = oddSum * 3;
		var bothTotal = oddsTotal + evenSum;
		var remainder = bothTotal % 10;
        var checksum = 10 - remainder;
		// alert("Checksum calculation: " + oddsTotal + " + " + evenSum + " = " + bothTotal + " % 10 = " + remainder + ", 10" + " - " + remainder + " = " + checksum);
		if(checksum == 10) {
			// alert("Trimming checksum of " + checksum + " to 0 so it can fit into one digit.");
			checksum = 0;
		}                       
		// alert("Calculated SSCC checksum of " + checksum + " for data '" + someData + "'");
		return checksum;
	}
} //end

function goToHelpPage(formType) { 
	if (confirm("Navigating away to this page that have changes will be lost.\n\nPlease note that saved tax returns can be viewed from the Main Screen.\n\nClick 'OK' should you wish to proceed anyway.\nOtherwise, click 'Cancel' if you want to save your changes first.")) { 
		//proceed to Help page
		window.location="../helpfile/Help"+formType+".hta";
	} else {
		//Do nothing to retain Form page.  User should hit 'Save' 
		}
}