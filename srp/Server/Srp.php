<?php

class Srp {
  /** @var \BigInteger Password verifier */
  protected $verifier;

  /** @var \BigInteger Password salt */
  protected $salt;

  /** @var \BigInteger|string */
  protected $N;
  protected $g;
  protected $k;
  protected $v;
  protected $A;
  protected $Ahex;

  /** @var \BigInteger|null Secure Random Number */
  protected $b = null;

  /** @var \BigInteger|null */
  protected $B = null;
  protected $Bhex;

  protected $M;
  protected $HAMK;

  public function __construct($verifier, $salt) {
    $this->verifier = $verifier;
    $this->salt     = $salt;
    $this->N        = new BigInteger("EEAF0AB9ADB38DD69C33F80AFA8FC5E86072618775FF3C0B9EA2314C9C256576D674DF7496EA81D3383B4813D692C6E0E0D5D8E250B98BE48E495C1D6089DAD15DC7D7B46154D6B6CE8EF4AD69B15D4982559B297BCF1885C529F566660E57EC68EDBC3C05726CC02FD4CBF4976EAA9AFD5138FE8376435B9FC61D2FC0EB06E3", 16);
    $this->g        = new BigInteger("2", 16);

    $this->k = new BigInteger($this->hash($this->N->toHex() . $this->g), 16);

    $this->v = new BigInteger($verifier, 16);

    $this->key = "";

    while (!$this->B || bcmod($this->B, $this->N) == 0) {
      $this->b = new BigInteger($this->getSecureRandom(), 16);
      $gPowed  = $this->g->powMod($this->b, $this->N);
      $this->B = $this->k->multiply($this->v)->add($gPowed)->powMod(new BigInteger(1), $this->N);
    }

    $this->Bhex = $this->B->toHex();
  }

  public function issueChallenge($A = '') {
    $this->A    = new BigInteger($A, 16);
    $this->Ahex = $this->A->toHex();

    if ($this->A->powMod(new BigInteger(1), $this->N) === 0) {
      throw new \Exception('Client sent invalid key: A mod N == 0.');
    }

    $u   = new BigInteger($this->hash($this->Ahex . $this->Bhex), 16);
    $v   = new BigInteger($this->getVerifier(), 16);
    $avu = $this->A->multiply($v->powMod($u, $this->N));

    $this->S   = $avu->modPow($this->b, $this->N);
    $Shex      = $this->S->toHex();
    $this->key = $this->hash($Shex);

    $this->M    = $this->hash($this->Ahex . $this->Bhex . $Shex);
    $this->HAMK = $this->hash($this->Ahex . $this->M . $Shex);

    return array(
      "salt" => $this->getSalt(),
      "B" => $this->Bhex
    );
  }

  public function getM() {
    return $this->M;
  }

  public function getHAMK() {
    return $this->HAMK;
  }

  public function getSesionKey() {
    return $this->key;
  }

  /**
   * Hash function to be used in SRP
   *
   * @param $x
   * @return string
   */
  public function hash($x) {
    return strtolower(hash('sha256', $x));
  }

  public function getSecureRandom($bits = 64) {
    /**
     * https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP
     * Our primary choice for a cryptographic strong randomness function is
     * openssl_random_pseudo_bytes.
     */

    $str = '';
    if (function_exists('openssl_random_pseudo_bytes') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || substr(PHP_OS, 0, 3) !== 'WIN')) {
      $str = openssl_random_pseudo_bytes($bits, $strong);
      if ($strong) {
        return $this->binary2hex($str);
      }
    }

    /*
     * If mcrypt extension is available then we use it to gather entropy from
     * the operating system's PRNG. This is better than reading /dev/urandom
     * directly since it avoids reading larger blocks of data than needed.
     * Older versions of mcrypt_create_iv may be broken or take too much time
     * to finish so we only use this function with PHP 5.3 and above.
     */
    if (function_exists('mcrypt_create_iv') && (version_compare(PHP_VERSION, '5.3.0') >= 0 || substr(PHP_OS, 0, 3) !== 'WIN')) {
      $str = mcrypt_create_iv($bits, MCRYPT_DEV_URANDOM);
      if ($str !== false) {
        return $this->binary2hex($str);
      }
    }

    /*
     * No build-in crypto randomness function found. We collect any entropy
     * available in the PHP core PRNGs along with some filesystem info and memory
     * stats. To make this data cryptographically strong we add data either from
     * /dev/urandom or if its unavailable, we gather entropy by measuring the
     * time needed to compute a number of SHA-1 hashes.
     */

    $bitsPerRound = 2; // bits of entropy collected in each clock drift round
    $msecPerRound = 400; // expected running time of each round in microseconds
    $hashLength   = 20; // SHA-1 Hash length
    $total        = $bits; // total bytes of entropy to collect

    $handle = @fopen('/dev/urandom', 'rb');
    if ($handle && function_exists('stream_set_read_buffer')) {
      @stream_set_read_buffer($handle, 0);
    }

    do {
      $bytes = ($total > $hashLength) ? $hashLength : $total;
      $total -= $bytes;

      //collect any entropy available from the PHP system and filesystem
      $entropy = rand() . uniqid(mt_rand(), true) . $str;
      $entropy .= implode('', @fstat(@fopen(__FILE__, 'r')));
      $entropy .= memory_get_usage();
      if ($handle) {
        $entropy .= @fread($handle, $bytes);
      } else {
        // Measure the time that the operations will take on average
        for ($i = 0; $i < 3; $i++) {
          $c1  = microtime(true);
          $var = sha1(mt_rand());
          for ($j = 0; $j < 50; $j++) {
            $var = sha1($var);
          }
          $c2 = microtime(true);
          $entropy .= $c1 . $c2;
        }

        // Based on the above measurement determine the total rounds
        // in order to bound the total running time.
        $rounds = (int)($msecPerRound * 50 / (int)(($c2 - $c1) * 1000000));

        // Take the additional measurements. On average we can expect
        // at least $bits_per_round bits of entropy from each measurement.
        $iter = $bytes * (int)(ceil(8 / $bitsPerRound));
        for ($i = 0; $i < $iter; $i++) {
          $c1  = microtime();
          $var = sha1(mt_rand());
          for ($j = 0; $j < $rounds; $j++) {
            $var = sha1($var);
          }
          $c2 = microtime();
          $entropy .= $c1 . $c2;
        }

      }
      // We assume sha1 is a deterministic extractor for the $entropy variable.
      $str .= sha1($entropy, true);
    } while ($bits > strlen($str));

    if ($handle) {
      @fclose($handle);
    }

    return $this->binary2hex($str);
  }

  public function binary2hex($string) {
    $chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');

    $length = strlen($string);

    $result = '';
    for ($i = 0; $i < $length; $i++) {
      $b      = ord($string[$i]);
      $result = $result . $chars[($b & 0xF0) >> 4];
      $result = $result . $chars[$b & 0x0F];
    }

    return $result;
  }

  public function getVerifier() {
    return $this->verifier;
  }

  public function getSalt() {
    return $this->salt;
  }
}
