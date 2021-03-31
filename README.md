# Validate
Validates your data from predefined schema or by single method

```php
  $spec = [
    "set" => [
      "user_id" => [ "int"          , "ID"    ],
      "login"   => [ "string"       , "Login" ],
      "name"    => [ "string_empty" , "Name"  ],       
      "bio"     => [ ["!js","!php" ], "Bio"   ],       
      "add"     => [ "array"        , "Additional", [  
          "set" => [
            "created" => ["types" => "date"]           
          ],
          "free" => ["string","int"]                   
        ]
      ]
    ]
  ];

  $data = [
    "user_id" => 0,
    "login"   => "login",
    "name"    => "name",
    "bio"     => "",
    "add"     => [
      "created" => "2020-01-01",
      "nana",
      "age" => 12
    ]
   ];

  if ( !Vali::dat( $data, $spec ) ) echo Vali::GetError();
```

# How to use

## Method

All functions are static so you could call them anywhere, anytime. If you wanna check if your array is associative `Vali::Assoc( array( 'a' => 'b' ) )`, if you want to assign name to validation for clearer errors `Vali::Assoc( array( 'a' => 'b' ), 'My assoc array' )`, if you wanna check if it's not associative array `Vali::Assoc( array( 'a' => 'b' ), 'My assoc array', true )` or just `!Vali::Assoc( array( 'a' => 'b' ), 'My assoc array' )`. All function and their usage is explained below but you get the idea.

## Schema

Schema is predefined structure of data you want to validate. It helps if you recive multiple, nested variables in your API and don't wanna create hundreds of ifs to validate them.

Structure is divided into `set` and `free` variables. The `set` is validated first then all left values with `free`. You can assign them names (for geting clearer errors) and types. Available types :
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
 - mail
 - json (checks if string is available to be read as JSON)

It's possible to check if data isn't something. Just add `!` before type and it will revert it. Example: `[..] 'desc' => ['name' => 'Description', 'types' => ['!js','!php']] [..]`. Now it will return false if `desc` contains any js or php.

If you don't want to use `string_empty` because all your strings can be empty just change the default setting `Vali::$_STRING_EMPTY` to true. It will change the behaviour of `String` function to return true even if string is empty.

And finaly errors: when error occurrs (when variable isn't valid or doesn't exist) apropriate error will be saved into `$_LAST_ERROR` as a string. To get error you can use `Vali::GetError()`.

## Dynamic type assign

What's cool is that you can change the validated type of variable by sending diffrent data and using keyword `this`. The script will look out in current scope for indicated data and use it contents as `types`. Example :

```php
$spec = [
    "check" => [
      "set" => [
        "type" => [ "string"   , "Type of sent data" ],
        "data" => [ "this.type", "Data"              ]
      ]
    ]
  ];

  $data = [
    "type" => 'int',
    "data" => 'a',
  ];

  if ( !Vali::dat( $data, $spec ) ) echo Vali::GetError(); // output : Data is not a number.
```

# FUNCTIONS

## Legend:
 - `$var` - variable to validate
 - `$name` - the name to use in error
 - `$reverse` - reverse the behaviour of method

## Usage

 - `Vali::Int($var, ?string $name = null, bool $reverse = false)`
 - `Vali::String($var, bool $empty = false, ?string $name = null, bool $reverse = false)`
 - `Vali::Decimal($var, ?string $name = null, bool $reverse = false)`
 - `Vali::Day($var, ?string $name = null, bool $reverse = false)`
 - `Vali::Bool($var, ?string $name = null, bool $reverse = false)`
 - `Vali::Null($var, ?string $name = null, bool $reverse = false)`
 - `Vali::Array($var, ?string $name = null, bool $empty = true, bool $reverse = false)`
 - `Vali::Assoc($var, ?string $name = null, bool $empty = true, bool $reverse = false)`
 - `Vali::Object($var, ?string $name = null, bool $reverse = false)`
 - `Vali::JS($var, ?string $name = null, bool $reverse = false)`
 - `Vali::PHP($var, ?string $name = null, bool $reverse = false)`
 - `Vali::JSON($var, ?string $name = null, bool $reverse = false)`
 - `Vali::Mail($var, ?string $name = null, bool $reverse = false)`
