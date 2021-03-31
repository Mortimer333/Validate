<?php
class Vali
{
  /**  @var bool   _STRING_EMPTY Default setting for checking if string is empty, cuz empty string might mean a lot in php */
  public  static $_STRING_EMPTY = false;

  /**  @var string _LAST_ERROR Holder of last error in readable form */
  private static $_LAST_ERROR = "";

  /**  @var array _DIR Contains current directory */
  private static $_DIR = [];

  public static function GetError() : string
  {
    return self::$_LAST_ERROR;
  }

  public static function Error( string $mes ) : bool
  {
    Vali::$_LAST_ERROR = $mes;
    Vali::$_DIR        = []; // here we clear _DIR so next check will have clear directory
    return false;
  }

  public static function Success() : bool
  {
    return true;
  }

  /**
    *    Control center of reading schema
    *
    *    Thanks to this method we have an easy way to assign proper validation to given type of data and manipulate it.
    *
    *    @param mixin  variable [REQ] Data to be validated
    *    @param string key      [REQ] Type of validation
    *    @param string name     [OPT] Name of given value to be used with errors
    *
    *    @return bool
    */

  public static function Ctrl( $variable, string $key, ?string $name = null, ?string $dir = null ) : bool
  {
    if ( isset( Vali::$_DIR[$dir] ) ) {
      return true; // if we have already validated this value just return true;
    }
    $reverse = false;

    if ( $key[0] === "!" ) {
      $reverse = true;
      $key = str_replace("!", "", $key );
    }

    $key = mb_strtolower($key);

    switch ( $key ) {

      case "js"      :
        $results = Vali::JS   ( $variable, $name, $reverse );
        break;

      case 'int'     :
        $results = Vali::Int  ( $variable, $name, $reverse );
        break;

      case "php"     :
        $results = Vali::PHP  ( $variable, $name, $reverse );
        break;

      case 'date'    :
        $results = Vali::Date ( $variable, $name, $reverse );
        break;

      case 'mail'    :
        $results = Vali::Mail ( $variable, $name, $reverse );
        break;

      case 'json'    :
        $results = Vali::JSON ( $variable, $name, $reverse );
        break;

      case "bool"    :
        $results = Vali::Bool ( $variable, $name, $reverse );
        break;

      case "null"    :
        $results = Vali::NULL ( $variable, $name, $reverse );
        break;

      case 'array'   :
        $results = Vali::Array  ( $variable, $name, true, $reverse                 );
        break;

      case 'string'  :
        $results = Vali::String ( $variable, Vali::$_STRING_EMPTY, $name, $reverse );
        break;

      case 'string_empty':
        $results = Vali::String ( $variable, true, $name, $reverse                 );
        break;

      case 'object'  :
        $results = Vali::Object ( $variable, $name, $reverse                       );
        break;

      case 'decimal' :
        $results = Vali::Decimal( $variable, $name, $reverse                       );
        break;

      default:
        $results = Vali::Error( "Not recognized type - " . $key );
        break;

    }

    Vali::$_DIR[$dir] = true;
    return $results;
  }

  private static function ExecVal( array $data, array $set, $var, string $type, string $dir, string $key, ?string $name = null ) : bool
  {
    if ( substr( $type, 0, 5 ) == "this." ) {
      $sub_key = substr( $type, 5 );

      if ( !isset( $data[ $sub_key ] ) ) return Vali::Error( "$name type is ill defined, it points at not existing value as its type" );

      $val  = $set [ $sub_key ]; // validate schema
      $type = $data[ $sub_key ]; // type of choosen var AKA value of another var
      $res  = Vali::Ctrl( $type, $val[0], $val[1] ?? null, $dir . $sub_key );
      if ( !$res ) return false;
    }
    return Vali::Ctrl( $var, $type, $name, $dir . $key );
  }

  /**
    *    Function starts recursive validation with given schema
    *
    *    @param array data Data to be validated by given schema
    *    @param array data Schema to valide with
    *
    *    @return bool
    */

