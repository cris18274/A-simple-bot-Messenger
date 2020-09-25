<?php

class Request {
  private $req = "";

  public function __construct($url) {
    $this -> req = curl_init($url);
  }

  public function option($opt, $val) {
    curl_setopt($this -> req, $opt, $val);
  }

  public function result() {
    curl_setopt($this -> req, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this -> req, CURLOPT_TIMEOUT, 60);

    $data = curl_exec($this -> req);
    curl_close($this -> req);
    return $data;
  }

  public function send() {
    curl_setopt($this -> req, CURLOPT_POST, 1);
    curl_setopt($this -> req, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    curl_exec($this -> req);
    curl_close($this -> req);
  }
}
