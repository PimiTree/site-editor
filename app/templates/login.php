<?php

function vj_render_login(array $view_params): void
{ ?>
  <main class="login main-padding">
  <form class="form-style-1" action="/login" method="POST">

    <label class="label-style-1">
      Email
      <input class="input-style-1"
          type="email"
          name="email"
          placeholder="Email"
          <?= isset($view_params['email']) ? 'value="' . $view_params['email'] . '"' : '' ?>
      >
    </label>

    <label class="label-style-1">
      Password
      <input class="input-style-1" type="password" name="password" autocomplete="off" placeholder="Enter password">
    </label>

    <div class="from-style-1-wrapper">
      <button class="button-style-1" type="submit">Login</button>

      <a class="reset-password-link" href="/reset-password">Forgot password?</a>
    </div>

  </form>

    <?php if (isset($view_params['password_verify_result']) && $view_params['password_verify_result'] === true):  ?>
      <p>Password verified</p>
    <?php elseif (isset($view_params['error'])):; ?>
      <p class="error"><?= $view_params["error"] ?></p>
    <?php endif; ?>

  </main>
<?php }