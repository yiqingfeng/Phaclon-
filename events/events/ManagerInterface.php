<?php

namespace Events;

interface ManagerInterface
{
	# 将一个事件监听器绑定到一个事件上
	public function attach($eventType, $handler);
	# 将一个事件监听器从某一个事件上解绑
	public function detach($eventType, $handler);
	# 移除所有的事件或是某事件上的所有侦听者
	public function detachAll($type);
	# 获取某一事件的侦听者
	public function getListeners($type);
	# 触发事件管理器中一个事件，并将其通知到其监听者
	public function fire($eventType, $source, $data);
}