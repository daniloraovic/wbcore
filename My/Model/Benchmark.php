<?php
class My_Model_Benchmark {
	
	private $_client_id;
	private $_process_name;
	private $_id;	
	private $_start;
	
	private $_microtime;	
	private $_rusage;
	private $_tusage;
	private $_system;
	private $_user;
	
	private $_checkpoint = array();
	
	/**
	 * 
	 * @param string $process_name
	 * @param int $client_id
	 */
	public function __construct( $process_name, $client_id = 0 ) {
		$dat = getrusage();
		
		$this->_process_name = $process_name;
		$this->_client_id = $client_id;
		$this->_id = 0;
		$this->_start = time();
		
		$this->_microtime = microtime(true);
		$this->_user = $dat["ru_utime.tv_sec"]*1e6+$dat["ru_utime.tv_usec"];
		$this->_system = $dat["ru_stime.tv_sec"]*1e6+$dat["ru_stime.tv_usec"];
	}
	
	/**
	 * set checkpoint for current script
	 */	
	public function setCheckpoint($checkpoint_id = 0, $total_count = 0, $comment = "") {
		
		if($checkpoint_id > 0) {
			$id = $checkpoint_id;
			
			$this->_checkpoint[$id]['end']['cpu_usage'] = $this->_returnCpuUsage();
			$this->_checkpoint[$id]['end']['mem_usage'] = $this->_returnMemoryUsage();
			$this->_checkpoint[$id]['end']['execution'] = microtime(true) - $this->_checkpoint[$id]['start']['start_time'];
			$this->_checkpoint[$id]['end']['total_time'] = $this->_returnRunTime();
			
			if($total_count > 0) $this->_checkpoint[$id]['end']['total_count'] = $total_count;
			if ($comment != "") $this->_checkpoint[$id]['end']['comment'] = $comment;
		} else {
			$this->_id++;
			$id = $this->_id;
			
			$this->_checkpoint[$id]['checkpoint_id'] = $id;
			$this->_checkpoint[$id]['start']['cpu_usage'] = $this->_returnCpuUsage();
			$this->_checkpoint[$id]['start']['mem_usage'] = $this->_returnMemoryUsage();
			$this->_checkpoint[$id]['start']['total_time'] = $this->_returnRunTime();
			$this->_checkpoint[$id]['start']['start_time'] = microtime(true);

			if($total_count > 0) $this->_checkpoint[$id]['start']['total_count'] = $total_count;
			if ($comment != "") $this->_checkpoint[$id]['start']['comment'] = $comment;
 		}
		
		return $id;
	}
	
	/**
	 * log result of benchmark
	 */
	public function result() {
		
		$filename = "benchmark-processes-client-" . $this->_client_id . ".log";
		$export_filename = APPLICATION_PATH . "/data/logs/export_proces_benchmark.csv";
		$cpu = $this->_returnCpuUsage();
		$mem = $this->_returnMemoryPeakUsage();
		$runtime = $this->_returnRunTime();
		
// 		$head = "comment\tcpu total\tcpu system\tcpu user\tmemory usage\tmemory unit\ttotal time\n";
// 		error_log(print_r($head, true), 3, $export_filename);
		
		
		$log = "\nClient " . $this->_client_id . " script " . $this->_process_name . " start at " . $this->_start . "\n\tCPU usage "  . $this->_formatCpu($cpu) . "\n\tMem peak usage\t=>\t" . $this->_formatMem($mem) . "\tCompleted in\t=>\t" . $this->_returnRunTime() . "\tmicroseconds.\n";
		$exe = $this->_start . "\t" . $this->_client_id . "\t" . $this->_process_name . " start at " . $this->_start . "\t" . $this->_exportCpu($cpu) . "\t" . $this->_exportMem($mem) . "\t" . $this->_returnRunTime() . "\t0\t0\n";  

		error_log(print_r($exe, true), 3, $export_filename);
 		//error_log(print_r($log, true), 3, "/tmp/" . $filename);
		
		foreach ($this->_checkpoint as $id => $chk) {

			$log = "";
			$exe = "";
			
			if(array_key_exists('start', $chk)) {
				$log .= "\n\tCheckpoint " . $chk['checkpoint_id'] . "\t";
				$exe .= $this->_start . "\t" .$this->_client_id . "\t" . "Checkpoint " . $chk['checkpoint_id'] . " ";
								
				if (array_key_exists('comment', $chk['start'])) {
					$log .= $chk['start']['comment'];
					$exe .= $chk['start']['comment'] . "\t";
				} else {
					$exe .= "\t";
				}
				
				$time = $chk['start']['total_time'];
				
				$log .= "\n\t\tCpu usage\t" . $this->_formatCpu($chk['start']['cpu_usage']) . "\n\t\t" . $this->_formatMem($chk['start']['mem_usage']) . "\n\t\tTotal time\t" . $time;
				$exe .= $this->_exportCpu($chk['start']['cpu_usage']) . "\t" . $this->_exportMem($chk['start']['mem_usage']) . "\t" . $time . "\t" . 0; 
				
				if(array_key_exists('total_count', $chk['start'])) {
					$log .= "\n\t\tTotal rows\t" . $chk['start']['total_count'];
					$exe .= "\t". $chk['start']['total_count'];
				}
				
				$exe .= "\n";
			}
			
			if(array_key_exists('end', $chk)) {
				$log .= "\n\tCheckpoint " . $chk['checkpoint_id'] . "\t";
				$exe .= $this->_start . "\t" .$this->_client_id . "\t" . "Checkpoint " . $chk['checkpoint_id'] . " ";
				
				if (array_key_exists('comment', $chk['end'])) {
					$log .= $chk['end']['comment'];
					$exe .= $chk['end']['comment'] . "\t";
				} else {
					$exe .= "\t";
				}
				
				$time = $chk['end']['total_time'];
				
				$log .= "\n\t\tCpu usage\t" . $this->_formatCpu($chk['end']['cpu_usage']) . "\n\t\t" . $this->_formatMem($chk['end']['mem_usage']) . "\n\t\tTime time\t" . $time . "\n\t\tExecution time\t" . $chk['end']['execution'];
				$exe .= $this->_exportCpu($chk['end']['cpu_usage']) . "\t" . $this->_exportMem($chk['end']['mem_usage']) . "\t" . $time . "\t" . $chk['end']['execution'];
				
				if(array_key_exists('total_count', $chk['end'])) {
					$log .= "\n\t\tTotal rows\t" . $chk['end']['total_count'];
					$exe .= "\t". $chk['end']['total_count'];
				}
				
				$exe .= "\n";
			}
			
			error_log(print_r($exe, true), 3, $export_filename);
 			//error_log(print_r($log, true), 3, "/tmp/" . $filename);
		}
	}
	
