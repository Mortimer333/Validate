<?php
class Vali
{

  public static $_STRING_EMPTY = false;     // defines is string can be empty

  public static $_LAST_ERROR = "";

  public static function Error($mes)
  {
    Vali::$_LAST_ERROR = array("error" => $mes);
    return false;
  }

  public static function Success()
  {
    return true;
  }

  public static function Ctrl( $value, $variable, $key, $name = false)
  {
         if ( is_string ( $key ) ) $dir = $key  ;
    else if ( is_numeric( $key ) ) $dir = $value;

    $reverse = false;

    if (strpos($dir,"!") !== false) {
      $reverse = true;
      $dir = str_replace("!","",$dir);
    }

    $results = true;

    switch ($dir) {
      case 'check'   :
        $results = Vali::Check  ($value, $variable, $name, $reverse);
        break;
      case 'array'   :
        $results = Vali::Array  ($variable, $name, true, $reverse);
        break;
      case 'int'     :
        $results = Vali::Int    ( $variable, $name, $reverse );
        break;
      case 'decimal' :
        $results = Vali::Decimal( $variable, $name, $reverse );
        break;
      case "bool"    :
        $results = Vali::Bool   ( $variable, $name, $reverse );
        break;
      case 'date'    :
        $results = Vali::Day    ( $variable, $name, $reverse );
        break;
      case 'string'  :
        $results = Vali::String ( $variable, Vali::$_STRING_EMPTY, $name, $reverse );
        break;
      case 'string_empty':
        $results = Vali::String ( $variable, true, $name, $reverse );
        break;
      case 'object'  :
        $results = Vali::Object ( $variable, $name, $reverse );
        break;
      case "null"    :
        $results = Vali::NULL   ( $variable, $name, $reverse );
        break;
      case "js"      :
        $results = Vali::JS     ( $variable, $name, $reverse );
        break;
      case "php"     :
        $results = Vali::PHP    ( $variable, $name, $reverse );
        break;
      default:
        $results = Vali::Error  ( "Not recognized type - " . $dir );
        break;
    }

    return $results;
  }

  public static function date( $variable, $specs, $name = false )
  {
    if ( Vali::Array( $specs ) ) {
      foreach ($specs as $key => $value) {
        $res = Vali::Ctrl( $value, $variable, $key );
        if ( !$res ) return $res;
      }
    } elseif ( Vali::String( $specs ) ) {
      $res = Vali::Ctrl( $specs, $variable, $specs, $name );
      if ( !$res ) return $res;
    } else {
      return Vali::Error("Specs must be the type of array or string.");
    }

    return Vali::Success();
  }

