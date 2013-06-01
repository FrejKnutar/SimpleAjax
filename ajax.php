<?php
session_start();
class Controller {
	static $divIds = array('fisk', 'brosk');

	function __construct() {
		self::upload();
		?>
		<head>
			<meta charset="utf-8">
			<script type="text/javascript" src="./js"></script>
		</head>
		<body>
			<p>This is dynamic JavaScript loading, neat huh?.</p>
			<?php
			foreach (self::$divIds as $id) {
				?><p id="<?php echo $id;?>">Fisk</p><?php
			}
			?>
		</body>
		<?php
	}
	static function js() {
		header('Content-Type: text/javascript');
?>window.onload = function() {
	<?php
	foreach(self::$divIds as $id) {
		?>document.getElementById('<?php echo $id;?>').innerHTML = '<?php echo rand(); ?>';
	<?php
	}
	?>
};
<?php
	}
	static function test($one, $two, $three) {
		echo("\n$one $two $three \n");
	}
	static function upload() {
		if (isset($_FILES) 
			&& count($_FILES) > 0)
		{
			var_dump($_FILES);
			die;
		}
		if (isset($_SERVER['HTTP_X_FILE_NAME'])) {
			var_dump($_SERVER['HTTP_X_FILE_NAME']);
			die;
		}
	}
}
define('__WIN__', substr_count(__FILE__,"\\") > 0);
define('__ROOT__', dirname(__FILE__));
define('__HOME__','/'.str_replace($_SERVER['DOCUMENT_ROOT'],"", (__WIN__ ? str_replace("\\","/",__ROOT__) : __ROOT__)).'/');
$ajaxMethod = $_SERVER['REQUEST_METHOD'];
$ajaxData = ${"_$ajaxMethod"};

if(isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_NAME'])) {
	$filePath = str_replace($_SERVER['DOCUMENT_ROOT'],"", (__WIN__ ? str_replace("\\","/",__FILE__) : __FILE__));
	$argv = array();
	$argv[0] = $filePath;
	$substr = substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['SCRIPT_NAME']));
	if ($substr == $_SERVER['SCRIPT_NAME']) {
		$argv[1] = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
	} else {
		$argv[1] = $_SERVER['REQUEST_URI'];
	}
}

if(isset($argv)) {
	$arguments = array();
	$class = "Controller";
	$method = null;
	$temp = explode("?",$argv[1]);
	$argv[1] = $temp[0];
	if(count($temp)>1) $argv[2] = $temp[1];
	foreach(explode("/", $argv[1]) as $str) {
		if($str == "" || $str == null || strlen($str) == 0) {
			;
		} elseif($method == null) {
			$method = $str;
		} else {
			$arguments[] = $str;
		}
	}
}
if (count($ajaxData) > 0) {
	if (method_exists($class, $method)) {
		$reflectionMethod = new ReflectionMethod($class,$method);
		$parameters = array();
		$requiredParameters = $reflectionMethod->getNumberOfRequiredParameters();
		$i = 0;
		foreach($reflectionMethod->getParameters() as $param) {
			if (isset($ajaxData[$param->name])) {
				$parameters[$i] = $ajaxData[$param->name];
			} elseif($i + 1 <= $requiredParameters) {
				header("HTTP/1.0 400 Bad Request");
				die;
			}
			$i++;
		}
		if ($reflectionMethod->isStatic()) {
			call_user_func_array(array($class, $method), $parameters);
		} else {
			$object = new $class();
			call_user_func_array(array($object, $method), $parameters);
		}
		die;
	}
} elseif(class_exists($class)) {
	if ($method != null) {
		if(method_exists($class, $method) && substr_count($method,"__",0,2) == 0) {
			$reflectionMethod = new ReflectionMEthod($class, $method);
			$requiredParameters = $reflectionMethod->getNumberOfRequiredParameters();
			$parameters = $reflectionMethod->getNumberOfParameters();
			if (count($arguments) >= $requiredParameters
			&&	count($arguments) <= $parameters) {
				if ($reflectionMethod->isStatic()) {
					call_user_func_array(array($class, $method), $arguments);
				} else {
					$object = new $class();
					call_user_func_array(array($object, $method), $arguments);
				}
			} else {
				header("HTTP/1.0 400 Bad Request");
			}
		} else {
			header("HTTP/1.0 404 Not Found");
		}
	} else {
		$object = new $class();
	}
} else {
	header("HTTP/1.0 404 Not Found");
}
?>