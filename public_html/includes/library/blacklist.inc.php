<?php
  
  class blacklist {
  
    private $system;
    private $blacklist_file;
    private $trigger;
    
    public function __construct(&$system) {
      $this->system = &$system;
      
    // Sanitization
      if (!function_exists('sanitize_variable')) {
        function sanitize_variable(&$item, $key) {
        
          $bad_words = array(
            //'<script',
            'eval(',
            'base64_'
          );
          
          $item = str_ireplace($bad_words, '****', $item);
        }
      }
      array_walk_recursive($_POST, 'sanitize_variable');
      array_walk_recursive($_GET, 'sanitize_variable');
      
    // Set blacklist file
      $this->blacklist_file = FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'blacklist.dat';
      
    // Ban union select attempts
      if (strpos($_SERVER['REQUEST_URI'], ' union select ')) $this->ban();
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    public function startup() {
    
      if (!isset($this->system->session->data['blacklist']['trigger'])) $this->system->session->data['blacklist']['trigger'] = array();
      $this->trigger = &$this->system->session->data['blacklist']['trigger'];
    
      if (empty($this->trigger['key']) || empty($this->trigger['expires']) || $this->trigger['expires'] < date('Y-m-d H:i:s')) {
        $this->system->session->data['blacklist']['trigger'] = array(
          'key' => substr(str_shuffle('abcdefghjklmnopqrstuvwxyzabcdefghjklmnopqrstuvwxyzabcdefghjklmnopqrstuvwxyz'), 0, rand(6, 8)),
          'expires' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
        );
      }
      
    
    // If in bot trap, ban the current user
      if (isset($_GET[$this->trigger['key']])) $this->ban();

    // Check if user IP is in ban list
      $this->check($_SERVER['REMOTE_ADDR']);
    }
    
    //public function before_capture() {
    //}
    
    public function after_capture() {
      
      if ($this->system->document->viewport == 'desktop') {
        $this->system->document->snippets['content'] = '<div style="position: absolute;"><a href="'. $this->system->document->link(WS_DIR_HTTP_HOME, array($this->trigger['key'] => '')) .'" rel="nofollow"><img src="data:image/gif;base64,R0lGODlhAQABAPAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" width="1" height="1" alt="" /></a></div>' . PHP_EOL
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
      
      $rows = file($this->blacklist_file);
      
      $banned = false;
      foreach (array_keys($rows) as $key) {
        if (trim(preg_replace('/^\[([^\]]+)\].*$/', '$1', $rows[$key])) < date('Y-m-d H:i:s', strtotime('-1 hours'))) {
          unset($rows[$key]);
          continue;
        }
        if (strpos($rows[$key], $ip) === false) {
          continue;
        }
        $banned = true;
      }
      
      file_put_contents($this->blacklist_file, implode('', $rows));
      
      if ($banned) {
        sleep(30);
        header('HTTP/1.1 403 Forbidden');
        die('<html>' . PHP_EOL
          . '<head>' . PHP_EOL
          . '<title>Banned</title>' . PHP_EOL
          . '</head>' . PHP_EOL
          . '<body>' . PHP_EOL
          . '<h1>Banned</h1>' . PHP_EOL
          . '<p>Your host has been banned due to <em>bad bot</em> behavior.</p>' . PHP_EOL
          . '<p>If you feel this an error, send an e-mail to '. $this->system->settings->get('store_email') .'.<br />' . PHP_EOL
          . '  Bad behaving bots are not welcome on this site!</p>' . PHP_EOL
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
      
      header('Location: '. $this->system->document->link(WS_DIR_HTTP_HOME));
      exit;
    }
    
  }
  
?>