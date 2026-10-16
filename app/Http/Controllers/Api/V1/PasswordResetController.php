<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;

class PasswordResetController extends Controller
{
    public function forgot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = trim($validated['email']);

        $user = DB::table('users')
            ->select(['id', 'full_name'])
            ->where('email', $email)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'Neu email ton tai trong he thong, ban se nhan duoc link dat lai mat khau.',
            ]);
        }

        DB::table('password_resets')
            ->where('email', $email)
            ->where('used', 0)
            ->update(['used' => 1]);

        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->addMinutes(10)->format('Y-m-d H:i:s');

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        $baseUrl = rtrim((string) env('TECHSHOP_FRONT_URL', 'http://localhost/TechShop'), '/');
        $resetLink = $baseUrl . '/auth/reset-password.php?token=' . $token;

        $mailConfigPath = base_path('../config/mail.php');
        if (!file_exists($mailConfigPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay cau hinh mail.',
            ], 500);
        }

        $mailConfig = require $mailConfigPath;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $mailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mailConfig['username'];
            $mail->Password = $mailConfig['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $mailConfig['port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
            $mail->addAddress($email, (string) $user->full_name);
            $mail->isHTML(true);
            $mail->Subject = 'Dat lai mat khau - TechShop';
            $mail->Body = '<p>Xin chao <strong>' . htmlspecialchars((string) $user->full_name) . '</strong>,</p>'
                . '<p>Ban da yeu cau dat lai mat khau. Nhan vao link ben duoi:</p>'
                . '<p><a href="' . $resetLink . '">Dat lai mat khau</a></p>'
                . '<p>Link co hieu luc trong 30 phut.</p>';

            $mail->send();

            return response()->json([
                'success' => true,
                'message' => 'Da gui link dat lai mat khau den email cua ban. Vui long kiem tra hop thu.',
            ]);
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Khong the gui email. Vui long thu lai sau.',
            ], 500);
        }
    }

    public function validateToken(string $token): JsonResponse
    {
        $reset = DB::table('password_resets as pr')
            ->join('users as u', 'u.email', '=', 'pr.email')
            ->select(['pr.email', 'pr.expires_at', 'u.id as user_id'])
            ->where('pr.token', $token)
            ->where('pr.used', 0)
            ->first();

        if (!$reset) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Link dat lai mat khau khong hop le hoac da duoc su dung.',
            ], 404);
        }

        if (strtotime((string) $reset->expires_at) < time()) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Link dat lai mat khau da het han. Vui long yeu cau lai.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'data' => [
                'email' => $reset->email,
            ],
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
            'confirm' => ['required', 'string', 'min:6'],
        ]);

        if ($validated['password'] !== $validated['confirm']) {
            return response()->json([
                'success' => false,
                'message' => 'Mat khau xac nhan khong khop.',
            ], 422);
        }

        $reset = DB::table('password_resets as pr')
            ->join('users as u', 'u.email', '=', 'pr.email')
            ->select(['pr.token', 'pr.expires_at', 'u.id as user_id'])
            ->where('pr.token', $validated['token'])
            ->where('pr.used', 0)
            ->first();

        if (!$reset) {
            return response()->json([
                'success' => false,
                'message' => 'Link dat lai mat khau khong hop le hoac da duoc su dung.',
            ], 404);
        }

        if (strtotime((string) $reset->expires_at) < time()) {
            return response()->json([
                'success' => false,
                'message' => 'Link dat lai mat khau da het han. Vui long yeu cau lai.',
            ], 422);
        }

        DB::table('users')
            ->where('id', (int) $reset->user_id)
            ->update(['password' => password_hash($validated['password'], PASSWORD_BCRYPT)]);

        DB::table('password_resets')
            ->where('token', $validated['token'])
            ->update(['used' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Dat lai mat khau thanh cong! Ban co the dang nhap ngay.',
        ]);
    }
}
