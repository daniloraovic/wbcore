<?php

class My_Communication_SmsCommandFactory {
	public static function getCommand($params = array()) {
		$interpreter = new My_Communication_SmsCommandInterpreter();
		$interpreter->setInstruction($params['content'])
					->execute();
	}
}