<?php
require_once("../crawler_classes.php");

//$crawler->enable_delay_between_requests(5,15);
//$crawler->use_cookies(true);
//$crawler->clean_cookies();
//$crawler->use_gzip(false);

$startPages[STATUS_FORSELL] = array
(
    TYPE_HOUSE        =>  array
    (
        "http://www.immolivit.be/index.php/te-koop/woningen"
    ),
    TYPE_APARTMENT    =>  array
    (
        "http://www.immolivit.be/index.php/te-koop/appartementen"
    ),
    TYPE_COMMERCIAL   =>  array
    (
        "http://www.immolivit.be/index.php/te-koop/gebouwen/commerciele-gebouwen",
        "http://www.immolivit.be/index.php/te-koop/gebouwen/industriele-gebouwen",
        "http://www.immolivit.be/index.php/te-koop/gebouwen/kantoorgebouwen"
    ),
    TYPE_PLOT         =>  array
    (
        "http://www.immolivit.be/index.php/te-koop/bouwgronden"
    ),
    TYPE_GARAGE       =>  array
    (
        "http://www.immolivit.be/index.php/te-koop/garages"
    ),
);

$startPages[STATUS_TORENT] = array
(
    TYPE_HOUSE        =>  array
    (
        "http://www.immolivit.be/index.php/te-huur/woningen"
    ),
    TYPE_APARTMENT    =>  array
    (
        "http://www.immolivit.be/index.php/te-huur/appartementen"
    ),
    TYPE_COMMERCIAL   =>  array
    (
        "http://www.immolivit.be/index.php/te-huur/gebouwen/commerciele-gebouwen",
        "http://www.immolivit.be/index.php/te-huur/gebouwen/industriele-gebouwen",
        "http://www.immolivit.be/index.php/te-huur/gebouwen/kantoorgebouwen"
    ),
    TYPE_GARAGE       =>  array
    (
        "http://www.immolivit.be/index.php/te-huur/garages"
    ),
);


CrawlerTool::startXML();

foreach($startPages as $status => $types)
{
    foreach($types as $type => $pages)
    {
        foreach($pages as $page)
        {
            $html = $crawler->request($page);
            processPage($crawler, $status, $type, $html);
        }
    }
}

CrawlerTool::endXML();

echo "<br /><b>Completed!!</b>";


function processPage($crawler, $status, $type, $html)
{
    static $propertyCount = 0;
    static $properties = array();

    $parser = new PageParser($html);

    $nodes = $parser->getNodes("h3[@class = 'catItemTitle']/a");

    $items = array();
	foreach($nodes as $node)
    {
        $property = array();
        $property[TAG_STATUS] = $status;
        $property[TAG_TYPE] = $type;
        $property[TAG_UNIQUE_URL_NL] = "http://www.immolivit.be" . $parser->getAttr($node, "href");
        $property[TAG_UNIQUE_ID] =  CrawlerTool::generateId($property[TAG_UNIQUE_URL_NL]);
        if(preg_match("/\ste\s(.*)/", $parser->getText($node), $match))
        {
            $property[TAG_CITY] = trim(preg_replace("/\(.*\)|\d|(.*\ste\s)/", "", $match[1]));
        }

        if(in_array($property[TAG_UNIQUE_ID], $properties)) continue;
        $properties[] = $property[TAG_UNIQUE_ID];

        $items[] = array("item"=>$property, "itemUrl" => $property[TAG_UNIQUE_URL_NL]);
	}

    foreach($items as $item)
    {
        // keep track of number of properties processed
        $propertyCount += 1;

        // process item to obtain detail information
        echo "--------- Processing property #$propertyCount ...";
        processItem($crawler, $item["item"], $crawler->request($item["itemUrl"]));
        echo "--------- Completed<br />";
    }

    return sizeof($items);
}

/**
 * Download and extract item detail information
 */
