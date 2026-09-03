<?php

use App\Http\Controllers\Admin\ArticleCommentController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ContactLeadController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomeSlideController;
use App\Http\Controllers\Admin\HouseDesignController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectDocumentController;
use App\Http\Controllers\Admin\ProjectIssueController;
use App\Http\Controllers\Admin\ProjectStepController;
use App\Http\Controllers\Admin\ProjectUpdateController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TwoFactorAuthenticationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserSecurityController;
use App\Http\Controllers\Admin\WelcomePopupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientProjectController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectIssueMediaController;
use App\Http\Controllers\ProjectMediaController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\RequiredPasswordChangeController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StoredFileController;
use App\Http\Controllers\TwoFactorChallengeController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicSiteController::class)->group(function (): void {
    Route::get('/', 'home')->name('site.home');
    Route::get('/about', 'about')->name('site.about');
    Route::get('/services', 'services')->name('site.services');
    Route::get('/house-designs', 'houseDesigns')->name('site.house-designs.index');
    Route::get('/house-designs/{slug}', 'houseDesign')->name('site.house-designs.show');
    Route::get('/updates', 'updates')->name('site.updates');
    Route::get('/blog', 'blog')->name('site.blog.index');
    Route::get('/blog/{slug}', 'article')->name('site.blog.show');
    Route::get('/faq', 'faq')->name('site.faq');
    Route::get('/contact', 'contact')->name('site.contact');
    Route::get('/sitemap.xml', 'sitemap')->name('site.sitemap');
});

Route::view('/terms-of-service', 'site.legal.terms')->name('legal.terms');
Route::view('/privacy-policy', 'site.legal.privacy')->name('legal.privacy');
Route::view('/cookie-policy', 'site.legal.cookies')->name('legal.cookies');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->defaults('portal', 'customer')->name('login');
    Route::get('/login/customer', [AuthController::class, 'showLogin'])->defaults('portal', 'customer')->name('login.customer');
    Route::get('/login/inspector', [AuthController::class, 'showLogin'])->defaults('portal', 'inspector')->name('login.inspector');
    Route::get('/login/admin', [AuthController::class, 'showLogin'])->defaults('portal', 'admin')->name('login.admin');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->name('two-factor.challenge.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/change-password', [RequiredPasswordChangeController::class, 'edit'])->name('password.change-required');
    Route::put('/change-password', [RequiredPasswordChangeController::class, 'update'])->name('password.change-required.update');
});

Route::get('/project-media/{media}', [ProjectMediaController::class, 'show'])
    ->middleware('auth')
    ->name('project-media.show');

Route::get('/media/{file}', [StoredFileController::class, 'show'])
    ->name('stored-files.show');

Route::middleware('auth')->group(function (): void {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/project-documents/{document}', [ProjectDocumentController::class, 'show'])
        ->middleware('throttle:30,1')
        ->name('project-documents.show');
    Route::get('/project-issue-media/{media}', [ProjectIssueMediaController::class, 'show'])->name('project-issue-media.show');
});

