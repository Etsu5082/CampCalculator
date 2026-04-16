<?php
/**
 * ローカルMAMP環境でテストを実行するエントリーポイント
 * 本番の database.php を変更せずにローカルDBでテスト可能
 *
 * 実行: php tests/run_local_test.php
 */

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');

// ---- ローカルDB設定を上書き ----
// DatabaseクラスはCONFIG_PATH/database.php を読むので
// 一時的にテスト用設定ファイルをCONFIG_PATHに置く代わりに
// Databaseクラスを直接モンキーパッチする
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        // MAMP MySQL (port 8889) + lesse_test DB
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;port=8889;dbname=lesse_test;charset=utf8mb4',
            'root',
            'root',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO { return $this->pdo; }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function insert(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void           { $this->pdo->commit(); }
    public function rollback(): void         { $this->pdo->rollBack(); }
}

// ---- 残りのクラスを読み込み ----
$classes = [
    'Models/AcademicYear.php',
    'Models/Camp.php',
    'Models/CampToken.php',
    'Models/TimeSlot.php',    // Participant が内部で使うので先にロード
    'Models/Participant.php',
    'Models/CampApplication.php',
    'Models/Member.php',
    'Models/Expense.php',
];
foreach ($classes as $c) {
    require_once SRC_PATH . '/' . $c;
}

// ---- テスト本体を読み込んで実行 ----
require_once __DIR__ . '/ApplicationAndGradeTest.php';

$test = new ApplicationAndGradeTest();
$test->run();
