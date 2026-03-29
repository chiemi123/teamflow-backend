<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')
                    ->where('organization_id', $this->user()->current_org_id)
            ],

            'assigned_user_id' => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where('organization_id', $this->user()->current_org_id)
            ],

            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
