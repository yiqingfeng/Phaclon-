<?php

# Event的作用：
# 1. 选择是否冒泡
# 2. 将与事件相关的东西集中进行管理
# 3. 将事件监听者的参数传入其中
# 3. (event,source,data)使得监听者的参数与fire一一对应

namespace Events;

class Event
{
	public $type;// 事件类型
	protected $_source;// 时间来源
	public $data;// 监听者所需要的参数
	protected $_stop = false;// 是否停止冒泡
	protected $_cancelable = true;// 事件是否能被取消，避免错误停止事件

	public function __construct($type, $source, $data, $cancelable=true){
		if (!is_string($type) || !is_bool($cancelable)) {
			throw new Exception("Invalid parameter!");
		}
		$this->type = $type;
		$this->_source = $source;
		$this->data = $data;
		$this->_cancelable = $cancelable;
	}
	public function getSource(){
		return $this->_source;
	}
	public function isCancelable(){
		return $this->_cancelable;
	}
	public function isStop(){
		return $this->_stop;
	}
	# 停止事件冒泡
	public function stop(){
		if (!$this->_cancelable) {
			throw new Exception("Trying to cancel a non-cancelable event");
		}
		$this->_stop = true;
	}
}