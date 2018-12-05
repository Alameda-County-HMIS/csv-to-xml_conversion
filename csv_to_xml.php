<?php

// Map CSV file to array
$rows = array_map('str_getcsv', file('test/client.csv'));
$header = array_shift($rows);
$data = array();
foreach ($rows as $row)
{
	$data[] = array_combine($header, $row);
}

// Process Data if need be
foreach($data AS $key => $val)
{// Processing here
}

 //Creates XML string and XML document using the DOM 
$xml = new DomDocument('1.0', 'UTF-8'); 

//Add Sources node
$Sources = $xml->createElementNS("xmlns:airs=\"http://www.clarityhumanservices.com/schema/6/1/AIRS_3_0_mod.xsd\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:hmis=\"http://www.clarityhumanservices.com/schema/6/1/HUD_HMIS.xsd\"", "hmis:Sources" );
$xml->appendChild($Sources);

//Add Source node
$Source = $xml->createElement("hmis:Source" );
$Sources->appendChild($Source);
$Source->appendChild($xml->createElement("hmis:SourceID","CA-502"));

//Add Export node
$Export = $xml->createElement("hmis:Export" );
$Source->appendChild($Export);
$Export->appendChild($xml->createElement("hmis:ExportID","123"));


// Add child nodes
foreach($data AS $key => $val) 
{	
	$client = $xml->createElement('hmis:Client');
	$Export->appendChild($client);
	$attDateCreated = $xml -> createAttribute('hmis:DateCreated'); $attDateCreated->value = date("Y-m-d\TH:i:s",strtotime($val["DateCreated"]));
	$client->appendChild($attDateCreated);
	$attDateUpdated = $xml -> createAttribute('hmis:DateUpdated'); $attDateUpdated->value = $val["DateUpdated"];
	$client->appendChild($attDateUpdated);
	$attuserID = $xml -> createAttribute('hmis:userID'); $attuserID->value = $val["UserID"];
	$client->appendChild($attuserID);
	
	
	print_r(array_slice($val,19));
	//echo $val["DateCreated"];
	
	
	foreach($val AS $field_name => $field_value) 
	{				
		$field_name = "hmis:" . preg_replace("/[^A-Za-z0-9]/", '', $field_name); // preg_replace has the allowed characters
		$name = $client->appendChild($xml->createElement($field_name,$field_value));

		if($field_name=="hmis:FirstName" or $field_name=="hmis:MiddleName" or $field_name=="hmis:LastName" or $field_name=="hmis:SSN"){		
			$domAttribute = $xml->createAttribute('hashstatus');  $domAttribute->value = '1';
			$name = $name->appendChild($domAttribute);
		} 
		
		if($field_name=="hmis:VeteranStatus"){Break;}
	}
	
	if($val["VeteranStatus"]=="1")
	{
			
		$ClientVeteranStatus = $xml->createElement('hmis:ClientVeteranStatus');
		$Export->appendChild($ClientVeteranStatus);
		$attDateCreated = $xml -> createAttribute('hmis:DateCreated'); $attDateCreated->value = date("Y-m-d\TH:i:s",strtotime($val["DateCreated"]));
		$ClientVeteranStatus->appendChild($attDateCreated);
		$attDateUpdated = $xml -> createAttribute('hmis:DateUpdated'); $attDateUpdated->value = $val["DateUpdated"];
		$ClientVeteranStatus->appendChild($attDateUpdated);
		$attuserID = $xml -> createAttribute('hmis:userID'); $attuserID->value = $val["UserID"];
		$ClientVeteranStatus->appendChild($attuserID);

		$name = $ClientVeteranStatus->appendChild($xml->createElement("hmis:ClientVeteranInfoID",$val["PersonalID"]."vet"));
		$name = $ClientVeteranStatus->appendChild($xml->createElement("hmis:PersonalID",$val["PersonalID"]));
		foreach(array_slice($val,19) AS $field_name => $field_value) 
		{				
			$field_name = "hmis:" . $field_name;
			$name = $ClientVeteranStatus->appendChild($xml->createElement($field_name,$field_value));
		
			if($field_name=="hmis:DischargeStatus"){Break;}
		}
	}

}

// Set the formatOutput attribute of xml to true
$xml->formatOutput = true; 

// Output to screen
//header('Content-Type: text/xml');
//echo $xml->saveXML();

// Save as file
$xml->save('xml-import.xml'); // save as file

?>