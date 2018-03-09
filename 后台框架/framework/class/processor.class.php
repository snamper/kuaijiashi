<?php

defined('IN_IA') or exit('Access Denied');


abstract class Processor {
	
	public $message;
	public $rule_reply;
	
	
	abstract function respText($content);
	
	abstract function respImage($media_id);
	
	abstract function respVoice($media_id);
	
	abstract function respVideo(array $video);
	
	abstract function respMusic(array $music);
	
	abstract function respNews(array $news);
	
	abstract function respPosition(array $position);
	
	abstract function respCustomer();
}

class WeModuleProcessor extends Processor {
	
	private $processor = NULL;
	
	public function __construct($platform = ''){
		if (empty($platform)) {
			$platform = strtolower($_GET['platform']);
		}
		if ($platform == 'weibo') {
			load()->classs('weibo.processor');
			$this->processor = new WeiboProcessor();
		} else {
			load()->classs('wechat.processor');
			$this->processor = new WechatProcessor();
		}
	}
	
	private function getProcessor(){
		$this->processor->message = $this->message;
		$this->processor->rule_reply = $this->rule_reply;
		return $this->processor;
	}
	
	public function respText($content){
		return $this->getProcessor()->respText($content);
	}
	
	public function respImage($media_id){
		return $this->getProcessor()->respImage($media_id);
	}
	
	public function respVoice($media_id){
		return $this->getProcessor()->respVoice($media_id);
	}
	
	public function respVideo(array $video){
		return $this->getProcessor()->respVideo($video);
	}
	
	public function respMusic(array $music){
		return $this->getProcessor()->respMusic($music);
	}
	
	public function respNews(array $news){
		return $this->getProcessor()->respNews($news);
	}
	
	public function respPosition(array $position){
		return $this->getProcessor()->respPosition($position);
	}
	
	public function respCustomer(){
		return $this->getProcessor()->respCustomer();
	}
	
	public function respond(){
		return '';
	}
	
	private $inContext;
	
	public function setInContext($inContext){
		$this->inContext = $inContext;
	}
	public function getInContext(){
		return $this->inContext;
	}
	
	public function beginContext($expire = 1800) {
		if($this->getInContext()) {
			return true;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
	
		$_SESSION['__contextmodule'] = $this->rule_reply['type'];
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;
	
		$this->setInContext(true);
	
		return true;
	}
	
	public function refreshContext($expire = 1800) {
		if(!$this->inContext) {
			return false;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;
	
		return true;
	}
	
	public function endContext() {
		unset($_SESSION['__contextmodule'], $_SESSION['__contextexpire'] ,$_SESSION);
		session_destroy();
	}
}


function processor_create($module, $platform = ''){
	$errmsg = 'get processor error : ';
	if (empty($module)) {
		return error(1, $errmsg.'params input error');
	}
	$file = IA_ROOT."/framework/builtin/{$module}/processor.php";
	if (!file_exists($file)) {
		$file = IA_ROOT."/addons/{$module}/processor.php";
	}
	if (!file_exists($file)) {
		return error(2, $errmsg."\"{$module}/processor.php\" file not exists");
	}
	@require_once $file;
	$class_name = ucfirst($module).'Processor';
	if (!class_exists($class_name)) {
		return error(3, $errmsg."\"{$class_name}Processor\" class not exists");
	}
	
	$processor = new $class_name($platform);
	if ($processor instanceof WeModuleProcessor) {
		return $processor;
	} else {
		return error(4, $errmsg."\"{$class_name}Processor\" class not extends WeModuleProcessor");
	}
}
