<?php
require_once("crawler_classes.php");
$crawler->set_script_dir(realpath(dirname(__FILE__)).'/');


$startPages[STATUS_FORSELL] = array
(
'http://www.dirkwillemyns.be/nl/vastgoed/te-koop' => '',
'http://www.dirkwillemyns.be/nl/vastgoed/handelspanden' => TYPE_COMMERCIAL,
);

$startPages[STATUS_TORENT] = array
(
'http://www.dirkwillemyns.be/nl/vastgoed/te-huur' => '',
);


CrawlerTool::startXML();

foreach($startPages as $status => $types)
{     
	foreach($types as $page_url => $type)
	{
		$html = $crawler->request($page_url);
		processPage($crawler, $status, $type, $html);
    
	}

}

CrawlerTool::endXML();

echo "<br /><b>Completed!!</b>";

function processPage($crawler, $status, $type, $html)
{
	static $propertyCount = 0;
	static $properties    = array();

	$parser = new PageParser($html);
	preg_match_all('!<div class="maincell">\s+<h3>([^<>]+)</h3>.*?<p class="info">(.*?)<a href="/nl/vastgoed/detail/(\d+)"!s',$html,$res1,PREG_SET_ORDER);

	$items = array();
	foreach($res1 as $arr)
	{
		$price = (preg_match('!&euro;&nbsp;([0-9.,]+)!',$arr[2],$res)) ? CrawlerTool::toNumber($res[1]) : '';

		$property                         = array();
		$property[TAG_UNIQUE_ID]          = $arr[3];
		$property[TAG_UNIQUE_URL_NL]      = 'http://www.dirkwillemyns.be/nl/vastgoed/detail/'.$arr[3];
		$property[TAG_STATUS]             = $status;
		
		if(in_array($property[TAG_UNIQUE_ID], $properties)) continue;
		$properties[] = $property[TAG_UNIQUE_ID];
		$items[]  = $property;
	}

   // return;
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
//	$html = $crawler->request($property[TAG_UNIQUE_URL_NL]);
$html = <<<ABC

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />



<title>goed onderhouden bel-ÃƒÂ©tagewoning met tuin te Aartselaar</title>
<link href="wvh.css" rel="stylesheet" type="text/css" />   
<!-- include jQuery library -->
<script type="text/javascript" src="js/jquery-1.7.2.js"></script>
<!-- include Cycle plugin -->
<script type="text/javascript" src="js/jquery.cycle.all.js"></script>       
<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
<script type="text/javascript" src="js/gmap3.js"></script> 
<script type="text/javascript">
$(document).ready(function() {
    $('.slideshow').cycle({
        fx: 'fade', // choose your transition type, ex: fade, scrollUp, shuffle, etc...,
        speed:  2500 
    });
});

function changeFoto(id)
{
    if (document.images)
    { 
    obj = document.images['fotoGroot']; 
    obj.src = "http://www.vastgoedvanhoof.be//picture_library/"+id;
    }
}
function changeFotoStyling(id)
{
    if (document.images)
    { 
    obj = document.images['fotoGroot']; 
    obj.src = id;
    }
}

var arrSubTypes = new Array();
 
arrSubTypes[0] = {
    ID:12,
    name:'Appartement - Appartement',
    parent:'1'
    };
 
arrSubTypes[1] = {
    ID:45,
    name:'Appartement - Dakappartement / Penthouse',
    parent:'1'
    };
 
arrSubTypes[2] = {
    ID:13,
    name:'Appartement - Duplex',
    parent:'1'
    };
 
arrSubTypes[3] = {
    ID:35,
    name:'Appartement - Gelijkvloersappartement',
    parent:'1'
    };
 
arrSubTypes[4] = {
    ID:14,
    name:'Appartement - Loft',
    parent:'1'
    };
 
arrSubTypes[5] = {
    ID:16,
    name:'Appartement - Studio',
    parent:'1'
    };
 
arrSubTypes[6] = {
    ID:59,
    name:'Opbrengsteigendom - Appartementsgebouw',
    parent:'1'
    };
 
arrSubTypes[7] = {
    ID:10,
    name:'Commercieel - Handelspand / Bedrijfsgebouw',
    parent:'4'
    };
 
arrSubTypes[8] = {
    ID:58,
    name:'Commercieel - Kantoor / Praktijk met woonst',
    parent:'4'
    };
 
arrSubTypes[9] = {
    ID:23,
    name:'Commercieel - Kantoorruimte / Praktijkruimte',
    parent:'4'
    };
 
arrSubTypes[10] = {
    ID:22,
    name:'Commercieel - Magazijn',
    parent:'4'
    };
 
arrSubTypes[11] = {
    ID:63,
    name:'Opbrengsteigendom - Handelspand / Bedrijfsgebouw',
    parent:'4'
    };
 
arrSubTypes[12] = {
    ID:32,
    name:'Garage - Garagebox',
    parent:'5'
    };
 
arrSubTypes[13] = {
    ID:39,
    name:'Garage - Garagestaanplaats',
    parent:'5'
    };
 
arrSubTypes[14] = {
    ID:17,
    name:'Grond - Bos / Park',
    parent:'2'
    };
 
arrSubTypes[15] = {
    ID:24,
    name:'Grond - Bouwgrond',
    parent:'2'
    };
 
arrSubTypes[16] = {
    ID:19,
    name:'Grond - Landbouw',
    parent:'2'
    };
 
arrSubTypes[17] = {
    ID:57,
    name:'Grond - Projectgrond',
    parent:'2'
    };
 
arrSubTypes[18] = {
    ID:18,
    name:'Grond - Recreatiegrond',
    parent:'2'
    };
 
arrSubTypes[19] = {
    ID:29,
    name:'Woning - Bel-etage',
    parent:'3'
    };
 
arrSubTypes[20] = {
    ID:5,
    name:'Woning - Boerderij / Hoeve',
    parent:'3'
    };
 
arrSubTypes[21] = {
    ID:25,
    name:'Woning - Fermette',
    parent:'3'
    };
 
arrSubTypes[22] = {
    ID:7,
    name:'Woning - Herenhuis',
    parent:'3'
    };
 
arrSubTypes[23] = {
    ID:46,
    name:'Woning - HOB',
    parent:'3'
    };
 
arrSubTypes[24] = {
    ID:41,
    name:'Woning - Rijwoning',
    parent:'3'
    };
 
arrSubTypes[25] = {
    ID:79,
    name:'Woning - uitzonderlijke woning',
    parent:'3'
    };
 
arrSubTypes[26] = {
    ID:47,
    name:'Woning - Villa',
    parent:'3'
    };
 
arrSubTypes[27] = {
    ID:26,
    name:'Woning - Woning',
    parent:'3'
    };
 
arrSubTypes[28] = {
    ID:21,
    name:'Project',
    parent:'9'
    };
    
function fillSelType(vWaarde, divNaam){
vFound = 0;
selType2="";

vStringSel2 = '<select name="selType2" id="selType2" style="width:125px; height:15px;">'
    + '<option></option>';
//vul dropdownlist met businesslines
    for (i=0;i<arrSubTypes.length;i++)
        {
        if(arrSubTypes[i].parent==vWaarde){
            vStringSel2 += '<option value="'+arrSubTypes[i].ID+'"'
            if(selType2 == arrSubTypes[i].ID){            
                vStringSel2 += ' selected ';
                }
            vStringSel2 += '>'+arrSubTypes[i].name+'</option>';
            vFound = 1;
            }
        }
vStringSel2 += "</select>"

if(vFound == 1){document.getElementById(divNaam).innerHTML = vStringSel2;}
else{document.getElementById(divNaam).innerHTML ='';}
}
        
</script>
<style>
  .gmap3{
    border: 1px dashed #C0C0C0;
    margin-left:5px;
    width: 535px;
    height: 500px;
  }
</style>
</head>

<body>
<center>
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td rowspan="4" style="width:211px; text-align:left;" valign="top">
<div style="width:170px; height:206px; background-color:#003B79; margin-top:20px; padding-left:12px;  padding-right:8px; padding-top:5px; padding-bottom:5px;">

<form action="default.php" name="snelZoekForm" method="get">
<input type="hidden" name="page" id="page" value="zoekresultaat"/>
<table>
<tr>
<td class="snelZoekenTitel" colspan="2">Snel zoeken</td>
<td class="uitgebreidZoekenTitel"><a href="default.php?page=uitgebreidzoeken" style="color:#FFFFFF; text-decoration:none;">Uitgebreid</a></td> 
</tr>
<tr><td colspan="3" style="height:15px;">&nbsp;</td></tr>
<tr>
    <td class="snelZoeken" style="height:15px;">Ik wil</td>
    <td class="snelZoeken"  colspan="2">
        <select name="selActie" style="width:125px; height:18px;">
            <option value="1"
                >Kopen</option>
            <option value="2"
                >Huren</option>
        </select>
    </td>
</tr>
<tr style="height:25px;">
    <td class="snelZoeken" style="vertical-align:top;" valign="top">Type</td>
    <td class="snelZoeken" colspan="2">    
    <select name="selType1" id="selType1" style="width:125px; height:18px;" >
        <option selected="selected"></option>
                    <option value="1" >Appartement</option>
                    <option value="4" >Commercieel</option>
                    <option value="5" >Garage</option>
                    <option value="2" >Grond</option>
                    <option value="3" >Huis</option>
                    <option value="9" >Project</option>
            </select>
    
    <span id="divSelType2" name="divSelType2" style="margin-top:0px;"></span>
    <script language="javascript">
    //fillSelType(document.getElementById('selType1').value, 'divSelType2');
    </script>
    </td>
</tr>
<tr>
    <td class="snelZoeken" style="height:25px;">Prijs</td>
    <td class="snelZoeken"><input name="txtMinPrijs" type="text" value="" style="width:55px; height:10px;"  /></td>
    <td class="snelZoeken"><input name="txtMaxPrijs" type="text" value="" style="width:55px; height:10px;"  /></td>
</tr>
<tr>
    <td class="snelZoeken" style="height:25px;">In</td>
    <td class="snelZoeken" colspan="2">
        <input name="txtGemeente" id="txtGemeente" type="text" value="" style="width:119px; height:10px;"  />
    </td>
</tr>
</table>
<p style="margin-top:25px;">
<input type="image" name="zoek" id="zoek" src="img/btnZoek.png" value="Submit" alt="Submit" align="middle"/>
</p>
</form>

</div>
<div style="width:190px; height:324px; background-color:#FFFFFF; margin-top:20px;">
<div style="margin:10px;">
<h1 class="titPandenInDeKijker">Panden in de kijker</h1>
        <a href="default.php?page=detail&id=1366&title=<P>Volledig Gerenoveerd Instapklaar Appartement</P> te Antwerpen (2020)" style="color:#87888A;">
        <div style="height:145px; width:170px;">
            <div style="position: absolute;"><img src="picture_library/805391/3027001614000000320.jpg" width="170" height="110" border="0"/></div>
            <div style="position: absolute; background-color:#4B6599; color:#FFFFFF; padding:2px; font-size:11px;">Antwerpen (2020)</div>
            <div style="position: absolute; margin-top:115px; font-size:11px; width:170px;">meer info...</div>
        </div>
        </a>
    
            <a href="default.php?page=detail&id=1309&title=Ruim vrijstaande ééngezinswoning met 4 slpk, bureau en tuin te Edegem" style="color:#87888A;">
        <div style="height:145px; width:170px;">
            <div style="position: absolute;"><img src="picture_library/806005/3027001614000000470.jpg" width="170" height="110" border="0"/></div>
            <div style="position: absolute; background-color:#4B6599; color:#FFFFFF; padding:2px; font-size:11px;">Edegem</div>
            <div style="position: absolute; margin-top:115px; font-size:11px; width:170px;">meer info...</div>
        </div>
        </a>
    
    </div>
</div>
</td>
<td valign="top" style="width:595px; height:108px;" height="108"><img src="img/logoHeader.gif" alt="vastgoed van Hoof - zonder meer een zorg minder" width="595" height="94" /></td>
<td rowspan="4" style="width:211px;" align="right" valign="top">
<div style="width:190px; height:216px; background-color:#D9DADB; right:0px; text-align:left; color:#87888A; margin-top:20px;">
<div style="width:157px; margin:15px;">
<h2 class="titVastgoedSchatten">Vastgoed naar
waarde schatten</h2>
<p class="parVastgoedSchatten">
Een correcte prijs voor
je pand krijg je niet
zomaar. Wij schatten
uw pand gratis en
vrijblijvend ...</p>
<p style="margin-top:22px;"><a href="default.php?page=schatting"><img src="img/btnSchatting.png" alt="Gratis schatting" width="157" height="24" border="0" /></a></p>
</div>
</div>
<div style="width:190px; height:324px; background-color:#FFFFFF; margin-top:20px; padding:0px;">
<a href="default.php?page=styling"><img src="img/btnStyling.png" alt="Styling - Hoe kan een woning eruitzien en wat zijn de mogelijke kosten ?" width="189" height="162" border="0"/></a><br/>
<a href="http://www.hetlandgoed.be/landgoed/" target="_blank"><img src="img/btnNieuwBouw.png" alt="Nieuwbouw - Wilt u genieten van de luxe van een nieuwbouw" width="189" height="162"  border="0"/></a></div>
</td>
</tr>
<tr>
<td style="height:61px; width:546px;" align="left" valign="top">
<div  style="padding-left:20px; color:#FFFFFF; font-size:14px; margin-top:15px;">
<a href="default.php?page=home" class="linksUp">HOME</a> | 
<a href="default.php?page=kantoor" class="linksUp">OVER ONS</a> | 
<a href="default.php?page=te koop" class="linksUp">TE KOOP</a> | 
<a href="default.php?page=te huur" class="linksUp">TE HUUR</a> | 
<a href="default.php?page=nieuws" class="linksUp">NIEUWS</a> | 
<a href="default.php?page=contact" class="linksUp">CONTACT</a>
</div></td>
</tr>
<tr>
<td style="background-color:#FFFFFF; padding:25px; width:546px;" align="left" valign="top">
<div style="position:relative; left:148px;  font-size:14px; margin-top:-18px; margin-bottom:-5px; width:346px; text-align:left;">Detailfiche</div>


    <br/>
      <div style="background-color:#4B6599; color:#FFFFFF; margin-bottom:10px; text-align:left; width:546px;">
        <table width="546" style="width:546px;">
        <tr>
        <td align="left">Aartselaar</td>
        <td style="text-align:right;">Oudestraat 51</td>
        </tr>
        </table>
      </div>
      <br/>
        <table width="546" style="width:546px;">
        <tr>
        <td rowspan="2" align="left">
            <table style="width:374px; height:374px;">
            <tr>
            <td valign="middle" align="center" style="text-align:center; vertical-align:middle; width:374px; height:374px; background-color:#ECEDED;">
               <img src="picture_library/805715/3027001614000000416.jpg" style="max-width:360px; max-height:360px;" id="fotoGroot" name="fotoGroot"/>
            </td>
            </tr>
            </table>
        </td>
        <td style="text-align:left; width:172px;"><img src="picture_library/805715/3027001614000000416.jpg" alt="Exterieur" title="Exterieur" width='76' height='49' style='margin:2px; cursor:pointer;' onclick='changeFotoStyling(this.src)'  onmouseover='changeFotoStyling(this.src)'/><img src="picture_library/805715/3027001614000000421.jpg" alt="Interieur" title="Interieur" width='76' height='49' style='margin:2px; cursor:pointer;' onclick='changeFotoStyling(this.src)'  onmouseover='changeFotoStyling(this.src)'/><img src="picture_library/805715/3027001614000000417.jpg" alt="Interieur" title="Interieur" width='76' height='49' style='margin:2px; cursor:pointer;' onclick='changeFotoStyling(this.src)'  onmouseover='changeFotoStyling(this.src)'/><img src="picture_library/805715/3027001614000000422.jpg" alt="Interieur" title="Interieur" width='76' height='49' style='margin:2px; cursor:pointer;' onclick='changeFotoStyling(this.src)'  onmouseover='changeFotoStyling(this.src)'/><img src="picture_library/805715/3027001614000000418.jpg" alt="Interieur" title="Interieur" width='76' height='49' style='margin:2px; cursor:pointer;' onclick='changeFotoStyling(this.src)'  onmouseover='changeFotoStyling(this.src)'/><img src="picture_library/805715/3027001614000000419.jpg" alt="Interieur" title="Interieur" width='76' height='49' style='margin:2px; cursor:pointer;' onclick='changeFotoStyling(this.src)'  onmouseover='changeFotoStyling(this.src)'/><img src="picture_library/805715/3027001614000000423.jpg" alt="Exterieur" title="Exterieur" width='76' height='49' style='margin:2px; cursor:pointer;' onclick='changeFotoStyling(this.src)'  onmouseover='changeFotoStyling(this.src)'/>            <br/>
            <br/>
                                <img src="img/btnSpeelfilm.png" border="0" onclick="slideshow();" style="cursor:pointer;"/>
                            </td>
        </tr>
        <tr>
            <td valign="bottom">
                        <script type="text/javascript">
            function fShowMap(){
                var ele = document.getElementById("plan");
                if(ele.style.display == "block") {
                    ele.style.display = "none"
                    }
                else{
                    ele.style.display = "block"
                    }
            }
            function slideshow() {
                sx = Math.floor((screen.width - 638) / 2);
                sy = Math.floor((screen.height - 400) / 2);
                window.open("movie.php?id=1111", "slideshow", "width=638,height=400,left="+sx+",top="+sy+"");
            }
            </script>
                <!--<img src="img/btnToonPlan.png" border="0" onclick="fShowMap();" style="cursor:pointer;"/>-->
                        </td>
        </tr>
        </table>

        <table width="546">
        <tr>
        <td valign="top" style="width:143px;" align="left">
            <a href="default.php?page=contact&id=805715"><img src="img/btnInfo.png" style="cursor:pointer;" border="0"/></a><br/>
            <a href="print.php?id=1111" target="blank"><img src="img/btnFicheAfdrukken.png" style="cursor:pointer;" border="0"/></a><br/>
            <a href="mailto:?subject=Een vriend heeft een interessant pand voor u gevonden op www.vastgoedvanhoof.be&body=goed onderhouden bel-étagewoning met tuin  http://www.vastgoedvanhoof.be?page=detail%26id%3D1111"><img src="img/btnPandDoorsturen.png" style="cursor:pointer;" border="0"/></a><br/>
            <a href="#" onclick="history.go(-1)"><img src="img/btnTerugNaarLijst.png" border="0" style="cursor:pointer;"/></a><br/><br/>
            <div style="width:120px; font-size:11px; font-family:Arial; color:#4B6599;">
            Voor meer info
            over dit pand,
            contacteer ons kantoor<br/>
            03/449 25 00 of<br/>
            <a href="mailto:info@vastgoedvanhoof.be" style="color:#87888A;">info@vastgoedvanhoof.be</a>

            </div>
        </td>
        <td valign="top" align="left">

        <div>
            <div style="float:left;"><h1 class="titZoekresultaat">goed onderhouden bel-étagewoning met tuin </h1></div>
            <div style="right:0px; float:right; background-color:#D9DADB; color:#4B6599;padding-left:5px;padding-right:5px;">
            € 249.000            </div>
        </div>
        
        
        
        <br/>
        <br/>
        In het residentiële Aarstelaar, in de onmiddellijke omgeving van invalswegen, openbaar vervoer, winkels en in de nabijheid van scholen, treft u deze ruime bel-étage in een rustige woonstraat.
De woning is als volgt ingedeeld: op het gelijkvloers inkomhal op tegels met vestiaire, gastentoilet, ruime garage, wasplaats/berging, CV-ruimte en ruime tuin met terras. Op de 1ste verdieping treft u een hall op laminaat, een ruime leefruimte op laminaat en een klassiek ingericht keuken op tegels. Op de 2de verdieping treft u tot slot 3 slaapkamers op kamerbreed tapijt (waarvan 1 baby-kamer) en een badkamer op tegels.

Mits lichte opfrissing door schilderwerken en eventuele modernisering van de natte ruimten is dit een zeer aangename en ruime woning in een rustige woonomgeving.
 
         <br/>
        </td>
        </tr>
        </table>
        <br/>
        
        <div id="plan" name="plan" class="gmap3"></div>
        
        <script type="text/javascript">
        $(function(){
                $('#plan').gmap3(
                { 
                    action: 'addMarker',
                    latLng: [51.1444,4.38414],
                    map:{
                        center: true,
                        zoom: 15
                        },
                    marker:{
                        options:{
                        draggable: true,
                                                
                        icon: "http://www.vastgoedvanhoof.be/img/huis.png"                        
                                                
                        }
                        }
                }
                );
            });
            </script>

            <br/>
        <div style="background-color:#D9DADB; width:546px;">
        <table width="546">
        <tr>
            <td valign="top" style="width:143px;"></td>
            <td valign="top" align="left">
            <h1 class="titZoekresultaat">Algemene info</h1>
            ref.: 805715/wvh0003                        <h2 class="titZoekresultaat2">Geografische ligging</h1>
            Oudestraat 51<br/>2630 Aartselaar            <h2 class="titZoekresultaat2">Financiële info</h1>
            <table>
            <tr>
                <td>Vrij vanaf</td>
                <td>bij akte</td>
            </tr>
            <tr>
                <td>Prijs</td>
                <td>€ 249.000                </td>
            </tr>
            
                                    
                <tr>
                    <td>Registratierechten</td>
                    <td>€ 24.900                    </td>
                </tr>
                            
    
            
                        <tr>
                <td>Kadastraal inkomen</td>
                <td>€ 939<!--non_indexed_ki, comment_ki  -->                 </td>
            </tr>
                                    </table>

                                    <span style="font-size:10px; margin-top:10px;">
                    * Schatting op basis van deze parameters: hoofdverblijfplaats, natuurlijk
                    persoon. 
                    <br/><br/>
                    <a href="?page=bereken&id=1111" style="color:#87888A;">Klik hier voor een gepersonaliseerde berekening</a>.
                    <br/><br/>
                    </span>
                
                
            <h2 class="titZoekresultaat2">Bouwtechnisch</h1>
            <table>
            <tr>
                <td>Type</td>
                <td>Gesloten</td>
            </tr>
            
                        <tr>
                <td>Staat</td>
                <td>Normaal</td>
            </tr>
                        <tr>
                <td>Nieuwbouw</td>
                <td>
                    neen                </td>
            </tr>
            
                        
                        
                        <tr>
                <td>Grondoppervlakte</td>
                <td>201m²</td>
            </tr>
                        
                        <tr>
                <td>Bouwoppervlakte</td>
                <td>150m²</td>
            </tr>
                        
                        <tr>
                <td>Bebouwbare opervlakte</td>
                <td>60m²</td>
            </tr>
                        
                        
                        
                        <tr>
                <td>Breedte grond</td>
                <td>6.5m²</td>
            </tr>
                        
                        <tr>
                <td>Breedte huis</td>
                <td>6.5m²</td>
            </tr>
                        
                    
                        
                        <tr>
                <td>EPC</td>
                <td>393</td>
            </tr>
                        </table>

            <h2 class="titZoekresultaat2">Ruimtelijke ordening</h1>

            <table>
            <tr>
                <td>Stedenbouwkundige vergunning</td>
                <td>
                    neen                </td>
            </tr>
            <tr>
                <td>Meest recente bestemming</td>
                <td>Woongebied</td>
            </tr>
            <tr>
                <td>Dagvaardingen uitgebracht</td>
                <td>
                    neen                </td>
            </tr>
            <tr>
                <td>Voorkooprecht op dit goed</td>
                <td>
                    neen                </td>
            </tr>
            <tr>
                <td>Verkavelingsvergunning</td>
                <td>
                    neen                </td>
            </tr>
            </table>



            <h1 class="titZoekresultaat">Indeling</h1>
            <table>
            <tr>
                <td>Aantal slaapkamers</td>
                <td>
                    3                </td>
            </tr>
            </table>

            <table>
                         <tr>
                <td>Slaapkamer</td>
                <td>

                    op kamerbreed tapijt<br/>                    opp: 16.54m²<br/>                </td>
             </tr>
                          <tr>
                <td>Slaapkamer</td>
                td>

                    op kamerbreed tapijt<br/>                    opp: 5.67m²<br/>                </td>
             </tr>
                          <tr>
                <td>Nachthal</td>
                <td>

                    op laminaat<br/>                    opp: 5.47m²<br/>                </td>
             </tr>
                          <tr>
                <td>Slaapkamer</td>
                <td>

                    op  kamerbreed tapijt<br/>                    opp: 17.14m²<br/>                </td>
             </tr>
                          <tr>
                <td>Woonkamer</td>
                <td>

                    op laminaat<br/>                    opp: 39.15m²<br/>                </td>
             </tr>
                          <tr>
                <td>Inkomhal</td>
                <td>

                    op tegels met vestiaire<br/>                    opp: 13.03m²<br/>                </td>
             </tr>
                          <tr>
                <td>Berging</td>
                <td>

                    wasplaats/berging op tegels<br/>                    opp: 13.12m²<br/>                </td>
             </tr>
                          <tr>
                <td>Tuin</td>
                <td>

                                        opp: 107.92m²<br/>                </td>
             </tr>
                          <tr>
                <td>Keuken</td>
                <td>

                    op tegels<br/>                    opp: 6.28m²<br/>                </td>
             </tr>
                          <tr>
                <td>Garage</td>
                <td>

                    met sectionaalpoort<br/>                    opp: 21.13m²<br/>                </td>
             </tr>
                          <tr>
                <td>Terras</td>
                <td>

                                        opp: 15.7m²<br/>                </td>
             </tr>
                          <tr>
                <td>Toilet</td>
                <td>

                    gastentoilet<br/>                    opp: 2m²<br/>                </td>
             </tr>
                          <tr>
                <td>Nachthal</td>
                <td>

                    op laminaat<br/>                    opp: 5.47m²<br/>                </td>
             </tr>
                          <tr>
                <td>CV Ruimte</td>
                <td>

                    op tegels<br/>                    opp: 4.23m²<br/>                </td>
             </tr>
                          <tr>
                <td>Badkamer</td>
                <td>

                    op tegels<br/>                    opp: 6.22m²<br/>                </td>
             </tr>
                          </table>


            <h2 class="titZoekresultaat2">Overige info</h1>
            <table >
                <tr>
                    <td>Oriëntatie tuin</td>
                    <td>
                        Noord                    </td>
                </tr>
            </table>

            <table>

                                                                                                                                            
            <tr><td>Dak:</td><td>plat dak met roofing, geen lekken Goed</td></tr>            <tr><td>Raamwerk:</td><td>pvc Goed</td></tr>            <tr><td>Beglazing:</td><td>dubbele beglazing + rolluiken Goed</td></tr>            <tr><td>Verwarming:</td><td>cv op mazout Goed</td></tr>            <tr><td>Elektriciteit:</td><td>enkel punten vernoemd op electriciteitsverslag Goed</td></tr>                                    <tr><td>Isolatie:</td><td>goede epc-score Goed</td></tr>                                                            <tr><td>Rustig:</td><td></td></tr>            </table>



            <h2 class="titZoekresultaat2">Downloads</h1>
            <table>
                                                                            </table>

            </td>
        </tr>
        </table>
        </div>
    

</td>
</tr>

<tr>
<td style="background-color:#FFFFFF; padding:25px; width:546px;"  valign="bottom">
<div class="titInschrijven">
<div style="float:left; margin-top:8px;">Blijf automatisch op de hoogte van ons nieuw aanbod.</div><div style="float:right; margin-top:5px;"><a href="default.php?page=inschrijven"><img src="img/btnInschrijven.png" alt="inschrijven" border="0"/></a></div>
</div>
<br/>
   <table class="tblFooter" cellpadding="0" cellspacing="0" >
        <tr>
        <td style="width:188px;">
        <b>Vastgoed Walther Van Hoof nv</b><br/>
        <span style="font-weight:600">HOOFDKANTOOR WILRIJK</span><br/>
        Prins Boudewijnlaan 112-116<br/>
        2610 Wilrijk<br/>
        Tel 03 449 25 00<br/>
        Fax 03 449 23 00<br/>
        <a href="mailto:info@vastgoedvanhoof.be" class="linkCredentials">info@vastgoedvanhoof.be</a><br/><br/>
        <span style="font-weight:600">KANTOOR ANTWERPEN</span><br/>
        Mechelsesteenweg 62<br/>
        2018 Antwerpen<br/>
        Tel 03 216 25 00<br/>
        Fax 03 216 25 12<br/>
        <a href="mailto:info@vanhoofantwerpen.be" class="linkCredentials">info@vanhoofantwerpen.be</a><br/>
        </td>
        <td style="width:188px;"><b>Erkende Vastgoedmakelaars</b><br/>
        <table cellpadding="0" cellspacing="0">
        <tr><td class="BIV">BIV 207 375</td><td>Miguel Van Hoof</td></tr>
        <tr><td class="BIV">BIV 501 190</td><td>Veerle Van Hecke</td></tr>
        <tr><td class="BIV">BIV 202 445</td><td>Walther Van Hoof</td></tr>
        <tr><td class="BIV">BIV 505 055</td><td>Petra Van Bauwel</td></tr>
        <tr><td class="BIV">BIV 505 981</td><td>Françoise Beghin</td></tr>
        <tr><td class="BIV">BIV 507 387</td><td>Hatem Bahlouli</td></tr>
        <tr><td class="BIV">BIV 507 760</td><td>Duncan Van Reck</td></tr>
        </table> 
        <!--
        <br/>
        <b>Sociale media</b><br/>
        Sluit je aan en volg ons op de voet<br/>
        <img src="img/btnFacebook.png" alt="volg ons op facebook" />
        <img src="img/btnLinkedIn.png" alt="volg ons op linkedIn" />
        <img src="img/btnTwitter.png" alt="volg ons op Twitter" /><br/>
        -->
        </td>
        <td style="width:95px;"><b>Sitemap</b><br/>
        <a href="default.php" class="linkCredentials">Home</a><br/>
        <a href="default.php?page=te koop" class="linkCredentials">Te koop</a><br/>
        <a href="default.php?page=te huur" class="linkCredentials">Te huur</a><br/>
        <a href="default.php?page=schatting" class="linkCredentials">Gratis schatting</a><br/>
        <a href="default.php?page=inschrijven" class="linkCredentials">Inschrijven</a><br/>
        <a href="default.php?page=nieuws" class="linkCredentials">Nieuws</a><br/>
        <a href="default.php?page=kantoor" class="linkCredentials">Over ons</a><br/>
        <a href="default.php?page=reclamemix" class="linkCredentials">Reclamemix</a><br/>
        <a href="default.php?page=medewerkers" class="linkCredentials">Medewerkers</a><br/>
        <a href="default.php?page=testimonials" class="linkCredentials">Getuigenissen</a><br/>
        <a href="default.php?page=styling" class="linkCredentials">Styling</a><br/>
        <a href="default.php?page=contact" class="linkCredentials">Contact</a><br/>
        /td>
        <td style="width:91px;">
        <b>Advies</b><br/>
        <a href="default.php?page=hypothecair advies" class="linkCredentials">Hypotheek</a><br/>
        <a href="default.php?page=juridisch advies" class="linkCredentials">Juridisch</a><br/>
        <a href="default.php?page=tips" class="linkCredentials">Tips</a><br/>
        <a href="default.php?page=verzekering" class="linkCredentials">Verzekering</a><br/><br/>
        <b>Links</b><br/>
        <a href="http://www.hetlandgoed.be/landgoed/" class="linkCredentials" target="_blank">Nieuwbouw</a><br/>
        <a href="http://www.maisons-lesoliviers.com/fr/" class="linkCredentials" target="_blank">Vakantiehuizen</a><br/>
        <a href="http://www.ithemba.be/iThemba_Home.html" class="linkCredentials" target="_blank">vzw iThemba</a><br/>
        </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
        <td colspan="4">
        &copy; Vastgoed Walther Van Hoof 2012 | <a class="linkCredentials" href="http://www.ithink.be" target="_blank">Webbuilding www.ithink.be</a> | <a class="linkCredentials" href="http://www.wit-zwart.be" target="_blank">Webdesign www.wit-zwart.be</a>
        </td>
        </tr>
    </table>
 

</td>
</tr>
</table>

</center>
</body>
</html>
ABC;

	$parser = new PageParser($html);
	//$parser->deleteTags(array("script", "style"));
    $property[TAG_TYPE]               = CrawlerTool::getPropertyType($arr[1]);
    $property[TAG_TYPE_LONG]          = trim($arr[1]);
    $property[TAG_PRICE]              = $price;

        
	if (empty($property[TAG_TYPE]) && preg_match('!<h2 class="printMT5">([^<>]+)</h2>!',$html,$res))
	{
		$property[TAG_TYPE] = CrawlerTool::getPropertyType($res[1]);
	}

	$property[TAG_TEXT_SHORT_DESC_NL]  = trim(strip_tags($parser->extract_regex("!<h2>Beschrijving</h2>(.*?)</div>!s")));

	if (empty($property[TAG_TYPE]))
	{
		$property[TAG_TYPE] = CrawlerTool::getPropertyType($property[TAG_TEXT_SHORT_DESC_NL],300);
	}

	$arr = $parser->regex_all('!<li><a href="(http://willemyns.websites.whoman2.be//Pictures/[^"]+)">\s+<img!', $html);
	$property[TAG_PICTURES]            = CrawlerTool::addTextToPicUrls($arr,'');

	$property[TAG_CITY]                = $parser->extract_regex('!<h1 class="like2 FLNI printFN">[^<>]+<span>([^<>]+)!s');

	$property[TAG_SURFACE_LIVING_AREA] = (preg_match('!Bewoonbare oppervlakte</span><span class="FR">(\d+)!',$html,$res)) ? $res[1] : '';
	$property[TAG_SURFACE_GROUND]      = (preg_match('!Oppervlakte perceel</span><span class="FR">(\d+)!',$html,$res)) ? $res[1] : '';
	$property[TAG_BEDROOMS_TOTAL]      = (preg_match('!Aantal slaapkamers</span><span class="FR">(\d+)</span>!',$html,$res)) ? $res[1] : '';
	$property[TAG_BATHROOMS_TOTAL]     = (preg_match('!Aantal badkamers</span><span class="FR">(\d+)</span>!',$html,$res)) ? $res[1] : '';
	$property[TAG_EPC_VALUE]           = (preg_match('!EPC score</span><span class="FR">(\d+)!',$html,$res)) ? $res[1] : '';

	$property[TAG_CONSTRUCTION_YEAR]   = (preg_match('!Bouwjaar</span><span class="FR">(\d{4})!',$html,$res)) ? $res[1] : '';
	$property[TAG_RENOVATION_YEAR]     = (preg_match('!Renovatiejaar</span><span class="FR">(\d{4})!',$html,$res)) ? $res[1] : '';

	$property[TAG_GARAGES_TOTAL]       = (preg_match('!Aantal garages</span><span class="FR">(\d+)!',$html,$res)) ? $res[1] : '';

	CrawlerTool::saveProperty($property);
}