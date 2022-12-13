<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            return json_encode(['data' => DB::table('tasks')->where('column_id', '=', $columnId)->orderBy('order')->get()]);
        } catch(\Exception $e)
        {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
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
                'name' => 'required',
                'description' => ''
            ]);
            
            $column = Column::find($columnId);
            if(! $column) {
                throw new \Exception('Column does not exist');
            } else {
                $colTasks = Task::where('column_id', '=', $columnId)->get();
                $countTasks = count($colTasks);
                if($countTasks > 99) {
                    throw new \Exception('Reached the limit of tasks per column. Can not exceed ' . $countTasks . ' tasks.');
                }
            }

            $task = new Task;
            $task->name = $validated['name'];
            $task->description = $validated['description'] ?? '';
            $task->column_id = $columnId;

            // last in asc is the first in desc
            $lastTask = DB::table('tasks')->where('column_id', '=', $columnId)->orderBy('order', 'desc')->first();
            $task->order = $lastTask !== null ? intval($lastTask->order) + 1 : 0;


            if(! $task->save())
            {
                throw new \Exception ('Failed');
            }

            return json_encode(['data' => $task]);

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
        $task = Task::find($id);
        
        return !$task ? json_encode(['message' => 'failed', 'data' => 'No task with this id.']) : 
            json_encode(['data' => $task]);
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
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
    }

    public function relocate(Request $request, $id) {
        try {
            $validated = $request->validate([
                'tasksOrderInTargetCol' => 'required|array',
                // 'orderOfBeforeTask' => '',
                // 'orderOfAfterTask' => '',
                'targetColId' => 'required|integer'
            ]);

            if(! Task::find($id)) {
                throw new \Exception('No task with id ' . $id . ' was found.');
            }
            
            $targetCol = Column::find($validated['targetColId']);
            if(! $targetCol) {
                throw new \Exception('No col with id ' . $validated['targetColId'] . ' was found.');
            }

            $tasksOrderInTargetCol = $validated['tasksOrderInTargetCol'];

            $counter = 0;
            foreach($tasksOrderInTargetCol as $id) {
                $t = Task::find($id);
                $t->order = $counter;
                $t->column_id = $validated['targetColId'];

                if(! $t->save()) {
                    throw new \Exception('Failed to save relocated task at i = ' . $counter);
                }
                $counter++;
            }
            return json_encode(['data' => $tasksOrderInTargetCol]);
        }
        catch (\Exception $e) {
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
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }        
    }
}