	private function _formatCpu($cpu) {
		return "TOTAL\t=>\t" . $cpu['total'] ."\tSYSTEM\t=>\t".$cpu['sys']."\tUSER\t=>\t".$cpu['user'];
	}
	
	
	private function _exportCpu($cpu) {
		return $cpu['total'] . "\t" . $cpu['sys'] . "\t" . $cpu['user'];
	}
	
	private function _formatMem($mem) {
		return "Memory usage\t=>\t" . $mem['usage'] . "\t" . $mem['unit'];
	}
	
	private function _exportMem($mem) {
		return $mem['usage'] . "\t" . $mem['unit'];
	}
	
	private function _returnMemoryUsage() {
		$mem_usage = memory_get_usage(true);
		
		$mem = array();
		
		if ($mem_usage < 1024) {
			$mem['usage'] = $mem_usage;
			$mem['unit'] = "bytes";
		}
		elseif ($mem_usage < 1048576) {
			$mem['usage'] = round($mem_usage/1024,2);
			$mem['unit'] = "kilobytes";
		}
		else {
			$mem['usage'] = round($mem_usage/1048576,2);
			$mem['unit'] = "megabytes";
		}
		
		return $mem;
	}
	
	private function _returnMemoryPeakUsage() {
		
		$mem_usage = memory_get_peak_usage(true);
		
		$mem = array();
		
        if ($mem_usage < 1024) {
            $mem['usage'] = $mem_usage;
            $mem['unit'] = "bytes";
        }
        elseif ($mem_usage < 1048576) {
            $mem['usage'] = round($mem_usage/1024,2);
        	$mem['unit'] = "kilobytes";
        }
        else {
            $mem['usage'] = round($mem_usage/1048576,2);
        	$mem['unit'] = "megabytes";
        }
        
        return $mem;
	}
	
	private function _returnCpuUsage() {
		$dat = getrusage();
		$user = ($dat["ru_utime.tv_sec"]*1e6 + $dat["ru_utime.tv_usec"]) - $this->_user;
		$time = (microtime(true) - $this->_microtime) * 1000000;
		
		$cpu = array();
		
		// cpu per request
		if($time > 0) {
			$cpu['user'] = sprintf("%01.2f", ($user / $time) * 100);
			$cpu['sys'] = sprintf("%01.2f", (($dat["ru_stime.tv_sec"]*1e6 + $dat["ru_stime.tv_usec"] - $this->_system) / $time) * 100);
			$cpu['total'] = $cpu['user'] + $cpu['sys'];
		} else {
			$cpu['total'] = '0.00';
			$cpu['sys'] = '0.00';
			$cpu['user'] = '0.00';
		}
	
		return $cpu;
	}	
	
	private function _returnRunTime() {
		return microtime(true) - $this->_microtime;
	}
}