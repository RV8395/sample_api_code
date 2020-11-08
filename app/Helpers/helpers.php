<?php

/**
 * Decrypt data from a CryptoJS json encoding string
 *
 * @param mixed $passphrase
 * @param mixed $jsonString
 * @return mixed
 */
function decryptData($passphrase, $jsonString)
{
    // decryption algorithm will be here
    // ....    
    // ....    
    // ....    
    // ....    
    // ....    
    // ....    
    // decryption algorithm will be here  
    return json_decode($data, true);
}

/**
 * Encrypt value to a cryptojs compatiable json encoding string
 *
 * @param mixed $passphrase
 * @param mixed $value
 * @return string
 */
function encryptData($passphrase, $value)
{
    // encryption algorithm will be here
    // ....    
    // ....    
    // ....    
    // ....    
    // ....    
    // ....    
    // encryption algorithm will be here    
    $data = array("xyz" => base64_encode($encrypted_data));
    return json_encode($data);
}

/**
 * Encrypt value to a cryptojs compatiable json encoding string
 *
 * @return string
 */
function getPassphrase()
{
    return 'base64:STRING_WILL_BE_HERE=';
}