  public static function Check( $specs, $vars, $name = false, $reverse = false )
  {

    $val = Vali::Specs($specs); // does nothing, can't remember what should it do, leave it for now
    if ( !$val ) return $val;

    if ( !Vali::Array( $vars, $name ) ) return false;

    if ( !isset( $specs['types'] ) ) {
      if (is_string($name)) return Vali::Error("`" . $name . "` hasn't got any defined attributes.");
      else                  return Vali::Error("Array hasn't got any defined attributes.");
    }

    if (isset($specs["indexes"])) {
      if (!is_array($specs["indexes"])) {
        if (is_string($name)) return Vali::Error("Passed index of new attributes isn't an array in `" . $name . "`.");
        else                  return Vali::Error("Passed index of new attributes isn't an array.");
      }

      $i = 0;

      foreach ($specs["indexes"] as $nVar => $type) {
        $i++;

        if ( !Vali::String( $nVar ) ) {
          if ( Vali::String( $name ) ) return Vali::Error("One of the keys is in wrong format in `" . $name . "`.");
          else                         return Vali::Error("One of the keys is in wrong format .");
        }

        // if element doesn't exists and has attribute exists set to false just skip it
        if ( !isset( $vars[$nVar] ) && isset( $type['exists'] ) && $type['exists'] == false ) {
          if ($i == sizeof($specs["indexes"])) return Vali::Success();
          else                                 continue;
        }

        if ( !isset( $vars[$nVar] ) ) return Vali::Error("Not found - `" . Tools::EscHTML($nVar) . "`.");

        if ( Vali::Array( $type ) ) {

          if ( !isset($type["name"]) )
            return Vali::Error("`" . $nVar . "` has no name.");

          if ( !isset($type["types"]) )
            return Vali::Error("`" . $nVar . "` has no allowed types.");

          if (Vali::Array($type["types"])) {
            foreach ($type["types"] as $i => $nType) {

              if (strpos($nType,'this') !== false) {
                $typeHoldName = str_replace("this.","",$nType);
                $res = Vali::date( $vars[$nVar], $vars[$typeHoldName], $type["name"] );
              } else $res = Vali::date( $vars[$nVar], $nType, $type["name"] );
              if ($res) break;
              if ( $i + 1 === sizeof($type["types"]) ) return $res;
            }
          } elseif (Vali::String($type["types"])) {


            if (strpos($type["types"],'this') !== false) {
              $typeHoldName = str_replace("this.","",$type["types"]);
              $res = Vali::date( $vars[$nVar], $vars[$typeHoldName], $type["name"] );
            } else $res = Vali::date( $vars[$nVar], $type["types"], $type["name"] );

            if (!$res) return $res;

          }

          if ( isset( $type["check"] ) ) {
            $res = Vali::date($vars[$nVar],["check" => $type["check"]]);
            if ( !$res ) return $res;
          }

        } elseif ( Vali::String($type) ) {
          $res = Vali::date( $vars[$nVar], $type, $nVar );
          if (!$res) return $res;
          unset($vars[$nVar]);
        }
      }
    }

    foreach ($vars as $var) {
      if (is_array($specs['types'])) {
        $index = 0;
        foreach ($specs['types'] as $key => $spec) {
          if (is_string($key)) {
            $res = Vali::date($var,array($key => $spec));
            if ($key === "array" && !$res) return $res;
          } else $res = Vali::date($var,$spec);

          if ($res) break;

          if ($index + 1 == sizeof($specs['types'])) return $res;

          $index++;
        }
      } else if (is_string($specs['types'])) {
        $res = Vali::date($var,$specs['types']);
        if (!$res) return $res;
      }
    }


    return Vali::Success();
  }

