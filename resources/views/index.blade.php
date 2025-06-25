@extends('layouts.app')

@section('title', 'Landing AI Studio - Home')

@section('content')
	<!--Hero section start-->
	<section
		id=""
		class="particals py-md-10 py-5"
		style="background: url({{ asset('theme/assets/images/ai-studio/ai-hero-glow.png') }}) no-repeat; background-size: cover; background-position: center"
		data-cue="fadeIn">
		<canvas id="starCanvas"></canvas>
		<div class="container py-xl-10">
			<div class="row py-xl-4">
				<div class="col-xxl-8 offset-xxl-2 col-xl-8 offset-xl-2 col-lg-10 offset-lg-1 col-12">
					<div class="text-center d-flex flex-column gap-6" data-cue="zoomIn">
						<div class="d-flex flex-column gap-3">
							<h1 class="display-4 mb-0"><span class="gradient-text">Write Your Next Bestseller</span></h1>
							<p class="mb-0 lead px-xxl-8">The complete novel writing platform with AI assistance, powerful planning tools, worldbuilding codex, and collaborative features designed to transform your ideas into published stories.</p>
						</div>
						<div class="d-flex flex-row gap-3 justify-content-center">
							<a href="{{route('register')}}" class="btn btn-primary">Start Writing for Free</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!--Hero section end-->
	
	<!-- Features Section Start -->
	<section class="py-xl-9 pb-lg-9 pt-5 pb-6" data-cue="fadeIn">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="text-center mb-xl-7 mb-5 d-flex flex-column gap-2">
						<h2 class="mb-0"><span class="gradient-text">A Feature for Every Step of Your Journey</span></h2>
						<p class="mb-0 lead">From the first spark of an idea to the final draft, we've got you covered.</p>
					</div>
				</div>
			</div>
			
			@php
				// Define an array of gradient classes to cycle through for visual appeal.
				// Make sure these classes (e.g., .bg-pink-gradient) are defined in your CSS.
				$gradients = ['bg-pink-gradient', 'bg-info-gradient', 'bg-success-gradient', 'bg-purple-gradient', 'bg-warning-gradient'];
			@endphp
			
			@foreach ($features as $category => $data)
				<!-- Category Header -->
				<div class="row mt-8 mb-4">
					<div class="col-xl-5 col-lg-10 col-12">
						<h1 class="mb-3">{{ $category }}</h1>
						<p class="lead mb-0">{{ $data['description'] }}</p>
					</div>
				</div>
				
				<!-- Feature Items -->
				<div class="row g-5">
					@foreach ($data['items'] as $item)
						<div class="col-lg-4 col-md-6 col-12" data-cue="fadeInUp">
							<a href="#!" class="text-decoration-none">
								<div class="card card-lift h-100">
									<div class="card-body d-flex flex-column gap-4 p-5">
										<div class="d-flex flex-column gap-2">
											<h4 class="mb-0 fs-4">{{ $item['title'] }}</h4>
											<p class="mb-0">{{ $item['description'] }}</p>
										</div>
										<img src="{{ asset('theme/assets/images/index/' . $item['image']) }}" alt="{{ $item['title'] }}" style="width: 100%; object-fit: contain;">
									</div>
								</div>
							</a>
						</div>
					@endforeach
				</div>
			@endforeach
		
		</div>
	</section>
	<!-- Features Section End -->
	
	<!--Call to action start-->
	<section data-cue="fadeIn" class="py-lg-9 py-md-8 py-5" style="background: url({{ asset('theme/assets/images/ai-studio/cta-glows.png') }}) no-repeat; background-size: cover; background-position: center">
		<div class="container">
			<div class="row">
				<div class="col-xxl-6 offset-xxl-3 col-12">
					<div class="d-flex flex-column gap-6">
						<div class="text-center d-flex flex-column gap-2" data-cue="zoomOut">
							<h2 class="mb-0 display-6">Ready to Write Your Next Bestseller?</h2>
							<p class="mb-0 px-xl-5 lead">Join thousands of authors using Novelskriver's AI-powered tools, comprehensive planning features, and collaborative workspace to bring their stories to life.</p>
						</div>
						<div class="d-flex flex-row gap-3 align-content-center justify-content-center">
							<a href="#!" class="btn btn-primary">Start Writing Free</a>
						</div>
						<div class="d-flex justify-content-center">
							<small class="fw-medium">No credit card required. Full access to manuscript, planning, and codex features.</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!--Call to action end-->
@endsection