Route::middleware(['auth', 'role:user'])->group(function (): void {
    Route::get('/my-projects', [ClientProjectController::class, 'index'])->name('client.projects.index');
    Route::get('/my-projects/{project}', [ClientProjectController::class, 'show'])->name('client.projects.show');
});

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
        Route::get('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');

        Route::post('/profile/two-factor/start', [TwoFactorAuthenticationController::class, 'start'])->name('profile.two-factor.start');
        Route::post('/profile/two-factor/confirm', [TwoFactorAuthenticationController::class, 'confirm'])->name('profile.two-factor.confirm');
        Route::delete('/profile/two-factor', [TwoFactorAuthenticationController::class, 'destroy'])->name('profile.two-factor.destroy');

        Route::middleware(['role:admin,inspector', 'staff.2fa'])->group(function (): void {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
            Route::get('/projects/{project}', [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
            Route::get('/projects/{project}/updates/create', [ProjectUpdateController::class, 'create'])->name('project-updates.create');
            Route::post('/projects/{project}/updates', [ProjectUpdateController::class, 'store'])->name('project-updates.store');
            Route::get('/projects/{project}/updates/{update}/edit', [ProjectUpdateController::class, 'edit'])->name('project-updates.edit');
            Route::put('/projects/{project}/updates/{update}', [ProjectUpdateController::class, 'update'])->name('project-updates.update');
            Route::post('/projects/{project}/issues', [ProjectIssueController::class, 'store'])->name('project-issues.store');
            Route::put('/projects/{project}/issues/{issue}', [ProjectIssueController::class, 'update'])->name('project-issues.update');
            Route::delete('/projects/{project}/issues/{issue}/media/{media}', [ProjectIssueController::class, 'destroyMedia'])->name('project-issues.media.destroy');
        });

        Route::middleware(['role:admin', 'staff.2fa'])->group(function (): void {
            Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('/customers/{customer}', [CustomerController::class, 'show'])->whereNumber('customer')->name('customers.show');
            Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->whereNumber('customer')->name('customers.edit');
            Route::put('/customers/{customer}', [CustomerController::class, 'update'])->whereNumber('customer')->name('customers.update');
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
            Route::get('/users/{user}/security', [UserSecurityController::class, 'show'])->name('users.security.show');
            Route::put('/users/{user}/security/password', [UserSecurityController::class, 'resetPassword'])->name('users.security.password');
            Route::put('/users/{user}/security/unlock', [UserSecurityController::class, 'unlock'])->name('users.security.unlock');
            Route::get('/settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SiteSettingController::class, 'update'])->name('settings.update');
            Route::post('/projects/{project}/restore', [ProjectController::class, 'restore'])
                ->whereNumber('project')
                ->name('projects.restore');
            Route::resource('projects', ProjectController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
            Route::post('/projects/{project}/steps', [ProjectStepController::class, 'store'])->name('project-steps.store');
            Route::put('/projects/{project}/steps/{step}', [ProjectStepController::class, 'update'])->name('project-steps.update');
            Route::put('/projects/{project}/steps/{step}/progress', [ProjectStepController::class, 'updateProgress'])->name('project-steps.progress');
            Route::delete('/projects/{project}/steps/{step}', [ProjectStepController::class, 'destroy'])->name('project-steps.destroy');
            Route::post('/projects/{project}/documents', [ProjectDocumentController::class, 'store'])->name('project-documents.store');
            Route::delete('/projects/{project}/documents/{document}', [ProjectDocumentController::class, 'destroy'])->name('project-documents.destroy');
            Route::delete('/projects/{project}/issues/{issue}', [ProjectIssueController::class, 'destroy'])->name('project-issues.destroy');
            Route::put('/projects/{project}/updates/{update}/approve', [ProjectUpdateController::class, 'approve'])->name('project-updates.approve');
            Route::put('/projects/{project}/updates/{update}/request-changes', [ProjectUpdateController::class, 'requestChanges'])->name('project-updates.request-changes');
            Route::delete('/projects/{project}/updates/{update}', [ProjectUpdateController::class, 'destroy'])->name('project-updates.destroy');
            Route::delete('/projects/{project}/updates/{update}/media/{media}', [ProjectUpdateController::class, 'destroyMedia'])->name('project-updates.media.destroy');
            Route::post('/articles/markdown', [ArticleController::class, 'importMarkdown'])->name('articles.markdown');
            Route::post('/articles/media', [ArticleController::class, 'uploadMedia'])->name('articles.media');
            Route::get('/articles/{article}/preview', [ArticleController::class, 'preview'])->name('articles.preview');
            Route::resource('articles', ArticleController::class)->except(['show']);
            Route::get('/comments', [ArticleCommentController::class, 'index'])->name('comments.index');
            Route::put('/comments/{comment}/status', [ArticleCommentController::class, 'updateStatus'])->name('comments.status');
            Route::put('/comments/{comment}/reply', [ArticleCommentController::class, 'reply'])->name('comments.reply');
            Route::delete('/comments/{comment}', [ArticleCommentController::class, 'destroy'])->name('comments.destroy');
            Route::get('/contact-leads', [ContactLeadController::class, 'index'])->name('contact-leads.index');
            Route::put('/contact-leads/{contactLead}', [ContactLeadController::class, 'update'])->name('contact-leads.update');
            Route::post('/contact-leads/{contactLead}/convert', [ContactLeadController::class, 'convert'])->name('contact-leads.convert');
            Route::delete('/contact-leads/{contactLead}', [ContactLeadController::class, 'destroy'])->name('contact-leads.destroy');
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
            Route::resource('home-slides', HomeSlideController::class)->except(['show']);
            Route::resource('welcome-popups', WelcomePopupController::class)->except(['show']);
            Route::delete('/house-designs/{houseDesign}/gallery/{image}', [HouseDesignController::class, 'destroyImage'])->name('house-designs.gallery.destroy');
            Route::resource('house-designs', HouseDesignController::class)->except(['show']);
        });
    });
