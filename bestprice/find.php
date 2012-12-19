<?php
require_once 'Parsing.php';
if(isset($_REQUEST['q'])){
	$query = urlencode($_REQUEST['q']);
	$query2 = urldecode($_REQUEST['q']);


	$cat = false;
	if(isset($_REQUEST['cat'])){
		$cat = $_REQUEST['cat'];
	}

	$parsing = new Parsing();
	$data = array();
	$ajaxParseSite = array();
	$errorSites = array();
	$emptySites = array();
	$delay = false;
	$sites = $parsing->getWebsites();

	if(isset($_REQUEST['site'])){
		$delay = false;
		$site = urldecode($_REQUEST['site']);
		$sites = array($site);
	}

	foreach($sites as $site){
		require_once 'Sites/'.$site.'.php';
		$siteObj = new $site;
		if($siteObj->allowCategory($cat)){
			try{
				$data1 = $siteObj->getPriceData($query,$cat,$delay);
				if(!$data1){
					$ajaxParseSite[] = $site;
				}else{

					$data2 = array();
					$count = 0;
					foreach($data1 as $row){
						$name = $row['name'];
						$row['levenshtein'] = levenshtein(strtolower($name), strtolower($query2));
						$row['lendiff'] = abs( strlen($name) - strlen($query2) );
						$row['levenshtein_score'] = $row['levenshtein'] - $row['lendiff'];
						$per = 0;
						$row['similar_text'] = similar_text(strtolower($name), strtolower($query2),$per);
						$row['similar_text_per'] = number_format($per,2);
						$row['query'] = $query2;
							
						$row['logo'] = $siteObj->getLogo();
							
						$data2[] = $row;
						$count++;
						if($count > 10){
							continue;
						}
					}
					$data1 = array();
					uasort($data2, 'priceSort');

					if(empty($data)){
						$data = $data2;
					}else{
						if(empty($data2)){
							$emptySites[] = array('site'=>$site);
						}else{
							$data = array_merge($data,$data2);
						}
					}
				}
			}catch(Exception $e){
				$errorSites[] = array('site'=>$site,'message'=>$e->getMessage());
			}
		}
	}
	$return = array('ajax_parse'=>$ajaxParseSite,'data'=>$data,'error_sites'=>$errorSites,'empty_sites'=>$emptySites);
	echo json_encode($return);
}
function priceSort($a,$b){
	if ($a['disc_price'] == $b['disc_price']) {
		return 0;
	}
	return ($a['disc_price'] < $b['disc_price']) ? -1 : 1;
}
?>