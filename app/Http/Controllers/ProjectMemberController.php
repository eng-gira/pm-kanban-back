<?php

namespace App\Http\Controllers;

use App\Models\ProjectMember;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
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
    public function index($projectId)
    {
        //
        try {
            return json_encode(['data' => ProjectMember::where('project_id', '=', $projectId)->orderBy('order')->get()]);
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
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $member = ProjectMember::find($id);
        
        return json_encode(['data' => $member]);
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

    public function delete($id)
    {
        $data = '';
        $member = ProjectMember::find($id);
        if($member) {
            $data =  $member->delete() ? 'Successfully deleted member' : 'failed to delete member';
        } else {
            $data = 'Member does not exist';
        }

        return json_encode(['data' => $data]);      
    }
}
