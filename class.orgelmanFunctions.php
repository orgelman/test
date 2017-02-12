<?php 
class orgelmanFunctions {
   public  $version                 = "0.1.1";
   public  $update                  = "https://github.com/orgelman/functions/releases";
   
   private $root                    = "";
   private $path                    = "";
   public function __construct($root="") {
      $this->root = $root;
      
      if(md5_file(__FILE__) != @file_get_contents("https://server.orgelman.systems/orgelman/functions/md5.php")) {
         $this->error("<strong>orgelman/functions Outdated</strong><br>Update: ".$this->update."<br><br>");
      }
   }
   private function error($message, $level=E_USER_NOTICE) { 
      $caller = debug_backtrace()[0];

      if(php_sapi_name()!='cli') {
         trigger_error($message.' in <strong>'.$caller['function'].'</strong> called from <strong>'.$caller['file'].'</strong> on line <strong>'.$caller['line'].'</strong><br>'."\n", $level);
      } else {
         trigger_error($message.' in '.$caller['function'].' called from '.$caller['file'].' on line '.$caller['line'], $level);
      } 
   }
   
   public function setRoot($root) {
      $this->root                   = $root;
   }
   public function setPath($path) {
      $this->path                   = $path;
   }
   public function getDomain($root="",$path="") {
      $this->server                 = new stdClass();
      $this->server->root           = "";
      $this->server->domain         = "";
      $this->server->full           = "";
      
      $this->server->protocol       = "";
      $this->server->port           = "";
      $this->server->IP             = "";
      $this->server->server         = "";
      $this->server->URI            = "";
      $this->server->dir            = "";
      $this->server->subDomain      = array();
      $this->server->subFolder      = array();
      $this->server->get            = array();
      
      if($root=="") {
         if($this->root!="") {
            $root = $this->root.DIRECTORY_SEPARATOR;
         } else {
            $root = __DIR__.DIRECTORY_SEPARATOR;
         }
      } else {
         $root = $root.DIRECTORY_SEPARATOR;
      }
      if($path=="") {
         if($this->path!="") {
            $path = $this->path;
         } else {
            if($this->server->dir!="") {
               $path = $this->server->dir."/";
            }
         }
      } else {
         $path = $path."/";
      }
      $path = trim($path,"/")."/";
      
      $this->server->root = trim(str_replace(array("/","\\"),DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR.trim($root,"/").DIRECTORY_SEPARATOR));
      if(!file_exists($this->server->root)) {
         $this->error("No root directory found",  E_USER_ERROR);
      }
      
      
      if((isset($_SERVER['SERVER_PROTOCOL'])) && (isset($_SERVER['SERVER_PORT'])) && (isset($_SERVER['SERVER_NAME']))) {
         $ssl                       = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' );
         $sp                        = strtolower( $_SERVER['SERVER_PROTOCOL'] );
         $this->server->protocol    = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
         $this->server->port        = $_SERVER['SERVER_PORT'];
         $port                      = ( ( ! $ssl && $_SERVER['SERVER_PORT']=='80' ) || ( $ssl && $_SERVER['SERVER_PORT']=='443' ) || ($_SERVER['SERVER_PORT']=='80') ) ? '' : ':'.$_SERVER['SERVER_PORT'];
         if($path!="") {
            $this->server->dir      = trim(str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(debug_backtrace()[0]["file"])),"/");
         } else {
            $this->server->dir      = $path;
         }
         
        
         
         foreach(explode("/",$this->server->dir) as $dir) {
            $this->server->subFolder[] = $dir;
         }

         $domain                    = explode(":",preg_replace("/^www\.(.+\.)/i", "$1", $_SERVER["HTTP_HOST"]),2);
         $server                    = parse_url($domain[0]);
         
         if(isset($server['host'])) {
            $host                   = strtolower($server['host']);
         } else if(isset($server['path'])) {
            $host                   = strtolower($server['path']);
         } else {
            $host                   = strtolower($_SERVER["HTTP_HOST"]);
         }
         $host_names                = explode(".", $host);
         
         if(filter_var($_SERVER["SERVER_NAME"], FILTER_VALIDATE_IP) == true) {
            $this->server->IP       = $_SERVER['SERVER_NAME'];
         } else {
            $this->server->IP       = gethostbyname($_SERVER['SERVER_NAME']);
            $this->server->server   = $host_names[count($host_names)-2] . "." . $host_names[count($host_names)-1];
            unset($host_names[count($host_names)-1]);
            unset($host_names[count($host_names)-1]);
            $arr = array();
            
            foreach($host_names as $host){
               $arr[] = $host;
            }
            if(!empty($arr)) {
               foreach($arr as $subdom) {
                  $this->server->subDomain[] = $subdom;
               }
            }
         }
         if((isset($this->server->subDomain[0])) && (isset($this->server->subFolder[0]))) {
            if($this->server->subDomain[0] == $this->server->subFolder[0]) {
               unset($this->server->subFolder[0]);
            }
         }
         
         $this->server->URI         = ltrim($_SERVER["REQUEST_URI"],"/");
         
         if (substr($this->server->URI, 0, strlen($this->server->dir)) == $this->server->dir) {
            $this->server->URI      = ltrim(substr(trim(trim($_SERVER["REQUEST_URI"],"/")), strlen(trim(trim($this->server->dir,"/")))),"/");
         } 
         $domain                    = $this->server->protocol."://";
         foreach($this->server->subDomain as $dom) {
            $domain                .= $dom.".";
         }
         if($this->server->server=="") {
            $domain                .= $this->server->IP.$port."/";
         } else {
            $domain                .= $this->server->server.$port."/";
         }
         $this->server->domain      = $domain;
         
         if($path!="") {
            $this->server->dir         = $path;
         } else {
            $this->server->dir         = "";
            foreach($this->server->subFolder as $dom) {
               $this->server->dir     .= $dom."/";
            }
         }
         
         
          
      if(isset($_SERVER["REQUEST_URI"])) {
         if (substr(trim($_SERVER["REQUEST_URI"],"/"), 0, strlen($this->server->dir)) == trim($this->server->dir,"/")) {
            $_SERVER["REQUEST_URI"]      = ltrim(substr(trim(trim($_SERVER["REQUEST_URI"],"/")), strlen(trim(trim($this->server->dir,"/")))),"/");
         } 
         if(strpos($_SERVER["REQUEST_URI"], '?') !== false) {
         
         $qust = substr($_SERVER["REQUEST_URI"], strpos($_SERVER["REQUEST_URI"], "?") + 1);
         $quest = explode("?",$qust);
         $qust =  implode ("&",$quest);
         $qust = explode("&",$qust);
         
         foreach($qust as $get) {
            $gets = explode("=",$get);
            $key  = $gets[0];
            $get  = $gets[1];
            $new = explode("?",$get);
            if(count($new)>1) {
               foreach($new as $nget) {
                  if (strpos($nget, '=') !== false) {
                     $nkey = explode("=",$nget);
                     $this->server->get[$nkey[0]] = $nkey[1];
                     $_GET[$nkey[0]] = $nkey[1];
                  } else {
                     $this->server->get[$key] = $nget;
                     $_GET[$key] = $nget;
                  }
               }
            } else {
               $this->server->get[$key] = $get;
               $_GET[$key] = $get;
            }
         }
         }
      }
         
         $q = "";
         if(!empty($this->server->get)) { 
            $q = "?";
            $i=0;
            foreach($this->server->get as $key => $get) {
               if($i!=0) {
                  $q .= "&";
               }
               $q .= $key."=".$get;
               $i++;
            }
         }
      }
      $uri = $this->server->URI;
      if(trim(trim($this->server->dir,"/")) !="") {
         if(substr($this->server->URI, 0, strlen($this->server->dir)) === $this->server->dir) {
            $uri = substr($this->server->URI, strlen($this->server->dir));
         }
      }
      if(strpos($this->server->URI, '?') !== false) {
         $uri = substr($uri, 0, strpos($uri, "?"));
      }
      $this->root                   = $this->server->root;
      $this->path                   = $this->server->dir;
      
