<?php
/**
 * Class RC4
 *
 * Implements the encrypt algorithm RC4.
 *
 * @category Crypt
 * @author   Rafael M. Salvioni
 * @see      http://pt.wikipedia.org/wiki/RC4
 */
class RC4
{
    /**
     * Store the permutation vectors
     *
     * @var array
     */
    private static $S = array();
    
    /**
     * Swaps values on the permutation vector.
     *
     * @param int $v1 Value 1
     * @param int $v2 Value 2
     */
    private static function swap(&$v1, &$v2)
    {
        $v1 = $v1 ^ $v2;
        $v2 = $v1 ^ $v2;
        $v1 = $v1 ^ $v2;
    }
    
    /**
     * Make, store and returns the permutation vector about the key.
     *
     * @param string $key Key
     * @return array
     */
    private static function KSA($key)
    {
        $idx = crc32($key);
        if (!isset(self::$S[$idx])) {
            $S   = range(0, 255);
            $j   = 0;
            $n   = strlen($key);
            for ($i = 0; $i < 255; $i++) {
                $char  = ord($key{$i % $n});
                $j     = ($j + $S[$i] + $char) % 256;
                self::swap($S[$i], $S[$j]);
            }
            self::$S[$idx] = $S;
        }
        return self::$S[$idx];
    }
    
    /**
     * Encrypt the data.
     *
     * @param string $key Key
     * @param string $data Data string
     * @return string
     */
    public static function encrypt($key, $data)
    {
        $S    = self::KSA($key);
        $n    = strlen($data);
        $i    = $j = 0;
        $data = str_split($data, 1);
        for ($m = 0; $m < $n; $m++) {
            $i        = ($i + 1) % 256;
            $j        = ($j + $S[$i]) % 256;
            self::swap($S[$i], $S[$j]);
            $char     = ord($data{$m});
            $char     = $S[($S[$i] + $S[$j]) % 256] ^ $char;
            $data[$m] = chr($char);
        }
        $data = implode('', $data);
        return $data;
    }
    
    /**
     * Decrypts the data.
     *
     * @param string $key Key
     * @param string $data Encripted data
     * @return string
     */
    public static function decrypt($key, $data)
    {
        return self::encrypt($key, $data);
    }
}
