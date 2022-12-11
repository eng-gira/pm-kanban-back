<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function __construct()
    {
        /**
         * @todo Uncomment after building and testing.
         */
        // $this->middleware('auth:api', ['except' => ['index', 'show']]);
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
            return json_encode(['data' => Column::where('project_id', '=', $projectId)->get()]);
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
    
            if(! Project::find($projectId)) {
                throw new \Exception('Project does not exist');
            }
    
            $column = new Column;
            $column->name = $validated['name'];
            $column->project_id = $projectId;
            if(! $column->save())
            {
                throw new \Exception ('Failed');
            }

            $column->tasks = [];

            return json_encode(['data' => $column]);

        } catch(\Exception $e)
        {
            return json_encode(['data' => $e->getMessage()]);
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
        
        return json_encode(['data' => $column]);
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

            $column->name = $validated['name'];
            
            if(! $column->save())
            {
                throw new \Exception('Failed to save updated column.');
            }

            return json_encode(['data' => $column]);

        } catch(\Exception $e)
        {
            return json_encode(['data' => $e->getMessage()]);
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

            if(! $column->destroy($id))
            {
                throw new \Exception('Failed to delete column.');
            }

            return json_encode(['data' => 'Deleted']);

        } catch(\Exception $e)
        {
            return json_encode(['data' => $e->getMessage()]);
        }        
    }
}
