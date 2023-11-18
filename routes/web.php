<?php

use App\Http\Controllers\Account\NotificationController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Client\ClientCompanyController;
use App\Http\Controllers\Client\ClientUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DropdownValuesController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MyWork\ActivityController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Settings\LabelController;
use App\Http\Controllers\Settings\OwnerCompanyController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\Task\AttachmentController;
use App\Http\Controllers\Task\GroupController;
use App\Http\Controllers\Task\TimeLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('mail', function () {
//     return (new App\Notifications\UserCreatedNotification('secret'))->toMail(App\Models\User::first());
// });

Route::group(['middleware' => ['auth:sanctum']], function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class)->except(['show']);

    Route::group(['prefix' => 'projects', 'as' => 'projects.'], function () {
        // PROJECT
        Route::post('{projectId}/restore', [ProjectController::class, 'restore'])->name('restore');
        Route::put('{project}/favorite/toggle', [ProjectController::class, 'favoriteToggle'])->name('favorite.toggle');
        Route::post('{project}/user-access', [ProjectController::class, 'userAccess'])->name('user_access');

        // TASK GROUPS
        Route::post('{project}/task-groups', [GroupController::class, 'store'])->name('task-groups.store');
        Route::put('{project}/task-groups/{taskGroup}', [GroupController::class, 'update'])->name('task-groups.update');
        Route::delete('{project}/task-groups/{taskGroup}', [GroupController::class, 'destroy'])->name('task-groups.destroy');
        Route::post('{project}/task-groups/{taskGroupId}/restore', [GroupController::class, 'restore'])->name('task-groups.restore');
        Route::post('{project}/task-groups/reorder', [GroupController::class, 'reorder'])->name('task-groups.reorder');

        // TASKS
        Route::get('{project}/tasks', [TaskController::class, 'index'])->name('tasks');
        Route::post('{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('{project}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::get('{project}/tasks/{task}/open', [TaskController::class, 'index'])->name('tasks.open');

        Route::post('{project}/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
        Route::post('{project}/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
        Route::post('{project}/tasks/move', [TaskController::class, 'move'])->name('tasks.move');

        Route::post('{project}/tasks/{task}/attachments/upload', [AttachmentController::class, 'store'])->name('tasks.attachments.upload');
        Route::delete('{project}/tasks/{task}/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('tasks.attachments.destroy');

        Route::post('{project}/tasks/{task}/time-log', [TimeLogController::class, 'store'])->name('tasks.time-logs.store');
        Route::delete('{project}/tasks/{task}/time-log/{timeLog}', [TimeLogController::class, 'destroy'])->name('tasks.time-logs.destroy');
        Route::post('{project}/tasks/{task}/time-log/timer/start', [TimeLogController::class, 'startTimer'])->name('tasks.time-logs.timer.start');
        Route::post('{project}/tasks/{task}/time-log/{timeLog}/timer/stop', [TimeLogController::class, 'stopTimer'])->name('tasks.time-logs.timer.stop');
    });

    // My Work
    Route::group(['prefix' => 'my-work', 'as' => 'my-work.'], function () {
        Route::resource('tasks', TaskController::class)->except(['show']);
        Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
    });

    // Clients
    Route::group(['prefix' => 'clients', 'as' => 'clients.'], function () {
        Route::resource('users', ClientUserController::class)->except(['show']);
        Route::post('users/{userId}/restore', [ClientUserController::class, 'restore'])->name('users.restore');

        Route::resource('companies', ClientCompanyController::class)->except(['show']);
        Route::post('companies/{companyId}/restore', [ClientCompanyController::class, 'restore'])->name('companies.restore');
    });

    // Users
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('users/{userId}/restore', [UserController::class, 'restore'])->name('users.restore');

    // Invoices
    Route::resource('invoices', InvoiceController::class)->except(['show']);

    // Reports
    Route::resource('reports', ReportController::class)->except(['show']);

    // Settings
    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::get('company', [OwnerCompanyController::class, 'edit'])->name('company.edit');
        Route::put('company', [OwnerCompanyController::class, 'update'])->name('company.update');

        Route::resource('roles', RoleController::class)->except(['show']);
        Route::post('roles/{roleId}/restore', [RoleController::class, 'restore'])->name('roles.restore');

        Route::resource('labels', LabelController::class)->except(['show']);
        Route::post('labels/{labelId}/restore', [LabelController::class, 'restore'])->name('labels.restore');
    });

    // Account
    Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
        Route::resource('profile', ProfileController::class)->except(['show']);
        Route::resource('notifications', NotificationController::class)->except(['show']);
    });

    // Notifications
    Route::put('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('dropdown/values', DropdownValuesController::class)->name('dropdown.values');
});
