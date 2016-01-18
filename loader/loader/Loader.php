<?php

class Loader
{
	protected $_extensions;// 文件后缀名
	protected $_classes     = null;// 类名映射
	protected $_register    = false;// 注册自动加载
	protected $_namespaces  = null;// 命名空间映射
	protected $_prefixes    = null;// 前缀映射
	protected $_directories = null;// 文件夹映射

	public function __construct(){
		$this->_extensions = array('php');
	}
	public static function startWith($str, $start){
		return $start === '' || strpos($str, $start) === 0;
	}
	public function setExtensions($extensions){
		if (is_array($extensions)){
			$this->_extensions = $extensions;
		}
		return $this;
	}
	public function getExtensions(){
		return $this->_extensions;
	}
	# 注册类名及其所在位置
	# 该函数返回-1时，表示参数类型错误
	public function registerClasses($classes, $merge=false){
		if (!is_array($classes)) return -1;
		if ($merge) {
			$currentClasses = $this->_classes;
			if ($currentClasses) {
				$this->_classes = array_merge($currentClasses, $classes);
				return $this;
			}
		}
		$this->_classes = $classes;
		return $this;
	}
	public function getClasses(){
		return $this->_classes;
	}
	# 注册命名空间及其所在位置
	public function registerNamespaces($namespaces, $merge=false){
		if (!is_array($namespaces)) return -1;
		if ($merge) {
			$currentNamespaces = $this->_namespaces;
			if ($currentNamespaces) {
				$this->_namespaces = array_merge($currentNamespaces, $namespaces);
				return $this;
			}
		}
		$this->_namespaces = $namespaces;
		return $this;
	}
	public function getNamespaces(){
		return $this->_namespaces;
	}
	# 注册前缀及其所在位置
	public function registerPrefixes($prefixes, $merge=false){
		if (!is_array($prefixes)) return -1;
		if ($merge) {
			$currentPrefixes = $this->_prefixes;
			if ($currentPrefixes) {
				$this->_prefixes = array_merge($currentPrefixes, $prefixes);
				return $this;
			}
		}
		$this->_prefixes = $prefixes;
		return $this;
	}
	public function getPrefixes(){
		return $this->_prefixes;
	}
	# 注册文件夹所在路径
	public function registerDirs($directories, $merge=false){
		if (!is_array($directories)) return -1;
		if ($merge) {
			$currentDirs = $this->_directories;
			if ($currentDirs) {
				$this->_directories =array_merge($currentDirs, $directories);
				return this;
			}
		}
		$this->_directories = $directories;
		return $this;
	}
	public function getDirs(){
		return $this->_directories;
	}

	# 注册自动加载方法
	public function register(){
		if ($this->_register === false) {
			spl_autoload_register(array($this, 'autoload'));
			$this->_register = true;
		}
		return $this;
	}
	# 取消自动加载方法
	 
	# 自动加载
	public function autoload($className){
		// (类名检查)检查该类是否存在静态路径
		$classes = $this->_classes;
		if (is_array($classes)) {
			if (array_key_exists($className, $classes)) {
				require($classes[$className]);
				return true;
			}
		}

		$extensions = $this->_extensions;
		$ds = DIRECTORY_SEPARATOR;
		$namespaceSeparator = '\\';

		// (命名空间映射)
		$namespaces = $this->_namespaces;
		if (is_array($namespaces)) {
			foreach ($namespaces as $nsPrefix => $namespace) {
				if (self::startWith($className, $nsPrefix)) {
					$filename = substr($className, strlen($nsPrefix . $namespaceSeparator));
					$filename = str_replace($namespaceSeparator, $ds, $filename);
					if ($filename) {
						$fixedDirectory = rtrim($namespace, $ds) . $ds;
						foreach ($extensions as $extension) {
							$fillPath = $fixedDirectory . $filename . '.' . $extension;
							if (is_file($fillPath)) {
								require($fillPath);
								return true;
							}
						}
					}
				}
			}
		}

		// 前缀映射
		$prefixes = $this->_prefixes;
		if (is_array($prefixes)) {
			foreach ($prefixes as $prefix => $directory) {
				if (self::startWith($className, $prefix)) {
					// $filename = str_replace($prefix . $namespaceSeparator, '', $className);
					$filename = str_replace($prefix . '_', '', $className);
					$filename = str_replace('_', $ds, $filename);
					if ($filename) {
						$fixedDirectory = rtrim($directory, $ds) . $ds;
						foreach ($extensions as $extension) {
							$fillPath = $fixedDirectory . $filename . '.' . $extension;
							if (is_file($fillPath)) {
								require($fillPath);
								return true;
							}
						}
					}
				}
			}
		}

		// 文件夹映射
		$dsClassName = str_replace('_', $ds, $className);
		$nsClassName = str_replace($namespaceSeparator, $ds, $dsClassName);
		$directories = $this->_directories;
		if (is_array($directories)) {
			foreach ($directories as $directory) {
				$fixedDirectory = rtrim($directory, $ds) . $ds;
				foreach ($extensions as $extension) {
					$fillPath = $fixedDirectory . $nsClassName . '.' . $extension;
					if (is_file($fillPath)) {
						require($fillPath);
						return true;
					}
				}
			}
		}
		return false;
	}
}