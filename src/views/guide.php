<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>使い方ガイド</h1>
        <a href="/index.php?route=camps" class="btn btn-outline-secondary">
            ← 合宿一覧に戻る
        </a>
    </div>

    <!-- 目次 -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <strong>目次</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <ol>
                        <li><a href="#overview">アプリ概要</a></li>
                        <li><a href="#flow">基本的な流れ</a></li>
                        <li><a href="#create-camp">合宿を作成する</a></li>
                        <li><a href="#participants">参加者を登録する</a></li>
                        <li><a href="#partial">途中参加・途中抜けの設定</a></li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <ol start="6">
                        <li><a href="#schedule">日程設定</a></li>
                        <li><a href="#expenses">雑費の登録</a></li>
                        <li><a href="#calculation">計算結果の確認</a></li>
                        <li><a href="#export">PDF/Excel出力</a></li>
                        <li><a href="#faq">よくある質問</a></li>
                        <li><a href="#contact">お問い合わせ</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. アプリ概要 -->
    <div class="card mb-4" id="overview">
        <div class="card-header">
            <h5 class="mb-0">1. アプリ概要</h5>
        </div>
        <div class="card-body">
            <p>このアプリは、サークルの合宿における費用計算を自動化するためのツールです。</p>

            <h6>主な機能</h6>
            <ul>
                <li><strong>参加者管理:</strong> CSV一括登録、学年・性別管理、途中参加・途中抜け対応</li>
                <li><strong>費用自動計算:</strong> 宿泊費、食事調整、バス代、施設利用料、雑費を自動計算</li>
                <li><strong>割り勘計算:</strong> 参加タイミングに応じた公平な割り勘</li>
                <li><strong>出力機能:</strong> PDF/Excel形式で精算表を出力</li>
            </ul>

            <h6>対応する費用項目</h6>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>カテゴリ</th>
                        <th>項目</th>
                        <th>計算方法</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="3">宿泊関連</td>
                        <td>宿泊費</td>
                        <td>1泊あたり × 宿泊数</td>
                    </tr>
                    <tr>
                        <td>保険料</td>
                        <td>1人あたり固定</td>
                    </tr>
                    <tr>
                        <td>食事調整</td>
                        <td>追加/欠食を自動計算</td>
                    </tr>
                    <tr>
                        <td rowspan="2">交通費</td>
                        <td>バス代</td>
                        <td>利用者で均等割り</td>
                    </tr>
                    <tr>
                        <td>高速代</td>
                        <td>利用者で均等割り</td>
                    </tr>
                    <tr>
                        <td rowspan="3">施設利用料</td>
                        <td>コート代</td>
                        <td>参加者で均等割り</td>
                    </tr>
                    <tr>
                        <td>体育館代</td>
                        <td>参加者で均等割り</td>
                    </tr>
                    <tr>
                        <td>宴会場代</td>
                        <td>参加者で均等割り</td>
                    </tr>
                    <tr>
                        <td>その他</td>
                        <td>雑費</td>
                        <td>指定タイミングの参加者で割り勘</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. 基本的な流れ -->
    <div class="card mb-4" id="flow">
        <div class="card-header">
            <h5 class="mb-0">2. 基本的な流れ</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-2">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">1</div>
                    <p class="mt-2 mb-0"><strong>合宿作成</strong></p>
                    <small class="text-muted">基本情報・費用設定</small>
                </div>
                <div class="col-md-1 d-flex align-items-center justify-content-center">
                    <span class="text-muted">→</span>
                </div>
                <div class="col-md-2">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">2</div>
                    <p class="mt-2 mb-0"><strong>参加者登録</strong></p>
                    <small class="text-muted">CSV一括登録</small>
                </div>
                <div class="col-md-1 d-flex align-items-center justify-content-center">
                    <span class="text-muted">→</span>
                </div>
                <div class="col-md-2">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">3</div>
                    <p class="mt-2 mb-0"><strong>途参途抜設定</strong></p>
                    <small class="text-muted">個別に編集</small>
                </div>
                <div class="col-md-1 d-flex align-items-center justify-content-center">
                    <span class="text-muted">→</span>
                </div>
                <div class="col-md-2">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">4</div>
                    <p class="mt-2 mb-0"><strong>計算・出力</strong></p>
                    <small class="text-muted">PDF/Excel</small>
                </div>
            </div>
            <hr>
            <p class="text-muted mb-0"><small>※ 日程設定・雑費登録はオプションです。必要に応じて設定してください。</small></p>
        </div>
    </div>

    <!-- 3. 合宿を作成する -->
    <div class="card mb-4" id="create-camp">
        <div class="card-header">
            <h5 class="mb-0">3. 合宿を作成する</h5>
        </div>
        <div class="card-body">
            <h6>3.1 新規作成</h6>
            <ol>
                <li>トップページの「+ 新規合宿作成」ボタンをクリック</li>
                <li>以下の項目を入力:
                    <ul>
                        <li><strong>合宿名:</strong> 例）2024年度春合宿</li>
                        <li><strong>開始日・終了日:</strong> 合宿の日程</li>
                        <li><strong>泊数:</strong> 宿泊数（3泊なら「3」）</li>
                    </ul>
                </li>
                <li>費用設定を入力（下記参照）</li>
                <li>「保存」をクリック</li>
            </ol>

            <h6 class="mt-4">3.2 費用設定項目</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>項目</th>
                            <th>説明</th>
                            <th>例</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1泊料金（3食付）</td>
                            <td>1泊あたりの宿泊費（朝・昼・夕食込み）</td>
                            <td>8,000円</td>
                        </tr>
                        <tr>
                            <td>保険料</td>
                            <td>1人あたりの保険料（宿泊数に関わらず固定）</td>
                            <td>500円</td>
                        </tr>
                        <tr>
                            <td>1日目昼食</td>
                            <td>1日目の昼食が費用計算に含まれるか</td>
                            <td>対象外（各自調達）</td>
                        </tr>
                        <tr>
                            <td>コート1面あたり料金</td>
                            <td>テニスコート1面1コマの料金</td>
                            <td>5,000円</td>
                        </tr>
                        <tr>
                            <td>体育館1コマあたり料金</td>
                            <td>体育館1コマの料金</td>
                            <td>3,000円</td>
                        </tr>
                        <tr>
                            <td>宴会場料金</td>
                            <td>宴会場使用時の1人あたり料金</td>
                            <td>500円</td>
                        </tr>
                        <tr>
                            <td>食事単価（追加/欠食）</td>
                            <td>食事を追加する場合と欠食する場合の金額</td>
                            <td>昼食: +990円 / -440円</td>
                        </tr>
                        <tr>
                            <td>バス代</td>
                            <td>大型バスの往復料金（総額）</td>
                            <td>160,000円</td>
                        </tr>
                        <tr>
                            <td>高速代</td>
                            <td>往路・復路それぞれの高速料金</td>
                            <td>往路15,000円 / 復路15,000円</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3">
                <strong>ポイント:</strong> 過去の合宿を「複製」すると、費用設定がコピーされます。日程と名前を変更するだけで新しい合宿を作成できます。
            </div>
        </div>
    </div>

    <!-- 4. 参加者を登録する -->
    <div class="card mb-4" id="participants">
        <div class="card-header">
            <h5 class="mb-0">4. 参加者を登録する</h5>
        </div>
        <div class="card-body">
            <h6>4.1 CSV一括登録（推奨）</h6>
            <p>多くの参加者を一度に登録できます。</p>
            <ol>
                <li>合宿詳細画面で「参加者管理」タブを選択</li>
                <li>「CSV一括登録」ボタンをクリック</li>
                <li>以下の形式でテキストを入力またはペースト</li>
                <li>「登録」をクリック</li>
            </ol>

            <h6 class="mt-3">CSV形式</h6>
            <pre class="bg-light p-3 border rounded"><code>山田太郎,1男
