<?php

namespace App\Http\Controllers;

use App\Models\Column;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectMember;
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
        $this->middleware('auth:api');
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
                'name' => 'string|required|unique:projects,name',
            ]);
    
            $project = new Project;
            $project->name = $validated['name'];
            $project->admin_id = auth()->user()->id;
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
        $project = Project::find($id);


        if(! $project) {
            return json_encode(['message' => 'failed', 'data' => 'No project with this id.']);
        }

        $cols = Column::where('project_id', '=', $id)->orderBy('order')->get();
        // Inserting columns' tasks in the columns.
        foreach($cols as $col) {
            $col->tasks = DB::table('tasks')->where('column_id', '=', $col->id)->orderBy('order')->get();
        }
        
        $project->columns = $cols;
        $project->members = DB::table('project_members')->where('project_id', '=', $id)->get()->pluck('user_id');

        return json_encode(['data' => $project]);
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
        try {
            $project = Project::find($id);

            if($project) {
                // Only the admin can update the name
                if($project->admin_id != auth()->user()->id) {
                    return response()->json(['data' => 'Not the project admin.'], 401);
                }

                $validated = $request->validate([
                    'name' => 'required|string|unique:projects,name,' . $id,
                ]);
    
                $project->name =  $validated['name'];
                
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
            // Only the admin can archive the project
            if($project->admin_id != auth()->user()->id) {
                return response()->json(['data' => 'Not the project admin.'], 401);
            }

            $project->archived = 1;
            
            return $project->save() ? json_encode(['data' => $project]) : json_encode(['message' => 'failed', 'data' => 'failed to archive project']);
        }    //

        return json_encode(['message' => 'failed', ' data' => 'Project does not exist']);
    }


    public function delete($id)
    {
        $message = '';
        $data = '';
        $project = Project::find($id);
        if($project) {
            // Only the admin can delete the project
            if($project->admin_id != auth()->user()->id) {
                return response()->json(['data' => 'Not the project admin.'], 401);
            }

            $message =  $project->delete() ? '' : 'failed';
        } else {
            $message = 'failed';
            $data = 'Project does not exist.';
        }

        return json_encode(['message' => $message, 'data' => $data]);      
    }
}
