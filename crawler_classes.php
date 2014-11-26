<?php
/**
* Author : sasha karpin, sasya8080@gmail.com
* @since : 2012/11/03
**/
class Crawler
{
	/** delay between requests options **/
	protected $min_delay              = 0;
	protected $max_delay              = 0;

	/** proxy options **/
	protected $proxy                  = '';
	protected $proxy_type             = '';
	protected $proxy_pwd              = '';

	protected $enable_cookie          = false;
	protected $cookie_name            = 'cookie.txt';

	/** try to perform request again, if error **/
	protected $reload_if_error        = true;
	protected $try_reload_max         = 3;

	/** header options **/
	protected $use_gzip               = true;
	protected $header_except_flag     = false;
	protected $header_xmlhttpreq_flag = false;
	protected $header_accept          = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
	protected $header_ua              = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3';
	protected $header_accept_lang     = "en-us,en;q=0.5";
	protected $header_accept_chrst    = "utf-8;q=0.7,*;q=0.7";
	protected $header_connection      = "keep-alive";


	public function __construct($config)
	{
		$this->proxy         = isset($config["proxy"]) ? $config["proxy"] : "";
		$this->proxy_type    = isset($config["proxy_type"]) ? $config["proxy_type"] : "";
		$this->proxy_pwd     = isset($config["proxy_pwd"]) ? $config["proxy_pwd"] : "";
	}

	/**
	 * Enable delay between requests
	 *
	 * @param integer $min_delay
	 * @param integer $max_delay
	 */
	public function enable_delay_between_requests($min_delay,$max_delay)
	{
		$this->min_delay = intval($min_delay);
		$this->max_delay = intval($max_delay);
	}

	public function set_proxy($proxy)
	{
		$this->proxy = $proxy;
	}

	public function set_proxy_type($proxy_type)
	{
		$this->proxy_type = $proxy_type;
	}

	public function set_proxy_pwd($proxy_pwd)
	{
		$this->proxy_pwd = $proxy_pwd;
	}

	/**
     * Enable or disable gzip header in curl
     *
     * @param boolean $v
     */
	public function use_gzip($v)
	{
		$this->use_gzip = $v;
	}

	/**
     * Enable or disable Expect: headaer
     *
     * @param boolean $v
     */
	public function set_except_header($v)
	{
		$this->header_except_flag = (bool) $v;
	}

	/**
     * Enable or disable cookies
     *
     * @param boolean $v
     */
	public function use_cookies($v)
	{
		$this->enable_cookie  = (bool) $v;
	}


	/**
     * Set user agent
     *
     * @param string $v
     */
	public function set_useragent($v)
	{
		$this->header_ua = $v;
	}

	/**
     * Set Accept-Language: header value
     *
     * @param string $v
     */
	public function set_accept_lang($v)
	{
		$this->header_accept_lang = $v;
	}

	/**
     * Enable or disable X-Requested-With: XMLHttpRequest header
     *
     * @param boolean $v
     */
	public function set_xmlhttpreq($v)
	{
		$this->header_xmlhttpreq_flag = (bool) $v;
	}

    /**
     * NOT USED! Set script dir
     *
     * @param string $dir
     */
    public function set_script_dir($dir)
    {

    }

	/**
	 * Clean cookies
	 *
	 */
	public function clean_cookies()
	{
        file_put_contents(SCRIPT_DIR.$this->cookie_name,'');
	}


	/**
     * Functions makes headers array for curl
     *
     * @return array
     */
	protected function get_headers()
	{
		$header = array();

		if ($this->header_except_flag)          {$header[] = 'Expect:';}
		if (!empty($this->header_accept))       {$header[] = 'Accept: ' . $this->header_accept;	}
		if (!empty($this->header_ua))           {$header[] = 'User-Agent: ' . $this->header_ua;	}
		if (!empty($this->header_accept_lang))  {$header[] = 'Accept-Language: ' . $this->header_accept_lang;	}
		if (!empty($this->header_accept_chrst)) {$header[] = 'Accept-Charset: ' . $this->header_accept_chrst;	}
		if ($this->header_xmlhttpreq_flag)      {$header[] = 'X-Requested-With: XMLHttpRequest';}
		if (!empty($this->header_connection))   {$header[] = 'Connection: ' . $this->header_connection;	}

		return $header;
	}


	/**
	 * Perform GET or POST request
	 *
     * @param string $url
     * @param string $postData
     * @param string $refer
     * @return string
     */
	public function request($url, $postData = false, $refer = '',$try = 0)
	{
		sleep(rand($this->min_delay, $this->max_delay));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, str_replace(" ", "%20", $url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		//curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		if ($this->use_gzip)
		{
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		}

		curl_setopt($ch, CURLOPT_TIMEOUT, 40);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$this->get_headers());

        if($this->enable_cookie)
        {
            curl_setopt($ch, CURLOPT_COOKIEFILE, SCRIPT_DIR.$this->cookie_name);
            curl_setopt($ch, CURLOPT_COOKIEJAR, SCRIPT_DIR.$this->cookie_name);
        }

		if(!empty($refer))
		{
			curl_setopt($ch, CURLOPT_REFERER, $refer);
		}

		if($postData !== false)
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}

		if(!empty($this->proxy))
		{
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		}
		if(!empty($this->proxy_type) && $this->proxy_type == 'socks')
		{
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		}
		if(!empty($this->proxy_pwd))
		{
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy_pwd);
		}

		$html = curl_exec($ch);

		if($err = curl_error($ch))
		{
			echo "Url:$url cURL error:$err<br>\r\n";
			$try++;
			if ($try>$this->try_reload_max || $this->reload_if_error == false) 	{exit("\n<br />A lot of curl errors");}
			return $this->request($url,$postData,$refer,$try);
		}
		curl_close($ch);

		return $html;
	}
}


/**
 *  PageParser class helps extract data from the given html document via 2 main methods:
 *      1. extract_xpath : Extract data by using DOM/XPath
 *      2. extract_regex : Extract data by using regular expression functions
 */

class PageParser
{
	protected $html;
	protected $xpath;
	protected $queryTemplate;

	public function __construct($html)
	{
		$this->html = $html;
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		@$dom->loadHTML($html);
		$this->xpath = new DOMXPath($dom);
		$this->queryTemplate = "";
	}

	public function setQueryTemplate($text)
	{
		$this->queryTemplate = $text;
	}

	public function clearQueryTemplate()
	{
		$this->queryTemplate = "";
	}

	public function getXPathQuery($text)
	{
		if(empty($this->queryTemplate)) return $text;

		return str_replace(XPATH_QUERY_TEMPLATE, $text, $this->queryTemplate);
	}

	/** Extract property infomation via Regular Expression
     * @param $exp Regular expression string
     * @param $returnType Refer to Return type constants section in CrawlerConst class for available return type values
     * @param Closure|null $closure A kind of callback function to further process the return value
     */
	public function extract_regex($exp, $returnType = RETURN_TYPE_TEXT, Closure $closure = null)
	{
		if(RETURN_TYPE_ARRAY === $returnType)
		{
			return $this->regex_all($exp, $this->html, $closure);
		}
		else if(RETURN_TYPE_NUMBER === $returnType)
		{
			$number = CrawlerTool::toNumber($this->regex($exp, $this->html));

			if(empty($closure))
			{
				if($number > 0) return $number;
			}
			else
			{
				return $closure($number);
			}

			return "";
		}
		else if(RETURN_TYPE_EPC === $returnType)
		{
			$epc = CrawlerTool::toNumber($this->regex($exp, $this->html));

			if(empty($closure))
			{
				if($epc > 0 && $epc <= 999) return $epc;
			}
			else
			{
				return $closure($epc);
			}

			return "";
		}
		else if(RETURN_TYPE_YEAR === $returnType)
		{
			$year = CrawlerTool::toNumber($this->regex($exp, $this->html));

			if(empty($closure))
			{
				if($year > 0 && strlen($year) == 4) return $year;
			}
			else
			{
				return $closure($year);
			}

			return "";
		}
		else if(RETURN_TYPE_UNIX_TIMESTAMP === $returnType)
		{
			$timestamp = CrawlerTool::toUnixTimestamp($this->regex($exp, $this->html));

			if(empty($closure))
			{
				if($timestamp > 0) return $timestamp;
			}
			else
			{
				return $closure($timestamp);
			}

			return "";
		}
		else
		{
			return $this->regex($exp, $this->html, $closure);
		}
	}

	/** Extract property infomation via XPath
     * @param $exp XPath query string
     * @param $returnType Refer to Return type constants section in CrawlerConst class for available return type values
     * @param Closure|null $closure A kind of callback function to further process the return value
     * @param $contextNode
     */
	public function extract_xpath($exp, $returnType = RETURN_TYPE_TEXT, Closure $closure = null, $contextNode = null)
	{
		$exp = $this->getXPathQuery($exp);

		if(RETURN_TYPE_ARRAY === $returnType)
		{
			$nodes = $this->getNodes($exp, $contextNode);
			$arr = array();

			if(!empty($nodes) && $nodes->length > 0)
			{
				$node = $nodes->item(0);
				$nodeName = $node->nodeName;

				if($nodeName === "a")
				{
					foreach($nodes as $node) $arr[] = $this->getAttr($node, "href");
				}
				else if($nodeName === "img")
				{
					foreach($nodes as $node) $arr[] = $this->getAttr($node, "src");
				}
				else
				{
					foreach($nodes as $node) $arr[] = $this->getText($node);
				}

				$arr = array_unique($arr);

				if(empty($closure))
				{
					return $arr;
				}
				else
				{
					return $closure($arr);
				}
			}

			return $arr;
		}
		else if(RETURN_TYPE_NUMBER === $returnType)
		{
			$node = $this->getNode($exp, $contextNode);
			if($node)
			{
				$number = CrawlerTool::toNumber($this->getText($node));
				if(empty($closure))
				{
					if($number > 0) return $number;
				}
				else
				{
					return $closure($number);
				}
			}

			return "";
		}
		else if(RETURN_TYPE_EPC === $returnType)
		{
			$node = $this->getNode($exp, $contextNode);
			if($node)
			{
				$epc = CrawlerTool::toNumber($this->getText($node));
				if(empty($closure))
				{
					if($epc > 0 && $epc <= 999) return $epc;
				}
				else
				{
					return $closure($epc);
				}
			}

			return "";
		}
		else if(RETURN_TYPE_YEAR === $returnType)
		{
			$node = $this->getNode($exp, $contextNode);
			if($node)
			{
				$year = CrawlerTool::toNumber($this->getText($node));
				if(empty($closure))
				{
					if($year > 0 && strlen($year) == 4) return $year;
				}
				else
				{
					return $closure($year);
				}
			}

			return "";
		}
		else if(RETURN_TYPE_UNIX_TIMESTAMP === $returnType)
		{
			$node = $this->getNode($exp, $contextNode);
			if($node)
			{
				$timestamp = CrawlerTool::toUnixTimestamp($this->getText($node));
				if(empty($closure))
				{
					if($timestamp > 0) return $timestamp;
				}
				else
				{
					return $closure($timestamp);
				}
			}

			return "";
		}
		else if(RETURN_TYPE_TEXT_ALL === $returnType)
		{
			$nodes = $this->getNodes($exp, $contextNode);
			if(!empty($nodes))
			{
				$text = $this->getText($nodes);
				if(empty($closure))
				{
					return $text;
				}
				else
				{
					return $closure($text);
				}
			}

			return "";
		}
		else
		{
			$node = $this->getNode($exp, $contextNode);
			if($node)
			{
				$text = $this->getText($node);
				if(empty($closure))
				{
					return $text;
				}
				else
				{
					return $closure($text);
				}
			}

			return "";
		}
	}

	public function regex_all($exp, $text, Closure $closure = null)
	{
		preg_match_all($exp, $text, $match);

		if(isset($match[1]))
		{
			if(empty($closure))
			{
				return $match[1];
			}
			else
			{
				return $closure($match);
			}
		}

		return array();
	}

	public function regex($exp, $text, Closure $closure = null)
	{
		if(preg_match($exp, $text, $match))
		{
			if(empty($closure))
			{
				return trim($match[1]);
			}
			else
			{
				return $closure($match);
			}
		}

		return "";
	}

	public function getNode($query, $contextNode = null, $index = 0)
	{
		$node = null;

		if(empty($contextNode))
		{
			$nodes = $this->xpath->query("//" . $query);
		}
		else
		{
			$nodes = $this->xpath->query(".//" . $query, $contextNode);
		}

		foreach($nodes as $i => $node)
		{
			if($i == $index) return $node;
		}

		return $node;
	}

	public function getNodes($query, $contextNode = null)
	{
		$query = str_replace("| ", "| //", $query);

		if(empty($contextNode))
		{
			$nodes = $this->xpath->query("//" . $query);
			return $nodes;
		}
		else
		{
			$nodes = $this->xpath->query(".//" . $query, $contextNode);
			return $nodes;
		}
	}

	public function parentNode($node, $n = 1)
	{
		$parentNode = $node;
		for($i = 1; $i <= $n; $i++) $parentNode = $parentNode->parentNode;

		return $parentNode;
	}

	public function getHTML($node)
	{
		$dom = new DOMDocument();
		$dom->appendChild($dom->importNode($node, true));
		return $dom->saveHTML();
	}

	public function getText($nodes, $delim = " ")
	{
		$text = "";

		// if DOMNode
		if(property_exists($nodes, "nodeValue"))
		{
			$text = CrawlerTool::strip($nodes->nodeValue);
		}
		// if DOMNodeList
		else if(property_exists($nodes, "length"))
		{
			foreach($nodes as $node) $text .= $node->nodeValue . $delim;

			$text = CrawlerTool::strip($text);
			if($delim !== " ") $text = substr($text, 0, strlen($text) - strlen($delim));
		}

		return $text;
	}

	public function getAttr($node, $attrName)
	{
		$text = "";

		if(method_exists($node, "getAttribute")) {
			$text = $node->getAttribute($attrName);
		}

		return trim($text);
	}

	public function deleteTags($tags)
	{
		foreach($tags as $tag)
		{
			$nodes = $this->getNodes($tag);
			foreach($nodes as $node)
			{
				$node->parentNode->removeChild($node);
			}
		}
	}
}


class CrawlerTool
{
	private static $saved_properties = 0;
	private static $saved_projects   = 0;
	private static $saved_offices    = 0;
    private static $saved_employees  = 0;
    private static $defaultInfo      = array();

    public static function setDefault($info)
    {
        self::$defaultInfo = $info;
    }

	public static function startXML()
	{
		file_put_contents(OUTPUT_FILE_NAME, "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<zimmo>");
		file_put_contents(OUTPUT_OFFICE_FILE_NAME, "");
        file_put_contents(OUTPUT_EMPLOYEE_FILE_NAME, "");
		file_put_contents(OUTPUT_PROJECT_FILE_NAME, "");
		file_put_contents(OUTPUT_PROPERTY_FILE_NAME, "");
	}

	public static function endXML()
	{
		$offices = file_get_contents(OUTPUT_OFFICE_FILE_NAME);
		if(!empty($offices))
		{
            $offices = preg_replace("/<geo>\n\t*<\/geo>|<contact>\n\t*<\/contact>|<social>\n\t*<\/social>/", "", $offices);
            $offices = preg_replace("/[\s\t]*[\r\n]+/", "\n", $offices);
            $xml = "\n\t<offices>" . $offices . "\n\t</offices>";
            file_put_contents(OUTPUT_FILE_NAME, $xml, FILE_APPEND);
		}
        else // set default office info
        {
            $xml = "\n\t<offices>";
            $xml .= "\n\t\t<office>";
            $xml .= "\n\t\t\t<general>";
            $xml .= "\n\t\t\t\t<unique_id>1</unique_id>";
            $xml .= "\n\t\t\t\t<name>Default Office Name</name>";
            if(!empty(self::$defaultInfo[TAG_OFFICE_URL]))
            {
                $xml .= "\n\t\t\t\t<url>" . self::$defaultInfo[TAG_OFFICE_URL] . "</url>";
            }
            else
            {
                $xml .= "\n\t\t\t\t<url></url>";
            }
            $xml .= "\n\t\t\t</general>";
            $xml .= "\n\t\t</office>";
            $xml .= "\n\t</offices>";

            self::$saved_offices++;
            file_put_contents(OUTPUT_FILE_NAME, $xml, FILE_APPEND);
        }
		unset($offices);

        $employees = file_get_contents(OUTPUT_EMPLOYEE_FILE_NAME);
        if(!empty($employees))
        {
            $employees = preg_replace("/<contact>\n\t*<\/contact>|<social>\n\t*<\/social>/", "", $employees);
            $employees = preg_replace("/[\s\t]*[\r\n]+/", "\n", $employees);
            $xml = "\n\t<employees>" . $employees . "\n\t</employees>";
            file_put_contents(OUTPUT_FILE_NAME, $xml, FILE_APPEND);
        }

		$projects = file_get_contents(OUTPUT_PROJECT_FILE_NAME);
		if(!empty($projects))
		{
            $projects = preg_replace("/<media>\n\t*<\/media>|<text>\n\t*<\/text>/", "", $projects);
            $projects = preg_replace("/[\s\t]*[\r\n]+/", "\n", $projects);
			$xml = "\n\t<projects>" . $projects . "\n\t</projects>";
			file_put_contents(OUTPUT_FILE_NAME, $xml, FILE_APPEND);
		}
		unset($projects);

		$properties = file_get_contents(OUTPUT_PROPERTY_FILE_NAME);
		if(!empty($properties))
		{
			$properties = preg_replace("/<media>\n\t*<\/media>|<text>\n\t*<\/text>|<construction>\n\t*<\/construction>|<certificates>\n\t*<\/certificates>|<planning>\n\t*<\/planning>|<financial>\n\t*<\/financial>|<layout>\n\t*<\/layout>|<comfort>\n\t*<\/comfort>|<other>\n\t*<\/other>|<source>\n\t*<\/source>/", "", $properties);
			$properties = preg_replace("/[\s\t]*[\r\n]+/", "\n", $properties);
			$xml = "\n\t<properties>" . $properties . "\n\t</properties>";
			file_put_contents(OUTPUT_FILE_NAME, $xml, FILE_APPEND);
		}
		unset($properties);

		unlink(OUTPUT_OFFICE_FILE_NAME);
        unlink(OUTPUT_EMPLOYEE_FILE_NAME);
		unlink(OUTPUT_PROJECT_FILE_NAME);
		unlink(OUTPUT_PROPERTY_FILE_NAME);

		file_put_contents(OUTPUT_FILE_NAME, "\n</zimmo>", FILE_APPEND);

        echo 'Saved properties/projects/offices/employees:'
        . self::$saved_properties . '/'
        . self::$saved_projects . '/'
        . self::$saved_offices . '/'
        . self::$saved_employees . ";<br>\r\n";
	}

	public static function saveOffice($office)
	{
		self::$saved_offices++;

		$xml = "\n\t\t<office>";
        $xml .= "\n\t\t\t<general>";
		$xml .= "\n\t\t\t\t<unique_id>" . $office[TAG_OFFICE_ID] .  "</unique_id>";
		if(!empty($office[TAG_OFFICE_NAME])) $xml .= "\n\t\t\t\t<name>" . CrawlerTool::encode($office[TAG_OFFICE_NAME]) .  "</name>";
        if(isset($office[TAG_OFFICE_URL]))
        {
            $xml .= "\n\t\t\t\t<url>" . CrawlerTool::encode($office[TAG_OFFICE_URL]) .  "</url>";
        }
        else
        {
            $xml .= "\n\t\t\t\t<url></url>";
        }
        $xml .= "\n\t\t\t</general>";
		$xml .= "\n\t\t\t<geo>";
		if(!empty($office[TAG_STREET])) $xml .= "\n\t\t\t\t<street>" . CrawlerTool::encode($office[TAG_STREET]) . "</street>";
		if(!empty($office[TAG_NUMBER])) $xml .= "\n\t\t\t\t<number>" . $office[TAG_NUMBER] . "</number>";
		if(!empty($office[TAG_BOX_NUMBER])) $xml .= "\n\t\t\t\t<box_number>" . $office[TAG_BOX_NUMBER] . "</box_number>";
		if(!empty($office[TAG_ZIP])) $xml .= "\n\t\t\t\t<zip>" . $office[TAG_ZIP] . "</zip>";
		$xml .= "\n\t\t\t\t<city>" . CrawlerTool::encode($office[TAG_CITY]) . "</city>";
		if(!empty($office[TAG_COUNTRY]))$xml .= "\n\t\t\t\t<country>" . CrawlerTool::encode($office[TAG_COUNTRY]) . "</country>";
		if(!empty($office[TAG_LATITUDE])) $xml .= "\n\t\t\t\t<latitude>" . $office[TAG_LATITUDE] . "</latitude>";
		if(!empty($office[TAG_LONGITUDE])) $xml .= "\n\t\t\t\t<longitude>" . $office[TAG_LONGITUDE] . "</longitude>";
		$xml .= "\n\t\t\t</geo>";
		$xml .= "\n\t\t\t<contact>";
		if(!empty($office[TAG_TELEPHONE]))$xml .= "\n\t\t\t\t<tel>" . $office[TAG_TELEPHONE] . "</tel>";
		if(!empty($office[TAG_CELLPHONE]))$xml .= "\n\t\t\t\t<cell>" . $office[TAG_CELLPHONE] . "</cell>";
		if(!empty($office[TAG_FAX]))$xml .= "\n\t\t\t\t<fax>" . $office[TAG_FAX] . "</fax>";
		if(!empty($office[TAG_EMAIL]))$xml .= "\n\t\t\t\t<email>" . $office[TAG_EMAIL] . "</email>";
		$xml .= "\n\t\t\t</contact>";
        $xml .= "\n\t\t\t<social>";
        if(isset($office[TAG_FACEBOOK_URL]))$xml .= "\n\t\t\t\t<facebook>" . CrawlerTool::encode($office[TAG_FACEBOOK_URL]) . "</facebook>";
        if(isset($office[TAG_TWITTER_URL]))$xml .= "\n\t\t\t\t<twitter>" . CrawlerTool::encode($office[TAG_TWITTER_URL]) . "</twitter>";
        if(isset($office[TAG_GOOGLE_PLUS_URL]))$xml .= "\n\t\t\t\t<google_plus>" . CrawlerTool::encode($office[TAG_GOOGLE_PLUS_URL]) . "</google_plus>";
        if(isset($office[TAG_LINKEDIN_URL]))$xml .= "\n\t\t\t\t<linkedin>" . CrawlerTool::encode($office[TAG_LINKEDIN_URL]) . "</linkedin>";
        $xml .= "\n\t\t\t</social>";

		$xml .= "\n\t\t</office>";

		file_put_contents(OUTPUT_OFFICE_FILE_NAME, $xml, FILE_APPEND);
	}

