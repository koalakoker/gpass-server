<?php

// Enable log files if true
function isLogEnabled()
{
  return false;
}

// False to use session, True to get params from url 
function isConfigForTesting() {
  return false;
}

// Skip the time token cript if true
function isLocal() {
  return false;
}

?>