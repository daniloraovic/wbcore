<?php
class My_Uuid_V4{
	
	public function createUuid() {
		return $this->guidv4(openssl_random_pseudo_bytes(16));
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
	public function guidv4($data =null )
	{
		assert(strlen($data) == 16);
	
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
	
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
	
}