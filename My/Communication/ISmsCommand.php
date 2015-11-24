<?php
interface My_Communication_ISmsCommand {
	public function getMessage();
	public function getIdClient();
	public function authorize();
	public function run();
}