  public static function dat( array $schema, array $data, string $dir = "", bool $start = true ) : bool
  {
    if ( !isset( $schema["set"] ) && !isset( $schema["free"] ) ) return Vali::Error( "Schema doesn't contain neither set nor free values." );

    $set  = $schema["set" ] ?? [];
    $free = $schema["free"] ?? [];

    // Validation of set (indexed) values
    foreach ( $set as $key => $value ) {
      $name  = $value[1] ?? $key;

      if ( !isset( $data[$key] ) ) return Vali::Error( "$name was not found"                        );
      if ( sizeof( $value ) == 0 ) return Vali::Error( "$name is ill defined, it doesn't have type" );

      $types = $value[0  ];
      $var   = $data[$key];

      if ( !is_iterable( $types ) ) $types = [$types];

      foreach ( $types as $type ) {
        $res = Vali::ExecVal( $data, $set, $var, $type, $dir, $key, $name );
      }

      if ( !$res ) return $res;

      if ( sizeof( $value ) >= 3 ) {

        Vali::$_DIR[$dir . $key] = true;

        // Notice that we are changing the type of value into array
        // but which wont impact the check because we validated that this value
        // has correct type so we can tranform it into array (from object I assume)
        // and make it usable for the rest of methods
        $res = Vali::dat( $value[2], (array) $var, $dir . $key . ".", false );
        if ( !$res ) return $res;


      }
    }

    // Delete all checked indexes so they wont be double checked with free types
    foreach ($set as $key => $value) {
      unset( $data[$key] );
    }

    // Validating free - non indexed values left after index check (set)
    foreach ( $data as $var ) {
      foreach ( $free as $type ) {
        $res = Vali::Ctrl( $var, $type );
        if ( !$res ) return $res;
      }
    }

    if ( $start ) Vali::$_DIR = []; // here we clear _DIR just before the validation will end

    return Vali::Success();
  }

  public static function Int( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !isset( $var )                                                                      ) return Vali::Error("$name not found."                             );
    if ( is_string( $var ) && $var != 0 && ltrim( $var,'-' )[0] == '0' && !$reverse          ) return Vali::Error("$name begins with 0, thus cannot be number."  );
    if ( $reverse && is_numeric( $var ) && filter_var( $var, FILTER_VALIDATE_INT ) !== false ) return Vali::Error("$name is of an illegal type - natural number.");
    if ( !is_numeric( $var )                                                                 ) return Vali::Error("$name is not a number."                       );
    if ( filter_var( $var, FILTER_VALIDATE_INT ) === false                                   ) return Vali::Error("$name is not a natural number."               );

