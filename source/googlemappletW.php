<?php
require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Http_Client');
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');

/*
===========================
 *latitiude/longitude ver*
===========================
*/

/*
==========================
global variables list
==========================
$gdClient					= client (login)
$currWkshtId;				= worksheet key code
$currKey					= spreedsheet key code
$rowArray;					= input values
		for adding address to data base
		0 = zipcode
		1 = city
		2 = feature
		3 = info
		4 = parking
		5 = ramps
		6 = streetnumber
		7 = street
		8 = state
		9 = country
		10 = lat (latitude)
		11 = long (longitude)
		for search and return info from the data base
		0 = zip
		1 = nothing
		2 = features
		
$_POST['operation']			 = to see if we are adding or not
=====================
functions list
=====================

filterInput() 			= getting and screening input for injection
findWkshtId()		    = finding worksheet key from client
listAdd()				= add row of information if new else update
listSearch()			= search data for info and respond in xml
listupdate()			= update information (non-address)
writeXML()				= output for search
printFeed($feed)		= testing for output
*/

$authService = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
$httpClient = Zend_Gdata_ClientLogin::getHttpClient('yzendaisein@hotmail.com', 'hop#$%^jump', $authService);
$gdClient = new Zend_Gdata_Spreadsheets($httpClient);


$currKey = 'p0QSLxzpqStsW0BU0zF1wHQ';
$currWkshtId;
$rowArray;

findWkshtId();

filterInput();

if($_REQUEST['operation'] == 'add')
	 listAdd();	
else 
	 listSearch();	

/*if(!empty($_POST['operation']))
{	 listAdd();	}
else 
{	 listSearch();	}
*/

//listSearch();

function findWkshtId()
{
	global $currKey, $gdClient, $currWkshtId;
	
	$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
	$query->setSpreadsheetKey($currKey);
	$feed = $gdClient->getWorksheetFeed($query);
	$tempWkshtId = split('/', $feed->entries[0]->id->text);
	$currWkshtId = $tempWkshtId[8];
}

function listAdd()
{
	// Condition: Only one row
	// rowData contains column variables in certain order
	// Transfer to rowArray accordingly and use insertRow

	global $rowArray, $gdClient, $currKey, $currWkshtId;
	
	//storing data
	$rowData['zipcode']	 	= $rowArray[0];
	$rowData['city']	 	= $rowArray[1];
	$rowData['feature']  	= $rowArray[2];
	$rowData['info'] 	 	= $rowArray[3]; 
	$rowData['parking']  	= $rowArray[4];
	$rowData['ramps']    	= $rowArray[5];
	$rowData['streetnumber'] = $rowArray[6];
	$rowData['street']   	= $rowArray[7];
	$rowData['state']    	= $rowArray[8]; 
	$rowData['country']  	= $rowArray[9];
	$rowData['lat']			= $rowArray[10];
	$rowData['long']		= $rowArray[11];
	$entry = $gdClient->insertRow($rowData, $currKey, $currWkshtId);
	
	return;
}

function listSearch()
{
	global $rowArray, $gdClient, $currKey, $currWkshtId;
	
    $query = new Zend_Gdata_Spreadsheets_ListQuery();	
    $query->setSpreadsheetKey($currKey);
    $query->setWorksheetId($currWkshtId);
	$query->orderby = 'column:zipcode';					//sorting the listfeed
	
	if($rowArray[0] != '' && $rowArray[2] != '' )		//check what is being asked for
	{ $query->spreadsheetQuery = 'zipcode = '.$rowArray[0].' and feature = '.$rowArray[2]; }
	else if($rowArray[0] != '')
	{ $query->spreadsheetQuery = 'zipcode = '.$rowArray[0];  }
	else
	{ print "error no entry"; }		//exception shouldn't happen: always at least zipcode
	
	$listFeed = $gdClient->getListFeed($query);

	if($rowArray[10] != '' && $rowArray[11] != '')
	{ $listFeed = addressSearch($listFeed,$rowArray[10],$rowArray[11]); }
	
	writeXML($listFeed);
	//printFeed($listFeed);
}

