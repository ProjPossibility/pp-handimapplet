<?php
require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Http_Client');
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');

/*
==========================
global variables list
==========================
$gdClient					= client (login)
$currWkshtId;				= worksheet key code
$currKey					= spreedsheet key code
$rowArray;					= input values
		for adding address to data base
		0 = zip
		1 = city
		2 = feature
		3 = info
		4 = parking
		5 = ramps
		6 = streetnumber
		7 = street
		8 = state
		9 = country
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
function findWkshtId()
{
	global $currKey, $gdClient, $currWkshtId;
	
	$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
	$query->setSpreadsheetKey($currKey);
	$feed = $gdClient->getWorksheetFeed($query);
	//print "== Available Worksheets ==\n";		//testing
	//printFeed($feed);							//testing
	$tempWkshtId = split('/', $feed->entries[0]->id->text);
	$currWkshtId = $tempWkshtId[8];
	//print($currWkshtId);
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
	$entry = $gdClient->insertRow($rowData, $currKey, $currWkshtId);
	
    /* Written Debug Check
	if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
        foreach ($rowArray as $column_header => $value) {
            echo "Success! Inserted '$value' in column '$column_header' at row ".   
            substr($entry->getTitle()->getText(), 5) ."\n";
      }
    }*/
	
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
	else if($rowArray[2] != '')
	{ $query->spreadsheetQuery = 'feature = '.$rowArray[2]; }
	else
	{ print "error no entry";	}		//exception shouldn't happen
	
	//print $query->spreadsheetQuery."<br>"; //testing
	
	$listFeed = $gdClient->getListFeed($query);
	
	writeXML($listFeed);
	/* testing looking at listfeed
	print "entry id | row-content in column A | column-header: cell-content\n". 
    "Please note: The 'dump' command on the list feed only dumps data until ". 
    "the first blank row is encountered.\n\n<br>";
    printFeed($listFeed);
	*/
}

function filterInput()
{
	global $rowArray;
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
	*/
	
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

/*
function listUpdate()
{
	global $rowArray, $gdClient, $currKey, $currWkshtId;
	
	$query = new Zend_Gdata_Spreadsheets_ListQuery();
    $query->setSpreadsheetKey($currKey);
    $query->setWorksheetId($currWkshtId);
	$query->orderby = 'column:zipcode';
	
	//require zip, strNmb, street, to match
	if($rowArray[0] != '' && $rowArray[1] != ''&& 
		($rowArray[2] != '' || $rowArray[3] != '' || $rowArray[4] != '' || $rowArray[5] != '')&& 
		$rowArray[6] != ''&& $rowArray[7] != ''&& $rowArray[8] != ''&& $rowArray[9] != '')
	{	$query->spreadsheetQuery = 'zipcode = '.$rowArray[0].' and streetnumber = '.$rowArray[6].' and street = '.$rowArray[7];	}
	
	$listFeed = $gdClient->getListFeed($query);
	$entry = $gdClient->updateRow($listFeed->entries[0], $rowArray);
}
*/

function addressSearch()
{
	global $rowArray, $gdClient, $currKey, $currWkshtId;
	
	$query = new Zend_Gdata_Spreadsheets_ListQuery();	
    $query->setSpreadsheetKey($currKey);
    $query->setWorksheetId($currWkshtId);
	$query->orderby = 'column:zipcode';					//sorting the listfeed
	
	if( $rowArray[0] != '' )
	{	$query->spreadsheetQuery = 'zipcode = '.$rowArray[0];	}
	if($rowArray[1] != '' )
	{
		if  ($query->spreadsheetQuery ='')
		{	$query->spreadsheetQuery = 'city = '.$rowArray[1];		}
		else
		{	$query->spreadsheetQuery .= ' and city = '.$rowArray[1];	}
	}
	if($rowArray[2] != '' )
	{	
		if  ($query->spreadsheetQuery ='')
		{	$query->spreadsheetQuery = 'feature = '.$rowArray[2];	}
		else
		{	$query->spreadsheetQuery .= ' and feature = '.$rowArray[2];	}	
	}		
	if($rowArray[3] != '' )
	{	
		if  ($query->spreadsheetQuery ='')
		{	$query->spreadsheetQuery = 'info = '.$rowArray[3];	}
		else
		{	$query->spreadsheetQuery .= ' and info = '.$rowArray[3];	}
	}
	if($rowArray[4] != '' )
	{
		if  ($query->spreadsheetQuery ='')
		{	$query->spreadsheetQuery = 'parking = '.$rowArray[4];	}
		else
		{	$query->spreadsheetQuery .= ' and parking = '.$rowArray[4];}
	}
	if($rowArray[5] != '' )
	{	
		if  ($query->spreadsheetQuery ='')
		{	$query->spreadsheetQuery = 'ramps = '.$rowArray[5];	}
		else
		{	$query->spreadsheetQuery .= ' and ramps = '.$rowArray[5];}
	}
	if($rowArray[6] != '' )
	{
		if  ($query->spreadsheetQuery ='')
		{	$query->spreadsheetQuery = 'streetnumber = '.$rowArray[6];	}
		else
		{	$query->spreadsheetQuery .= ' and streetnumber = '.$rowArray[6];}
	}
	if($rowArray[7] != '' )
	{
		if  ($query->spreadsheetQuery ='')
		{	$query->spreadsheetQuery = 'street = '.$rowArray[7];	}
		else
		{	$query->spreadsheetQuery .= ' and street = '.$rowArray[7];}
	}
	if($rowArray[8] != '' )
	{
		if  ($query->spreadsheetQuery ='')
		{	$query->spreadsheetQuery = 'state = '.$rowArray[8];	}
		else
		{	$query->spreadsheetQuery .= ' and state = '.$rowArray[8];}
	}
	if($rowArray[9] != '' )
	{
		if  ($query->spreadsheetQuery ='')
		{	$query->spreadsheetQuery = 'country= '.$rowArray[9];	}
		else
		{	$query->spreadsheetQuery .= ' and country = '.$rowArray[9];}
	}

	$listFeed = $gdClient->getListFeed($query);
	
	writeXML($listFeed);
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
			// PROBLEMS WITH SPLITTING COMMA SPACES
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


//	TESTING
/*
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
}*/
?>