<?php require_once '../Vali.class.php'; ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Test</title>
    <style media="screen">
      * {
        padding: 0;
        margin: 0;
        font-family: Verdana, serif;
        color : #333;
      }

      .box {
        max-width: 1560px;
        margin: auto;
      }

      .title {
        padding: 0 20px;
        margin: 20px 0;
        border-bottom: 5px solid #95a5a6;
      }

      .tests {
        background-color: #f2f2f2;
        padding: 20px;
        border-radius: 5px;
      }

      .tests span.value,
      .tests span.result {
        display: inline-block;
      }

      .tests span.value {
        min-width: 175px;
        text-align: center;
      }

      .tests span.result.false {
        color: #e74c3c;
      }

      .tests span.result.false::before {
        content: "false";
      }

      .tests span.result.true {
        color: #2ecc71;
      }

      .tests span.result.true::before {
        content: "true";
      }
    </style>
  </head>
  <body>
    <div class="box">
      <?php
        $methods = get_class_methods("Vali");

        $GetError = array_search("GetError", $methods );
        $Error    = array_search("Error"   , $methods );
        $Success  = array_search("Success" , $methods );
        $Ctrl     = array_search("Ctrl"    , $methods );
        $dat      = array_search("dat"     , $methods );

        unset( $methods[$GetError] );
        unset( $methods[$Error   ] );
        unset( $methods[$Success ] );
        unset( $methods[$Ctrl    ] );
        unset( $methods[$dat     ] );

        $json = new stdClass();
        $json->a = "b";
      ?>
      <?php foreach ($methods as $method): ?>
        <h1 class="title">Vali::<?= $method; ?></h1>
        <div class="tests">
          <p> <span class="value">1</span> : <span class="result <?= Vali::$method(1) ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">""</span> : <span class="result <?= Vali::$method("") ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">"1"</span> : <span class="result <?= Vali::$method("1") ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">"a"</span> : <span class="result <?= Vali::$method("a") ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">"01"</span> : <span class="result <?= Vali::$method("01") ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">1.1</span> : <span class="result <?= Vali::$method(1.1) ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">[]</span> : <span class="result <?= Vali::$method([]) ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">["a","b"]</span> : <span class="result <?= Vali::$method(["a","b"]) ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">["a" => "b"]</span> : <span class="result <?= Vali::$method(["a" => "b"]) ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">true</span> : <span class="result <?= Vali::$method(true) ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">"2020-12-01"</span> : <span class="result <?= Vali::$method("2020-12-01") ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">null</span> : <span class="result <?= Vali::$method(null) ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">new stdClass</span> : <span class="result <?= Vali::$method(new stdClass()) ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">"<?= htmlspecialchars("<script></script>") ?>"</span> : <span class="result <?= Vali::$method("<script></script>") ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">"<?= htmlspecialchars("<?php ?>") ?>"</span> : <span class="result <?= Vali::$method("<?php ?>") ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">"{ "a" : "b" }"</span> : <span class="result <?= Vali::$method('{ "a" : "b" }') ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">"{ 'a' : "b" }"</span> : <span class="result <?= Vali::$method('{ \'a\' : "b" }') ? "true" : "false"; ?>"></span> </p>
          <p> <span class="value">"mail@mail.com"</span> : <span class="result <?= Vali::$method('mail@mail.com') ? "true" : "false"; ?>"></span> </p>
        </div>
      <?php endforeach; ?>
    </div>
  </body>
</html>