    public static function saveEmployee($employee)
    {
        self::$saved_employees++;

        $xml = "\n\t\t<employee>";
        $xml .= "\n\t\t\t<general>";
        $xml .= "\n\t\t\t\t<employee_id>" . $employee[TAG_EMPLOYEE_ID] .  "</employee_id>";
        if(isset($employee[TAG_OFFICE_ID]))
        {
            $xml .= "\n\t\t\t\t<office_id>" . $employee[TAG_OFFICE_ID] .  "</office_id>";
        }
        else
        {
            $xml .= "\n\t\t\t\t<office_id>1</office_id>";
        }
        if(isset($employee[TAG_EMPLOYEE_FIRST_NAME])) $xml .= "\n\t\t\t\t<first_name>" . CrawlerTool::encode($employee[TAG_EMPLOYEE_FIRST_NAME]) .  "</first_name>";
        if(isset($employee[TAG_EMPLOYEE_NAME])) $xml .= "\n\t\t\t\t<name>" . CrawlerTool::encode($employee[TAG_EMPLOYEE_NAME]) .  "</name>";
        if(isset($employee[TAG_EMPLOYEE_GENDER])) $xml .= "\n\t\t\t\t<gender>" . $employee[TAG_EMPLOYEE_GENDER] .  "</gender>";
        if(isset($employee[TAG_EMPLOYEE_TITLE])) $xml .= "\n\t\t\t\t<title>" . CrawlerTool::encode($employee[TAG_EMPLOYEE_TITLE]) .  "</title>";
        $xml .= "\n\t\t\t</general>";
        $xml .= "\n\t\t\t<contact>";
        if(isset($employee[TAG_TELEPHONE]))$xml .= "\n\t\t\t\t<tel>" . $employee[TAG_TELEPHONE] . "</tel>";
        if(isset($employee[TAG_CELLPHONE]))$xml .= "\n\t\t\t\t<cell>" . $employee[TAG_CELLPHONE] . "</cell>";
        if(isset($employee[TAG_FAX]))$xml .= "\n\t\t\t\t<fax>" . $employee[TAG_FAX] . "</fax>";
        if(isset($employee[TAG_EMAIL]))$xml .= "\n\t\t\t\t<email>" . $employee[TAG_EMAIL] . "</email>";
        if(isset($employee[TAG_EMPLOYEE_URL]))$xml .= "\n\t\t\t\t<url>" . CrawlerTool::encode($employee[TAG_EMPLOYEE_URL]) . "</url>";
        $xml .= "\n\t\t\t</contact>";
        $xml .= "\n\t\t\t<social>";
        if(isset($employee[TAG_FACEBOOK_URL]))$xml .= "\n\t\t\t\t<facebook>" . CrawlerTool::encode($employee[TAG_FACEBOOK_URL]) . "</facebook>";
        if(isset($employee[TAG_TWITTER_URL]))$xml .= "\n\t\t\t\t<twitter>" . CrawlerTool::encode($employee[TAG_TWITTER_URL]) . "</twitter>";
        if(isset($employee[TAG_GOOGLE_PLUS_URL]))$xml .= "\n\t\t\t\t<google_plus>" . CrawlerTool::encode($employee[TAG_GOOGLE_PLUS_URL]) . "</google_plus>";
        if(isset($employee[TAG_LINKEDIN_URL]))$xml .= "\n\t\t\t\t<linkedin>" . CrawlerTool::encode($employee[TAG_LINKEDIN_URL]) . "</linkedin>";
        $xml .= "\n\t\t\t</social>";
        $xml .= "\n\t\t</employee>";

        file_put_contents(OUTPUT_EMPLOYEE_FILE_NAME, $xml, FILE_APPEND);
    }