function filterInput()
{
	global $rowArray;
	
	if(!empty($_POST['operation']) && $_POST['operation'] == 'add')
	{
		$rowt[0] = $_POST['zipcode'];
		$rowt[1] = $_POST['city'];
		$rowt[2] = $_POST['feature'];
		$rowt[3] = $_POST['info'];
		$rowt[4] = $_POST['parking'];
		$rowt[5] = $_POST['ramps'];
		$rowt[6] = $_POST['streetnumber'];
		$rowt[7] = $_POST['street'];
		$rowt[8] = $_POST['state'];
		$rowt[9] = $_POST['country'];	
		$rowt[10] = $_POST['lat'];
		$rowt[11] = $_POST['long'];
	}
	else if (!empty($_GET['operation']) && $_GET['operation'] == 'search')
	{
		$rowt[0] = $_GET['zipcode'];
		$rowt[2] = $_GET['feature'];
	}
	else
	{
	//if we get here something is wrong...
	}
	
		$row[0] = trim($row[0]);
		$row[1] = trim($row[1]);
		$row[2] = trim($row[2]);
		$row[3] = trim($row[3]);
		$row[4] = trim($row[4]);
		$row[5] = trim($row[5]);
		$row[6] = trim($row[6]);
		$row[7] = trim($row[7]);
		$row[8] = trim($row[8]);
		$row[9] = trim($row[9]);
		$row[10] = trim($row[10]);
		$row[11] = trim($row[11]);
		
		$address = sortAddress();
		
		if($address != NULL)
		{
			$row[6] = $address[0];
			$row[7] = $address[1];
			$row[1] = $address[2];
			$row[8] = $address[3];
			$row[9] = "US";
		}

	//additional injection filtering code add here
		$rowt[0]=filter_var($rowt[0],FILTER_SANITIZE_NUMBER_INT);		//has to be number
		$rowt[1]=filter_var($rowt[1],FILTER_SANITIZE_STRIPPED);
		$rowt[2]=filter_var($rowt[2],FILTER_SANITIZE_STRIPPED);
		//$rowt[3]=filter_var($rowt[3],FILTER_SANITIZE_SPECIAL_CHARS);
		$rowt[4]=filter_var($rowt[4],FILTER_SANITIZE_NUMBER_INT);		//has to be a number
		$rowt[5]=filter_var($rowt[5],FILTER_SANITIZE_STRIPPED);
		$rowt[6]=filter_var($rowt[6],FILTER_SANITIZE_NUMBER_INT);		//has to be a number
		$rowt[7]=filter_var($rowt[7],FILTER_SANITIZE_STRIPPED);
		$rowt[8]=filter_var($rowt[8],FILTER_SANITIZE_STRING);			//has to be letters
		$rowt[9]=filter_var($rowt[9],FILTER_SANITIZE_STRING);			//has to be letters
		//$rowt[10]=filter_var($rowt[10],FILTER_SANITIZE_NUMBER_INT);	//number with decimals - possibly unnecessary since given by google
		//$rowt[11]=filter_var($rowt[11],FILTER_SANITIZE_NUMBER_INT);	//number with decimals - possibly unnecessary since given by google
		
		$rowArray[0] = $rowt[0];
		$rowArray[1] = $rowt[1];
		$rowArray[2] = $rowt[2];
		$rowArray[3] = $rowt[3];
		$rowArray[4] = $rowt[4];
		$rowArray[5] = $rowt[5];
		$rowArray[6] = $rowt[6];
		$rowArray[7] = $rowt[7];
		$rowArray[8] = $rowt[8];
		$rowArray[9] = $rowt[9];	
		$rowArray[10] = $rowt[10];
		$rowArray[11] = $rowt[11];

	/*
	$rowArray[0]='99000';		//hard code data for testing 
	$rowArray[1]='pizza';
	$rowArray[2]='jake';
	$rowArray[3]='joo';
	$rowArray[4]='moo';
	$rowArray[5]='no';
	$rowArray[6]='123123';
	$rowArray[7]='moo';
	$rowArray[8]='no';
	$rowArray[9]='no';
	$rowArray[10]='76.928';
	$rowArray[11]='23.478';
	*/
		$rowArray[0] = 91320;
		$rowArray[10] = 34.1934576;
		$rowArray[11] = -118.9222422;
}

// Takes given address form (streetnumber,street,city,state), breaks into pieces and trims.
// Returns in array from 0-3.
function sortAddress()
{
	$address = $_REQUEST['address'];
	if($address != NULL)
	{ $sect = split(",",$address); }
	for($i=0; $i<4; $i++)
	{ $sect[$i] = trim($sect[$i]); }
	return $sect;
}

function listUpdate()
{
	global $rowArray, $gdClient, $currKey, $currWkshtId;
	
	$query = new Zend_Gdata_Spreadsheets_ListQuery();
    $query->setSpreadsheetKey($currKey);
    $query->setWorksheetId($currWkshtId);
	$query->orderby = 'column:zipcode';
	
	//require zip, strNmb, street, to match (consider lat/long)
	if($rowArray[0] != '' && $rowArray[6] != '' && $rowArray[7] != '' && $rowArray[8] != '' && 
	   $rowArray[9] != '' && $rowArray[10] != '' && $rowArray[11] != '')
	{	$query->spreadsheetQuery = 'zipcode = '.$rowArray[0].' and streetnumber = '.$rowArray[6].' and street = '.$rowArray[7];	}
	
	$listFeed = $gdClient->getListFeed($query);
	$entry = $gdClient->updateRow($listFeed->entries[0], $rowArray);
}

