<?php

namespace App\Http\Requests\User;

use App\Enum\DevicePlatformEnum;
use App\Enum\GenderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserRegisterRequest extends FormRequest
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

            'first_name'    => ['required', 'string', $min_3 = 'min:3'],
            'last_name'     => ['required', 'string', $min_3],
            'email'         => ['required', 'email', "unique:users,email"],
            'phone'         => [
                'required', 'string',
                'regex:/^(\+\d{1,2}\s?)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/i', "unique:users,phone"
            ],
            'gender'        => ['required', Rule::in(GenderEnum::cases())],
            'dob'           => ['required', 'date', 'before:today'],
            'avatar'        => ['nullable', 'image'],
            'password'      => ['required', 'confirmed', 'min:6'],

            'device'                => ['sometimes', 'array'],
            'device.udid'           => ['required_with:device', 'string', 'min:1'],
            'device.platform'       => ['required_with:device', 'string', new Enum(DevicePlatformEnum::class)],
            'device.token'          => ['required_with:device', 'string', 'min:1'],

        ];
    }
}
