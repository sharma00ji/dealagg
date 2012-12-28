<?php
class MirchiMart extends Parsing{
	public $_code = 'MirchiMart';

	public function getAllowedCategory(){
		return array(Category::MOBILE,Category::MOBILE_ACC);
	}

	public function getWebsiteUrl(){
		return 'http://www.mirchimart.com/';
	}
	public function getSearchURL($query,$category = false,$subcat=false){
		$q = urldecode($query);
		$q = str_replace(" ", '+', $q);
		$query = urlencode($q);
		if($category == Category::MOBILE){
			return "http://www.mirchimart.com/chilbuli/searchUrl?selectedCategory=Mobiles&searchText=$query";
		}else if($category == Category::MOBILE_ACC){
			return "http://www.mirchimart.com/chilbuli/searchUrl?selectedCategory=MbAccess&searchText=$query";
		}
		return "http://www.mirchimart.com/chilbuli/searchUrl?selectedCategory=MMCatalog_ROOT&searchText=$query";
	}
	public function getLogo(){
		return 'http://www.mirchimart.com/image1//mmimages/mirchimart_logo.png';
	}
	public function getData($html,$query,$category,$subcat){

		$data = array();
		phpQuery::newDocumentHTML($html);
		if(sizeof(pq('.searchFilter')) > 0){
			foreach(pq('.searchFilter') as $div){
				foreach(pq($div)->children('ul')->children('li') as $div){
					$image = pq($div)->children('.img')->children('a');
					$url = pq($div)->children('.img')->children('a')->attr('href');
					$name = pq($div)->children('.txt')->children('a')->html();
					$disc_price = pq($div)->find('.txt2')->html();
					$offer = '';
					$shipping = '';
					$stock = 0;
					if(sizeof(pq($div)->children('.inStock'))){
						$stock = 1;
					}else{
						$stock = -1;
					}
					$author = '';
					$data[] = array(
							'name'=>$name,
							'image'=>$image,
							'disc_price'=>$disc_price,
							'url'=>$url,
							'website'=>$this->getCode(),
							'offer'=>$offer,
							'shipping'=>$shipping,
							'stock'=>$stock,
							'author' => $author,
							'cat' => ''
					);
				}
			}
		}
		$data2 = array();
		foreach($data as $row){
			$html = $row['image'];
			$html .= '</img>';
			phpQuery::newDocumentHTML($html);
			$img = pq('img')->attr('data-original');
			if(strpos($img, 'http') === false){
				$img = $this->getWebsiteUrl().$img;
			}
			$row['image'] = $img;
			$data2[] = $row;
		}
		$data2 = $this->cleanData($data2, $query);
		$data2 = $this->bestMatchData($data2, $query,$category,$subcat);
		return $data2;
	}
}