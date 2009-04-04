<?php
require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Http_Client');
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');

$authService = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
$httpClient = Zend_Gdata_ClientLogin::getHttpClient('yzendaisein@hotmail.com', 'hop#$%^jump', $authService);
$gdClient = new Zend_Gdata_Spreadsheets($httpClient);

/*	OBSOLETE SPREADSHEET FEED
$feed = $gdClient->getSpreadsheetFeed();
print "== Available Spreadsheets ==\n";
printFeed($feed);
$tempKey = split('/', $feed->entries[0]->id->text);
$currKey = $tempKey[5];*/

$currKey = 'p0QSLxzpqStsW0BU0zF1wHQ';
//print($currKey);
$currWkshtId;
findWkshtId();

function findWkshtId()
{
	global $currKey, $gdClient, $currWkshtId;
	$query = new Zend_Gdata_Spreadsheets_DocumentQuery();
	$query->setSpreadsheetKey($currKey);
	$feed = $gdClient->getWorksheetFeed($query);
	//print "== Available Worksheets ==\n";
	//printFeed($feed);
	$tempWkshtId = split('/', $feed->entries[0]->id->text);
	$currWkshtId = $tempWkshtId[8];
	//print($currWkshtId);
}

$rowArray;
filterInput();

if($_POST['operation'] == 'search')
{	return listSearch();	}
else if($_POST['operation'] == 'add')
{	return listAdd();	}
else
{	return 0;	}

listSearch();

function listAdd()
{
	// Condition: Only one row
	// rowData contains column variables in certain order
	// Transfer to rowArray accordingly and use insertRow

	global $rowArray, $gdClient, $currKey, $currWkshtId;
    //$rowArray = $stringToArray($rowData);
	$rowData['zipcode'] = $rowArray[0];
	$rowData['city'] = $rowArray[1];
	$rowData['feature'] = $rowArray[2]; 
	$rowData['info'] = $rowArray[3]; 
	$rowData['parking'] = $rowArray[4];
	$rowData['ramps'] = $rowArray[5];
	$rowData['strNmb'] = $rowArray[6];
	$rowData['street'] = $rowArray[7];
	$rowData['state'] = $rowArray[8]; 
	$rowData['country'] = $rowArray[9]; 
	$entry = $gdClient->insertRow($rowData, $currKey, $currWkshtId);
	
    /* Written Debug Check
	if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
        foreach ($rowArray as $column_header => $value) {
            echo "Success! Inserted '$value' in column '$column_header' at row ".   
            substr($entry->getTitle()->getText(), 5) ."\n";
      }
    }*/
}

function listSearch()
{
	global $rowArray, $gdClient, $currKey, $currWkshtId;
    $query = new Zend_Gdata_Spreadsheets_ListQuery();
    $query->setSpreadsheetKey($currKey);
    $query->setWorksheetId($currWkshtId);
	$query->orderby = 'column:zipcode';
	$nonZero = 0;

	if($rowArray[0] != '')
	{ $query->spreadsheetQuery = 'zipcode == '.$rowArray[0]; $nonZero = 1; }

	$listFeed = $gdClient->getListFeed($query);
	$urlString = 'http://spreadsheets.google.com/feeds/list/'.$currKey.'/'.$currWkshtId.'/private/full';

	if($nonZero == 1)
	{ $urlString .= '?sq=zipcode%3D'.$rowArray[0]; }

    /*
	print "entry id | row-content in column A | column-header: cell-content\n". 
    "Please note: The 'dump' command on the list feed only dumps data until ". 
    "the first blank row is encountered.\n\n";
  
    printFeed($listFeed);
    print "\n";
	*/

	print($urlString);
	
	//return $urlString;
}

function filterInput()
{
	global $rowArray;
	/*
	$rowArray[0]='93848';
	$rowArray[1]='400 Castle';
	$rowArray[2]='jake';
	$rowArray[3]='joo';
	$rowArray[4]='moo';
	$rowArray[5]='no';
	*/
	
	if($_POST['operation'] == 'add')
	{
		$rowt[0] = $_POST['zipcode'];
		$rowt[1] = $_POST['city'];
		$rowt[2] = $_POST['feature'];
		$rowt[3] = $_POST['info'];
		$rowt[4] = $_POST['parking'];
		$rowt[5] = $_POST['ramps'];
		$rowt[6] = $_POST['strNmb'];
		$rowt[7] = $_POST['street'];
		$rowt[8] = $_POST['state'];
		$rowt[9] = $_POST['country'];	
		
		//injection filtering code add here
		
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
	else if ($_POST['operation'] == 'search')
	{
		$rowt[0] = $_POST['zipcode'];
		$rowt[1] = $_POST['feature'];
		
		//injection filtering code add here
			
		$rowArray[0] = $rowt[0];
		$rowArray[1] = $rowt[1];
	}
}

/*	TESTING
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