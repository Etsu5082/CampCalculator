<?php
/**
 * メール送信サービス
 */

if (!class_exists('EmailService')) {

class EmailService
{
    private Database $db;
    private array $config;

    public function __construct()
    {
        $this->db = Database::getInstance();

        // メール設定を読み込み
        $configPath = CONFIG_PATH . '/mail.php';
        if (file_exists($configPath)) {
            $this->config = require $configPath;
        } else {
            // デフォルト設定
            $this->config = [
                'smtp_host' => 'smtp.onamae.com',
                'smtp_port' => 587,
                'smtp_user' => '',
                'smtp_pass' => '',
                'from_address' => 'noreply@example.com',
                'from_name' => 'レッセフェールT.C. 会計システム',
            ];
        }
    }

    /**
     * 申し込み完了メールを送信
     *
     * @param array $member 会員情報
     * @param array $camp 合宿情報
     * @param array $application 申し込み情報
     * @return bool 成功/失敗
     */
    public function sendApplicationConfirmation(array $member, array $camp, array $application): bool
    {
        if (empty($member['email'])) {
            return false;
        }

        $subject = "【レッセフェールT.C.】{$camp['name']} 申し込み完了のお知らせ";

        $body = <<<EOT
{$member['name_kanji']} 様

{$camp['name']}への申し込みを受け付けました。

■ 申し込み内容
合宿名:     {$camp['name']}
日程:       {$camp['start_date']} 〜 {$camp['end_date']}（{$camp['nights']}泊）
参加期間:   {$application['join_day']}日目 {$this->formatTiming($application['join_timing'])} 〜 {$application['leave_day']}日目 {$this->formatTiming($application['leave_timing'])}
往路バス:   {$this->formatBusUsage($application['use_outbound_bus'])}
復路バス:   {$this->formatBusUsage($application['use_return_bus'])}

■ 変更・キャンセルについて
申し込み後の変更・キャンセルは、幹事に連絡してください。
管理者の承認後に変更が反映されます。

---
このメールは自動送信されています。
返信はできませんのでご了承ください。

レッセフェールT.C.
EOT;

        return $this->send($member['email'], $subject, $body, 'application_confirmation', $member['id']);
    }

    /**
     * 学科未登録通知メールを送信
     *
     * @param array $member 会員情報
     * @return bool 成功/失敗
     */
    public function sendDepartmentRequired(array $member): bool
    {
        if (empty($member['email'])) {
            return false;
        }

        $subject = "【レッセフェールT.C.】学科選択のお願い";

        $body = <<<EOT
{$member['name_kanji']} 様

新学期になりましたので、学科を選択してください。

基幹理工学部の2年生は進振りにより学科が確定しています。
以下のURLから学科を選択してください。

URL: [システムURL]/members/update

---
このメールは自動送信されています。

レッセフェールT.C.
EOT;

        return $this->send($member['email'], $subject, $body, 'department_required', $member['id']);
    }

    /**
     * 入会申請通知メールを管理者に送信
     *
     * @param array $member 会員情報
     * @return bool 成功/失敗
     */
    public function sendEnrollmentNotification(array $member): bool
    {
        $adminEmail = $this->config['admin_email'] ?? 'kohetsu.watanabe@gmail.com';

        $subject = "【レッセフェールT.C.】新規入会申請";

        $body = <<<EOT
新規入会申請がありました。

名前: {$member['name_kanji']} ({$member['name_kana']})
学籍番号: {$member['student_id']}
学部: {$member['faculty']}
学科: {$member['department']}
学年: {$member['grade']}年
性別: {$this->formatGender($member['gender'])}

以下のURLから承認・却下を行ってください。
URL: [システムURL]/members/pending

---
このメールは自動送信されています。

レッセフェールT.C. 会計システム
EOT;

        return $this->send($adminEmail, $subject, $body, 'enrollment_application', $member['id']);
    }

    /**
     * メール送信（内部メソッド）
     *
     * @param string $to 送信先アドレス
     * @param string $subject 件名
     * @param string $body 本文
     * @param string $type メール種別
     * @param int|null $memberId 会員ID
     * @return bool 成功/失敗
     */
    private function send(string $to, string $subject, string $body, string $type, ?int $memberId = null): bool
    {
        // メールログに記録
        $logId = $this->logEmail($to, $subject, $body, $type, $memberId);

        try {
            // TODO: 実際のメール送信処理（PHPMailer or mb_send_mail）
            // 現在は簡易実装（ログのみ）

            // mb_send_mail を使用した簡易送信
            $headers = "From: {$this->config['from_name']} <{$this->config['from_address']}>\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            $result = mb_send_mail($to, $subject, $body, $headers);

            // 送信結果をログに記録
            if ($result) {
                $this->updateEmailLog($logId, 'sent');
                return true;
            } else {
                $this->updateEmailLog($logId, 'failed', 'mb_send_mail failed');
                return false;
            }

        } catch (Exception $e) {
            $this->updateEmailLog($logId, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * メールログを記録
     *
     * @param string $to 送信先
     * @param string $subject 件名
     * @param string $body 本文
     * @param string $type メール種別
     * @param int|null $memberId 会員ID
     * @return int ログID
     */
    private function logEmail(string $to, string $subject, string $body, string $type, ?int $memberId = null): int
    {
        $sql = "INSERT INTO email_logs (
            member_id, email_type, to_address, subject, body, status
        ) VALUES (?, ?, ?, ?, ?, 'pending')";

        return $this->db->insert($sql, [
            $memberId,
            $type,
            $to,
            $subject,
            $body,
        ]);
    }

    /**
     * メールログのステータスを更新
     *
     * @param int $logId ログID
     * @param string $status ステータス
     * @param string|null $errorMessage エラーメッセージ
     * @return bool 成功/失敗
     */
    private function updateEmailLog(int $logId, string $status, ?string $errorMessage = null): bool
    {
        $sql = "UPDATE email_logs SET status = ?, sent_at = NOW(), error_message = ? WHERE id = ?";

        return $this->db->execute($sql, [
            $status,
            $errorMessage,
            $logId,
        ]) > 0;
    }

    /**
     * タイミングを日本語に変換
     *
     * @param string $timing タイミングコード
     * @return string 日本語表記
     */
    private function formatTiming(string $timing): string
    {
        $map = [
            'outbound_bus' => '往路バスから',
            'morning' => '午前から',
            'lunch' => '昼食から',
            'afternoon' => '午後から',
            'dinner' => '夕食から',
            'night' => '夜から',
            'after_breakfast' => '朝食後',
            'after_lunch' => '昼食後',
            'return_bus' => '復路バスまで',
        ];

        return $map[$timing] ?? $timing;
    }

    /**
     * バス利用を日本語に変換
     *
     * @param int $use 利用フラグ
     * @return string 日本語表記
     */
    private function formatBusUsage(int $use): string
    {
        return $use ? '利用する' : '利用しない';
    }

    /**
     * 性別を日本語に変換
     *
     * @param string $gender 性別コード
     * @return string 日本語表記
     */
    private function formatGender(string $gender): string
    {
        return $gender === 'male' ? '男性' : '女性';
    }
}

}
