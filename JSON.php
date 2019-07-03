<?php

function unichr($unicode){
    return mb_convert_encoding("&#{$unicode};", 'UTF-8', 'HTML-ENTITIES');
}
function uniord($s) {
    return unpack('V', iconv('UTF-8', 'UCS-4LE', $s))[1];
}

function IsValidJSON(&$json, $errorMessage){

  $elementReference = new stdClass();

  $success = ReadJSON($json, $elementReference, $errorMessage);

  if($success){
    DeleteElement($elementReference->element);
  }

  return $success;
}
function JSONTokenize(&$string, $tokenArrayReference, $errorMessages){

  $count = CreateNumberReference(0.0);
  $success = JSONTokenizeWithCountOption($string, $tokenArrayReference, $count, false, $errorMessages);

  if($success){
    $tokenArrayReference->array = array_fill(0, $count->numberValue, 0);
    JSONTokenizeWithCountOption($string, $tokenArrayReference, $count, true, $errorMessages);
  }

  return $success;
}
function JSONTokenizeWithCountOption(&$json, $tokenArrayReference, $countReference, $add, $errorMessages){

  $success = true;

  $tokenReference = new stdClass();
  $countReference->numberValue = 0.0;

  $tokens = $tokenArrayReference->array;
  $stringLength = new stdClass();
  $t = 0.0;

  for($i = 0.0; $i < count($json) && $success; ){
    $c = $json[$i];

    if($c == "{"){
      if($add){
        $tokens[$t] = CreateToken(GetTokenType($literal = str_split("openCurly")));
        $t = $t + 1.0;
      }else{
        $countReference->numberValue = $countReference->numberValue + 1.0;
      }
      $i = $i + 1.0;
    }else if($c == "}"){
      if($add){
        $tokens[$t] = CreateToken(GetTokenType($literal = str_split("closeCurly")));
        $t = $t + 1.0;
      }else{
        $countReference->numberValue = $countReference->numberValue + 1.0;
      }
      $i = $i + 1.0;
    }else if($c == "["){
      if($add){
        $tokens[$t] = CreateToken(GetTokenType($literal = str_split("openSquare")));
        $t = $t + 1.0;
      }else{
        $countReference->numberValue = $countReference->numberValue + 1.0;
      }
      $i = $i + 1.0;
    }else if($c == "]"){
      if($add){
        $tokens[$t] = CreateToken(GetTokenType($literal = str_split("closeSquare")));
        $t = $t + 1.0;
      }else{
        $countReference->numberValue = $countReference->numberValue + 1.0;
      }
      $i = $i + 1.0;
    }else if($c == ":"){
      if($add){
        $tokens[$t] = CreateToken(GetTokenType($literal = str_split("colon")));
        $t = $t + 1.0;
      }else{
        $countReference->numberValue = $countReference->numberValue + 1.0;
      }
      $i = $i + 1.0;
    }else if($c == ","){
      if($add){
        $tokens[$t] = CreateToken(GetTokenType($literal = str_split("comma")));
        $t = $t + 1.0;
      }else{
        $countReference->numberValue = $countReference->numberValue + 1.0;
      }
      $i = $i + 1.0;
    }else if($c == "f"){
      $success = GetJSONPrimitiveName($json, $i, $errorMessages, $literal = str_split("false"), $tokenReference);
      if($success){
        if($add){
          $tokens[$t] = $tokenReference->token;
          $t = $t + 1.0;
        }else{
          $countReference->numberValue = $countReference->numberValue + 1.0;
        }
        $i = $i + count(str_split("false"));
      }
    }else if($c == "t"){
      $success = GetJSONPrimitiveName($json, $i, $errorMessages, $literal = str_split("true"), $tokenReference);
      if($success){
        if($add){
          $tokens[$t] = $tokenReference->token;
          $t = $t + 1.0;
        }else{
          $countReference->numberValue = $countReference->numberValue + 1.0;
        }
        $i = $i + count(str_split("true"));
      }
    }else if($c == "n"){
      $success = GetJSONPrimitiveName($json, $i, $errorMessages, $literal = str_split("null"), $tokenReference);
      if($success){
        if($add){
          $tokens[$t] = $tokenReference->token;
          $t = $t + 1.0;
        }else{
          $countReference->numberValue = $countReference->numberValue + 1.0;
        }
        $i = $i + count(str_split("null"));
      }
    }else if($c == " " || $c == "\n" || $c == "\t" || $c == "\r"){
      /* Skip. */
      $i = $i + 1.0;
    }else if($c == "\""){
      $success = GetJSONString($json, $i, $tokenReference, $stringLength, $errorMessages);
      if($success){
        $i = $i + $stringLength->numberValue;
        if($add){
          $tokens[$t] = $tokenReference->token;
          $t = $t + 1.0;
        }else{
          $countReference->numberValue = $countReference->numberValue + 1.0;
        }
      }
    }else if(IsJSONNumberCharacter($c)){
      $success = GetJSONNumberToken($json, $i, $tokenReference, $errorMessages);
      if($success){
        $numberToken = $tokenReference->token;
        $i = $i + count($numberToken->value);
        if($add){
          $tokens[$t] = $numberToken;
          $t = $t + 1.0;
        }else{
          $countReference->numberValue = $countReference->numberValue + 1.0;
        }
      }
    }else{
      $str = strConcatenateCharacter($literal = str_split("Invalid start of Token: "), $c);
      $stringReference = CreateStringReference($str);
      AddStringRef($errorMessages, $stringReference);
      $i = $i + 1.0;
      $success = false;
    }
  }

  if($success){
    if($add){
      $tokens[$t] = CreateToken(GetTokenType($literal = str_split("end")));
      $t = $t + 1.0;
    }else{
      $countReference->numberValue = $countReference->numberValue + 1.0;
    }
    $tokenArrayReference->array = $tokens;
  }

  return $success;
}
function GetJSONNumberToken(&$json, $start, $tokenReference, $errorMessages){

  $end = count($json);
  $done = false;

  for($i = $start; $i < count($json) &&  !$done ; $i = $i + 1.0){
    $c = $json[$i];
    if( !IsJSONNumberCharacter($c) ){
      $done = true;
      $end = $i;
    }
  }

  $numberString = strSubstring($json, $start, $end);

  $success = IsValidJSONNumber($numberString, $errorMessages);

  $tokenReference->token = CreateNumberToken($numberString);

  return $success;
}
function IsValidJSONNumber(&$n, $errorMessages){

  $i = 0.0;

  /* JSON allows an optional negative sign. */
  if($n[$i] == "-"){
    $i = $i + 1.0;
  }

  if($i < count($n)){
    $success = IsValidJSONNumberAfterSign($n, $i, $errorMessages);
  }else{
    $success = false;
    AddStringRef($errorMessages, CreateStringReference($literal = str_split("Number must contain at least one digit.")));
  }

  return $success;
}
function IsValidJSONNumberAfterSign(&$n, $i, $errorMessages){

  if(charIsNumber($n[$i])){
    /* 0 first means only 0. */
    if($n[$i] == "0"){
      $i = $i + 1.0;
    }else{
      /* 1-9 first, read following digits. */
      $i = IsValidJSONNumberAdvancePastDigits($n, $i);
    }

    if($i < count($n)){
      $success = IsValidJSONNumberFromDotOrExponent($n, $i, $errorMessages);
    }else{
      /* If integer, we are done now. */
      $success = true;
    }
  }else{
    $success = false;
    AddStringRef($errorMessages, CreateStringReference($literal = str_split("A number must start with 0-9 (after the optional sign).")));
  }

  return $success;
}
function IsValidJSONNumberAdvancePastDigits(&$n, $i){

  $i = $i + 1.0;
  $done = false;
  for(; $i < count($n) &&  !$done ; ){
    if(charIsNumber($n[$i])){
      $i = $i + 1.0;
    }else{
      $done = true;
    }
  }

  return $i;
}
function IsValidJSONNumberFromDotOrExponent(&$n, $i, $errorMessages){

  $wasDotAndOrE = false;
  $success = true;

  if($n[$i] == "."){
    $i = $i + 1.0;
    $wasDotAndOrE = true;

    if($i < count($n)){
      if(charIsNumber($n[$i])){
        /* Read digits following decimal point. */
        $i = IsValidJSONNumberAdvancePastDigits($n, $i);

        if($i == count($n)){
          /* If non-scientific decimal number, we are done. */
          $success = true;
        }
      }else{
        $success = false;
        AddStringRef($errorMessages, CreateStringReference($literal = str_split("There must be numbers after the decimal point.")));
      }
    }else{
      $success = false;
      AddStringRef($errorMessages, CreateStringReference($literal = str_split("There must be numbers after the decimal point.")));
    }
  }

  if($i < count($n) && $success){
    if($n[$i] == "e" || $n[$i] == "E"){
      $wasDotAndOrE = true;
      $success = IsValidJSONNumberFromExponent($n, $i, $errorMessages);
    }else{
      $success = false;
      AddStringRef($errorMessages, CreateStringReference($literal = str_split("Expected e or E.")));
    }
  }else if($i == count($n) && $success){
    /* If number with decimal point. */
    $success = true;
  }else{
    $success = false;
    AddStringRef($errorMessages, CreateStringReference($literal = str_split("There must be numbers after the decimal point.")));
  }

  if($wasDotAndOrE){
  }else{
    $success = false;
    AddStringRef($errorMessages, CreateStringReference($literal = str_split("Exprected decimal point or e or E.")));
  }

  return $success;
}
function IsValidJSONNumberFromExponent(&$n, $i, $errorMessages){

  $i = $i + 1.0;

  if($i < count($n)){
    /* The exponent sign can either + or -, */
    if($n[$i] == "+" || $n[$i] == "-"){
      $i = $i + 1.0;
    }

    if($i < count($n)){
      if(charIsNumber($n[$i])){
        /* Read digits following decimal point. */
        $i = IsValidJSONNumberAdvancePastDigits($n, $i);

        if($i == count($n)){
          /* We found scientific number. */
          $success = true;
        }else{
          $success = false;
          AddStringRef($errorMessages, CreateStringReference($literal = str_split("There was characters following the exponent.")));
        }
      }else{
        $success = false;
        AddStringRef($errorMessages, CreateStringReference($literal = str_split("There must be a digit following the optional exponent sign.")));
      }
    }else{
      $success = false;
      AddStringRef($errorMessages, CreateStringReference($literal = str_split("There must be a digit following optional the exponent sign.")));
    }
  }else{
    $success = false;
    AddStringRef($errorMessages, CreateStringReference($literal = str_split("There must be a sign or a digit following e or E.")));
  }

  return $success;
}
function IsJSONNumberCharacter($c){

  $numericCharacters = str_split("0123456789.-+eE");

  $found = false;

  for($i = 0.0; $i < count($numericCharacters); $i = $i + 1.0){
    if($numericCharacters[$i] == $c){
      $found = true;
    }
  }

  return $found;
}
function GetJSONPrimitiveName(&$string, $start, $errorMessages, &$primitive, $tokenReference){

  $token = new stdClass();
  $done = false;
  $success = true;

  for($i = $start; $i < count($string) && (($i - $start) < count($primitive)) &&  !$done ; $i = $i + 1.0){
    $c = $string[$i];
    $p = $primitive[$i - $start];
    if($c == $p){
      /* OK */
      if(($i + 1.0 - $start) == count($primitive)){
        $done = true;
      }
    }else{
      $str = array();
      $str = strConcatenateString($str, $literal = str_split("Primitive invalid: "));
      $str = strAppendCharacter($str, $c);
      $str = strAppendString($str, $literal = str_split(" vs "));
      $str = strAppendCharacter($str, $p);

      AddStringRef($errorMessages, CreateStringReference($str));
      $done = true;
      $success = false;
    }
  }

  if($done){
    if(StringsEqual($primitive, $literal = str_split("false"))){
      $token = CreateToken(GetTokenType($literal = str_split("falseValue")));
    }
    if(StringsEqual($primitive, $literal = str_split("true"))){
      $token = CreateToken(GetTokenType($literal = str_split("trueValue")));
    }
    if(StringsEqual($primitive, $literal = str_split("null"))){
      $token = CreateToken(GetTokenType($literal = str_split("nullValue")));
    }
  }else{
    AddStringRef($errorMessages, CreateStringReference($literal = str_split("Primitive invalid")));
    $success = false;
  }

  $tokenReference->token = $token;

  return $success;
}
function GetJSONString(&$json, $start, $tokenReference, $stringLengthReference, $errorMessages){

  $characterCount = CreateNumberReference(0.0);
  $hex = CreateString(4.0, "0");
  $hexReference = new stdClass();
  $errorMessage = new stdClass();

  $success = IsValidJSONStringInJSON($json, $start, $characterCount, $stringLengthReference, $errorMessages);

  if($success){
    $l = $characterCount->numberValue;
    $string = array_fill(0, $l, 0);

    $c = 0.0;
    $string[$c] = "\"";
    $c = $c + 1.0;

    $done = false;
    for($i = $start + 1.0;  !$done ; $i = $i + 1.0){
      if($json[$i] == "\\"){
        $i = $i + 1.0;
        if($json[$i] == "\"" || $json[$i] == "\\" || $json[$i] == "/"){
          $string[$c] = $json[$i];
          $c = $c + 1.0;
        }else if($json[$i] == "b"){
          $string[$c] = unichr(8.0);
          $c = $c + 1.0;
        }else if($json[$i] == "f"){
          $string[$c] = unichr(12.0);
          $c = $c + 1.0;
        }else if($json[$i] == "n"){
          $string[$c] = unichr(10.0);
          $c = $c + 1.0;
        }else if($json[$i] == "r"){
          $string[$c] = unichr(13.0);
          $c = $c + 1.0;
        }else if($json[$i] == "t"){
          $string[$c] = unichr(9.0);
          $c = $c + 1.0;
        }else if($json[$i] == "u"){
          $i = $i + 1.0;
          $hex[0.0] = charToUpperCase($json[$i + 0.0]);
          $hex[1.0] = charToUpperCase($json[$i + 1.0]);
          $hex[2.0] = charToUpperCase($json[$i + 2.0]);
          $hex[3.0] = charToUpperCase($json[$i + 3.0]);
          nCreateNumberFromStringWithCheck($hex, 16.0, $hexReference, $errorMessage);
          $string[$c] = unichr($hexReference->numberValue);
          $i = $i + 3.0;
          $c = $c + 1.0;
        }
      }else if($json[$i] == "\""){
        $string[$c] = $json[$i];
        $c = $c + 1.0;
        $done = true;
      }else{
        $string[$c] = $json[$i];
        $c = $c + 1.0;
      }
    }

    $tokenReference->token = CreateStringToken($string);
    $success = true;
  }else{
    AddStringRef($errorMessages, CreateStringReference($literal = str_split("End of string was not found.")));
    $success = false;
  }

  return $success;
}
function IsValidJSONString(&$jsonString, $errorMessages){

  $numberReference = new stdClass();
  $stringLength = new stdClass();

  $valid = IsValidJSONStringInJSON($jsonString, 0.0, $numberReference, $stringLength, $errorMessages);

  return $valid;
}
function IsValidJSONStringInJSON(&$json, $start, $characterCount, $stringLengthReference, $errorMessages){

  $success = true;
  $done = false;

  $characterCount->numberValue = 1.0;

  for($i = $start + 1.0; $i < count($json) &&  !$done  && $success; $i = $i + 1.0){
    if( !IsJSONIllegalControllCharacter($json[$i]) ){
      if($json[$i] == "\\"){
        $i = $i + 1.0;
        if($i < count($json)){
          if($json[$i] == "\"" || $json[$i] == "\\" || $json[$i] == "/" || $json[$i] == "b" || $json[$i] == "f" || $json[$i] == "n" || $json[$i] == "r" || $json[$i] == "t"){
            $characterCount->numberValue = $characterCount->numberValue + 1.0;
          }else if($json[$i] == "u"){
            if($i + 4.0 < count($json)){
              for($j = 0.0; $j < 4.0 && $success; $j = $j + 1.0){
                $c = $json[$i + $j + 1.0];
                if(nCharacterIsNumberCharacterInBase($c, 16.0) || $c == "a" || $c == "b" || $c == "c" || $c == "d" || $c == "e" || $c == "f"){
                }else{
                  $success = false;
                  AddStringRef($errorMessages, CreateStringReference($literal = str_split("\\u must be followed by four hexadecimal digits.")));
                }
              }
              $characterCount->numberValue = $characterCount->numberValue + 1.0;
              $i = $i + 4.0;
            }else{
              $success = false;
              AddStringRef($errorMessages, CreateStringReference($literal = str_split("\\u must be followed by four characters.")));
            }
          }else{
            $success = false;
            AddStringRef($errorMessages, CreateStringReference($literal = str_split("Escaped character invalid.")));
          }
        }else{
          $success = false;
          AddStringRef($errorMessages, CreateStringReference($literal = str_split("There must be at least two character after string escape.")));
        }
      }else if($json[$i] == "\""){
        $characterCount->numberValue = $characterCount->numberValue + 1.0;
        $done = true;
      }else{
        $characterCount->numberValue = $characterCount->numberValue + 1.0;
      }
    }else{
      $success = false;
      AddStringRef($errorMessages, CreateStringReference($literal = str_split("Unicode code points 0-31 not allowed in JSON string.")));
    }
  }

  if($done){
    $stringLengthReference->numberValue = $i - $start;
  }else{
    $success = false;
    AddStringRef($errorMessages, CreateStringReference($literal = str_split("String must end with \".")));
  }

  return $success;
}
function IsJSONIllegalControllCharacter($c){

  $cnr = uniord($c);

  if($cnr >= 0.0 && $cnr < 32.0){
    $isControll = true;
  }else{
    $isControll = false;
  }

  return $isControll;
}
function CreateToken($tokenType){
  $token = new stdClass();
  $token->type = $tokenType;
  return $token;
}
function CreateStringToken(&$string){
  $token = new stdClass();
  $token->type = GetTokenType($literal = str_split("string"));
  $token->value = $string;
  return $token;
}
function CreateNumberToken(&$string){
  $token = new stdClass();
  $token->type = GetTokenType($literal = str_split("number"));
  $token->value = $string;
  return $token;
}
function &AddElement(&$list, $a){

  $newlist = array_fill(0, count($list) + 1.0, 0);

  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    $newlist[$i] = $list[$i];
  }
  $newlist[count($list)] = $a;

  unset($list);

  return $newlist;
}
function AddElementRef($list, $i){
  $list->array = AddElement($list->array, $i);
}
function &RemoveElement(&$list, $n){

  $newlist = array_fill(0, count($list) - 1.0, 0);

  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    if($i < $n){
      $newlist[$i] = $list[$i];
    }
    if($i > $n){
      $newlist[$i - 1.0] = $list[$i];
    }
  }

  unset($list);

  return $newlist;
}
function GetElementRef($list, $i){
  return $list->array[$i];
}
function RemoveElementRef($list, $i){
  $list->array = RemoveElement($list->array, $i);
}
function ComputeJSONStringLength($element){

  $result = 0.0;

  if(ElementTypeEnumEquals($element->type->name, $literal = str_split("object"))){
    $result = $result + ComputeJSONObjectStringLength($element);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("string"))){
    $result = JSONEscapedStringLength($element->string) + 2.0;
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("array"))){
    $result = $result + ComputeJSONArrayStringLength($element);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("number"))){
    $result = $result + ComputerJSONNumberStringLength($element);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("nullValue"))){
    $result = $result + count(str_split("null"));
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("booleanValue"))){
    $result = $result + ComputeJSONBooleanStringLength($element);
  }

  return $result;
}
function ComputeJSONBooleanStringLength($element){

  if($element->booleanValue){
    $result = count(str_split("true"));
  }else{
    $result = count(str_split("false"));
  }

  return $result;
}
function ComputerJSONNumberStringLength($element){

  if(abs($element->number) >= 10.0**15.0 || abs($element->number) <= 10.0**(-15.0)){
    $length = count(nCreateStringScientificNotationDecimalFromNumber($element->number));
  }else{
    $length = count(nCreateStringDecimalFromNumber($element->number));
  }

  return $length;
}
function ComputeJSONArrayStringLength($element){

  $length = 1.0;

  for($i = 0.0; $i < count($element->array); $i = $i + 1.0){
    $arrayElement = $element->array[$i];

    $length = $length + ComputeJSONStringLength($arrayElement);

    if($i + 1.0 != count($element->array)){
      $length = $length + 1.0;
    }
  }

  $length = $length + 1.0;

  return $length;
}
function ComputeJSONObjectStringLength($element){

  $length = 1.0;

  $keys = GetStringElementMapKeySet($element->object);
  for($i = 0.0; $i < count($keys->stringArray); $i = $i + 1.0){
    $key = $keys->stringArray[$i]->string;
    $objectElement = GetObjectValue($element->object, $key);

    $length = $length + 1.0;
    $length = $length + JSONEscapedStringLength($key);
    $length = $length + 1.0;
    $length = $length + 1.0;

    $length = $length + ComputeJSONStringLength($objectElement);

    if($i + 1.0 != count($keys->stringArray)){
      $length = $length + 1.0;
    }
  }

  $length = $length + 1.0;

  return $length;
}
function CreateStringElement(&$string){
  $element = new stdClass();
  $element->type = GetElementType($literal = str_split("string"));
  $element->string = $string;
  return $element;
}
function CreateBooleanElement($booleanValue){
  $element = new stdClass();
  $element->type = GetElementType($literal = str_split("booleanValue"));
  $element->booleanValue = $booleanValue;
  return $element;
}
function CreateNullElement(){
  $element = new stdClass();
  $element->type = GetElementType($literal = str_split("nullValue"));
  return $element;
}
function CreateNumberElement($number){
  $element = new stdClass();
  $element->type = GetElementType($literal = str_split("number"));
  $element->number = $number;
  return $element;
}
function CreateArrayElement($length){
  $element = new stdClass();
  $element->type = GetElementType($literal = str_split("array"));
  $element->array = array_fill(0, $length, 0);
  return $element;
}
function CreateObjectElement($length){
  $element = new stdClass();
  $element->type = GetElementType($literal = str_split("object"));
  $element->object = new stdClass();
  $element->object->stringListRef = CreateStringArrayReferenceLengthValue($length, $literal = array());
  $element->object->elementListRef = new stdClass();
  $element->object->elementListRef->array = array_fill(0, $length, 0);
  return $element;
}
function DeleteElement($element){
  if(ElementTypeEnumEquals($element->type->name, $literal = str_split("object"))){
    DeleteObject($element);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("string"))){
    unset($element);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("array"))){
    DeleteArray($element);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("number"))){
    unset($element);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("nullValue"))){
    unset($element);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("booleanValue"))){
    unset($element);
  }else{
  }
}
function DeleteObject($element){

  $keys = GetStringElementMapKeySet($element->object);
  for($i = 0.0; $i < count($keys->stringArray); $i = $i + 1.0){
    $key = $keys->stringArray[$i]->string;
    $objectElement = GetObjectValue($element->object, $key);
    DeleteElement($objectElement);
  }
}
function DeleteArray($element){

  for($i = 0.0; $i < count($element->array); $i = $i + 1.0){
    $arrayElement = $element->array[$i];
    DeleteElement($arrayElement);
  }
}
function &WriteJSON($element){

  $length = ComputeJSONStringLength($element);
  $result = array_fill(0, $length, 0);
  $index = CreateNumberReference(0.0);

  if(ElementTypeEnumEquals($element->type->name, $literal = str_split("object"))){
    WriteObject($element, $result, $index);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("string"))){
    WriteString($element, $result, $index);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("array"))){
    WriteArray($element, $result, $index);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("number"))){
    WriteNumber($element, $result, $index);
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("nullValue"))){
    strWriteStringToStingStream($result, $index, $literal = str_split("null"));
  }else if(ElementTypeEnumEquals($element->type->name, $literal = str_split("booleanValue"))){
    WriteBooleanValue($element, $result, $index);
  }

  return $result;
}
function WriteBooleanValue($element, &$result, $index){
  if($element->booleanValue){
    strWriteStringToStingStream($result, $index, $literal = str_split("true"));
  }else{
    strWriteStringToStingStream($result, $index, $literal = str_split("false"));
  }
}
function WriteNumber($element, &$result, $index){

  if(abs($element->number) >= 10.0**15.0 || abs($element->number) <= 10.0**(-15.0)){
    $numberString = nCreateStringScientificNotationDecimalFromNumber($element->number);
  }else{
    $numberString = nCreateStringDecimalFromNumber($element->number);
  }

  strWriteStringToStingStream($result, $index, $numberString);
}
function WriteArray($element, &$result, $index){

  strWriteStringToStingStream($result, $index, $literal = str_split("["));

  for($i = 0.0; $i < count($element->array); $i = $i + 1.0){
    $arrayElement = $element->array[$i];

    $s = WriteJSON($arrayElement);
    strWriteStringToStingStream($result, $index, $s);

    if($i + 1.0 != count($element->array)){
      strWriteStringToStingStream($result, $index, $literal = str_split(","));
    }
  }

  strWriteStringToStingStream($result, $index, $literal = str_split("]"));
}
function WriteString($element, &$result, $index){
  strWriteStringToStingStream($result, $index, $literal = str_split("\""));
  $element->string = JSONEscapeString($element->string);
  strWriteStringToStingStream($result, $index, $element->string);
  strWriteStringToStingStream($result, $index, $literal = str_split("\""));
}
function &JSONEscapeString(&$string){

  $length = JSONEscapedStringLength($string);

  $ns = array_fill(0, $length, 0);
  $index = CreateNumberReference(0.0);
  $lettersReference = CreateNumberReference(0.0);

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    if(JSONMustBeEscaped($string[$i], $lettersReference)){
      $escaped = JSONEscapeCharacter($string[$i]);
      strWriteStringToStingStream($ns, $index, $escaped);
    }else{
      strWriteCharacterToStingStream($ns, $index, $string[$i]);
    }
  }

  return $ns;
}
function JSONEscapedStringLength(&$string){

  $lettersReference = CreateNumberReference(0.0);
  $length = 0.0;

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    if(JSONMustBeEscaped($string[$i], $lettersReference)){
      $length = $length + $lettersReference->numberValue;
    }else{
      $length = $length + 1.0;
    }
  }
  return $length;
}
function &JSONEscapeCharacter($c){

  $code = uniord($c);

  $q = 34.0;
  $rs = 92.0;
  $s = 47.0;
  $b = 8.0;
  $f = 12.0;
  $n = 10.0;
  $r = 13.0;
  $t = 9.0;

  $hexNumber = new stdClass();

  if($code == $q){
    $escaped = array_fill(0, 2.0, 0);
    $escaped[0.0] = "\\";
    $escaped[1.0] = "\"";
  }else if($code == $rs){
    $escaped = array_fill(0, 2.0, 0);
    $escaped[0.0] = "\\";
    $escaped[1.0] = "\\";
  }else if($code == $s){
    $escaped = array_fill(0, 2.0, 0);
    $escaped[0.0] = "\\";
    $escaped[1.0] = "/";
  }else if($code == $b){
    $escaped = array_fill(0, 2.0, 0);
    $escaped[0.0] = "\\";
    $escaped[1.0] = "b";
  }else if($code == $f){
    $escaped = array_fill(0, 2.0, 0);
    $escaped[0.0] = "\\";
    $escaped[1.0] = "f";
  }else if($code == $n){
    $escaped = array_fill(0, 2.0, 0);
    $escaped[0.0] = "\\";
    $escaped[1.0] = "n";
  }else if($code == $r){
    $escaped = array_fill(0, 2.0, 0);
    $escaped[0.0] = "\\";
    $escaped[1.0] = "r";
  }else if($code == $t){
    $escaped = array_fill(0, 2.0, 0);
    $escaped[0.0] = "\\";
    $escaped[1.0] = "t";
  }else if($code >= 0.0 && $code <= 31.0){
    $escaped = array_fill(0, 6.0, 0);
    $escaped[0.0] = "\\";
    $escaped[1.0] = "u";
    $escaped[2.0] = "0";
    $escaped[3.0] = "0";

    nCreateStringFromNumberWithCheck($code, 16.0, $hexNumber);

    if(count($hexNumber->string) == 1.0){
      $escaped[4.0] = "0";
      $escaped[5.0] = $hexNumber->string[0.0];
    }else if(count($hexNumber->string) == 2.0){
      $escaped[4.0] = $hexNumber->string[0.0];
      $escaped[5.0] = $hexNumber->string[1.0];
    }
  }else{
    $escaped = array_fill(0, 1.0, 0);
    $escaped[0.0] = $c;
  }

  return $escaped;
}
function JSONMustBeEscaped($c, $letters){

  $code = uniord($c);
  $mustBeEscaped = false;

  $q = 34.0;
  $rs = 92.0;
  $s = 47.0;
  $b = 8.0;
  $f = 12.0;
  $n = 10.0;
  $r = 13.0;
  $t = 9.0;

  if($code == $q || $code == $rs || $code == $s || $code == $b || $code == $f || $code == $n || $code == $r || $code == $t){
    $mustBeEscaped = true;
    $letters->numberValue = 2.0;
  }else if($code >= 0.0 && $code <= 31.0){
    $mustBeEscaped = true;
    $letters->numberValue = 6.0;
  }else if($code >= 2.0**16.0){
    $mustBeEscaped = true;
    $letters->numberValue = 6.0;
  }

  return $mustBeEscaped;
}
function WriteObject($element, &$result, $index){

  strWriteStringToStingStream($result, $index, $literal = str_split("{"));

  $keys = GetStringElementMapKeySet($element->object);
  for($i = 0.0; $i < count($keys->stringArray); $i = $i + 1.0){
    $key = $keys->stringArray[$i]->string;
    $key = JSONEscapeString($key);
    $objectElement = GetObjectValue($element->object, $key);

    strWriteStringToStingStream($result, $index, $literal = str_split("\""));
    strWriteStringToStingStream($result, $index, $key);
    strWriteStringToStingStream($result, $index, $literal = str_split("\""));
    strWriteStringToStingStream($result, $index, $literal = str_split(":"));

    $s = WriteJSON($objectElement);
    strWriteStringToStingStream($result, $index, $s);

    if($i + 1.0 != count($keys->stringArray)){
      strWriteStringToStingStream($result, $index, $literal = str_split(","));
    }
  }

  strWriteStringToStingStream($result, $index, $literal = str_split("}"));
}
function ReadJSON(&$string, $elementReference, $errorMessages){

  /* Tokenize. */
  $tokenArrayReference = new stdClass();
  $success = JSONTokenize($string, $tokenArrayReference, $errorMessages);

  if($success){
    /* Parse. */
    $success = GetJSONValue($tokenArrayReference->array, $elementReference, $errorMessages);
  }

  return $success;
}
function GetJSONValue(&$tokens, $elementReference, $errorMessages){

  $i = CreateNumberReference(0.0);
  $counts = CreateNumberArrayReferenceLengthValue(count($tokens), 0.0);

  $success = GetJSONValueWithCheckOption($tokens, $i, 0.0, $elementReference, false, $counts, $errorMessages);

  if($success){
    $i->numberValue = 0.0;
    GetJSONValueWithCheckOption($tokens, $i, 0.0, $elementReference, true, $counts, $errorMessages);
  }

  return $success;
}
function GetJSONValueWithCheckOption(&$tokens, $i, $depth, $elementReference, $add, $counts, $errorMessages){

  $success = true;
  $token = $tokens[$i->numberValue];

  if(TokenTypeEnumEquals($token->type->name, $literal = str_split("openCurly"))){
    $success = GetJSONObject($tokens, $i, $depth + 1.0, $elementReference, $add, $counts, $errorMessages);
  }else if(TokenTypeEnumEquals($token->type->name, $literal = str_split("openSquare"))){
    $success = GetJSONArray($tokens, $i, $depth + 1.0, $elementReference, $add, $counts, $errorMessages);
  }else if(TokenTypeEnumEquals($token->type->name, $literal = str_split("trueValue"))){
    if($add){
      $elementReference->element = CreateBooleanElement(true);
    }
    $i->numberValue = $i->numberValue + 1.0;
  }else if(TokenTypeEnumEquals($token->type->name, $literal = str_split("falseValue"))){
    if($add){
      $elementReference->element = CreateBooleanElement(false);
    }
    $i->numberValue = $i->numberValue + 1.0;
  }else if(TokenTypeEnumEquals($token->type->name, $literal = str_split("nullValue"))){
    if($add){
      $elementReference->element = CreateNullElement();
    }
    $i->numberValue = $i->numberValue + 1.0;
  }else if(TokenTypeEnumEquals($token->type->name, $literal = str_split("number"))){
    if($add){
      $stringToDecimalResult = nCreateNumberFromDecimalString($token->value);
      $elementReference->element = CreateNumberElement($stringToDecimalResult);
    }
    $i->numberValue = $i->numberValue + 1.0;
  }else if(TokenTypeEnumEquals($token->type->name, $literal = str_split("string"))){
    if($add){
      $substr = strSubstring($token->value, 1.0, count($token->value) - 1.0);
      $elementReference->element = CreateStringElement($substr);
    }
    $i->numberValue = $i->numberValue + 1.0;
  }else{
    $str = array();
    $str = strConcatenateString($str, $literal = str_split("Invalid token first in value: "));
    $str = strAppendString($str, $token->type->name);
    AddStringRef($errorMessages, CreateStringReference($str));
    $success = false;
  }

  if($success && $depth == 0.0){
    if(TokenTypeEnumEquals($tokens[$i->numberValue]->type->name, $literal = str_split("end"))){
    }else{
      AddStringRef($errorMessages, CreateStringReference($literal = str_split("The outer value cannot have any tokens following it.")));
      $success = false;
    }
  }

  return $success;
}
function GetJSONObject(&$tokens, $i, $depth, $elementReference, $add, $counts, $errorMessages){

  $countIndex = $i->numberValue;
  if($add){
    $element = CreateObjectElement($counts->numberArray[$countIndex]);
  }else{
    $element = new stdClass();
  }
  $valueReference = new stdClass();
  $success = true;
  $i->numberValue = $i->numberValue + 1.0;
  $index = 0.0;

  if( !TokenTypeEnumEquals($tokens[$i->numberValue]->type->name, $literal = str_split("closeCurly")) ){
    $done = false;

    for(;  !$done  && $success; ){
      $key = $tokens[$i->numberValue];

      if(TokenTypeEnumEquals($key->type->name, $literal = str_split("string"))){
        $i->numberValue = $i->numberValue + 1.0;
        $colon = $tokens[$i->numberValue];
        if(TokenTypeEnumEquals($colon->type->name, $literal = str_split("colon"))){
          $i->numberValue = $i->numberValue + 1.0;
          $success = GetJSONValueWithCheckOption($tokens, $i, $depth, $valueReference, $add, $counts, $errorMessages);

          if($success){
            $value = $valueReference->element;

            $keystring = strSubstring($key->value, 1.0, count($key->value) - 1.0);
            if($add){
              SetStringElementMap($element->object, $index, $keystring, $value);
            }

            $index = $index + 1.0;

            $comma = $tokens[$i->numberValue];
            if(TokenTypeEnumEquals($comma->type->name, $literal = str_split("comma"))){
              /* OK */
              $i->numberValue = $i->numberValue + 1.0;
            }else{
              $done = true;
            }
          }
        }else{
          $str = array();
          $str = strConcatenateString($str, $literal = str_split("Expected colon after key in object: "));
          $str = strAppendString($str, $colon->type->name);
          AddStringRef($errorMessages, CreateStringReference($str));

          $success = false;
          $done = true;
        }
      }else{
        AddStringRef($errorMessages, CreateStringReference($literal = str_split("Expected string as key in object.")));

        $done = true;
        $success = false;
      }
    }
  }

  if($success){
    $closeCurly = $tokens[$i->numberValue];

    if(TokenTypeEnumEquals($closeCurly->type->name, $literal = str_split("closeCurly"))){
      /* OK */
      $elementReference->element = $element;
      $i->numberValue = $i->numberValue + 1.0;
    }else{
      AddStringRef($errorMessages, CreateStringReference($literal = str_split("Expected close curly brackets at end of object value.")));
      $success = false;
    }
  }

  $counts->numberArray[$countIndex] = $index;

  return $success;
}
function GetJSONArray(&$tokens, $i, $depth, $elementReference, $add, $counts, $errorMessages){

  $index = 0.0;
  $countIndex = $i->numberValue;
  $i->numberValue = $i->numberValue + 1.0;

  if($add){
    $element = CreateArrayElement($counts->numberArray[$countIndex]);
  }else{
    $element = new stdClass();
  }
  $valueReference = new stdClass();
  $success = true;

  $nextToken = $tokens[$i->numberValue];

  if( !TokenTypeEnumEquals($nextToken->type->name, $literal = str_split("closeSquare")) ){
    $done = false;
    for(;  !$done  && $success; ){
      $success = GetJSONValueWithCheckOption($tokens, $i, $depth, $valueReference, $add, $counts, $errorMessages);

      if($success){
        $value = $valueReference->element;

        if($add){
          $element->array[$index] = $value;
        }

        $index = $index + 1.0;

        $comma = $tokens[$i->numberValue];

        if(TokenTypeEnumEquals($comma->type->name, $literal = str_split("comma"))){
          /* OK */
          $i->numberValue = $i->numberValue + 1.0;
        }else{
          $done = true;
        }
      }
    }
  }

  $nextToken = $tokens[$i->numberValue];
  if(TokenTypeEnumEquals($nextToken->type->name, $literal = str_split("closeSquare"))){
    /* OK */
    $i->numberValue = $i->numberValue + 1.0;
    $elementReference->element = $element;
  }else{
    AddStringRef($errorMessages, CreateStringReference($literal = str_split("Expected close square bracket at end of array.")));
    $success = false;
  }

  $elementReference->element = $element;
  $counts->numberArray[$countIndex] = $index;

  return $success;
}
function GetStringElementMapKeySet($stringElementMap){
  return $stringElementMap->stringListRef;
}
function GetObjectValue($stringElementMap, &$key){

  $result = new stdClass();

  for($i = 0.0; $i < GetStringElementMapNumberOfKeys($stringElementMap); $i = $i + 1.0){
    if(StringsEqual($stringElementMap->stringListRef->stringArray[$i]->string, $key)){
      $result = $stringElementMap->elementListRef->array[$i];
    }
  }

  return $result;
}
function GetObjectValueWithCheck($stringElementMap, &$key, $foundReference){

  $result = new stdClass();

  $foundReference->booleanValue = false;
  for($i = 0.0; $i < GetStringElementMapNumberOfKeys($stringElementMap); $i = $i + 1.0){
    if(StringsEqual($stringElementMap->stringListRef->stringArray[$i]->string, $key)){
      $result = $stringElementMap->elementListRef->array[$i];
      $foundReference->booleanValue = true;
    }
  }

  return $result;
}
function PutStringElementMap($stringElementMap, &$keystring, $value){
  AddStringRef($stringElementMap->stringListRef, CreateStringReference($keystring));
  AddElementRef($stringElementMap->elementListRef, $value);
}
function SetStringElementMap($stringElementMap, $index, &$keystring, $value){
  $stringElementMap->stringListRef->stringArray[$index]->string = $keystring;
  $stringElementMap->elementListRef->array[$index] = $value;
}
function GetStringElementMapNumberOfKeys($stringElementMap){
  return count($stringElementMap->stringListRef->stringArray);
}
function GetTokenType(&$elementTypeName){

  $et = new stdClass();
  $et->name = $elementTypeName;

  return $et;
}
function GetAndCheckTokenType(&$elementTypeName, $found){

  $count = 12.0;

  $elementTypes = array_fill(0, $count, 0);

  for($i = 0.0; $i < $count; $i = $i + 1.0){
    $elementTypes[$i] = new stdClass();
  }

  $elementTypes[0.0]->name = str_split("openCurly");
  $elementTypes[1.0]->name = str_split("closeCurly");
  $elementTypes[2.0]->name = str_split("openSquare");
  $elementTypes[3.0]->name = str_split("closeSquare");
  $elementTypes[4.0]->name = str_split("comma");
  $elementTypes[5.0]->name = str_split("colon");
  $elementTypes[6.0]->name = str_split("nullValue");
  $elementTypes[7.0]->name = str_split("trueValue");
  $elementTypes[8.0]->name = str_split("falseValue");
  $elementTypes[9.0]->name = str_split("string");
  $elementTypes[10.0]->name = str_split("number");
  $elementTypes[11.0]->name = str_split("end");

  $found->booleanValue = false;
  $tokenType = new stdClass();
  for($i = 0.0; $i < $count &&  !$found->booleanValue ; $i = $i + 1.0){
    $tokenType = $elementTypes[$i];
    if(StringsEqual($tokenType->name, $elementTypeName)){
      $found->booleanValue = true;
    }
  }

  return $tokenType;
}
function TokenTypeEnumStructureEquals($a, $b){
  return StringsEqual($a->name, $b->name);
}
function TokenTypeEnumEquals(&$a, &$b){

  $founda = new stdClass();
  $foundb = new stdClass();

  $eta = GetAndCheckTokenType($a, $founda);
  $etb = GetAndCheckTokenType($b, $foundb);

  if($founda->booleanValue && $foundb->booleanValue){
    $equals = TokenTypeEnumStructureEquals($eta, $etb);
  }else{
    $equals = false;
  }

  return $equals;
}
function JSONCompare(&$a, &$b, $epsilon, $equal, $errorMessage){

  $eaRef = new stdClass();
  $ebRef = new stdClass();

  $success = ReadJSON($a, $eaRef, $errorMessage);

  if($success){
    $ea = $eaRef->element;

    $success = ReadJSON($b, $ebRef, $errorMessage);

    if($success){
      $eb = $ebRef->element;

      $equal->booleanValue = JSONCompareElements($ea, $eb, $epsilon);

      DeleteElement($eb);
    }

    DeleteElement($ea);
  }

  return $success;
}
function JSONCompareElements($ea, $eb, $epsilon){

  $equal = StringsEqual($ea->type->name, $eb->type->name);
        
  if($equal){
    $typeName = $ea->type->name;
    if(ElementTypeEnumEquals($typeName, $literal = str_split("object"))){
      $equal = JSONCompareObjects($ea->object, $eb->object, $epsilon);
    }else if(ElementTypeEnumEquals($typeName, $literal = str_split("string"))){
      $equal = StringsEqual($ea->string, $eb->string);
    }else if(ElementTypeEnumEquals($typeName, $literal = str_split("array"))){
      $equal = JSONCompareArrays($ea->array, $eb->array, $epsilon);
    }else if(ElementTypeEnumEquals($typeName, $literal = str_split("number"))){
      $equal = EpsilonCompare($ea->number, $eb->number, $epsilon);
    }else if(ElementTypeEnumEquals($typeName, $literal = str_split("nullValue"))){
      $equal = true;
    }else if(ElementTypeEnumEquals($typeName, $literal = str_split("booleanValue"))){
      $equal = $ea->booleanValue == $eb->booleanValue;
    }
  }
        
  return $equal;
}
function JSONCompareArrays(&$ea, &$eb, $epsilon){

  $equals = count($ea) == count($eb);

  if($equals){
    $length = count($ea);
    for($i = 0.0; $i < $length && $equals; $i = $i + 1.0){
      $equals = JSONCompareElements($ea[$i], $eb[$i], $epsilon);
    }
  }

  return $equals;
}
function JSONCompareObjects($ea, $eb, $epsilon){

  $aFoundReference = new stdClass();
  $bFoundReference = new stdClass();

  $akeys = GetStringElementMapNumberOfKeys($ea);
  $bkeys = GetStringElementMapNumberOfKeys($eb);

  $equals = $akeys == $bkeys;

  if($equals){
    $keys = GetStringElementMapKeySet($ea);

    for($i = 0.0; $i < count($keys->stringArray) && $equals; $i = $i + 1.0){
      $key = $keys->stringArray[$i]->string;

      $eaValue = GetObjectValueWithCheck($ea, $key, $aFoundReference);
      $ebValue = GetObjectValueWithCheck($eb, $key, $bFoundReference);

      if($aFoundReference->booleanValue && $bFoundReference->booleanValue){
        $equals = JSONCompareElements($eaValue, $ebValue, $epsilon);
      }else{
        $equals = false;
      }
    }
  }

  return $equals;
}
function GetElementType(&$elementTypeName){

  $et = new stdClass();
  $et->name = $elementTypeName;

  return $et;
}
function GetAndCheckElementType(&$elementTypeName, $found){

  $antall = 6.0;

  $elementTypes = array_fill(0, $antall, 0);

  for($i = 0.0; $i < $antall; $i = $i + 1.0){
    $elementTypes[$i] = new stdClass();
  }

  $elementTypes[0.0]->name = str_split("object");
  $elementTypes[1.0]->name = str_split("array");
  $elementTypes[2.0]->name = str_split("string");
  $elementTypes[3.0]->name = str_split("number");
  $elementTypes[4.0]->name = str_split("booleanValue");
  $elementTypes[5.0]->name = str_split("nullValue");

  $found->booleanValue = false;
  $elementType = new stdClass();
  for($i = 0.0; $i < $antall &&  !$found->booleanValue ; $i = $i + 1.0){
    $elementType = $elementTypes[$i];
    if(StringsEqual($elementType->name, $elementTypeName)){
      $found->booleanValue = true;
    }
  }

  return $elementType;
}
function ElementTypeStructureEquals($a, $b){
  return StringsEqual($a->name, $b->name);
}
function ElementTypeEnumEquals(&$a, &$b){

  $founda = new stdClass();
  $foundb = new stdClass();

  $eta = GetAndCheckElementType($a, $founda);
  $etb = GetAndCheckElementType($b, $foundb);

  if($founda->booleanValue && $foundb->booleanValue){
    $equals = ElementTypeStructureEquals($eta, $etb);
  }else{
    $equals = false;
  }

  return $equals;
}
function testEscaper(){

  $failures = CreateNumberReference(0.0);
  $letters = CreateNumberReference(0.0);

  $c = unichr(9.0);
  $mustBeEscaped = JSONMustBeEscaped($c, $letters);
  AssertTrue($mustBeEscaped, $failures);
  AssertEquals($letters->numberValue, 2.0, $failures);

  $escaped = JSONEscapeCharacter($c);
  AssertStringEquals($escaped, $literal = str_split("\\t"), $failures);

  $c = unichr(0.0);
  $mustBeEscaped = JSONMustBeEscaped($c, $letters);
  AssertTrue($mustBeEscaped, $failures);
  AssertEquals($letters->numberValue, 6.0, $failures);

  $escaped = JSONEscapeCharacter($c);
  AssertStringEquals($escaped, $literal = str_split("\\u0000"), $failures);

  return $failures->numberValue;
}
function mapTo($root){

  $example = new stdClass();
  $example->a = GetObjectValue($root->object, $literal = str_split("a"))->string;
  $example->b = mapbTo(GetObjectValue($root->object, $literal = str_split("b"))->array);
  $example->x = mapXTo(GetObjectValue($root->object, $literal = str_split("x"))->object);

  return $example;
}
function mapXTo($object){

  $x = new stdClass();

  if(ElementTypeEnumEquals(GetObjectValue($object, $literal = str_split("x1"))->type->name, $literal = str_split("nullValue"))){
    $x->x1IsNull = true;
    $x->x1 = array();
  }

  $x->x2 = GetObjectValue($object, $literal = str_split("x2"))->booleanValue;
  $x->x3 = GetObjectValue($object, $literal = str_split("x3"))->booleanValue;

  return $x;
}
function &mapbTo(&$array){

  $b = array_fill(0, count($array), 0);

  for($i = 0.0; $i < count($array); $i = $i + 1.0){
    $b[$i] = $array[$i]->number;
  }

  return $b;
}
function testWriter(){

  $failures = CreateNumberReference(0.0);

  $root = createExampleJSON();

  $string = WriteJSON($root);

  AssertEquals(count($string), 66.0, $failures);

  /* Does not work with Java Maps.. */
  $example = mapTo($root);

  AssertStringEquals($literal = str_split("hei"), $example->a, $failures);
  AssertTrue($example->x->x1IsNull, $failures);
  AssertTrue($example->x->x2, $failures);
  AssertFalse($example->x->x3, $failures);
  AssertEquals(1.2, $example->b[0.0], $failures);
  AssertEquals(0.1, $example->b[1.0], $failures);
  AssertEquals(100.0, $example->b[2.0], $failures);

  DeleteElement($root);

  return $failures->numberValue;
}
function createExampleJSON(){

  $root = CreateObjectElement(3.0);

  $innerObject = CreateObjectElement(3.0);

  SetStringElementMap($innerObject->object, 0.0, $literal = str_split("x1"), CreateNullElement());
  SetStringElementMap($innerObject->object, 1.0, $literal = str_split("x2"), CreateBooleanElement(true));
  SetStringElementMap($innerObject->object, 2.0, $literal = str_split("x3"), CreateBooleanElement(false));

  $array = CreateArrayElement(3.0);
  $array->array[0.0] = CreateNumberElement(1.2);
  $array->array[1.0] = CreateNumberElement(0.1);
  $array->array[2.0] = CreateNumberElement(100.0);

  SetStringElementMap($root->object, 0.0, $literal = str_split("a"), CreateStringElement($literal = str_split("hei")));
  SetStringElementMap($root->object, 1.0, $literal = str_split("b"), $array);
  SetStringElementMap($root->object, 2.0, $literal = str_split("x"), $innerObject);

  return $root;
}
function testWriterEscape(){

  $failures = CreateNumberReference(0.0);

  $root = CreateStringElement($literal = str_split("\t\n"));

  $string = WriteJSON($root);

  AssertEquals(count($string), 6.0, $failures);

  AssertStringEquals($literal = str_split("\"\\t\\n\""), $string, $failures);

  DeleteElement($root);

  return $failures->numberValue;
}
function testReader(){

  $failures = CreateNumberReference(0.0);

  $json = createExampleJSON();
  $string = WriteJSON($json);
  $elementReference = new stdClass();

  $errorMessages = CreateStringArrayReferenceLengthValue(0.0, $literal = array());

  $success = ReadJSON($string, $elementReference, $errorMessages);
  AssertTrue($success, $failures);

  if($success){
    $json = $elementReference->element;
    $string2 = WriteJSON($json);

    AssertEquals(count($string), count($string2), $failures);
  }

  return $failures->numberValue;
}
function test2(){

  $failures = CreateNumberReference(0.0);

  $string = strConcatenateString($literal = str_split("{"), $literal = str_split("\"name\":\"base64\","));
  $string = strAppendString($string, $literal = str_split("\"version\":\"0.1.0\","));
  $string = strAppendString($string, $literal = str_split("\"business namespace\":\"no.inductive.idea10.programs\","));
  $string = strAppendString($string, $literal = str_split("\"scientific namespace\":\"computerscience.algorithms.base64\","));
  $string = strAppendString($string, $literal = str_split("\"imports\":["));
  $string = strAppendString($string, $literal = str_split("],"));
  $string = strAppendString($string, $literal = str_split("\"imports2\":{"));
  $string = strAppendString($string, $literal = str_split("},"));
  $string = strAppendString($string, $literal = str_split("\"development imports\":["));
  $string = strAppendString($string, $literal = str_split("[\"\",\"no.inductive.idea10.programs\",\"arrays\",\"0.1.0\"]"));
  $string = strAppendString($string, $literal = str_split("]"));
  $string = strAppendString($string, $literal = str_split("}"));

  $errorMessages = CreateStringArrayReferenceLengthValue(0.0, $literal = array());
  $elementReference = new stdClass();
  $success = ReadJSON($string, $elementReference, $errorMessages);
  AssertTrue($success, $failures);

  if($success){
    $json = $elementReference->element;

    $string2 = WriteJSON($json);

    AssertEquals(count($string), count($string2), $failures);
  }

  return $failures->numberValue;
}
function testReaderExample(){

  $errorMessages = CreateStringArrayReferenceLengthValue(0.0, $literal = array());
  $elementReference = new stdClass();
  $outputJSON = CreateStringReference($literal = array());

  $json = str_split("{\"a\":\"hi\",\"b\":[1.2, 0.1, 100],\"x\":{\"x1\":null,\"x2\":true,\"x3\":false}}");

  JSONExample($json, $errorMessages, $elementReference, $outputJSON);

  return 0.0;
}
function JSONExample(&$json, $errorMessages, $elementReference, $outputJSON){

  /* The following JSON is in the string json:
           {
             "a": "hi",
             "b": [1.2, 0.1, 100],
             "x": {
               "x1": null,
               "x2": true,
               "x3": false
             }
           }
         */

  /* This functions reads the JSON */
  $success = ReadJSON($json, $elementReference, $errorMessages);

  /* The return value 'success' is set to true of the parsing succeeds, */
  /* if not, errorMessages contains the reason. */
  if($success){
    /* We can now extract the data structure: */
    $element = $elementReference->element;

    /* The following is gets the value "hi" for key "a": */
    $aElement = GetObjectValue($element->object, $literal = str_split("a"));
    $string = $aElement->string;

    /* The following is gets the array [1.2, 0.1, 100] for key "b": */
    $aElement = GetObjectValue($element->object, $literal = str_split("b"));
    $array = $aElement->array;
    $x = $array[0.0]->number;
    $y = $array[1.0]->number;
    $z = $array[2.0]->number;

    /* This is how you write JSON: */
    $outputJSON->string = WriteJSON($element);
  }else{
    /* There was a problem, so we cannot read our data structure. */
    /* Instead, we can check out the error message. */
    $string = $errorMessages->stringArray[0.0]->string;
  }
}
function test(){

  $failures = 0.0;

  $failures = $failures + testReader();
  $failures = $failures + test2();
  $failures = $failures + testWriter();
  $failures = $failures + testWriterEscape();
  $failures = $failures + testTokenizer1();
  $failures = $failures + testReaderExample();
  $failures = $failures + testEscaper();
  $failures = $failures + testValidator();
  $failures = $failures + testComparator();

  return $failures;
}
function testValidator(){

  $failures = CreateNumberReference(0.0);
  $errorMessages = CreateStringArrayReferenceLengthValue(0.0, $literal = array());

  $a = str_split("{\"a\":\"hi\",\"b\":[1.2, 0.1, 100],\"x\":{\"x1\":null,\"x2\":true,\"x3\":false}}");
  $b = str_split("{{}}]");

  AssertTrue(IsValidJSON($a, $errorMessages), $failures);
  AssertFalse(IsValidJSON($b, $errorMessages), $failures);

  return $failures->numberValue;
}
function testComparator(){

  $failures = CreateNumberReference(0.0);
  $errorMessages = CreateStringArrayReferenceLengthValue(0.0, $literal = array());
  $equalsReference = CreateBooleanReference(false);

  $a = str_split("{\"a\":\"hi\",\"b\":[1.2, 0.1, 100],\"x\":{\"x1\":null,\"x2\":true,\"x3\":false}}");
  $b = str_split("{\"x\":{\"x1\":null,\"x2\":true,\"x3\":false},\"a\":\"hi\",\"b\":[1.2, 0.1, 100]}");

  $success = JSONCompare($a, $b, 0.0001, $equalsReference, $errorMessages);

  AssertTrue($success, $failures);
  AssertTrue($equalsReference->booleanValue, $failures);

  $a = str_split("{\"a\":\"hi\",\"b\":[1.201, 0.1, 100],\"x\":{\"x1\":null,\"x2\":true,\"x3\":false}}");
  $b = str_split("{\"x\":{\"x1\":null,\"x2\":true,\"x3\":false},\"a\":\"hi\",\"b\":[1.2, 0.1, 100]}");

  $success = JSONCompare($a, $b, 0.0001, $equalsReference, $errorMessages);

  AssertTrue($success, $failures);
  AssertFalse($equalsReference->booleanValue, $failures);

  $success = JSONCompare($a, $b, 0.1, $equalsReference, $errorMessages);

  AssertTrue($success, $failures);
  AssertTrue($equalsReference->booleanValue, $failures);

  return $failures->numberValue;
}
function testTokenizer1(){

  $failures = CreateNumberReference(0.0);
  $countReference = CreateNumberReference(0.0);
  $stringLength = CreateNumberReference(0.0);
  $errorMessages = CreateStringArrayReferenceLengthValue(0.0, $literal = array());

  $tokenArrayReference = new stdClass();

  $success = JSONTokenize($literal = str_split("false"), $tokenArrayReference, $errorMessages);
  AssertTrue($success, $failures);
  if($success){
    AssertEquals(count($tokenArrayReference->array), 2.0, $failures);
    AssertStringEquals($tokenArrayReference->array[0.0]->type->name, $literal = str_split("falseValue"), $failures);
  }

  $numbers = strSplitByString($literal = str_split("11, -1e-1, -0.123456789e-99, 1E1, -0.1E23"), $literal = str_split(", "));

  for($i = 0.0; $i < count($numbers); $i = $i + 1.0){
    $success = JSONTokenize($numbers[$i]->string, $tokenArrayReference, $errorMessages);
    AssertTrue($success, $failures);
    if($success){
      AssertEquals(count($tokenArrayReference->array), 2.0, $failures);
      AssertStringEquals($tokenArrayReference->array[0.0]->value, $numbers[$i]->string, $failures);
    }
  }

  $success = IsValidJSONStringInJSON($literal = str_split("\"\""), 0.0, $countReference, $stringLength, $errorMessages);
  AssertTrue($success, $failures);
  if($success){
    AssertEquals($countReference->numberValue, 2.0, $failures);
  }

  $success = IsValidJSONString($literal = str_split("\"\\u1234\\n\\r\\f\\b\\t\""), $errorMessages);
  AssertTrue($success, $failures);

  $success = JSONTokenize($literal = str_split("\""), $tokenArrayReference, $errorMessages);
  AssertFalse($success, $failures);

  $success = IsValidJSONNumber($literal = str_split("1.1-e1"), $errorMessages);
  AssertFalse($success, $failures);

  $success = IsValidJSONNumber($literal = str_split("1E+2"), $errorMessages);
  AssertTrue($success, $failures);

  $success = IsValidJSONString($literal = str_split("\"\\uAAAG\""), $errorMessages);
  AssertFalse($success, $failures);

  return $failures->numberValue;
}
function CreateBooleanReference($value){
  $ref = new stdClass();
  $ref->booleanValue = $value;

  return $ref;
}
function CreateBooleanArrayReference(&$value){
  $ref = new stdClass();
  $ref->booleanArray = $value;

  return $ref;
}
function CreateBooleanArrayReferenceLengthValue($length, $value){
  $ref = new stdClass();
  $ref->booleanArray = array_fill(0, $length, 0);

  for($i = 0.0; $i < $length; $i = $i + 1.0){
    $ref->booleanArray[$i] = $value;
  }

  return $ref;
}
function FreeBooleanArrayReference($booleanArrayReference){
  unset($booleanArrayReference->booleanArray);
  unset($booleanArrayReference);
}
function CreateCharacterReference($value){
  $ref = new stdClass();
  $ref->characterValue = $value;

  return $ref;
}
function CreateNumberReference($value){
  $ref = new stdClass();
  $ref->numberValue = $value;

  return $ref;
}
function CreateNumberArrayReference(&$value){
  $ref = new stdClass();
  $ref->numberArray = $value;

  return $ref;
}
function CreateNumberArrayReferenceLengthValue($length, $value){
  $ref = new stdClass();
  $ref->numberArray = array_fill(0, $length, 0);

  for($i = 0.0; $i < $length; $i = $i + 1.0){
    $ref->numberArray[$i] = $value;
  }

  return $ref;
}
function FreeNumberArrayReference($numberArrayReference){
  unset($numberArrayReference->numberArray);
  unset($numberArrayReference);
}
function CreateStringReference(&$value){
  $ref = new stdClass();
  $ref->string = $value;

  return $ref;
}
function CreateStringReferenceLengthValue($length, $value){
  $ref = new stdClass();
  $ref->string = array_fill(0, $length, 0);

  for($i = 0.0; $i < $length; $i = $i + 1.0){
    $ref->string[$i] = $value;
  }

  return $ref;
}
function FreeStringReference($stringReference){
  unset($stringReference->string);
  unset($stringReference);
}
function CreateStringArrayReference(&$strings){
  $ref = new stdClass();
  $ref->stringArray = $strings;

  return $ref;
}
function CreateStringArrayReferenceLengthValue($length, &$value){
  $ref = new stdClass();
  $ref->stringArray = array_fill(0, $length, 0);

  for($i = 0.0; $i < $length; $i = $i + 1.0){
    $ref->stringArray[$i] = CreateStringReference($value);
  }

  return $ref;
}
function FreeStringArrayReference($stringArrayReference){
  for($i = 0.0; $i < count($stringArrayReference->stringArray); $i = $i + 1.0){
    unset($stringArrayReference->stringArray[$i]);
  }
  unset($stringArrayReference->stringArray);
  unset($stringArrayReference);
}
function strWriteStringToStingStream(&$stream, $index, &$src){

  for($i = 0.0; $i < count($src); $i = $i + 1.0){
    $stream[$index->numberValue + $i] = $src[$i];
  }
  $index->numberValue = $index->numberValue + count($src);
}
function strWriteCharacterToStingStream(&$stream, $index, $src){
  $stream[$index->numberValue] = $src;
  $index->numberValue = $index->numberValue + 1.0;
}
function strWriteBooleanToStingStream(&$stream, $index, $src){
  if($src){
    strWriteStringToStingStream($stream, $index, $literal = str_split("true"));
  }else{
    strWriteStringToStingStream($stream, $index, $literal = str_split("false"));
  }
}
function strSubstringWithCheck(&$string, $from, $to, $stringReference){

  if($from >= 0.0 && $from <= count($string) && $to >= 0.0 && $to <= count($string) && $from <= $to){
    $stringReference->string = strSubstring($string, $from, $to);
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function &strSubstring(&$string, $from, $to){

  $length = $to - $from;

  $n = array_fill(0, $length, 0);

  for($i = $from; $i < $to; $i = $i + 1.0){
    $n[$i - $from] = $string[$i];
  }

  return $n;
}
function &strAppendString(&$s1, &$s2){

  $newString = strConcatenateString($s1, $s2);

  unset($s1);

  return $newString;
}
function &strConcatenateString(&$s1, &$s2){

  $newString = array_fill(0, count($s1) + count($s2), 0);

  for($i = 0.0; $i < count($s1); $i = $i + 1.0){
    $newString[$i] = $s1[$i];
  }

  for($i = 0.0; $i < count($s2); $i = $i + 1.0){
    $newString[count($s1) + $i] = $s2[$i];
  }

  return $newString;
}
function &strAppendCharacter(&$string, $c){

  $newString = strConcatenateCharacter($string, $c);

  unset($string);

  return $newString;
}
function &strConcatenateCharacter(&$string, $c){
  $newString = array_fill(0, count($string) + 1.0, 0);

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    $newString[$i] = $string[$i];
  }

  $newString[count($string)] = $c;

  return $newString;
}
function &strSplitByCharacter(&$toSplit, $splitBy){

  $stringToSplitBy = array_fill(0, 1.0, 0);
  $stringToSplitBy[0.0] = $splitBy;

  $split = strSplitByString($toSplit, $stringToSplitBy);

  unset($stringToSplitBy);

  return $split;
}
function strIndexOfCharacter(&$string, $character, $indexReference){

  $found = false;
  for($i = 0.0; $i < count($string) &&  !$found ; $i = $i + 1.0){
    if($string[$i] == $character){
      $found = true;
      $indexReference->numberValue = $i;
    }
  }

  return $found;
}
function strSubstringEqualsWithCheck(&$string, $from, &$substring, $equalsReference){

  if($from < count($string)){
    $success = true;
    $equalsReference->booleanValue = strSubstringEquals($string, $from, $substring);
  }else{
    $success = false;
  }

  return $success;
}
function strSubstringEquals(&$string, $from, &$substring){

  $equal = true;
  for($i = 0.0; $i < count($substring) && $equal; $i = $i + 1.0){
    if($string[$from + $i] != $substring[$i]){
      $equal = false;
    }
  }

  return $equal;
}
function strIndexOfString(&$string, &$substring, $indexReference){

  $found = false;
  for($i = 0.0; $i < count($string) - count($substring) + 1.0 &&  !$found ; $i = $i + 1.0){
    if(strSubstringEquals($string, $i, $substring)){
      $found = true;
      $indexReference->numberValue = $i;
    }
  }

  return $found;
}
function strContainsCharacter(&$string, $character){

  $found = false;
  for($i = 0.0; $i < count($string) &&  !$found ; $i = $i + 1.0){
    if($string[$i] == $character){
      $found = true;
    }
  }

  return $found;
}
function strContainsString(&$string, &$substring){
  return strIndexOfString($string, $substring, new stdClass());
}
function strToUpperCase(&$string){

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    $string[$i] = charToUpperCase($string[$i]);
  }
}
function strToLowerCase(&$string){

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    $string[$i] = charToLowerCase($string[$i]);
  }
}
function strEqualsIgnoreCase(&$a, &$b){

  if(count($a) == count($b)){
    $equal = true;
    for($i = 0.0; $i < count($a) && $equal; $i = $i + 1.0){
      if(charToLowerCase($a[$i]) != charToLowerCase($b[$i])){
        $equal = false;
      }
    }
  }else{
    $equal = false;
  }

  return $equal;
}
function &strReplaceString(&$string, &$toReplace, &$replaceWith){

  $equalsReference = new stdClass();
  $result = array_fill(0, 0.0, 0);

  for($i = 0.0; $i < count($string); ){
    $success = strSubstringEqualsWithCheck($string, $i, $toReplace, $equalsReference);
    if($success){
      $success = $equalsReference->booleanValue;
    }

    if($success && count($toReplace) > 0.0){
      $result = strConcatenateString($result, $replaceWith);
      $i = $i + count($toReplace);
    }else{
      $result = strConcatenateCharacter($result, $string[$i]);
      $i = $i + 1.0;
    }
  }

  return $result;
}
function &strReplaceCharacter(&$string, $toReplace, $replaceWith){

  $result = array_fill(0, 0.0, 0);

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    if($string[$i] == $toReplace){
      $result = strConcatenateCharacter($result, $replaceWith);
    }else{
      $result = strConcatenateCharacter($result, $string[$i]);
    }
  }

  return $result;
}
function &strTrim(&$string){

  /* Find whitepaces at the start. */
  $lastWhitespaceLocationStart = -1.0;
  $firstNonWhitespaceFound = false;
  for($i = 0.0; $i < count($string) &&  !$firstNonWhitespaceFound ; $i = $i + 1.0){
    if(charIsWhiteSpace($string[$i])){
      $lastWhitespaceLocationStart = $i;
    }else{
      $firstNonWhitespaceFound = true;
    }
  }

  /* Find whitepaces at the end. */
  $lastWhitespaceLocationEnd = count($string);
  $firstNonWhitespaceFound = false;
  for($i = count($string) - 1.0; $i >= 0.0 &&  !$firstNonWhitespaceFound ; $i = $i - 1.0){
    if(charIsWhiteSpace($string[$i])){
      $lastWhitespaceLocationEnd = $i;
    }else{
      $firstNonWhitespaceFound = true;
    }
  }

  if($lastWhitespaceLocationStart < $lastWhitespaceLocationEnd){
    $result = strSubstring($string, $lastWhitespaceLocationStart + 1.0, $lastWhitespaceLocationEnd);
  }else{
    $result = array_fill(0, 0.0, 0);
  }

  return $result;
}
function strStartsWith(&$string, &$start){

  $startsWithString = false;
  if(count($string) >= count($start)){
    $startsWithString = strSubstringEquals($string, 0.0, $start);
  }

  return $startsWithString;
}
function strEndsWith(&$string, &$end){

  $endsWithString = false;
  if(count($string) >= count($end)){
    $endsWithString = strSubstringEquals($string, count($string) - count($end), $end);
  }

  return $endsWithString;
}
function &strSplitByString(&$toSplit, &$splitBy){

  $split = array_fill(0, 0.0, 0);

  $next = array_fill(0, 0.0, 0);
  for($i = 0.0; $i < count($toSplit); ){
    $c = $toSplit[$i];

    if(strSubstringEquals($toSplit, $i, $splitBy)){
      if(count($split) != 0.0 || $i != 0.0){
        $n = new stdClass();
        $n->string = $next;
        $split = AddString($split, $n);
        $next = array_fill(0, 0.0, 0);
        $i = $i + count($splitBy);
      }
    }else{
      $next = strAppendCharacter($next, $c);
      $i = $i + 1.0;
    }
  }

  if(count($next) > 0.0){
    $n = new stdClass();
    $n->string = $next;
    $split = AddString($split, $n);
  }

  return $split;
}
function &nCreateStringScientificNotationDecimalFromNumber($decimal){
  $mantissaReference = new stdClass();
  $exponentReference = new stdClass();
  $result = array_fill(0, 0.0, 0);
  $done = false;
  $exponent = 0.0;

  if($decimal < 0.0){
    $isPositive = false;
    $decimal = -$decimal;
  }else{
    $isPositive = true;
  }

  if($decimal == 0.0){
    $done = true;
  }

  if( !$done ){
    $multiplier = 0.0;
    $inc = 0.0;
    if($decimal < 1.0){
      $multiplier = 10.0;
      $inc = -1.0;
    }else if($decimal >= 10.0){
      $multiplier = 0.1;
      $inc = 1.0;
    }else{
      $done = true;
    }
    if( !$done ){
      for(; $decimal >= 10.0 || $decimal < 1.0; ){
        $decimal = $decimal*$multiplier;
        $exponent = $exponent + $inc;
      }
    }
  }

  nCreateStringFromNumberWithCheck($decimal, 10.0, $mantissaReference);

  nCreateStringFromNumberWithCheck($exponent, 10.0, $exponentReference);

  if( !$isPositive ){
    $result = AppendString($result, $literal = str_split("-"));
  }

  $result = AppendString($result, $mantissaReference->string);
  $result = AppendString($result, $literal = str_split("e"));
  $result = AppendString($result, $exponentReference->string);

  return $result;
}
function &nCreateStringDecimalFromNumber($decimal){
  $stringReference = new stdClass();

  /* This will succeed because base = 10. */
  nCreateStringFromNumberWithCheck($decimal, 10.0, $stringReference);

  return $stringReference->string;
}
function nCreateStringFromNumberWithCheck($decimal, $base, $stringReference){
  $isPositive = true;

  if($decimal < 0.0){
    $isPositive = false;
    $decimal = -$decimal;
  }

  if($decimal == 0.0){
    $stringReference->string = str_split("0");
    $success = true;
  }else{
    $characterReference = new stdClass();
    if(IsInteger($base)){
      $success = true;
      $string = array_fill(0, 0.0, 0);
      $maximumDigits = nGetMaximumDigitsForBase($base);
      $digitPosition = nGetFirstDigitPosition($decimal, $base);
      $decimal = round($decimal*$base**($maximumDigits - $digitPosition - 1.0));
      $hasPrintedPoint = false;
      if( !$isPositive ){
        $string = AppendCharacter($string, "-");
      }
      if($digitPosition < 0.0){
        $string = AppendCharacter($string, "0");
        $string = AppendCharacter($string, ".");
        $hasPrintedPoint = true;
        for($i = 0.0; $i < -$digitPosition - 1.0; $i = $i + 1.0){
          $string = AppendCharacter($string, "0");
        }
      }
      for($i = 0.0; $i < $maximumDigits && $success; $i = $i + 1.0){
        $d = floor($decimal/$base**($maximumDigits - $i - 1.0));
        if($d >= $base){
          $d = $base - 1.0;
        }
        if( !$hasPrintedPoint  && $digitPosition - $i + 1.0 == 0.0){
          if($decimal != 0.0){
            $string = AppendCharacter($string, ".");
          }
          $hasPrintedPoint = true;
        }
        if($decimal == 0.0 && $hasPrintedPoint){
        }else{
          $success = nGetSingleDigitCharacterFromNumberWithCheck($d, $base, $characterReference);
          if($success){
            $c = $characterReference->characterValue;
            $string = AppendCharacter($string, $c);
          }
        }
        if($success){
          $decimal = $decimal - $d*$base**($maximumDigits - $i - 1.0);
        }
      }
      if($success){
        for($i = 0.0; $i < $digitPosition - $maximumDigits + 1.0; $i = $i + 1.0){
          $string = AppendCharacter($string, "0");
        }
        $stringReference->string = $string;
      }
    }else{
      $success = false;
    }
  }

  /* Done */
  return $success;
}
function nGetMaximumDigitsForBase($base){
  $t = 10.0**15.0;
  return floor(log10($t)/log10($base));
}
function nGetFirstDigitPosition($decimal, $base){
  $power = ceil(log10($decimal)/log10($base));

  $t = $decimal*$base**(-$power);
  if($t < $base && $t >= 1.0){
  }else if($t >= $base){
    $power = $power + 1.0;
  }else if($t < 1.0){
    $power = $power - 1.0;
  }

  return $power;
}
function nGetSingleDigitCharacterFromNumberWithCheck($c, $base, $characterReference){
  $numberTable = nGetDigitCharacterTable();

  if($c < $base || $c < count($numberTable)){
    $success = true;
    $characterReference->characterValue = $numberTable[$c];
  }else{
    $success = false;
  }

  return $success;
}
function &nGetDigitCharacterTable(){
  $numberTable = str_split("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ");

  return $numberTable;
}
function nCreateNumberFromDecimalStringWithCheck(&$string, $decimalReference, $errorMessage){
  return nCreateNumberFromStringWithCheck($string, 10.0, $decimalReference, $errorMessage);
}
function nCreateNumberFromDecimalString(&$string){
  $doubleReference = CreateNumberReference(0.0);
  $stringReference = CreateStringReference($literal = array());
  nCreateNumberFromStringWithCheck($string, 10.0, $doubleReference, $stringReference);
  $number = $doubleReference->numberValue;

  unset($doubleReference);
  unset($stringReference);

  return $number;
}
function nCreateNumberFromStringWithCheck(&$string, $base, $numberReference, $errorMessage){
  $numberIsPositive = CreateBooleanReference(true);
  $exponentIsPositive = CreateBooleanReference(true);
  $beforePoint = new stdClass();
  $afterPoint = new stdClass();
  $exponent = new stdClass();

  if($base >= 2.0 && $base <= 36.0){
    $success = nExtractPartsFromNumberString($string, $base, $numberIsPositive, $beforePoint, $afterPoint, $exponentIsPositive, $exponent, $errorMessage);
    if($success){
      $numberReference->numberValue = nCreateNumberFromParts($base, $numberIsPositive->booleanValue, $beforePoint->numberArray, $afterPoint->numberArray, $exponentIsPositive->booleanValue, $exponent->numberArray);
    }
  }else{
    $success = false;
    $errorMessage->string = str_split("Base must be from 2 to 36.");
  }

  return $success;
}
function nCreateNumberFromParts($base, $numberIsPositive, &$beforePoint, &$afterPoint, $exponentIsPositive, &$exponent){
  $n = 0.0;

  for($i = 0.0; $i < count($beforePoint); $i = $i + 1.0){
    $p = $beforePoint[count($beforePoint) - $i - 1.0];
    $n = $n + $p*$base**$i;
  }

  for($i = 0.0; $i < count($afterPoint); $i = $i + 1.0){
    $p = $afterPoint[$i];
    $n = $n + $p*$base**(-($i + 1.0));
  }

  if(count($exponent) > 0.0){
    $e = 0.0;
    for($i = 0.0; $i < count($exponent); $i = $i + 1.0){
      $p = $exponent[count($exponent) - $i - 1.0];
      $e = $e + $p*$base**$i;
    }
    if( !$exponentIsPositive ){
      $e = -$e;
    }
    $n = $n*$base**$e;
  }

  if( !$numberIsPositive ){
    $n = -$n;
  }

  return $n;
}
function nExtractPartsFromNumberString(&$n, $base, $numberIsPositive, $beforePoint, $afterPoint, $exponentIsPositive, $exponent, $errorMessages){
  $i = 0.0;

  if($i < count($n)){
    if($n[$i] == "-"){
      $numberIsPositive->booleanValue = false;
      $i = $i + 1.0;
    }else if($n[$i] == "+"){
      $numberIsPositive->booleanValue = true;
      $i = $i + 1.0;
    }
    $success = nExtractPartsFromNumberStringFromSign($n, $base, $i, $beforePoint, $afterPoint, $exponentIsPositive, $exponent, $errorMessages);
  }else{
    $success = false;
    $errorMessages->string = str_split("Number cannot have length zero.");
  }

  return $success;
}
function nExtractPartsFromNumberStringFromSign(&$n, $base, $i, $beforePoint, $afterPoint, $exponentIsPositive, $exponent, $errorMessages){
  $done = false;
  $count = 0.0;
  for(; $i + $count < count($n) &&  !$done ; ){
    if(nCharacterIsNumberCharacterInBase($n[$i + $count], $base)){
      $count = $count + 1.0;
    }else{
      $done = true;
    }
  }

  if($count >= 1.0){
    $beforePoint->numberArray = array_fill(0, $count, 0);
    for($j = 0.0; $j < $count; $j = $j + 1.0){
      $beforePoint->numberArray[$j] = nGetNumberFromNumberCharacterForBase($n[$i + $j], $base);
    }
    $i = $i + $count;
    if($i < count($n)){
      $success = nExtractPartsFromNumberStringFromPointOrExponent($n, $base, $i, $afterPoint, $exponentIsPositive, $exponent, $errorMessages);
    }else{
      $afterPoint->numberArray = array_fill(0, 0.0, 0);
      $exponent->numberArray = array_fill(0, 0.0, 0);
      $success = true;
    }
  }else{
    $success = false;
    $errorMessages->string = str_split("Number must have at least one number after the optional sign.");
  }

  return $success;
}
function nExtractPartsFromNumberStringFromPointOrExponent(&$n, $base, $i, $afterPoint, $exponentIsPositive, $exponent, $errorMessages){
  if($n[$i] == "."){
    $i = $i + 1.0;
    if($i < count($n)){
      $done = false;
      $count = 0.0;
      for(; $i + $count < count($n) &&  !$done ; ){
        if(nCharacterIsNumberCharacterInBase($n[$i + $count], $base)){
          $count = $count + 1.0;
        }else{
          $done = true;
        }
      }
      if($count >= 1.0){
        $afterPoint->numberArray = array_fill(0, $count, 0);
        for($j = 0.0; $j < $count; $j = $j + 1.0){
          $afterPoint->numberArray[$j] = nGetNumberFromNumberCharacterForBase($n[$i + $j], $base);
        }
        $i = $i + $count;
        if($i < count($n)){
          $success = nExtractPartsFromNumberStringFromExponent($n, $base, $i, $exponentIsPositive, $exponent, $errorMessages);
        }else{
          $exponent->numberArray = array_fill(0, 0.0, 0);
          $success = true;
        }
      }else{
        $success = false;
        $errorMessages->string = str_split("There must be at least one digit after the decimal point.");
      }
    }else{
      $success = false;
      $errorMessages->string = str_split("There must be at least one digit after the decimal point.");
    }
  }else if($base <= 14.0 && ($n[$i] == "e" || $n[$i] == "E")){
    if($i < count($n)){
      $success = nExtractPartsFromNumberStringFromExponent($n, $base, $i, $exponentIsPositive, $exponent, $errorMessages);
      $afterPoint->numberArray = array_fill(0, 0.0, 0);
    }else{
      $success = false;
      $errorMessages->string = str_split("There must be at least one digit after the exponent.");
    }
  }else{
    $success = false;
    $errorMessages->string = str_split("Expected decimal point or exponent symbol.");
  }

  return $success;
}
function nExtractPartsFromNumberStringFromExponent(&$n, $base, $i, $exponentIsPositive, $exponent, $errorMessages){
  if($base <= 14.0 && ($n[$i] == "e" || $n[$i] == "E")){
    $i = $i + 1.0;
    if($i < count($n)){
      if($n[$i] == "-"){
        $exponentIsPositive->booleanValue = false;
        $i = $i + 1.0;
      }else if($n[$i] == "+"){
        $exponentIsPositive->booleanValue = true;
        $i = $i + 1.0;
      }
      if($i < count($n)){
        $done = false;
        $count = 0.0;
        for(; $i + $count < count($n) &&  !$done ; ){
          if(nCharacterIsNumberCharacterInBase($n[$i + $count], $base)){
            $count = $count + 1.0;
          }else{
            $done = true;
          }
        }
        if($count >= 1.0){
          $exponent->numberArray = array_fill(0, $count, 0);
          for($j = 0.0; $j < $count; $j = $j + 1.0){
            $exponent->numberArray[$j] = nGetNumberFromNumberCharacterForBase($n[$i + $j], $base);
          }
          $i = $i + $count;
          if($i == count($n)){
            $success = true;
          }else{
            $success = false;
            $errorMessages->string = str_split("There cannot be any characters past the exponent of the number.");
          }
        }else{
          $success = false;
          $errorMessages->string = str_split("There must be at least one digit after the decimal point.");
        }
      }else{
        $success = false;
        $errorMessages->string = str_split("There must be at least one digit after the exponent symbol.");
      }
    }else{
      $success = false;
      $errorMessages->string = str_split("There must be at least one digit after the exponent symbol.");
    }
  }else{
    $success = false;
    $errorMessages->string = str_split("Expected exponent symbol.");
  }

  return $success;
}
function nGetNumberFromNumberCharacterForBase($c, $base){
  $numberTable = nGetDigitCharacterTable();
  $position = 0.0;

  for($i = 0.0; $i < $base; $i = $i + 1.0){
    if($numberTable[$i] == $c){
      $position = $i;
    }
  }

  return $position;
}
function nCharacterIsNumberCharacterInBase($c, $base){
  $numberTable = nGetDigitCharacterTable();
  $found = false;

  for($i = 0.0; $i < $base; $i = $i + 1.0){
    if($numberTable[$i] == $c){
      $found = true;
    }
  }

  return $found;
}
function &nStringToNumberArray(&$str){
  $numberArrayReference = new stdClass();
  $stringReference = new stdClass();

  nStringToNumberArrayWithCheck($str, $numberArrayReference, $stringReference);

  $numbers = $numberArrayReference->numberArray;

  unset($numberArrayReference);
  unset($stringReference);

  return $numbers;
}
function nStringToNumberArrayWithCheck(&$str, $numberArrayReference, $errorMessage){
  $numberStrings = SplitByString($str, $literal = str_split(","));

  $numbers = array_fill(0, count($numberStrings), 0);
  $success = true;
  $numberReference = new stdClass();

  for($i = 0.0; $i < count($numberStrings); $i = $i + 1.0){
    $numberString = $numberStrings[$i]->string;
    $trimmedNumberString = Trimx($numberString);
    $success = nCreateNumberFromDecimalStringWithCheck($trimmedNumberString, $numberReference, $errorMessage);
    $numbers[$i] = $numberReference->numberValue;
    FreeStringReference($numberStrings[$i]);
    unset($trimmedNumberString);
  }

  unset($numberStrings);
  unset($numberReference);

  $numberArrayReference->numberArray = $numbers;

  return $success;
}
function &AddNumber(&$list, $a){

  $newlist = array_fill(0, count($list) + 1.0, 0);
  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    $newlist[$i] = $list[$i];
  }
  $newlist[count($list)] = $a;
		
  unset($list);
		
  return $newlist;
}
function AddNumberRef($list, $i){
  $list->numberArray = AddNumber($list->numberArray, $i);
}
function &RemoveNumber(&$list, $n){

  $newlist = array_fill(0, count($list) - 1.0, 0);

  if($n >= 0.0 && $n < count($list)){
    for($i = 0.0; $i < count($list); $i = $i + 1.0){
      if($i < $n){
        $newlist[$i] = $list[$i];
      }
      if($i > $n){
        $newlist[$i - 1.0] = $list[$i];
      }
    }

    unset($list);
  }else{
    unset($newlist);
  }
		
  return $newlist;
}
function GetNumberRef($list, $i){
  return $list->numberArray[$i];
}
function RemoveNumberRef($list, $i){
  $list->numberArray = RemoveNumber($list->numberArray, $i);
}
function &AddString(&$list, $a){

  $newlist = array_fill(0, count($list) + 1.0, 0);

  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    $newlist[$i] = $list[$i];
  }
  $newlist[count($list)] = $a;
		
  unset($list);
		
  return $newlist;
}
function AddStringRef($list, $i){
  $list->stringArray = AddString($list->stringArray, $i);
}
function &RemoveString(&$list, $n){

  $newlist = array_fill(0, count($list) - 1.0, 0);

  if($n >= 0.0 && $n < count($list)){
    for($i = 0.0; $i < count($list); $i = $i + 1.0){
      if($i < $n){
        $newlist[$i] = $list[$i];
      }
      if($i > $n){
        $newlist[$i - 1.0] = $list[$i];
      }
    }

    unset($list);
  }else{
    unset($newlist);
  }
		
  return $newlist;
}
function GetStringRef($list, $i){
  return $list->stringArray[$i];
}
function RemoveStringRef($list, $i){
  $list->stringArray = RemoveString($list->stringArray, $i);
}
function &AddBoolean(&$list, $a){

  $newlist = array_fill(0, count($list) + 1.0, 0);
  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    $newlist[$i] = $list[$i];
  }
  $newlist[count($list)] = $a;
		
  unset($list);
		
  return $newlist;
}
function AddBooleanRef($list, $i){
  $list->booleanArray = AddBoolean($list->booleanArray, $i);
}
function &RemoveBoolean(&$list, $n){

  $newlist = array_fill(0, count($list) - 1.0, 0);

  if($n >= 0.0 && $n < count($list)){
    for($i = 0.0; $i < count($list); $i = $i + 1.0){
      if($i < $n){
        $newlist[$i] = $list[$i];
      }
      if($i > $n){
        $newlist[$i - 1.0] = $list[$i];
      }
    }

    unset($list);
  }else{
    unset($newlist);
  }
		
  return $newlist;
}
function GetBooleanRef($list, $i){
  return $list->booleanArray[$i];
}
function RemoveDecimalRef($list, $i){
  $list->booleanArray = RemoveBoolean($list->booleanArray, $i);
}
function &AddCharacter(&$list, $a){

  $newlist = array_fill(0, count($list) + 1.0, 0);
  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    $newlist[$i] = $list[$i];
  }
  $newlist[count($list)] = $a;
		
  unset($list);
		
  return $newlist;
}
function AddCharacterRef($list, $i){
  $list->string = AddCharacter($list->string, $i);
}
function &RemoveCharacter(&$list, $n){

  $newlist = array_fill(0, count($list) - 1.0, 0);

  if($n >= 0.0 && $n < count($list)){
    for($i = 0.0; $i < count($list); $i = $i + 1.0){
      if($i < $n){
        $newlist[$i] = $list[$i];
      }
      if($i > $n){
        $newlist[$i - 1.0] = $list[$i];
      }
    }

    unset($list);
  }else{
    unset($newlist);
  }

  return $newlist;
}
function GetCharacterRef($list, $i){
  return $list->string[$i];
}
function RemoveCharacterRef($list, $i){
  $list->string = RemoveCharacter($list->string, $i);
}
function &StringToNumberArray(&$string){

  $array = array_fill(0, count($string), 0);

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    $array[$i] = uniord($string[$i]);
  }
  return $array;
}
function &NumberArrayToString(&$array){

  $string = array_fill(0, count($array), 0);

  for($i = 0.0; $i < count($array); $i = $i + 1.0){
    $string[$i] = unichr($array[$i]);
  }
  return $string;
}
function NumberArraysEqual(&$a, &$b){

  $equal = true;
  if(count($a) == count($b)){
    for($i = 0.0; $i < count($a) && $equal; $i = $i + 1.0){
      if($a[$i] != $b[$i]){
        $equal = false;
      }
    }
  }else{
    $equal = false;
  }

  return $equal;
}
function BooleanArraysEqual(&$a, &$b){

  $equal = true;
  if(count($a) == count($b)){
    for($i = 0.0; $i < count($a) && $equal; $i = $i + 1.0){
      if($a[$i] != $b[$i]){
        $equal = false;
      }
    }
  }else{
    $equal = false;
  }

  return $equal;
}
function StringsEqual(&$a, &$b){

  $equal = true;
  if(count($a) == count($b)){
    for($i = 0.0; $i < count($a) && $equal; $i = $i + 1.0){
      if($a[$i] != $b[$i]){
        $equal = false;
      }
    }
  }else{
    $equal = false;
  }

  return $equal;
}
function FillNumberArray(&$a, $value){

  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $a[$i] = $value;
  }
}
function FillString(&$a, $value){

  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $a[$i] = $value;
  }
}
function FillBooleanArray(&$a, $value){

  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $a[$i] = $value;
  }
}
function FillNumberArrayRange(&$a, $value, $from, $to){

  if($from >= 0.0 && $from <= count($a) && $to >= 0.0 && $to <= count($a) && $from <= $to){
    $length = $to - $from;
    for($i = 0.0; $i < $length; $i = $i + 1.0){
      $a[$from + $i] = $value;
    }

    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function FillBooleanArrayRange(&$a, $value, $from, $to){

  if($from >= 0.0 && $from <= count($a) && $to >= 0.0 && $to <= count($a) && $from <= $to){
    $length = $to - $from;
    for($i = 0.0; $i < $length; $i = $i + 1.0){
      $a[$from + $i] = $value;
    }

    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function FillStringRange(&$a, $value, $from, $to){

  if($from >= 0.0 && $from <= count($a) && $to >= 0.0 && $to <= count($a) && $from <= $to){
    $length = $to - $from;
    for($i = 0.0; $i < $length; $i = $i + 1.0){
      $a[$from + $i] = $value;
    }

    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function &CopyNumberArray(&$a){

  $n = array_fill(0, count($a), 0);

  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $n[$i] = $a[$i];
  }

  return $n;
}
function &CopyBooleanArray(&$a){

  $n = array_fill(0, count($a), 0);

  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $n[$i] = $a[$i];
  }

  return $n;
}
function &CopyString(&$a){

  $n = array_fill(0, count($a), 0);

  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $n[$i] = $a[$i];
  }

  return $n;
}
function CopyNumberArrayRange(&$a, $from, $to, $copyReference){

  if($from >= 0.0 && $from <= count($a) && $to >= 0.0 && $to <= count($a) && $from <= $to){
    $length = $to - $from;
    $n = array_fill(0, $length, 0);

    for($i = 0.0; $i < $length; $i = $i + 1.0){
      $n[$i] = $a[$from + $i];
    }

    $copyReference->numberArray = $n;
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function CopyBooleanArrayRange(&$a, $from, $to, $copyReference){

  if($from >= 0.0 && $from <= count($a) && $to >= 0.0 && $to <= count($a) && $from <= $to){
    $length = $to - $from;
    $n = array_fill(0, $length, 0);

    for($i = 0.0; $i < $length; $i = $i + 1.0){
      $n[$i] = $a[$from + $i];
    }

    $copyReference->booleanArray = $n;
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function CopyStringRange(&$a, $from, $to, $copyReference){

  if($from >= 0.0 && $from <= count($a) && $to >= 0.0 && $to <= count($a) && $from <= $to){
    $length = $to - $from;
    $n = array_fill(0, $length, 0);

    for($i = 0.0; $i < $length; $i = $i + 1.0){
      $n[$i] = $a[$from + $i];
    }

    $copyReference->string = $n;
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function IsLastElement($length, $index){
  return $index + 1.0 == $length;
}
function &CreateNumberArray($length, $value){

  $array = array_fill(0, $length, 0);
  FillNumberArray($array, $value);

  return $array;
}
function &CreateBooleanArray($length, $value){

  $array = array_fill(0, $length, 0);
  FillBooleanArray($array, $value);

  return $array;
}
function &CreateString($length, $value){

  $array = array_fill(0, $length, 0);
  FillString($array, $value);

  return $array;
}
function SwapElementsOfArray(&$A, $ai, $bi){

  $tmp = $A[$ai];
  $A[$ai] = $A[$bi];
  $A[$bi] = $tmp;
}
function AssertFalse($b, $failures){
  if($b){
    $failures->numberValue = $failures->numberValue + 1.0;
  }
}
function AssertTrue($b, $failures){
  if( !$b ){
    $failures->numberValue = $failures->numberValue + 1.0;
  }
}
function AssertEquals($a, $b, $failures){
  if($a != $b){
    $failures->numberValue = $failures->numberValue + 1.0;
  }
}
function AssertBooleansEqual($a, $b, $failures){
  if($a != $b){
    $failures->numberValue = $failures->numberValue + 1.0;
  }
}
function AssertCharactersEqual($a, $b, $failures){
  if($a != $b){
    $failures->numberValue = $failures->numberValue + 1.0;
  }
}
function AssertStringEquals(&$a, &$b, $failures){
  if( !StringsEqual($a, $b) ){
    $failures->numberValue = $failures->numberValue + 1.0;
  }
}
function AssertNumberArraysEqual(&$a, &$b, $failures){

  if(count($a) == count($b)){
    for($i = 0.0; $i < count($a); $i = $i + 1.0){
      AssertEquals($a[$i], $b[$i], $failures);
    }
  }else{
    $failures->numberValue = $failures->numberValue + 1.0;
  }
}
function AssertBooleanArraysEqual(&$a, &$b, $failures){

  if(count($a) == count($b)){
    for($i = 0.0; $i < count($a); $i = $i + 1.0){
      AssertBooleansEqual($a[$i], $b[$i], $failures);
    }
  }else{
    $failures->numberValue = $failures->numberValue + 1.0;
  }
}
function AssertStringArraysEqual(&$a, &$b, $failures){

  if(count($a) == count($b)){
    for($i = 0.0; $i < count($a); $i = $i + 1.0){
      AssertStringEquals($a[$i]->string, $b[$i]->string, $failures);
    }
  }else{
    $failures->numberValue = $failures->numberValue + 1.0;
  }
}
function charToLowerCase($character){

  $toReturn = $character;
  if($character == "A"){
    $toReturn = "a";
  }else if($character == "B"){
    $toReturn = "b";
  }else if($character == "C"){
    $toReturn = "c";
  }else if($character == "D"){
    $toReturn = "d";
  }else if($character == "E"){
    $toReturn = "e";
  }else if($character == "F"){
    $toReturn = "f";
  }else if($character == "G"){
    $toReturn = "g";
  }else if($character == "H"){
    $toReturn = "h";
  }else if($character == "I"){
    $toReturn = "i";
  }else if($character == "J"){
    $toReturn = "j";
  }else if($character == "K"){
    $toReturn = "k";
  }else if($character == "L"){
    $toReturn = "l";
  }else if($character == "M"){
    $toReturn = "m";
  }else if($character == "N"){
    $toReturn = "n";
  }else if($character == "O"){
    $toReturn = "o";
  }else if($character == "P"){
    $toReturn = "p";
  }else if($character == "Q"){
    $toReturn = "q";
  }else if($character == "R"){
    $toReturn = "r";
  }else if($character == "S"){
    $toReturn = "s";
  }else if($character == "T"){
    $toReturn = "t";
  }else if($character == "U"){
    $toReturn = "u";
  }else if($character == "V"){
    $toReturn = "v";
  }else if($character == "W"){
    $toReturn = "w";
  }else if($character == "X"){
    $toReturn = "x";
  }else if($character == "Y"){
    $toReturn = "y";
  }else if($character == "Z"){
    $toReturn = "z";
  }

  return $toReturn;
}
function charToUpperCase($character){

  $toReturn = $character;
  if($character == "a"){
    $toReturn = "A";
  }else if($character == "b"){
    $toReturn = "B";
  }else if($character == "c"){
    $toReturn = "C";
  }else if($character == "d"){
    $toReturn = "D";
  }else if($character == "e"){
    $toReturn = "E";
  }else if($character == "f"){
    $toReturn = "F";
  }else if($character == "g"){
    $toReturn = "G";
  }else if($character == "h"){
    $toReturn = "H";
  }else if($character == "i"){
    $toReturn = "I";
  }else if($character == "j"){
    $toReturn = "J";
  }else if($character == "k"){
    $toReturn = "K";
  }else if($character == "l"){
    $toReturn = "L";
  }else if($character == "m"){
    $toReturn = "M";
  }else if($character == "n"){
    $toReturn = "N";
  }else if($character == "o"){
    $toReturn = "O";
  }else if($character == "p"){
    $toReturn = "P";
  }else if($character == "q"){
    $toReturn = "Q";
  }else if($character == "r"){
    $toReturn = "R";
  }else if($character == "s"){
    $toReturn = "S";
  }else if($character == "t"){
    $toReturn = "T";
  }else if($character == "u"){
    $toReturn = "U";
  }else if($character == "v"){
    $toReturn = "V";
  }else if($character == "w"){
    $toReturn = "W";
  }else if($character == "x"){
    $toReturn = "X";
  }else if($character == "y"){
    $toReturn = "Y";
  }else if($character == "z"){
    $toReturn = "Z";
  }

  return $toReturn;
}
function charIsUpperCase($character){

  $isUpper = false;
  if($character == "A"){
    $isUpper = true;
  }else if($character == "B"){
    $isUpper = true;
  }else if($character == "C"){
    $isUpper = true;
  }else if($character == "D"){
    $isUpper = true;
  }else if($character == "E"){
    $isUpper = true;
  }else if($character == "F"){
    $isUpper = true;
  }else if($character == "G"){
    $isUpper = true;
  }else if($character == "H"){
    $isUpper = true;
  }else if($character == "I"){
    $isUpper = true;
  }else if($character == "J"){
    $isUpper = true;
  }else if($character == "K"){
    $isUpper = true;
  }else if($character == "L"){
    $isUpper = true;
  }else if($character == "M"){
    $isUpper = true;
  }else if($character == "N"){
    $isUpper = true;
  }else if($character == "O"){
    $isUpper = true;
  }else if($character == "P"){
    $isUpper = true;
  }else if($character == "Q"){
    $isUpper = true;
  }else if($character == "R"){
    $isUpper = true;
  }else if($character == "S"){
    $isUpper = true;
  }else if($character == "T"){
    $isUpper = true;
  }else if($character == "U"){
    $isUpper = true;
  }else if($character == "V"){
    $isUpper = true;
  }else if($character == "W"){
    $isUpper = true;
  }else if($character == "X"){
    $isUpper = true;
  }else if($character == "Y"){
    $isUpper = true;
  }else if($character == "Z"){
    $isUpper = true;
  }

  return $isUpper;
}
function charIsLowerCase($character){

  $isLower = false;
  if($character == "a"){
    $isLower = true;
  }else if($character == "b"){
    $isLower = true;
  }else if($character == "c"){
    $isLower = true;
  }else if($character == "d"){
    $isLower = true;
  }else if($character == "e"){
    $isLower = true;
  }else if($character == "f"){
    $isLower = true;
  }else if($character == "g"){
    $isLower = true;
  }else if($character == "h"){
    $isLower = true;
  }else if($character == "i"){
    $isLower = true;
  }else if($character == "j"){
    $isLower = true;
  }else if($character == "k"){
    $isLower = true;
  }else if($character == "l"){
    $isLower = true;
  }else if($character == "m"){
    $isLower = true;
  }else if($character == "n"){
    $isLower = true;
  }else if($character == "o"){
    $isLower = true;
  }else if($character == "p"){
    $isLower = true;
  }else if($character == "q"){
    $isLower = true;
  }else if($character == "r"){
    $isLower = true;
  }else if($character == "s"){
    $isLower = true;
  }else if($character == "t"){
    $isLower = true;
  }else if($character == "u"){
    $isLower = true;
  }else if($character == "v"){
    $isLower = true;
  }else if($character == "w"){
    $isLower = true;
  }else if($character == "x"){
    $isLower = true;
  }else if($character == "y"){
    $isLower = true;
  }else if($character == "z"){
    $isLower = true;
  }

  return $isLower;
}
function charIsLetter($character){
  return charIsUpperCase($character) || charIsLowerCase($character);
}
function charIsNumber($character){

  $isNumberx = false;
  if($character == "0"){
    $isNumberx = true;
  }else if($character == "1"){
    $isNumberx = true;
  }else if($character == "2"){
    $isNumberx = true;
  }else if($character == "3"){
    $isNumberx = true;
  }else if($character == "4"){
    $isNumberx = true;
  }else if($character == "5"){
    $isNumberx = true;
  }else if($character == "6"){
    $isNumberx = true;
  }else if($character == "7"){
    $isNumberx = true;
  }else if($character == "8"){
    $isNumberx = true;
  }else if($character == "9"){
    $isNumberx = true;
  }

  return $isNumberx;
}
function charIsWhiteSpace($character){

  $isWhiteSpacex = false;
  if($character == " "){
    $isWhiteSpacex = true;
  }else if($character == "\t"){
    $isWhiteSpacex = true;
  }else if($character == "\n"){
    $isWhiteSpacex = true;
  }else if($character == "\r"){
    $isWhiteSpacex = true;
  }

  return $isWhiteSpacex;
}
function charIsSymbol($character){

  $isSymbolx = false;
  if($character == "!"){
    $isSymbolx = true;
  }else if($character == "\""){
    $isSymbolx = true;
  }else if($character == "#"){
    $isSymbolx = true;
  }else if($character == "$"){
    $isSymbolx = true;
  }else if($character == "%"){
    $isSymbolx = true;
  }else if($character == "&"){
    $isSymbolx = true;
  }else if($character == "\'"){
    $isSymbolx = true;
  }else if($character == "("){
    $isSymbolx = true;
  }else if($character == ")"){
    $isSymbolx = true;
  }else if($character == "*"){
    $isSymbolx = true;
  }else if($character == "+"){
    $isSymbolx = true;
  }else if($character == ","){
    $isSymbolx = true;
  }else if($character == "-"){
    $isSymbolx = true;
  }else if($character == "."){
    $isSymbolx = true;
  }else if($character == "/"){
    $isSymbolx = true;
  }else if($character == ":"){
    $isSymbolx = true;
  }else if($character == ";"){
    $isSymbolx = true;
  }else if($character == "<"){
    $isSymbolx = true;
  }else if($character == "="){
    $isSymbolx = true;
  }else if($character == ">"){
    $isSymbolx = true;
  }else if($character == "?"){
    $isSymbolx = true;
  }else if($character == "@"){
    $isSymbolx = true;
  }else if($character == "["){
    $isSymbolx = true;
  }else if($character == "\\"){
    $isSymbolx = true;
  }else if($character == "]"){
    $isSymbolx = true;
  }else if($character == "^"){
    $isSymbolx = true;
  }else if($character == "_"){
    $isSymbolx = true;
  }else if($character == "`"){
    $isSymbolx = true;
  }else if($character == "{"){
    $isSymbolx = true;
  }else if($character == "|"){
    $isSymbolx = true;
  }else if($character == "}"){
    $isSymbolx = true;
  }else if($character == "~"){
    $isSymbolx = true;
  }

  return $isSymbolx;
}
function SubstringWithCheck(&$string, $from, $to, $stringReference){
  if($from < count($string) && $to < count($string) && $from <= $to && $from >= 0.0 && $to >= 0.0){
    $stringReference->string = Substring($string, $from, $to);
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function &Substring(&$string, $from, $to){
  $n = array_fill(0, max($to - $from, 0.0), 0);

  for($i = $from; $i < $to; $i = $i + 1.0){
    $n[$i - $from] = $string[$i];
  }

  return $n;
}
function &AppendString(&$string, &$s){
  $newString = ConcatenateString($string, $s);

  unset($string);

  return $newString;
}
function &ConcatenateString(&$string, &$s){
  $newString = array_fill(0, count($string) + count($s), 0);

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    $newString[$i] = $string[$i];
  }

  for($i = 0.0; $i < count($s); $i = $i + 1.0){
    $newString[count($string) + $i] = $s[$i];
  }

  return $newString;
}
function &AppendCharacter(&$string, $c){
  $newString = ConcatenateCharacter($string, $c);

  unset($string);

  return $newString;
}
function &ConcatenateCharacter(&$string, $c){
  $newString = array_fill(0, count($string) + 1.0, 0);

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    $newString[$i] = $string[$i];
  }

  $newString[count($string)] = $c;

  return $newString;
}
function &SplitByCharacter(&$toSplit, $splitBy){
  $stringToSplitBy = array_fill(0, 1.0, 0);
  $stringToSplitBy[0.0] = $splitBy;

  $split = SplitByString($toSplit, $stringToSplitBy);

  unset($stringToSplitBy);

  return $split;
}
function IndexOfCharacter(&$string, $character, $indexReference){
  $found = false;
  for($i = 0.0; $i < count($string) &&  !$found ; $i = $i + 1.0){
    if($string[$i] == $character){
      $found = true;
      $indexReference->numberValue = $i;
    }
  }

  return $found;
}
function SubstringEqualsWithCheck(&$string, $from, &$substring, $equalsReference){
  if($from < count($string)){
    $success = true;
    $equalsReference->booleanValue = SubstringEquals($string, $from, $substring);
  }else{
    $success = false;
  }

  return $success;
}
function SubstringEquals(&$string, $from, &$substring){
  $equal = true;
  for($i = 0.0; $i < count($substring) && $equal; $i = $i + 1.0){
    if($string[$from + $i] != $substring[$i]){
      $equal = false;
    }
  }

  return $equal;
}
function IndexOfString(&$string, &$substring, $indexReference){
  $found = false;
  for($i = 0.0; $i < count($string) - count($substring) + 1.0 &&  !$found ; $i = $i + 1.0){
    if(SubstringEquals($string, $i, $substring)){
      $found = true;
      $indexReference->numberValue = $i;
    }
  }

  return $found;
}
function ContainsCharacter(&$string, $character){
  return IndexOfCharacter($string, $character, new stdClass());
}
function ContainsString(&$string, &$substring){
  return IndexOfString($string, $substring, new stdClass());
}
function ToUpperCase(&$string){
  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    $string[$i] = cToUpperCase($string[$i]);
  }
}
function ToLowerCase(&$string){
  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    $string[$i] = cToLowerCase($string[$i]);
  }
}
function EqualsIgnoreCase(&$a, &$b){
  if(count($a) == count($b)){
    $equal = true;
    for($i = 0.0; $i < count($a) && $equal; $i = $i + 1.0){
      if(cToLowerCase($a[$i]) != cToLowerCase($b[$i])){
        $equal = false;
      }
    }
  }else{
    $equal = false;
  }

  return $equal;
}
function &ReplacesString(&$string, &$toReplace, &$replaceWith){
  $equalsReference = new stdClass();
  $result = array_fill(0, 0.0, 0);

  for($i = 0.0; $i < count($string); ){
    $success = SubstringEqualsWithCheck($string, $i, $toReplace, $equalsReference);
    if($success){
      $success = $equalsReference->booleanValue;
    }
    if($success && count($toReplace) > 0.0){
      $result = ConcatenateString($result, $replaceWith);
      $i = $i + count($toReplace);
    }else{
      $result = ConcatenateCharacter($result, $string[$i]);
      $i = $i + 1.0;
    }
  }

  return $result;
}
function &ReplaceCharacter(&$string, $toReplace, $replaceWith){
  $result = array_fill(0, 0.0, 0);

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    if($string[$i] == $toReplace){
      $result = ConcatenateCharacter($result, $replaceWith);
    }else{
      $result = ConcatenateCharacter($result, $string[$i]);
    }
  }

  return $result;
}
function &Trimx(&$string){
  $lastWhitespaceLocationStart = -1.0;
  $firstNonWhitespaceFound = false;
  for($i = 0.0; $i < count($string) &&  !$firstNonWhitespaceFound ; $i = $i + 1.0){
    if(cIsWhiteSpace($string[$i])){
      $lastWhitespaceLocationStart = $i;
    }else{
      $firstNonWhitespaceFound = true;
    }
  }

  /* Find whitepaces at the end. */
  $lastWhitespaceLocationEnd = count($string);
  $firstNonWhitespaceFound = false;
  for($i = count($string) - 1.0; $i >= 0.0 &&  !$firstNonWhitespaceFound ; $i = $i - 1.0){
    if(cIsWhiteSpace($string[$i])){
      $lastWhitespaceLocationEnd = $i;
    }else{
      $firstNonWhitespaceFound = true;
    }
  }

  if($lastWhitespaceLocationStart < $lastWhitespaceLocationEnd){
    $result = Substring($string, $lastWhitespaceLocationStart + 1.0, $lastWhitespaceLocationEnd);
  }else{
    $result = array_fill(0, 0.0, 0);
  }

  return $result;
}
function StartsWith(&$string, &$start){
  $startsWithString = false;
  if(count($string) >= count($start)){
    $startsWithString = SubstringEquals($string, 0.0, $start);
  }

  return $startsWithString;
}
function EndsWith(&$string, &$end){
  $endsWithString = false;
  if(count($string) >= count($end)){
    $endsWithString = SubstringEquals($string, count($string) - count($end), $end);
  }

  return $endsWithString;
}
function &SplitByString(&$toSplit, &$splitBy){
  $split = array_fill(0, 0.0, 0);

  $next = array_fill(0, 0.0, 0);
  for($i = 0.0; $i < count($toSplit); ){
    $c = $toSplit[$i];
    if(SubstringEquals($toSplit, $i, $splitBy)){
      if(count($split) != 0.0 || $i != 0.0){
        $n = new stdClass();
        $n->string = $next;
        $split = lAddString($split, $n);
        $next = array_fill(0, 0.0, 0);
        $i = $i + count($splitBy);
      }
    }else{
      $next = AppendCharacter($next, $c);
      $i = $i + 1.0;
    }
  }

  if(count($next) > 0.0){
    $n = new stdClass();
    $n->string = $next;
    $split = lAddString($split, $n);
  }

  return $split;
}
function Negate($x){
  return -$x;
}
function Positive($x){
  return +$x;
}
function Factorial($x){
  $f = 1.0;

  for($i = 2.0; $i <= $x; $i = $i + 1.0){
    $f = $f*$i;
  }

  return $f;
}
function Roundx($x){
  return floor($x + 0.5);
}
function BankersRound($x){
  if(Absolute($x - Truncate($x)) == 0.5){
    if( !DivisibleBy(Roundx($x), 2.0) ){
      $r = Roundx($x) - 1.0;
    }else{
      $r = Roundx($x);
    }
  }else{
    $r = Roundx($x);
  }

  return $r;
}
function Ceilx($x){
  return ceil($x);
}
function Floorx($x){
  return floor($x);
}
function Truncate($x){
  if($x >= 0.0){
    $t = floor($x);
  }else{
    $t = ceil($x);
  }

  return $t;
}
function Absolute($x){
  return abs($x);
}
function Logarithm($x){
  return log10($x);
}
function NaturalLogarithm($x){
  return log($x);
}
function Sinx($x){
  return sin($x);
}
function Cosx($x){
  return cos($x);
}
function Tanx($x){
  return tan($x);
}
function Asinx($x){
  return asin($x);
}
function Acosx($x){
  return acos($x);
}
function Atanx($x){
  return atan($x);
}
function Atan2x($y, $x){
  $a = 0.0;

  if($x > 0.0){
    $a = Atanx($y/$x);
  }else if($x < 0.0 && $y >= 0.0){
    $a = Atanx($y/$x) + M_PI;
  }else if($x < 0.0 && $y < 0.0){
    $a = Atanx($y/$x) - M_PI;
  }else if($x == 0.0 && $y > 0.0){
    $a = M_PI/2.0;
  }else if($x == 0.0 && $y < 0.0){
    $a = -M_PI/2.0;
  }

  return $a;
}
function Squareroot($x){
  return sqrt($x);
}
function Expx($x){
  return exp($x);
}
function DivisibleBy($a, $b){
  return (($a%$b) == 0.0);
}
function Combinations($n, $k){
  return Factorial($n)/(Factorial($n - $k)*Factorial($k));
}
function EpsilonCompareApproximateDigits($a, $b, $digits){
  if($a < 0.0 && $b < 0.0 || $a > 0.0 && $b > 0.0){
    if($a < 0.0 && $b < 0.0){
      $a = -$a;
      $b = -$b;
    }
    $ad = log10($a);
    $bd = log10($b);
    $d = max($ad, $bd);
    $epsilon = 10.0**($d - $digits);
    $ret = abs($a - $b) > $epsilon;
  }else{
    $ret = false;
  }

  return $ret;
}
function EpsilonCompare($a, $b, $epsilon){
  return abs($a - $b) < $epsilon;
}
function GreatestCommonDivisor($a, $b){
  for(; $b != 0.0; ){
    $t = $b;
    $b = $a%$b;
    $a = $t;
  }

  return $a;
}
function IsInteger($a){
  return ($a - floor($a)) == 0.0;
}
function GreatestCommonDivisorWithCheck($a, $b, $gcdReference){
  if(IsInteger($a) && IsInteger($b)){
    $gcd = GreatestCommonDivisor($a, $b);
    $gcdReference->numberValue = $gcd;
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function LeastCommonMultiple($a, $b){
  if($a > 0.0 && $b > 0.0){
    $lcm = abs($a*$b)/GreatestCommonDivisor($a, $b);
  }else{
    $lcm = 0.0;
  }

  return $lcm;
}
function Sign($a){
  if($a > 0.0){
    $s = 1.0;
  }else if($a < 0.0){
    $s = -1.0;
  }else{
    $s = 0.0;
  }

  return $s;
}
function Maxx($a, $b){
  return max($a, $b);
}
function Minx($a, $b){
  return min($a, $b);
}
function Power($a, $b){
  return $a**$b;
}
function &lAddNumber(&$list, $a){
  $newlist = array_fill(0, count($list) + 1.0, 0);
  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    $newlist[$i] = $list[$i];
  }
  $newlist[count($list)] = $a;
		
  unset($list);
		
  return $newlist;
}
function lAddNumberRef($list, $i){
  $list->numberArray = lAddNumber($list->numberArray, $i);
}
function &lRemoveNumber(&$list, $n){
  $newlist = array_fill(0, count($list) - 1.0, 0);

  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    if($i < $n){
      $newlist[$i] = $list[$i];
    }
    if($i > $n){
      $newlist[$i - 1.0] = $list[$i];
    }
  }
		
  unset($list);
		
  return $newlist;
}
function lGetNumberRef($list, $i){
  return $list->numberArray[$i];
}
function lRemoveNumberRef($list, $i){
  $list->numberArray = lRemoveNumber($list->numberArray, $i);
}
function &lAddString(&$list, $a){
  $newlist = array_fill(0, count($list) + 1.0, 0);

  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    $newlist[$i] = $list[$i];
  }
  $newlist[count($list)] = $a;
		
  unset($list);
		
  return $newlist;
}
function lAddStringRef($list, $i){
  $list->stringArray = lAddString($list->stringArray, $i);
}
function &lRemoveString(&$list, $n){
  $newlist = array_fill(0, count($list) - 1.0, 0);

  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    if($i < $n){
      $newlist[$i] = $list[$i];
    }
    if($i > $n){
      $newlist[$i - 1.0] = $list[$i];
    }
  }
		
  unset($list);
		
  return $newlist;
}
function lGetStringRef($list, $i){
  return $list->stringArray[$i];
}
function lRemoveStringRef($list, $i){
  $list->stringArray = lRemoveString($list->stringArray, $i);
}
function &lAddBoolean(&$list, $a){
  $newlist = array_fill(0, count($list) + 1.0, 0);
  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    $newlist[$i] = $list[$i];
  }
  $newlist[count($list)] = $a;
		
  unset($list);
		
  return $newlist;
}
function lAddBooleanRef($list, $i){
  $list->booleanArray = lAddBoolean($list->booleanArray, $i);
}
function &lRemoveBoolean(&$list, $n){
  $newlist = array_fill(0, count($list) - 1.0, 0);

  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    if($i < $n){
      $newlist[$i] = $list[$i];
    }
    if($i > $n){
      $newlist[$i - 1.0] = $list[$i];
    }
  }
		
  unset($list);
		
  return $newlist;
}
function lGetBooleanRef($list, $i){
  return $list->booleanArray[$i];
}
function lRemoveDecimalRef($list, $i){
  $list->booleanArray = lRemoveBoolean($list->booleanArray, $i);
}
function &lAddCharacter(&$list, $a){
  $newlist = array_fill(0, count($list) + 1.0, 0);
  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    $newlist[$i] = $list[$i];
  }
  $newlist[count($list)] = $a;
		
  unset($list);
		
  return $newlist;
}
function lAddCharacterRef($list, $i){
  $list->string = lAddCharacter($list->string, $i);
}
function &lRemoveCharacter(&$list, $n){
  $newlist = array_fill(0, count($list) - 1.0, 0);

  for($i = 0.0; $i < count($list); $i = $i + 1.0){
    if($i < $n){
      $newlist[$i] = $list[$i];
    }
    if($i > $n){
      $newlist[$i - 1.0] = $list[$i];
    }
  }

  unset($list);

  return $newlist;
}
function lGetCharacterRef($list, $i){
  return $list->string[$i];
}
function lRemoveCharacterRef($list, $i){
  $list->string = lRemoveCharacter($list->string, $i);
}
function &arraysStringToNumberArray(&$string){
  $array = array_fill(0, count($string), 0);

  for($i = 0.0; $i < count($string); $i = $i + 1.0){
    $array[$i] = uniord($string[$i]);
  }
  return $array;
}
function &arraysNumberArrayToString(&$array){
  $string = array_fill(0, count($array), 0);

  for($i = 0.0; $i < count($array); $i = $i + 1.0){
    $string[$i] = unichr($array[$i]);
  }
  return $string;
}
function arraysNumberArraysEqual(&$a, &$b){
  $equal = true;
  if(count($a) == count($b)){
    for($i = 0.0; $i < count($a) && $equal; $i = $i + 1.0){
      if($a[$i] != $b[$i]){
        $equal = false;
      }
    }
  }else{
    $equal = false;
  }

  return $equal;
}
function arraysBooleanArraysEqual(&$a, &$b){
  $equal = true;
  if(count($a) == count($b)){
    for($i = 0.0; $i < count($a) && $equal; $i = $i + 1.0){
      if($a[$i] != $b[$i]){
        $equal = false;
      }
    }
  }else{
    $equal = false;
  }

  return $equal;
}
function arraysStringsEqual(&$a, &$b){
  $equal = true;
  if(count($a) == count($b)){
    for($i = 0.0; $i < count($a) && $equal; $i = $i + 1.0){
      if($a[$i] != $b[$i]){
        $equal = false;
      }
    }
  }else{
    $equal = false;
  }

  return $equal;
}
function arraysFillNumberArray(&$a, $value){
  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $a[$i] = $value;
  }
}
function arraysFillString(&$a, $value){
  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $a[$i] = $value;
  }
}
function arraysFillBooleanArray(&$a, $value){
  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $a[$i] = $value;
  }
}
function arraysFillNumberArrayInterval(&$a, $value, $from, $to){
  if($from >= 0.0 && $from < count($a) && $to >= 0.0 && $to < count($a)){
    for($i = $from; $i < $to; $i = $i + 1.0){
      $a[$i] = $value;
    }
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function arraysFillBooleanArrayInterval(&$a, $value, $from, $to){
  if($from >= 0.0 && $from < count($a) && $to >= 0.0 && $to < count($a)){
    for($i = max($from, 0.0); $i < min($to, count($a)); $i = $i + 1.0){
      $a[$i] = $value;
    }
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function arraysFillStringInterval(&$a, $value, $from, $to){
  if($from >= 0.0 && $from < count($a) && $to >= 0.0 && $to < count($a)){
    for($i = max($from, 0.0); $i < min($to, count($a)); $i = $i + 1.0){
      $a[$i] = $value;
    }
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function &arraysCopyNumberArray(&$a){
  $n = array_fill(0, count($a), 0);

  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $n[$i] = $a[$i];
  }

  return $n;
}
function &arraysCopyBooleanArray(&$a){
  $n = array_fill(0, count($a), 0);

  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $n[$i] = $a[$i];
  }

  return $n;
}
function &arraysCopyString(&$a){
  $n = array_fill(0, count($a), 0);

  for($i = 0.0; $i < count($a); $i = $i + 1.0){
    $n[$i] = $a[$i];
  }

  return $n;
}
function arraysCopyNumberArrayRange(&$a, $from, $to, $copyReference){
  if($from >= 0.0 && $from < count($a) && $to >= 0.0 && $to < count($a) && $from <= $to){
    $length = $to - $from;
    $n = array_fill(0, $length, 0);
    for($i = 0.0; $i < $length; $i = $i + 1.0){
      $n[$i] = $a[$from + $i];
    }
    $copyReference->numberArray = $n;
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function arraysCopyBooleanArrayRange(&$a, $from, $to, $copyReference){
  if($from >= 0.0 && $from < count($a) && $to >= 0.0 && $to < count($a) && $from <= $to){
    $length = $to - $from;
    $n = array_fill(0, $length, 0);
    for($i = 0.0; $i < $length; $i = $i + 1.0){
      $n[$i] = $a[$from + $i];
    }
    $copyReference->booleanArray = $n;
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function arraysCopyStringRange(&$a, $from, $to, $copyReference){
  if($from >= 0.0 && $from < count($a) && $to >= 0.0 && $to < count($a) && $from <= $to){
    $length = $to - $from;
    $n = array_fill(0, $length, 0);
    for($i = 0.0; $i < $length; $i = $i + 1.0){
      $n[$i] = $a[$from + $i];
    }
    $copyReference->string = $n;
    $success = true;
  }else{
    $success = false;
  }

  return $success;
}
function arraysIsLastElement($length, $index){
  return $index + 1.0 == $length;
}
function &arraysCreateNumberArray($length, $value){
  $array = array_fill(0, $length, 0);
  arraysFillNumberArray($array, $value);

  return $array;
}
function &arraysCreateBooleanArray($length, $value){
  $array = array_fill(0, $length, 0);
  arraysFillBooleanArray($array, $value);

  return $array;
}
function &arraysCreateString($length, $value){
  $array = array_fill(0, $length, 0);
  arraysFillString($array, $value);

  return $array;
}
function cToLowerCase($character){
  $toReturn = $character;
  if($character == "A"){
    $toReturn = "a";
  }else if($character == "B"){
    $toReturn = "b";
  }else if($character == "C"){
    $toReturn = "c";
  }else if($character == "D"){
    $toReturn = "d";
  }else if($character == "E"){
    $toReturn = "e";
  }else if($character == "F"){
    $toReturn = "f";
  }else if($character == "G"){
    $toReturn = "g";
  }else if($character == "H"){
    $toReturn = "h";
  }else if($character == "I"){
    $toReturn = "i";
  }else if($character == "J"){
    $toReturn = "j";
  }else if($character == "K"){
    $toReturn = "k";
  }else if($character == "L"){
    $toReturn = "l";
  }else if($character == "M"){
    $toReturn = "m";
  }else if($character == "N"){
    $toReturn = "n";
  }else if($character == "O"){
    $toReturn = "o";
  }else if($character == "P"){
    $toReturn = "p";
  }else if($character == "Q"){
    $toReturn = "q";
  }else if($character == "R"){
    $toReturn = "r";
  }else if($character == "S"){
    $toReturn = "s";
  }else if($character == "T"){
    $toReturn = "t";
  }else if($character == "U"){
    $toReturn = "u";
  }else if($character == "V"){
    $toReturn = "v";
  }else if($character == "W"){
    $toReturn = "w";
  }else if($character == "X"){
    $toReturn = "x";
  }else if($character == "Y"){
    $toReturn = "y";
  }else if($character == "Z"){
    $toReturn = "z";
  }

  return $toReturn;
}
function cToUpperCase($character){
  $toReturn = $character;
  if($character == "a"){
    $toReturn = "A";
  }else if($character == "b"){
    $toReturn = "B";
  }else if($character == "c"){
    $toReturn = "C";
  }else if($character == "d"){
    $toReturn = "D";
  }else if($character == "e"){
    $toReturn = "E";
  }else if($character == "f"){
    $toReturn = "F";
  }else if($character == "g"){
    $toReturn = "G";
  }else if($character == "h"){
    $toReturn = "H";
  }else if($character == "i"){
    $toReturn = "I";
  }else if($character == "j"){
    $toReturn = "J";
  }else if($character == "k"){
    $toReturn = "K";
  }else if($character == "l"){
    $toReturn = "L";
  }else if($character == "m"){
    $toReturn = "M";
  }else if($character == "n"){
    $toReturn = "N";
  }else if($character == "o"){
    $toReturn = "O";
  }else if($character == "p"){
    $toReturn = "P";
  }else if($character == "q"){
    $toReturn = "Q";
  }else if($character == "r"){
    $toReturn = "R";
  }else if($character == "s"){
    $toReturn = "S";
  }else if($character == "t"){
    $toReturn = "T";
  }else if($character == "u"){
    $toReturn = "U";
  }else if($character == "v"){
    $toReturn = "V";
  }else if($character == "w"){
    $toReturn = "W";
  }else if($character == "x"){
    $toReturn = "X";
  }else if($character == "y"){
    $toReturn = "Y";
  }else if($character == "z"){
    $toReturn = "Z";
  }

  return $toReturn;
}
function cIsUpperCase($character){
  $isUpper = false;
  if($character == "A"){
    $isUpper = true;
  }else if($character == "B"){
    $isUpper = true;
  }else if($character == "C"){
    $isUpper = true;
  }else if($character == "D"){
    $isUpper = true;
  }else if($character == "E"){
    $isUpper = true;
  }else if($character == "F"){
    $isUpper = true;
  }else if($character == "G"){
    $isUpper = true;
  }else if($character == "H"){
    $isUpper = true;
  }else if($character == "I"){
    $isUpper = true;
  }else if($character == "J"){
    $isUpper = true;
  }else if($character == "K"){
    $isUpper = true;
  }else if($character == "L"){
    $isUpper = true;
  }else if($character == "M"){
    $isUpper = true;
  }else if($character == "N"){
    $isUpper = true;
  }else if($character == "O"){
    $isUpper = true;
  }else if($character == "P"){
    $isUpper = true;
  }else if($character == "Q"){
    $isUpper = true;
  }else if($character == "R"){
    $isUpper = true;
  }else if($character == "S"){
    $isUpper = true;
  }else if($character == "T"){
    $isUpper = true;
  }else if($character == "U"){
    $isUpper = true;
  }else if($character == "V"){
    $isUpper = true;
  }else if($character == "W"){
    $isUpper = true;
  }else if($character == "X"){
    $isUpper = true;
  }else if($character == "Y"){
    $isUpper = true;
  }else if($character == "Z"){
    $isUpper = true;
  }

  return $isUpper;
}
function cIsLowerCase($character){
  $isLower = false;
  if($character == "a"){
    $isLower = true;
  }else if($character == "b"){
    $isLower = true;
  }else if($character == "c"){
    $isLower = true;
  }else if($character == "d"){
    $isLower = true;
  }else if($character == "e"){
    $isLower = true;
  }else if($character == "f"){
    $isLower = true;
  }else if($character == "g"){
    $isLower = true;
  }else if($character == "h"){
    $isLower = true;
  }else if($character == "i"){
    $isLower = true;
  }else if($character == "j"){
    $isLower = true;
  }else if($character == "k"){
    $isLower = true;
  }else if($character == "l"){
    $isLower = true;
  }else if($character == "m"){
    $isLower = true;
  }else if($character == "n"){
    $isLower = true;
  }else if($character == "o"){
    $isLower = true;
  }else if($character == "p"){
    $isLower = true;
  }else if($character == "q"){
    $isLower = true;
  }else if($character == "r"){
    $isLower = true;
  }else if($character == "s"){
    $isLower = true;
  }else if($character == "t"){
    $isLower = true;
  }else if($character == "u"){
    $isLower = true;
  }else if($character == "v"){
    $isLower = true;
  }else if($character == "w"){
    $isLower = true;
  }else if($character == "x"){
    $isLower = true;
  }else if($character == "y"){
    $isLower = true;
  }else if($character == "z"){
    $isLower = true;
  }

  return $isLower;
}
function cIsLetter($character){
  return cIsUpperCase($character) || cIsLowerCase($character);
}
function cIsNumber($character){
  $isNumber = false;
  if($character == "0"){
    $isNumber = true;
  }else if($character == "1"){
    $isNumber = true;
  }else if($character == "2"){
    $isNumber = true;
  }else if($character == "3"){
    $isNumber = true;
  }else if($character == "4"){
    $isNumber = true;
  }else if($character == "5"){
    $isNumber = true;
  }else if($character == "6"){
    $isNumber = true;
  }else if($character == "7"){
    $isNumber = true;
  }else if($character == "8"){
    $isNumber = true;
  }else if($character == "9"){
    $isNumber = true;
  }

  return $isNumber;
}
function cIsWhiteSpace($character){
  $isWhiteSpace = false;
  if($character == " "){
    $isWhiteSpace = true;
  }else if($character == "\t"){
    $isWhiteSpace = true;
  }else if($character == "\n"){
    $isWhiteSpace = true;
  }else if($character == "\r"){
    $isWhiteSpace = true;
  }

  return $isWhiteSpace;
}
function cIsSymbol($character){
  $isSymbol = false;
  if($character == "!"){
    $isSymbol = true;
  }else if($character == "\""){
    $isSymbol = true;
  }else if($character == "#"){
    $isSymbol = true;
  }else if($character == "$"){
    $isSymbol = true;
  }else if($character == "%"){
    $isSymbol = true;
  }else if($character == "&"){
    $isSymbol = true;
  }else if($character == "\'"){
    $isSymbol = true;
  }else if($character == "("){
    $isSymbol = true;
  }else if($character == ")"){
    $isSymbol = true;
  }else if($character == "*"){
    $isSymbol = true;
  }else if($character == "+"){
    $isSymbol = true;
  }else if($character == ","){
    $isSymbol = true;
  }else if($character == "-"){
    $isSymbol = true;
  }else if($character == "."){
    $isSymbol = true;
  }else if($character == "/"){
    $isSymbol = true;
  }else if($character == ":"){
    $isSymbol = true;
  }else if($character == ";"){
    $isSymbol = true;
  }else if($character == "<"){
    $isSymbol = true;
  }else if($character == "="){
    $isSymbol = true;
  }else if($character == ">"){
    $isSymbol = true;
  }else if($character == "?"){
    $isSymbol = true;
  }else if($character == "@"){
    $isSymbol = true;
  }else if($character == "["){
    $isSymbol = true;
  }else if($character == "\\"){
    $isSymbol = true;
  }else if($character == "]"){
    $isSymbol = true;
  }else if($character == "^"){
    $isSymbol = true;
  }else if($character == "_"){
    $isSymbol = true;
  }else if($character == "`"){
    $isSymbol = true;
  }else if($character == "{"){
    $isSymbol = true;
  }else if($character == "|"){
    $isSymbol = true;
  }else if($character == "}"){
    $isSymbol = true;
  }else if($character == "~"){
    $isSymbol = true;
  }

  return $isSymbol;
}