function addressSearch($feed,$lat1,$long1)
{
	//print $lat1.' '.$long1.' ';
	//$i = 0;
	//$returnFeed = new Zend_Gdata_Spreadsheets_ListFeed();
    foreach($feed->entries as $entry) {
		if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
			$latEntry = $entry->getCustomByName('lat');
			$longEntry = $entry->getCustomByName('long');
			//print $latEntry.'/n'.$longEntry;
			$distance = distance($lat1,$long1,$latEntry,$longEntry);
			//print ' '.$distance.' ';
			if(abs($distance) < 0.5)
			{
				//Find working solution
				//$entry->delete(); Deletes actual spreadsheet data
				//$returnFeed->entries[$i] = $spreadsheetService->insertRow($entry, $currKey, $currWkshtId);
				//$i++;
			}
			//echo $latEntry->getColumnName() . " = " . $latEntry->getText();
			//echo $longEntry->getColumnName() . " = " . $longEntry->getText();
        }
	}
	return $feed;
}

function writeXML($listFeed)
{  
	$doc = new DOMDocument();
	$doc->formatOutput = true;

	$r = $doc->createElement( "locations" );
	$doc->appendChild( $r );

	if($listFeed != NULL)
	{ $allEnts = listData($listFeed); }
	
	/*foreach($allEnts as $e)
	{
	   //$i=0;
	   print '<br>New Entry:'.$e['zipcode']. ", ". $e['city'];
	   
	   // print 'index:'. $val['zipcode']."<br>";
		//$i++;
		
    }*/	
	
	if($allEnts != NULL) {
	foreach($allEnts as $e)
	{
		$b = $doc->createElement( "location" );
		
		if($e['zipcode'] != null && $e['zipcode'] != '')
		{ $b->setAttribute('zipcode', $e['zipcode']); }
		if($e['streetnumber'] != null && $e['streetnumber'] != '')
		{ $b->setAttribute('streetnumber', $e['streetnumber']); }
		if($e['city'] != null && $e['city'] != '')
		{ $b->setAttribute('city', $e['city']); }
		if($e['feature'] != null && $e['feature'] != '')
		{ $b->setAttribute('feature', $e['feature']); }
		if($e['state'] != null && $e['state'] != '')
		{ $b->setAttribute('state', $e['state']); }
		if($e['country'] != null && $e['country'] != '')
		{ $b->setAttribute('country', $e['country']); }
		if($e['info'] != null && $e['info'] != '')
		{ $b->setAttribute('info', $e['info']); }
		if($e['parking'] != null && $e['parking'] != '')
		{ $b->setAttribute('parking', $e['parking']); }
		if($e['ramps'] != null && $e['ramps'] != '')
		{ $b->setAttribute('ramps', $e['ramps']); }
		if($e['street'] != null && $e['street'] != '')
		{ $b->setAttribute('street', $e['street']); }
		if($e['lat'] != null && $e['lat'] != '')
		{ $b->setAttribute('lat', $e['lat']); }
		if($e['long'] != null && $e['long'] != '')
		{ $b->setAttribute('long', $e['long']); }
		
		$r->appendChild( $b );
	} }
	
	print ($doc->saveXML());
//	$doc->save("write.xml");
}

function listData($listFeed)
{
	global $allEnts;
	$j = 0;
	foreach($listFeed->entries as $entry)
	{
		$entrySplit = split(", ", $entry->content->text);
		$result;
		foreach($entrySplit as $ent)
		{
			// PROBLEMS WITH SPLITTING COMMA SPACES - alleviated with filterInput function
			$values = split(": ", $ent);
			
			$result[trim($values[0])] = $values[1];
			//print "<br>".$values[0]." = ".$result[trim($values[0])] ;
		}
		$allEnts[$j] = $result;
		$j++;
	}
	
	//print "val:".count($allEnts);
/*	foreach($allEnts as $e)
	{
	   //$i=0;
	   print '<br>New Entry:'.$e['zipcode']. ", ". $e['city'];
	   
	   // print 'index:'. $val['zipcode']."<br>";
		//$i++;
		
    }*/
	return $allEnts;
}

// Currently not working - returning large distance even when 0
function distance($lat1, $lon1, $lat2, $lon2)
{
	/*$dlon = $lon2 - $lon1;
	$dlat = $lat2 - $lat1;
	$dist = sin(deg2rad($dLat/2)) * sin(deg2rad($dLat/2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin(deg2rad($dLon/2)) * sin(deg2rad($dLon/2));
	$dist = 2 * atan2(sqrt($dist), sqrt(1-$dist));
	$dist = 6371 * dist;*/
	$dist = acos(sin($lat1*pi()/180) * sin($lat2*pi()/180) + cos($lat1*pi()/180) * cos($lat2*pi()/180) *  cos(($lon1 - $lon2)*pi()/180)) * 3963.1676;
	print $lat1.' seppp '.$lon1.' seppp '.$lat2.' seppp '.$lon2.' dist: '.$dist.'<br>';
	return $dist;
}
 
//	TESTING USE
function printFeed($feed)
{
    $i = 0;
    foreach($feed->entries as $entry) {
        if ($entry instanceof Zend_Gdata_Spreadsheets_CellEntry) {
            print $entry->title->text .' '. $entry->content->text . "\n";
        } else if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
            print $i .' '. $entry->title->text .' | '. $entry->content->text . "\n";
        } else {
            print $i .' '. $entry->title->text . "\n";
        }
        $i++;
    }
}
?>