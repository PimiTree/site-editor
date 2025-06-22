<?php function vj_render_header_template(array $header_params): void
{ ?>
  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $header_params['title'] ?></title>
    <link rel="icon" href="/assets/images/favicon.svg">

    <link rel="stylesheet" href="/assets/css/index.css">

    <?php if (isset($header_params['stylesheets']) && is_array($header_params['stylesheets'])) {
      foreach ($header_params['stylesheets'] as $stylesheet) {
        echo "<link rel='stylesheet' type='text/css' href='" . $stylesheet . "'>\n";
      }
    } ?>

  </head>
  <body>
  
  <header>
    <ul>
      <li>
        <a href="/sign-up">Sign in</a>
        <a href="/sign-up">Sign up</a>
        <a href="/sign-out">Sign out</a>
      </li>
    </ul>
  </header>
<?php } ?>




<?php function vj_render_footer_template(): void { ?>
  <script defer src="/assets/js/index.js"></script>
</body>
  </html>

<?php } ?>
