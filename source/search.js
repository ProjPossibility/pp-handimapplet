<?xml version="1.0" encoding="UTF-8"?>
<Module>
<ModulePrefs 	
	title="Parking Search"              
	description="Displays a Hello World message in the left panel"             
	author="Your name"            
	author_email="your-email@gmail.com"
	height="150">
	<Require feature="sharedmap" /> 
</ModulePrefs>
<Content type="html">
<![CDATA[

<h2>Handicapped Parking Finder</h2>

<div>
<div style="padding-top:.25em; background-color: #e5ecf9;">
<style>
.headertable{padding:0px 5px 5px 5px;width:300px;}
.headerrow{font-size:8pt;padding:0px 0px 0px 5px;}
.disabled{color:gray;}
</style>
<table border=0 cellspacing="0" cellpadding="0" class="headertable">
<form onsubmit="doSearch(); return false;" style="margin-bottom: 4px; padding: 0px; border:0px">
<tr class="headerrow">


<td colspan=2>Location &nbsp; <span style="color: gray">e.g., zip, address, city </span></td>
</tr>
<tr>
<td>
<input id="location" name="location" value="" style="width: 180px">
</td>
<td><input type=submit value="Search"></td>
</tr>
</table>
</form>
</div>

<div id="resultsheader" style="margin-bottom: 4px; margin-top: 4px;"></div>
<div id="results"></div>
<div id="resultsfooter" style="text-align: center;"></div>

<script>
var map = new GMap2();
var geocoder = new GClientGeocoder();

function centerAddress(address) 
{
	geocoder.getLatLngAsync(address,    
		function(latlng) 
		{
			if (!latlng)
			{
				alert(address + " not found");
			}
			else
			{
				map.setCenter(latlng, 14);
			}
		}
	);
}

function markAddress(address, infoWindow)
{
	geocoder.getLatLngAsync(address,    
		function(latlng) 
		{
			if (!latlng)
			{
				alert(address + " not found");
			}
			else
			{
				var markerimg = "http://ss12.info/image/sponsor/logo_projpos_large.jpg";
				var icon = new GIcon(G_DEFAULT_ICON, markerimg);
				icon.iconSize=new GSize(25,25);
				icon.iconAnchor=new GPoint(14,40);
				icon.shadowSize=new GSize(0, 0);
				var marker = new GMarker(latlng, {icon:icon});
				map.addOverlay(marker);
				GEvent.addListener(marker, "click", 
					function()
					{
						marker.openInfoWindowHtml(infoWindow);
					}
				);
			}
		}
	);
}

function doSearch()
{
	var address= document.getElementById('location').value;
	centerAddress(address);
	map.clearOverlays();
	resultsheader.innerHTML="";
	getLocations(address);
	
}

function getLocations(zip)
{
	var url = "http://projectpossibility.org/svn/handicapmapplet/source/data.xml";
	_IG_FetchXmlContent(url,
		function(response)
		{
			var listings = [];
			var locations = response.getElementsByTagName("location");
			for(var i = 0 ; i < locations.length; i++)
			{
				var location = locations.item(i);
				if(location.getAttribute("zip") == zip)
				{
					listings.push(location);
					var contents= formatInfoWindow(location);
					markAddress(contents[0], contents[1]);
				}
			}
			displayListings(listings);
		}
	,{refreshInterval:0});	
}

function formatInfoWindow(location)
{
	var zip = location.getAttribute("zip");
	var streetNum = location.getAttribute("streetNum");
	var street = location.getAttribute("street");
	var city= location.getAttribute("city");
	var state = location.getAttribute("state");
	var address = streetNum + " " + street + " " + city + " " + state + " " + zip;
	var name = location.firstChild.nodeValue;
	var numSpaces = 0;
	var html = "<div>" + 
		name + "<br>" +
		streetNum + " " + street + ", " + "<br>" +
		city + ", " + state + " " + zip + "<br><br>" +
		"Handicap Parking Spaces: " + numSpaces +
		"</div>";
	var returnResults= new Array();
	returnResults[0] = address;
	returnResults[1] = html;
	return returnResults;
}

function displayListings(listings)
{
	var targetDiv = document.getElementById("resultsheader");
	if(!listings || listings.length==0)
	{
		targetDiv += "Sorry, nothing found";
	}
	else
	{
		targetDiv.innerHTML += "Found " + listings.length + " results. <br><br>";
		for(var i = 0 ; i < listings.length ; i++)
		{
			var listing = formatInfoWindow(listings[i]);
			alert(listing[1]);
			targetDiv.innerHTML += listing[1] + "<br>";
		}
	}
}
</script>


]]>
</Content>
</Module>












