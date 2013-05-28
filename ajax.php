<?php
session_start();
class Controller {
	static $formId = "form-id";
	static $fileId = "file-id";
	static $uploadButtonId = "upload-button-id";
	static $fieldId = "other-field-id";
	static $supportId = "support-notice";
	static $progressId = "progress";
	static $resultId = "result";
	static $uploadId = "upload-status";
	static $uploadUrl = "/ajax/ajax.php";

	function __construct() {
		self::upload();
		?>
		<head>
			<meta charset="utf-8">
			<script type="text/javascript" src="/ajax/ajax.php/js"></script>
		</head>
		<body>
			<p id="support-notice">Your browser does not support Ajax uploads :-(<br/>The form will be submitted as normal.</p>

			<!-- The form starts -->
			<form action="/" method="post" enctype="multipart/form-data" id="<?php echo self::$formId;?>">

			  <!-- The file to upload -->
			  <p><input id="<?php echo self::$fileId;?>" type="file" name="our-file" />

			  <!--
			Also by default, we disable the upload button.
			If Ajax uploads are supported we'll enable it.
			-->
			  <input type="button" value="Upload" id="<?php echo self::$uploadButtonId;?>" disabled="disabled" /></p>

			  <!-- A different field, just for the sake of the example -->
			  <p><label>Some other field: <input name="other-field" type="text" id="<?php echo self::$fieldId;?>" /></label></p>

			  <!-- And finally a submit button -->
			  <p><input type="submit" value="Submit" /></p>
			  <p id="<?php echo self::$uploadId;?>"></p>
			  <p id="<?php echo self::$progressId;?>"></p>
			  <pre id="<?php echo self::$resultId;?>"></pre>
		</body>
		<?php
	}
	static function js() {
		header('Content-Type: text/javascript');
?>function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function supportAjaxUploadWithProgress() {
  return supportFileAPI() && supportAjaxUploadProgressEvents() && supportFormData();

  // Is the File API supported?
  function supportFileAPI() {
    var fi = document.createElement('INPUT');
    fi.type = 'file';
    return 'files' in fi;
  };

  // Are progress events supported?
  function supportAjaxUploadProgressEvents() {
    var xhr = new XMLHttpRequest();
    return !! (xhr && ('upload' in xhr) && ('onprogress' in xhr.upload));
  };

  // Is FormData supported?
  function supportFormData() {
    return !! window.FormData;
  }
}

function initFullFormAjaxUpload() {
  var form = document.getElementById('<?php echo self::$formId; ?>');
  form.onsubmit = function() {
    // FormData receives the whole form
    var formData = new FormData(form);

    // We send the data where the form wanted
    var action = form.getAttribute('action');

    // Code common to both variants
    sendXHRequest(formData, action);

    // Avoid normal form submission
    return false;
  }
}

function initFileOnlyAjaxUpload() {
  var uploadBtn = document.getElementById('<?php echo self::$uploadButtonId; ?>');
  uploadBtn.onclick = function (evt) {
    var formData = new FormData();

    // Since this is the file only, we send it to a specific location
    var action = '<?php echo self::$uploadUrl; ?>';

    // FormData only has the file
    var fileInput = document.getElementById('<?php echo self::$fileId; ?>');
    var file = fileInput.files[0];
    formData.append('our-file', file);

    // Code common to both variants
    sendXHRequest(formData, action);
  }
}

// Once the FormData instance is ready and we know
// where to send the data, the code is the same
// for both variants of this technique
function sendXHRequest(formData, uri) {
  // Get an XMLHttpRequest instance
  var xhr = new XMLHttpRequest();

  // Set up events
  xhr.upload.addEventListener('loadstart', onloadstartHandler, false);
  xhr.upload.addEventListener('progress', onprogressHandler, false);
  xhr.upload.addEventListener('load', onloadHandler, false);
  xhr.addEventListener('readystatechange', onreadystatechangeHandler, false);

  // Set up request
  xhr.open('POST', uri, true);

  // Fire!
  xhr.send(formData);
}

// Handle the start of the transmission
function onloadstartHandler(evt) {
  var div = document.getElementById('<?php echo self::$uploadId; ?>');
  div.innerHTML = 'Upload started!';
}

// Handle the end of the transmission
function onloadHandler(evt) {
  var div = document.getElementById('<?php echo self::$uploadId; ?>');
  div.innerHTML = 'Upload successful!';
}

// Handle the progress
function onprogressHandler(evt) {
  var div = document.getElementById('<?php echo self::$progressId; ?>');
  var percent = evt.loaded/evt.total*100;
  div.innerHTML = 'Progress: ' + percent + '%';
}

// Handle the response from the server
function onreadystatechangeHandler(evt) {
  var status = null;

  try {
    status = evt.target.status;
  }
  catch(e) {
    return;
  }

  if (status == '200' && evt.target.responseText) {
    var result = document.getElementById('<?php echo self::$resultId; ?>');
    result.innerHTML = '<p>The server saw it as:</p><pre>' + evt.target.responseText + '</pre>';
  }
}
window.onload = function() {
	// Actually confirm support
	if (supportAjaxUploadWithProgress()) {
	  // Ajax uploads are supported!
	  // Change the support message and enable the upload button
	  var notice = document.getElementById('<?php echo self::$supportId; ?>');
	  var uploadBtn = document.getElementById('<?php echo self::$uploadButtonId; ?>');
	  notice.innerHTML = "Your browser supports HTML uploads. Go try me! :-)";
	  uploadBtn.removeAttribute('disabled');

	  // Init the Ajax form submission
	  initFullFormAjaxUpload();

	  // Init the single-field file upload
	  initFileOnlyAjaxUpload();
	}
}
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

if(isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['REQUEST_URI'])) {
	$argv = array();
	$argv[0] = '/'.str_replace($_SERVER['DOCUMENT_ROOT'],"", (__WIN__ ? str_replace("\\","/",__FILE__) : __FILE__));
	$argv[1] = str_replace($argv,"",$_SERVER['REQUEST_URI']);
}

if(isset($argv)) {
	$arguments = array();
	$class = "Controller";
	$method = null;
	$temp = explode("?",$argv[1]);
	$argv[1] = $temp[0];
	if(count($temp)>1) $argv[2] = $temp[1];
	foreach(explode("/", $argv[1]) as $str) {
		if($str == "" || $str == null) {
			;
		} elseif($method == null) {
			$method = $str;
		} else {
			$arguments[] = $str;
		}
	}
}
if (count($ajaxData) > 0) {
	echo "requesting \n  class: $class \n  method: $method";
	if (method_exists($class, $method)) {
		$reflectionMethod = new ReflectionMethod($class,$method);
		$parameters = array();
		$requiredParameters = $reflectionMethod->getNumberOfRequiredParameters();
		$i = 0;
		foreach($reflectionMethod->getParameters() as $param) {
			if (isset($ajaxData[$param->name])) {
				$parameters[$i] = $ajaxData[$param->name];
			} elseif($i + 1 <= $requiredParameters + 1) {
				die;
			}
			$i++;
		}
		if ($reflectionMethod->isStatic()) {
			call_user_func_array([$class, $method], $parameters);
		} else {
			$object = new $class();
			call_user_func_array([$object, $method], $parameters);
		}
		die;
	}
} elseif(class_exists($class)) {
	if ($method != null) {
		if(method_exists($class, $method) && substr_count($method,"__",0,2) == 0) {
			$reflectionMethod = new ReflectionMEthod($class, $method);
			if ($reflectionMethod->isStatic()) {
				call_user_func_array([$class, $method], []);
			} else {
				$object = new $class();
				call_user_func_array([$object, $method], []);
			}
		} else {
			header("HTTP/1.0 501 Not Implemented");
		}
	} else {
		$object = new $class();
	}
} else {
	header("HTTP/1.0 404 Not Found");
}
?>