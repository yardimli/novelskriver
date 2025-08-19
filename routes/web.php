<?php

	use App\Http\Controllers\Admin\AdminAIController;
	use App\Http\Controllers\Admin\AdminTemplateCloneController;
	use App\Http\Controllers\Admin\BlogManagementController;
	use App\Http\Controllers\Admin\AdminDashboardController;
	use App\Http\Controllers\Auth\SocialLoginController;
	use App\Http\Controllers\BlogController;
	use App\Http\Controllers\ChangelogController;
	use App\Http\Controllers\ChapterController;
	use App\Http\Controllers\ChapterCodexController; // NEW: Import ChapterCodexController.
	use App\Http\Controllers\CodexEntryController;
	use App\Http\Controllers\CoverController;
	use App\Http\Controllers\DesignerController;
	use App\Http\Controllers\FavoriteController;
	use App\Http\Controllers\HomeController;
	use App\Http\Controllers\NovelController;
	use App\Http\Controllers\NovelEditorController;
	use App\Http\Controllers\PageController;
	use App\Http\Controllers\ProfileController;
	use App\Http\Controllers\SeriesController;
	use App\Http\Controllers\ShopController;
	use App\Http\Controllers\UserDesignController;
	use Illuminate\Support\Facades\Route;

	/*
	|--------------------------------------------------------------------------
	| Web Routes
	|--------------------------------------------------------------------------
	|
	| Here is where you can register web routes for your application. These
	| routes are loaded by the RouteServiceProvider and all of them will
	| be assigned to the "web" middleware group. Make something great!
	|
	*/

	Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
		// Route::middleware(['auth'])->group(function () { // Uncomment to protect admin routes
		Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

		Route::prefix('blog')->name('blog.')->group(function () {
			Route::get('/', [BlogManagementController::class, 'index'])->name('index');

			// AI Post Generation (endpoint remains, UI trigger will move or be re-evaluated)
			Route::post('/posts/generate-ai', [BlogManagementController::class, 'generateAiBlogPost'])->name('posts.generate-ai');

			// Categories (no changes here)
			Route::get('/categories', [BlogManagementController::class, 'listCategories'])->name('categories.list');
			Route::post('/categories', [BlogManagementController::class, 'storeCategory'])->name('categories.store');
			Route::put('/categories/{category}', [BlogManagementController::class, 'updateCategory'])->name('categories.update');
			Route::delete('/categories/{category}', [BlogManagementController::class, 'destroyCategory'])->name('categories.destroy');

			// Posts - Updated for page-based CRUD
			Route::get('/posts', [BlogManagementController::class, 'listPosts'])->name('posts.list'); // List remains AJAX populated
			Route::get('/posts/create', [BlogManagementController::class, 'create'])->name('posts.create'); // Page to create a post
			Route::post('/posts', [BlogManagementController::class, 'storePost'])->name('posts.store'); // Form submission for create
			Route::get('/posts/{post}/edit', [BlogManagementController::class, 'edit'])->name('posts.edit'); // Page to edit a post
			Route::post('/posts/{post}', [BlogManagementController::class, 'updatePost'])->name('posts.update'); // Form submission for update (using POST for simplicity with @method)
			Route::delete('/posts/{post}', [BlogManagementController::class, 'destroyPost'])->name('posts.destroy'); // Delete remains AJAX
		});
	});


	Route::get('/', [PageController::class, 'index'])->name('home');

	Route::get('/about-us', [HomeController::class, 'about'])->name('about');
	Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
	Route::get('/terms-and-conditions', function () {
		return view('terms');
	})->name('terms');

	Route::get('/privacy-policy', function () {
		return view('privacy');
	})->name('privacy');

	Route::get('/contact-us', [HomeController::class, 'showContactForm'])->name('contact.show');
	Route::post('/contact-us', [HomeController::class, 'submitContactForm'])->name('contact.submit');
	Route::get('/changelog', [ChangelogController::class, 'index'])->name('changelog.index');

	Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
	Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

	// Socialite Login Routes
	Route::get('/login/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('social.login');
	Route::get('/login/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('social.callback');


	Route::middleware('auth')->group(function () {
		Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');


		// Novel Creation Routes
		Route::get('/novels/create', [NovelController::class, 'create'])->name('novels.create');
		Route::post('/novels', [NovelController::class, 'store'])->name('novels.store');
		Route::post('/novels/generate-title', [NovelController::class, 'generateTitle'])->name('novels.generate-title');

		// Series Creation (for AJAX)
		Route::post('/series', [SeriesController::class, 'store'])->name('series.store');

		// Novel Structure Generation
		Route::post('/novels/{novel}/generate-structure', [NovelController::class, 'generateStructure'])->name('novels.generate-structure');

		// Novel Editor Routes
		Route::get('/novels/{novel}/edit', [NovelEditorController::class, 'index'])->name('novels.edit');
		Route::post('/novels/{novel}/editor-state', [NovelEditorController::class, 'saveState'])->name('novels.editor.save-state');

		// Chapter Route for editor window content.
		Route::get('/chapters/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');

		// NEW: Routes for linking/unlinking codex entries to chapters.
		Route::post('/chapters/{chapter}/codex-entries/{codexEntry}', [ChapterCodexController::class, 'attach'])->name('chapters.codex.attach');
		Route::delete('/chapters/{chapter}/codex-entries/{codexEntry}', [ChapterCodexController::class, 'detach'])->name('chapters.codex.detach');

		// Codex Entry Routes
		Route::get('/novels/codex-entries/{codexEntry}', [CodexEntryController::class, 'show'])->name('codex-entries.show');
		Route::post('/codex-entries/{codexEntry}/generate-image', [CodexEntryController::class, 'generateImage'])->name('codex-entries.generate-image');
		Route::post('/codex-entries/{codexEntry}/upload-image', [CodexEntryController::class, 'uploadImage'])->name('codex-entries.upload-image');

		Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
		Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
		Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
	});


	require __DIR__.'/auth.php';
