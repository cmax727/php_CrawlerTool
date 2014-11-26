<?php
/**
* This file is created by sasya8080 for test purpose
* 
* @since        06/08/2012
* @filesource   sasya.php
* @testurl      http://www.vastgoedvanhoof.be
*/
require_once("../crawler_classes.php");

error_reporting(0);

$crawler->set_script_dir(realpath(dirname(__FILE__)).'/');

function outfile($fname, $content){
    $f = fopen($fname, "rw");
    fwrite($f, $content);
    fclose($f);
}
function loadf($file, &$var){
    $var = file_get_contents($fname);
}

class Funcs{
    public static function getPermission($perm){
        if ( $perm == "neen"){
            return 1;   
        }else if ( $perm == "ja"){
            return 2;
        }else{//if ( $perm == "niet gevuld"){
            return 3;
        }
    }

    public static function getOrientation($ori){
        $ori = strtolower($ori);
        $oriMap = array(
            'oost' =>"E",
            'west' =>"W",
            'noord' =>"N",
            'zuiden' =>"S",
            'noordoosten' =>"NE" ,
            'noordwesten' =>"NW",
            'zuidoost' => "SE" ,
            'zuidwest' => "SW",
        );
        
        return $oriMap[$ori];
        
    }
}

$startPages[STATUS_FORSELL] = array
(
//'http://www.vastgoedvanhoof.be/default.php?page=te%20koop' => TYPE_NONE,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=1&selType1=1" => TYPE_APARTMENT,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=1&selType1=4" => TYPE_COMMERCIAL,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=1&selType1=5" => TYPE_GARAGE,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=1&selType1=2" => TYPE_GARAGE, // TYPE_GROND,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=1&selType1=3" => TYPE_HOUSE,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=1&selType1=9" => TYPE_PLOT,  // TYPE_PROJECT,

//'http://www.dirkwillemyns.be/nl/vastgoed/handelspanden' => TYPE_COMMERCIAL,
);


$startPages[STATUS_FORRENT] = array
(
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=2&selType1=1" => TYPE_PLOT,  // TYPE_PROJECT,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=2&selType1=4" => TYPE_APARTMENT,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=2&selType1=5" => TYPE_COMMERCIAL,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=2&selType1=2" => TYPE_GARAGE,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=2&selType1=3" => TYPE_GARAGE, // TYPE_GROND,
"http://www.vastgoedvanhoof.be/default.php?page=zoekresultaat&selActie=2&selType1=9" => TYPE_HOUSE,
);


CrawlerTool::startXML();



foreach($startPages as $status => $types)
{
	foreach($types as $page_url => $type)
	{
		
       $html = $crawler->request($page_url);
       //$html = include "b.txt";

        
		processPage($crawler, $status, $type, $html);
	}
}
      //Element[@attribute1="abc" and @attribute2="xyz" and text()="Data"]
CrawlerTool::endXML();

echo "<br /><b>Completed!!</b>";

function processPage($crawler, $status, $type, $html)
{
	static $propertyCount = 0;
	static $properties    = array();

	$parser = new PageParser($html);
	preg_match_all('#<a href="(default.php\?page=detail\&id=(\d+)[^\"<>]*?title=([^\"]*?))"[^><]*?>meer info...<\/a>#s',$html,$res1,PREG_SET_ORDER);
    $parser->extract_xpath('//a[text()="meer info..." and count(*)=0]', RETURN_TYPE_ARRAY);
	$items = array();
	foreach($res1 as $arr)
	{
		$price = (preg_match('!&euro;&nbsp;([0-9.,]+)!',$arr[2],$res)) ? CrawlerTool::toNumber($res[1]) : '';

		$property                         = array();
        
        /* general - uniq id */
		$property[TAG_UNIQUE_ID]          = $arr[2];
		/* general - uniq url */
        $property[TAG_UNIQUE_URL_NL]      = "http://www.vastgoedvanhoof.be/${arr[1]}";
        /* general - status */
        $property[TAG_STATUS]             = $status;
        /* general - type */
        $property[TAG_TYPE]               = $type;
        /* text - title */
        $property[TAG_TEXT_TITLE_NL]      = $arr[3];
        
		if(in_array($property[TAG_UNIQUE_ID], $properties)) continue;
		$properties[] = $property[TAG_UNIQUE_ID];
		$items[]  = $property;
	}


	foreach($items as $item)
	{
		$propertyCount += 1;
		echo "Processing property #$propertyCount<br>\r\n";
		processItem($crawler, $item);
        
	}

	return sizeof($items);
}


