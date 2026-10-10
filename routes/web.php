<?php

use App\Models\User;
use Inertia\Inertia;
use Termwind\render;
use App\Models\Answer;
use App\Models\Question;
use App\Models\SolveUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Cloudstudio\Ollama\Facades\Ollama;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SearchController;
use App\Http\Middleware\SolvesAdmin;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SolveAuthController;

Route::get("/" , function(Request $request){
    return view('welcome');
});

// Route::get('/', function () {
//     $response = Ollama::agent('You are a helpful assistant.')
//      ->options(['temperature' => 0.8])
//     ->prompt('Explain quantum computing in simple terms')
//     ->ask();

//     echo $response['response'];

//         return view('welcome');
//     });

Route::livewire("/help" , "pages::help-page")->name('help-page');

Route::livewire("/auth/login" , "pages::auth.login")->name('login');
Route::post("/auth/logout", [AuthController::class , 'logout'] )->name('logout');
Route::livewire("/tickets/create" , "pages::tickets.create")->name("create-ticket")->middleware('auth');
Route::livewire("/tickets/get-tickets" , "pages::view-tickets")->name("get-tickets");
Route::livewire("/users/chat/v1" , "pages::users.chat")->name("user-chat")->middleware('auth');
Route::livewire("/profile" , "pages::profile")->name("profile")->middleware('auth');

// support
Route::livewire("/support/dashboard" , "pages::ict.dashboard")->name("ict-dashboard")->middleware("auth:support");
Route::livewire("/support/manage-desk" , 'pages::ict.manage-desk');
Route::livewire("/support/manage-assets" , "pages::ict.assets")->name('manage-asset');
Route::livewire("/support/manage-desks" , "pages::ict.features.manage-desk")->name('manage-desk');
Route::livewire("/support/notifications/view" , "pages::ict.features.view-notification")->name('view-notification');
Route::livewire("/support/chat/v1" , "pages::ict.features.chat")->name("support-chat")->middleware("auth:support");
Route::livewire("/support/auth/login" , "pages::ict.auth.login")->name('ict-login');
Route::livewire("/support/my-tickets" , "pages::ict.features.my-tickets")->name("my-tickets");
Route::livewire("/support/system/health" , "pages::ict.settings.health")->name('system-health');
Route::livewire("/support/profile" , "pages::ict.profile")->name('support-profile')->middleware("auth:support");
Route::post("/support/auth/logout" , [AuthController::class , 'support_logout'])->name('support-logout');




// DISI-SOLVES
Route::get("/disi-solves/auth/login" , [SolveAuthController::class , 'render_login_page'] );
Route::post("/disi-solves/auth/login" , [SolveAuthController::class , 'login'] )->name('solves-login');
Route::post("/disi-solves/auth/logout" , [SolveAuthController::class , 'logout'] )->name('solves-logout');
Route::post("/disi-solves/questions/store" , [QuestionController::class , 'store'] )->name('solves-question-store')->middleware('auth:solves');
Route::get("/disi-solves/profile" , [ProfileController::class , 'show'] )->name('solves-profile')->middleware('auth:solves');
Route::post("/disi-solves/profile" , [ProfileController::class , 'update'] )->name('solves-profile-update')->middleware('auth:solves');
Route::delete("/disi-solves/profile/picture" , [ProfileController::class , 'destroyPicture'] )->name('solves-profile-picture-destroy')->middleware('auth:solves');
Route::get("/disi-solves/search" , [SearchController::class , 'index'])->name('solves-search')->middleware('auth:solves');
Route::get("/disi-solves/search/similar" , [SearchController::class , 'similar'])->name('solves-search-similar')->middleware('auth:solves');
Route::get("/disi-solves/activity" , [ActivityController::class , 'index'])->name('solves-activity')->middleware('auth:solves');

