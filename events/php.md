# php新知识
----------

1. 匿名函数在php中是对象。（1/20/2016 9:40 :35 PM） 

		function a($hander){
		    var_dump(is_object($hander));
		}
		a(function(){
		    echo '0';
		});
		class B{}
		a(new B());
		a('asdas');

2. php现在支持数组字面量了，即`[]`可以替代`array()`。
		$a = [];
		$a[] = 21;
		$a[] = 'asd';
		var_dump($a);

3. 空数组的布尔值为false。1/21/2016 1:38 :09 PM 
4. 对象不等于对象。(function(){} !== function(){})，除非双方指向同一个对象。
5. php数组中删除指定元素的方法，Phalcon（现获取该元素所对应的key值，array_search($value, $arr, true);然后unset掉该元素）
6. 字符串查询—— int strpos ( string $haystack , mixed $needle [, int $offset = 0])，$haystactk被查询的字符串，$needle查询者，**不存在则返回false**。
7. 将字符串分割成数组，array explode(string $separator, string $string [,int $limit])
8. php中函数的参数数量可以多余指定参数数量，但是不得少于未赋默认值的参数数量。
9. PHP中检查类是否存在某方法，bool method_exists(object $object , string $method_name)。

10. php函数中可以带上参数所属对象。1/22/2016 1:18 :31 PM 
11. 一个类所在的文件不能`require`两次，否则会视为同一个类被定义两次。

4. 待定。。。。。。。。