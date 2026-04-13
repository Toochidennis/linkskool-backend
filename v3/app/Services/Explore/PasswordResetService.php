<?php

namespace V3\App\Services\Explore;

use PDO;
use V3\App\Models\Common\PasswordResetToken;
use V3\App\Models\Explore\CbtUser;
use V3\App\Services\Common\MailService;

class PasswordResetService
{
    private CbtUser $user;
    private PasswordResetToken $passwordResetToken;
    private MailService $mailService;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->user = new CbtUser($pdo);
        $this->mailService = new MailService($pdo);
        $this->passwordResetToken = new PasswordResetToken($pdo);
    }

    /**
     * Generate a password reset token and send reset email
     *
     * @param string $email User's email address
     * @return array Contains token and user info
     * @throws \RuntimeException if email not found
     */
    public function generateResetToken(string $email): array
    {
        // Verify email exists
        $user = $this->user
            ->where('email', '=', $email)
            ->first();

        if (empty($user)) {
            throw new \RuntimeException('Email address not found.');
        }

        // Generate unique reset token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store reset session
        $this->passwordResetToken->insert([
            'user_id' => $user['id'],
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'used' => 0,
        ]);

        // Get reset link from environment
        $resetLink = getenv('PASSWORD_RESET_LINK');
        if (empty($resetLink)) {
            throw new \RuntimeException('Password reset link is not configured.');
        }

        // Prepare reset link with token
        $fullResetLink = "$resetLink?token=$token";

        // Send reset email
        $this->sendResetEmail($user, $fullResetLink);

        return [
            'success' => true,
            'message' => 'Password reset email sent successfully.',
            'token' => $token,
            'user_email' => $email,
        ];
    }

    /**
     * Validate reset token and reset the password
     *
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return array Result of password reset
     * @throws \RuntimeException if token invalid or expired
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        // Verify token exists and is not expired
        $resetSession = $this->getValidResetSession($token);

        if (empty($resetSession)) {
            throw new \RuntimeException('Invalid or expired reset token.');
        }

        // Check if token already used
        if ($resetSession['used'] == 1) {
            throw new \RuntimeException('This reset token has already been used.');
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update user password
        $this->user
            ->where('id', '=', $resetSession['user_id'])
            ->update([
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // Mark token as used
        $this->markResetSessionAsUsed($resetSession['id']);

        return [
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ];
    }

    /**
     * Get valid reset session (not expired, not used)
     *
     * @param string $token Reset token
     * @return array|null Reset session or null if invalid/expired
     */
    private function getValidResetSession(string $token): ?array
    {
        return $this->passwordResetToken
            ->where('token', '=', $token)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->where('used', '=', 0)
            ->first();
    }

    /**
     * Mark reset session as used
     *
     * @param int $sessionId Reset session ID
     */
    private function markResetSessionAsUsed(int $sessionId): void
    {
        $this->passwordResetToken
            ->where('id', '=', $sessionId)
            ->update([
                'used' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Send password reset email
     *
     * @param array $user User data
     * @param string $resetLink Reset link
     */
    private function sendResetEmail(array $user, string $resetLink): void
    {
        $userName = $user['first_name'];
        $userEmail = $user['email'];

        $htmlContent = $this->loadEmailTemplate('password_reset', [
            'user_name' => $userName,
            'reset_link' => $resetLink,
        ]);

        $this->mailService->send(
            $userEmail,
            'Password Reset Request',
            $htmlContent
        );
    }

    /**
     * Load and render email template
     *
     * @param string $templateName Template file name (without .php extension)
     * @param array $data Data to pass to template
     * @return string Rendered HTML content
     */
    private function loadEmailTemplate(string $templateName, array $data): string
    {
        $templatePath = __DIR__ . '/../../Templates/emails/' . $templateName . '.php';

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Email template not found: {$templateName}");
        }

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Clean up expired reset tokens (optional maintenance)
     */
    public function cleanupExpiredTokens(): int
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM password_reset_tokens 
            WHERE expires_at < NOW() 
            OR (used = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY))'
        );

        $stmt->execute();
        return $stmt->rowCount();
    }
}
