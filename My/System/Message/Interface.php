<?php
interface My_System_Message_Interface
{

    const SUCCESS       = 1;
    const ATTENTION     = 2;
    const ERROR         = 3;
    const INFORMATION   = 4;
    const DEFAULT_TYPE  = self::INFORMATION;

    function getMessageCode();
}