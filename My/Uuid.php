<?php
class My_Uuid { 
    /** 
     * This class enables you to get real uuids using the OSSP library. 
     * Note you need php-uuid installed. 
     * 
     * Source: http://www.php.net/manual/en/function.uniqid.php#88434
     * 
     * @author Marius Karthaus 
     * 
     */ 
    
    protected $uuidobject; 
    
    /** 
     * This checks the resource and creates it if needed. 
     * 
     */ 
    protected function create() { 
        if (! is_resource ( $this->uuidobject )) { 
            @uuid_create ( &$this->uuidobject ); 
        } 
    } 
    
    /** 
     * Return a type 1 (MAC address and time based) uuid 
     * 
     * @return String 
     */ 
    public function v1() { 
        $this->create (); 
        uuid_make ( $this->uuidobject, UUID_MAKE_V1 ); 
        uuid_export ( $this->uuidobject, UUID_FMT_STR, &$uuidstring ); 
        return trim ( $uuidstring ); 
    } 
    
    /** 
     * Return a type 4 (random) uuid 
     * 
     * @return String 
     */ 
    public function v4() { 
        return $this->guidv4(openssl_random_pseudo_bytes(16));  
//         $this->create (); 
//         uuid_make ( $this->uuidobject, UUID_MAKE_V4 ); 
//         @uuid_export ( $this->uuidobject, UUID_FMT_STR, &$uuidstring ); 
//         return trim ( $uuidstring ); 
    } 
    
    /** 
     * Return a type 5 (SHA-1 hash) uuid 
     * 
     * @return String 
     */ 
    public function v5() { 
        $this->create (); 
        uuid_make ( $this->uuidobject, UUID_MAKE_V5 ); 
        @uuid_export ( $this->uuidobject, UUID_FMT_STR, &$uuidstring ); 
        return trim ( $uuidstring ); 
    } 
    
    /**
     * 
     * According to RFC 4122 - Section 4.4, you need to change these fields:
	   time_hi_and_version (bits 4-7 of 7th octet),
	   clock_seq_hi_and_reserved (bit 6 & 7 of 9th octet)
	   All of the other 122 bits should be sufficiently random.
       @see http://stackoverflow.com/a/15875555/587228
     * @param unknown $data
     */
    public function guidv4($data)
    {
    	assert(strlen($data) == 16);
    
    	$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    	$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    
    	return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    
} 
?>