佐藤花子,2女
鈴木一郎,3男
田中美咲,4女
高橋健太,OB
渡辺さくら,OG</code></pre>

            <div class="row mt-3">
                <div class="col-md-6">
                    <h6>対応する形式</h6>
                    <ul>
                        <li><code>1男</code>, <code>2女</code>, <code>3男</code>, <code>4女</code></li>
                        <li><code>１男</code>, <code>２女</code>（全角数字）</li>
                        <li><code>1年男</code>, <code>2年女</code></li>
                        <li><code>OB</code>, <code>OG</code></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>登録時のデフォルト</h6>
                    <ul>
                        <li>参加開始: 1日目・往路バスから</li>
                        <li>離脱: 最終日・復路バスまで</li>
                        <li>バス利用: 往復とも利用</li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-warning mt-3">
                <strong>注意:</strong> CSV登録後、途中参加・途中抜けの人は個別に編集してください。
            </div>

            <h6 class="mt-4">4.2 個別登録</h6>
            <p>1人ずつ登録する場合は「+ 参加者を追加」ボタンから登録できます。</p>

            <h6 class="mt-4">4.3 並び替え・検索</h6>
            <ul>
                <li><strong>検索:</strong> 名前で絞り込み</li>
                <li><strong>並び替え:</strong> 学年・性別・名前で複合ソート可能</li>
            </ul>
        </div>
    </div>

    <!-- 5. 途中参加・途中抜けの設定 -->
    <div class="card mb-4" id="partial">
        <div class="card-header">
            <h5 class="mb-0">5. 途中参加・途中抜けの設定</h5>
        </div>
        <div class="card-body">
            <h6>5.1 設定方法</h6>
            <ol>
                <li>参加者一覧で該当者の「編集」ボタンをクリック</li>
                <li>「参加開始」と「離脱」のタイミングを変更</li>
                <li>バス利用の有無を設定</li>
                <li>「保存」をクリック</li>
            </ol>

            <h6 class="mt-4">5.2 参加タイミングの選択肢</h6>
            <div class="row">
                <div class="col-md-6">
                    <h6>参加開始タイミング</h6>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>選択肢</th>
                                <th>意味</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>往路バスから</td><td>1日目のみ選択可</td></tr>
                            <tr><td>午前から</td><td>午前の活動から参加</td></tr>
                            <tr><td>昼食から</td><td>昼食を食べてから参加</td></tr>
                            <tr><td>午後から</td><td>昼食後の活動から参加</td></tr>
                            <tr><td>夕食から</td><td>夕食を食べてから参加</td></tr>
                            <tr><td>夜から</td><td>夕食後から参加</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>離脱タイミング</h6>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>選択肢</th>
                                <th>意味</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>朝食後</td><td>朝食を食べて離脱</td></tr>
                            <tr><td>午前まで</td><td>午前の活動後に離脱</td></tr>
                            <tr><td>昼食まで</td><td>昼食を食べて離脱</td></tr>
                            <tr><td>午後まで</td><td>午後の活動後に離脱</td></tr>
                            <tr><td>夕食まで</td><td>夕食を食べて離脱</td></tr>
                            <tr><td>夜まで</td><td>夜の活動後に離脱</td></tr>
                            <tr><td>復路バスまで</td><td>最終日のみ選択可</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <h6 class="mt-4">5.3 自動計算される項目</h6>
            <ul>
                <li><strong>宿泊数:</strong> 参加期間から自動計算</li>
                <li><strong>食事の追加/欠食:</strong> タイミングに応じて自動計算</li>
                <li><strong>施設利用料:</strong> 参加しているコマのみ対象</li>
            </ul>

            <div class="alert alert-info">
                <h6>食事計算のルール</h6>
                <p class="mb-0">1泊に含まれる食事は「<strong>その日の夕食 + 翌日の朝食 + 翌日の昼食</strong>」です。</p>
                <ul class="mb-0 mt-2">
                    <li>宿泊するのに該当の食事を食べない → <span class="text-danger">欠食（減額）</span></li>
                    <li>宿泊しないのに該当の食事を食べる → <span class="text-success">追加（加算）</span></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 6. 日程設定 -->
    <div class="card mb-4" id="schedule">
        <div class="card-header">
            <h5 class="mb-0">6. 日程設定（オプション）</h5>
        </div>
        <div class="card-body">
            <p>施設利用料を計算する場合は、日程設定が必要です。</p>

            <h6>6.1 設定方法</h6>
            <ol>
                <li>合宿詳細画面で「日程設定」タブを選択</li>
                <li>各日・各時間帯の活動を設定:
                    <ul>
                        <li><strong>午前・午後:</strong> テニスコート / 体育館 / なし</li>
                        <li><strong>コート面数:</strong> 使用する面数（複数面の場合）</li>
                        <li><strong>宴会:</strong> 宴会場を使用するかどうか</li>
                    </ul>
                </li>
                <li>「保存」をクリック</li>
            </ol>

            <h6 class="mt-4">6.2 施設利用料の計算</h6>
            <ul>
                <li><strong>コート代:</strong> 1面あたり料金 × 面数 ÷ 参加者数</li>
                <li><strong>体育館代:</strong> 1コマあたり料金 ÷ 参加者数</li>
                <li><strong>宴会場代:</strong> 1人あたり料金（参加者のみ）</li>
            </ul>

            <div class="alert alert-secondary">
                <strong>注:</strong> 日程設定をしない場合、施設利用料は0円として計算されます。
            </div>
        </div>
    </div>

    <!-- 7. 雑費の登録 -->
    <div class="card mb-4" id="expenses">
        <div class="card-header">
            <h5 class="mb-0">7. 雑費の登録（オプション）</h5>
        </div>
        <div class="card-body">
            <p>飲み物代、買い出し費用など、その他の費用を登録できます。</p>

            <h6>7.1 登録方法</h6>
            <ol>
                <li>合宿詳細画面で「雑費管理」タブを選択</li>
                <li>タイムテーブル上で、該当する日・時間帯のセルをクリック</li>
                <li>項目名と金額を入力</li>
                <li>「追加」をクリック</li>
            </ol>

            <h6 class="mt-4">7.2 割り勘対象</h6>
            <p>雑費は、<strong>その時間帯に参加していた人</strong>で自動的に割り勘されます。</p>
            <div class="alert alert-info">
                <strong>例:</strong> 2日目の夜に飲み物代3,000円を登録した場合<br>
                → 2日目の夜に参加していた20人で割り勘 = 1人あたり150円
            </div>
        </div>
    </div>

    <!-- 8. 計算結果の確認 -->
    <div class="card mb-4" id="calculation">
        <div class="card-header">
            <h5 class="mb-0">8. 計算結果の確認</h5>
        </div>
        <div class="card-body">
            <h6>8.1 計算結果画面</h6>
            <ol>
                <li>合宿詳細画面で「計算結果を見る」ボタンをクリック</li>
                <li>以下の情報が表示されます:
                    <ul>
                        <li>合計金額・参加者数・平均金額</li>
                        <li>フル参加者の負担額と内訳</li>
                        <li>途中参加・途中抜けの個別負担額と内訳</li>
                    </ul>
                </li>
            </ol>

            <h6 class="mt-4">8.2 途参途抜一覧</h6>
            <p>「途参途抜一覧」ボタンをクリックすると、各参加者のスケジュールを一覧で確認できます。</p>
            <ul>
                <li>各日のスロット（往路、午前、午後、宴会、復路）ごとに○×で表示</li>
                <li>合計行には全参加者の参加人数を表示</li>
            </ul>
        </div>
    </div>

    <!-- 9. PDF/Excel出力 -->
    <div class="card mb-4" id="export">
        <div class="card-header">
            <h5 class="mb-0">9. PDF/Excel出力</h5>
        </div>
        <div class="card-body">
            <h6>9.1 出力方法</h6>
            <ol>
                <li>計算結果画面で「PDF出力」または「Excel出力」をクリック</li>
                <li>ファイルがダウンロードされます</li>
            </ol>

            <h6 class="mt-4">9.2 出力形式</h6>
            <div class="row">
                <div class="col-md-6">
                    <h6>PDF出力</h6>
                    <ul>
                        <li>印刷用のフォーマット</li>
                        <li>精算表として配布可能</li>
                        <li>途参途抜一覧も含む</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Excel出力（CSV）</h6>
                    <ul>
                        <li>Excelで開けるCSV形式</li>
                        <li>詳細データの確認・加工に便利</li>
                        <li>UTF-8 BOM付きで文字化け防止</li>
                    </ul>
                </div>
            </div>

            <h6 class="mt-4">9.3 出力内容</h6>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>セクション</th>
                        <th>内容</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>フル参加者</td>
                        <td>1行にまとめて表示（負担額・内訳・対象者リスト）</td>
                    </tr>
                    <tr>
                        <td>途中参加・途中抜け</td>
                        <td>各人の参加期間・負担額・内訳を個別表示</td>
                    </tr>
                    <tr>
                        <td>途参途抜スケジュール一覧</td>
                        <td>各スロットの参加状況を○×で表示</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 10. よくある質問 -->
    <div class="card mb-4" id="faq">
        <div class="card-header">
            <h5 class="mb-0">10. よくある質問</h5>
        </div>
        <div class="card-body">
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Q: 食事の追加/欠食はどのように計算されますか？
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p><strong>A:</strong> 1泊に含まれる食事は「その日の夕食、翌日の朝食、翌日の昼食」です。</p>
                            <ul>
                                <li>宿泊するのに該当の食事を食べない → 欠食として減額</li>
                                <li>宿泊しないのに該当の食事を食べる → 追加として加算</li>
                            </ul>
                            <p class="mb-0">参加タイミングに応じて自動計算されます。</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Q: 端数はどうなりますか？
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p><strong>A:</strong> 割り勘計算は四捨五入されます。</p>
                            <p class="mb-0">合計の端数は会計担当が調整してください。</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Q: 過去の合宿を参考に新しい合宿を作りたい
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p><strong>A:</strong> 合宿一覧で「複製」ボタンをクリックしてください。</p>
                            <ul class="mb-0">
                                <li>費用設定・日程設定がコピーされます</li>
                                <li>参加者はコピーされません（個人情報保護のため）</li>
                                <li>複製後、合宿名と日程を編集してください</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Q: 参加者を間違えて登録してしまった
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p><strong>A:</strong> 参加者一覧で該当者の「編集」ボタンをクリックして修正できます。</p>
                            <p class="mb-0">削除する場合は、編集画面の「削除」ボタンをクリックしてください。</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            Q: バスを利用しない人がいる場合は？
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p><strong>A:</strong> 参加者編集画面で「往路バス利用」「復路バス利用」のチェックを外してください。</p>
                            <p class="mb-0">バス代・高速代はバス利用者のみで割り勘されます。</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                            Q: レンタカーを出す場合は？
                        </button>
                    </h2>
                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p><strong>A:</strong> 合宿作成/編集画面で「レンタカーを追加する」にチェックを入れてください。</p>
                            <ul class="mb-0">
                                <li>レンタカー代・高速代を入力</li>
                                <li>参加者編集画面で「レンタカー利用」にチェックを入れた人で割り勘</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                            Q: OB/OGの区別はどうなっていますか？
                        </button>
                    </h2>
                    <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p><strong>A:</strong> 学年が「0」（OBOG）の場合、性別で区別されます。</p>
                            <ul class="mb-0">
                                <li>男性 → OB</li>
                                <li>女性 → OG</li>
                            </ul>
                            <p class="mt-2 mb-0">CSV登録時は「OB」「OG」と入力すれば自動で設定されます。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- お問い合わせ -->
    <div class="card mb-4" id="contact">
        <div class="card-header">
            <h5 class="mb-0">お問い合わせ・トラブル発生時</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>開発・管理者</h6>
                    <p class="mb-1"><strong>Laissez-Faire T.C. 11th 幹事長 渡邉光悦</strong></p>
                    <ul class="list-unstyled text-muted">
                        <li><i class="bi bi-envelope"></i> <a href="mailto:kohetsu.watanabe@gmail.com">kohetsu.watanabe@gmail.com</a></li>
                        <li><i class="bi bi-envelope"></i> <a href="mailto:kohetsu.watanabe@etsu-dx.com">kohetsu.watanabe@etsu-dx.com</a></li>
                        <li><i class="bi bi-telephone"></i> 080-2671-9571</li>
                        <li><i class="bi bi-geo-alt"></i> 〒103-0014 東京都中央区日本橋蛎殻町1-22-1<br>
                            <span class="ms-4">デュークスカーラ日本橋1205号</span></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>ソースコード</h6>
                    <p class="text-muted">
                        このアプリはオープンソースで公開されています。<br>
                        バグ報告や機能リクエストはGitHub、メールの両方からお願いします。
                    </p>
                    <p>
                        <a href="https://github.com/Etsu5082/CampCalculator" target="_blank" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-github"></i> GitHub - CampCalculator
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- フッター -->
    <div class="text-center text-muted mb-4">
        <a href="/index.php?route=camps" class="btn btn-primary">合宿一覧に戻る</a>
    </div>
