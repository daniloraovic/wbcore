<?php
class My_Model_Utility {	
	public static function safeWrite($content, $filename, $mode = 0775) {
		$tempfile = @tempnam(dirname($filename), 'tmp_');
		$fp = @fopen($tempfile, 'wb+'); $filesize = @fwrite($fp, $content); @fclose($fp);
		$succ = @rename($tempfile, $filename);
		if (!$succ) {
			@copy($tempfile, $filename);
			@unlink($tempfile);
		}
		@chmod($filename, $mode);
		return $succ ? $filesize : 0;
	}
}