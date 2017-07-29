<?php
  // Check the arguments
  if ($argc != 2) {
    echo 'Use a single argument which is the password to be used' . PHP_EOL;
    echo PHP_EOL;
    echo 'Usage:' . PHP_EOL;
    echo '  ' . $argv[0] . ' CLEAR_TEXT' . PHP_EOL;
    exit(1);
  }
  $clear = $argv[1];

  // Load DokuWiki PassHash class
  require('vendor/paragonie/random_compat/lib/random.php');
  require('inc/PassHash.class.php');
  $pass = new PassHash();

  // Output using a random salt
  echo $pass->hash_smd5($clear) . PHP_EOL;
  