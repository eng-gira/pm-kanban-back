<?php

namespace App\Http\Controllers;

use App\Models\Column;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ProjectController extends Controller
{
    public function __construct()
    {
        /**
         * @todo uncomment after testing
         */
        // $this->middleware('auth:api', ['except' => ['index', 'show', 'archive']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return Project::where('archived', '<>', 1)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        try {

            $validated = $request->validate([
                'title' => 'string|required|unique:projects,title',
            ]);
    
            $project = new Project;
            $project->title = $validated['title'];
            return $project->save() ? json_encode(['data' => Project::find($project->id)]) : json_encode(['message' => 'failed']);
        } catch(\Exception $e)
        {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $project = Project::find($id);

        $cols = Column::where('project_id', '=', $id)->get();
        // Inserting columns' tasks in the columns.
        $project->columns = array_map(function($col) {
            $col->tasks = Task::where('col_id', '=', $col->id)->get();
            return $col;
        }, $cols); 

        return json_encode(['data' => $project]) ;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        try {
            $project = Project::find($id);
            if($project) {
                $validated = $request->validate([
                    'title' => 'string|unique:projects,title,' . $id,
                    'budget' => 'integer',
                    'total_spent_money' => 'integer',
                    'status' => 'integer|min:0|max:1',
                    'description' => 'string',
                    'completed_at' => 'string|min:10', // e.g. 2022-11-19
                    'currency' => 'string'
                ]);
    
                if(count($validated) < 1) throw new \Exception('Empty payload was received!');

                $project->title =  $validated['title'] ?? $project->title;
                $project->budget = $validated['budget'] ?? $project->budget;
                $project->total_spent_money = $validated['total_spent_money'] ?? $project->total_spent_money;
                $project->status = $validated['status'] ?? $project->status;
                $project->description = $validated['description'] ?? $project->description;
                // Only update completed_at if status is actually 1 (indicating completion)
                if(isset($validated['status']) && $validated['status'] == 1) {
                    $project->completed_at = $validated['completed_at'] ?? Carbon::now();
                }
                $project->currency = $validated['currency'] ?? $project->currency;

                $project->next_to_do = $request['next_to_do'] ?? $project->next_to_do;
                
                return $project->save() ? json_encode(['data' => $project]) : ['message' => 'failed', 'data' => 'failed to update project'];
            }
            else 
            {
                throw new \Exception('Project does not exit');
            }
        } catch(\Exception $e)
        {
            return json_encode(['message'=>'failed', 'data'=>$e->getMessage()]);
        }
        

    }

    /**
     * Get archived projects
     */
    public function archive(){
        return json_encode(['data' => DB::table('projects')->where('archived', '=', 1)->get() ]);
    }

    /**
     * Add project to archive
     */
    public function addToArchive($id)
    {
        $project = Project::find($id);
        if($project) {
            $project->archived = 1;
            
            return $project->save() ? json_encode(['data' => $project]) : json_encode(['message' => 'failed', 'data' => 'failed to archive project']);
        }    //

        return json_encode(['message' => 'failed', ' data' => 'Project does not exist']);
    }


    public function delete($id)
    {
        $data = '';
        $project = Project::find($id);
        if($project) {
            $data =  $project->delete() ? 'Successfully deleted project' : 'failed to delete project';
        } else {
            $data = 'Project does not exist';
        }

        return json_encode(['data' => $data]);      
    }
}