</div>

<!-- AIチャットボット（フローティング） -->
<div id="chatbot-container">
    <!-- チャットボタン -->
    <button id="chatbot-toggle" class="btn btn-primary rounded-circle shadow" style="width: 60px; height: 60px; position: fixed; bottom: 20px; right: 20px; z-index: 1050;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
            <path d="M5 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
            <path d="m2.165 15.803.02-.004c1.83-.363 2.948-.842 3.468-1.105A9.06 9.06 0 0 0 8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6a10.437 10.437 0 0 1-.524 2.318l-.003.011a10.722 10.722 0 0 1-.244.637c-.079.186.074.394.273.362a21.673 21.673 0 0 0 .693-.125zm.8-3.108a1 1 0 0 0-.287-.801C1.618 10.83 1 9.468 1 8c0-3.192 3.004-6 7-6s7 2.808 7 6c0 3.193-3.004 6-7 6a8.06 8.06 0 0 1-2.088-.272 1 1 0 0 0-.711.074c-.387.196-1.24.57-2.634.893a10.97 10.97 0 0 0 .398-2z"/>
        </svg>
    </button>

    <!-- チャットウィンドウ -->
    <div id="chatbot-window" class="card shadow-lg" style="display: none; position: fixed; bottom: 90px; right: 20px; width: 380px; max-height: 500px; z-index: 1050;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><strong>AIアシスタント</strong></span>
            <button type="button" class="btn-close btn-close-white" id="chatbot-close"></button>
        </div>
        <div class="card-body p-0" style="height: 350px; overflow-y: auto;" id="chatbot-messages">
            <div class="p-3">
                <div class="alert alert-info mb-0" id="chatbot-welcome">
                    <small>
                        <strong>こんにちは！</strong><br>
                        合宿費用計算アプリについて質問があれば、お気軽にどうぞ。<br>
                        例：「食事の計算方法は？」「CSV登録の形式は？」
                    </small>
                </div>
            </div>
        </div>
        <div class="card-footer p-2">
            <div id="chatbot-disabled-notice" class="text-muted small text-center py-2" style="display: none;">
                AI機能は現在無効です
            </div>
            <form id="chatbot-form" class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" id="chatbot-input" placeholder="質問を入力..." maxlength="500">
                <button type="submit" class="btn btn-primary btn-sm" id="chatbot-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
