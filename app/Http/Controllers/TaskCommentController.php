<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\ProjectMember;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($taskId)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function checkIfCanAccessTaskComment($taskId) {
        $task = Task::find($taskId);
        if($task) {
            $col = Column::find($task->column_id);

            if(!$col) {
                throw new \Exception('Can not access task');
            }
            $projectId = $col->project_id;

            $projectMembers = ProjectMember::where('project_id', '=', $projectId)->get();
    
            foreach($projectMembers as $member) {
                if($member->user_id == auth()->user()->id) return true;
            }
        }
        throw new \Exception('Can not access task');
    }
}
