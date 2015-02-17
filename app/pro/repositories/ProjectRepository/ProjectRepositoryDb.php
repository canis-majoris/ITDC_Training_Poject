<?php
namespace pro\repositories\ProjectRepository;

use Moltin\Currency\Currency as Currency;
use Moltin\Currency\Format\Runtime as RuntimeFormat;
use Moltin\Currency\Exchange\Runtime as RuntimeExchange;

use Project;
use Hash;
use Redirect;
use Project_type;
use Validator;
use Auth;
use User;
use Skill;
use Input;
use Response;
use View;
use Config;

class ProjectRepositoryDb implements ProjectRepositoryInterface {

	protected $exchange;

	protected $timespan = [
		'Lesst than 1 week',
		'1 week - 4 weeks',
		'1 month - 3 months',
		'3 months - 6 months',
		'Over 6 months/Ongoing',
		'Not sure'
	];
	protected $currencies = [
		'USD' => 'USD',
		'GEL' => 'GEL',
		'AUD' => 'AUD',
		'CAD' => 'CAD',
		'EUR' => 'EUR',
		'GBP' => 'GBP'
	];

	public function all() {
		return Project::orderBy('created_at', 'desc')->with('users')->paginate(10);
	}

	public function byId($id) {
		return Project::find($id);
	}
	public function user() {
		return Auth::user();
	}
	public function createOrUpdate($input, $project, $id) {
		if ($input) {
			///if there is an input
			if ($id) {
			///update

			########
			
			} else {
				///create
			    $input['user_id'] = $this->user()->id;
			    $input['duration'] = $this->timespan[$input['duration']];
			    if (isset($input['pt_type'])) {
			    	$types = '';
			    	$ptoject_types = $input['pt_type'];
			    	foreach ($ptoject_types as $v) {
			    		$types .= $v.'|';
			    	}
			    $project->project_type_id = $types;

			    }
			    $project->fill($input);
			    $project->active = 1;
				$project->save();
			}

			if (Input::file('file')!=null) {
				$projectAttachmentName = str_random(40).'.'.Input::file('file')->guessClientExtension();
				Input::file('file')->move('./public/uploads/projects',$projectAttachmentName);
				$project->files = $projectAttachmentName;
			}
		}

		$skills = null; $levels_sk = null;
		if (isset($input['skill']) && array_filter($input['level'])) {
			$skills = $input['skill'];
			$levels_sk = $input['level'];
		}

		// All data to update
		$updateData = [
			'skills' => $skills,
			'levels_sk' => $levels_sk
		];
		$this->updateAll($project, $updateData);

		$project->save();


		return $project;
	}

	public function getCreate(){
		$project_types = Project_type::all();
		$skills = Skill::all();
		$data = [
			'project_types' => $project_types,
			'currencies'    => $this->currencies,
			'timespan'      => $this->timespan,
			'skills'        => $skills
		];
		return $data;
	}

	public function show($id) {
		$project = $this->byId($id);
		$creator = User::find($project->user_id);
		$currUser = $this->user();

		///get all bidders
		$bidders = User::whereHas('projects', function($q) use($id) {
			$q->where('project_id', '=', $id);
			##############
		})->with('projects')->get();

		$data = [
			'project'   	=> $project,
			'creator'   	=> $creator,
			'bidders'   	=> $bidders,
			'currUser'  	=> $currUser,
			'timespan'  	=> $this->timespan,
			'currencies'	=> $this->currencies,
			'skills'        => $project->skills
		];

		return $data;
	}

	public function delete($id) {
		$project = $this->byId($id);
		if(is_null($project)) {
			return Redirect::back();
		}
		$project->delete();
	}

	public function bid($input) {

		$user_id = $this->user()->id;
		$user = User::find($user_id);

		$id = $input['project_id'];
		$project = $this->byId($id);
		if (!isset($user->projects()->where('project_id', '=', $project->id)->first()->pivot)) {

			$this->exchange = new RuntimeExchange;

			$this->exchange->add('GEL', 2.05);
			$this->exchange->add('USD', 1.0);
			$this->exchange->add('EUR', 0.89);
			$this->exchange->add('AUD', 1.28);
			$this->exchange->add('GBP', 0.66);
			$this->exchange->add('CAD', 1.25);

			$currency = new Currency($this->exchange, new RuntimeFormat);
			$convertedPrice = $currency->convert($input['price'])->from($input['bid_currency'])->to($project->currency)->fifty()->value();
			$project_bid_count = $project->bid_count;
			$project_avg_price = $project->avg_price;

			$project->avg_price = ($project_avg_price * $project_bid_count + $convertedPrice)/($project_bid_count + 1);
			$project->bid_count = ++$project_bid_count;

			$project->save();

			$sth = [
				'user_id' 		=> $this->user()->id,
				'project_id' 	=> $id,
				'bid_price'		=> $input['price'], 
				'comment'		=> $input['description'], 
				'duration'		=> $this->timespan[$input['duration']],
				'bid_currency'	=> $this->currencies[$input['bid_currency']]
			];

			$project->users()->attach($id, $sth);

			return Redirect::back()
				->with('message_type','success')
				->with('message', 'You Have Bidded!');
		} else {
			return Redirect::back()
				->with('message_type','danger')
				->with('message', 'You Have Already Bidded This Project!');
		}	

		return Redirect::back()
			->with('message_type','danger')
			->with('message', 'Ooops.... Something went wrong...');
	}
	
	public function my($user){
		$user = $user->first();
		$projects = Project::where('user_id', '=', $user->id)->get();
		$bids = $user->projects()->get();
		$data = [
			'projects' => $projects,
			'bids' => $bids
		];

		return $data;
	}

	public function unbid($id){

		$this->exchange = new RuntimeExchange;

		$this->exchange->add('USD', 1.0);
		$this->exchange->add('EUR', 0.89);
		$this->exchange->add('GEL', 2.05);
		$this->exchange->add('AUD', 1.28);
		$this->exchange->add('GBP', 0.66);
		$this->exchange->add('CAD', 1.25);

		$user = $this->user();
		$bid = $user->projects()->where('project_id', '=', $id)->first()->pivot;
		$project = $this->byId($id);

		$currency = new Currency($this->exchange, new RuntimeFormat);
		$convertedPrice = $currency->convert($bid->bid_price)->from($bid->bid_currency)->to($project->currency)->fifty()->value();

		$project_bid_count = $project->bid_count;
		
		if ($project_bid_count == 1) {
			$project->avg_price = 0;
		}else{
			$project->avg_price = ($project->avg_price * $project_bid_count - $convertedPrice)/($project_bid_count - 1);
		}

		$project->bid_count = --$project_bid_count;
		$project->save();
		$user->projects()->detach($id);
	}
	public function sort($input){
		if (isset($input['sorter'])) {
			$sorter = $input['sorter'];
			$arr = explode('.', $sorter);
			$projects = Project::orderBy($arr[0], $arr[1])->get();
			$view = View::make('ITDC_Project.home.project.load_projects')->with(['projects' => $projects])->render();
			return Response::json($view);
		}
	}


	public function updateAll($project, $updateData){
		if (isset($updateData['skills']) && isset($updateData['levels_sk'])) {
			$this->manageSkills($project, $updateData['skills'], $updateData['levels_sk']);
		}
	}

	public function manageSkills($project, $skills, $levels){
		$sl = [];
		foreach ($skills as $skill) {
			if (!empty($levels[$skill])) {
				$sl[$skill] = ['level' => $levels[$skill]];
			}
			$project->skills()->sync($sl);
		}
	}
	//////////////////////////////////////
}