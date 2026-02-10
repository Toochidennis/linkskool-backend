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
            ->select(['surname', 'first_name', 'guardian_phone_no AS phone', 'birthdate'])
            ->whereRaw("DATE_FORMAT(birthdate, '%m-%d') = ?", [$today])
            ->whereNotNull('guardian_phone_no')
            ->get();
    }

    private static function generateMessage(string $name, string $schoolName): string
    {
        $templates = [
            "Happy Birthday, {$name}! Wishing you joy and success from all of us at {$schoolName}.",
            "Cheers to you on your special day, {$name}. Have a wonderful year ahead from {$schoolName}.",
            "{$name}, may your birthday be filled with happiness and blessings from {$schoolName}.",
            "Warmest birthday wishes to you, {$name}. We at {$schoolName} celebrate you today.",
            "Wishing you a year full of growth, success, and happiness, {$name}. Greetings from {$schoolName}.",
            "Happy Birthday, {$name}. May your day be as special as you are. Best regards from {$schoolName}.",
            "It's your special day, {$name}. The entire {$schoolName} family wishes you the very best.",
            "May this year bring you closer to your dreams, {$name}. Birthday wishes from {$schoolName}.",
            "{$name}, we hope your birthday is filled with joy and wonderful moments. With love from {$schoolName}.",
            "Congratulations on another year, {$name}. We at {$schoolName} wish you continued success.",
            "Best wishes on your birthday, {$name}. The {$schoolName} family is proud of you.",
            "Happy Birthday, {$name}. May today mark the start of another fulfilling year. From {$schoolName}.",
            "Wishing you good health, happiness, and success always, {$name}. Greetings from {$schoolName}.",
            "Many happy returns, {$name}. Enjoy your day to the fullest. Regards from {$schoolName}.",
            "We at {$schoolName} wish you all the best on your birthday, {$name}.",
            "Happy Birthday, {$name}. May your journey ahead be bright and rewarding. From {$schoolName}.",
            "A very happy birthday to you, {$name}. Warm regards from {$schoolName}.",
            "May your special day bring peace and joy, {$name}. Best wishes from {$schoolName}.",
            "Happy Birthday, {$name}. Keep striving and shining. Greetings from {$schoolName}.",
            "Celebrating you today, {$name}. Wishing you happiness from {$schoolName}."
        ];

        return $templates[array_rand($templates)];
    }

    public static function sendSms($phone, $message, $senderId, $studentName, $schoolName)
    {
        $data = [
            "to" => $phone,
            "from" => $senderId,
            "sms" => $message,
            "type" => "plain",
            "channel" => "dnd",
            "api_key" => getenv('TERMII_API_KEY')
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, getenv('TERMII_URL'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        self::logResult(
            $schoolName,
            $studentName,
            $phone,
            $response
        );
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
                $firstName = ucwords(strtolower($student['first_name']));
                $surname = ucwords(strtolower($student['surname']));
                $name = "$firstName $surname";
                $message = self::generateMessage($name, $school['name']);
                $phone = self::formatPhone($student['phone']);
                self::sendSms(
                    $phone,
                    $message,
                    $school['sender_id'],
                    $name,
                    $school['name']
                );
            }
        }
    }

    private static function logResult(
        string $schoolName,
        string $studentName,
        string $phone,
        ?string $response
    ) {
        $logDir = __DIR__ . '/../../../public/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/birthday-messages.log';
        $timestamp = date("Y-m-d H:i:s");

        $decoded = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            if (($decoded['code'] ?? null) === "ok") {
                $status = "SUCCESS";
                $details = "Message ID: {$decoded['message_id']}, Balance: {$decoded['balance']}";
            } else {
                $status = "FAILED";
                $details = "Error: {$decoded['message']} | Code: {$decoded['code']} | Status: {$decoded['status']}";
            }
        } else {
            $status = "FAILED";
            $details = "Invalid response or cURL error";
        }

        $logEntry = "[{$timestamp}] School: {$schoolName} | Student: {$studentName} |
                Phone: {$phone} | Status: {$status} | {$details}" . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