#chatbot-messages .message {
    margin-bottom: 12px;
    padding: 8px 12px;
    border-radius: 12px;
    max-width: 85%;
    word-wrap: break-word;
}
#chatbot-messages .message.user {
    background-color: #0d6efd;
    color: white;
    margin-left: auto;
}
#chatbot-messages .message.assistant {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}
#chatbot-messages .message.error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
#chatbot-messages .typing-indicator {
    display: flex;
    gap: 4px;
    padding: 12px;
}
#chatbot-messages .typing-indicator span {
    width: 8px;
    height: 8px;
    background-color: #6c757d;
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}
#chatbot-messages .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
#chatbot-messages .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('chatbot-toggle');
    const window_ = document.getElementById('chatbot-window');
    const close = document.getElementById('chatbot-close');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const messages = document.getElementById('chatbot-messages');
    const disabledNotice = document.getElementById('chatbot-disabled-notice');
    const submitBtn = document.getElementById('chatbot-submit');

    let isEnabled = false;

    // チャットボットの状態を確認
    async function checkStatus() {
        try {
            const res = await fetch('/index.php?route=api/chatbot/status');
            const data = await res.json();
            isEnabled = data.success && data.data.enabled;

            if (!isEnabled) {
                disabledNotice.style.display = 'block';
                form.style.display = 'none';
            }
        } catch (e) {
            console.error('Chatbot status check failed:', e);
        }
    }

    // トグル
    toggle.addEventListener('click', function() {
        const isVisible = window_.style.display !== 'none';
        window_.style.display = isVisible ? 'none' : 'block';
        if (!isVisible) {
            input.focus();
            checkStatus();
        }
    });

    // 閉じる
    close.addEventListener('click', function() {
        window_.style.display = 'none';
    });

    // メッセージ追加
    function addMessage(text, type) {
        const welcome = document.getElementById('chatbot-welcome');
        if (welcome) welcome.remove();

        const div = document.createElement('div');
        div.className = 'message ' + type;
        div.innerHTML = text.replace(/\n/g, '<br>');

        const container = document.createElement('div');
        container.className = 'p-3 pt-0';
        container.appendChild(div);

        messages.appendChild(container);
        messages.scrollTop = messages.scrollHeight;
    }

    // ローディング表示
    function showTyping() {
        const div = document.createElement('div');
        div.className = 'typing-indicator';
        div.id = 'typing-indicator';
        div.innerHTML = '<span></span><span></span><span></span>';

        const container = document.createElement('div');
        container.className = 'p-3 pt-0';
        container.appendChild(div);

        messages.appendChild(container);
        messages.scrollTop = messages.scrollHeight;
    }

    function hideTyping() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) indicator.parentElement.remove();
    }

    // 送信
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const question = input.value.trim();
        if (!question || !isEnabled) return;

        addMessage(question, 'user');
        input.value = '';
        input.disabled = true;
        submitBtn.disabled = true;
        showTyping();

        try {
            const res = await fetch('/index.php?route=api/chatbot/ask', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ question })
            });

            const data = await res.json();
            hideTyping();

            if (data.success) {
                addMessage(data.data.answer, 'assistant');
            } else {
                addMessage(data.error || 'エラーが発生しました', 'error');
            }
        } catch (e) {
            hideTyping();
            addMessage('通信エラーが発生しました', 'error');
        }

        input.disabled = false;
        submitBtn.disabled = false;
        input.focus();
    });

    // 初期状態チェック
    checkStatus();
});
</script>
