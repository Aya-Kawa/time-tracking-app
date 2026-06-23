<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceRequest extends FormRequest
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
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'breaks.*.start_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.end_time' => ['nullable', 'date_format:H:i',],
            'remarks' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間は必須です。',
            'start_time.date_format' => '出勤時間はHH:MMの形式で入力してください。',
            'end_time.required' => '退勤時間は必須です。',
            'end_time.date_format' => '退勤時間はHH:MMの形式で入力してください。',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'breaks.*.start_time.date_format' => '休憩開始時間はHH:MMの形式で入力してください。',
            'breaks.*.end_time.date_format' => '休憩終了時間はHH:MMの形式で入力してください。',
            'remarks.required' => '備考を入力してください。',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('start_time');
            $clockOut = $this->input('end_time');
            $breaks = $this->input('breaks', []);
            if (!$clockIn || !$clockOut) {
                return;
            }

            $clockInTime = \Carbon\Carbon::createFromFormat('H:i', $clockIn);
            $clockOutTime = \Carbon\Carbon::createFromFormat('H:i', $clockOut);

            foreach ($breaks as $index => $break) {
                $breakStart = $break['start_time'] ?? null;
                $breakEnd = $break['end_time'] ?? null;
                if ($breakStart) {
                    $breakStartTime = \Carbon\Carbon::createFromFormat('H:i', $breakStart);
                    if ($breakStartTime->lt($clockInTime) || $breakStartTime->gt($clockOutTime)) {
                        $validator->errors()->add(
                            "breaks.$index.start_time",
                            '休憩時間が不適切な値です'
                        );
                    }
                }

                if ($breakEnd) {
                    $breakEndTime = \Carbon\Carbon::createFromFormat('H:i', $breakEnd);
                    if ($breakEndTime->gt($clockOutTime)) {
                        $validator->errors()->add(
                            "breaks.$index.end_time",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }

        });
    }
}
