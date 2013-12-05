<?php

function validate_card_number($card){
    $issuer = get_card_issuer($card);
    if(!$issuer)
        return false;
    switch($issuer){
        case 'mastercard':
            if(strlen($card) == 16 && luhn_check($card))
                return true;
            return false;
            break;
        case 'visa':
            if((strlen($card) == 13 || strlen($card) == 16) && luhn_check($card))
                return true;
            return false;
            break;
        case 'amex':
            if(strlen($card) == 15 && luhn_check($card))
                return true;
            return false;
            break;
        case 'diners':
            if(strlen($card) == 14 && luhn_check($card))
                return true;
            return false;
            break;
        case 'discover':
            if(strlen($card) == 16 && luhn_check($card))
                return true;
            return false;
            break;
    }
    return false;
}

/* Luhn algorithm number checker - (c) 2005-2008 shaman - www.planzero.org *
 * This code has been released into the public domain, however please      *
 * give credit to the original author where possible.                      */

function luhn_check($number) {

    // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
    $number=preg_replace('/\D/', '', $number);

    // Set the string length and parity
    $number_length=strlen($number);
    $parity=$number_length % 2;

    // Loop through each digit and do the maths
    $total=0;
    for ($i=0; $i<$number_length; $i++) {
        $digit=$number[$i];
        // Multiply alternate digits by two
        if ($i % 2 == $parity) {
            $digit*=2;
            // If the sum is two digits, add them together (in effect)
            if ($digit > 9) {
                $digit-=9;
            }
        }
        // Total up the digits
        $total+=$digit;
    }

    // If the total mod 10 equals 0, the number is valid
    return ($total % 10 == 0) ? TRUE : FALSE;
}

function get_card_issuer($card){
    switch(substr($card,0,2)){
        case '51':
        case '52':
        case '53':
        case '54':
        case '55':
            return 'mastercard';
            break;
        case '34':
        case '37':
            return 'amex';
            break;
        case '36':
        case '38':
            return 'diners';
            break;
    }
    switch(substr($card,0,3)){
        case '300':
        case '301':
        case '302':
        case '303':
        case '304':
        case '305':
            return 'diners';
        break;
    }
    if(substr($card,0,4) == "6011")
        return 'discover';
    if(substr($card,0,1) == 4)
        return 'visa';

    return false;
}

function encode_card($card){
    switch(strlen($card)){
        case 16:
            return "**** **** **** ". substr($card,-4);
            break;
        case 15:
            return "**** ****** *".substr($card, -4);
            break;
        default:
            $c = "";
            for($i = 1; $i<=strlen($card) - 4; $i++){
                $c .= "*";
            }
            return $c . substr($card, -4);
            break;
    }
}