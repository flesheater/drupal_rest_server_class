<?php
/**
 * @file
 * Containing some examples of using of the DrupalRest class.
 *
 * The latest version of the class could be found here:
 * http://github.com/flesheater/drupal_rest_server_class.
 */

require_once 'DrupalRest.php';

/**
 * Create an instance of the class.
 */

$request = new DrupalRest('http://yoursite.com/', '/rest', 'user', 'pass', 0);

print_r($request);
print '<hr>';

/**
 * Login.
 */
$request->login();
print_r($request);
print '<hr>';

/**
 * Get node 1.
 */
$nodes = $request->retrieveNode(1);
print_r($nodes);
print '<hr>';


/**
 * Create a node.
 */
$node_data = array(
  'title' => 'edited test layer yyy',
  'type' => 'article',
);

$node = $request->createNode($node_data);
print_r($node);
print '<hr>';


/**
 * Update a node.
 */

$node_data = array(
  'nid' => 9,
  'title' => 'edited test layer 9',
);

$node = $request->updateNode($node_data);
print_r($node);
print '<hr>';


/**
 * Upload an image.
 */

$path = 'cat.jpg';
$base64 = base64_encode(file_get_contents($path));

$file_data = array(
  "filename" => "cat.jpg",
  "file" => $base64,
);

$file = $request->createFile($file_data);
print_r($file);
print '<hr>';

/**
 * Get an image.
 */

$file = $request->retrieveFile($file->fid);
print_r($file);
print '<hr>';
