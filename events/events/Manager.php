<?php

// require('ManagerInterface.php');
// require('Event.php');

namespace Events;

use \SplPriorityQueue as PriorityQueue;

class Manager implements ManagerInterface
{
	protected $_events = [];// 事件数组，存储事件与监听者之间的关系
	// public $_events = [];
	protected $_enablePriorities = false;// 是否使用优先权
	protected $_enabelCollect = false;// 是否开启收集监听者的返回结果
	protected $_responses = [];// 每个事件的监听者的返回结果
	// 是否开启优先权
	public function enablePriorities($enablePriorities){
		if (!is_bool($enablePriorities)) {
			throw new Exception("Invalid parameter!", 1);// 请求参数无效
		}
		$this->_enablePriorities = $enablePriorities;
	}
	// 返回优先权的开关状态
	public function arePrioritiesEnable(){
		return $this->_enablePriorities;
	}
	public function collectResponses($collect){
		if (!is_bool($collect)) {
			throw new Exception("Invalid parameter!", 1);
		}
		$this->_enabelCollect = $collect;
	}
	public function isCollecting(){
		return $this->_enabelCollect;
	}
	// 获取监听者的响应结果
	public function getResponses(){
		if ($this->_enabelCollect) {
			throw new Exception("Trying to get a non-collect responses");
		}
		return $this->_responses;
	}
	public function attach($eventType, $handler, $priority=10){
		# 监听器只能是对象或匿名函数
		if (!is_string($eventType) || !is_object($handler)) {
			throw new Exception("Invalid parameter!", 1);// 请求参数无效
		}
		if (!isset($this->_events[$eventType])) {
			if ($this->_enablePriorities) {
				$priorityQueue = new PriorityQueue();
				$priorityQueue->setExtractFlags(PriorityQueue::EXTR_DATA);// 设置优先队列只输出数据，不输出优先权
				$this->_events[$eventType] = $priorityQueue;
			} else {
				$priorityQueue = [];
			}
		} else {
			$priorityQueue = $this->_events[$eventType];
		}
		// 插入数据
		if (is_object($priorityQueue)) {
			$priorityQueue->insert($handler, $priority);
		} else {
			$priorityQueue[] = $handler;
			$this->_events[$eventType] = $priorityQueue;
		}
	}
	public function detach($eventType, $handler){
		if (!is_string($eventType) || !is_object($handler)) {
			throw new Exception("Invalid parameter!", 1);
		}
		if (isset($this->_events[$eventType])) {
			$priorityQueue = $this->_events[$eventType];
			if (is_object($priorityQueue)) {
				# 有SplPriorityQueue无法删除元素，因此我们需要创建一个新的
				$newPriorityQueue = new PriorityQueue();
				$newPriorityQueue->setExtractFlags(PriorityQueue::EXTR_DATA);

				$priorityQueue->setExtractFlags(PriorityQueue::EXTR_BOTH);
				$priorityQueue->top();// 将优先队列的指针指向起始位置
				while ($priorityQueue->valid()) {
					$data = $priorityQueue->current();// 此时既输出了数据，有输出了优先级
					$priorityQueue->next();
					if ($data['data'] !== $handler) {
						$newPriorityQueue->insert($data['data'], $data['priority']);
					}
				}
				$this->_events[$eventType] = $newPriorityQueue;
			} else {
				$key = array_search($handler, $priorityQueue);
				if ($key !== false) {
					unset($priorityQueue[$key]);
				}
				$this->_events[$eventType] = $priorityQueue;
			}
		}
	}
	public function detachAll($type=null){
		if ($type === null) {
			$this->_events = [];
		} else {
			if (!is_string($type)) {
				throw new Exception("Invalid parameter!", 1);
			}
			if (isset($this->_events[$type])) {
				unset($this->_events[$type]);
			}
		}
	}
	public function hasListeners($type){
		return isset($this->_events[$type]);
	}
	public function getListeners($type){
		if (!is_string($type)) {
			throw new Exception("Invalid parameter!", 1);
		}
		if (isset($this->_events[$type])) {
			return $this->_events[$type];
		}
		return null;
	}
	# 处理事件队列中的侦听者
	protected final function fireQueue($queue, $event){
		if (!is_object($event) || !($event instanceof Event)) {
			throw new Exception("Invalid parameter!", 1);
		}
		$typeName = $event->type;
		$source = $event->getSource();
		$data = $event->data;
		$cancelable = $event->isCancelable();
		$arguments = [$event, $source, $data];
		if (is_array($queue)) {
			foreach ($queue as $handler) {
				if (is_object($handler)) {
					if ($handler instanceof \Closure) {
						$status = call_user_func_array($handler, $arguments);// 函数引用
 					} else {
						if (method_exists($handler, $typeName)) {
							$status = call_user_func_array([$handler, $typeName], $arguments);// 函数引用
						} else {
							$status = null;
						}
					}
					if ($this->_enabelCollect) {
						$this->_responses[] = $status;
					}
					if ($cancelable) {
						if ($event->isStop()) {
							break;
						}
					}
				}
			}
		} else {
			if (!is_object($queue) || !($queue instanceof PriorityQueue)) {
				throw new Exception("Invalid parameter!", 1);
			}
			// 队列在迭代之前进行克隆
			$iterator = clone $queue;
			$iterator->top();
			while ($iterator->valid()) {
				$handler = $iterator->current();
				$iterator->next();
				if (is_object($handler)) {
					if ($handler instanceof \Closure) {
						$status = call_user_func_array($handler, $arguments);// 函数引用
					} else {
						if (method_exists($handler, $typeName)) {
							$status = call_user_func_array([$handler, $typeName], $arguments);// 函数引用
						} else {
							$status = null;
						}
					}
					if ($this->_enabelCollect) {
						$this->_responses[] = $status;
					}
					if ($cancelable) {
						if ($event->isStop()) {
							break;
						}
					}
				}
			}
		}
	}
	public function fire($eventType, $source, $data=null, $cancelable=true){
		# 约定事件的名称必须是A:B的形式
		# 这样我们就能够创建一个公共事件A了
		if (!is_string($eventType) || !strpos($eventType, ':')) {
			throw new Exception("Invalid parameter!", 1);
		}
		$events = $this->_events;
		$eventParts = explode(':', $eventType);
		$type = $eventParts[0];
		$eventName = $eventParts[1];
		if ($this->_enabelCollect) {
			$this->_responses = [];
		}
		$event = null;
		# 公共事件
		if (isset($events[$type])) {
			$fireEvents = $events[$type];
			if (is_array($fireEvents) || is_object($fireEvents)) {
				$event = new Event($eventName, $source, $data, $cancelable);
				$this->fireQueue($fireEvents, $event);
			}
		}
		# 定点事件
		if (isset($events[$eventType])) {
			$fireEvents = $events[$eventType];
			if (is_array($fireEvents) || is_object($fireEvents)) {
				if ($event === null) {
					$event = new Event($eventName, $source, $data, $cancelable);
				}
				$this->fireQueue($fireEvents, $event);
			}
		}
		return $this->_enabelCollect ? $this->_responses : null;
	}
}