      $this->server->URI            = $uri.$q;
      
      $this->server->domain         = trim($this->server->domain.$this->server->dir,"/")."/";
      $this->server->full           = $this->server->domain.$uri.$q;
      
      return $this->server;
   }
   
   
   public function setCron($cronPath) {
      $response   = @file_get_contents("https://cron.orgelman.systems/setCall.php", false, $this->CronOptions($cronPath) );
      return true;
   }
   private function CronOptions($path = "") {
      if(isset($this->server)) {
         
      } else {
         $this->getDomain();
      }
      $postdata = http_build_query(
         array(
            'data'   => array('path'=>$path)
         )
      );
      $options = array(
         'http'=>array(
               "method"=>
                  "POST",
               "header"=>
                  "Accept-language: en\r\n" .
                  "Referer: ".$this->server->full."\r\n" . 
                  "User-Agent: orgelman/functions (".$this->version.")\r\n" .
                  "Content-type: application/x-www-form-urlencoded\r\n",
               "content"=> 
                  $postdata,
         )
      );
      return @stream_context_create($options);
   }

   // Get client browser info
   public function get_client_browser() {
      $browser = new Browser();
      print_r($browser);
      if( $browser->getBrowser() == Browser::BROWSER_FIREFOX && $browser->getVersion() >= 2 ) {
         echo 'You have FireFox version 2 or greater';
      }
   }
   
   // Get client User Agent
   public function get_client_ua() {
      $ua = 'UNKNOWN';
      if((isset($_SERVER['HTTP_USER_AGENT'])) && ($_SERVER['HTTP_USER_AGENT']!="")) {
         $ua = $_SERVER['HTTP_USER_AGENT'];
      }
      return $ua;
   }
   
   // Get client IP
   public function get_client_ip() {
      $ipaddress = '';
      if (getenv('HTTP_CLIENT_IP'))
         $ipaddress = getenv('HTTP_CLIENT_IP');
      else if(getenv('HTTP_X_FORWARDED_FOR'))
         $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
      else if(getenv('HTTP_X_FORWARDED'))
         $ipaddress = getenv('HTTP_X_FORWARDED');
      else if(getenv('HTTP_FORWARDED_FOR'))
         $ipaddress = getenv('HTTP_FORWARDED_FOR');
      else if(getenv('HTTP_FORWARDED'))
         $ipaddress = getenv('HTTP_FORWARDED');
      else if(getenv('REMOTE_ADDR'))
         $ipaddress = getenv('REMOTE_ADDR');
      else
         $ipaddress = 'UNKNOWN';
      
      if (!filter_var($ipaddress, FILTER_VALIDATE_IP) === false) {
      } else {
         $ipaddress = 'UNKNOWN';
      }
      
      return $ipaddress;
   }
   
   // Clean string URL
   public function toAscii($str, $replace=array(), $delimiter='-') {
      if( !empty($replace) ) {
         $str = str_replace((array)$replace, ' ', $str);
      }

      $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
      $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
      $clean = strtolower(trim($clean, '-'));
      $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

      return $clean;
   }

   //If internet access
   public function is_connected() {
      $connected = @fsockopen("www.example.com", 80); 
      if ($connected){
         $is_conn = true; 
         fclose($connected);
      } else {
         $is_conn = false;
      }
      return $is_conn;
   }

   //IF remote file exists
   //if(remoteFileExists("https://example.com/file.zip")) {
   public function remoteFileExists($url) {
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_NOBODY, true);
      $result = curl_exec($curl);
      $ret = false;
      if ($result !== false) {
         $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  
         if ($statusCode == 200) {
            $ret = true;   
         }
      }
      curl_close($curl);
      return $ret;
   }

   // Obfuscate email
   public function obfuscate_email($email) {
      $em   = explode("@",$email);
      $name = implode(array_slice($em, 0, count($em)-1), '@');
      $len  = floor(strlen($name)/2);

      return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);
   }
   
   // Get directory size in byte
   public function folderSize($dir) {
      $count_size = 0;
      $count = 0;
      $dir_array = scandir($dir);
      foreach($dir_array as $key=>$filename){
         if($filename!=".." && $filename!="."){
            if(is_dir($dir."/".$filename)){
               $new_foldersize = foldersize($dir."/".$filename);
               $count_size = $count_size+ $new_foldersize;
            } else if(is_file($dir."/".$filename)) {
               $count_size = $count_size + filesize($dir."/".$filename);
               $count++;
            }
         }
      }
      return $count_size;
   }
   
   // Format bytes
   public function formatBytes($bytes, $precision = 2) {
      $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
      $bytes = max($bytes, 0); 
      $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
      $pow = min($pow, count($units) - 1); 

      $bytes /= pow(1024, $pow);

      return round($bytes, $precision) . ' ' . $units[$pow]; 
   } 

   // Format numbers
   public function formatNumbers($numbers, $precision = 1) { 
      $units = array('', 'K', 'M'); 
      if($numbers>999) {
         $numbers = max($numbers, 0); 
         $pow = floor(($numbers ? log($numbers) : 0) / log(1000)); 
         $pow = min($pow, count($units) - 1); 

         $numbers /= pow(1000, $pow);

         return round($numbers, $precision) . ' ' . $units[$pow];
      } else {
         return $numbers;
      }
   } 
   
   // Print array to string
   public function array2str($array) {
      $str="";
      foreach($array as $k=>$i){
         if(is_array($i)){
            $str.=addslashes("<b>[".$k."]</b> => (".array2str($i).")<br> ");
         } else {
            if($i!=""){
               $str.=addslashes("[".$k."] => (".$i."), ");
            }
         }
      }
      return $str;
   }
   
   // Invers RBG
   public function color_inverse($color){
      $color = str_replace('#', '', $color);
      if (strlen($color) != 6){ 
         return '000000'; 
      }
      $rgb = '';
      for ($x=0;$x<3;$x++){
         $c = 255 - hexdec(substr($color,(2*$x),2));
         $c = ($c < 0) ? 0 : dechex($c);
         $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
      }
      return '#'.$rgb;
   }

   // Zip directory
   public function Zip($source, $destination) {
      if (!extension_loaded('zip') || !file_exists($source)) {
         return false;
      }

      $zip = new ZipArchive();
      if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
         return false;
      }

      $source = str_replace('\\', '/', realpath($source));

      if (is_dir($source) === true) {
         $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

         foreach ($files as $file) {
            $file = str_replace('\\', '/', realpath($file));
            if(is_dir($file) === true) {
               $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            } elseif(is_file($file) === true) {
               $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
         }
      } elseif(is_file($source) === true) {
         $zip->addFromString(basename($source), file_get_contents($source));
      }
      return $zip->close();
   }
}

?>
