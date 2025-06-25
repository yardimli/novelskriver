<header>
	<nav class="navbar navbar-expand-lg navbar-light w-100">
		<div class="container px-3">
			<a class="navbar-brand" href="{{ url('/') }}"><img src="{{ asset('theme/assets/images/logo/logo.svg') }}" alt /></a>
			<button class="navbar-toggler offcanvas-nav-btn" type="button">
				<i class="bi bi-list"></i>
			</button>
			<div class="offcanvas offcanvas-start offcanvas-nav" style="width: 20rem">
				<div class="offcanvas-header">
					<a href="{{ url('/') }}" class="text-inverse"><img src="{{ asset('theme/assets/images/logo/logo.svg') }}" alt /></a>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body pt-0 align-items-center">
					<ul class="navbar-nav mx-auto align-items-lg-center">
						<li class="nav-item">
							<a class="nav-link" href="#" role="button" aria-expanded="false">Home</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#" role="button" aria-expanded="false">Features</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#" role="button" aria-expanded="false">Blog</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#" role="button" aria-expanded="false">Help</a>
						</li>
					
					</ul>
					<div class="mt-3 mt-lg-0 d-flex align-items-center">
						<a href="{{route('login')}}" class="btn btn-light mx-2">Login</a>
						<a href="{{route('register')}}" class="btn btn-primary">Create account</a>
					</div>
				</div>
			</div>
		</div>
	</nav>
</header>
