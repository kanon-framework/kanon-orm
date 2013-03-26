<?php
#require_once dirname(__FILE__).'/integerProperty.php';
class timestampProperty extends integerProperty{
	protected $_dataSize = 10;
	protected $_dataUnsigned = true;
	/**
	 * @return string Human presentation
	 */
	public function format($format = "d.m.Y H:i:s"){
		return date($format, $this->getValue());
	}
	public function chanFormat(){//Вск 06 Дек 2009
		$ts = $this->getValue();
		$wa = array('Вск','Пнд','Втр','Срд','Чтв','Птн','Сбт');
		$ma = array(null,'Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек');
		return $wa[date("w", $ts)].' '.date("d", $ts).' '.$ma[date("n", $ts)].' '.date("Y H:i:s", $ts);
	}
	public function vkFormat(){//2 фев 2010 в 20:06
		$ts = $this->getValue();
		$wa = array('Вск','Пнд','Втр','Срд','Чтв','Птн','Сбт');
		$ma = array(null,'Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек');
		//$wa[date("w", $ts)].' '.
		return date("j", $ts).' '.mb_strtolower($ma[date("n", $ts)],'utf8').' '.date("Y в H:i", $ts);
	}
	public function html5(){ // html5 <time datetime="">
		// P - Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)  	Example: +02:00
		// c - ISO 8601 date Example: 2004-02-12T15:19:21+00:00
		return date("c", $this->getValue());
		return date("Y-m-d").'T'.date("H:i:sP");
	}
        public function timeHtml(){
            return '<time datetime="'.$this->html5().'">'.$this->vkFormat().'</time>';
        }
	public function html($format = "d.m.Y H:i:s"){
		return $this->format($format);
	}
}