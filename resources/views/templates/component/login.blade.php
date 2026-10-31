<div class="modal video-modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        Sign In & Sign Up
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>						
      </div>
      <section>
        <div class="modal-body">
          <div class="w3_login_module">
            <div class="module form-module">
              <div class="toggle"><i class="fa fa-times fa-pencil"></i>
              <div class="tooltip">Click Me</div>
              </div>
              <div class="form">
              <h3>Login to your account</h3>
              @if ($errors->login->any())
                <div class="alert alert-danger" role="alert">
                  @foreach ($errors->login->all() as $error)
                    <div>{{ $error }}</div>
                  @endforeach
                </div>
              @endif
              <form action="{{ route('login.store') }}" method="post">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" autocomplete="email" required>
                <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                <label><input type="checkbox" name="remember" value="1"> Remember me</label>
                <input type="submit" value="Login">
              </form>
              </div>
              <div class="form" style="{{ $errors->register->any() ? 'display:block;' : '' }}">
              <h3>Create an account</h3>
              @if ($errors->register->any())
                <div class="alert alert-danger" role="alert">
                  @foreach ($errors->register->all() as $error)
                    <div>{{ $error }}</div>
                  @endforeach
                </div>
              @endif
              <form action="{{ route('register.store') }}" method="post">
                @csrf
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Name" autocomplete="name" required>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" autocomplete="email" required>
                <input type="password" name="password" placeholder="Password (minimum 8 characters)" autocomplete="new-password" required>
                <input type="password" name="password_confirmation" placeholder="Confirm Password" autocomplete="new-password" required>
                <input type="submit" value="Register">
              </form>
              </div>
              <div class="cta"><a href="#" data-toggle="modal" data-target="#myModal">Login / Register</a></div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<script>
  $('.module .toggle').click(function(){
    // Switches the Icon
    $(this).children('i').toggleClass('fa-pencil');
    // Switches the forms  
    $('.module .form').animate({
    height: "toggle",
    'padding-top': 'toggle',
    'padding-bottom': 'toggle',
    opacity: "toggle"
    }, "slow");
  });
  $('[data-auth-form]').click(function () {
    var showRegister = $(this).data('auth-form') === 'register';
    var registerForm = $('.module .form').eq(1);

    if (showRegister !== registerForm.is(':visible')) {
      $('.module .toggle').trigger('click');
    }
  });
  $(function () {
    @if ($errors->login->any() || session('open_login'))
      $('#myModal').modal('show');
    @elseif ($errors->register->any() || session('open_register'))
      $('.module .toggle').trigger('click');
      $('#myModal').modal('show');
    @endif
  });
</script>