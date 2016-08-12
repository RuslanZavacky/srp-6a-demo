<?php
require __DIR__ . '/../vendor/autoload.php';

$root = $_SERVER['DOCUMENT_ROOT'];
chdir($root);
$path = '/' . ltrim(parse_url($_SERVER['REQUEST_URI'])['path'], '/');

if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|map)$/', $path, $matches)) {
  if (file_exists($root . $path) && is_file($root . $path) && !strpos($path, ".php")) {
    $mimes = new \Mimey\MimeTypes;

    header("Content-Type: " . $mimes->getMimeType($path));
    return readfile($root . $path);
  }
} else {
  $bootstrap = new Rz\Bootstrap();
  $bootstrap->route($path);
}