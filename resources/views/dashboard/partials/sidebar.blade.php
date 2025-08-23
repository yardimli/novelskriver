{{-- User Info --}}
<div class="d-flex align-items-center mb-4 justify-content-center justify-content-md-start">
	<img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=8b3dff&color=fff' }}" alt="avatar" class="avatar avatar-lg rounded-circle">
	<div class="ms-3">
		<h5 class="mb-0">{{ Auth::user()->name }}</h5>
		<small>Personal account</small>
	</div>
</div>

{{-- Mobile Menu Toggle --}}
<div class="d-md-none text-center d-grid">
	<button class="btn btn-light mb-3 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAccountMenu" aria-expanded="false" aria-controls="collapseAccountMenu">
		Account Menu
		<i class="bi bi-chevron-down ms-2"></i>
	</button>
</div>

{{-- Sidebar Navigation --}}
<div class="collapse d-md-block" id="collapseAccountMenu">
	<ul class="nav flex-column nav-account">
		<li class="nav-item">
			<a class="nav-link {{ $active === 'home' ? 'active' : '' }}" href="{{ route('dashboard') }}">
				<i class="align-bottom bx bx-home"></i>
				<span class="ms-2">Home</span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link {{ $active === 'profile' ? 'active' : '' }}" href="{{ route('profile.edit') }}">
				<i class="align-bottom bx bx-user"></i>
				<span class="ms-2">Profile</span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#">
				<i class="align-bottom bx bx-lock-alt"></i>
				<span class="ms-2">Security</span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#">
				<i class="align-bottom bx bx-credit-card-front"></i>
				<span class="ms-2">Billing</span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#">
				<i class="align-bottom bx bx-bell"></i>
				<span class="ms-2">Notifications</span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
				<i class="align-bottom bx bx-log-out"></i>
				<span class="ms-2">Sign Out</span>
			</a>
			<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
				@csrf
			</form>
		</li>
	</ul>
</div>
