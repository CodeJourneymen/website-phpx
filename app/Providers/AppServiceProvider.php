<?php

namespace App\Providers;

use App\Models\ExternalGroup;
use App\Models\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Lorisleiva\Actions\Facades\Actions;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		//
	}
	
	public function boot(): void
	{
		Model::unguard();
		
		Actions::registerCommands();
		
		Route::middleware('web')->group(fn() => Actions::registerRoutes());
		
		$this->sharePhpxNetwork();
	}
	
	protected function sharePhpxNetwork()
	{
		$network = Cache::remember('phpx-network', now()->addWeek(), function() {
			return Group::query()
				->select('domain', 'name', 'region')
				->get()
				->mapWithKeys(fn(Group $group) => [$group->domain => $group->label()])
				->toArray();
		});
		
		$external = Cache::remember('phpx-network-external', now()->addWeek(), function() {
			try {
				return ExternalGroup::query()
					->select('domain', 'name', 'region')
					->get()
					->mapWithKeys(fn(ExternalGroup $g) => [$g->domain => $g->label()])
					->toArray();
			} catch (Throwable) {
				return [];
			}
		});
		
		View::share('phpx_network', $network);
		View::share('phpx_external', $external);
	}
}
