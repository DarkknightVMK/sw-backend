<?php

class secretquestion
{
  function getSecretQuestions()
  {
    $amf = new stdClass();
    $amf->success=true;
    return $amf;
  }
}