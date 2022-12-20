<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
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
            $members = ProjectMember::where('project_id', '=', $projectId)->get();
            foreach($members as $member) $member['user_data'] = User::find($member['user_id']);

            return json_encode(['data' => $members]);
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
    public function store(Request $request, $projectId)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);
            $project = Project::find($projectId);

            if(!$project) {
                return json_encode(['message' => 'failed', 'data' => 'Project does not exist.']);
            }

            if($project->admin_id != auth()->user()->id) {
                return response()->json(['data' => 'Not the project admin.'], 401);
            }

            $newMemberEmail = $validated['email'];
            $newMember = User::where('email', '=', $newMemberEmail)->first();
            
            $projectMembers = ProjectMember::where('project_id', '=', $projectId)->get();
            
            
            foreach($projectMembers as $projectMember)
            {
                if($projectMember->user_id == $newMember->id) {

                    throw new \Exception('User is already a member.');
                }
            }

            $projectMemberRecord = new ProjectMember;
            $projectMemberRecord->user_id = $newMember->id;
            $projectMemberRecord->project_id = $projectId;
            $projectMemberRecord->user_email = $newMemberEmail;

            if(!$projectMemberRecord->save()) {
                throw new \Exception();
            }

            return json_encode(['data' => $projectMemberRecord]);

        } catch(\Exception $e) {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
        
    }

    public function delete(Request $request, $projectId)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);
            $project = Project::find($projectId);

            if(!$project) {
                return json_encode(['message' => 'failed', 'data' => 'Project does not exist.']);
            }

            if($project->admin_id != auth()->user()->id) {
                return response()->json(['data' => 'Not the project admin.'], 401);
            }

            $memberToDelEmail = $validated['email'];

            if($memberToDelEmail == auth()->user()->email) {
                throw new \Exception('Admin cannot be deleted');
            }

            $memberToDel = User::where('email', '=', $memberToDelEmail)->first();
            
            $recordToDel = ProjectMember::where('project_id', '=', $projectId)->where('user_id', '=', $memberToDel->id)->first();

            if(!$recordToDel->delete()) {
                throw new \Exception();
            }

            // Remove from any task in proj.
            $tasksWhereTheRemovedMemberIn  = Task::where('project_id', '=', $projectId)->where('assignee_id', '=', $memberToDel->id)->get();
            foreach($tasksWhereTheRemovedMemberIn as $t) {
                $t->assignee_id = null;
                if(!$t->save()) {
                    throw new \Exception('Failed to update assignee id of a task.');
                }
            }

            return json_encode(['data' => 'Deleted']);

        } catch(\Exception $e) {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
                
    }
}
