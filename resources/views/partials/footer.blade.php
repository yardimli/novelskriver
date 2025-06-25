<footer class="pt-7">
	<div class="container">
		<!-- Footer 4 column -->
		<div class="row mb-7 gy-4">
			<div class="col-xl-3 col-lg-4 col-12">
				<div class="mb-4">
					<a href="#" class="text-inverse">
						<img src="{{ asset('theme/assets/images/logo/logo.svg') }}" alt="logo" />
					</a>
					<div class="mt-4">
						<p>
							123 SEO Street, Suite 456,
							<br />
							Optimization City, ST 78901
						</p>
						<span>(123) 456-7890</span>
						<a href="#">contact@agencyname.com</a>
					</div>
					<div class="d-flex align-items-center mt-4">
						<div class="me-3 d-flex gap-2">
							{{-- Social Links --}}
							<a href="#!" class="text-reset btn btn-instagram btn-icon btn-sm">
								<i class="bi bi-instagram"></i>
							</a>
							<a href="#!" class="text-reset btn btn-facebook btn-icon btn-sm">
								<i class="bi bi-facebook"></i>
							</a>
							<a href="#!" class="text-reset btn btn-twitter btn-icon btn-sm">
								<i class="bi bi-twitter"></i>
							</a>
						</div>
						<div class="dropdown">
							<button class="btn btn-light btn-icon btn-sm rounded-circle d-flex align-items-center" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
								<i class="bi theme-icon-active"></i>
								<span class="visually-hidden bs-theme-text">Toggle theme</span>
							</button>
							<ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bs-theme-text">
								<li>
									<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
										<i class="bi theme-icon bi-sun-fill"></i>
										<span class="ms-2">Light</span>
									</button>
								</li>
								<li>
									<button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
										<i class="bi theme-icon bi-moon-stars-fill"></i>
										<span class="ms-2">Dark</span>
									</button>
								</li>
								<li>
									<button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
										<i class="bi theme-icon bi-circle-half"></i>
										<span class="ms-2">Auto</span>
									</button>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			{{-- Footer Links Columns --}}
			<div class="col-lg-2 col-12">
				{{-- Company Links --}}
			</div>
			<div class="col-lg-2 col-12">
				{{-- Industry Links --}}
			</div>
			<div class="col-lg-2 col-12">
				{{-- Service Links --}}
			</div>
			<div class="col-lg-2 col-12">
				{{-- Locations Links --}}
			</div>
		</div>
	</div>
</footer>

<div class="btn-scroll-top">
	<svg class="progress-square svg-content" width="100%" height="100%" viewBox="0 0 40 40">
		<path d="M8 1H32C35.866 1 39 4.13401 39 8V32C39 35.866 35.866 39 32 39H8C4.13401 39 1 35.866 1 32V8C1 4.13401 4.13401 1 8 1Z" />
	</svg>
</div>

<!-- Libs JS -->
<script src="{{ asset('theme/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('theme/assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
<script src="{{ asset('theme/assets/libs/headhesive/dist/headhesive.min.js') }}"></script>

<!-- Theme JS -->
<script src="{{ asset('theme/assets/js/theme.min.js') }}"></script>

<script src="{{ asset('theme/assets/js/vendors/partical.js') }}"></script>
<script src="{{ asset('theme/assets/libs/embla-carousel/embla-carousel.umd.js') }}"></script>
<script src="{{ asset('theme/assets/libs/embla-carousel-auto-scroll/embla-carousel-auto-scroll.umd.js') }}"></script>
<script src="{{ asset('theme/assets/js/vendors/embla.js') }}"></script>
<script src="{{ asset('theme/assets/libs/scrollcue/scrollCue.min.js') }}"></script>
<script src="{{ asset('theme/assets/js/vendors/scrollcue.js') }}"></script>