  public static function Int($var, $name = false, $reverse = false)
  {

    if (!isset($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` not found.");
      else                  return Vali::Error("Data not found - Int.");
    }

    if ($reverse && is_numeric($var) && filter_var($var, FILTER_VALIDATE_INT) !== false) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is of an illegal type - natural number.");
      else                  return Vali::Error("Data is of an illegal type - natural number.");
    }

    if (!is_numeric($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not liczbą.");
      else                  return Vali::Error("Data is not liczbą.");
    }

    if (filter_var($var, FILTER_VALIDATE_INT) === false) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not natural number.");
      else                  return Vali::Error("Data is not natural number.");
    }

    return Vali::Success();
  }

  public static function String($var, $empty = false, $name = false, $reverse = false)
  {
    if (!isset($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` not found.");
      else                  return Vali::Error("Data not found - String.");
    }

    if ($reverse && is_string($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is of an illegal type - string.");
      else                  return Vali::Error("Data is of an illegal type - string.");
    }

    if (!is_string($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not string.");
      else                  return Vali::Error("Data is not string.");
    }

    if (!$empty && $var == "") {
      if (is_string($name)) return Vali::Error("`" . $name . "` is empty.");
      else                  return Vali::Error("String is empty.");
    }

    return Vali::Success();
  }

  public static function Decimal($var, $name = false, $reverse = false)
  {
    if (!isset($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` not found.");
      else                  return Vali::Error("Data not found - Decimal.");
    }

    if ($reverse && is_numeric($var) && filter_var($var, FILTER_VALIDATE_FLOAT) !== false) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is of an illegal type - decimal.");
      else                  return Vali::Error("Data  is of an illegal type - decimal.");
    }

    if (!is_numeric($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not number.");
      else                  return Vali::Error("Data is not liczbą.");
    }

    if (filter_var($var, FILTER_VALIDATE_FLOAT) === false) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not decimal.");
      else                  return Vali::Error("Data is not decimal.");
    }

    return Vali::Success();
  }

  public static function Day($var, $name = false, $reverse = false)
  {
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $var);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    $is_date = $d && $d->format($format) === $var;

    if (!isset($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` not found.");
      else                  return Vali::Error("Data not found - Day.");
    }

    if ($reverse && $is_date) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is of an illegal type - date.");
      else                  return Vali::Error("Data  is of an illegal type - date.");
    }

    if (!$is_date) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not a date.");
      else                  return Vali::Error("Date is not a date.");
    }

    return Vali::Success();
  }

  public static function Bool($var, $name = false, $reverse = false)
  {
    if (!isset($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` not found.");
      else                  return Vali::Error("Data not found - Bool.");
    }

    if ($reverse && ($var === false || $var === true)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is of an illegal type - logical.");
      else                  return Vali::Error("Data  is of an illegal type - logical.");
    }

    if ($var !== false && $var !== true) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not logical type.");
      else                  return Vali::Error("Data is not logical type.");
    }

    return Vali::Success();
  }

  public static function Null($var, $name = false, $reverse = false)
  {
    if ($reverse && $var === null) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is of an illegal type - NULL.");
      else                  return Vali::Error("Data  is of an illegal type - NULL.");
    }

    if ($var !== null) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not NULL.");
      else                  return Vali::Error("Data is not NULL.");
    }

    return Vali::Success();
  }

  public static function Array($var, $name = false, $empty = true, $reverse = false)
  {

    if (!isset($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` not found.");
      else                  return Vali::Error("Data not found - Array.");
    }

    if ($reverse && is_array($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is of an illegal type - array.");
      else                  return Vali::Error("Data  is of an illegal type - array.");
    }

    if (!is_array($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not array.");
      else                  return Vali::Error("Data is not array.");
    }

    if (!$empty && sizeof($var) === 0) {
      if (is_string($name)) return Vali::Error("`" . $name . "` cannot be empty.");
      else                  return Vali::Error("Array cannot be empty.");
    }

    return Vali::Success();
  }



  public static function Assoc($var, $name = false, $empty = true, $reverse = false) : bool
  {

    if (!isset($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` not found.");
      else                  return Vali::Error("Data not found - Assoc.");
    }

    if ($reverse && (!is_array($var) || array() !== $var || array_keys($var) !== range(0, count($var) - 1))) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is of an illegal type - associative array.");
      else                  return Vali::Error("Data  is of an illegal type - associative array.");
    }

    if (!is_array($var) || array() === $var || array_keys($var) === range(0, count($var) - 1)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not associative array.");
      else                  return Vali::Error("Data is not associative array.");
    }

    if (!$empty && sizeof($var) === 0) {
      if (is_string($name)) return Vali::Error("`" . $name . "` cannot be empty.");
      else                  return Vali::Error("array cannot be empty.");
    }

    return Vali::Success();

  }

  public static function Object($var, $name = false, $reverse = false)
  {
    if (!isset($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` not found.");
      else                  return Vali::Error("Data not found - Object.");
    }

    if ($reverse && is_object($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is of an illegal type - object.");
      else                  return Vali::Error("Data  is of an illegal type - object.");
    }

    if (!is_object($var)) {
      if (is_string($name)) return Vali::Error("`" . $name . "` is not an object.");
      else                  return Vali::Error("Data is not an object.");
    }

    return Vali::Success();
  }

  public static function JS($var, $name = false, $reverse = false)
  {
    if (!Vali::String($var, $name)) return false;

    $var = preg_replace('/\s+/', '', $var);
    $var = strtolower($var);
    $pos = strpos($var,"<script");

    if ($reverse && $pos !== false) {
      if (is_string($name)) return Vali::Error("`" . $name . "` contains js script.");
      else                  return Vali::Error("Data contains js script.");
    } elseif (!$reverse && $pos === false) {
      if (is_string($name)) return Vali::Error("`" . $name . "` doesn't contain js script.");
      else                  return Vali::Error("Data doesn't contain js script.");
    }

    return Vali::Success();

  }

  public static function PHP($var, $name = false, $reverse = false)
  {
    if (!Vali::String($var, $name)) return false;

    $var = preg_replace('/\s+/', '', $var);
    $var = strtolower($var);
    $pos = strpos($var,"<?");

    if ($reverse && $pos !== false) {
      if (is_string($name)) return Vali::Error("`" . $name . "` caontains php script.");
      else                  return Vali::Error("Data caontains php script.");
    } elseif (!$reverse && $pos === false) {
      if (is_string($name)) return Vali::Error("`" . $name . "` doesn't contain php script.");
      else                  return Vali::Error("Data doesn't contain php script.");
    }

    return Vali::Success();

  }

  public static function Specs($specs)
  {

    return Vali::Success();
  }

}