Route::get("/disi-solves/dashboard", function (Request $request) {
    /** @var \App\Models\SolveUser|null $user */
    $user = auth('solves')->user();
    $isAdmin = $user && $user->role === 'admin';

    $query = Question::with('author')
        ->withCount(['answer as answer_count' => fn ($q) => $q->where('status', 'approved')]);

    // 1. Base Status Scope: Admins can view all statuses; non-admins are restricted to 'approved'
    if (!$isAdmin) {
        $query->where('status', 'approved');
    } elseif ($request->filled('status') && $request->input('status') !== 'all') {
        // Admins filtering by a specific status (e.g., 'pending' or 'rejected')
        $query->where('status', $request->input('status'));
    }

    // 2. Search Filter (Title & Description)
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // 3. Category Filter
    if ($request->filled('category') && $request->input('category') !== 'all') {
        $query->where('category', $request->input('category'));
    }

    // 4. Sorting Logic
    switch ($request->input('sort', 'recent')) {
        case 'views':
            $query->orderBy('views', 'desc');
            break;
        case 'trending':
            $query->orderBy('views', 'desc')->orderBy('created_at', 'desc');
            break;
        case 'recent':
        default:
            $query->orderBy('created_at', 'desc');
            break;
    }

    return Inertia::render('Dashboard', [
        'questions' => $query->get(),
        'filters' => [
            'search' => $request->input('search', ''),
            'category' => $request->input('category', 'all'),
            'status' => $request->input('status', $isAdmin ? 'all' : 'approved'),
            'sort' => $request->input('sort', 'recent'),
        ],
        'userCount' => SolveUser::count(),
        'pendingApprovals' => Question::where('status', 'pending')->count() + Answer::where('status', 'pending')->count(),
        'totalQuestions' => Question::count(),
        'resolutionRate' => (function () {
            $approved = Question::where('status', 'approved')->count();

            return $approved === 0 ? 0 : (int) round(
                Question::where('status', 'approved')->whereHas('answer', fn ($q) => $q->where('status', 'approved'))->count() / $approved * 100
            );
        })(),
    ]);
})->name('solves-dashboard')->middleware("auth:solves");


Route::get("/disi-solves/{id}/details" , [AdminController::class , 'detail_page'])->name('solves-detail')->middleware('auth:solves');

Route::post('/disi-solves/questions/{question}/answers', [AnswerController::class, 'store'])
    ->name('solves.answers.store')->middleware('auth:solves');

// Owners may delete their own pending items; admins may delete anything (checked in the controllers).
Route::delete('/disi-solves/questions/{id}', [QuestionController::class, 'destroy'])->name('solves.questions.destroy')->middleware('auth:solves');
Route::delete('/disi-solves/answers/{id}', [AnswerController::class, 'destroy'])->name('solves.answers.destroy')->middleware('auth:solves');

// Admin-only
Route::middleware(['auth:solves', SolvesAdmin::class])->group(function () {
    Route::post("/disi-solves/admin/post" , [AdminController::class , 'store_question_answer'] )->name('solves-admin-store');
    Route::put("/disi-solves/admin/{id}/approve" , [AdminController::class , 'approve_question'])->whereNumber('id')->name('solves-admin-approve');
    Route::put("/disi-solves/admin/{id}/reject" , [AdminController::class , 'reject_question'])->whereNumber('id')->name('solves-admin-reject');
    Route::patch('/disi-solves/answers/{id}/approve', [AnswerController::class, 'approve'])->name('solves.answers.approve');
    Route::patch('/disi-solves/answers/{id}/reject', [AnswerController::class, 'reject'])->name('solves.answers.reject');

    Route::get("/disi-solves/admin/user-management" , [AdminController::class , 'render_users_page'])->name('solves-usr-mgmt');
    Route::post('/disi-solves/admin/add/users', [AdminController::class, 'store'])->name('solves.users.store');
    Route::put('/disi-solves/admin/{id}', [AdminController::class, 'update'])->whereNumber('id')->name('solves.users.update');
    Route::delete('/disi-solves/admin/users/{id}', [AdminController::class, 'destroy'])->name('solves.users.destroy');
});
