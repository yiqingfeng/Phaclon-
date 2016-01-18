# 自动加载(phalcon\Loader)
----------
转载请注明来源：[http://www.cnblogs.com/mengxuan/p/5137881.html](http://www.cnblogs.com/mengxuan/p/5137881.html)

## 问题引入
**我们为什么要进行自动加载？**

## 一、php文件引入
通过 include() 或 require() 函数，可以在PHP程序执行之前在该文件中插入一个文件的内容。

> 区别：处理错误的方式不同。**include() 函数**会生成一个**警告**（但是脚本会继续执行），而 **require()** 函数会生成一个**致命错误**（fatal error）（在错误发生后脚本会停止执行）

**正因为在文件不存在或被重命名后脚本不会继续执行，因此我们推荐使用 require() 而不是 include()。**

## 二、php类自动加载
参考文章：
[php手册](http://php.net/manual/zh/function.spl-autoload-register.php)
[PHP的类自动加载机制](http://blog.csdn.net/hguisu/article/details/7463333)

在php5之前，各php框架实现类的加载，一般要按照某种约定实现一个遍历目录，自动加载符合约定条件的文件类或函数。因此在php5之前类的使用并没有现在频繁。

在php5之后，当加载php类的时候，如果类所在文件夹并没有被包含进来或是类名出错时，Zend引擎会自动调用**__autoload函数**。__autoload函数需要用户自己实现。

在php5.1.2版本之后，可以使用**spl_autoload_register函数**自定义加载处理函数。当没有调用此函数，默认情况下会使用spl自定义的spl_autoload函数。

### php自动加载之__autoload

	function __autoload($className) {
		$file = $className . '.php';
		if (is_file($file)) {
			require($file);
		}else{
			echo 'no this ' . $className . ' class file';
		}
	}
	$demo = new Demo();

事实上，我们可以看到`__autoload`至少需要做三件事(“三步走”)，它们分别是：

1. 根据类名确定类的文件名。
2. 确定类文件所在路径，上例用的是中用的是相对定位，我们的测试文件其实在同一目录下。
3. 将指定类所在文件加载到程序中。

在第一步和第二步中，我们必须约定类名与文件的**映射方法**，只有这样我们才能够依据类名找到其所对应的文件，实现加载。

因此__autoload自动加载中，最重要的就是**指定类名与其所在文件的对应关系**。当有大量的类需要包含进来的时候，我们只需要确立相应的规则，然后将类名与其对应的文件进行映射，就能够实现惰性加载(lazy loading)了。

> **Tip：**spl_autoload_register() 提供了一种更加灵活的方式来实现类的自动加载。因此，不再建议使用 __autoload() 函数，在以后的版本中它可能被弃用。 

### php自动加载之spl_autoload_register
引言：如果在一个php系统实现中，使用了很多的其他类库，这些类库可能是由不同的工程师进行开发的，因此类名与其所在文件的映射规则不尽相同。这时候如果要实现类库的自动加载，就必须在\__autoload函数中将所有的映射规则全部实现。这就会导致\__autoload会非常复杂，甚至无法实现。同时还会使得\__autoload函数十分臃肿。为将来系统的维护和性能带来很大的负面影响。

#### spl_autoload_register:

注册给定的函数作为\__autoload的实现。简单来说就是将函数注册之SPL的\__autoload函数栈中，并移除系统默认的\__autload()函数。

	function __autoload($className) {  
	    echo 'autload class:', $className, '<br />';  
	}  
	function classLoader($className) {  
	    echo 'SPL load class:', $className, '<br />';  
	}  
	spl_autoload_register('classLoader');  
	new Test();//结果：SPL load class:Test 

> **Tip：**
> 1. 如果在你的程序中已经实现了\__autoload()函数，它必须**显式注册**到\__autoload()队列中。因为 spl_autoload_register()函数会将Zend Engine中的\__autoload()函数取代为spl_autoload()或spl_autoload_call()。
> 2. 相比于\__autoload只能够定义一次。spl_autoload_register()函数可以**定义多个autoload函数**。因为spl_autoload_register创建了autoload函数队列，该队列按照定义的先后顺序逐个执行。
>
		function __autoload($className) {  
		    echo 'autload class:' . $className . '<br />';  
		}  
		function classLoader($className) {  
		    echo 'SPL load class:' . $className . '<br />';  
		}  
		spl_autoload_register('classLoader');  
		$demo = new Demo();//结果：SPL load class:Demo

#### 函数说明
    bool spl_autoload_register ([ callable $autoload_function [, bool $throw = true [, bool $prepend = false ]]] )

-  autoload_function【可选】添加到自动加载栈的函数。默认为spl_autoload()。
	-  还可以调用spl_autoload_register()函数以注册一个回调函数,而不是为函数提供一个字符串名称。如提供一个如array('class','method')这样的数组,使得可以**使用某个对象的方法**。
-  throw【可选】无法成功注册时，是否抛出异常
-  prepend【可选】是否将将该函数添加到队列之首，而不是队列的尾部。

> **备注：**SPL自动加载功能是由spl_autoload() ,spl_autoload_register(), spl_autoload_functions() ,spl_autoload_extensions()和spl_autoload_call()函数提供的。

## 三、Phalcon的类自动加载

Phalcon\Loader 通用类加载器(Universal Class Loader),意在根据协议帮助项目自动加载项目中的类(This component helps to load your project classes automatically based on some conventions)。Phalcon支持四种类加载方式，先后顺序分别是注册类名、注册命名空间、注册前缀和注册文件夹的方式。当然Phalcon支持多种方式混合。

Phalcon的默认文件后缀为php，当然你自己也可以配置(setExtensions())。

### 注册类名

	<?php
	$loader = new \Phalcon\Loader();
	$loader->registerClasses(
	    array(
	        "Some"         => "library/OtherComponent/Other/Some.php",
	        "Example\Base" => "vendor/example/adapters/Example/BaseClass.php",
	    )
	);
	$loader->register();	
	// i.e. library/OtherComponent/Other/Some.php
	$some = new Some();

1. 最快的自动方法
2. 不利于维护

> 具体实现：
> 1. 判断是否有类被注册。
> 2. 判断需要加载的类是否被注册，如果已注册则加载其对应路径文件。

### 注册命名空间
	<?php
	$loader = new \Phalcon\Loader();
	$loader->registerNamespaces(
	    array(
	       "Example\Base"    => "vendor/example/base/",
	       "Example\Adapter" => "vendor/example/adapter/",
	       "Example"         => "vendor/example/",
	    )
	);
	$loader->register();
	// vendor/example/adapter/Some.php
	$some = new Example\Adapter\Some();
使用命名空间或外部库组织代码时，你可以利用注册命名空间的方式来自动加载其包含的库。
> 对于命名空间对应的路径，要其末尾加一个斜杠。

> 具体实现：
> 1. 判断是否有命名空间被注册。
> 2. 判断需要加载的类是否已以注册的命名开始。
>>  例如注册的命名空间为`"Example\Base"    => "vendor/example/base/"`
> 
		"Example\Base"    => "vendor/example/base/"
		$test1 = new Example\Base\Test();// vendor/example/base/Test.php
		$test2 = new Example\Test();// 错误，无法加载。
> 名称处理：1、去掉命名指定空间前缀。2、将命名空间分隔符`\`转换成文件分隔符`/`
> 3. 依据文件拓展名构建完整的文件路径，并判断该文件是否存在，如该文件存在加载。

### 注册前缀
	<?php
	$loader = new \Phalcon\Loader();
	$loader->registerPrefixes(
	    array(
	       "Example_Base"     => "vendor/example/base/",
	       "Example_Adapter"  => "vendor/example/adapter/",
	       "Example_"         => "vendor/example/",
	    )
	);	
	$loader->register();	
	// vendor/example/adapter/Some.php
	$some = new Example_Adapter_Some();
类似于命名空间，从2.1.0开始phalcon将不再支持前缀。

> 具体实现：
> 1. 判断是否有前缀被注册。
> 2. 判断需要加载的类是否已以前缀开始命名。
>>  例如注册的前缀为`"Example_Base"    => "vendor/example/base/"`
> 
		"Example_Base"    => "vendor/example/base/"
		$test1 = new Example_Base_Test();// vendor/example/base/Test.php
		$test2 = new Example_Test();// 错误，无法加载。
> 名称处理：1、去掉类的前缀。2、将前缀分隔符`_`转换成文件分隔符`/`
> 3. 依据文件拓展名构建完整的文件路径，并判断该文件是否存在，如该文件存在加载。

### 注册文件夹
	<?php
	$loader = new \Phalcon\Loader();	
	$loader->registerDirs(
	    array(
	        "library/MyComponent/",
	        "library/OtherComponent/Other/",
	        "vendor/example/adapters/",
	        "vendor/example/"
	    )
	);	
	$loader->register();	
	// i.e. library/OtherComponent/Other/Some.php
	$some = new Some();
可以自动加载注册目录下的类文件。但是该方法在性能方面并不被推荐，因为Phalcon将在个文件夹下大量查找与类名相同的文件。在使用注册目录自动加载时，要注意注册目录的相关性，即将重要的目录放在前面。

> 具体实现：
> 1. 将类名中的前缀分隔符`_`或是命名空间分隔符`\`替换成文件夹分割符`/`
> 2. 判断是否有文件夹被注册。
> 3. 依据文件后缀构建可能的文件路径
>>  例如注册的前缀为`"vendor/example/base/"`
> 
		$test = new Test();// vendor/example/base/Test.php

### 修改当前策略(Modifying current strategies)
即为当前自动加载数据添加额外的值。

	<?php
	// Adding more directories
	$loader->registerDirs(
    	array(
        	"../app/library/",
        	"../app/plugins/"
    	),
    	true
	);

注册时添加第二个参数值true，使其与原数组合并。

### 安全层（Security Layer）
没有进行任何安全检查的自动加载器，如下：

	<?php

	//Basic autoloader
	spl_autoload_register(function($className) {
    	if (file_exists($className . '.php')) {
        	require $className . '.php';
    	}
	});

假如我们没有进行任何安全检查时，如果误启了自动加载器，那么恶意准备的字符串就回作为参数访问程序中的重要文件。

	<?php

	//This variable is not filtered and comes from an insecure source
	$className = '../processes/important-process';
	
	//Check if the class exists triggering the auto-loader
	if (class_exists($className)) {
	    //...
	}

Phalcon的做法是删除任何无用的字符串，减少被攻击的可能性。

### 自动加载事件
在下面的例子中,而不必使用类加载器,使我们获得调试信息的流程操作:

	<?php
	$eventsManager = new \Phalcon\Events\Manager();
	$loader = new \Phalcon\Loader();
	$loader->registerNamespaces(array(
   		'Example\\Base' => 'vendor/example/base/',
   		'Example\\Adapter' => 'vendor/example/adapter/',
   		'Example' => 'vendor/example/'
	));
	//Listen all the loader events
	$eventsManager->attach('loader', function($event, $loader) {
    	if ($event->getType() == 'beforeCheckPath') {
        	echo $loader->getCheckedPath();
    	}
	});
	$loader->setEventsManager($eventsManager);
	$loader->register();

Phalcon自动加载支持以下事件:
- beforeCheckClass,自动加载的过程开始前触发,当返回布尔假可以停止活动操作。
- pathFound，当一个类装入器定位触发
- afterCheckClass，自动加载的过程完成后触发。

### 注意事项（Troubleshooting）

1. 自动加载区分大小写。
2. 命名空间或前缀的方式要比文件夹的方式要快得多。
