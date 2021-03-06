<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\Applicant;
use App\Models\Reference;
use App\Models\Project;
use App\Models\Lookup\Relationship;
use App\Services\Validation\Requests\UpdateReferenceValidator;

class ReferencesController extends Controller
{

    /**
     * Display the Skills page associated with the applicant.
     *
     * @param  \App\Models\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function show(Applicant $applicant)
    {
        //

    }

    /**
     * Show the form for editing the applicant's references
     *
     * @param  Request  $request
     * @param  \App\Models\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Applicant $applicant)
    {
        return view('applicant/profile_04_references', [
            'applicant' => $applicant,
            'profile' => Lang::get('applicant/profile_references'),
            'reference_template' => Lang::get('common/references'),
            'relationships' => Relationship::all(),
            'form_submit_action' => route('profile.references.update', $applicant),
        ]);
    }

    /**
     * Update all the applicant's references in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Applicant  $applicant
     * @return \Illuminate\Http\Response
     */
    public function updateAll(Request $request, Applicant $applicant)
    {
        $input = $request->input();

        $references = $input['references'];

        //Delete old references that weren't resubmitted
        //Note: this must be done before adding new references, so we don't delete
        // them right after adding them
        foreach($applicant->references as $oldReference) {
            //Check if no references were resubmitted, or if this specific one wasn't
            if (!isset($references['old']) ||
                !isset($references['old'][$oldReference->id])) {
                $oldReference->delete();
            }
        }

        //Save new references
        if (isset($references['new'])) {
            foreach($references['new'] as $referenceInput) {
                $reference = new Reference();
                $reference->applicant_id = $applicant->id;
                $reference->fill([
                    'name' => $referenceInput['name'],
                    'email' => $referenceInput['email'],
                    'relationship_id' => $referenceInput['relationship_id'],
                    'description' => $referenceInput['description'],
                ]);

                $reference->save();

                $projectIds = [];
                $projects = $referenceInput['projects'];
                if (isset($projects['new'])) {
                    foreach($projects['new'] as $projectInput) {
                        $project = new Project();
                        $project->applicant_id = $applicant->id;
                        $project->fill([
                            'name' => $projectInput['name'],
                            'start_date' => $projectInput['start_date'],
                            'end_date' => $projectInput['end_date'],
                        ]);
                        $project->save();
                        $projectIds[] = $project->id;
                    }
                }
                //Sync attaches the specified ids, and detaches all others
                $reference->projects()->sync($projectIds);


                $skillDeclarationIds =$this->getRelativeIds($referenceInput, 'skills');
                $reference->skill_declarations()->sync($skillDeclarationIds);
            }
        }

        //Update old references
        if (isset($references['old'])) {
            foreach($references['old'] as $id=>$referenceInput) {
                //Ensure this reference belongs to this applicant
                $reference = $applicant->references->firstWhere('id', $id);
                if ($reference != null) {
                    $reference->fill([
                        'name' => $referenceInput['name'],
                        'email' => $referenceInput['email'],
                        'relationship_id' => $referenceInput['relationship_id'],
                        'description' => $referenceInput['description'],
                    ]);
                    $reference->save();

                    $projectIds = [];
                    $projects = $referenceInput['projects'];
                    if (isset($projects['new'])) {
                        foreach($projects['new'] as $projectInput) {
                            $project = new Project();
                            $project->applicant_id = $applicant->id;
                            $project->fill([
                                'name' => $projectInput['name'],
                                'start_date' => $projectInput['start_date'],
                                'end_date' => $projectInput['end_date'],
                            ]);
                            $project->save();
                            $projectIds[] = $project->id;
                        }
                    }
                    if (isset($projects['old'])) {
                        foreach($projects['old'] as $projectId=>$projectInput) {
                            //Ensure this project belongs to this applicant
                            $project = $applicant->projects->firstWhere('id', $projectId);
                            if ($project != null) {
                                $project->fill([
                                    'name' => $projectInput['name'],
                                    'start_date' => $projectInput['start_date'],
                                    'end_date' => $projectInput['end_date'],
                                ]);
                                $project->save();
                                $projectIds[] = $project->id;
                            }
                        }
                    }
                    //TODO: when projects exists independpently on profile, don't delete them Here
                    // Delete projects that will be detached from this reference
                    foreach($reference->projects as $project) {
                        if (!in_array($project->id, $projectIds)) {
                            $project->delete();
                        }
                    }

                    //Sync attaches the specified ids, and detaches all others
                    $reference->projects()->sync($projectIds);

                    $skillDeclarationIds =$this->getRelativeIds($referenceInput, 'skills');
                    $reference->skill_declarations()->sync($skillDeclarationIds);
                } else {
                    Log::warning('Applicant '.$applicant->id.' attempted to update reference with invalid id '.$id);
                }
            }
        }

        return redirect( route('profile.references.edit', $applicant) );
    }

    /**
     * Update or create a reference with the supplied data.
     *
     * @param \Illuminate\Http\Request   $request   The incoming request object.
     * @param \App\Models\Reference|null $reference The reference to update. If null, a new one should be created.
     *
     * @return
     */
    public function update(Request $request, ?Reference $reference = null)
    {
        $validator = new UpdateReferenceValidator();
        $validator->validate($request->input());

        if ($reference === null) {
            $reference = new Reference();
            $reference->applicant_id = $request->user()->applicant->id;
        }
        $reference->fill([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'relationship_id' => $request->input('relationship_id'),
            'description' => $request->input('description'),
        ]);
        $reference->save();

        $reference->load('projects');

        //TODO: As soon as you can interact with projects outside of references,
        //  this will become a dangerous operation
        foreach ($reference->projects as $project) {
            $project->delete();
        }

        $newProjects = [];
        if ($request->input('projects')) {
            foreach ($request->input('projects') as $projectInput) {
                $project = new Project();
                $project->applicant_id = $reference->applicant_id;
                $project->fill([
                    'name' => $projectInput['name'],
                    'start_date' => $projectInput['start_date'],
                    'end_date' => $projectInput['end_date'],
                ]);
                $project->save();
                $newProjects[] = $project->id;
                // $reference->projects()->attach($project);
            }
        }
        $reference->projects()->sync($newProjects);

        //Attach relatives
        $skillIds = $this->getRelativeIds($request->input(), 'skills');
        $reference->skill_declarations()->sync($skillIds);

        // if an ajax request, return the new object
        if ($request->ajax()) {
            $reference->load('relationship');
            $reference->load('projects');
            return $reference->toJson();
        } else {
            return redirect()->back();
        }
    }

    /**
     * Delete the particular reference from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reference  $reference
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Reference $reference)
    {
        $this->authorize('delete', $reference);

        //TODO: when projects exist independently on profile, delete seperatley
        foreach($reference->projects as $project) {
            $project->delete();
        }

        $reference->delete();

        if($request->ajax()) {
            return [
                "message" => 'Reference deleted'
            ];
        }

        return redirect()->back();
    }

}
