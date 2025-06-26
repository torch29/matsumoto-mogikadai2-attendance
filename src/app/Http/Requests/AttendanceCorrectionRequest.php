<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'corrected_clock_in' => 'required|date_format:H:i',
            'corrected_clock_out' => ['required', 'date_format:H:i', 'after:corrected_clock_in'],
            'note' => 'required',

            'rest_corrections' => 'nullable|array',

            'rest_corrections.*.corrected_rest_start' => ['nullable', 'required_with:rest_corrections.*.corrected_rest_end', 'date_format:H:i', 'after:corrected_clock_in', 'before:corrected_clock_out'],

            'rest_corrections.*.corrected_rest_end' => ['nullable', 'required_with:rest_corrections.*.corrected_rest_start', 'date_format:H:i', 'after:rest_corrections.*.corrected_rest_start', 'before:corrected_clock_out'],
        ];
    }

    public function messages()
    {
        return [
            'corrected_clock_in.required' => '出勤時間を入力してください',
            'corrected_clock_out.required' => '退勤時間を入力してください',
            'corrected_clock_in.date_format' => '半角数字で XX:XX 形式で入力してください',
            'corrected_clock_out.date_format' => '半角数字で XX:XX 形式で入力してください',
            'corrected_clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',

            'rest_corrections.*.corrected_rest_start.required_with' => '休憩開始時刻と終了時刻はセットで入力してください',
            'rest_corrections.*.corrected_rest_end.required_with' => '休憩開始時刻と終了時刻はセットで入力してください。',
            'rest_corrections.*.corrected_rest_start.date_format' => '半角数字で XX:XX 形式で入力してください',
            'rest_corrections.*.corrected_rest_end.date_format' => '半角数字で XX:XX 形式で入力してください',
            'rest_corrections.*.corrected_rest_start.after' => '休憩時間が勤務時間外です',
            'rest_corrections.*.corrected_rest_start.before' => '休憩時間が勤務時間外です',
            'rest_corrections.*.corrected_rest_end.after' => '休憩時間の入力が正しくありません',
            'rest_corrections.*.corrected_rest_end.before' => '休憩時間が勤務時間外です',
        ];
    }
}
