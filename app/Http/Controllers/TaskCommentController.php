<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
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
    public function index($taskId)
    {
        //
        try {
            $this->checkIfCanAccessTaskComments($taskId);

            $comments = TaskComment::where('task_id', '=', $taskId)->get();

            return json_encode(['data' => $comments]);
        } catch (\Exception $e) {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $taskId)
    {
        try {
            $this->checkIfCanAccessTaskComments($taskId);

            $validated = $request->validate([
                'body' => 'required'
            ]);

            $taskComment = new TaskComment;
            $taskComment->body = $validated['body'];
            $taskComment->task_id = $taskId;
            $taskComment->author_id = auth()->user()->id;

            if(!$taskComment->save()) {
                throw new \Exception('Could not save comment.');
            }

            return json_encode(['data' => $taskComment]);
        }
        catch (\Exception $e) {
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
        try {
            $taskComment = TaskComment::find($id);
            if(! $taskComment) {
                throw new \Exception('Comment does not exist.');
            }

            $this->checkIfCanAccessTaskComments($taskComment->task_id);

            return json_encode(['data' => $taskComment]);
        }
        catch (\Exception $e) {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
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
            $taskComment = TaskComment::find($id);
            if(! $taskComment) {
                throw new \Exception('Comment does not exist.');
            }

            if($taskComment->author_id != auth()->user()->id) {
                throw new \Exception('Not the comment author.');
            }

            $validated = $request->validate([
                'body' => 'required'
            ]);

            $taskComment->body = $validated['body'];

            if(!$taskComment->save()) {
                throw new \Exception('Could not update comment.');
            }

            return json_encode(['data' => $taskComment]);
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
        try {
            $taskComment = TaskComment::find($id);
            if(! $taskComment) {
                throw new \Exception('Comment does not exist.');
            }
            
            if($taskComment->author_id != auth()->user()->id) {
                throw new \Exception('Not the comment author.');
            }

            if(!$taskComment->delete()) {
                throw new \Exception('Could not delete comment.');
            }

            return json_encode(['data' => 'Deleted']);
        }
        catch (\Exception $e) {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
    }

    private function checkIfCanAccessTaskComments($taskId) {
        $task = Task::find($taskId);
        if($task) {
            $col = Column::find($task->column_id);

            if(!$col) {
                throw new \Exception('Can not access comment');
            }
            $projectId = $col->project_id;

            // quick check
            if(Project::find($projectId)->admin_id == auth()->user()->id) return true;

            $projectMembers = ProjectMember::where('project_id', '=', $projectId)->get();
    
            foreach($projectMembers as $member) {
                if($member->user_id == auth()->user()->id) return true;
            }
        }
        throw new \Exception('Can not access task');
    }
}
