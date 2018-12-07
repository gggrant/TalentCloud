<?php

namespace App\Services\Validation;

use App\Models\JobApplication;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as BaseValidator;
use App\Models\Lookup\CitizenshipDeclaration;
use App\Models\Lookup\VeteranStatus;
use App\Models\Lookup\PreferredLanguage;
use App\Services\Validation\Rules\ContainsObjectWithAttributeRule;

class ApplicationValidator {

    protected $citizenship_ids;
    protected $veteran_status_ids;
    protected $preferred_language_ids;

    public function __construct() {
        $this->citizenship_ids = CitizenshipDeclaration::all()->pluck('id')->toArray();
        $this->veteran_status_ids = VeteranStatus::all()->pluck('id')->toArray();
        $this->preferred_language_ids = PreferredLanguage::all()->pluck('id')->toArray();
    }

    public function validate(JobApplication $application) {

        $rules = [

        ];

        //Validate basic data is filled in
        Validator::make($application->getAttributes(), [
            'job_poster_id' => 'required',
            'application_status_id' => 'required',
            'citizenship_declaration_id' => ['required', Rule::in($this->citizenship_ids)],
            'veteran_status_id' => ['required', Rule::in($this->veteran_status_ids)],
            'preferred_language_id' => ['required', Rule::in($this->preferred_language_ids)],
            'applicant_id' => 'required',
            'submission_signature' => 'required|string|max:191',
            'submission_date' => 'required|string|max:191',
        ])->validate();

        //TODO
        //Validate that all questions have been answered

        //TODO
        //Validate that essential skill declarations have been supplied
    }

    public function basicsValidator(JobApplication $application) {
        // Questions consistent on every application
        $rules = [
            'citizenship_declaration_id' => ['required', Rule::in($this->citizenship_ids)],
            'veteran_status_id' => ['required', Rule::in($this->veteran_status_ids)],
            'preferred_language_id' => ['required', Rule::in($this->preferred_language_ids)],
        ];
        // questions that depend on job poster
        $jobPosterQuestionRules = [];
        foreach($application->job_poster->job_poster_questions as $question) {
            $jobPosterQuestionRules[] = new ContainsObjectWithAttributeRule('job_poster_question_id', $question->id);
        }
        $rules['job_application_answers'] = $jobPosterQuestionRules;
        //Load application answers so they are included in application->toArray()
        $application->load('job_application_answers');

        debugbar()->debug($application->toArray());
        return Validator::make($application->toArray(), $rules);
    }

    public function basicsComplete(JobApplication $application) {
        $validator = $this->basicsValidator($application);
        debugbar()->debug($validator->messages());
        return $validator->passes();
    }
}