    return Vali::Success();
  }

  public static function String( $var, ?bool $empty = null, ?string $name = null, bool $reverse = false ) : bool
  {
    $bool = $bool ?? Vali::$_STRING_EMPTY;
    $name = $name ?? "Data";

    if ( !isset( $var )                ) return Vali::Error("$name not found."                     );
    if ( $reverse && is_string( $var ) ) return Vali::Error("$name is of an illegal type - string.");
    if ( !is_string( $var )            ) return Vali::Error("$name is not a string."               );
    if ( !$empty && $var == ""         ) return Vali::Error("$name is empty."                      );

    return Vali::Success();
  }

  public static function Decimal( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !isset( $var )      ) return Vali::Error("$name not found."    );
    if ( is_string( $var ) && $var != 0 && ltrim( $var,'-' )[0] == '0' && !$reverse            ) return Vali::Error("$name begins with 0, thus cannot be number.");
    if ( !is_numeric( $var )                                                                   ) return Vali::Error("$name is not number."                       );
    if ( $reverse && is_numeric( $var ) && filter_var( $var, FILTER_VALIDATE_FLOAT ) !== false ) return Vali::Error("$name is of an illegal type - decimal."     );
    if ( filter_var( $var, FILTER_VALIDATE_FLOAT ) === false                                   ) return Vali::Error("$name is not decimal."                      );

    return Vali::Success();
  }

  public static function Date( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name   = $name ?? "Data";
    $format = 'Y-m-d';
    $d      = \DateTime::createFromFormat( $format, $var );

    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    $is_date = $d && $d->format( $format ) === $var;

    if ( !isset( $var )       ) return Vali::Error("$name not found."                   );
    if ( $reverse && $is_date ) return Vali::Error("$name is of an illegal type - date.");
    if ( !$is_date            )  return Vali::Error("$name is not a date."              );

    return Vali::Success();
  }

  public static function Bool( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !isset( $var )                                  ) return Vali::Error("$name not found."                      );
    if ( $reverse && ( $var === false || $var === true ) ) return Vali::Error("$name is of an illegal type - logical.");
    if ( $var !== false && $var !== true                 ) return Vali::Error("$name is not logical type."            );

    return Vali::Success();
  }

  public static function Null( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( $reverse && $var === null ) return Vali::Error("$name is of an illegal type - NULL.");
    if ( $var !== null             ) return Vali::Error("$name is not NULL."                 );

    return Vali::Success();
  }

  public static function Array( $var, ?string $name = null, bool $empty = true, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !isset( $var )                  ) return Vali::Error("$name not found."                    );
    if ( $reverse && is_array( $var )    ) return Vali::Error("$name is of an illegal type - array.");
    if ( !is_array( $var )               ) return Vali::Error("$name is not array."                 );
    if ( !$empty && sizeof( $var ) === 0 ) return Vali::Error("$name cannot be empty."              );

    return Vali::Success();
  }

  public static function Assoc( $var, ?string $name = null, bool $empty = true, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !isset( $var ) ) return Vali::Error("$name not found.");

    $is_ar = is_array( $var ) && array() !== $var && array_keys( $var ) !== range( 0, count($var) - 1 );
    if ( $reverse && $is_ar              ) return Vali::Error("$name is of an illegal type - associative array.");
    if ( !$is_ar                          ) return Vali::Error("$name is not associative array."                 );
    if ( !$empty && sizeof( $var ) === 0 ) return Vali::Error("$name cannot be empty."                          );

    return Vali::Success();

  }

  public static function Object( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !isset( $var )                ) return Vali::Error("$name not found."                     );
    if ( $reverse && is_object( $var ) ) return Vali::Error("$name is of an illegal type - object.");
    if ( !is_object( $var )            ) return Vali::Error("$name is not an object."              );

    return Vali::Success();
  }

  public static function JS( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !Vali::String( $var, $name ) ) return false;

    $var = preg_replace('/\s+/', '', $var );
    $var = strtolower( $var );
    $pos = strpos( $var,"<script" );

        if ( $reverse  && $pos !== false ) return Vali::Error("$name contains js script."       );
    elseif ( !$reverse && $pos === false ) return Vali::Error("$name doesn't contain js script.");

    return Vali::Success();

  }

  public static function PHP( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !Vali::String( $var, $name ) ) return false;

    $var = preg_replace('/\s+/', '', $var);
    $var = strtolower( $var );
    $pos = strpos( $var, "<?" );

        if ( $reverse  && $pos !== false ) return Vali::Error("$name contains php script."       );
    elseif ( !$reverse && $pos === false ) return Vali::Error("$name doesn't contain php script.");

    return Vali::Success();

  }

  public static function JSON( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !isset( $var ) ) return Vali::Error("$name not found.");

    $json = json_decode( $var );
    $val  = $json && $var != $json;

    if ( $reverse  && $val  ) return Vali::Error("$name is of illegal format - JSON.");
    if ( !$reverse && !$val ) return Vali::Error("$name is not an JSON."             );

    return Vali::Success();
  }

  public static function Mail( $var, ?string $name = null, bool $reverse = false ) : bool
  {
    $name = $name ?? "Data";

    if ( !isset( $var ) ) return Vali::Error("$name not found.");

    $val = filter_var( $var, FILTER_VALIDATE_EMAIL );

    if ( $reverse  && $val  ) return Vali::Error("$name is a valid mail."   );
    if ( !$reverse && !$val ) return Vali::Error("$name is an invalid mail.");

    return Vali::Success();
  }

}
