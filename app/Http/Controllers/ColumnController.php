<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColumnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($projectId)
    {
        //
        try {
            $this->checkIfCanAccessColumn(-1, $projectId);

            return json_encode(['data' => Column::where('project_id', '=', $projectId)->orderBy('order')->get()]);
        } catch(\Exception $e)
        {
            return json_encode(['data' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $projectId)
    {
        try {

            $validated = $request->validate([
                'name' => 'required|min:3',
            ]);
            
            $project = Project::find($projectId);
            $projCols = Column::where('project_id', '=', $projectId)->orderBy('order', 'desc')->get(); // sorted for the usage later in 'order'
            
            
            if(! $project) {
                throw new \Exception('Project does not exist');
            } else {
                $countProjCols = count($projCols);
                if ($countProjCols > 49) {
                    throw new \Exception('Reached limit of columns per project. Can not exceed ' . $countProjCols . ' columns.');
                }
            }
            
            $this->checkIfCanAccessColumn(-1, $projectId);


            $column = new Column;
            $column->name = $validated['name'];
            $column->project_id = $projectId;
            $column->order = count($projCols) > 0 ? intval( ($projCols[0])->order ) + 1 : 0; // first in desc has the highest order val.
            if(! $column->save())
            {
                throw new \Exception ('Failed');
            }

            $column->tasks = [];

            return json_encode(['data' => $column]);

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
        $column = Column::find($id);
        try {
            $this->checkIfCanAccessColumn($id);
        } catch (\Exception $e) {
            return response()->json(['data' => $e->getMessage()], 401);
        }

        return !$column ? json_encode(['message' => 'failed', 'data' => 'No column with that id']) : json_encode(['data' => $column]);
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
            $validated = $request->validate([
                'name' => 'required|min:3',
            ]);

            $column = Column::find($id);

            if(! $column) {
                throw new \Exception('No column with id ' . $id);
            }

            $this->checkIfCanAccessColumn($id);

            $column->name = $validated['name'];
            
            if(! $column->save())
            {
                throw new \Exception('Failed to save updated column.');
            }

            return json_encode(['data' => $column]);

        } catch(\Exception $e)
        {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
    }

    public function changeOrder(Request $request, $projectId) {
        try {
            $validated = $request->validate([
                'orderedCols' => 'required|array'
            ]);

            //
            $this->checkIfCanAccessColumn(-1, $projectId);

            $projectCols = DB::table('columns')->where('project_id', '=', $projectId)->get();
            $orderedCols = $validated['orderedCols'];

            if(count($projectCols) != count($orderedCols)) throw new \Exception('All cols of the project must be included.');

            $counter = 0;
            foreach($orderedCols as $colId) {
                $column = Column::find($colId);
                if(! $column) {
                    throw new \Exception('No column with id ' . $colId);
                } elseif($column->project_id != $projectId) {
                    throw new \Exception('Columns should all be part of the same project');
                }

                $column->order = $counter;
                if(! $column->save())
                {
                    throw new \Exception('Failed to save updated column.');
                }

                $counter++;
            }

            return json_encode(['data' => $orderedCols]);

        } catch(\Exception $e) {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        //
        try {
            $column = Column::find($id);

            if(! $column) {
                throw new \Exception('No column with id ' . $id);
            }

            $this->checkIfCanAccessColumn($id);

            if(! $column->destroy($id))
            {
                throw new \Exception('Failed to delete column.');
            }

            return json_encode(['data' => 'Deleted']);

        } catch(\Exception $e)
        {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }        
    }

    private function checkIfCanAccessColumn($columnId = -1, $projectId = -1) {
        if($projectId == -1)
        {
            $col = Column::find($columnId);
            if(!$col) {
                throw new \Exception('Can not access column');
            }

            $projectId = $col->project_id;
        }
        $projectMembers = ProjectMember::where('project_id', '=', $projectId)->get();

        foreach($projectMembers as $member) {
            if($member->user_id == auth()->user()->id) return true;
        }
        
        throw new \Exception('Can not access column');
    }
}
