<?php

// Enable log files if true
function isLogEnabled()
{
  return true;
}

// False to use session, True to get params from url 
function isConfigForTesting() {
  return true;
}

// Skip the time token cript if true
function isLocal() {
  return true;
}

?>