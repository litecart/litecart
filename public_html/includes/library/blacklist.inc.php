<?php
  
  class blacklist {
  
    private $system;
    private $blacklist_file;
    
    public function __construct(&$system) {
      $this->system = &$system;
      
    // Sanitization
      if (!function_exists('sanitize_variable')) {
        function sanitize_variable(&$item, $key) {
        
          $bad_words = array('<script', 'eval(', 'base64_');
          
          $item = str_ireplace($bad_words, '****', $item);
        }
      }
      array_walk_recursive($_POST, 'sanitize_variable');
      array_walk_recursive($_GET, 'sanitize_variable');
    
    // Set blacklist file
      $this->blacklist_file = FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'blacklist.dat';
      
    // Ban union select tries
      if (strpos($_SERVER['REQUEST_URI'], '+union+select+') || strpos($_SERVER['REQUEST_URI'], ' union select ')) $this->ban();
      if (strpos($_SERVER['REQUEST_URI'], '+union+select+') || strpos($_SERVER['REQUEST_URI'], ' union select ')) $this->ban();
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    public function startup() {
    
    // If in bot trap, ban the current user
      if (!empty($_GET['c87acf3b'])) $this->ban();

    // Check if user IP is in ban list
      $this->check($_SERVER['REMOTE_ADDR']);
    }
    
    //public function before_capture() {
    //}
    
    public function after_capture() {
      
      if ($this->system->document->viewport == 'desktop') {
        $this->system->document->snippets['content'] = '<div style="position: absolute;"><a href="?c87acf3b=1" rel="nofollow"><img src="data:image/gif;base64,R0lGODlhAQABAPAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" width="1" height="1" alt="" /></a></div>' . PHP_EOL
                                               . $this->system->document->snippets['content'];
      }
    }
    
    //public function prepare_output() {
    //}
    
    public function before_output() {
    }
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function check($ip) {
      
      $contents = file_get_contents($this->blacklist_file);

      if (strpos($contents, $ip) !== false) {
      
        sleep(30);
        
        die('<html>' . PHP_EOL
          . '<head>' . PHP_EOL
          . '<title>Banned</title>' . PHP_EOL
          . '</head>' . PHP_EOL
          . '<body>' . PHP_EOL
          . '<h1>Banned</h1>' . PHP_EOL
          . '<p>Your host has been banned and reported due to <em>bad bot</em> behavior.</p>' . PHP_EOL
          . '<p>If you feel this an error, send an e-mail to abuse@'. $_SERVER['SERVER_NAME'] .'.<br />' . PHP_EOL
          . '  If you are an anti-social ill-behaving bot, just go away!</p>' . PHP_EOL
          . '</body>' . PHP_EOL
          . '</html>' . PHP_EOL
        );
      }
    }
    
    public function ban() {
      
      $already_added = false;
      
      $contents = file_get_contents($this->blacklist_file);
      
      $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);

      if (strpos($contents, $_SERVER['REMOTE_ADDR']) === false) {
        
        $message = 'A bad robot with a suspected ill behavior was trapped.' . PHP_EOL . PHP_EOL
                 . 'Trap: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] .' on '. date('r') .'.' . PHP_EOL . PHP_EOL
                 . 'Address: '. $_SERVER['REMOTE_ADDR'] .' ('. $hostname .')' . PHP_EOL
                 . 'Agent: '. $_SERVER['HTTP_USER_AGENT'];
        
        file_put_contents($this->blacklist_file, '['. date('Y-m-d H:i:s') .'] ' . $_SERVER['REMOTE_ADDR'] .' ('. $hostname .') - "'. $_SERVER['REQUEST_METHOD'] .' '. $_SERVER['REQUEST_URI'] .' '. $_SERVER['SERVER_PROTOCOL'] .'" '. ((!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '-') .' '. $_SERVER['HTTP_USER_AGENT'] . PHP_EOL, FILE_APPEND);
        
        $this->system->functions->email_send('', $this->system->settings->get('store_email'), 'Bad Robot '. $_SERVER['REMOTE_ADDR'] .' ('. $hostname .')', $message);
      }
    }
    
  }
  
?>