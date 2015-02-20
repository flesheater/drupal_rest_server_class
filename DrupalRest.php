<?php
/*
 * @file 
 * Content of the DrupalRest class
 * 
 * This is my version of the class DrupalREST, that could be found here
 * https://github.com/RandallKent/DrupalREST.PHP/blob/master/DrupalREST.php
 * 
 * Things that are fixed: 
 * - better encapsulation
 * - added the $host param to the construct method so that it is not hardcoded
 * - made the $endpoint variable only the endpoint part of the service, without the host
 * - trailing slash fix
 * - added a method for updating the node
 * - added more comments in the code
 * - added examples of using it in the examples.php
 * 
 * The last version of this class could be found here:
 * http://github.com/flesheater/drupal_rest_server_class
 * 
 */

class DrupalRest {
    private $username;
    private $password;
    private $session;
    private $host;
    private $hostendpoint;
    private $debug;
    private $csrf_token;

    /*
     * @param $host
     *  The host of the site e.g. http://yoursite.com
     * @param $endpoint
     *  The endpoint that you want to access. e.g. rest (from http://yoursite.com/rest)
     * @param $username
     *  The username of the user you want to login with to the drupal site
     * @param $password
     *  The password of the user you want to login with to the drupal site
     * @param $debug
     *  A bool value if you want it to be in debug mode
     * 
     */
    function __construct($host, $endpoint, $username, $password, $debug) {
        $this->username = $username;
        $this->password = $password;
        $this->hostendpoint = $this->_trailSlashFilter($host) . '/' . $this->_trailSlashFilter($endpoint) . '/';
        $this->host = $this->_trailSlashFilter($host) . '/';
        $this->debug = $debug;
    }

    public function login() {
        $ch = curl_init($this->hostendpoint . 'user/login.json');
        $post_data = array(
          'username' => $this->username,
          'password' => $this->password,
        );
        $post = http_build_query($post_data, '', '&');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array (
          "Accept: application/json",
          "Content-type: application/x-www-form-urlencoded"
        ));
        $response = json_decode(curl_exec($ch));
        
        //Save Session information to be sent as cookie with future calls
        $this->session = $response->session_name . '=' . $response->sessid;
        
        // GET CSRF Token
        curl_setopt_array($ch, array(
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_URL => $this->host . 'services/session/token',
        ));
        curl_setopt($ch, CURLOPT_COOKIE, "$this->session"); 

        $ret = new stdClass;
        $ret->response = curl_exec($ch);
        $ret->error    = curl_error($ch);
        $ret->info     = curl_getinfo($ch);


        $this->csrf_token = $ret->response;
    }

    // Retrieve a node from a node id
    public function retrieveNode($nid) {
    		$result = new stdClass;
        $result->ErrorCode = NULL;
				
        $nid = (int) $nid;
        $ch = curl_init($this->hostendpoint . 'node/' . $nid );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array (
          "Accept: application/json",
          "Cookie: $this->session"
        ));

        $result = $this->_handleResponse($ch);
        curl_close($ch);

        return $result;
    }

    /*
     * @param $node
     *  An array of the node that you want to create
     * @param $node['title']
     *  The title of the node
     * @param $node['type']
     *  The content type of the node you want to craete. It is required.
     * 
     * You can specify the other fields you want to change the same way.
     * E.g. $node['body']['und'][0]['value']
     */
    public function createNode($node) {
        $post = http_build_query($node, '', '&');
        $ch = curl_init($this->hostendpoint . 'node');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
        array (
          "Accept: application/json",
          "Content-type: application/x-www-form-urlencoded",
          "Cookie: $this->session",
          'X-CSRF-Token: ' . $this->csrf_token,
        ));

        $result = $this->_handleResponse($ch);
        curl_close($ch);

        return $result;
    }

    /*
     * @param $node
     *  An array of the node that you want to create
     * @param $node['nid']
     *  The id of the node you want to edit. It is required.
     * 
     * You can specify the other fields you want to change the same way.
     * E.g. $node['title'] or $node['body']['und'][0]['value']
     */  
    public function updateNode($node) {
      $post = http_build_query($node, '', '&');
      $ch = curl_init($this->hostendpoint . 'node/' . $node['nid']);

      // Emulate file.
      $putData = fopen('php://temp', 'rw+');
      fwrite($putData, $post);
      fseek($putData, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, TRUE);
      curl_setopt($ch, CURLOPT_PUT, TRUE);
      curl_setopt($ch, CURLOPT_INFILE, $putData);
      curl_setopt($ch, CURLOPT_INFILESIZE,  mb_strlen($post));
      curl_setopt($ch, CURLOPT_HTTPHEADER,
      array (
        "Accept: application/json",
        "Content-type: application/x-www-form-urlencoded",
        "Cookie: $this->session",
        'X-CSRF-Token: ' . $this->csrf_token
      ));

      $result = $this->_handleResponse($ch);
      curl_close($ch);

      return $result;
    }
		
		// Retrieve a file based on fid
    public function retrieveFile($fid) {
    		$result = new stdClass;
        $result->ErrorCode = NULL;

        $fid = (int) $fid;
        $ch = curl_init($this->hostendpoint . 'file/' . $fid );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array (
          "Accept: application/json",
          "Cookie: $this->session"
        ));

        $result = $this->_handleResponse($ch);
        curl_close($ch);

        return $result;
    }

		public function createFile($file) {
        $post = http_build_query($file, '', '&');
        $ch = curl_init($this->hostendpoint . 'file');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
        array (
          "Accept: application/json",
          "Content-type: application/x-www-form-urlencoded",
          "Cookie: $this->session",
          'X-CSRF-Token: ' . $this->csrf_token,
        ));

        $result = $this->_handleResponse($ch);
        curl_close($ch);

        return $result;
    }

    /*
     * @param $string
     *  The string of the host or the endpoint that have to be checked for slashes
     * 
     * A helper function for removing the trailing slash from the $host variable
     * at the end or from the $endpoint variable from the beginning 
     * 
     */
    private function _trailSlashFilter($string) {
      if (substr($string, -1) == '/') {
        $string = substr($string, 0, -1);
      }
  
      if (substr($string, 0, 1) == '/') {
       $string = substr($string, 1);
      }
    
      return $string;
    }
    
    /*
     * @param $ch
     *  The cURL handle
     * 
     */
    private function _handleResponse($ch) {
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        //break apart header & body
        $header = substr($response, 0, $info['header_size']);
        $body = substr($response, $info['header_size']);

        $result = new stdClass();

        if ($info['http_code'] != '200') {
          $header_arrray = explode("\n",$header);
          $result->ErrorCode = $info['http_code'];
          $result->ErrorText = $header_arrray['0'];
        }
        else {
          $result->ErrorCode = NULL;
          $decodedBody= json_decode($body);
          $result = (object) array_merge((array) $result, (array) $decodedBody );
        }

        if ($this->debug) {
            $result->header = $header;
            $result->body = $body;
        }

        return $result;
    }
}
