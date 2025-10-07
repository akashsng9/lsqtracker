<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\TeamConfigController;
use App\Http\Controllers\TLMasterController;
use App\Http\Controllers\CourseMasterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadSourceController;
use App\Http\Controllers\LeadSourceImportController;

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
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

Route::get('/lead/by-search-parameter', function() { return view('lead_by_search_parameter'); })->name('lead.by-search-parameter');

// Route::post('/lead/fetch-by-search-parameter', [LeadController::class, 'fetchAndStoreLeadsBySearchParameter'])->name('lead.fetch-by-search-parameter');
Route::match(['get', 'post'], 'lead/fetch-by-search-parameter', [LeadController::class, 'fetchAndStoreLeadsBySearchParameter'])->name('lead.fetch-by-search-parameter');

Route::get('/lead', [LeadController::class, 'getLeadById'])->name('lead.index');

Route::get('/lead-activity', [LeadController::class, 'getLeadActivity']);

// Course Master CRUD
Route::resource('/configuration/course', CourseMasterController::class)->parameters([
    'course' => 'course'
])->names([
    'index' => 'configuration.course.index',
    'create' => 'configuration.course.create',
    'store' => 'configuration.course.store',
    'edit' => 'configuration.course.edit',
    'update' => 'configuration.course.update',
    'destroy' => 'configuration.course.destroy',
    'show' => 'configuration.course.show',
]);

// Course ⇄ LC mapping
Route::get('/configuration/course/{course}/map-lcs', [CourseMasterController::class, 'mapLcs'])
    ->name('configuration.course.map-lcs');
Route::post('/configuration/course/{course}/map-lcs', [CourseMasterController::class, 'saveMapLcs'])
    ->name('configuration.course.map-lcs.save');

// Assigned Courses listing
Route::get('/configuration/assigned-courses', [CourseMasterController::class, 'assignedCourses'])
    ->name('configuration.assigned-courses.index');

Route::get('/lead/fetch/{leadId?}', [LeadController::class, 'fetchAndStoreLead'])->name('lead.fetch');
Route::get('/lead/fetch-activity', [LeadController::class, 'fetchAndStoreActivity'])->name('lead.fetch-activity');
Route::get('/lead/fetch-search-results', [LeadController::class, 'fetchAndStoreSearchResults'])->name('lead.fetch-search-results');

// Export lead + activities (no DB, JSON only) as CSV
Route::get('/lead/export/full', [LeadController::class, 'exportLeadFullDetails'])->name('lead.export.full');
Route::get('/lead/export/full/{leadId}', [LeadController::class, 'exportLeadFullDetails'])->name('lead.export.full.with-id');

// Test route for BySearchParameter API call
Route::get('/lead/test-by-search-parameter', [LeadController::class, 'testBySearchParam'])->name('lead.test-by-search-parameter');

// Configuration Routes
Route::get('/config/team', [TeamConfigController::class, 'getTeamConfig'])->name('config.team');
Route::post('/config/team', [TeamConfigController::class, 'saveTeamConfig'])->name('config.team.save');
Route::get('/config/lead-type', [TeamConfigController::class, 'getLeadTypeConfig'])->name('config.lead-type');
Route::post('/config/lead-type', [TeamConfigController::class, 'saveLeadTypeConfig'])->name('config.lead-type.save');

Route::get('/leads/hydrate', [LeadController::class, 'hydrateLeadsFromSearch'])
    ->name('leads.hydrate');

// Team Routes
Route::get('/team/mumbai', [TeamConfigController::class, 'mumbai'])
    ->name('team.mumbai');
Route::get('/team/gurgaon', [TeamConfigController::class, 'gurgao'])
    ->name('team.gurgaon');

// LC Management Routes
Route::get('/configuration/lc', [TeamConfigController::class, 'lcIndex'])
    ->name('configuration.lc.index');
Route::post('/configuration/lc/{id}/status', [TeamConfigController::class, 'updateLcStatus'])
    ->name('configuration.lc.updateStatus');
Route::post('/configuration/lc', [TeamConfigController::class, 'storeLc'])
    ->name('configuration.lc.store');

// LC ⇄ TL mapping
Route::post('/configuration/lc/{lcId}/map-tl', [TeamConfigController::class, 'saveMapTl'])
    ->name('configuration.lc.map-tl.save');

// LC ⇄ Course mapping (LC-centric)
Route::get('/configuration/lc/{lcId}/map-courses', [TeamConfigController::class, 'mapCourses'])
    ->name('configuration.lc.map-courses');
Route::post('/configuration/lc/{lcId}/map-courses', [TeamConfigController::class, 'saveMapCourses'])
    ->name('configuration.lc.map-courses.save');


// Lead Source Management
// Lead Source Management
Route::prefix('configuration/lead-sources')->name('lead-sources.')->group(function() {
    // Regular CRUD routes
    Route::get('/', [LeadSourceController::class, 'index'])->name('index');
    Route::get('/create', [LeadSourceController::class, 'create'])->name('create');
    Route::post('/', [LeadSourceController::class, 'store'])->name('store');
    
    // Import routes
    Route::get('/import', [LeadSourceImportController::class, 'create'])->name('import.form');
    Route::post('/import', [LeadSourceImportController::class, 'store'])->name('import');
    
    // Edit/Update/Delete routes
    Route::get('/{id}/edit', [LeadSourceController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LeadSourceController::class, 'update'])->name('update');
    Route::delete('/{id}', [LeadSourceController::class, 'destroy'])->name('destroy');
});

// TL Master CRUD
Route::resource('/configuration/tl', TLMasterController::class)->parameters([
    'tl' => 'tl'
])->names([
    'index' => 'configuration.tl.index',
    'create' => 'configuration.tl.create',
    'store' => 'configuration.tl.store',
    'edit' => 'configuration.tl.edit',
    'update' => 'configuration.tl.update',
    'destroy' => 'configuration.tl.destroy',
    'show' => 'configuration.tl.show',
]);
