<?php

function vj_render_sign_up(array $view_params): void
{ ?>
  <main class="registration main-padding">
    <form class="form-style-1" action="/sign-up" method="POST">

      <label class="label-style-1">
        Name
        <input class="input-style-1"
            type="text"
            name="name"
            autocomplete="name"
            placeholder="Name"
            <?= isset($view_params['post']['name']) ? "value='{$view_params['post']['name']}'" : ''; ?>>
      </label>

      <label class="label-style-1">
        Email
        <input class="input-style-1"
            type="email"
            name="email"
            autocomplete="email"
            placeholder="Email"
            <?= isset($view_params['post']['email']) ? "value='{$view_params['post']['email']}'" : ''; ?>>
      </label>

      <label class="label-style-1">
        Password, a-zA-Z0-9=+_!@#$%^&*()_||~)(
        <input class="input-style-1"
            type="password"
            name="password"
            autocomplete="new-password"
            placeholder="Enter password"
            <?= isset($view_params['post']['password']) ? "value='{$view_params['post']['password']}'" : ''; ?> >
      </label>
      <label class="label-style-1">
        Repeat password
        <input class="input-style-1" 
            type="password"
            name="confirm_password"
            autocomplete="new-password"
            placeholder="Repeat password">
      </label>

      <div class="from-style-1-wrapper">
        <button class="button-style-1" type="submit">Sign up</button>
      </div>

    </form>
      <?php if (isset($view_params['error'])): ?>
        <p class="error form-error-style-1"><?= $view_params['error'] ?></p>
      <?php endif; ?>
  </main>
<?php }