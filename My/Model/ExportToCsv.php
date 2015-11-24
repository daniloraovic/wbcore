<?php
class My_Model_ExportToCsv extends My_Model_Abstract {


	public function export($file, array $collection = array(), array $fieldsList = array(), array $head = array()) {
		
		if(empty($file)) {
			throw new Exception("Filename or file path must not be empty.");
		}
		
// 		if(empty($collection)) {
// 			throw new Exception("There are no data to export.");
// 		}
		
		if(empty($fieldsList) && empty($head)) {
			throw new Exception("List of fields for export must not be empty.");
		}
		
		$fp = fopen($file, 'w');
				
		fputcsv($fp, empty($head) ? $fieldsList : $head);
		
		foreach ($collection as $field) {
			$row = array();
			
			if (!empty($fieldsList)) {
				foreach ($fieldsList as $index) {
					$row[] = array_key_exists($index, $field) ? $field[$index] : null;
				}
			} else {
				$row = $field;
			}
			
			
			fputcsv($fp, $row);
		}
		
				
		fclose($fp);
	}
}