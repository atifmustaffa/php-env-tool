#!/usr/bin/env php
<?php
/**
 * .env PHP Encryption Tool
 * 
 * Encrypt and decrypt .env files using AES-256-CBC.
 * 
 * Author: Atif Mustaffa
 * GitHub: https://github.com/atifmustaffa
 * License: MIT
 */

function getKey(bool $generate = false)
{
  if ($generate) {
    $key = bin2hex(random_bytes(16)); // 32-char hex = 128-bit
    echo "\n🔐 Generated key: $key\n";
    echo "⚠️  Save this key securely — you'll need it to decrypt!\n\n";
    return $key;
  }

  $key = getenv('ENV_KEY');
  if (!$key) {
    echo "Enter decryption key: ";
    $key = trim(fgets(STDIN));
    if (!$key) {
      echo "No key provided. Aborting.\n";
      exit(1);
    }
  }

  $key = hash('sha256', $key, true);

  return $key;
}

function encryptFile($file, $key, $force = false, $silent = false, $outputOverride = null)
{
  $output = $outputOverride ?? "$file.enc";
  if (file_exists($output) && !$force) {
    echo "⚠️ $output exists. Overwrite? [y/N]: ";
    $input = strtolower(trim(fgets(STDIN)));
    if ($input !== 'y') {
      if (!$silent)
        echo "❌ Skipped $file\n";
      return;
    }
  }

  if (!file_exists($file)) {
    file_put_contents($file, "# Empty generated env\n");
  }

  $plaintext = file_get_contents($file);
  $ivLength = openssl_cipher_iv_length('aes-256-cbc');
  $iv = openssl_random_pseudo_bytes($ivLength);
  $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
  $encoded = base64_encode($iv . $ciphertext);

  file_put_contents($output, $encoded);
  if (!$silent)
    echo "✅ Encrypted $file → $output\n";
}

function decryptFile($file, $key, $force = false, $silent = false, $outputOverride = null)
{
  if (!file_exists($file)) {
    if (!$silent)
      echo "❌ File not found: $file\n";
    exit(1);
  }

  $output = $outputOverride ?? preg_replace('/\.enc$/', '', $file);
  if (file_exists($output) && !$force) {
    echo "⚠️ $output exists. Overwrite? [y/N]: ";
    $input = strtolower(trim(fgets(STDIN)));
    if ($input !== 'y') {
      if (!$silent)
        echo "❌ Skipped $file\n";
      return;
    }
  }

  $data = base64_decode(file_get_contents($file));
  $ivLength = openssl_cipher_iv_length('aes-256-cbc');
  $iv = substr($data, 0, $ivLength);
  $ciphertext = substr($data, $ivLength);

  $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
  if ($plaintext === false) {
    if (!$silent)
      echo "❌ Decryption failed for $file\n";
    exit(1);
  }

  file_put_contents($output, $plaintext);
  if (!$silent)
    echo "✅ Decrypted $file → $output\n";
}

// --- CLI Parsing ---
$args = $argv;
array_shift($args); // remove script name

$generate = in_array('--generate', $argv);
$force = in_array('--force', $args);
$silent = in_array('--silent', $args);

$outputTo = null;
foreach ($argv as $arg) {
  if (str_starts_with($arg, '--output=')) {
    $outputTo = substr($arg, 9); // after "--output="
  }
}

$args = array_filter($args, fn($a) => !str_starts_with($a, '--'));
$args = array_values($args); // reindex

$command = $args[0] ?? null;
$targetFile = $args[1] ?? null;

if (!$command || !in_array($command, ['encrypt', 'decrypt'])) {
  echo "Usage: encrypt|decrypt [file] [--force] [--silent] [--generate] [--output=file]\n";
  exit(1);
}

$key = getKey($generate);

// Discover files
$files = [];

if ($targetFile) {
  $files = [$targetFile];
} else {
  if ($command === 'encrypt') {
    $files = array_filter(glob('.env*'), function ($file) {
      return is_file($file)
        && !str_ends_with($file, '.example')
        && !str_ends_with($file, '.enc');
    });
  } elseif ($command === 'decrypt') {
    $files = glob('.env*.enc');
  }
}

// Run
foreach ($files as $file) {
  if (!is_file($file))
    continue;

  if ($command === 'encrypt') {
    encryptFile($file, $key, $force, $silent, count($files) === 1 ? $outputTo : null);
  } elseif ($command === 'decrypt') {
    decryptFile($file, $key, $force, $silent, count($files) === 1 ? $outputTo : null);
  }
}
