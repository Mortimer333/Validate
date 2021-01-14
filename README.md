# Validate
Validates your data from pre defined schema or from single function

# Example

```php
  $spec = [
    "check" => [
      "indexes" => [
        "user_id" => ["name" => "ID"        , "types" => "int"          ],
        "login"   => ["name" => "Login"     , "types" => "string"       ],
        "name"    => ["name" => "Name"      , "types" => "string_empty" ],       // checks if string and returns true even if empty
        "bio"     => ["name" => "Name"      , "types" => ["!js","!php" ]],       // will check if contains php or js script
        "add"     => ["name" => "Additional", "types" => "array", "check" => [   // it's possible to nest schemats
            "indexes" => [
              "created" => ["types" => "date"]                                   // name is optional
            ],
            "types"   => ["string","int"]                                        // check if whole array contains only strings or ints except "created" variable
          ]
        ]
      ],
      "types"   => []
    ]
  ];

  $data = [
    "user_id" => 0,
    "login"   => "login",
    "name"    => "name",
    "bio"     => "",
    "add"     => [
      "created" => "2020-01-01",
      "surname" => "nana",
      "age"     => 12
    ]
   ];

  if(!Vali::date($data,$spec)) print_r(Vali::GetError());
```

# How to use 

## Single function

All functions are static so you could call them anywhere, anytime. If you wanna check if your array is associative `Vali::Assoc( array( 'a' => 'b' ) )`, if you want to assign name to validation for clearer errors `Vali::Assoc( array( 'a' => 'b' ), 'My assoc array' )`, if you wanna check if it's not associative array `Vali::Assoc( array( 'a' => 'b' ), 'My assoc array', true )` or just `!Vali::Assoc( array( 'a' => 'b' ), 'My assoc array' )`. All function and their usage is written lower but you get the idea. 

## Schema

Schema is predefined structure of data you want to validate. It helps if you recive multiple, nested variables in your API and don't wanna create hundreds of ifs to validate them. 

Structure is divided into _indexed variables_ and _loose ones_. The indexed are validated first and you can set them in check->indexes (as shown above). You can assign them names (for geting clearer errors) and types. Available types :
 - int
 - decimal
 - array
 - bool (boolean)
 - date
 - string (will return false if string is empty)
 - string_empty (will return true if string is empty)
 - object
 - null
 - js (will return true if string contains javascript)
 - php (will return true if string contains php)

It's possible to check if data isn't something. Just add `!` before type and it will revers it. Example: `[..] 'desc' => ['name' => 'Description', 'types' => ['!js','!php']] [..]`. Now it will return false if `desc` contains any js or php.

If you don't want to use `string_empty` because all your strings can be empty just change the default setting for checking them `$_STRING_EMPTY` to true. It wil change behaviour of String function to return true even if string is empty. (WORKS ONLY WHEN USING SCHEMA)

And lastly errors: when error occurrs (when variable isn't valid or doesn't exist) apropriate error will be saved into `$_LAST_ERROR` as array ['error' => 'Error occurred!']. To get error back use `Vali::GetError()`.

## Dynamic type assign

What's cool is that you can change the validated type of variable by sending diffrent data and using keyword `this`. The script will look out in current scope for indicated data and use it contents as `types`. Example :

```php
$spec = [
    "check" => [
      "indexes" => [
        "type" => ["name" => "Type of sent data", "types" => "string"   ],
        "data" => ["name" => "Data"             , "types" => "this.type"],

      ],
      "types"   => []
    ]
  ];

  $data = [
    "type" => 'int',
    "data" => 'a',
  ];

  if(!Vali::date($data,$spec)) print_r(Vali::GetError());
  
  // output : Array ( [error] => `Data` is not a number. ) 
```

# FUNCTIONS

## Legend:
 - `$var` - variable to validate
 - `$name` - the name to use in error
 - `$reverse` - reverse the behaviour of function 

## Usage

 - `Vali::Int($var, $name = false, $reverse = false)` - check if `$var` is integer
 - `Vali::String($var, $empty = false, $name = false, $reverse = false)` - check if `$var` is string, change `$empty` to true if you wanna get return if string is empty
 - `Vali::Decimal($var, $name = false, $reverse = false)` - check if `$var` is float/double/decimal
 - `Vali::Day($var, $name = false, $reverse = false)` - check if `$var` id a date, for now format is 'Y-m-d' but in future you will be able to specify it while calling this function or in schema
 - `Vali::Bool($var, $name = false, $reverse = false)` - check if `$var` is boolean (true or false)
 - `Vali::Null($var, $name = false, $reverse = false)` - check if `$var` is null
 - `Vali::Array($var, $name = false, $empty = true, $reverse = false)` - check if `$var` is array, set `$empty` to false to get false if array is empty
 - `Vali::Assoc($var, $name = false, $empty = true, $reverse = false)` - check if `$var` is assoc array, set `$empty` to false to get false if array is empty
 - `Vali::Object($var, $name = false, $reverse = false)` - check if `$var` is object
 - `Vali::JS($var, $name = false, $reverse = false)` - check if `$var` contains javascript [EXPERIMENTAL] (work in progress)
 - `Vali::PHP($var, $name = false, $reverse = false)` - check if `$var` contains php [EXPERIMENTAL] (work in progress)
