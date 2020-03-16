<?php
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ControllerCatalogAmazomimport extends Controller {
	private $error = array();
	private $access_key = "";
	private $secret_key = "";
	private $associate_tag = 'sonataruby-20';
	private $common_params = [];
	private $client;
	private $response_group = 'BrowseNodes,Images,ItemAttributes,OfferSummary';
	private $endpoint = 'webservices.amazon.com';
	private $locale = 'com';
	


	public function index() {
		$this->load->language('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');
		if (($this->request->server['REQUEST_METHOD'] == 'GET')) {
			if (isset($this->request->get['action']) && $this->request->get['action'] == "search") {
				$this->search(@$this->request->get['keyword']);
				exit();
			}
			if (isset($this->request->get['action']) && $this->request->get['action'] == "info") {
				$this->getData(@$this->request->get['url'],@$this->request->get['catalog'],@$this->request->get['offprice']);
				exit();
			}
		}
		$data = array();
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['user_token'] = $this->session->data['user_token'];
		//$this->feed();
		//print_r(urldecode('https://www.amazon.com/s?bbn=16225007011&rh=n%3A16225007011%2Cp_36%3A1253506011&dc&fst=as%3Aoff&qid=1584297477&rnid=386442011&ref=lp_16225007011_nr_p_36_3'));
		$this->response->setOutput($this->load->view('catalog/amazomimport', $data));
	}

	public function feed(){
		//include __DIR__."/simple_html_dom.php";
		

	}

	private function search($key){
		error_reporting(0);
		ini_set('display_errors', 0);
		$url = 'https://www.amazon.com/s?k='.$key.'&rh=n:16225007011,p_36:1253506011';
		
		$dom = new DOMDocument;
		libxml_use_internal_errors(true);
		$dom->loadHTMLFile($url);
		
		$xpath = new \DOMXpath($dom);
  		$articles = $xpath->query('//div[@class="sg-col-20-of-24 sg-col-28-of-32 sg-col-16-of-20 sg-col sg-col-32-of-36 sg-col-8-of-12 sg-col-12-of-16 sg-col-24-of-28"]/div[@class="sg-col-inner"]/span[@class="rush-component s-latency-cf-section"]/div[@class="s-result-list s-search-results sg-row"]/div[@class="sg-col-20-of-24 s-result-item sg-col-0-of-12 sg-col-28-of-32 sg-col-16-of-20 sg-col sg-col-32-of-36 sg-col-12-of-16 sg-col-24-of-28"]/div[@class="sg-col-inner"]/span/div[@class="s-include-content-margin s-border-bottom"]/div/div[@class="sg-row"]');
		$links = [];
		
		//print_r($articles->item(0));
		  foreach($articles as $container) {
		  	
			  	$childImage = $xpath->query('.//div[@class="sg-col-4-of-24 sg-col-4-of-12 sg-col-4-of-36 sg-col-4-of-28 sg-col-4-of-16 sg-col sg-col-4-of-20 sg-col-4-of-32"]/div/div/span/a/div/img[@class="s-image"]', $container);

			if($childImage->length == 1){
			  	print_r($container);
			  	$childInfo = $xpath->query('.//div[@class="sg-col-4-of-12 sg-col-8-of-16 sg-col-16-of-24 sg-col-12-of-20 sg-col-24-of-32 sg-col sg-col-28-of-36 sg-col-20-of-28"]', $container);
			}
		  	/*
		  	$child = $xpath->query('.//div[@class="sg-row"]', $container);

		  	foreach($child as $containerchild) {

			  	$child2 = $xpath->query('.//div[@class="sg-col-4-of-24 sg-col-4-of-12 sg-col-4-of-36 sg-col-4-of-28 sg-col-4-of-16 sg-col sg-col-4-of-20 sg-col-4-of-32"]/div[@class="sg-col-inner"]/div[@class="a-section a-spacing-none"]', $containerchild);

			  	

			  	foreach ($child2 as $keyChild => $valueChild) {
			  		$image = $xpath->query('.//div[@class="a-section a-spacing-none"]', $valueChild);
			  		$info = $xpath->query('.//span[@class="a-offscreen"]', $valueChild);
			  		print_r($info->item(1));
			  		$href = $valueChild->getElementsByTagName("a")->item(0);
			  		$obj = $valueChild->getElementsByTagName("img")->item(0);
			  		$name = (isset($obj->attributes) && @$obj->attributes > 0 ? @$obj->getAttribute("alt") : "");
			  		$src = (isset($obj->attributes) && @$obj->attributes > 0 ? @$obj->getAttribute("src") : "");
			  		if(trim($name) && trim($src) && $name != "Black"){
			  			list($dataUrl,$fx) = explode( '/ref',$href->getAttribute("href"));
				  		$links[] = [
					        'href' => "https://www.amazon.com".$dataUrl,
					        'name' => $name,
					        'src'	=> $src
					      ];
					}
			  		
			  	}
			  }
			*/
		    
			
		  }
		//echo $dom->saveHTML();
		header('Content-Type: application/json');
		echo json_encode($links);
		

		exit();
	}
	private function getData($site_url, $catalog="", $offprice=5) 
	{
		
		error_reporting(0);
		ini_set('display_errors', 0);
		if(!trim($site_url)) exit();
		$dom = new DOMDocument;
		libxml_use_internal_errors(true);
		$dom->loadHTMLFile($site_url);
		
		$xpath = new \DOMXpath($dom);
  		//$articles = $xpath->query('//div[@id="centerCol"]');//$dom->getElementById("centerCol");
  		$image = $xpath->query('//div[@id="altImages"]');
  		//$image = $dom->getElementById("altImages");
  		$img = [];
  		foreach ($image as $key => $value) {
  			$arr = $value->getElementsByTagName("img");

  			foreach ($arr as $keyArr => $valueArr) {
  				list($full,$mini,$ext) = explode('.', basename($valueArr->getAttribute("src")));
  				$srcImg = str_replace(basename($valueArr->getAttribute("src")), $full.".".$ext, $valueArr->getAttribute("src"));
  				if (strlen(strstr($srcImg, 'transparent-pixel')) == 0) {
	  				$img[] = $srcImg;
	  			}
  			}
  			
  		}
  		
  		$links = [];
  		$arvImg = [];
  		$thumbnail = "";
  		foreach ($img as $key => $value) {

  			$name = "products/". str_replace(['+',' ','%','-'],'_',strtolower(urldecode(basename($value))));
  			if(!file_exists(DIR_IMAGE . $name)) file_put_contents(DIR_IMAGE . $name, file_get_contents($value));
  			if($key == 0){
  				$thumbnail = $name;
  			}else{
  				$arvImg[] = [
	  				"image" => $name,
	  				"sort_order" => $key
	  			];
  			}
  		}
  		$links["name"] = trim($dom->getElementById("titleSection")->nodeValue);
  		
  		$price = number_format(str_replace('$', '', $dom->getElementById("priceblock_ourprice")->nodeValue),2);
  		$price = $price - $price*$offprice/100;
  		$links["description"] = trim($dom->getElementById("featurebullets_feature_div")->nodeValue);
  		
  		
  		//print_r($articles);
  		
		$catalog = explode('|', $catalog);
		$catalogSE = [];
		foreach ($catalog as $key => $value) {
			if($value){
				$catalogSE[] = $value;
			}
		}

  		$data = [
  			"product_description" => [

  				"1" => [
	  				"name" => $links["name"],
	  				"description" => $links["description"],
	  				"meta_title" => $links["name"],
	  				"meta_description" => "",
	  				"meta_keyword" => "",
	  				"tag" => ""
	  			]
  			],
  			"model" => "IQ-".$this->struuid(false),
  			"sku" => "",
		    "upc" => "",
		    "ean" => "",
		    "jan" => "",
		    "isbn" => "",
		    "mpn" => "",
		    "location" => "",
		    "price" => $price,
		    "tax_class_id" => "",
		    "quantity" => "100",
		    "minimum" => "1",
		    "subtract" => "1",
		    "stock_status_id" => "7",
		    "shipping" => "1",
		    "date_available" => date("Y-m-d"),
		    "length" => "",
		    "width" => "",
		    "height" => "",
		    "length_class_id" => "1",
		    "weight" => "",
		    "weight_class_id" => "1",
		    "status" => "1",
		    "sort_order" => "1",
		    "manufacturer" => "",
		    "manufacturer_id" => "",
		    "category" => "",
		    "product_category" => $catalogSE,
		    "filter" => "",
		    "product_store" => [0],

		    "download" => "",
		    "related" => "",
		    "option" => "",
		    "image" => $thumbnail,
		    "product_image" => $arvImg,
		    
		    "product_reward" => [
		    	"1" => [
		    		"points" => ""
		    	]
		    ],

		    "product_seo_url" => [],

		    "product_layout" => []
  		];
  		
  		$this->load->model('catalog/product');
  		$this->model_catalog_product->addProduct($data);
  		
	   
	    
  		
  		
	   
	    
	    header('Content-Type: application/json');
		echo json_encode($data);
		

		exit();

	}

	private function struuid($entropy)
	{
	    $s=uniqid("",$entropy);
	    $num= hexdec(str_replace(".","",(string)$s));
	    $index = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $base= strlen($index);
	    $out = '';
	        for($t = floor(log10($num) / log10($base)); $t >= 0; $t--) {
	            $a = floor($num / pow($base,$t));
	            $out = $out.substr($index,$a,1);
	            $num = $num-($a*pow($base,$t));
	        }
	    return $out;
	}
	
	


}