function processItem($crawler, $property)
{
	$html = $crawler->request($property[TAG_UNIQUE_URL_NL]);
    //$html = include "a.txt";

	$parser = new PageParser($html);
    
    /* general - TYPE */
    /*
    $value="";
    $property[TAG_TYPE_LONG] = '';
    $property[TAG_TYPE] = CrawlerTool::getPropertyType("");
    */
	//$parser->deleteTags(array("script", "style"));
    
    //detail information section
    $cection = $parser->getNode('/html/body/center/table/tr[3]/td/div[4]/table/tr/td[2]');
    $cection = $cection->ownerDocument->saveXML($cection);
             
    /* geo - street */
        //$value = $parser->extract_regex('#<h2 class="titZoekresultaat2">Geografische ligging</h1>(.+?) (\d+?)<br#s', RETURN_TYPE_ARRAY); 
    preg_match('#<h2 class="titZoekresultaat2">Geografische ligging</h1>(.*?)<br#s', $html, $value);                                       
    $property[TAG_STREET] = trim($value[1]);
    preg_match('#(d+)#s', $value, $value2);                                       
    $property[TAG_NUMBER] =  $value2[1];
    
    /* geo - box number*/
    $property[TAG_BOX_NUMBER] = "";
    
    /* geo - city & zip*/
    preg_match('#<h2 class="titZoekresultaat2">Geografische ligging</h1>.*?<br\/>(\d+) (.*?)<#s', $html, $value);                                       
    $property[TAG_ZIP] = $value[1];
    $property[TAG_CITY] = trim($value[2]);
    
    /* geo - country & address_visible & number_visible */
    $property[TAG_COUNTRY] = "Dutch";
    $property[TAG_ADDRESS_VISIBLE] = "";
    $property[TAG_NUMBER_VISIBLE] = "";
    
    /* geo - latitude & longitude */
    $latlang = preg_match("#latLng\: \[([\d\.]*?),([\d\.]*?)\]#s", $html, $res );  
    $property[TAG_LATITUDE]     = $res[1];
    $property[TAG_LONGITUDE]    = $res[2];
    
     /* media - pictures */
    $arr = $parser->extract_xpath('/html/body/center/table/tr[3]/td/table[1]/tr[1]/td[2]/img[@title]', RETURN_TYPE_ARRAY);
    $imgArr = array();
    foreach ( $arr as $v){
        $imgArr[] = "http://www.vastgoedvanhoof.be".$v;
    }
    $property[TAG_PICTURES]            = CrawlerTool::addTextToPicUrls($imgArr,'');
    
    /* general - uniq id */
    /* general - uniq url */
    /* general - status */
    
    /* text - title & desc */
    $val2 = $parser->extract_xpath('/html/body/center/table/tr[3]/td/table[2]/tr/td[2]'); 
    $val1 = $parser->extract_xpath('/html/body/center/table/tr[3]/td/table[2]/tr/td[2]/div');
    $value = substr($val2, strlen($val1));
    $property[TAG_TEXT_SHORT_DESC_NL]  = $value;
    
     
    /* construction : con-type */ 
    //$value = $parser->extract_xpath('/html/body/center/table/tr[3]/td/div[4]/table/tr/td[2]/table[2]/tr[1]/td[2]'); 
    $value = $parser->extract_xpath('//td[text()="Type"]/following-sibling::td[1]'); 
    $property[TAG_CONSTRUCTION_TYPE]     = CrawlerTool::getConstructionType($value);
    
    /* construction : con-year */ 
    //$value = $parser->extract_xpath('/html/body/center/table/tr[3]/td/div[4]/table/tr/td[2]/table[2]/tr[4]/td[2]'); 
    $value = $parser->extract_xpath('//td[text()="Bouwjaar"]/following-sibling::td[1]'); 
    $property[TAG_CONSTRUCTION_YEAR]    =$value;
        
    /* construction : bedroom-total*/ 
    //$value = $parser->extract_xpath('/html/body/center/table/tr[3]/td/div[4]/table/tr/td[2]/table[4]/tr/td[2]'); 
    $value = $parser->extract_xpath('//td[text()="Aantal slaapkamers"]/following-sibling::td[1]'); 
    $property[TAG_BEDROOMS_TOTAL]    = $value;
    
    /* construction : toilet-total */ 
    $value = "";
    $property[TAG_TOILETS_TOTAL]    = $value;
    
    /* construction : toilet-desc */ 
    //$value = $parser->extract_xpath('/html/body/center/table/tr[3]/td/div[4]/table/tr/td[2]/table[5]/tr[12]/td[2]'); 
    $value = $parser->extract_xpath('//td[text()="Toilet"]/following-sibling::td[1]');
    $property[TAG_TOILET_DESC_NL]    = $value;
    
    /* construction : con-is-new */                                               
    //$value = $parser->extract_xpath('/html/body/center/table/tr[3]/td/div[4]/table/tr/td[2]/table[2]/tr[3]/td[2]'); 
    $value = $parser->extract_xpath('//td[text()="Nieuwbouw"]/following-sibling::td[1]');
    $property[TAG_IS_NEW_CONSTRUCTION]   = CrawlerTool::isNewConstruction($value);
    
    /* construction : con-is-investment-property*/ 
    $property[TAG_IS_INVESTMENT_PROPERTY]   = "";
    
    /* construction : surface-area*/                  
    $value = $parser->extract_xpath('//td[text()="Grondoppervlakte"]/following-sibling::td[1]');
    $property[TAG_SURFACE_GROUND]   = $value;
    
    /* construction : surface-area*/ 
    $value = $parser->extract_xpath('//td[text()="Bouwoppervlakte"]/following-sibling::td[1]');
    $property[TAG_SURFACE_CONSTRUCTION]   = $value;
    
    /* construction : leaving-area*/ 
    $value = $parser->extract_xpath('//td[text()="Woonkamer"]/following-sibling::td[1]');
    $property[TAG_SURFACE_LIVING_AREA]   = $value;
    
    /* construction : lot-width*/ 
    $property[TAG_LOT_WIDTH]            = "";
    $property[TAG_FRONTAGE_WIDTH]       = "";
    $property[TAG_FLOOR]                = "";
    $property[TAG_AMOUNT_OF_FLOORS]     = "";
    $property[TAG_AMOUNT_OF_FACADES]    = "";
    
    /* certifications : EPC value */
    $value = $parser->extract_xpath('//td[text()="EPC"]/following-sibling::td[1]'); 
    $property[TAG_EPC_VALUE]   = $value ;
    
    /* planing : has-permission */ 
    $value = $parser->extract_xpath('//td[text()="Stedenbouwkundige vergunning"]/following-sibling::td[1]');
    $property[TAG_PLANNING_PERMISSION]   = Funcs::getPermission($value);
    /* planing : permission-info */ 
    $property[TAG_PLANNING_PERMISSION_INFORMATION_NL]   = "";
    
    /* planing : has-permission */ 
    $property[TAG_HAS_PROCEEDING]   = "";
    
    /* planing : has-priority-purchase */
    $property[TAG_PRIORITY_PURCHASE]   = "";
    $property[TAG_PRIORITY_PURCHASE_INFORMATION_NL]     = "";
    
    /* planing : subdivision-permitted*/ 
    $value = $parser->extract_xpath('//td[text()="Verkavelingsvergunning"]/following-sibling::td[1]');
    $property[TAG_SUBDIVISION_PERMIT]   = $value;
    
    $property[TAG_SUBDIVISION_INFORMATION_NL]           = "";
    
    /* planing : most-recent-dest*/ 
    $value = $parser->extract_xpath('//td[text()="Meest recente bestemming"]/following-sibling::td[1]');
    $property[TAG_MOST_RECENT_DESTINATION]   = $value;
    
    $property[TAG_MOST_RECENT_DESTINATION_INFORMATION_NL]   = "";
    
    /* financial : price*/ 
    $value = $parser->extract_regex('#<td>Prijs<\/td>.*?<td>(.*?)<\/td>#s');
    $value = CrawlerTool::toNumber( $parser->extract_xpath('/html/body/center/table/tr[3]/td/table[2]/tr/td[2]/div/div[2]'));
    $property[TAG_PRICE]   = $value;
    
    $property[TAG_PRICE_VISIBLE]                            = "";
    
    $value = $parser->extract_xpath('//td[text()="Kadastraal inkomen"]/following-sibling::td[1]');
    $property[TAG_KI]   = CrawlerTool::toNumber($value);
    
    /* comfort : heating*/ 
    $value = $parser->extract_xpath('//td[text()="Verwarming:"]/following-sibling::td[1]');
    $property[TAG_HEATING_NL]                = $value; 
    
    /* other : garden*/
    $value = $parser->extract_xpath('//td[text()="Tuin"]/following-sibling::td[1]');             
    $property[TAG_GARDEN_AVAILABLE]   = empty($value) ;//= $value
    
    $property[TAG_GARDEN_DESC_NL]                           = "";
    
    $value = $parser->extract_regex('#<td>Ori..ntatie tuin<\/td>.*?<td>(.*?)<\/td>#s');
    $property[TAG_GARDEN_ORIENTATION]   = Funcs::getOrientation($value);
    
    /* other : garages*/
    $property[TAG_GARAGES_TOTAL]                       ="";
	
    
	CrawlerTool::saveProperty($property);
    
 /*
 $ff = fopen('a.txt', "w");

fwrite( $ff, $c)

fclose($ff)


*/
}