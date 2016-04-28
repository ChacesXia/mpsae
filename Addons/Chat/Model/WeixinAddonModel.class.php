<?php

namespace Addons\Chat\Model;

use Home\Model\WeixinModel;

class WeixinAddonModel extends WeixinModel {
	var $config = array ();
	function reply($dataArr, $keywordArr = array()) {
        
        
        
        
		$this->config = getAddonConfig ( 'Chat' ); // 获取后台插件的配置参数
		
        //$content = $this->_tuling ( $dataArr ['Content'] );
        
        $content=$dataArr['Content'];
		
		
        if(preg_match("/[\x7f-\xff]/", $content)){//有中文的情况
				if (mb_strlen($content)<=2){
					$content=$this->dictionary_2en($content, $fromstr="", $tostr="");
				}else {
					$content=$this->translate_2en($content, $fromstr, $tostr);
				}
			}else {//英文的情况
				if(!preg_match("/\s/", $content)){
					$content=$this->dictionary_2zh($content, $fromstr, $tostr);
				}else {
					$content=$this->translate_2zh($content, $fromstr, $tostr);
				}
			}
        
        
		// TODO 此处可继续增加其它API接口
		
		$this->replyText($content);
		
	
	
		// 最后只能随机回复了
		if (empty ( $content )) {
			$content = $this->_rand ();
		}
		
		// 增加积分,每隔5分钟才加一次，5分钟内只记一次积分
		add_credit ( 'chat', 300 );
		
		$res = $this->replyText ( $content );
		return $res;
	}
	
	// 随机回复
	private function _rand() {
		$this->config ['rand_reply'] = array_map ( 'trim', explode ( "\n", $this->config ['rand_reply'] ) );
		$key = array_rand ( $this->config ['rand_reply'] );
		
		return $this->config ['rand_reply'] [$key];
	}
	
	// 小黄鸡
	private function _simsim($keyword) {
		$api_url = $this->config ['simsim_url'] . "?key=" . $this->config ['simsim_key'] . "&lc=ch&ft=0.0&text=" . $keyword;
		
		$result = file_get_contents ( $api_url );
		$result = json_decode ( $result, true );
		
		return $result ['response'];
	}
	
	// 小九机器人
	private function _xiaojo($keyword) {
		$curlPost ['chat'] = $keyword;
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $this->config ['i9_url'] );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $curlPost );
		$data = curl_exec ( $ch );
		curl_close ( $ch );
		
		return $data;
	}
	
	// 图灵机器人
	private function _tuling($keyword) {
		$api_url = $this->config ['tuling_url'] . "?key=" . $this->config ['tuling_key'] . "&info=" . $keyword;
		
		$result = file_get_contents ( $api_url );
		$result = json_decode ( $result, true );
		if ($_GET ['format'] == 'test') {
			dump ( '图灵机器人结果：' );
			dump ( $result );
		}
		if ($result ['code'] > 40000) {
			if ($result ['code'] < 40008 && ! empty ( $result ['text'] )) {
				$this->replyText ( '图灵机器人请你注意：' . $result ['text'] );
			} else {
				return false;
			}
		}
		switch ($result ['code']) {
			case '200000' :
				$text = $result ['text'] . ',<a href="' . $result ['url'] . '">点击进入</a>';
				$this->replyText ( $text );
				break;
			case '200000' :
				$text = $result ['text'] . ',<a href="' . $result ['url'] . '">点击进入</a>';
				$this->replyText ( $text );
				break;
			case '301000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => $info ['author'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '302000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['article'],
							'Description' => $info ['source'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '304000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => $info ['count'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '305000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['start'] . '--' . $info ['terminal'],
							'Description' => $info ['starttime'] . '--' . $info ['endtime'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '306000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['flight'] . '--' . $info ['route'],
							'Description' => $info ['starttime'] . '--' . $info ['endtime'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '307000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => $info ['info'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '308000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => $info ['info'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '309000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => '价格 : ' . $info ['price'] . ' 满意度 : ' . $info ['satisfaction'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '310000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['number'],
							'Description' => $info ['info'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '311000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => '价格 : ' . $info ['price'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			case '312000' :
				foreach ( $result ['list'] as $info ) {
					$articles [] = array (
							'Title' => $info ['name'],
							'Description' => '价格 : ' . $info ['price'],
							'PicUrl' => $info ['icon'],
							'Url' => $info ['detailurl'] 
					);
				}
				$this->replyNews ( $articles );
				break;
			default :
				if (empty ( $result ['text'] )) {
					return false;
				} else {
					$this->replyText ( $result ['text'] );
				}
		}
		
		return true;
	}
	
	private function translate_2en($query,$fromstr,$tostr) {
		$encode_query = urlencode ( $query );
		$fromstr = "zh";
		$tostr = "en";
		
		$url = "http://apistore.baidu.com/microservice/translate?query=" . $encode_query . "&from=" . $fromstr . "&to=" . $tostr;
		$str = file_get_contents ( $url );
		$de_json = json_decode ( $str, true );
		print_r ( $de_json );
		
		return  $de_json ["retData"] ['trans_result'] ['0'] ['dst'];
	}
	private function translate_2zh($query,$fromstr,$tostr) {
		$encode_query = urlencode ( $query );
		$fromstr = "en";
		$tostr = "zh";
	
		$url = "http://apistore.baidu.com/microservice/translate?query=" . $encode_query . "&from=" . $fromstr . "&to=" . $tostr;
		$str = file_get_contents ( $url );
		$de_json = json_decode ( $str, true );
		print_r ( $de_json );
	
		return  $de_json ["retData"] ['trans_result'] ['0'] ['dst'];
	}
	private function dictionary_2en($query,$fromstr,$tostr) {
		$encode_query = urlencode ( $query );
		$fromstr = "zh";
		$tostr = "en";
	
		$url = "http://apistore.baidu.com/microservice/dictionary?query=".$encode_query."&from=" . $fromstr . "&to=" . $tostr;
		$str = file_get_contents ( $url );
		$de_json = json_decode ( $str, true );

		
		$result ="这是词典到英文";
		for($i = 0, $size_i = sizeof ( $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ['parts'] ); $i < $size_i; $i ++) {
			$result .= $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ['parts'] [$i] ['part'];
			for($j = 0, $size_j = sizeof ( $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ['parts'] [$i] ['means'] ); $j < $size_j; $j ++) {
				$result .= $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ['parts'] [$i] ['means'] [$j] ;
			}
		}

		return  $result;
	}
	
	private function dictionary_2zh($query,$fromstr,$tostr) {
		$encode_query = urlencode ( $query );
		$fromstr = "en";
		$tostr = "zh";
	
		$url = "http://apistore.baidu.com/microservice/dictionary?query=".$encode_query."&from=" . $fromstr . "&to=" . $tostr;
		$str = file_get_contents ( $url );
		$de_json = json_decode ( $str, true );
		
		$result = "美式发音【" . $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ["ph_am"] . "】英式发音【" . $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ["ph_en"] . "】";
		for($i = 0, $size_i = sizeof ( $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ['parts'] ); $i < $size_i; $i ++) {
			$result .= $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ['parts'] [$i] ['part']." ";
			for($j = 0, $size_j = sizeof ( $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ['parts'] [$i] ['means'] ); $j < $size_j; $j ++) {
				$result .= $de_json ["retData"] ['dict_result'] ['symbols'] ['0'] ['parts'] [$i] ['means'] [$j]." " ;
			}
		}
		return  $result;
	}
}
