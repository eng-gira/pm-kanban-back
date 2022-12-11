<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
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
    public function index($columnId)
    {
        //
        try {
            return json_encode(['data' => Task::where('column_id', '=', $columnId)->get()]);
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
    public function store(Request $request, $columnId)
    {
        try {

            $validated = $request->validate([
                'name' => 'required|min:3',
                'description' => ''
            ]);
    
            if(! Column::find($columnId)) {
                throw new \Exception('Column does not exist');
            }
    
            $task = new Task;
            $task->name = $validated['name'];
            $task->description = $validated['description'] ?? '';
            $task->column_id = $columnId;
            if(! $task->save())
            {
                throw new \Exception ('Failed');
            }

            return json_encode(['data' => $task]);

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
        $task = Task::find($id);
        
        return json_encode(['data' => $task]);
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
                'description' => '',
            ]);
            $task = Task::find($id);

            if(! $task) {
                throw new \Exception('No task with id ' . $id);
            }

            $task->name = $validated['name'];
            $task->description = $validated['description'] ?? $task->description;
            
            if(! $task->save())
            {
                throw new \Exception('Failed to save updated task.');
            }

            return json_encode(['data' => $task]);

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
            $task = Task::find($id);

            if(! $task) {
                throw new \Exception('No task with id ' . $id);
            }

            if(! $task->destroy($id))
            {
                throw new \Exception('Failed to delete task.');
            }

            return json_encode(['data' => 'Deleted']);

        } catch(\Exception $e)
        {
            return json_encode(['data' => $e->getMessage()]);
        }        
    }
}