function processItem($crawler, $property, $html)
{
    $parser = new PageParser($html);
    $parser->deleteTags(array("script", "style"));

    $property[TAG_TEXT_SHORT_DESC_NL] = $parser->extract_xpath("div[@class = 'itemFullText']");
    $property[TAG_PLAIN_TEXT_ALL_NL] = $parser->extract_xpath("div[@class = 'itemBody']", RETURN_TYPE_TEXT_ALL);
    $property[TAG_PICTURES] = $parser->extract_xpath("a[contains(@rel, 'lightbox[gallery')]/@href | img[@class = 'border floatleft']/@src", RETURN_TYPE_ARRAY, function($pics)
    {
        $picUrls = array();
        foreach($pics as $pic)
        {
            if(!empty($pic)) $picUrls[] = array(TAG_PICTURE_URL => "http://www.immolivit.be/" . str_replace("http://www.immolivit.be/", "", $pic));
        }

        return $picUrls;
    });

    if(preg_match("/address\s*=\s*\"(.*),(.*),\s*Belgi/", $html, $match))
    {
        CrawlerTool::parseAddress($match[1], $property);
        $property[TAG_CITY] = trim(preg_replace("/\(.*\)|\d/", "", $match[2]));
    }

    $property[TAG_PRICE] = $parser->extract_xpath("strong[contains(text(), 'prijs:')]", RETURN_TYPE_NUMBER);

    $parser->setQueryTemplate("span[contains(text(), '" . XPATH_QUERY_TEMPLATE . "')]/following-sibling::span[1]");

    $property[TAG_IS_NEW_CONSTRUCTION] = CrawlerTool::isNewConstruction($parser->extract_xpath("Nieuwbouw"));
    $property[TAG_EPC_VALUE] = $parser->extract_regex("/(\d+)\sKwh/", RETURN_TYPE_EPC);
    $property[TAG_EPC_CERTIFICATE_NUMBER] = $parser->extract_regex("/UC\s(\d+)/", RETURN_TYPE_EPC);
    $property[TAG_KI] = $parser->extract_xpath("Kadastraal inkomen", RETURN_TYPE_NUMBER);
    $property[TAG_CONSTRUCTION_YEAR] = $parser->extract_xpath("Bouwjaar", RETURN_TYPE_YEAR);
    $property[TAG_SURFACE_LIVING_AREA] = $parser->extract_xpath("Bewoonbare oppervlakte", RETURN_TYPE_NUMBER);
    $property[TAG_SURFACE_GROUND] = $parser->extract_xpath("Oppervlakte perceel", RETURN_TYPE_NUMBER);
    if(empty($property[TAG_SURFACE_GROUND])) {
        $surface = CrawlerTool::toMeter($property[TAG_TEXT_SHORT_DESC_NL]);
        if($surface > 0) $property[TAG_SURFACE_GROUND] = $surface;
    }
    $property[TAG_LOT_WIDTH] = $parser->extract_xpath("Breedte perceel", RETURN_TYPE_NUMBER);
    $property[TAG_LOT_DEPTH] = $parser->extract_xpath("Diepte perceel", RETURN_TYPE_NUMBER);
    $property[TAG_FRONTAGE_WIDTH] = $parser->extract_xpath("Breedte gevel", RETURN_TYPE_NUMBER);
    $property[TAG_DEPTH_GROUND_FLOOR] = $parser->extract_xpath("Bouwdiepte gelijkgrondse verdieping", RETURN_TYPE_NUMBER);
    $property[TAG_DEPTH_FLOOR] = $parser->extract_xpath("Bouwdiepte verdieping", RETURN_TYPE_NUMBER);
    $property[TAG_BEDROOMS_TOTAL] = $parser->extract_xpath("Aantal slaapkamers", RETURN_TYPE_NUMBER);
    $property[TAG_BATHROOMS_TOTAL] = $parser->extract_xpath("Aantal badkamers", RETURN_TYPE_NUMBER);
    $property[TAG_GARAGES_TOTAL] = $parser->extract_xpath("Garage", RETURN_TYPE_NUMBER);
    $property[TAG_HEATING_NL] = $parser->extract_xpath("Verwarmingsbron");
    $property[TAG_FLOOR] = $parser->extract_xpath("Verdieping", RETURN_TYPE_NUMBER);
    $property[TAG_PLANNING_PERMISSION] = $parser->extract_xpath("Stedenbouwkundige vergunning verkregen") === "Ja" ? 1 : 0;

    // WRITING item data to output.xml file
    CrawlerTool::saveProperty($property);
}