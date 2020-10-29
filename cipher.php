<?php

// Store a string into the variable which 
// need to be Encrypted 
//$simple_string = "Welcome to GeeksforGeeks";

//$encryption = encrypt('128');
// Display the original string 
//echo "encryption String: " . $encryption;

//$decryption = decrypt($encryption);
// Display the decrypted string 
//echo "Decrypted String: " . $decryption;

function encrypt($str)
{
    // Store the cipher method 
    $ciphering = "AES-128-CTR";

    // Use OpenSSl Encryption method 
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

    // Non-NULL Initialization Vector for encryption 
    $encryption_iv = '1234567891011121';

    // Store the encryption key 
    $encryption_key = "esJh3a9xNPLT7EpbshECDRuaCa8u";

    // Use openssl_encrypt() function to encrypt the data 
    $encryption = openssl_encrypt(
        $str,
        $ciphering,
        $encryption_key,
        $options,
        $encryption_iv
    );
    return $encryption;
}

function decrypt($str)
{

    // Non-NULL Initialization Vector for decryption 
    $decryption_iv = '1234567891011121';
    $ciphering = "AES-128-CTR";
    // Store the decryption key 
    $decryption_key = "esJh3a9xNPLT7EpbshECDRuaCa8u";
    $options = 0;
    // Use openssl_decrypt() function to decrypt the data 
    $decryption = openssl_decrypt(
        $str,
        $ciphering,
        $decryption_key,
        $options,
        $decryption_iv
    );
    return $decryption;
}
