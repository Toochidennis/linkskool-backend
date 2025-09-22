<?php

namespace V3\App\Common\Utilities;

use V3\App\Models\Portal\Academics\Student;

class BirthdayMessenger
{
    private static function getBirthdayStudents($pdo)
    {
        $student = new Student($pdo);
        $today = date("m-d"); // Format: 09-22
        return $student
            ->select(['first_name AS name', 'guardian_phone_no AS phone', 'birthdate'])
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') = ?", [$today])
            ->whereNotNull('guardian_phone_no')
            ->get();
    }

    private static function generateMessage($name)
    {
        $templates = [
            "Happy Birthday 🎉, {$name}! Wishing you joy and success from all of us at LinkSkool.",
            "Cheers to you on your special day, {$name}! 🎂 Have a wonderful year ahead.",
            "{$name}, may your birthday be filled with happiness and blessings from LinkSkool."
        ];

        return $templates[array_rand($templates)];
    }

    public static function sendSms($phone, $message, $senderId)
    {
        $data = [
            "to" => self::formatPhone($phone),
            "from" => $senderId,
            "sms" => $message,
            "type" => "plain",
            "channel" => "generic",
            "api_key" => getenv('TERMII_API_KEY')
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, getenv('TERMII_URL'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private static function formatPhone($phone)
    {
        // Remove all spaces and non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);

        // If number starts with "0", replace it with "234"
        if (preg_match('/^0\d{10}$/', $phone)) {
            return '234' . substr($phone, 1);
        }

        // If number already starts with "234", keep it
        if (preg_match('/^234\d{10}$/', $phone)) {
            return $phone;
        }

        // If number starts with "+234", remove "+"
        if (preg_match('/^\+234\d{10}$/', $phone)) {
            return substr($phone, 1);
        }

        return $phone;
    }

    public static function send()
    {
        $schools = include __DIR__ . '/../../../config/schools.php';

        foreach ($schools as $school) {
            $pdo = \V3\App\Database\DatabaseConnector::connect(getenv('DB_NAME_PREFIX') . $school['dbname']);
            $students = self::getBirthdayStudents($pdo);

            foreach ($students as $student) {
                $name = ucwords(strtolower($student['name']));
                $message = self::generateMessage($name);
                self::sendSms(
                    $student['phone'],
                    $message,
                    $school['sender_id'],
                );
            }
        }
    }
}