	public static function saveProject($project)
	{
		self::$saved_projects++;

		$xml = "\n\t\t<project>";
        $xml .= "\n\t\t\t<general>";
		$xml .= "\n\t\t\t\t<unique_id>" . $project[TAG_PROJECT_ID] .  "</unique_id>";
		$xml .= "\n\t\t\t\t<unique_url>";
		if(isset($project[TAG_UNIQUE_URL_NL]))
		{
			$xml .= "\n\t\t\t\t\t<nl>" . CrawlerTool::encode($project[TAG_UNIQUE_URL_NL]) . "</nl>";
			if(isset($project[TAG_UNIQUE_URL_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($project[TAG_UNIQUE_URL_FR]) . "</fr>";
			if(isset($project[TAG_UNIQUE_URL_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($project[TAG_UNIQUE_URL_DE]) . "</de>";
			if(isset($project[TAG_UNIQUE_URL_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($project[TAG_UNIQUE_URL_EN]) . "</en>";
		}
		else if(isset($project[TAG_UNIQUE_URL_FR]) || isset($project[TAG_UNIQUE_URL_EN]) || isset($project[TAG_UNIQUE_URL_DE]))
		{
			$xml .= "\n\t\t\t\t\t<nl></nl>";
			if(isset($project[TAG_UNIQUE_URL_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($project[TAG_UNIQUE_URL_FR]) . "</fr>";
			if(isset($project[TAG_UNIQUE_URL_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($project[TAG_UNIQUE_URL_DE]) . "</de>";
			if(isset($project[TAG_UNIQUE_URL_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($project[TAG_UNIQUE_URL_EN]) . "</en>";
		}
		$xml .= "\n\t\t\t\t</unique_url>";
        if(isset($project[TAG_OFFICE_ID]))
        {
            $xml .= "\n\t\t\t\t<office_id>" . $project[TAG_OFFICE_ID] .  "</office_id>";
        }
        else
        {
            $xml .= "\n\t\t\t\t<office_id>1</office_id>";
        }
        if(isset($project[TAG_EMPLOYEE_ID])) $xml .= "\n\t\t\t\t<employee_id>" . $project[TAG_EMPLOYEE_ID] . "</employee_id>";
        $xml .= "\n\t\t\t</general>";
		$xml .= "\n\t\t\t<geo>";
		if(!empty($project[TAG_STREET])) $xml .= "\n\t\t\t\t<street>" . CrawlerTool::encode($project[TAG_STREET]) . "</street>";
		if(!empty($project[TAG_NUMBER])) $xml .= "\n\t\t\t\t<number>" . $project[TAG_NUMBER] . "</number>";
		if(!empty($project[TAG_BOX_NUMBER])) $xml .= "\n\t\t\t\t<box_number>" . $project[TAG_BOX_NUMBER] . "</box_number>";
		if(!empty($project[TAG_ZIP])) $xml .= "\n\t\t\t\t<zip>" . $project[TAG_ZIP] . "</zip>";
		$xml .= "\n\t\t\t\t<city>" . CrawlerTool::encode($project[TAG_CITY]) . "</city>";
		if(!empty($project[TAG_COUNTRY]))$xml .= "\n\t\t\t\t<country>" . CrawlerTool::encode($project[TAG_COUNTRY]) . "</country>";
		if(!empty($project[TAG_LATITUDE])) $xml .= "\n\t\t\t\t<latitude>" . $project[TAG_LATITUDE] . "</latitude>";
		if(!empty($project[TAG_LONGITUDE])) $xml .= "\n\t\t\t\t<longitude>" . $project[TAG_LONGITUDE] . "</longitude>";
		$xml .= "\n\t\t\t</geo>";
        $xml .= "\n\t\t\t<media>";
        if(!empty($project[TAG_PICTURES]))
        {
            $xml .= "\n\t\t\t\t<pictures>";
            $pics = $project[TAG_PICTURES];
            foreach($pics as $pic)
            {
                $xml .= "\n\t\t\t\t\t<picture>";
                $xml .= "\n\t\t\t\t\t\t<url>" . CrawlerTool::encode($pic[TAG_PICTURE_URL]) . "</url>";
                if(isset($pic[TAG_PICTURE_TITLE_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_NL]) . "</nl>";
                    if(isset($pic[TAG_PICTURE_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_FR]) . "</fr>";
                    if(isset($pic[TAG_PICTURE_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_DE]) . "</de>";
                    if(isset($pic[TAG_PICTURE_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</title>";
                }
                else if(isset($pic[TAG_PICTURE_TITLE_FR]) || isset($pic[TAG_PICTURE_TITLE_EN]) || isset($pic[TAG_PICTURE_TITLE_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($pic[TAG_PICTURE_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_FR]) . "</fr>";
                    if(isset($pic[TAG_PICTURE_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_DE]) . "</de>";
                    if(isset($pic[TAG_PICTURE_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</title>";
                }

                if(isset($pic[TAG_PICTURE_DESC_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_NL]) . "</nl>";
                    if(isset($pic[TAG_PICTURE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_FR]) . "</fr>";
                    if(isset($pic[TAG_PICTURE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_DE]) . "</de>";
                    if(isset($pic[TAG_PICTURE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                else if(isset($pic[TAG_PICTURE_DESC_FR]) || isset($pic[TAG_PICTURE_DESC_EN]) || isset($pic[TAG_PICTURE_DESC_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($pic[TAG_PICTURE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_FR]) . "</fr>";
                    if(isset($pic[TAG_PICTURE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_DE]) . "</de>";
                    if(isset($pic[TAG_PICTURE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                $xml .= "\n\t\t\t\t\t</picture>";
            }
            $xml .= "\n\t\t\t\t</pictures>";
        }

        if(!empty($project[TAG_FILES]))
        {
            $xml .= "\n\t\t\t\t<files>";
            $files = $project[TAG_FILES];
            foreach($files as $file)
            {
                $xml .= "\n\t\t\t\t\t<file>";
                if(isset($file[TAG_FILE_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $file[TAG_FILE_FLOOR] . "</floor>";
                $xml .= "\n\t\t\t\t\t\t<url>";
                if(isset($file[TAG_FILE_URL_NL])) $xml .= "\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($file[TAG_FILE_URL_NL]) . "</nl>";
                if(isset($file[TAG_FILE_URL_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_URL_FR]) . "</fr>";
                if(isset($file[TAG_FILE_URL_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_URL_DE]) . "</de>";
                if(isset($file[TAG_FILE_URL_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_URL_EN]) . "</en>";
                $xml .= "\n\t\t\t\t\t\t</url>";
                if(isset($file[TAG_FILE_TITLE_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($file[TAG_FILE_TITLE_NL]) . "</nl>";
                    if(isset($file[TAG_FILE_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_TITLE_FR]) . "</fr>";
                    if(isset($file[TAG_FILE_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_TITLE_DE]) . "</de>";
                    if(isset($file[TAG_FILE_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_TITLE_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</title>";
                }
                else if(isset($file[TAG_FILE_TITLE_FR]) || isset($file[TAG_FILE_TITLE_EN]) || isset($file[TAG_FILE_TITLE_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($file[TAG_FILE_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_TITLE_FR]) . "</fr>";
                    if(isset($file[TAG_FILE_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_TITLE_DE]) . "</de>";
                    if(isset($file[TAG_FILE_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_TITLE_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</title>";
                }

                if(isset($file[TAG_FILE_DESC_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($file[TAG_FILE_DESC_NL]) . "</nl>";
                    if(isset($file[TAG_FILE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_DESC_FR]) . "</fr>";
                    if(isset($file[TAG_FILE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_DESC_DE]) . "</de>";
                    if(isset($file[TAG_FILE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_DESC_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                else if(isset($file[TAG_FILE_DESC_FR]) || isset($file[TAG_FILE_DESC_EN]) || isset($file[TAG_FILE_DESC_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($file[TAG_FILE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_DESC_FR]) . "</fr>";
                    if(isset($file[TAG_FILE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_DESC_DE]) . "</de>";
                    if(isset($file[TAG_FILE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_DESC_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                $xml .= "\n\t\t\t\t\t</file>";
            }
            $xml .= "\n\t\t\t\t</files>";
        }

        if(!empty($project[TAG_VIDEOS]))
        {
            $xml .= "\n\t\t\t\t<videos>";
            $videos = $project[TAG_VIDEOS];
            foreach($videos as $video)
            {
                $xml .= "\n\t\t\t\t\t<video>";
                $xml .= "\n\t\t\t\t\t\t<url>";
                if(isset($video[TAG_VIDEO_URL_NL])) $xml .= "\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($video[TAG_VIDEO_URL_NL]) . "</nl>";
                if(isset($video[TAG_VIDEO_URL_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($video[TAG_VIDEO_URL_FR]) . "</fr>";
                if(isset($video[TAG_VIDEO_URL_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_URL_DE]) . "</de>";
                if(isset($video[TAG_VIDEO_URL_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_URL_EN]) . "</en>";
                $xml .= "\n\t\t\t\t\t\t</url>";
                if(isset($video[TAG_VIDEO_TITLE_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_NL]) . "</nl>";
                    if(isset($video[TAG_VIDEO_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_FR]) . "</fr>";
                    if(isset($video[TAG_VIDEO_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_DE]) . "</de>";
                    if(isset($video[TAG_VIDEO_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</title>";
                }
                else if(isset($video[TAG_VIDEO_TITLE_FR]) || isset($video[TAG_VIDEO_TITLE_EN]) || isset($video[TAG_VIDEO_TITLE_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($video[TAG_VIDEO_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_FR]) . "</fr>";
                    if(isset($video[TAG_VIDEO_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_DE]) . "</de>";
                    if(isset($video[TAG_VIDEO_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</title>";
                }

                if(isset($video[TAG_VIDEO_DESC_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_NL]) . "</nl>";
                    if(isset($video[TAG_VIDEO_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_FR]) . "</fr>";
                    if(isset($video[TAG_VIDEO_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_DE]) . "</de>";
                    if(isset($video[TAG_VIDEO_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                else if(isset($video[TAG_VIDEO_DESC_FR]) || isset($video[TAG_VIDEO_DESC_EN]) || isset($video[TAG_VIDEO_DESC_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($video[TAG_VIDEO_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\tt<fr>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_FR]) . "</fr>";
                    if(isset($video[TAG_VIDEO_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_DE]) . "</de>";
                    if(isset($video[TAG_VIDEO_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                $xml .= "\n\t\t\t\t\t</video>";
            }
            $xml .= "\n\t\t\t\t</videos>";
        }

        if(!empty($project[TAG_FLOOR_PLANS]))
        {
            $xml .= "\n\t\t\t\t<floorplans>";
            $plans = $project[TAG_FLOOR_PLANS];
            foreach($plans as $plan)
            {
                $xml .= "\n\t\t\t\t\t<plan>";
                if(isset($plan[TAG_FLOOR_PLAN_FLOOR_LEVEL])) $xml .= "\n\t\t\t\t\t\t<floor_level>" . $plan[TAG_FLOOR_PLAN_FLOOR_LEVEL] . "</floor_level>";
                $xml .= "\n\t\t\t\t\t\t<url>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_URL]) . "</url>";
                if(isset($plan[TAG_FLOOR_PLAN_TITLE_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_NL]) . "</nl>";
                    if(isset($plan[TAG_FLOOR_PLAN_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_FR]) . "</fr>";
                    if(isset($plan[TAG_FLOOR_PLAN_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_DE]) . "</de>";
                    if(isset($plan[TAG_FLOOR_PLAN_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</title>";
                }
                else if(isset($plan[TAG_FLOOR_PLAN_TITLE_FR]) || isset($plan[TAG_FLOOR_PLAN_TITLE_EN]) || isset($plan[TAG_FLOOR_PLAN_TITLE_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($plan[TAG_FLOOR_PLAN_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_FR]) . "</fr>";
                    if(isset($plan[TAG_FLOOR_PLAN_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_DE]) . "</de>";
                    if(isset($plan[TAG_FLOOR_PLAN_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</title>";
                }

                if(isset($plan[TAG_FLOOR_PLAN_DESC_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_NL]) . "</nl>";
                    if(isset($plan[TAG_FLOOR_PLAN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_FR]) . "</fr>";
                    if(isset($plan[TAG_FLOOR_PLAN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_DE]) . "</de>";
                    if(isset($plan[TAG_FLOOR_PLAN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                else if(isset($plan[TAG_FLOOR_PLAN_DESC_FR]) || isset($plan[TAG_FLOOR_PLAN_DESC_EN]) || isset($plan[TAG_FLOOR_PLAN_DESC_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($plan[TAG_FLOOR_PLAN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_FR]) . "</fr>";
                    if(isset($plan[TAG_FLOOR_PLAN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_DE]) . "</de>";
                    if(isset($plan[TAG_FLOOR_PLAN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_EN]) . "</en>";

                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                $xml .= "\n\t\t\t\t\t</plan>";
            }
            $xml .= "\n\t\t\t\t</floorplans>";
        }

        if(!empty($project[TAG_VIRTUAL_VISITS]))
        {
            $xml .= "\n\t\t\t\t<virtual_visits>";
            $virtuals = $project[TAG_VIRTUAL_VISITS];
            foreach($virtuals as $virtual)
            {
                $xml .= "\n\t\t\t\t\t<virtual>";
                $xml .= "\n\t\t\t\t\t\t<url>";
                if(isset($virtual[TAG_VIRTUAL_VISIT_URL_NL])) $xml .= "\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_URL_NL]) . "</nl>";
                if(isset($virtual[TAG_VIRTUAL_VISIT_URL_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_URL_FR]) . "</fr>";
                if(isset($virtual[TAG_VIRTUAL_VISIT_URL_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_URL_DE]) . "</de>";
                if(isset($virtual[TAG_VIRTUAL_VISIT_URL_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_URL_EN]) . "</en>";
                $xml .= "\n\t\t\t\t\t\t</url>";
                if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_NL]) . "</nl>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_FR]) . "</fr>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_DE]) . "</de>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_EN]) . "</en>";

                    $xml .= "\n\t\t\t\t\t\t</title>";
                }
                else if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_FR]) || isset($virtual[TAG_VIRTUAL_VISIT_TITLE_EN]) || isset($virtual[TAG_VIRTUAL_VISIT_TITLE_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_FR]) . "</fr>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_DE]) . "</de>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_EN]) . "</en>";

                    $xml .= "\n\t\t\t\t\t\t</title>";
                }

                if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_NL]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_NL]) . "</nl>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_FR]) . "</fr>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_DE]) . "</de>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                else if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_FR]) || isset($virtual[TAG_VIRTUAL_VISIT_DESC_EN]) || isset($virtual[TAG_VIRTUAL_VISIT_DESC_DE]))
                {
                    $xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_FR]) . "</fr>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_DE]) . "</de>";
                    if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_EN]) . "</en>";
                    $xml .= "\n\t\t\t\t\t\t</description>";
                }
                $xml .= "\n\t\t\t\t\t</virtual>";
            }
            $xml .= "\n\t\t\t\t</virtual_visits>";
        }
        $xml .= "\n\t\t\t</media>";
		$xml .= "\n\t\t\t<text>";

		if(isset($project[TAG_TEXT_TITLE_NL]))
		{
			$xml .= "\n\t\t\t\t<title>\n\t\t\t\t\t<nl>" . CrawlerTool::encode($project[TAG_TEXT_TITLE_NL]) . "</nl>";
			if(isset($project[TAG_TEXT_TITLE_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($project[TAG_TEXT_TITLE_FR]) . "</fr>";
			if(isset($project[TAG_TEXT_TITLE_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($project[TAG_TEXT_TITLE_DE]) . "</de>";
			if(isset($project[TAG_TEXT_TITLE_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($project[TAG_TEXT_TITLE_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</title>";
		}
		else if(isset($project[TAG_TEXT_TITLE_FR]) || isset($project[TAG_TEXT_TITLE_EN]) || isset($project[TAG_TEXT_TITLE_DE]))
		{
			$xml .= "\n\t\t\t\t<title>\n\t\t\t\t\t<nl></nl>";
			if(isset($project[TAG_TEXT_TITLE_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($project[TAG_TEXT_TITLE_FR]) . "</fr>";
			if(isset($project[TAG_TEXT_TITLE_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($project[TAG_TEXT_TITLE_DE]) . "</de>";
			if(isset($project[TAG_TEXT_TITLE_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($project[TAG_TEXT_TITLE_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</title>";
		}

		if(isset($project[TAG_TEXT_SHORT_DESC_NL]))
		{
			$xml .= "\n\t\t\t\t<short_description>\n\t\t\t\t\t<nl>" . CrawlerTool::encode($project[TAG_TEXT_SHORT_DESC_NL]) . "</nl>";
			if(isset($project[TAG_TEXT_SHORT_DESC_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($project[TAG_TEXT_SHORT_DESC_FR]) . "</fr>";
			if(isset($project[TAG_TEXT_SHORT_DESC_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($project[TAG_TEXT_SHORT_DESC_DE]) . "</de>";
			if(isset($project[TAG_TEXT_SHORT_DESC_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($project[TAG_TEXT_SHORT_DESC_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</short_description>";
		}
		else if(isset($project[TAG_TEXT_SHORT_DESC_FR]) || isset($project[TAG_TEXT_SHORT_DESC_EN]) || isset($project[TAG_TEXT_SHORT_DESC_DE]))
		{
			$xml .= "\n\t\t\t\t<short_description>\n\t\t\t\t\t<nl></nl>";
			if(isset($project[TAG_TEXT_SHORT_DESC_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($project[TAG_TEXT_SHORT_DESC_FR]) . "</fr>";
			if(isset($project[TAG_TEXT_SHORT_DESC_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($project[TAG_TEXT_SHORT_DESC_DE]) . "</de>";
			if(isset($project[TAG_TEXT_SHORT_DESC_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($project[TAG_TEXT_SHORT_DESC_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</short_description>";
		}

		if(isset($project[TAG_TEXT_DESC_NL]))
		{
			$xml .= "\n\t\t\t\t<description>\n\t\t\t\t\t<nl>" . CrawlerTool::encode($project[TAG_TEXT_DESC_NL]) . "</nl>";
			if(isset($project[TAG_TEXT_DESC_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($project[TAG_TEXT_DESC_FR]) . "</fr>";
			if(isset($project[TAG_TEXT_DESC_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($project[TAG_TEXT_DESC_DE]) . "</de>";
			if(isset($project[TAG_TEXT_DESC_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($project[TAG_TEXT_DESC_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</description>";
		}
		else if(isset($project[TAG_TEXT_DESC_FR]) || isset($project[TAG_TEXT_DESC_EN]) || isset($project[TAG_TEXT_DESC_DE]))
		{
			$xml .= "\n\t\t\t\t<description>\n\t\t\t\t\t<nl></nl>";
			if(isset($project[TAG_TEXT_DESC_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($project[TAG_TEXT_DESC_FR]) . "</fr>";
			if(isset($project[TAG_TEXT_DESC_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($project[TAG_TEXT_DESC_DE]) . "</de>";
			if(isset($project[TAG_TEXT_DESC_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($project[TAG_TEXT_DESC_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</description>";
		}
		$xml .= "\n\t\t\t</text>";
		if(isset($project[TAG_SOLD_PERCENTAGE_MAX]) || isset($project[TAG_SOLD_PERCENTAGE_MAX]))
		{
			$xml .= "\n\t\t\t<sold>\n\t\t\t\t<percentage>";
			if(isset($project[TAG_SOLD_PERCENTAGE_MAX])) $xml .= "\n\t\t\t\t\t<max>" . $project[TAG_SOLD_PERCENTAGE_MAX] . "</max>";
			if(isset($project[TAG_SOLD_PERCENTAGE_VALUE])) $xml .= "\n\t\t\t\t\t<value>" . $project[TAG_SOLD_PERCENTAGE_VALUE] . "</value>";
			$xml .= "\n\t\t\t\t</percentage>\n\t\t\t</sold>";
		}

		if(isset($project[TAG_REALIZATION_DATE]) || isset($project[TAG_REALIZATION_COMMENTS]) || isset($project[TAG_REALIZATION_FINISHED]))
		{
			$xml .= "\n\t\t\t<realization>";
			if(isset($project[TAG_REALIZATION_DATE])) $xml .= "\n\t\t\t\t<date>" . $project[TAG_REALIZATION_DATE] . "</date>";
			if(isset($project[TAG_REALIZATION_COMMENTS])) $xml .= "\n\t\t\t\t<comments>" . CrawlerTool::encode($project[TAG_REALIZATION_COMMENTS]) . "</comments>";
			if(isset($project[TAG_REALIZATION_FINISHED])) $xml .= "\n\t\t\t\t<finished>" . $project[TAG_REALIZATION_FINISHED] . "</finished>";
			$xml .= "\n\t\t\t</realization>";
		}

		if(!empty($project[TAG_ARCHIVE])) $xml .= "\n\t\t\t<archive>" . $project[TAG_ARCHIVE] . "</archive>";
		if(!empty($project[TAG_ARCHIVE_DATE])) $xml .= "\n\t\t\t<archive_date>" . $project[TAG_ARCHIVE_DATE] . "</archive_date>";

		$xml .= "\n\t\t</project>";

		file_put_contents(OUTPUT_PROJECT_FILE_NAME, $xml, FILE_APPEND);
	}

	public static function saveProperty($property)
	{
        // search and set property data at Crawler Class level
        CrawlerTool::findMoreData($property);
        // apply some rules at Crawler Class level
        CrawlerTool::applyRules($property);

        self::$saved_properties++;

		$xml = "\n\t\t<property>";
		$xml .= "\n\t\t\t<general>";
		$xml .= "\n\t\t\t\t<unique_id>" . $property[TAG_UNIQUE_ID] .  "</unique_id>";
        if(empty($property[TAG_OFFICE_ID]))
        {
            $xml .= "\n\t\t\t\t<office_id>1</office_id>";
        }
        else
        {
            $xml .= "\n\t\t\t\t<office_id>" . $property[TAG_OFFICE_ID] .  "</office_id>";
        }
        if(!empty($property[TAG_EMPLOYEE_IDS]))
        {
            $xml .= "\n\t\t\t\t<employee_ids>";
            $employeeIds = $property[TAG_EMPLOYEE_IDS];
            foreach($employeeIds as $employeeId)
            {
                $xml .= "\n\t\t\t\t\t<employee_id>" . $employeeId . "</employee_id>";
            }
            $xml .= "\n\t\t\t\t</employee_ids>";
        }
		if(isset($property[TAG_PROJECT_ID])) $xml .= "\n\t\t\t\t<project_id>" . $property[TAG_PROJECT_ID] . "</project_id>";
		$xml .= "\n\t\t\t\t<unique_url>";
		if(isset($property[TAG_UNIQUE_URL_NL]))
		{
			$xml .= "\n\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_UNIQUE_URL_NL]) . "</nl>";
		}
		else
		{
			$xml .= "\n\t\t\t\t\t<nl></nl>";
		}

		if(isset($property[TAG_UNIQUE_URL_FR]))
		{
			$xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_UNIQUE_URL_FR]) . "</fr>";
		}
		else
		{
			$xml .= "\n\t\t\t\t\t<fr></fr>";
		}

		if(isset($property[TAG_UNIQUE_URL_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_UNIQUE_URL_DE]) . "</de>";
		if(isset($property[TAG_UNIQUE_URL_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_UNIQUE_URL_EN]) . "</en>";
		$xml .= "\n\t\t\t\t</unique_url>";
		$xml .= "\n\t\t\t\t<status>" . $property[TAG_STATUS] . "</status>";
		$xml .= "\n\t\t\t\t<type>" . $property[TAG_TYPE] . "</type>";
		if(!empty($property[TAG_TYPE_LONG])) $xml .= "\n\t\t\t\t<type_long>" . CrawlerTool::encode($property[TAG_TYPE_LONG]) . "</type_long>";
		if(!empty($property[TAG_ARCHIVE])) $xml .= "\n\t\t\t\t<archive>" . $property[TAG_ARCHIVE] . "</archive>";
		if(!empty($property[TAG_ARCHIVE_DATE])) $xml .= "\n\t\t\t\t<archive_date>" . $property[TAG_ARCHIVE_DATE] . "</archive_date>";
		$xml .= "\n\t\t\t</general>";
		$xml .= "\n\t\t\t<geo>";
		if(!empty($property[TAG_STREET])) $xml .= "\n\t\t\t\t<street>" . CrawlerTool::encode($property[TAG_STREET]) . "</street>";
		if(!empty($property[TAG_NUMBER])) $xml .= "\n\t\t\t\t<number>" . $property[TAG_NUMBER] . "</number>";
		if(!empty($property[TAG_BOX_NUMBER])) $xml .= "\n\t\t\t\t<box_number>" . $property[TAG_BOX_NUMBER] . "</box_number>";
		if(!empty($property[TAG_ZIP]))       $xml .= "\n\t\t\t\t<zip>" . $property[TAG_ZIP] . "</zip>";

		$xml .= "\n\t\t\t\t<city>" . CrawlerTool::encode($property[TAG_CITY]) . "</city>";
		if(isset($property[TAG_COUNTRY]))
		{
			$xml .= "\n\t\t\t\t<country>" . CrawlerTool::encode($property[TAG_COUNTRY]) . "</country>";
		}
		else
		{
			$xml .= "\n\t\t\t\t<country></country>";
		}
		if(isset($property[TAG_ADDRESS_VISIBLE])) $xml .= "\n\t\t\t\t<address_visible>" . $property[TAG_ADDRESS_VISIBLE] . "</address_visible>";
		if(isset($property[TAG_NUMBER_VISIBLE])) $xml .= "\n\t\t\t\t<number_visible>" . $property[TAG_NUMBER_VISIBLE] . "</number_visible>";
		if(!empty($property[TAG_LATITUDE])) $xml .= "\n\t\t\t\t<latitude>" . $property[TAG_LATITUDE] . "</latitude>";
		if(!empty($property[TAG_LONGITUDE])) $xml .= "\n\t\t\t\t<longitude>" . $property[TAG_LONGITUDE] . "</longitude>";
		$xml .= "\n\t\t\t</geo>";
		$xml .= "\n\t\t\t<media>";
		if(!empty($property[TAG_PICTURES]))
		{
			$xml .= "\n\t\t\t\t<pictures>";
			$pics = $property[TAG_PICTURES];
			foreach($pics as $pic)
			{
				$xml .= "\n\t\t\t\t\t<picture>";
				$xml .= "\n\t\t\t\t\t\t<url>" . CrawlerTool::encode($pic[TAG_PICTURE_URL]) . "</url>";
				if(isset($pic[TAG_PICTURE_TITLE_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_NL]) . "</nl>";
					if(isset($pic[TAG_PICTURE_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_FR]) . "</fr>";
					if(isset($pic[TAG_PICTURE_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_DE]) . "</de>";
					if(isset($pic[TAG_PICTURE_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</title>";
				}
				else if(isset($pic[TAG_PICTURE_TITLE_FR]) || isset($pic[TAG_PICTURE_TITLE_EN]) || isset($pic[TAG_PICTURE_TITLE_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($pic[TAG_PICTURE_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_FR]) . "</fr>";
					if(isset($pic[TAG_PICTURE_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_DE]) . "</de>";
					if(isset($pic[TAG_PICTURE_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($pic[TAG_PICTURE_TITLE_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</title>";
				}

				if(isset($pic[TAG_PICTURE_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_NL]) . "</nl>";
					if(isset($pic[TAG_PICTURE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_FR]) . "</fr>";
					if(isset($pic[TAG_PICTURE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_DE]) . "</de>";
					if(isset($pic[TAG_PICTURE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($pic[TAG_PICTURE_DESC_FR]) || isset($pic[TAG_PICTURE_DESC_EN]) || isset($pic[TAG_PICTURE_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($pic[TAG_PICTURE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_FR]) . "</fr>";
					if(isset($pic[TAG_PICTURE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_DE]) . "</de>";
					if(isset($pic[TAG_PICTURE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($pic[TAG_PICTURE_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				$xml .= "\n\t\t\t\t\t</picture>";
			}
			$xml .= "\n\t\t\t\t</pictures>";
		}

		if(!empty($property[TAG_FILES]))
		{
			$xml .= "\n\t\t\t\t<files>";
			$files = $property[TAG_FILES];
			foreach($files as $file)
			{
				$xml .= "\n\t\t\t\t\t<file>";
                if(isset($file[TAG_FILE_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $file[TAG_FILE_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t\t<url>";
                // will delete TAG_FILE_URL after crawlers update
                if(isset($file[TAG_FILE_URL]))
                {
                    if(!empty($property[TAG_UNIQUE_URL_NL])) $file[TAG_FILE_URL_NL] = $file[TAG_FILE_URL];
                    if(!empty($property[TAG_UNIQUE_URL_FR])) $file[TAG_FILE_URL_FR] = $file[TAG_FILE_URL];
                    if(!empty($property[TAG_UNIQUE_URL_DE])) $file[TAG_FILE_URL_DE] = $file[TAG_FILE_URL];
                    if(!empty($property[TAG_UNIQUE_URL_EN])) $file[TAG_FILE_URL_EN] = $file[TAG_FILE_URL];
                }

                if(isset($file[TAG_FILE_URL_NL])) $xml .= "\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($file[TAG_FILE_URL_NL]) . "</nl>";
                if(isset($file[TAG_FILE_URL_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_URL_FR]) . "</fr>";
                if(isset($file[TAG_FILE_URL_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_URL_DE]) . "</de>";
                if(isset($file[TAG_FILE_URL_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_URL_EN]) . "</en>";
                $xml .= "\n\t\t\t\t\t\t</url>";
				if(isset($file[TAG_FILE_TITLE_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($file[TAG_FILE_TITLE_NL]) . "</nl>";
					if(isset($file[TAG_FILE_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_TITLE_FR]) . "</fr>";
					if(isset($file[TAG_FILE_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_TITLE_DE]) . "</de>";
					if(isset($file[TAG_FILE_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_TITLE_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</title>";
				}
				else if(isset($file[TAG_FILE_TITLE_FR]) || isset($file[TAG_FILE_TITLE_EN]) || isset($file[TAG_FILE_TITLE_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($file[TAG_FILE_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_TITLE_FR]) . "</fr>";
					if(isset($file[TAG_FILE_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_TITLE_DE]) . "</de>";
					if(isset($file[TAG_FILE_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_TITLE_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</title>";
				}

				if(isset($file[TAG_FILE_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($file[TAG_FILE_DESC_NL]) . "</nl>";
					if(isset($file[TAG_FILE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_DESC_FR]) . "</fr>";
					if(isset($file[TAG_FILE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_DESC_DE]) . "</de>";
					if(isset($file[TAG_FILE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($file[TAG_FILE_DESC_FR]) || isset($file[TAG_FILE_DESC_EN]) || isset($file[TAG_FILE_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($file[TAG_FILE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($file[TAG_FILE_DESC_FR]) . "</fr>";
					if(isset($file[TAG_FILE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($file[TAG_FILE_DESC_DE]) . "</de>";
					if(isset($file[TAG_FILE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($file[TAG_FILE_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				$xml .= "\n\t\t\t\t\t</file>";
			}
			$xml .= "\n\t\t\t\t</files>";
		}

		if(!empty($property[TAG_VIDEOS]))
		{
			$xml .= "\n\t\t\t\t<videos>";
			$videos = $property[TAG_VIDEOS];
			foreach($videos as $video)
			{
				$xml .= "\n\t\t\t\t\t<video>";
                $xml .= "\n\t\t\t\t\t\t<url>";
                // will delete TAG_VIDEO_URL after crawlers update
                if(isset($video[TAG_VIDEO_URL]))
                {
                    if(!empty($property[TAG_UNIQUE_URL_NL])) $video[TAG_VIDEO_URL_NL] = $video[TAG_VIDEO_URL];
                    if(!empty($property[TAG_UNIQUE_URL_FR])) $video[TAG_VIDEO_URL_FR] = $video[TAG_VIDEO_URL];
                    if(!empty($property[TAG_UNIQUE_URL_DE])) $video[TAG_VIDEO_URL_DE] = $video[TAG_VIDEO_URL];
                    if(!empty($property[TAG_UNIQUE_URL_EN])) $video[TAG_VIDEO_URL_EN] = $video[TAG_VIDEO_URL];
                }

                if(isset($video[TAG_VIDEO_URL_NL])) $xml .= "\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($video[TAG_VIDEO_URL_NL]) . "</nl>";
                if(isset($video[TAG_VIDEO_URL_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($video[TAG_VIDEO_URL_FR]) . "</fr>";
                if(isset($video[TAG_VIDEO_URL_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_URL_DE]) . "</de>";
                if(isset($video[TAG_VIDEO_URL_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_URL_EN]) . "</en>";
                $xml .= "\n\t\t\t\t\t\t</url>";
				if(isset($video[TAG_VIDEO_TITLE_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_NL]) . "</nl>";
					if(isset($video[TAG_VIDEO_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_FR]) . "</fr>";
					if(isset($video[TAG_VIDEO_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_DE]) . "</de>";
					if(isset($video[TAG_VIDEO_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</title>";
				}
				else if(isset($video[TAG_VIDEO_TITLE_FR]) || isset($video[TAG_VIDEO_TITLE_EN]) || isset($video[TAG_VIDEO_TITLE_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($video[TAG_VIDEO_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_FR]) . "</fr>";
					if(isset($video[TAG_VIDEO_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_DE]) . "</de>";
					if(isset($video[TAG_VIDEO_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_TITLE_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</title>";
				}

				if(isset($video[TAG_VIDEO_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_NL]) . "</nl>";
					if(isset($video[TAG_VIDEO_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_FR]) . "</fr>";
					if(isset($video[TAG_VIDEO_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_DE]) . "</de>";
					if(isset($video[TAG_VIDEO_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($video[TAG_VIDEO_DESC_FR]) || isset($video[TAG_VIDEO_DESC_EN]) || isset($video[TAG_VIDEO_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($video[TAG_VIDEO_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\tt<fr>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_FR]) . "</fr>";
					if(isset($video[TAG_VIDEO_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_DE]) . "</de>";
					if(isset($video[TAG_VIDEO_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($video[TAG_VIDEO_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				$xml .= "\n\t\t\t\t\t</video>";
			}
			$xml .= "\n\t\t\t\t</videos>";
		}

		if(!empty($property[TAG_FLOOR_PLANS]))
		{
			$xml .= "\n\t\t\t\t<floorplans>";
			$plans = $property[TAG_FLOOR_PLANS];
			foreach($plans as $plan)
			{
				$xml .= "\n\t\t\t\t\t<plan>";
				if(isset($plan[TAG_FLOOR_PLAN_FLOOR_LEVEL])) $xml .= "\n\t\t\t\t\t\t<floor_level>" . $plan[TAG_FLOOR_PLAN_FLOOR_LEVEL] . "</floor_level>";
				$xml .= "\n\t\t\t\t\t\t<url>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_URL]) . "</url>";
				if(isset($plan[TAG_FLOOR_PLAN_TITLE_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_NL]) . "</nl>";
					if(isset($plan[TAG_FLOOR_PLAN_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_FR]) . "</fr>";
					if(isset($plan[TAG_FLOOR_PLAN_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_DE]) . "</de>";
					if(isset($plan[TAG_FLOOR_PLAN_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</title>";
				}
				else if(isset($plan[TAG_FLOOR_PLAN_TITLE_FR]) || isset($plan[TAG_FLOOR_PLAN_TITLE_EN]) || isset($plan[TAG_FLOOR_PLAN_TITLE_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($plan[TAG_FLOOR_PLAN_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_FR]) . "</fr>";
					if(isset($plan[TAG_FLOOR_PLAN_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_DE]) . "</de>";
					if(isset($plan[TAG_FLOOR_PLAN_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_TITLE_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</title>";
				}

				if(isset($plan[TAG_FLOOR_PLAN_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_NL]) . "</nl>";
					if(isset($plan[TAG_FLOOR_PLAN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_FR]) . "</fr>";
					if(isset($plan[TAG_FLOOR_PLAN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_DE]) . "</de>";
					if(isset($plan[TAG_FLOOR_PLAN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($plan[TAG_FLOOR_PLAN_DESC_FR]) || isset($plan[TAG_FLOOR_PLAN_DESC_EN]) || isset($plan[TAG_FLOOR_PLAN_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($plan[TAG_FLOOR_PLAN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_FR]) . "</fr>";
					if(isset($plan[TAG_FLOOR_PLAN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_DE]) . "</de>";
					if(isset($plan[TAG_FLOOR_PLAN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($plan[TAG_FLOOR_PLAN_DESC_EN]) . "</en>";

					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				$xml .= "\n\t\t\t\t\t</plan>";
			}
			$xml .= "\n\t\t\t\t</floorplans>";
		}

		if(!empty($property[TAG_VIRTUAL_VISITS]))
		{
			$xml .= "\n\t\t\t\t<virtual_visits>";
			$virtuals = $property[TAG_VIRTUAL_VISITS];
			foreach($virtuals as $virtual)
			{
				$xml .= "\n\t\t\t\t\t<virtual>";
                $xml .= "\n\t\t\t\t\t\t<url>";
                // will delete TAG_VIRTUAL_VISIT_URL after crawlers update
                if(isset($virtual[TAG_VIRTUAL_VISIT_URL]))
                {
                    if(!empty($property[TAG_UNIQUE_URL_NL])) $virtual[TAG_VIRTUAL_VISIT_URL_NL] = $virtual[TAG_VIRTUAL_VISIT_URL];
                    if(!empty($property[TAG_UNIQUE_URL_FR])) $virtual[TAG_VIRTUAL_VISIT_URL_FR] = $virtual[TAG_VIRTUAL_VISIT_URL];
                    if(!empty($property[TAG_UNIQUE_URL_DE])) $virtual[TAG_VIRTUAL_VISIT_URL_DE] = $virtual[TAG_VIRTUAL_VISITO_URL];
                    if(!empty($property[TAG_UNIQUE_URL_EN])) $virtual[TAG_VIRTUAL_VISIT_URL_EN] = $virtual[TAG_VIRTUAL_VISIT_URL];
                }

                if(isset($virtual[TAG_VIRTUAL_VISIT_URL_NL])) $xml .= "\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_URL_NL]) . "</nl>";
                if(isset($virtual[TAG_VIRTUAL_VISIT_URL_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_URL_FR]) . "</fr>";
                if(isset($virtual[TAG_VIRTUAL_VISIT_URL_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_URL_DE]) . "</de>";
                if(isset($virtual[TAG_VIRTUAL_VISIT_URL_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_URL_EN]) . "</en>";
                $xml .= "\n\t\t\t\t\t\t</url>";
				if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_NL]) . "</nl>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_FR]) . "</fr>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_DE]) . "</de>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_EN]) . "</en>";

					$xml .= "\n\t\t\t\t\t\t</title>";
				}
				else if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_FR]) || isset($virtual[TAG_VIRTUAL_VISIT_TITLE_EN]) || isset($virtual[TAG_VIRTUAL_VISIT_TITLE_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<title>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_FR]) . "</fr>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_DE]) . "</de>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_TITLE_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_TITLE_EN]) . "</en>";

					$xml .= "\n\t\t\t\t\t\t</title>";
				}

				if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_NL]) . "</nl>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_FR]) . "</fr>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_DE]) . "</de>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_FR]) || isset($virtual[TAG_VIRTUAL_VISIT_DESC_EN]) || isset($virtual[TAG_VIRTUAL_VISIT_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_FR]) . "</fr>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_DE]) . "</de>";
					if(isset($virtual[TAG_VIRTUAL_VISIT_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($virtual[TAG_VIRTUAL_VISIT_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				$xml .= "\n\t\t\t\t\t</virtual>";
			}
			$xml .= "\n\t\t\t\t</virtual_visits>";
		}

		$xml .= "\n\t\t\t</media>";
		$xml .= "\n\t\t\t<text>";
		if(!empty($property[TAG_TEXT_TITLE_NL]))
		{
			$xml .= "\n\t\t\t\t<title>\n\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_TEXT_TITLE_NL]) . "</nl>";
			if(!empty($property[TAG_TEXT_TITLE_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_TEXT_TITLE_FR]) . "</fr>";
			if(!empty($property[TAG_TEXT_TITLE_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_TEXT_TITLE_DE]) . "</de>";
			if(!empty($property[TAG_TEXT_TITLE_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_TEXT_TITLE_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</title>";
		}
		else if(!empty($property[TAG_TEXT_TITLE_FR]) || !empty($property[TAG_TEXT_TITLE_EN]) || !empty($property[TAG_TEXT_TITLE_DE]))
		{
			$xml .= "\n\t\t\t\t<title>\n\t\t\t\t\t<nl></nl>";
			if(!empty($property[TAG_TEXT_TITLE_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_TEXT_TITLE_FR]) . "</fr>";
			if(!empty($property[TAG_TEXT_TITLE_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_TEXT_TITLE_DE]) . "</de>";
			if(!empty($property[TAG_TEXT_TITLE_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_TEXT_TITLE_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</title>";
		}

		if(!empty($property[TAG_TEXT_SHORT_DESC_NL]))
		{
			$xml .= "\n\t\t\t\t<short_description>\n\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_TEXT_SHORT_DESC_NL]) . "</nl>";
			if(!empty($property[TAG_TEXT_SHORT_DESC_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_TEXT_SHORT_DESC_FR]) . "</fr>";
			if(!empty($property[TAG_TEXT_SHORT_DESC_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_TEXT_SHORT_DESC_DE]) . "</de>";
			if(!empty($property[TAG_TEXT_SHORT_DESC_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_TEXT_SHORT_DESC_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</short_description>";
		}
		else if(!empty($property[TAG_TEXT_SHORT_DESC_FR]) || !empty($property[TAG_TEXT_SHORT_DESC_EN]) || !empty($property[TAG_TEXT_SHORT_DESC_DE]))
		{
			$xml .= "\n\t\t\t\t<short_description>\n\t\t\t\t\t<nl></nl>";
			if(!empty($property[TAG_TEXT_SHORT_DESC_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_TEXT_SHORT_DESC_FR]) . "</fr>";
			if(!empty($property[TAG_TEXT_SHORT_DESC_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_TEXT_SHORT_DESC_DE]) . "</de>";
			if(!empty($property[TAG_TEXT_SHORT_DESC_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_TEXT_SHORT_DESC_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</short_description>";
		}

		if(!empty($property[TAG_TEXT_DESC_NL]))
		{
			$xml .= "\n\t\t\t\t<description>\n\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_TEXT_DESC_NL]) . "</nl>";
			if(!empty($property[TAG_TEXT_DESC_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_TEXT_DESC_FR]) . "</fr>";
			if(!empty($property[TAG_TEXT_DESC_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_TEXT_DESC_DE]) . "</de>";
			if(!empty($property[TAG_TEXT_DESC_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_TEXT_DESC_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</description>";
		}
		else if(!empty($property[TAG_TEXT_DESC_FR]) || !empty($property[TAG_TEXT_DESC_EN]) || !empty($property[TAG_TEXT_DESC_DE]))
		{
			$xml .= "\n\t\t\t\t<description>\n\t\t\t\t\t<nl></nl>";
			if(!empty($property[TAG_TEXT_DESC_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_TEXT_DESC_FR]) . "</fr>";
			if(!empty($property[TAG_TEXT_DESC_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_TEXT_DESC_DE]) . "</de>";
			if(!empty($property[TAG_TEXT_DESC_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_TEXT_DESC_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</description>";
		}

		$xml .= "\n\t\t\t</text>";
		$xml .= "\n\t\t\t<construction>";
		if(!empty($property[TAG_CONSTRUCTION_TYPE])) $xml .= "\n\t\t\t\t<construction_type>" . $property[TAG_CONSTRUCTION_TYPE] . "</construction_type>";
		if(!empty($property[TAG_CONSTRUCTION_YEAR])) $xml .= "\n\t\t\t\t<construction_year>" . $property[TAG_CONSTRUCTION_YEAR] . "</construction_year>";
		if(!empty($property[TAG_BEDROOMS_TOTAL])) $xml .= "\n\t\t\t\t<bedrooms_total>" . $property[TAG_BEDROOMS_TOTAL] . "</bedrooms_total>";
		if(!empty($property[TAG_BATHROOMS_TOTAL])) $xml .= "\n\t\t\t\t<bathrooms_total>" . $property[TAG_BATHROOMS_TOTAL] . "</bathrooms_total>";
		if(!empty($property[TAG_TOILETS_TOTAL])) $xml .= "\n\t\t\t\t<toilets_total>" . $property[TAG_TOILETS_TOTAL] . "</toilets_total>";
		if(!empty($property[TAG_RENOVATION_YEAR])) $xml .= "\n\t\t\t\t<renovation_year>" . $property[TAG_RENOVATION_YEAR] . "</renovation_year>";
		if(!empty($property[TAG_NEED_TO_RENOVATE])) $xml .= "\n\t\t\t\t<need_to_renovate>" . $property[TAG_NEED_TO_RENOVATE] . "</need_to_renovate>";
		if(!empty($property[TAG_RENOVATION_COSTS])) $xml .= "\n\t\t\t\t<renovation_costs>" . $property[TAG_RENOVATION_COSTS] . "</renovation_costs>";
		if(!empty($property[TAG_IS_NEW_CONSTRUCTION])) $xml .= "\n\t\t\t\t<is_new_construction>" . $property[TAG_IS_NEW_CONSTRUCTION] . "</is_new_construction>";
		if(!empty($property[TAG_IS_INVESTMENT_PROPERTY])) $xml .= "\n\t\t\t\t<is_investment_property>" . $property[TAG_IS_INVESTMENT_PROPERTY] . "</is_investment_property>";
		if(!empty($property[TAG_IS_EXCLUSIVE])) $xml .= "\n\t\t\t\t<is_exclusive>" . $property[TAG_IS_EXCLUSIVE] . "</is_exclusive>";
		if(!empty($property[TAG_IS_VACATION])) $xml .= "\n\t\t\t\t<is_vacation>" . $property[TAG_IS_VACATION] . "</is_vacation>";
		if(!empty($property[TAG_IS_HOMESHARING])) $xml .= "\n\t\t\t\t<is_homesharing>" . $property[TAG_IS_HOMESHARING] . "</is_homesharing>";
		if(!empty($property[TAG_IS_STUDENT])) $xml .= "\n\t\t\t\t<is_student>" . $property[TAG_IS_STUDENT] . "</is_student>";
		if(!empty($property[TAG_IS_PASSIVE])) $xml .= "\n\t\t\t\t<is_passive>" . $property[TAG_IS_PASSIVE] . "</is_passive>";
		if(!empty($property[TAG_IS_LOW_ENERGY])) $xml .= "\n\t\t\t\t<is_low_energy>" . $property[TAG_IS_LOW_ENERGY] . "</is_low_energy>";
		if(!empty($property[TAG_CONSTRUCTION_OBLIGATION])) $xml .= "\n\t\t\t\t<construction_obligation>" . $property[TAG_CONSTRUCTION_OBLIGATION] . "</construction_obligation>";
		if(!empty($property[TAG_SURFACE_GROUND])) $xml .= "\n\t\t\t\t<surface_ground>" . $property[TAG_SURFACE_GROUND] . "</surface_ground>";
		if(!empty($property[TAG_SURFACE_CONSTRUCTION])) $xml .= "\n\t\t\t\t<surface_construction>" . $property[TAG_SURFACE_CONSTRUCTION] . "</surface_construction>";
		if(!empty($property[TAG_SURFACE_LIVING_AREA])) $xml .= "\n\t\t\t\t<surface_living_area>" . $property[TAG_SURFACE_LIVING_AREA] . "</surface_living_area>";
		if(!empty($property[TAG_LOT_WIDTH])) $xml .= "\n\t\t\t\t<lot_width>" . $property[TAG_LOT_WIDTH] . "</lot_width>";
		if(!empty($property[TAG_LOT_DEPTH])) $xml .= "\n\t\t\t\t<lot_depth>" . $property[TAG_LOT_DEPTH] . "</lot_depth>";
		if(!empty($property[TAG_LOT_NUMBER])) $xml .= "\n\t\t\t\t<lot_number>" . $property[TAG_LOT_NUMBER] . "</lot_number>";
		if(!empty($property[TAG_FRONTAGE_WIDTH])) $xml .= "\n\t\t\t\t<frontage_width>" . $property[TAG_FRONTAGE_WIDTH] . "</frontage_width>";
		if(!empty($property[TAG_FLOOR])) $xml .= "\n\t\t\t\t<floor>" . $property[TAG_FLOOR] . "</floor>";
		if(!empty($property[TAG_AMOUNT_OF_FLOORS])) $xml .= "\n\t\t\t\t<amount_of_floors>" . $property[TAG_AMOUNT_OF_FLOORS] . "</amount_of_floors>";
		if(!empty($property[TAG_DEPTH_GROUND_FLOOR])) $xml .= "\n\t\t\t\t<depth_ground_floor>" . $property[TAG_DEPTH_GROUND_FLOOR] . "</depth_ground_floor>";
		if(!empty($property[TAG_DEPTH_FLOOR])) $xml .= "\n\t\t\t\t<depth_floor>" . $property[TAG_DEPTH_FLOOR] . "</depth_floor>";
		if(!empty($property[TAG_DISTANCE_TO_BUILDING_LINE])) $xml .= "\n\t\t\t\t<distance_to_building_line>" . $property[TAG_DISTANCE_TO_BUILDING_LINE] . "</distance_to_building_line>";
		if(!empty($property[TAG_DISTANCE_TO_STREET_AXIS])) $xml .= "\n\t\t\t\t<distance_to_street_axis>" . $property[TAG_DISTANCE_TO_STREET_AXIS] . "</distance_to_street_axis>";
		if(!empty($property[TAG_DISTANCE_TO_LEFT_SIDE_BOUNDARY])) $xml .= "\n\t\t\t\t<distance_to_left_side_boundary>" . $property[TAG_DISTANCE_TO_LEFT_SIDE_BOUNDARY] . "</distance_to_left_side_boundary>";
		if(!empty($property[TAG_DISTANCE_TO_RIGHT_SIDE_BOUNDARY])) $xml .= "\n\t\t\t\t<distance_to_right_side_boundary>" . $property[TAG_DISTANCE_TO_RIGHT_SIDE_BOUNDARY] . "</distance_to_right_side_boundary>";
		if(!empty($property[TAG_AMOUNT_OF_FACADES])) $xml .= "\n\t\t\t\t<amount_of_facades>" . $property[TAG_AMOUNT_OF_FACADES] . "</amount_of_facades>";
		if(!empty($property[TAG_ROOF_SLOPE])) $xml .= "\n\t\t\t\t<roof_slope>" . $property[TAG_ROOF_SLOPE] . "</roof_slope>";
		if(!empty($property[TAG_CADASTRAL_CLASSIFICATION])) $xml .= "\n\t\t\t\t<cadastral_classification>" . $property[TAG_CADASTRAL_CLASSIFICATION] . "</cadastral_classification>";
		if(!empty($property[TAG_CADASTRAL_SECTION])) $xml .= "\n\t\t\t\t<cadastral_section>" . $property[TAG_CADASTRAL_SECTION] . "</cadastral_section>";
		if(!empty($property[TAG_CADASTRAL_KIND])) $xml .= "\n\t\t\t\t<cadastral_kind>" . $property[TAG_CADASTRAL_KIND] . "</cadastral_kind>";
		$xml .= "\n\t\t\t</construction>";
		$xml .= "\n\t\t\t<certificates>";
		if(!empty($property[TAG_EPC_VALUE])) $xml .= "\n\t\t\t\t<epc_value>" . $property[TAG_EPC_VALUE] . "</epc_value>";
		if(!empty($property[TAG_EPC_CERTIFICATE_NUMBER])) $xml .= "\n\t\t\t\t<epc_certificate_number>" . $property[TAG_EPC_CERTIFICATE_NUMBER] . "</epc_certificate_number>";
		if(!empty($property[TAG_CO2_EMISSION])) $xml .= "\n\t\t\t\t<co2_emission>" . $property[TAG_CO2_EMISSION] . "</co2_emission>";
		if(!empty($property[TAG_MAZOUT_CERTIFICATE])) $xml .= "\n\t\t\t\t<mazout_certificate>" . $property[TAG_MAZOUT_CERTIFICATE] . "</mazout_certificate>";
		if(!empty($property[TAG_HAS_ELECTRICAL_INSPECTION_CERTIFICATE])) $xml .= "\n\t\t\t\t<has_electrical_inspection_certificate>" . $property[TAG_HAS_ELECTRICAL_INSPECTION_CERTIFICATE] . "</has_electrical_inspection_certificate>";
		if(!empty($property[TAG_NON_VALID_ELECTRICAL_INSPECTION_CERTIFICATE])) $xml .= "\n\t\t\t\t<non_valid_electrical_inspection_certificate>" . $property[TAG_NON_VALID_ELECTRICAL_INSPECTION_CERTIFICATE] . "</non_valid_electrical_inspection_certificate>";
		if(!empty($property[TAG_K_LEVEL])) $xml .= "\n\t\t\t\t<k_level>" . $property[TAG_K_LEVEL] . "</k_level>";
		if(!empty($property[TAG_E_LEVEL])) $xml .= "\n\t\t\t\t<e_level>" . $property[TAG_E_LEVEL] . "</e_level>";
		if(!empty($property[TAG_AS_BUILD_ATTEST])) $xml .= "\n\t\t\t\t<as_build_attest>" . $property[TAG_AS_BUILD_ATTEST] . "</as_build_attest>";
		$xml .= "\n\t\t\t</certificates>";
		$xml .= "\n\t\t\t<planning>";

		if(!empty($property[TAG_PLANNING_PERMISSION]))
		{
			$xml .= "\n\t\t\t\t<planning_permission>";
			$xml .= "\n\t\t\t\t\t<has_permission>" . $property[TAG_PLANNING_PERMISSION] . "</has_permission>";
			if(isset($property[TAG_PLANNING_PERMISSION_INFORMATION_NL]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_PLANNING_PERMISSION_INFORMATION_NL]) . "</nl>";
				if(isset($property[TAG_PLANNING_PERMISSION_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_PLANNING_PERMISSION_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_PLANNING_PERMISSION_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_PLANNING_PERMISSION_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_PLANNING_PERMISSION_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_PLANNING_PERMISSION_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			else if(isset($property[TAG_PLANNING_PERMISSION_INFORMATION_FR]) || isset($property[TAG_PLANNING_PERMISSION_INFORMATION_EN]) || isset($property[TAG_PLANNING_PERMISSION_INFORMATION_DE]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl></nl>";
				if(isset($property[TAG_PLANNING_PERMISSION_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_PLANNING_PERMISSION_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_PLANNING_PERMISSION_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_PLANNING_PERMISSION_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_PLANNING_PERMISSION_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_PLANNING_PERMISSION_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			$xml .= "\n\t\t\t\t</planning_permission>";
		}

		if(!empty($property[TAG_HAS_PROCEEDING]))
		{
			$xml .= "\n\t\t\t\t<proceeding>";
			$xml .= "\n\t\t\t\t\t<has_proceeding>" . $property[TAG_HAS_PROCEEDING] . "</has_proceeding>";
			if(isset($property[TAG_PROCEEDING_INFORMATION_NL]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_PROCEEDING_INFORMATION_NL]) . "</nl>";
				if(isset($property[TAG_PROCEEDING_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_PROCEEDING_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_PROCEEDING_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_PROCEEDING_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_PROCEEDING_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_PROCEEDING_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			else if(isset($property[TAG_PROCEEDING_INFORMATION_FR]) || isset($property[TAG_PROCEEDING_INFORMATION_EN]) || isset($property[TAG_PROCEEDING_INFORMATION_DE]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl></nl>";
				if(isset($property[TAG_PROCEEDING_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_PROCEEDING_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_PROCEEDING_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_PROCEEDING_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_PROCEEDING_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_PROCEEDING_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			$xml .= "\n\t\t\t\t</proceeding>";
		}

		if(!empty($property[TAG_PRIORITY_PURCHASE]))
		{
			$xml .= "\n\t\t\t\t<priority_purchase>";
			$xml .= "\n\t\t\t\t\t<has_priority_purchase>" . $property[TAG_PRIORITY_PURCHASE] . "</has_priority_purchase>";
			if(isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_NL]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_PRIORITY_PURCHASE_INFORMATION_NL]) . "</nl>";
				if(isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_PRIORITY_PURCHASE_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_PRIORITY_PURCHASE_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_PRIORITY_PURCHASE_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			else if(isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_FR]) || isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_EN]) || isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_DE]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl></nl>";
				if(isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_PRIORITY_PURCHASE_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_PRIORITY_PURCHASE_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_PRIORITY_PURCHASE_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_PRIORITY_PURCHASE_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			$xml .= "\n\t\t\t\t</priority_purchase>";
		}

		if(!empty($property[TAG_SUBDIVISION_PERMIT]))
		{
			$xml .= "\n\t\t\t\t<subdivision_permit>";
			$xml .= "\n\t\t\t\t\t<permitted>" . $property[TAG_SUBDIVISION_PERMIT] . "</permitted>";
			if(isset($property[TAG_SUBDIVISION_INFORMATION_NL]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_SUBDIVISION_INFORMATION_NL]) . "</nl>";
				if(isset($property[TAG_SUBDIVISION_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_SUBDIVISION_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_SUBDIVISION_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_SUBDIVISION_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_SUBDIVISION_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_SUBDIVISION_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			else if(isset($property[TAG_SUBDIVISION_INFORMATION_FR]) || isset($property[TAG_SUBDIVISION_INFORMATION_EN]) || isset($property[TAG_SUBDIVISION_INFORMATION_DE]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl></nl>";
				if(isset($property[TAG_SUBDIVISION_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_SUBDIVISION_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_SUBDIVISION_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_SUBDIVISION_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_SUBDIVISION_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_SUBDIVISION_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			$xml .= "\n\t\t\t\t</subdivision_permit>";
		}

		if(!empty($property[TAG_MOST_RECENT_DESTINATION]))
		{
			$xml .= "\n\t\t\t\t<most_recent_destination>";
			$xml .= "\n\t\t\t\t\t<value>" . $property[TAG_MOST_RECENT_DESTINATION] . "</value>";
			if(isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_NL]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_NL]) . "</nl>";
				if(isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			else if(isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_FR]) || isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_EN]) || isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_DE]))
			{
				$xml .= "\n\t\t\t\t\t<information>\n\t\t\t\t\t\t<nl></nl>";
				if(isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_FR]) . "</fr>";
				if(isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_DE]) . "</de>";
				if(isset($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_MOST_RECENT_DESTINATION_INFORMATION_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</information>";
			}
			$xml .= "\n\t\t\t\t</most_recent_destination>";
		}

		$xml .= "\n\t\t\t</planning>";
		$xml .= "\n\t\t\t<financial>";
		if(!empty($property[TAG_PRICE])) $xml .= "\n\t\t\t\t<price>" . $property[TAG_PRICE] . "</price>";
		if(isset($property[TAG_PRICE_VISIBLE])) $xml .= "\n\t\t\t\t<price_visible>" . $property[TAG_PRICE_VISIBLE] . "</price_visible>";
		if(!empty($property[TAG_KI])) $xml .= "\n\t\t\t\t<ki>" . $property[TAG_KI] . "</ki>";
		if(!empty($property[TAG_KI_INDEX])) $xml .= "\n\t\t\t\t<ki_index>" . $property[TAG_KI_INDEX] . "</ki_index>";
		if(!empty($property[TAG_DATE_KI_INDEX])) $xml .= "\n\t\t\t\t<date_ki_index>" . $property[TAG_DATE_KI_INDEX] . "</date_ki_index>";
		if(!empty($property[TAG_COMMON_COSTS])) $xml .= "\n\t\t\t\t<common_costs>" . $property[TAG_COMMON_COSTS] . "</common_costs>";
		if(!empty($property[TAG_PRICE_PER_M2])) $xml .= "\n\t\t\t\t<price_per_m2>" . $property[TAG_PRICE_PER_M2] . "</price_per_m2>";
		if(!empty($property[TAG_PROPERTY_TAX])) $xml .= "\n\t\t\t\t<property_tax>" . $property[TAG_PROPERTY_TAX] . "</property_tax>";
		if(!empty($property[TAG_DATE_PROPERTY_TAX])) $xml .= "\n\t\t\t\t<date_property_tax>" . $property[TAG_DATE_PROPERTY_TAX] . "</date_property_tax>";
		if(!empty($property[TAG_PROVISION])) $xml .= "\n\t\t\t\t<provision>" . $property[TAG_PROVISION] . "</provision>";
		if(!empty($property[TAG_NET_REVENUE])) $xml .= "\n\t\t\t\t<net_revenue>" . $property[TAG_NET_REVENUE] . "</net_revenue>";
		$xml .= "\n\t\t\t</financial>";
		$xml .= "\n\t\t\t<layout>";

		if(!empty($property[TAG_ATELIERS]))
		{
			$xml .= "\n\t\t\t\t<ateliers>";
			$arr = $property[TAG_ATELIERS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<atelier>";

				if(isset($item[TAG_ATELIER_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_ATELIER_DESC_NL]) . "</nl>";
					if(isset($item[TAG_ATELIER_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_ATELIER_DESC_FR]) . "</fr>";
					if(isset($item[TAG_ATELIER_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_ATELIER_DESC_DE]) . "</de>";
					if(isset($item[TAG_ATELIER_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_ATELIER_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_ATELIER_DESC_FR]) || isset($item[TAG_ATELIER_DESC_EN]) || isset($item[TAG_ATELIER_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_ATELIER_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_ATELIER_DESC_FR]) . "</fr>";
					if(isset($item[TAG_ATELIER_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_ATELIER_DESC_DE]) . "</de>";
					if(isset($item[TAG_ATELIER_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_ATELIER_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_ATELIER_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_ATELIER_SURFACE] . "</surface>";
				if(isset($item[TAG_ATELIER_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_ATELIER_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</atelier>";
			}
			$xml .= "\n\t\t\t\t</ateliers>";
		}

		if(!empty($property[TAG_BATHROOMS]))
		{
			$xml .= "\n\t\t\t\t<bathrooms>";
			$arr = $property[TAG_BATHROOMS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<bathroom>";

				if(isset($item[TAG_BATHROOM_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_BATHROOM_DESC_NL]) . "</nl>";
					if(isset($item[TAG_BATHROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_BATHROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_BATHROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_BATHROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_BATHROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_BATHROOM_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_BATHROOM_DESC_FR]) || isset($item[TAG_BATHROOM_DESC_EN]) || isset($item[TAG_BATHROOM_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_BATHROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_BATHROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_BATHROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_BATHROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_BATHROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_BATHROOM_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_BATHROOM_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_BATHROOM_SURFACE] . "</surface>";
				if(isset($item[TAG_BATHROOM_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_BATHROOM_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</bathroom>";
			}
			$xml .= "\n\t\t\t\t</bathrooms>";
		}

		if(!empty($property[TAG_STOREROOMS]))
		{
			$xml .= "\n\t\t\t\t<store_rooms>";
			$arr = $property[TAG_STOREROOMS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<store_room>";

				if(isset($item[TAG_STOREROOM_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_STOREROOM_DESC_NL]) . "</nl>";
					if(isset($item[TAG_STOREROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_STOREROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_STOREROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_STOREROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_STOREROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_STOREROOM_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_STOREROOM_DESC_FR]) || isset($item[TAG_STOREROOM_DESC_EN]) || isset($item[TAG_STOREROOM_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_STOREROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_STOREROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_STOREROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_STOREROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_STOREROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_STOREROOM_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_STOREROOM_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_STOREROOM_SURFACE] . "</surface>";
				if(isset($item[TAG_STOREROOM_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_STOREROOM_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</store_room>";
			}
			$xml .= "\n\t\t\t\t</store_rooms>";
		}

		if(!empty($property[TAG_STUDIES]))
		{
			$xml .= "\n\t\t\t\t<studies>";
			$arr = $property[TAG_STUDIES];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<study>";

				if(isset($item[TAG_STUDY_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_STUDY_DESC_NL]) . "</nl>";
					if(isset($item[TAG_STUDY_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_STUDY_DESC_FR]) . "</fr>";
					if(isset($item[TAG_STUDY_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_STUDY_DESC_DE]) . "</de>";
					if(isset($item[TAG_STUDY_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_STUDY_DESC_EN]) . "</en>";

					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_STUDY_DESC_FR]) || isset($item[TAG_STUDY_DESC_EN]) || isset($item[TAG_STUDY_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_STUDY_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_STUDY_DESC_FR]) . "</fr>";
					if(isset($item[TAG_STUDY_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_STUDY_DESC_DE]) . "</de>";
					if(isset($item[TAG_STUDY_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_STUDY_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_STUDY_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_STUDY_SURFACE] . "</surface>";
				if(isset($item[TAG_STUDY_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_STUDY_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</study>";
			}
			$xml .= "\n\t\t\t\t</studies>";
		}

		if(!empty($property[TAG_HEATING_AREAS]))
		{
			$xml .= "\n\t\t\t\t<heating_areas>";
			$arr = $property[TAG_HEATING_AREAS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<heating_area>";

				if(isset($item[TAG_HEATING_AREA_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_HEATING_AREA_DESC_NL]) . "</nl>";
					if(isset($item[TAG_HEATING_AREA_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_HEATING_AREA_DESC_FR]) . "</fr>";
					if(isset($item[TAG_HEATING_AREA_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_HEATING_AREA_DESC_DE]) . "</de>";
					if(isset($item[TAG_HEATING_AREA_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_HEATING_AREA_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_HEATING_AREA_DESC_FR]) || isset($item[TAG_HEATING_AREA_DESC_EN]) || isset($item[TAG_HEATING_AREA_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_HEATING_AREA_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_HEATING_AREA_DESC_FR]) . "</fr>";
					if(isset($item[TAG_HEATING_AREA_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_HEATING_AREA_DESC_DE]) . "</de>";
					if(isset($item[TAG_HEATING_AREA_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_HEATING_AREA_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_HEATING_AREA_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_HEATING_AREA_SURFACE] . "</surface>";
				if(isset($item[TAG_HEATING_AREA_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_HEATING_AREA_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</heating_area>";
			}
			$xml .= "\n\t\t\t\t</heating_areas>";
		}

		if(!empty($property[TAG_DRESSINGS]))
		{
			$xml .= "\n\t\t\t\t<dressings>";
			$arr = $property[TAG_DRESSINGS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<dressing>";

				if(isset($item[TAG_DRESSING_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_DRESSING_DESC_NL]) . "</nl>";
					if(isset($item[TAG_DRESSING_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_DRESSING_DESC_FR]) . "</fr>";
					if(isset($item[TAG_DRESSING_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_DRESSING_DESC_DE]) . "</de>";
					if(isset($item[TAG_DRESSING_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_DRESSING_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_DRESSING_DESC_FR]) || isset($item[TAG_DRESSING_DESC_EN]) || isset($item[TAG_DRESSING_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_DRESSING_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_DRESSING_DESC_FR]) . "</fr>";
					if(isset($item[TAG_DRESSING_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_DRESSING_DESC_DE]) . "</de>";
					if(isset($item[TAG_DRESSING_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_DRESSING_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_DRESSING_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_DRESSING_SURFACE] . "</surface>";
				if(isset($item[TAG_DRESSING_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_DRESSING_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</dressing>";
			}
			$xml .= "\n\t\t\t\t</dressings>";
		}

		if(!empty($property[TAG_DININGS]))
		{
			$xml .= "\n\t\t\t\t<dinings>";
			$arr = $property[TAG_DININGS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<dining>";

				if(isset($item[TAG_DINING_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_DINING_DESC_NL]) . "</nl>";
					if(isset($item[TAG_DINING_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_DINING_DESC_FR]) . "</fr>";
					if(isset($item[TAG_DINING_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_DINING_DESC_DE]) . "</de>";
					if(isset($item[TAG_DINING_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_DINING_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_DINING_DESC_FR]) || isset($item[TAG_DINING_DESC_EN]) || isset($item[TAG_DINING_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_DINING_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_DINING_DESC_FR]) . "</fr>";
					if(isset($item[TAG_DINING_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_DINING_DESC_DE]) . "</de>";
					if(isset($item[TAG_DINING_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_DINING_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_DINING_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_DINING_SURFACE] . "</surface>";
				if(isset($item[TAG_DINING_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_DINING_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</dining>";
			}
			$xml .= "\n\t\t\t\t</dinings>";
		}

		if(!empty($property[TAG_GARAGES]))
		{
			$xml .= "\n\t\t\t\t<garages>";
			$arr = $property[TAG_GARAGES];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<garage>";

				if(isset($item[TAG_GARAGE_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_GARAGE_DESC_NL]) . "</nl>";
					if(isset($item[TAG_GARAGE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_GARAGE_DESC_FR]) . "</fr>";
					if(isset($item[TAG_GARAGE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_GARAGE_DESC_DE]) . "</de>";
					if(isset($item[TAG_GARAGE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_GARAGE_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_GARAGE_DESC_FR]) || isset($item[TAG_GARAGE_DESC_EN]) || isset($item[TAG_GARAGE_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_GARAGE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_GARAGE_DESC_FR]) . "</fr>";
					if(isset($item[TAG_GARAGE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_GARAGE_DESC_DE]) . "</de>";
					if(isset($item[TAG_GARAGE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_GARAGE_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_GARAGE_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_GARAGE_SURFACE] . "</surface>";
				if(isset($item[TAG_GARAGE_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_GARAGE_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</garage>";
			}
			$xml .= "\n\t\t\t\t</garages>";
		}

		if(!empty($property[TAG_HALLS]))
		{
			$xml .= "\n\t\t\t\t<halls>";
			$arr = $property[TAG_HALLS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<hall>";

				if(isset($item[TAG_HALL_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_HALL_DESC_NL]) . "</nl>";
					if(isset($item[TAG_HALL_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_HALL_DESC_FR]) . "</fr>";
					if(isset($item[TAG_HALL_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_HALL_DESC_DE]) . "</de>";
					if(isset($item[TAG_HALL_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_HALL_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_HALL_DESC_FR]) || isset($item[TAG_HALL_DESC_EN]) || isset($item[TAG_HALL_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_HALL_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_HALL_DESC_FR]) . "</fr>";
					if(isset($item[TAG_HALL_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_HALL_DESC_DE]) . "</de>";
					if(isset($item[TAG_HALL_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_HALL_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_HALL_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_HALL_SURFACE] . "</surface>";
				if(isset($item[TAG_HALL_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_HALL_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</hall>";
			}
			$xml .= "\n\t\t\t\t</halls>";
		}

		if(!empty($property[TAG_CELLARS]))
		{
			$xml .= "\n\t\t\t\t<cellars>";
			$arr = $property[TAG_CELLARS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<cellar>";

				if(isset($item[TAG_CELLAR_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_CELLAR_DESC_NL]) . "</nl>";
					if(isset($item[TAG_CELLAR_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_CELLAR_DESC_FR]) . "</fr>";
					if(isset($item[TAG_CELLAR_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_CELLAR_DESC_DE]) . "</de>";
					if(isset($item[TAG_CELLAR_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_CELLAR_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_CELLAR_DESC_FR]) || isset($item[TAG_CELLAR_DESC_EN]) || isset($item[TAG_CELLAR_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_CELLAR_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_CELLAR_DESC_FR]) . "</fr>";
					if(isset($item[TAG_CELLAR_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_CELLAR_DESC_DE]) . "</de>";
					if(isset($item[TAG_CELLAR_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_CELLAR_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_CELLAR_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_CELLAR_SURFACE] . "</surface>";
				if(isset($item[TAG_CELLAR_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_CELLAR_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</cellar>";
			}
			$xml .= "\n\t\t\t\t</cellars>";
		}

		if(!empty($property[TAG_KITCHENS]))
		{
			$xml .= "\n\t\t\t\t<kitchens>";
			$arr = $property[TAG_KITCHENS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<kitchen>";

				if(isset($item[TAG_KITCHEN_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_KITCHEN_DESC_NL]) . "</nl>";
					if(isset($item[TAG_KITCHEN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_KITCHEN_DESC_FR]) . "</fr>";
					if(isset($item[TAG_KITCHEN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_KITCHEN_DESC_DE]) . "</de>";
					if(isset($item[TAG_KITCHEN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_KITCHEN_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_KITCHEN_DESC_FR]) || isset($item[TAG_KITCHEN_DESC_EN]) || isset($item[TAG_KITCHEN_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_KITCHEN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_KITCHEN_DESC_FR]) . "</fr>";
					if(isset($item[TAG_KITCHEN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_KITCHEN_DESC_DE]) . "</de>";
					if(isset($item[TAG_KITCHEN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_KITCHEN_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_KITCHEN_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_KITCHEN_SURFACE] . "</surface>";
				if(isset($item[TAG_KITCHEN_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_KITCHEN_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</kitchen>";
			}
			$xml .= "\n\t\t\t\t</kitchens>";
		}

		if(!empty($property[TAG_LIVINGS]))
		{
			$xml .= "\n\t\t\t\t<livings>";
			$arr = $property[TAG_LIVINGS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<living>";

				if(isset($item[TAG_LIVING_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_LIVING_DESC_NL]) . "</nl>";
					if(isset($item[TAG_LIVING_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_LIVING_DESC_FR]) . "</fr>";
					if(isset($item[TAG_LIVING_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_LIVING_DESC_DE]) . "</de>";
					if(isset($item[TAG_LIVING_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_LIVING_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_LIVING_DESC_FR]) || isset($item[TAG_LIVING_DESC_EN]) || isset($item[TAG_LIVING_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_LIVING_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_LIVING_DESC_FR]) . "</fr>";
					if(isset($item[TAG_LIVING_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_LIVING_DESC_DE]) . "</de>";
					if(isset($item[TAG_LIVING_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_LIVING_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_LIVING_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_LIVING_SURFACE] . "</surface>";
				if(isset($item[TAG_LIVING_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_LIVING_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</living>";
			}
			$xml .= "\n\t\t\t\t</livings>";
		}

		if(!empty($property[TAG_NIGHT_HALLS]))
		{
			$xml .= "\n\t\t\t\t<night_halls>";
			$arr = $property[TAG_NIGHT_HALLS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<night_hall>";

				if(isset($item[TAG_NIGHT_HALL_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_NIGHT_HALL_DESC_NL]) . "</nl>";
					if(isset($item[TAG_NIGHT_HALL_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_NIGHT_HALL_DESC_FR]) . "</fr>";
					if(isset($item[TAG_NIGHT_HALL_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_NIGHT_HALL_DESC_DE]) . "</de>";
					if(isset($item[TAG_NIGHT_HALL_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_NIGHT_HALL_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_NIGHT_HALL_DESC_FR]) || isset($item[TAG_NIGHT_HALL_DESC_EN]) || isset($item[TAG_NIGHT_HALL_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_NIGHT_HALL_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_NIGHT_HALL_DESC_FR]) . "</fr>";
					if(isset($item[TAG_NIGHT_HALL_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_NIGHT_HALL_DESC_DE]) . "</de>";
					if(isset($item[TAG_NIGHT_HALL_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_NIGHT_HALL_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_NIGHT_HALL_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_NIGHT_HALL_SURFACE] . "</surface>";
				if(isset($item[TAG_NIGHT_HALL_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_NIGHT_HALL_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</night_hall>";
			}
			$xml .= "\n\t\t\t\t</night_halls>";
		}

		if(!empty($property[TAG_SHOWROOMS]))
		{
			$xml .= "\n\t\t\t\t<showrooms>";
			$arr = $property[TAG_SHOWROOMS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<showroom>";

				if(isset($item[TAG_SHOWROOM_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_SHOWROOM_DESC_NL]) . "</nl>";
					if(isset($item[TAG_SHOWROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_SHOWROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_SHOWROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_SHOWROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_SHOWROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_SHOWROOM_DESC_EN]) . "</en>";

					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_SHOWROOM_DESC_FR]) || isset($item[TAG_SHOWROOM_DESC_EN]) || isset($item[TAG_SHOWROOM_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_SHOWROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_SHOWROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_SHOWROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_SHOWROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_SHOWROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_SHOWROOM_DESC_EN]) . "</en>";

					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_SHOWROOM_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_SHOWROOM_SURFACE] . "</surface>";
				if(isset($item[TAG_SHOWROOM_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_SHOWROOM_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</showroom>";
			}
			$xml .= "\n\t\t\t\t</showrooms>";
		}

		if(!empty($property[TAG_BEDROOMS]))
		{
			$xml .= "\n\t\t\t\t<bedrooms>";
			$arr = $property[TAG_BEDROOMS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<bedroom>";

				if(isset($item[TAG_BEDROOM_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_BEDROOM_DESC_NL]) . "</nl>";
					if(isset($item[TAG_BEDROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_BEDROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_BEDROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_BEDROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_BEDROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_BEDROOM_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_BEDROOM_DESC_FR]) || isset($item[TAG_BEDROOM_DESC_EN]) || isset($item[TAG_BEDROOM_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_BEDROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_BEDROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_BEDROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_BEDROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_BEDROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_BEDROOM_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_BEDROOM_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_BEDROOM_SURFACE] . "</surface>";
				if(isset($item[TAG_BEDROOM_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_BEDROOM_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</bedroom>";
			}
			$xml .= "\n\t\t\t\t</bedrooms>";
		}

		if(!empty($property[TAG_TERRACES]))
		{
			$xml .= "\n\t\t\t\t<terraces>";
			$arr = $property[TAG_TERRACES];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<terrace>";

				if(isset($item[TAG_TERRACE_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_TERRACE_DESC_NL]) . "</nl>";
					if(isset($item[TAG_TERRACE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_TERRACE_DESC_FR]) . "</fr>";
					if(isset($item[TAG_TERRACE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_TERRACE_DESC_DE]) . "</de>";
					if(isset($item[TAG_TERRACE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_TERRACE_DESC_EN]) . "</en>";

					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_TERRACE_DESC_FR]) || isset($item[TAG_TERRACE_DESC_EN]) || isset($item[TAG_TERRACE_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_TERRACE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_TERRACE_DESC_FR]) . "</fr>";
					if(isset($item[TAG_TERRACE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_TERRACE_DESC_DE]) . "</de>";
					if(isset($item[TAG_TERRACE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_TERRACE_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_TERRACE_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_TERRACE_SURFACE] . "</surface>";
				if(isset($item[TAG_TERRACE_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_TERRACE_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</terrace>";
			}
			$xml .= "\n\t\t\t\t</terraces>";
		}

		if(!empty($property[TAG_WINTERGARDENS]))
		{
			$xml .= "\n\t\t\t\t<wintergardens>";
			$arr = $property[TAG_WINTERGARDENS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<wintergarden>";

				if(isset($item[TAG_WINTERGARDEN_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_WINTERGARDEN_DESC_NL]) . "</nl>";
					if(isset($item[TAG_WINTERGARDEN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_WINTERGARDEN_DESC_FR]) . "</fr>";
					if(isset($item[TAG_WINTERGARDEN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_WINTERGARDEN_DESC_DE]) . "</de>";
					if(isset($item[TAG_WINTERGARDEN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_WINTERGARDEN_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_WINTERGARDEN_DESC_FR]) || isset($item[TAG_WINTERGARDEN_DESC_EN]) || isset($item[TAG_WINTERGARDEN_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_WINTERGARDEN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_WINTERGARDEN_DESC_FR]) . "</fr>";
					if(isset($item[TAG_WINTERGARDEN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_WINTERGARDEN_DESC_DE]) . "</de>";
					if(isset($item[TAG_WINTERGARDEN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_WINTERGARDEN_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_WINTERGARDEN_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_WINTERGARDEN_SURFACE] . "</surface>";
				if(isset($item[TAG_WINTERGARDEN_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_WINTERGARDEN_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</wintergarden>";
			}
			$xml .= "\n\t\t\t\t</wintergardens>";
		}

		if(!empty($property[TAG_WARDROBES]))
		{
			$xml .= "\n\t\t\t\t<wardrobes>";
			$arr = $property[TAG_WARDROBES];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<wardrobe>";

				if(isset($item[TAG_WARDROBE_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_WARDROBE_DESC_NL]) . "</nl>";
					if(isset($item[TAG_WARDROBE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_WARDROBE_DESC_FR]) . "</fr>";
					if(isset($item[TAG_WARDROBE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_WARDROBE_DESC_DE]) . "</de>";
					if(isset($item[TAG_WARDROBE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_WARDROBE_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_WARDROBE_DESC_FR]) || isset($item[TAG_WARDROBE_DESC_EN]) || isset($item[TAG_WARDROBE_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_WARDROBE_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_WARDROBE_DESC_FR]) . "</fr>";
					if(isset($item[TAG_WARDROBE_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_WARDROBE_DESC_DE]) . "</de>";
					if(isset($item[TAG_WARDROBE_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_WARDROBE_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_WARDROBE_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_WARDROBE_SURFACE] . "</surface>";
				if(isset($item[TAG_WARDROBE_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_WARDROBE_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</wardrobe>";
			}
			$xml .= "\n\t\t\t\t</wardrobes>";
		}

		if(!empty($property[TAG_FREE_PROFESSIONS]))
		{
			$xml .= "\n\t\t\t\t<free_professions>";
			$arr = $property[TAG_FREE_PROFESSIONS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<free_profession>";

				if(isset($item[TAG_FREE_PROFESSION_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_FREE_PROFESSION_DESC_NL]) . "</nl>";
					if(isset($item[TAG_FREE_PROFESSION_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_FREE_PROFESSION_DESC_FR]) . "</fr>";
					if(isset($item[TAG_FREE_PROFESSION_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_FREE_PROFESSION_DESC_DE]) . "</de>";
					if(isset($item[TAG_FREE_PROFESSION_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_FREE_PROFESSION_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_FREE_PROFESSION_DESC_FR]) || isset($item[TAG_FREE_PROFESSION_DESC_EN]) || isset($item[TAG_FREE_PROFESSION_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_FREE_PROFESSION_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_FREE_PROFESSION_DESC_FR]) . "</fr>";
					if(isset($item[TAG_FREE_PROFESSION_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_FREE_PROFESSION_DESC_DE]) . "</de>";
					if(isset($item[TAG_FREE_PROFESSION_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_FREE_PROFESSION_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_FREE_PROFESSION_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_FREE_PROFESSION_SURFACE] . "</surface>";
				if(isset($item[TAG_FREE_PROFESSION_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_FREE_PROFESSION_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</free_profession>";
			}
			$xml .= "\n\t\t\t\t</free_professions>";
		}

		if(!empty($property[TAG_LAUNDRY_ROOMS]))
		{
			$xml .= "\n\t\t\t\t<laundry_rooms>";
			$arr = $property[TAG_LAUNDRY_ROOMS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<laundry_room>";

				if(isset($item[TAG_LAUNDRY_ROOM_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_LAUNDRY_ROOM_DESC_NL]) . "</nl>";
					if(isset($item[TAG_LAUNDRY_ROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_LAUNDRY_ROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_LAUNDRY_ROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_LAUNDRY_ROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_LAUNDRY_ROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_LAUNDRY_ROOM_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_LAUNDRY_ROOM_DESC_FR]) || isset($item[TAG_LAUNDRY_ROOM_DESC_EN]) || isset($item[TAG_LAUNDRY_ROOM_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_LAUNDRY_ROOM_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_LAUNDRY_ROOM_DESC_FR]) . "</fr>";
					if(isset($item[TAG_LAUNDRY_ROOM_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_LAUNDRY_ROOM_DESC_DE]) . "</de>";
					if(isset($item[TAG_LAUNDRY_ROOM_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_LAUNDRY_ROOM_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_LAUNDRY_ROOM_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_LAUNDRY_ROOM_SURFACE] . "</surface>";
				if(isset($item[TAG_LAUNDRY_ROOM_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_LAUNDRY_ROOM_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</laundry_room>";
			}
			$xml .= "\n\t\t\t\t</laundry_rooms>";
		}

		if(!empty($property[TAG_TOILETS]))
		{
			$xml .= "\n\t\t\t\t<toilets>";
			$arr = $property[TAG_TOILETS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<toilet>";

				if(isset($item[TAG_TOILET_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_TOILET_DESC_NL]) . "</nl>";
					if(isset($item[TAG_TOILET_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_TOILET_DESC_FR]) . "</fr>";
					if(isset($item[TAG_TOILET_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_TOILET_DESC_DE]) . "</de>";
					if(isset($item[TAG_TOILET_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_TOILET_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_TOILET_DESC_FR]) || isset($item[TAG_TOILET_DESC_EN]) || isset($item[TAG_TOILET_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_TOILET_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_TOILET_DESC_FR]) . "</fr>";
					if(isset($item[TAG_TOILET_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_TOILET_DESC_DE]) . "</de>";
					if(isset($item[TAG_TOILET_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_TOILET_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_TOILET_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_TOILET_SURFACE] . "</surface>";
				if(isset($item[TAG_TOILET_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_TOILET_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</toilet>";
			}
			$xml .= "\n\t\t\t\t</toilets>";
		}

		if(!empty($property[TAG_SITTING_AREAS]))
		{
			$xml .= "\n\t\t\t\t<sitting_areas>";
			$arr = $property[TAG_SITTING_AREAS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<sitting_area>";

				if(isset($item[TAG_SITTING_AREA_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_SITTING_AREA_DESC_NL]) . "</nl>";
					if(isset($item[TAG_SITTING_AREA_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_SITTING_AREA_DESC_FR]) . "</fr>";
					if(isset($item[TAG_SITTING_AREA_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_SITTING_AREA_DESC_DE]) . "</de>";
					if(isset($item[TAG_SITTING_AREA_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_SITTING_AREA_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_SITTING_AREA_DESC_FR]) || isset($item[TAG_SITTING_AREA_DESC_EN]) || isset($item[TAG_SITTING_AREA_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_SITTING_AREA_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_SITTING_AREA_DESC_FR]) . "</fr>";
					if(isset($item[TAG_SITTING_AREA_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_SITTING_AREA_DESC_DE]) . "</de>";
					if(isset($item[TAG_SITTING_AREA_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_SITTING_AREA_DESC_EN]) . "</en>";
					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_SITTING_AREA_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_SITTING_AREA_SURFACE] . "</surface>";
				if(isset($item[TAG_SITTING_AREA_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_SITTING_AREA_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</sitting_area>";
			}
			$xml .= "\n\t\t\t\t</sitting_areas>";
		}

		if(!empty($property[TAG_ATTICS]))
		{
			$xml .= "\n\t\t\t\t<attics>";
			$arr = $property[TAG_ATTICS];
			foreach($arr as $item)
			{
				$xml .= "\n\t\t\t\t\t<attic>";

				if(isset($item[TAG_ATTIC_DESC_NL]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl>" . CrawlerTool::encode($item[TAG_ATTIC_DESC_NL]) . "</nl>";
					if(isset($item[TAG_ATTIC_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_ATTIC_DESC_FR]) . "</fr>";
					if(isset($item[TAG_ATTIC_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_ATTIC_DESC_DE]) . "</de>";
					if(isset($item[TAG_ATTIC_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_ATTIC_DESC_EN]) . "</en>";

					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				else if(isset($item[TAG_ATTIC_DESC_FR]) || isset($item[TAG_ATTIC_DESC_EN]) || isset($item[TAG_ATTIC_DESC_DE]))
				{
					$xml .= "\n\t\t\t\t\t\t<description>\n\t\t\t\t\t\t\t<nl></nl>";
					if(isset($item[TAG_ATTIC_DESC_FR])) $xml .= "\n\t\t\t\t\t\t\t<fr>" . CrawlerTool::encode($item[TAG_ATTIC_DESC_FR]) . "</fr>";
					if(isset($item[TAG_ATTIC_DESC_DE])) $xml .= "\n\t\t\t\t\t\t\t<de>" . CrawlerTool::encode($item[TAG_ATTIC_DESC_DE]) . "</de>";
					if(isset($item[TAG_ATTIC_DESC_EN])) $xml .= "\n\t\t\t\t\t\t\t<en>" . CrawlerTool::encode($item[TAG_ATTIC_DESC_EN]) . "</en>";

					$xml .= "\n\t\t\t\t\t\t</description>";
				}
				if(isset($item[TAG_ATTIC_SURFACE])) $xml .= "\n\t\t\t\t\t\t<surface>" . $item[TAG_ATTIC_SURFACE] . "</surface>";
				if(isset($item[TAG_ATTIC_FLOOR])) $xml .= "\n\t\t\t\t\t\t<floor>" . $item[TAG_ATTIC_FLOOR] . "</floor>";
				$xml .= "\n\t\t\t\t\t</attic>";
			}
			$xml .= "\n\t\t\t\t</attics>";
		}

		$xml .= "\n\t\t\t</layout>";
		$xml .= "\n\t\t\t<comfort>";

		if(!empty($property[TAG_KITCHEN_TYPE_NL]))
		{
			$xml .= "\n\t\t\t\t<kitchen_type>\n\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_KITCHEN_TYPE_NL]) . "</nl>";
			if(!empty($property[TAG_KITCHEN_TYPE_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_KITCHEN_TYPE_FR]) . "</fr>";
			if(!empty($property[TAG_KITCHEN_TYPE_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_KITCHEN_TYPE_DE]) . "</de>";
			if(!empty($property[TAG_KITCHEN_TYPE_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_KITCHEN_TYPE_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</kitchen_type>";
		}
		else if(!empty($property[TAG_KITCHEN_TYPE_FR]) || !empty($property[TAG_KITCHEN_TYPE_EN]) || !empty($property[TAG_KITCHEN_TYPE_DE]))
		{
			$xml .= "\n\t\t\t\t<kitchen_type>\n\t\t\t\t\t<nl></nl>";
			if(!empty($property[TAG_KITCHEN_TYPE_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_KITCHEN_TYPE_FR]) . "</fr>";
			if(!empty($property[TAG_KITCHEN_TYPE_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_KITCHEN_TYPE_DE]) . "</de>";
			if(!empty($property[TAG_KITCHEN_TYPE_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_KITCHEN_TYPE_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</kitchen_type>";
		}

		if(!empty($property[TAG_HEATING_NL]))
		{
			$xml .= "\n\t\t\t\t<heating>\n\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_HEATING_NL]) . "</nl>";
			if(!empty($property[TAG_HEATING_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_HEATING_FR]) . "</fr>";
			if(!empty($property[TAG_HEATING_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_HEATING_DE]) . "</de>";
			if(!empty($property[TAG_HEATING_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_HEATING_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</heating>";
		}
		else if(!empty($property[TAG_HEATING_FR]) || !empty($property[TAG_HEATING_EN]) || !empty($property[TAG_HEATING_DE]))
		{
			$xml .= "\n\t\t\t\t<heating>\n\t\t\t\t\t<nl></nl>";
			if(!empty($property[TAG_HEATING_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_HEATING_FR]) . "</fr>";
			if(!empty($property[TAG_HEATING_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_HEATING_DE]) . "</de>";
			if(!empty($property[TAG_HEATING_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_HEATING_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</heating>";
		}

		if(!empty($property[TAG_BOILER])) $xml .= "\n\t\t\t\t<boiler>" . $property[TAG_BOILER] . "</boiler>";
		if(!empty($property[TAG_CONTENT_TANK_DOMESTIC_FUEL_OIL])) $xml .= "\n\t\t\t\t<content_tank_domestic_fuel_oil>" . $property[TAG_CONTENT_TANK_DOMESTIC_FUEL_OIL] . "</content_tank_domestic_fuel_oil>";
		if(!empty($property[TAG_FLOOR_HEATING])) $xml .= "\n\t\t\t\t<floor_heating>" . $property[TAG_FLOOR_HEATING] . "</floor_heating>";
		if(!empty($property[TAG_CENTRAL_HEATING])) $xml .= "\n\t\t\t\t<central_heating>" . $property[TAG_CENTRAL_HEATING] . "</central_heating>";
		if(!empty($property[TAG_SOLAR_PANELS])) $xml .= "\n\t\t\t\t<solar_panels>" . $property[TAG_SOLAR_PANELS] . "</solar_panels>";
		if(!empty($property[TAG_SOLAR_BOILER])) $xml .= "\n\t\t\t\t<solar_boiler>" . $property[TAG_SOLAR_BOILER] . "</solar_boiler>";
		if(!empty($property[TAG_HEAT_PUMP])) $xml .= "\n\t\t\t\t<heat_pump>" . $property[TAG_HEAT_PUMP] . "</heat_pump>";
		if(!empty($property[TAG_WINDMILL])) $xml .= "\n\t\t\t\t<windmill>" . $property[TAG_WINDMILL] . "</windmill>";
		if(!empty($property[TAG_FLOOR_MATERIAL])) $xml .= "\n\t\t\t\t<floor_material>" . $property[TAG_FLOOR_MATERIAL] . "</floor_material>";
		if(!empty($property[TAG_SCHUTTERS])) $xml .= "\n\t\t\t\t<schutters>" . $property[TAG_SCHUTTERS] . "</schutters>";
		if(!empty($property[TAG_SUN_BLINDS])) $xml .= "\n\t\t\t\t<sun_blinds>" . $property[TAG_SUN_BLINDS] . "</sun_blinds>";
		if(!empty($property[TAG_DOUBLE_GLAZING])) $xml .= "\n\t\t\t\t<double_glazing>" . $property[TAG_DOUBLE_GLAZING] . "</double_glazing>";
		if(!empty($property[TAG_PARLOPHONE])) $xml .= "\n\t\t\t\t<parlophone>" . $property[TAG_PARLOPHONE] . "</parlophone>";
		if(!empty($property[TAG_VIDEOPHONE])) $xml .= "\n\t\t\t\t<videophone>" . $property[TAG_VIDEOPHONE] . "</videophone>";
		if(!empty($property[TAG_ALARM])) $xml .= "\n\t\t\t\t<alarm>" . $property[TAG_ALARM] . "</alarm>";
		if(!empty($property[TAG_SECOND_KITCHEN])) $xml .= "\n\t\t\t\t<second_kitchen>" . $property[TAG_SECOND_KITCHEN] . "</second_kitchen>";
		if(!empty($property[TAG_DISTRIBUTION])) $xml .= "\n\t\t\t\t<distribution>" . $property[TAG_DISTRIBUTION] . "</distribution>";
		if(!empty($property[TAG_DRESSING])) $xml .= "\n\t\t\t\t<dressing>" . $property[TAG_DRESSING] . "</dressing>";
		if(!empty($property[TAG_SWIMMINGPOOL])) $xml .= "\n\t\t\t\t<swimmingpool>" . $property[TAG_SWIMMINGPOOL] . "</swimmingpool>";
		if(!empty($property[TAG_TENNIS_COURT])) $xml .= "\n\t\t\t\t<tennis_court>" . $property[TAG_TENNIS_COURT] . "</tennis_court>";
		if(!empty($property[TAG_FURNISHED])) $xml .= "\n\t\t\t\t<furnished>" . $property[TAG_FURNISHED] . "</furnished>";
		if(!empty($property[TAG_LIFT])) $xml .= "\n\t\t\t\t<lift>" . $property[TAG_LIFT] . "</lift>";
		if(!empty($property[TAG_HOUSEKEEPER])) $xml .= "\n\t\t\t\t<housekeeper>" . $property[TAG_HOUSEKEEPER] . "</housekeeper>";
		if(!empty($property[TAG_SECURITY_DOOR])) $xml .= "\n\t\t\t\t<security_door>" . $property[TAG_SECURITY_DOOR] . "</security_door>";
		if(!empty($property[TAG_ACCESS_SEMIVALID])) $xml .= "\n\t\t\t\t<access_semivalid>" . $property[TAG_ACCESS_SEMIVALID] . "</access_semivalid>";
		if(!empty($property[TAG_DOMESTIC_ANIMALS_ALLOWED])) $xml .= "\n\t\t\t\t<domestic_animals_allowed>" . $property[TAG_DOMESTIC_ANIMALS_ALLOWED] . "</domestic_animals_allowed>";
		if(!empty($property[TAG_METER_FOR_ELECTRICITY])) $xml .= "\n\t\t\t\t<meter_for_electricity>" . $property[TAG_METER_FOR_ELECTRICITY] . "</meter_for_electricity>";
		if(!empty($property[TAG_CONNECTION_TO_SEWER])) $xml .= "\n\t\t\t\t<connection_to_sewer>" . $property[TAG_CONNECTION_TO_SEWER] . "</connection_to_sewer>";
		if(!empty($property[TAG_SEPTIC_TANK])) $xml .= "\n\t\t\t\t<septic_tank>" . $property[TAG_SEPTIC_TANK] . "</septic_tank>";
		if(!empty($property[TAG_GAS_CONNECTION])) $xml .= "\n\t\t\t\t<gas_connection>" . $property[TAG_GAS_CONNECTION] . "</gas_connection>";
		if(!empty($property[TAG_METER_FOR_GAS])) $xml .= "\n\t\t\t\t<meter_for_gas>" . $property[TAG_METER_FOR_GAS] . "</meter_for_gas>";
		if(!empty($property[TAG_TELEPHONE_CONNECTION])) $xml .= "\n\t\t\t\t<telephone_connection>" . $property[TAG_TELEPHONE_CONNECTION] . "</telephone_connection>";
		if(!empty($property[TAG_INTERNET_CONNECTION])) $xml .= "\n\t\t\t\t<internet_connection>" . $property[TAG_INTERNET_CONNECTION] . "</internet_connection>";
		if(!empty($property[TAG_CONNECTION_TO_WATER])) $xml .= "\n\t\t\t\t<connection_to_water>" . $property[TAG_CONNECTION_TO_WATER] . "</connection_to_water>";
		if(!empty($property[TAG_METER_FOR_WATER])) $xml .= "\n\t\t\t\t<meter_for_water>" . $property[TAG_METER_FOR_WATER] . "</meter_for_water>";
		if(!empty($property[TAG_WATER_SOFTENER])) $xml .= "\n\t\t\t\t<water_softener>" . $property[TAG_WATER_SOFTENER] . "</water_softener>";
		if(!empty($property[TAG_WELL])) $xml .= "\n\t\t\t\t<well>" . $property[TAG_WELL] . "</well>";
		if(!empty($property[TAG_CONTENT_WELL])) $xml .= "\n\t\t\t\t<content_well>" . $property[TAG_CONTENT_WELL] . "</content_well>";
		$xml .= "\n\t\t\t</comfort>";
		$xml .= "\n\t\t\t<other>";
		if(!empty($property[TAG_GARDEN_AVAILABLE]))
		{
			$xml .= "\n\t\t\t\t<garden>\n\t\t\t\t\t<available>" . $property[TAG_GARDEN_AVAILABLE] . "</available>";
			if(isset($property[TAG_GARDEN_DESC_NL]))
			{
				$xml .= "\n\t\t\t\t\t<description>\n\t\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_GARDEN_DESC_NL]) . "</nl>";
				if(isset($property[TAG_GARDEN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_GARDEN_DESC_FR]) . "</fr>";
				if(isset($property[TAG_GARDEN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_GARDEN_DESC_DE]) . "</de>";
				if(isset($property[TAG_GARDEN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_GARDEN_DESC_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</description>";
			}
			else if(isset($property[TAG_GARDEN_DESC_FR]) || isset($property[TAG_GARDEN_DESC_EN]) || isset($property[TAG_GARDEN_DESC_DE]))
			{
				$xml .= "\n\t\t\t\t\t<description>\n\t\t\t\t\t\t<nl></nl>";
				if(isset($property[TAG_GARDEN_DESC_FR])) $xml .= "\n\t\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_GARDEN_DESC_FR]) . "</fr>";
				if(isset($property[TAG_GARDEN_DESC_DE])) $xml .= "\n\t\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_GARDEN_DESC_DE]) . "</de>";
				if(isset($property[TAG_GARDEN_DESC_EN])) $xml .= "\n\t\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_GARDEN_DESC_EN]) . "</en>";
				$xml .= "\n\t\t\t\t\t</description>";
			}
			if(isset($property[TAG_GARDEN_ORIENTATION])) $xml .= "\n\t\t\t\t\t<orientation>" . $property[TAG_GARDEN_ORIENTATION] . "</orientation>";
			$xml .= "\n\t\t\t\t</garden>";
		}
		if(!empty($property[TAG_SHOWERS_TOTAL])) $xml .= "\n\t\t\t\t<showers_total>" . $property[TAG_SHOWERS_TOTAL] . "</showers_total>";
		if(!empty($property[TAG_OPEN_FIRE])) $xml .= "\n\t\t\t\t<open_fire>" . $property[TAG_OPEN_FIRE] . "</open_fire>";
		if(!empty($property[TAG_GARAGES_TOTAL])) $xml .= "\n\t\t\t\t<garages_total>" . $property[TAG_GARAGES_TOTAL] . "</garages_total>";
		if(!empty($property[TAG_CARPORTS_TOTAL])) $xml .= "\n\t\t\t\t<carports_total>" . $property[TAG_CARPORTS_TOTAL] . "</carports_total>";
		if(!empty($property[TAG_PARKINGS_TOTAL])) $xml .= "\n\t\t\t\t<parkings_total>" . $property[TAG_PARKINGS_TOTAL] . "</parkings_total>";
		if(!empty($property[TAG_DISTANCE_PUBLIC_TRANSPORT]) || !empty($property[TAG_DISTANCE_BEACH]) || !empty($property[TAG_DISTANCE_SCHOOL]) || !empty($property[TAG_DISTANCE_SHOPS]))
		{
			$xml .= "\n\t\t\t\t<distance>";
			if(!empty($property[TAG_DISTANCE_PUBLIC_TRANSPORT])) $xml .= "\n\t\t\t\t\t<public_transport>" . $property[TAG_DISTANCE_PUBLIC_TRANSPORT] . "</public_transport>";
			if(!empty($property[TAG_DISTANCE_SHOPS])) $xml .= "\n\t\t\t\t\t<shops>" . $property[TAG_DISTANCE_SHOPS] . "</shops>";
			if(!empty($property[TAG_DISTANCE_SCHOOL])) $xml .= "\n\t\t\t\t\t<school>" . $property[TAG_DISTANCE_SCHOOL] . "</school>";
			if(!empty($property[TAG_DISTANCE_BEACH])) $xml .= "\n\t\t\t\t\t<beach>" . $property[TAG_DISTANCE_BEACH] . "</beach>";
			$xml .= "\n\t\t\t\t</distance>";
		}
		if(!empty($property[TAG_SEAVIEW])) $xml .= "\n\t\t\t\t<seaview>" . $property[TAG_SEAVIEW] . "</seaview>";
		if(!empty($property[TAG_SIDE_SEAVIEW])) $xml .= "\n\t\t\t\t<side_sea_view>" . $property[TAG_SIDE_SEAVIEW] . "</side_sea_view>";
		if(!empty($property[TAG_READY_TO_MOVE_IN])) $xml .= "\n\t\t\t\t<ready_to_move_in>" . $property[TAG_READY_TO_MOVE_IN] . "</ready_to_move_in>";
		if(!empty($property[TAG_FREE_FROM_DATE])) $xml .= "\n\t\t\t\t<free_from_date>" . $property[TAG_FREE_FROM_DATE] . "</free_from_date>";
		if(!empty($property[TAG_MINIMUM_STAY])) $xml .= "\n\t\t\t\t<minimum_stay>" . $property[TAG_MINIMUM_STAY] . "</minimum_stay>";
		$xml .= "\n\t\t\t</other>";
		if(!empty($property[TAG_BEDS]) || !empty($property[TAG_PREFERED_GENDER]) || !empty($property[TAG_PREFERED_SMOKER]))
		{
			$xml .= "\n\t\t\t<colocation>";
			if(!empty($property[TAG_BEDS])) $xml .= "\n\t\t\t\t<beds>" . $property[TAG_BEDS] . "</beds>";
			if(!empty($property[TAG_PREFERED_GENDER])) $xml .= "\n\t\t\t\t<prefered_gender>" . $property[TAG_PREFERED_GENDER] . "</prefered_gender>";
			if(!empty($property[TAG_PREFERED_SMOKER])) $xml .= "\n\t\t\t\t<prefered_smoker>" . $property[TAG_PREFERED_SMOKER] . "</prefered_smoker>";
			$xml .= "\n\t\t\t</colocation>";
		}
		if(!empty($property[TAG_IS_NOTARY]) || !empty($property[TAG_IS_ANNUITY]) || !empty($property[TAG_IS_PUBLIC]))
		{
			$xml .= "\n\t\t\t<notary>";
			if(!empty($property[TAG_IS_NOTARY])) $xml .= "\n\t\t\t\t<is_notary>" . $property[TAG_IS_NOTARY] . "</is_notary>";
			if(!empty($property[TAG_IS_ANNUITY])) $xml .= "\n\t\t\t\t<is_annuity>" . $property[TAG_IS_ANNUITY] . "</is_annuity>";
			if(!empty($property[TAG_IS_PUBLIC])) $xml .= "\n\t\t\t\t<is_public>" . $property[TAG_IS_PUBLIC] . "</is_public>";
			$xml .= "\n\t\t\t</notary>";
		}

		$xml .= "\n\t\t\t<source>";
		if(isset($property[TAG_PLAIN_TEXT_ALL_NL]))
		{
			$xml .= "\n\t\t\t\t<plain_text_all>\n\t\t\t\t\t<nl>" . CrawlerTool::encode($property[TAG_PLAIN_TEXT_ALL_NL]) . "</nl>";
			if(isset($property[TAG_PLAIN_TEXT_ALL_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_PLAIN_TEXT_ALL_FR]) . "</fr>";
			if(isset($property[TAG_PLAIN_TEXT_ALL_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_PLAIN_TEXT_ALL_DE]) . "</de>";
			if(isset($property[TAG_PLAIN_TEXT_ALL_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_PLAIN_TEXT_ALL_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</plain_text_all>";
		}
		else if(isset($property[TAG_PLAIN_TEXT_ALL_FR]) || isset($property[TAG_PLAIN_TEXT_ALL_EN]) || isset($property[TAG_PLAIN_TEXT_ALL_DE]))
		{
			$xml .= "\n\t\t\t\t<plain_text_all>\n\t\t\t\t\t<nl></nl>";
			if(isset($property[TAG_PLAIN_TEXT_ALL_FR])) $xml .= "\n\t\t\t\t\t<fr>" . CrawlerTool::encode($property[TAG_PLAIN_TEXT_ALL_FR]) . "</fr>";
			if(isset($property[TAG_PLAIN_TEXT_ALL_DE])) $xml .= "\n\t\t\t\t\t<de>" . CrawlerTool::encode($property[TAG_PLAIN_TEXT_ALL_DE]) . "</de>";
			if(isset($property[TAG_PLAIN_TEXT_ALL_EN])) $xml .= "\n\t\t\t\t\t<en>" . CrawlerTool::encode($property[TAG_PLAIN_TEXT_ALL_EN]) . "</en>";
			$xml .= "\n\t\t\t\t</plain_text_all>";
		}

		if(!empty($property[TAG_UNMATCHED_VARIABLES]))
		{
			$xml .= "\n\t\t\t\t<unmatched_variables>";
			$variables = $property[TAG_UNMATCHED_VARIABLES];
			foreach($variables as $variable)
			{
				$xml .= "\n\t\t\t\t\t<variable>";
				if(isset($variable[TAG_VARIABLE_LABEL])) $xml .= "\n\t\t\t\t\t\t<label>" . CrawlerTool::encode($variable[TAG_VARIABLE_LABEL]) . "</label>";
				if(isset($variable[TAG_VARIABLE_VALUE])) $xml .= "\n\t\t\t\t\t\t<value>" . CrawlerTool::encode($variable[TAG_VARIABLE_VALUE]) . "</value>";
				$xml .= "\n\t\t\t\t\t</variable>";
			}
			$xml .= "\n\t\t\t\t</unmatched_variables>";
		}

		if(!empty($property[TAG_IGNORED_VARIABLES]))
		{
			$xml .= "\n\t\t\t\t<ignored_variables>";
			$variables = $property[TAG_IGNORED_VARIABLES];
			foreach($variables as $variable)
			{
				$xml .= "\n\t\t\t\t\t<variable>";
				if(isset($variable[TAG_VARIABLE_LABEL])) $xml .= "\n\t\t\t\t\t\t<label>" . CrawlerTool::encode($variable[TAG_VARIABLE_LABEL]) . "</label>";
				if(isset($variable[TAG_VARIABLE_VALUE])) $xml .= "\n\t\t\t\t\t\t<value>" . CrawlerTool::encode($variable[TAG_VARIABLE_VALUE]) . "</value>";
				$xml .= "\n\t\t\t\t\t</variable>";
			}
			$xml .= "\n\t\t\t\t</ignored_variables>";
		}

		$xml .= "\n\t\t\t</source>";
		$xml .= "\n\t\t</property>";

		file_put_contents(OUTPUT_PROPERTY_FILE_NAME, $xml, FILE_APPEND);
	}

    public static function findMoreData(&$property)
    {
        // find investment property
        self::findInvestmentProperty($property);
    }

    public static function applyRules(&$property)
    {
        // if text_desc tag is not set, and text_short_desc tag is set then set text_desc tag as text_short_desc
        if(!isset($property[TAG_TEXT_DESC_NL]) && !isset($property[TAG_TEXT_DESC_FR]) && !isset($property[TAG_TEXT_DESC_DE]) && !isset($property[TAG_TEXT_DESC_EN]))
        {
            if(isset($property[TAG_TEXT_SHORT_DESC_NL]))
            {
                $property[TAG_TEXT_DESC_NL] = $property[TAG_TEXT_SHORT_DESC_NL];
                unset($property[TAG_TEXT_SHORT_DESC_NL]);
            }

            if(isset($property[TAG_TEXT_SHORT_DESC_FR]))
            {
                $property[TAG_TEXT_DESC_FR] = $property[TAG_TEXT_SHORT_DESC_FR];
                unset($property[TAG_TEXT_SHORT_DESC_FR]);
            }

            if(isset($property[TAG_TEXT_SHORT_DESC_DE]))
            {
                $property[TAG_TEXT_DESC_DE] = $property[TAG_TEXT_SHORT_DESC_DE];
                unset($property[TAG_TEXT_SHORT_DESC_DE]);
            }

            if(isset($property[TAG_TEXT_SHORT_DESC_EN]))
            {
                $property[TAG_TEXT_DESC_EN] = $property[TAG_TEXT_SHORT_DESC_EN];
                unset($property[TAG_TEXT_SHORT_DESC_EN]);
            }
        }
    }

    public static function findInvestmentProperty(&$property)
    {
        $text = "";

        if(!empty($property[TAG_TYPE_LONG])) $text .= $property[TAG_TYPE_LONG];
        if(!empty($property[TAG_TEXT_TITLE_NL])) $text .= $property[TAG_TEXT_TITLE_NL];
        if(!empty($property[TAG_TEXT_TITLE_FR])) $text .= $property[TAG_TEXT_TITLE_FR];
        if(!empty($property[TAG_TEXT_TITLE_DE])) $text .= $property[TAG_TEXT_TITLE_DE];
        if(!empty($property[TAG_TEXT_TITLE_EN])) $text .= $property[TAG_TEXT_TITLE_EN];
        if(!empty($property[TAG_TEXT_SHORT_DESC_NL])) $text .= $property[TAG_TEXT_SHORT_DESC_NL];
        if(!empty($property[TAG_TEXT_SHORT_DESC_FR])) $text .= $property[TAG_TEXT_SHORT_DESC_FR];
        if(!empty($property[TAG_TEXT_SHORT_DESC_DE])) $text .= $property[TAG_TEXT_SHORT_DESC_DE];
        if(!empty($property[TAG_TEXT_SHORT_DESC_EN])) $text .= $property[TAG_TEXT_SHORT_DESC_EN];
        if(!empty($property[TAG_TEXT_DESC_NL])) $text .= $property[TAG_TEXT_DESC_NL];
        if(!empty($property[TAG_TEXT_DESC_FR])) $text .= $property[TAG_TEXT_DESC_FR];
        if(!empty($property[TAG_TEXT_DESC_DE])) $text .= $property[TAG_TEXT_DESC_DE];
        if(!empty($property[TAG_TEXT_DESC_EN])) $text .= $property[TAG_TEXT_DESC_EN];

        $property[TAG_IS_INVESTMENT_PROPERTY] = CrawlerTool::isInvestmentProperty($text);
    }

	public static function toXHTML($html)
	{
		$tidy_config = array(
		"clean" => true,
		"output-xhtml" => true,
		"wrap" => 0,
		);

		$tidy = tidy_parse_string($html, $tidy_config);
		$tidy->cleanRepair();
		return $tidy;
	}

	/**
     * Example convert 500.00 to 500 (500,00 to 500)
     * and 500.000 to 500000 (500,000 to 500000)
     *
     * @param string $str
     * @return integer
     */
	public static function toNumber($str)
	{
		$value = 0;
		$str = preg_replace("/(,\d{2})$|(\.\d{2})$|\s/", "", $str);
		$str = preg_replace("/,(\d{3})|\.(\d{3})/",  "$1$2", $str);
		if(preg_match("/\d+/", $str, $match)) $value = intval($match[0]);

		return $value;
	}

	public static function strip($str)
	{
		$str = str_replace(chr(194) . chr(160), " ", $str);
		$str = preg_replace("/\s+|\n/", " ", $str);
		return trim($str);
	}

	public static function generateId($text)
	{
		$str = crc32($text);
		$id = sprintf("%u",$str);
		return $id;
	}

	public static function isNewConstruction($text)
	{
		if(stripos($text, "nieuwbouw") !== false
		|| stripos($text, "ja") !== false
		|| stripos($text, "nieuw") !== false
		)
		{
			return 1;
		}
	}

    public static function isInvestmentProperty($text)
    {
        if(stripos($text, "Opbrengsteigendom") !== false
        || stripos($text, "Immeubele de rapport") !== false
        )
        {
            return 1;
        }
    }

    public static function getConstructionType($text)
    {
        if(stripos($text, "Gesloten") !== false)
        {
            return "closed";
        }
        elseif(stripos($text, "Halfopen") !== false || stripos($text, "Half open") !== false)
        {
            return "half-open";
        }
        elseif(stripos($text, "Open") !== false)
        {
            return "open";
        }
        else
        {
            return "";
        }
    }

	public static function getPropertyStatus($text)
	{
		if(stripos($text, "te koop") !== false
		|| stripos($text, " koop ") !== false
        || stripos($text, "Vraagprijs") !== false
		|| stripos($text, "vendre") !== false
		|| stripos($text, "sell") !== false
		)
		{
			return STATUS_FORSALE;
		}
		else if(stripos($text, "te huur") !== false
		|| stripos($text, "huur ") !== false
        || stripos($text, "Huurprijs") !== false
		|| stripos($text, "louer") !== false
		|| stripos($text, "location") !== false
		)
		{
			return STATUS_FORRENT;
		}
		else if(stripos($text, "verkocht") !== false
        || stripos($text, "verk.gif") !== false
		|| stripos($text, "vendu") !== false
		|| stripos($text, "vend") !== false
		|| stripos($text, "sold") !== false
		)
		{
			return STATUS_SOLD;
		}
		else if(stripos($text, "verhuurd") !== false
        || stripos($text, "verh.gif") !== false
		|| stripos($text, "lou") !== false
		|| stripos($text, "rented") !== false
		)
		{
			return STATUS_RENTED;
		}
		else
		{
			return "";
		}

	}

	public static function getPropertyType($text,$max_len = 100)
	{
		$text = substr($text,0,$max_len);

		//should be before house check because contains "huis" = house
		if (stripos($text, 'Koffiehuis') !== false)
		{
			return TYPE_COMMERCIAL;
		}

		if(stripos($text, 'Appartement') !== false
		|| stripos($text, 'Flat') !== false
		|| stripos($text, 'Duplex') !== false
		|| stripos($text, 'Triplex') !== false
		|| stripos($text, 'Rez-de-chauss') !== false
		|| stripos($text, 'Loft') !== false
		|| stripos($text, 'Dernier ') !== false
		|| stripos($text, 'Penthouse') !== false
		|| stripos($text, 'apartment') !==false
		|| stripos($text, 'appartemen') !==false
		|| stripos($text, 'studio') !== false
		|| stripos($text, 'slaapkamerapp') !== false
		|| stripos($text, 'slaapkamer app ') !== false
				
		|| stripos($text, 'Gelijkvloerse verdieping') !== false

		)
		{
			return TYPE_APARTMENT;
		}
		else if(stripos($text, 'HANDEL') !== false
		|| stripos($text, 'Bureau') !== false
		|| stripos($text, 'Bedrijfsgebouw') !== false
		|| stripos($text, 'Shop') !== false
		|| stripos($text, 'industri') !== false
		|| stripos($text, 'office') !== false
		|| stripos($text, 'FRITUUR') !== false
		|| stripos($text, 'commerci') !== false
		|| stripos($text, 'commerce') !== false
		|| stripos($text, 'kantoor') !== false
		|| stripos($text, 'kantoren') !== false
		|| stripos($text, 'industrieel') !== false
		|| stripos($text, 'Magazijn') !== false
		|| stripos($text, 'werkplaats') !== false
		|| stripos($text, 'Burelencomplex') !== false
		|| stripos($text, 'Bedrijfspand') !== false
        || stripos($text, 'Bedrijfsvastgoed') !== false
		|| stripos($text, 'Entrep') !== false
		|| stripos($text, 'recreatie') !== false
		|| stripos($text, 'winkelruimte') !== false
		|| stripos($text, 'winkelpand') !== false
		|| stripos($text, 'bedrijfshal') !== false
		|| stripos($text, 'opbrengsteigendom') !== false
        || stripos($text, 'magasin') !== false
        || stripos($text, 'Restaurant') !== false
		)
		{
			return TYPE_COMMERCIAL;
		}
		else if(stripos($text, 'Maison') !== false
		|| stripos($text, 'Woning') !== false
		|| stripos($text, 'chalet') !== false
		|| stripos($text, 'Villa') !== false
		|| stripos($text, 'manoir') !== false
		|| stripos($text, 'Ferme') !== false
		|| stripos($text, 'Boerderij') !== false
		|| stripos($text, 'woonboerderij') !== false
		|| stripos($text, 'woonkaravaan') !== false
		|| stripos($text, 'huis') !== false
        || stripos($text, 'Huizen') !== false
		|| stripos($text, 'Hoeve') !== false
		|| stripos($text, 'House') !== false
		|| stripos($text, 'Cottage') !== false
		|| stripos($text, 'Bungalow') !== false
		|| stripos($text, 'Immobilier') !== false
		|| stripos($text, 'Immeuble') !== false
		|| stripos($text, "woonproject") !== false
		|| stripos($text, "Open bebouwing") !== false
		|| stripos($text, "Half open bebouwing") !== false
		|| stripos($text, "Chateau") !== false
        || stripos($text, "Caravane") !== false
        || stripos($text, 'kasteel') !== false

		)
		{
			return TYPE_HOUSE;
		}
		else if(stripos($text, 'grond') !== false
		|| stripos($text, 'Terrain') !== false
		|| stripos($text, 'bouwgrond') !== false
		|| stripos($text, 'bouwperceel') !== false
		|| stripos($text, 'Plot surface') !== false
		)
		{
			return TYPE_PLOT;
		}
		else if(stripos($text, 'garage') !== false
		|| stripos($text, 'parking') !== false
		|| stripos($text, 'berging') !== false
		|| stripos($text, 'kavel') !== false
		|| stripos($text, 'autostaanplaats') !== false
		|| stripos($text, 'autostandplaats') !== false
		|| stripos($text, 'Parkeerplaats') !== false
		)
		{
			return TYPE_GARAGE;
		}
		else
		{
			return "";
		}
	}

	public static function test($obj)
	{
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
		exit;
	}

	/**
	 * Add full url to every pic_url in array,
	 *
	 * @param array $arr
	 * @param string $add
	 * @return array
	 */
	public static function addTextToPicUrls($arr,$add)
	{
		foreach ($arr as &$v) {$v = array(TAG_PICTURE_URL => $add.str_replace('&amp;','&',$v));}
		unset($v);
		return $arr;
	}

	public static function toUTF8($text)
	{
		return iconv("ISO-8859-1", "UTF-8//IGNORE", $text);
	}


	public static function encode_funct($x)
	{
		if ($x=='&amp;') {return $x;}
		if ($x=='&euro;') {return '&#x20AC;';}
		return '&#'.ord(html_entity_decode($x,ENT_NOQUOTES,'UTF-8')).';';
	}

	public static function encode($text)
	{

		if (mb_detect_encoding($text)!='UTF-8')
		{
			$text = mb_convert_encoding($text,'UTF-8');
		}


		$text  = html_entity_decode($text,ENT_QUOTES,"UTF-8");

		$text2 = htmlentities($text,ENT_QUOTES,"UTF-8");


		//sometimes mb_detect_encoding not detect utf8, so htmlentities returns empty string
		if (empty($text2))
		{
			$text  = mb_convert_encoding($text,'UTF-8');
			$text2 = htmlentities($text,ENT_QUOTES,"UTF-8");
		}

		$codes = array(
		'À'=>'&Agrave;',
		'à'=>'&agrave;',
		'Á'=>'&Aacute;',
		'á'=>'&aacute;',
		'Â'=>'&Acirc;',
		'â'=>'&acirc;',
		'Ã'=>'&Atilde;',
		'ã'=>'&atilde;',
		'Ä'=>'&Auml;',
		'ä'=>'&auml;',
		'Å'=>'&Aring;',
		'å'=>'&aring;',
		'Æ'=>'&AElig;',
		'æ'=>'&aelig;',
		'Ç'=>'&Ccedil;',
		'ç'=>'&ccedil;',
		'Ð'=>'&ETH;',
		'ð'=>'&eth;',
		'È'=>'&Egrave;',
		'è'=>'&egrave;',
		'É'=>'&Eacute;',
		'é'=>'&eacute;',
		'Ê'=>'&Ecirc;',
		'ê'=>'&ecirc;',
		'Ë'=>'&Euml;',
		'ë'=>'&euml;',
		'Ì'=>'&Igrave;',
		'ì'=>'&igrave;',
		'Í'=>'&Iacute;',
		'í'=>'&iacute;',
		'Î'=>'&Icirc;',
		'î'=>'&icirc;',
		'Ï'=>'&Iuml;',
		'ï'=>'&iuml;',
		'Ñ'=>'&Ntilde;',
		'ñ'=>'&ntilde;',
		'Ò'=>'&Ograve;',
		'ò'=>'&ograve;',
		'Ó'=>'&Oacute;',
		'ó'=>'&oacute;',
		'Ô'=>'&Ocirc;',
		'ô'=>'&ocirc;',
		'Õ'=>'&Otilde;',
		'õ'=>'&otilde;',
		'Ö'=>'&Ouml;',
		'ö'=>'&ouml;',
		'Ø'=>'&Oslash;',
		'ø'=>'&oslash;',
		'Œ'=>'&OElig;',
		'œ'=>'&oelig;',
		'ß'=>'&szlig;',
		'Þ'=>'&THORN;',
		'þ'=>'&thorn;',
		'Ù'=>'&Ugrave;',
		'ù'=>'&ugrave;',
		'Ú'=>'&Uacute;',
		'ú'=>'&uacute;',
		'Û'=>'&Ucirc;',
		'û'=>'&ucirc;',
		'Ü'=>'&Uuml;',
		'ü'=>'&uuml;',
		'Ý'=>'&Yacute;',
		'ý'=>'&yacute;',
		'Ÿ'=>'&Yuml;',
		'ÿ'=>'&yuml;'
		);
		$codes['&#130;'] = '&sbquo;';    // Single Low-9 Quotation Mark
		$codes['&#131;'] = '&fnof;';    // Latin Small Letter F With Hook
		$codes['&#132;'] = '&bdquo;';    // Double Low-9 Quotation Mark
		$codes['&#133;'] = '&hellip;';    // Horizontal Ellipsis
		$codes['&#134;'] = '&dagger;';    // Dagger
		$codes['&#135;'] = '&Dagger;';    // Double Dagger
		$codes['&#136;'] = '&circ;';    // Modifier Letter Circumflex Accent
		$codes['&#137;'] = '&permil;';    // Per Mille Sign
		$codes['&#138;'] = '&Scaron;';    // Latin Capital Letter S With Caron
		$codes['&#139;'] = '&lsaquo;';    // Single Left-Pointing Angle Quotation Mark
		$codes['&#140;'] = '&OElig;';    // Latin Capital Ligature OE
		$codes['&#145;'] = '&lsquo;';    // Left Single Quotation Mark
		$codes['&#146;'] = '&rsquo;';    // Right Single Quotation Mark
		$codes['&#149;'] = '&bull;';    // Bullet
		$codes['&#150;'] = '&ndash;';    // En Dash
		$codes['&#151;'] = '&mdash;';    // Em Dash
		$codes['&#152;'] = '&tilde;';    // Small Tilde
		$codes['&#153;'] = '&trade;';    // Trade Mark Sign
		$codes['&#154;'] = '&scaron;';    // Latin Small Letter S With Caron
		$codes['&#155;'] = '&rsaquo;';    // Single Right-Pointing Angle Quotation Mark
		$codes['&#156;'] = '&oelig;';    // Latin Small Ligature OE
		$codes['&#159;'] = '&Yuml;';    // Latin Capital Letter Y With Diaeresis
		$codes['euro']   = '&euro;';    // euro currency symbol
		$codes['&#178;'] = '&sup2;';

		$text2 = preg_replace('!&[rl]{1}dquo;!i','"',$text2);

		foreach ($codes as $char => $code)
		{
			$text2 = str_replace($code,$char,$text2);
		}

		preg_match_all('!(&[^#][^; ]+;)!',$text2,$res);
		$res[1] = array_unique($res[1]);
		foreach ($res[1] as $element)
		{
			if ($element=='&quot;') {continue;}
			$text2 = str_replace($element,self::encode_funct($element),$text2);
		}

		return $text2;
	}

	public static function toUnixTimestamp($text)
	{
		return strtotime($text);
	}

	/**
     * parse street, number and box number from string
     * supports input format:street name 123 A , street 123, street
     *
     * @param string $text
     * @param array $property
     */
	public static function parseAddress($text, &$property)
	{
		//remove (aaa) or ( or )
        $text = preg_replace("/\([A-Za-z\s]{3,}\)|\(|\)/", "", $text);
		$text = preg_replace("/\s+(\-|\/)\s+/", "$1", $text);

		if(preg_match("/([^\d]*)\s(\d+[\/\d*|\d+[-\d]*)/", $text, $match))
		{
			$property[TAG_STREET] = trim(preg_replace("/\+|\&|\d/", "", $match[1]));
			$property[TAG_NUMBER] = trim($match[2]);
            $property[TAG_BOX_NUMBER] = trim(str_replace($match[1] . " " . $match[2], "", $text));
		}
		else
		{
			$property[TAG_STREET] = trim($text);
		}
	}

	/**
     * parse ground surface from string
     * supports input format: "1 ha" or "1ha" or "1 are 45 ca"
     *
     * @param string $text 1 ha or 1ha or 1 are 45 ca..
     * @param integer 
     */
	public static function toMeter($text)
	{
		$result = 0;

		if(preg_match("/(\d+[,.\d+]*)\s*ha\s*(\d+[,.\d+]*)\s*(a|are|ares)\s*(\d+[,.\d+]*)\s*ca/i", $text, $match))
		{
			$ha = str_replace(",", ".", $match[1]);
			$a =  str_replace(",", ".", $match[2]);
			$ca =  str_replace(",", ".", $match[4]);
			$result = ($ha * 10000) + ($a * 100) + $ca;
		}
		else if(preg_match("/(\d+[,.\d+]*)\s*ha\s*(\d+[,.\d+]*)\s*a/i", $text, $match))
		{
			$ha = str_replace(",", ".", $match[1]);
			$a =  str_replace(",", ".", $match[2]);
			$result = ($ha * 10000) + ($a * 100);
		}
		else if(preg_match("/(\d+[,.\d+]*)\s*ha\s*(\d+[,.\d+]*)\s*ca/i", $text, $match))
		{
			$ha = str_replace(",", ".", $match[1]);
			$ca =  str_replace(",", ".", $match[2]);
			$result = ($ha * 10000) + $ca;
		}
		else if(preg_match("/(\d+[,.\d+]*)\s*(a|are|ares)\s*(\d+[,.\d+]*)\s*[ca]*/i", $text, $match))
		{
			$a =  str_replace(",", ".", $match[1]);
			$ca =  str_replace(",", ".", $match[3]);
			$result = ($a * 100) + $ca;
		}
		else if(preg_match("/(\d+[,.\d+]*)\s*(ha|hectare)/i", $text, $match))
		{
			$ha = str_replace(",", ".", $match[1]);
			$result = ($ha * 10000) ;
		}
		else if(preg_match("/(\d+[,.\d+]*)\s*a/i", $text, $match))
		{
			$a =  str_replace(",", ".", $match[1]);
			$result = ($a * 100);
		}
		else if(preg_match("/(\d+[,.\d+]*)\s*ca/i", $text, $match))
		{
			$ca =  str_replace(",", ".", $match[1]);
			$result = $ca;
		}

		return CrawlerTool::toNumber($result);
	}
	
	/**
	 * Returns true, if function found word, that shouldn't be in property text
	 *
	 * @param string $text
	 * @return boolean
	 */
	public static function skipWordFound($text)
	{
		static $file_loaded = false;
		static $skip_words = array();
		
		if (!$file_loaded) 
		{
			$file_loaded = true;
			if (is_file(SCRIPT_DIR.'skip_words.txt'))
			{
				$tmp = file(SCRIPT_DIR.'skip_words.txt');
				foreach ($tmp as $v) 
				{
					$v = trim($v);
					if (empty($v)) {continue;}
					$skip_words[] = $v;
				}
			}
		}

		
		foreach ($skip_words as $word) 
		{
			if (stripos($text,$word)!==false) {return true;}
		}
		
		return false;
	}
	
}

// value constants for TAG_STATUS
define('STATUS_FORSELL'   , "forsale"); // it should be spelled FORSALE, it was a mistake
define('STATUS_FORSALE'   , "forsale");
define('STATUS_FORRENT'   , "torent");
define('STATUS_TORENT'    , "torent");
define('STATUS_TAKEOVER'  , "takeover");
define('STATUS_SOLD'      , "sold");
define('STATUS_RENTED'    , "rented");
define('STATUS_TAKENOVER' , "takenover");

// value constants for TAG_TYPE
define('TYPE_HOUSE'       , "house");
define('TYPE_APARTMENT'   , "apartment");
define('TYPE_GARAGE'      , "garage");
define('TYPE_PLOT'        , "plot");
define('TYPE_COMMERCIAL'  , "commercial");
define('TYPE_NONE'        , "");


const TAG_STATUS = "status";
const TAG_TYPE = "type";
const TAG_TYPE_LONG = "type_long";
const TAG_UNIQUE_ID = "unique_id";
const TAG_UNIQUE_URL_NL = "unique_url_nl";
const TAG_UNIQUE_URL_FR = "unique_url_fr";
const TAG_UNIQUE_URL_EN = "unique_url_en";
const TAG_UNIQUE_URL_DE = "unique_url_de";
const TAG_OFFICE_ID = "office_id";
const TAG_OFFICE_NAME = "office_name";
const TAG_OFFICE_URL = "office_url";
const TAG_FACEBOOK_URL = "facebook_url";
const TAG_TWITTER_URL = "twitter_url";
const TAG_GOOGLE_PLUS_URL = "google_plus_url";
const TAG_LINKEDIN_URL = "linked_url";
const TAG_EMPLOYEES = "employees";
const TAG_EMPLOYEE_IDS = "employee_ids";
const TAG_EMPLOYEE_ID = "employee_id";
const TAG_EMPLOYEE_FIRST_NAME = "employee_first_name";
const TAG_EMPLOYEE_NAME = "employee_name";
const TAG_EMPLOYEE_GENDER = "employee_gender";
const TAG_EMPLOYEE_TITLE = "employee_title";
const TAG_EMPLOYEE_URL = "employee_url";
const TAG_PROJECT_ID = "project_id";
const TAG_ARCHIVE = "archive";
const TAG_ARCHIVE_DATE = "archive_date";
const TAG_STREET = "street";
const TAG_NUMBER = "number";
const TAG_BOX_NUMBER = "box_number";
const TAG_ZIP = "zip";
const TAG_CITY = "city";
const TAG_COUNTRY = "country";
const TAG_ADDRESS_VISIBLE = "address_visible";
const TAG_NUMBER_VISIBLE = "number_visible";
const TAG_LATITUDE = "latitude";
const TAG_LONGITUDE = "longitude";
const TAG_TELEPHONE = "telephone";
const TAG_CELLPHONE = "cellphone";
const TAG_FAX = "fax";
const TAG_EMAIL = "email";
const TAG_SOLD_PERCENTAGE_MAX = "sold_percentage_max";
const TAG_SOLD_PERCENTAGE_VALUE = "sold_percentage_value";
const TAG_REALIZATION_DATE = "realization_date";
const TAG_REALIZATION_COMMENTS = "realization_conmments";
const TAG_REALIZATION_FINISHED = "realization_finished";
const TAG_PICTURES = "pictures";
const TAG_PICTURE_URL = "picture_url";
const TAG_PICTURE_TITLE_NL = "picture_title_nl";
const TAG_PICTURE_TITLE_FR = "picture_title_fr";
const TAG_PICTURE_TITLE_EN = "picture_title_en";
const TAG_PICTURE_TITLE_DE = "picture_title_de";
const TAG_PICTURE_DESC_NL = "picture_desc_nl";
const TAG_PICTURE_DESC_FR = "picture_desc_fr";
const TAG_PICTURE_DESC_EN = "picture_desc_en";
const TAG_PICTURE_DESC_DE = "picture_desc_de";
const TAG_FILES = "files";
const TAG_FILE_URL = "file_url";
const TAG_FILE_FLOOR = "file_floor";
const TAG_FILE_URL_NL = "file_url_nl";
const TAG_FILE_URL_FR = "file_url_fr";
const TAG_FILE_URL_EN = "file_url_en";
const TAG_FILE_URL_DE = "file_url_de";
const TAG_FILE_TITLE_NL = "file_title_nl";
const TAG_FILE_TITLE_FR = "file_title_fr";
const TAG_FILE_TITLE_EN = "file_title_en";
const TAG_FILE_TITLE_DE = "file_title_de";
const TAG_FILE_DESC_NL = "file_desc_nl";
const TAG_FILE_DESC_FR = "file_desc_fr";
const TAG_FILE_DESC_EN = "file_desc_en";
const TAG_FILE_DESC_DE = "file_desc_de";
const TAG_VIDEOS = "videos";
const TAG_VIDEO_URL = "video_url";
const TAG_VIDEO_URL_NL = "video_url_nl";
const TAG_VIDEO_URL_FR = "video_url_fr";
const TAG_VIDEO_URL_EN = "video_url_en";
const TAG_VIDEO_URL_DE = "video_url_de";
const TAG_VIDEO_TITLE_NL = "video_title_nl";
const TAG_VIDEO_TITLE_FR = "video_title_fr";
const TAG_VIDEO_TITLE_EN = "video_title_en";
const TAG_VIDEO_TITLE_DE = "video_title_de";
const TAG_VIDEO_DESC_NL = "video_desc_nl";
const TAG_VIDEO_DESC_FR = "video_desc_fr";
const TAG_VIDEO_DESC_EN = "video_desc_en";
const TAG_VIDEO_DESC_DE = "video_desc_de";
const TAG_FLOOR_PLANS = "floor_plans";
const TAG_FLOOR_PLAN_FLOOR_LEVEL = "floor_plan_floor_level";
const TAG_FLOOR_PLAN_URL = "floor_plan_url";
const TAG_FLOOR_PLAN_TITLE_NL = "floor_plan_title_nl";
const TAG_FLOOR_PLAN_TITLE_FR = "floor_plan_title_fr";
const TAG_FLOOR_PLAN_TITLE_EN = "floor_plan_title_en";
const TAG_FLOOR_PLAN_TITLE_DE = "floor_plan_title_de";
const TAG_FLOOR_PLAN_DESC_NL = "floor_plan_desc_nl";
const TAG_FLOOR_PLAN_DESC_FR = "floor_plan_desc_fr";
const TAG_FLOOR_PLAN_DESC_EN = "floor_plan_desc_en";
const TAG_FLOOR_PLAN_DESC_DE = "floor_plan_desc_de";
const TAG_VIRTUAL_VISITS = "virtual_visits";
const TAG_VIRTUAL_VISIT_URL = "virtual_visit_url";
const TAG_VIRTUAL_VISIT_URL_NL = "virtual_visit_url_nl";
const TAG_VIRTUAL_VISIT_URL_FR = "virtual_visit_url_fr";
const TAG_VIRTUAL_VISIT_URL_EN = "virtual_visit_url_en";
const TAG_VIRTUAL_VISIT_URL_DE = "virtual_visit_url_de";
const TAG_VIRTUAL_VISIT_TITLE_NL = "virtual_visit_title_nl";
const TAG_VIRTUAL_VISIT_TITLE_FR = "virtual_visit_title_fr";
const TAG_VIRTUAL_VISIT_TITLE_EN = "virtual_visit_title_en";
const TAG_VIRTUAL_VISIT_TITLE_DE = "virtual_visit_title_de";
const TAG_VIRTUAL_VISIT_DESC_NL = "virtual_visit_desc_nl";
const TAG_VIRTUAL_VISIT_DESC_FR = "virtual_visit_desc_fr";
const TAG_VIRTUAL_VISIT_DESC_EN = "virtual_visit_desc_en";
const TAG_VIRTUAL_VISIT_DESC_DE = "virtual_visit_desc_de";
const TAG_TEXT_TITLE_NL = "text_title_nl";
const TAG_TEXT_TITLE_FR = "text_title_fr";
const TAG_TEXT_TITLE_EN = "text_title_en";
const TAG_TEXT_TITLE_DE = "text_title_de";
const TAG_TEXT_SHORT_DESC_NL = "text_short_desc_nl";
const TAG_TEXT_SHORT_DESC_FR = "text_short_desc_fr";
const TAG_TEXT_SHORT_DESC_EN = "text_short_desc_en";
const TAG_TEXT_SHORT_DESC_DE = "text_short_desc_de";
const TAG_TEXT_DESC_NL = "text_desc_nl";
const TAG_TEXT_DESC_FR = "text_desc_fr";
const TAG_TEXT_DESC_EN = "text_desc_en";
const TAG_TEXT_DESC_DE = "text_desc_de";
const TAG_CONSTRUCTION_TYPE = "construction_type";
const TAG_CONSTRUCTION_YEAR = "construction_year";
const TAG_RENOVATION_YEAR = "renovation_year";
const TAG_BEDROOMS_TOTAL = "bedrooms_total";
const TAG_BATHROOMS_TOTAL = "bathrooms_total";
const TAG_TOILETS_TOTAL = "toilets_total";
const TAG_NEED_TO_RENOVATE = "need_to_renovate";
const TAG_RENOVATION_COSTS = "renovation_costs";
const TAG_IS_NEW_CONSTRUCTION = "is_new_construction";
const TAG_IS_INVESTMENT_PROPERTY = "is_investment_property";
const TAG_IS_EXCLUSIVE = "is_exclusive";
const TAG_IS_VACATION = "is_vacation";
const TAG_IS_HOMESHARING = "is_homesharing";
const TAG_IS_STUDENT = "is_student";
const TAG_IS_PASSIVE = "is_passive";
const TAG_IS_LOW_ENERGY = "is_low_energy";
const TAG_CONSTRUCTION_OBLIGATION = "construction_obligation";
const TAG_SURFACE_GROUND = "surface_ground";
const TAG_SURFACE_CONSTRUCTION = "surface_construction";
const TAG_SURFACE_LIVING_AREA = "surface_living_area";
const TAG_LOT_WIDTH = "lot_width";
const TAG_LOT_DEPTH = "lot_depth";
const TAG_LOT_NUMBER = "lot_number";
const TAG_FRONTAGE_WIDTH = "frontage_width";
const TAG_FLOOR = "floor";
const TAG_AMOUNT_OF_FLOORS = "amount_of_floors";
const TAG_DEPTH_GROUND_FLOOR = "depth_ground_floor";
const TAG_DEPTH_FLOOR = "depth_floor";
const TAG_DISTANCE_TO_BUILDING_LINE = "distance_to_building_line";
const TAG_DISTANCE_TO_STREET_AXIS = "distance_to_street_axis";
const TAG_DISTANCE_TO_LEFT_SIDE_BOUNDARY = "distance_to_left_side_boundary";
const TAG_DISTANCE_TO_RIGHT_SIDE_BOUNDARY = "distance_to_right_side_boundary";
const TAG_AMOUNT_OF_FACADES = "amount_of_facades";
const TAG_ROOF_SLOPE = "roof_slope";
const TAG_CADASTRAL_CLASSIFICATION = "cadastral_classification";
const TAG_CADASTRAL_SECTION = "cadastral_section";
const TAG_CADASTRAL_KIND = "cadastral_kind";
const TAG_EPC_VALUE = "epc_value";
const TAG_EPC_CERTIFICATE_NUMBER = "epc_certificate_number";
const TAG_CO2_EMISSION = "co2_emission";
const TAG_MAZOUT_CERTIFICATE = "mazout_certificate";
const TAG_HAS_ELECTRICAL_INSPECTION_CERTIFICATE = "has_electrical_inspection_certificate";
const TAG_NON_VALID_ELECTRICAL_INSPECTION_CERTIFICATE = "non_valid_electrical_inspection_certificate";
const TAG_K_LEVEL = "k_level";
const TAG_E_LEVEL = "e_level";
const TAG_AS_BUILD_ATTEST = "as_build_attest";
const TAG_PLANNING_PERMISSION = "planning_permission";
const TAG_PLANNING_PERMISSION_INFORMATION_NL = "planning_permission_information_nl";
const TAG_PLANNING_PERMISSION_INFORMATION_FR = "planning_permission_information_fr";
const TAG_PLANNING_PERMISSION_INFORMATION_EN = "planning_permission_information_en";
const TAG_PLANNING_PERMISSION_INFORMATION_DE = "planning_permission_information_de";
const TAG_HAS_PROCEEDING = "has_proceeding";
const TAG_PROCEEDING_INFORMATION_NL = "proceeding_information_nl";
const TAG_PROCEEDING_INFORMATION_FR = "proceeding_information_fr";
const TAG_PROCEEDING_INFORMATION_EN = "proceeding_information_en";
const TAG_PROCEEDING_INFORMATION_DE = "proceeding_information_de";
const TAG_PRIORITY_PURCHASE = "priority_purchase";
const TAG_PRIORITY_PURCHASE_INFORMATION_NL = "priority_purchase_information_nl";
const TAG_PRIORITY_PURCHASE_INFORMATION_FR = "priority_purchase_information_fr";
const TAG_PRIORITY_PURCHASE_INFORMATION_EN = "priority_purchase_information_en";
const TAG_PRIORITY_PURCHASE_INFORMATION_DE = "priority_purchase_information_de";
const TAG_SUBDIVISION_PERMIT = "subdivision_permit";
const TAG_SUBDIVISION_INFORMATION_NL = "subdivision_information_nl";
const TAG_SUBDIVISION_INFORMATION_FR = "subdivision_information_fr";
const TAG_SUBDIVISION_INFORMATION_EN = "subdivision_information_en";
const TAG_SUBDIVISION_INFORMATION_DE = "subdivision_information_de";
const TAG_MOST_RECENT_DESTINATION = "most_recent_destination";
const TAG_MOST_RECENT_DESTINATION_INFORMATION_NL = "most_recent_destination_information_nl";
const TAG_MOST_RECENT_DESTINATION_INFORMATION_FR = "most_recent_destination_information_fr";
const TAG_MOST_RECENT_DESTINATION_INFORMATION_EN = "most_recent_destination_information_en";
const TAG_MOST_RECENT_DESTINATION_INFORMATION_DE = "most_recent_destination_information_de";
const TAG_PRICE = "price";
const TAG_PRICE_VISIBLE = "price_visible";
const TAG_KI = "ki";
const TAG_KI_INDEX = "ki_index";
const TAG_DATE_KI_INDEX = "date_ki_index";
const TAG_COMMON_COSTS = "common_costs";
const TAG_PRICE_PER_M2 = "price_per_m2";
const TAG_PROPERTY_TAX = "property_tax";
const TAG_DATE_PROPERTY_TAX = "date_property_tax";
const TAG_PROVISION = "provision";
const TAG_NET_REVENUE = "net_revenue";
const TAG_ATELIERS = "ateliers";
const TAG_ATELIER_SURFACE = "atelier_surface";
const TAG_ATELIER_FLOOR = "atelier_floor";
const TAG_ATELIER_DESC_NL = "atelier_desc_nl";
const TAG_ATELIER_DESC_FR = "atelier_desc_fr";
const TAG_ATELIER_DESC_EN = "atelier_desc_en";
const TAG_ATELIER_DESC_DE = "atelier_desc_de";
const TAG_BATHROOMS = "bathrooms";
const TAG_BATHROOM_SURFACE = "bathroom_surface";
const TAG_BATHROOM_FLOOR = "bathroom_floor";
const TAG_BATHROOM_DESC_NL = "bathroom_desc_nl";
const TAG_BATHROOM_DESC_FR = "bathroom_desc_fr";
const TAG_BATHROOM_DESC_EN = "bathroom_desc_en";
const TAG_BATHROOM_DESC_DE = "bathroom_desc_de";
const TAG_STOREROOMS = "storerooms";
const TAG_STOREROOM_SURFACE = "storeroom_surface";
const TAG_STOREROOM_FLOOR = "storeroom_floor";
const TAG_STOREROOM_DESC_NL = "storeroom_desc_nl";
const TAG_STOREROOM_DESC_FR = "storeroom_desc_fr";
const TAG_STOREROOM_DESC_EN = "storeroom_desc_en";
const TAG_STOREROOM_DESC_DE = "storeroom_desc_de";
const TAG_STUDIES = "studies";
const TAG_STUDY_SURFACE = "study_surface";
const TAG_STUDY_FLOOR = "study_floor";
const TAG_STUDY_DESC_NL = "study_desc_nl";
const TAG_STUDY_DESC_FR = "study_desc_fr";
const TAG_STUDY_DESC_EN = "study_desc_en";
const TAG_STUDY_DESC_DE = "study_desc_de";
const TAG_HEATING_AREAS = "heating_areas";
const TAG_HEATING_AREA_SURFACE = "heating_area_surface";
const TAG_HEATING_AREA_FLOOR = "heating_area_floor";
const TAG_HEATING_AREA_DESC_NL = "heating_area_desc_nl";
const TAG_HEATING_AREA_DESC_FR = "heating_area_desc_fr";
const TAG_HEATING_AREA_DESC_EN = "heating_area_desc_en";
const TAG_HEATING_AREA_DESC_DE = "heating_area_desc_de";
const TAG_DRESSINGS = "dressings";
const TAG_DRESSING_SURFACE = "dressing_surface";
const TAG_DRESSING_FLOOR = "dressing_floor";
const TAG_DRESSING_DESC_NL = "dressing_desc_nl";
const TAG_DRESSING_DESC_FR = "dressing_desc_fr";
const TAG_DRESSING_DESC_EN = "dressing_desc_en";
const TAG_DRESSING_DESC_DE = "dressing_desc_de";
const TAG_DININGS = "dinings";
const TAG_DINING_SURFACE = "dining_surface";
const TAG_DINING_FLOOR = "dining_floor";
const TAG_DINING_DESC_NL = "dining_desc_nl";
const TAG_DINING_DESC_FR = "dining_desc_fr";
const TAG_DINING_DESC_EN = "dining_desc_en";
const TAG_DINING_DESC_DE = "dining_desc_de";
const TAG_GARAGES= "garages";
const TAG_GARAGE_SURFACE = "garage_surface";
const TAG_GARAGE_FLOOR = "garage_floor";
const TAG_GARAGE_DESC_NL = "garage_desc_nl";
const TAG_GARAGE_DESC_FR = "garage_desc_fr";
const TAG_GARAGE_DESC_EN = "garage_desc_en";
const TAG_GARAGE_DESC_DE = "garage_desc_de";
const TAG_HALLS = "halls";
const TAG_HALL_SURFACE = "hall_surface";
const TAG_HALL_FLOOR = "hall_floor";
const TAG_HALL_DESC_NL = "hall_desc_nl";
const TAG_HALL_DESC_FR = "hall_desc_fr";
const TAG_HALL_DESC_EN = "hall_desc_en";
const TAG_HALL_DESC_DE = "hall_desc_de";
const TAG_CELLARS = "cellars";
const TAG_CELLAR_SURFACE = "cellar_surface";
const TAG_CELLAR_FLOOR = "cellar_floor";
const TAG_CELLAR_DESC_NL = "cellar_desc_nl";
const TAG_CELLAR_DESC_FR = "cellar_desc_fr";
const TAG_CELLAR_DESC_EN = "cellar_desc_en";
const TAG_CELLAR_DESC_DE = "cellar_desc_de";
const TAG_KITCHENS = "kitchens";
const TAG_KITCHEN_SURFACE = "kitchen_surface";
const TAG_KITCHEN_FLOOR = "kitchen_floor";
const TAG_KITCHEN_DESC_NL = "kitchen_desc_nl";
const TAG_KITCHEN_DESC_FR = "kitchen_desc_fr";
const TAG_KITCHEN_DESC_EN = "kitchen_desc_en";
const TAG_KITCHEN_DESC_DE = "kitchen_desc_de";
const TAG_LIVINGS = "livings";
const TAG_LIVING_SURFACE = "living_surface";
const TAG_LIVING_FLOOR = "living_floor";
const TAG_LIVING_DESC_NL = "living_desc_nl";
const TAG_LIVING_DESC_FR = "living_desc_fr";
const TAG_LIVING_DESC_EN = "living_desc_en";
const TAG_LIVING_DESC_DE = "living_desc_de";
const TAG_NIGHT_HALLS = "night_halls";
const TAG_NIGHT_HALL_SURFACE = "night_hall_surface";
const TAG_NIGHT_HALL_FLOOR = "night_hall_floor";
const TAG_NIGHT_HALL_DESC_NL = "night_hall_desc_nl";
const TAG_NIGHT_HALL_DESC_FR = "night_hall_desc_fr";
const TAG_NIGHT_HALL_DESC_EN = "night_hall_desc_en";
const TAG_NIGHT_HALL_DESC_DE = "night_hall_desc_de";
const TAG_SHOWROOMS = "showrooms";
const TAG_SHOWROOM_SURFACE = "showroom_surface";
const TAG_SHOWROOM_FLOOR = "showroom_floor";
const TAG_SHOWROOM_DESC_NL = "showroom_desc_nl";
const TAG_SHOWROOM_DESC_FR = "showroom_desc_fr";
const TAG_SHOWROOM_DESC_EN = "showroom_desc_en";
const TAG_SHOWROOM_DESC_DE = "showroom_desc_de";
const TAG_BEDROOMS = "bedrooms";
const TAG_BEDROOM_SURFACE = "bedroom_surface";
const TAG_BEDROOM_FLOOR = "bedroom_floor";
const TAG_BEDROOM_DESC_NL = "bedroom_desc_nl";
const TAG_BEDROOM_DESC_FR = "bedroom_desc_fr";
const TAG_BEDROOM_DESC_EN = "bedroom_desc_en";
const TAG_BEDROOM_DESC_DE = "bedroom_desc_de";
const TAG_TERRACES = "terraces";
const TAG_TERRACE_SURFACE = "terrace_surface";
const TAG_TERRACE_FLOOR = "terrace_floor";
const TAG_TERRACE_DESC_NL = "terrace_desc_nl";
const TAG_TERRACE_DESC_FR = "terrace_desc_fr";
const TAG_TERRACE_DESC_EN = "terrace_desc_en";
const TAG_TERRACE_DESC_DE = "terrace_desc_de";
const TAG_WINTERGARDENS = "wintergardens";
const TAG_WINTERGARDEN_SURFACE = "wintergarden_surface";
const TAG_WINTERGARDEN_FLOOR = "wintergarden_floor";
const TAG_WINTERGARDEN_DESC_NL = "wintergarden_desc_nl";
const TAG_WINTERGARDEN_DESC_FR = "wintergarden_desc_fr";
const TAG_WINTERGARDEN_DESC_EN = "wintergarden_desc_en";
const TAG_WINTERGARDEN_DESC_DE = "wintergarden_desc_de";
const TAG_WARDROBES = "wardrobes";
const TAG_WARDROBE_SURFACE = "wardrobe_surface";
const TAG_WARDROBE_FLOOR = "wardrobe_floor";
const TAG_WARDROBE_DESC_NL = "wardrobe_desc_nl";
const TAG_WARDROBE_DESC_FR = "wardrobe_desc_fr";
const TAG_WARDROBE_DESC_EN = "wardrobe_desc_en";
const TAG_WARDROBE_DESC_DE = "wardrobe_desc_de";
const TAG_FREE_PROFESSION_SURFACE = "free_profession_surface";
const TAG_FREE_PROFESSIONS = "free_professions";
const TAG_FREE_PROFESSION_FLOOR = "free_profession_floor";
const TAG_FREE_PROFESSION_DESC_NL = "free_profession_desc_nl";
const TAG_FREE_PROFESSION_DESC_FR = "free_profession_desc_fr";
const TAG_FREE_PROFESSION_DESC_EN = "free_profession_desc_en";
const TAG_FREE_PROFESSION_DESC_DE = "free_profession_desc_de";
const TAG_LAUNDRY_ROOMS = "laundry_rooms";
const TAG_LAUNDRY_ROOM_SURFACE = "laundry_room_surface";
const TAG_LAUNDRY_ROOM_FLOOR = "laundry_room_floor";
const TAG_LAUNDRY_ROOM_DESC_NL = "laundry_room_desc_nl";
const TAG_LAUNDRY_ROOM_DESC_FR = "laundry_room_desc_fr";
const TAG_LAUNDRY_ROOM_DESC_EN = "laundry_room_desc_en";
const TAG_LAUNDRY_ROOM_DESC_DE = "laundry_room_desc_de";
const TAG_TOILETS = "toilets";
const TAG_TOILET_SURFACE = "toilet_surface";
const TAG_TOILET_FLOOR = "toilet_floor";
const TAG_TOILET_DESC_NL = "toilet_desc_nl";
const TAG_TOILET_DESC_FR = "toilet_desc_fr";
const TAG_TOILET_DESC_EN = "toilet_desc_en";
const TAG_TOILET_DESC_DE = "toilet_desc_de";
const TAG_SITTING_AREAS = "sitting_areas";
const TAG_SITTING_AREA_SURFACE = "sitting_area_surface";
const TAG_SITTING_AREA_FLOOR = "sitting_area_floor";
const TAG_SITTING_AREA_DESC_NL = "sitting_area_desc_nl";
const TAG_SITTING_AREA_DESC_FR = "sitting_area_desc_fr";
const TAG_SITTING_AREA_DESC_EN = "sitting_area_desc_en";
const TAG_SITTING_AREA_DESC_DE = "sitting_area_desc_de";
const TAG_ATTICS = "attics";
const TAG_ATTIC_SURFACE = "attic_surface";
const TAG_ATTIC_FLOOR = "attic_floor";
const TAG_ATTIC_DESC_NL = "attic_desc_nl";
const TAG_ATTIC_DESC_FR = "attic_desc_fr";
const TAG_ATTIC_DESC_EN = "attic_desc_en";
const TAG_ATTIC_DESC_DE = "attic_desc_de";
const TAG_KITCHEN_TYPE_NL = "kitchen_type_nl";
const TAG_KITCHEN_TYPE_FR = "kitchen_type_fr";
const TAG_KITCHEN_TYPE_EN = "kitchen_type_en";
const TAG_KITCHEN_TYPE_DE = "kitchen_type_de";
const TAG_HEATING_NL = "heating_nl";
const TAG_HEATING_FR = "heating_fr";
const TAG_HEATING_EN = "heating_en";
const TAG_HEATING_DE = "heating_de";
const TAG_BOILER = "boiler";
const TAG_CONTENT_TANK_DOMESTIC_FUEL_OIL = "content_tank_domestic_fuel_oil";
const TAG_FLOOR_HEATING = "floor_heating";
const TAG_CENTRAL_HEATING = "central_heating";
const TAG_SOLAR_PANELS = "solar_panels";
const TAG_SOLAR_BOILER = "solar_boiler";
const TAG_HEAT_PUMP = "heat_pump";
const TAG_WINDMILL = "windmill";
const TAG_FLOOR_MATERIAL = "floor_material";
const TAG_SCHUTTERS = "schutters";
const TAG_SUN_BLINDS = "sun_blinds";
const TAG_DOUBLE_GLAZING = "double_glazing";
const TAG_PARLOPHONE = "parlophone";
const TAG_VIDEOPHONE = "videophone";
const TAG_ALARM = "alarm";
const TAG_SECOND_KITCHEN = "second_kitchen";
const TAG_DISTRIBUTION = "distribution";
const TAG_DRESSING = "dressing";
const TAG_SWIMMINGPOOL = "swimmingpool";
const TAG_TENNIS_COURT = "tennis_court";
const TAG_FURNISHED = "furnished";
const TAG_LIFT = "lift";
const TAG_HOUSEKEEPER = "housekeeper";
const TAG_SECURITY_DOOR = "security_door";
const TAG_ACCESS_SEMIVALID = "access_semivalid";
const TAG_DOMESTIC_ANIMALS_ALLOWED = "domestic_animals_allowed";
const TAG_METER_FOR_ELECTRICITY = "meter_for_electricity";
const TAG_CONNECTION_TO_SEWER = "connection_to_sewer";
const TAG_SEPTIC_TANK = "septic_tank";
const TAG_GAS_CONNECTION = "gas_connection";
const TAG_METER_FOR_GAS = "meter_for_gas";
const TAG_TELEPHONE_CONNECTION = "telephone_connection";
const TAG_INTERNET_CONNECTION = "internet_connection";
const TAG_CONNECTION_TO_WATER = "connection_to_water";
const TAG_METER_FOR_WATER = "meter_for_water";
const TAG_WATER_SOFTENER = "water_softener";
const TAG_WELL = "well";
const TAG_CONTENT_WELL = "content_well";
const TAG_GARDEN_AVAILABLE = "garden_available";
const TAG_GARDEN_ORIENTATION = "garden_orientation";
const TAG_GARDEN_DESC_NL = "garden_desc_nl";
const TAG_GARDEN_DESC_FR = "garden_desc_fr";
const TAG_GARDEN_DESC_EN = "garden_desc_en";
const TAG_GARDEN_DESC_DE = "garden_desc_de";
const TAG_SHOWERS_TOTAL = "showers_total";
const TAG_OPEN_FIRE = "open_fire";
const TAG_GARAGES_TOTAL = "garages_total";
const TAG_CARPORTS_TOTAL = "carports_total";
const TAG_PARKINGS_TOTAL = "parkings_total";
const TAG_DISTANCE_PUBLIC_TRANSPORT = "distance_public_transport";
const TAG_DISTANCE_SHOPS = "distance_shops";
const TAG_DISTANCE_SCHOOL = "distance_school";
const TAG_DISTANCE_BEACH = "distance_beach";
const TAG_SEAVIEW = "seaview";
const TAG_SIDE_SEAVIEW = "side_sea_view";
const TAG_READY_TO_MOVE_IN = "ready_to_move_in";
const TAG_FREE_FROM_DATE = "free_from_date";
const TAG_MINIMUM_STAY = "minimum_stay";
const TAG_BEDS = "beds";
const TAG_PREFERED_GENDER = "prefered_gender";
const TAG_PREFERED_SMOKER = "prefered_smoker";
const TAG_IS_NOTARY = "is_notAry";
const TAG_IS_ANNUITY = "is_annuity";
const TAG_IS_PUBLIC = "is_public";
const TAG_PLAIN_TEXT_ALL_NL = "plain_text_all_nl";
const TAG_PLAIN_TEXT_ALL_FR = "plain_text_all_fr";
const TAG_PLAIN_TEXT_ALL_EN = "plain_text_all_en";
const TAG_PLAIN_TEXT_ALL_DE = "plain_text_all_de";
const TAG_UNMATCHED_VARIABLES = "unmatched_variables";
const TAG_IGNORED_VARIABLES = "ignored_variables";
const TAG_VARIABLE_LABEL = "variable_label";
const TAG_VARIABLE_VALUE  = "variable_value";


// return type constants used in PageParser->extract methods.
const RETURN_TYPE_TEXT           = "text";
const RETURN_TYPE_TEXT_ALL       = "text_all";
const RETURN_TYPE_NUMBER         = "number";
const RETURN_TYPE_ARRAY          = "array";
const RETURN_TYPE_EPC            = "epc";
const RETURN_TYPE_YEAR           = "year";
const RETURN_TYPE_UNIX_TIMESTAMP = "unix_timestamp";

const XPATH_QUERY_TEMPLATE = "{template}";

define('CRAWLER_CLASS_DIR',        dirname(__FILE__).'/');
define('SCRIPT_DIR',               getcwd().'/');

define('OUTPUT_FILE_NAME',         SCRIPT_DIR . "output.xml");
define('OUTPUT_PROPERTY_FILE_NAME',SCRIPT_DIR . "properties.xml");
define('OUTPUT_PROJECT_FILE_NAME', SCRIPT_DIR . "projects.xml");
define('OUTPUT_OFFICE_FILE_NAME',  SCRIPT_DIR . "offices.xml");
define('OUTPUT_EMPLOYEE_FILE_NAME',  SCRIPT_DIR . "employees.xml");


/* ============================= CONFIG ============================= */
set_time_limit(14400);//4hours limit, you can set 0 in crawler, if need more run time
error_reporting(E_ALL);
libxml_use_internal_errors(true);
date_default_timezone_set("Europe/Brussels");

$crawlerConfig = array
(
"proxy"             => isset($_GET['proxy'])  ? $_GET['proxy']  : "",
"proxy_type"        => isset($_GET['p_type']) ? $_GET['p_type'] : "",
"proxy_pwd"         => isset($_GET['auth'])   ? $_GET['auth']   : ""
);

$crawler = new Crawler($crawlerConfig);

/* ============================= END CONFIG ========================== */