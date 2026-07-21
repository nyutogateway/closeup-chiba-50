<?php
/**
 * 既存投稿へ「記事見出し / 代表役職 / 星の数」を一括追記する単体ツール（WP-CLI不要）。
 *
 * 使い方（本番: umi.design/prod/closeup）:
 *   1) このファイルを WordPress のルート（wp-load.php と同じ階層）へFTPアップ
 *   2) WordPressに「管理者」でログインした状態で、ブラウザで下記を開く
 *        …/cu-update-fields.php            → 確認だけ（何も書き込まない）
 *        …/cu-update-fields.php?apply=1     → 実際に追記
 *   3) 完了したら、このファイルは必ず削除する
 *
 * ※ 会社名（投稿タイトル）で突き合わせ。既存の画像・本文・氏名・URL等には一切触れません。
 * ※ 追記するのは detail_head_copy / detail_head_role / detail_star の3つだけ。
 */

// --- WP読み込み（同じ階層の wp-load.php を探す。無ければ1つ上まで） ---
$__wp = __DIR__ . '/wp-load.php';
if (!file_exists($__wp)) { $__wp = dirname(__DIR__) . '/wp-load.php'; }
require $__wp;

if (!function_exists('current_user_can') || !current_user_can('manage_options')) {
  header('Content-Type: text/html; charset=UTF-8');
  exit('管理者でログインしてから実行してください。');
}

$apply = isset($_GET['apply']) && $_GET['apply'] == '1';

$DATA = array(
  array('corp'=>'イオンアグリ創造株式会社','head'=>'サプライチェーン一貫型農業ビジネスの「AEON農場」ブランド','role'=>'代表取締役社長','star'=>'3'),
  array('corp'=>'株式会社ウェザーニューズ','head'=>'千葉が誇る世界最大規模の民間気象情報会社','role'=>'代表取締役社長','star'=>'3'),
  array('corp'=>'日本空港無線サービス 株式会社','head'=>'空の玄関口たらしめる航空無線技術とは','role'=>'サービスエンジニア','star'=>'3'),
  array('corp'=>'プレシジョン・システム・サイエンス株式会社','head'=>'日々の研究に欠かせない存在・PSSとは','role'=>'代表取締役社長','star'=>'3'),
  array('corp'=>'いすみ鉄道株式会社','head'=>'房総半島の架け橋を支えている若き代表','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'チーズ工房【千】sen','head'=>'0.01gにこだわるチーズ工房','role'=>'代表','star'=>'2'),
  array('corp'=>'千葉大学医学部附属病院','head'=>'総合診療科の先駆者','role'=>'教授','star'=>'2'),
  array('corp'=>'IBM BIG BLUE','head'=>'幕張の青い鎧球軍団','role'=>'代表取締役社長','star'=>'2'),
  array('corp'=>'株式会社ベストマッチ','head'=>'千葉県の雇用創出に尽力し続ける人材企業','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'有限会社幸和商事','head'=>'長生郡から世界へ。バイオガスのトップランナーとは','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'株式会社マザープラネット','head'=>'地域と交わりながら、子育て環境をつくる','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'稲村工業株式会社','head'=>'千葉県民の暮らしの根底を創る会社','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'株式会社武田設備','head'=>'日常に向き合い続ける会社','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'株式会社友愛メディカル','head'=>'ぬくもりを大切にする調剤薬局','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'株式会社SAHエレテクティカルワークス','head'=>'“真摯さ”を追求する会社','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'Healing Sea','head'=>'銚子の海で健康を取り戻す','role'=>'代表','star'=>'2'),
  array('corp'=>'開成鉄筋株式会社','head'=>'日本の礎を創る会社','role'=>'専務取締役','star'=>'2'),
  array('corp'=>'アクシビワーズ株式会社','head'=>'教師から経営者へ','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'サン・ジオテック株式会社','head'=>'立体世界を映し出す測量会社','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'ホオジロ行政書士事務所','head'=>'依頼者の思いを丁寧に汲み取る行政書士','role'=>'代表','star'=>'2'),
  array('corp'=>'浦田空調工業株式会社','head'=>'建物を呼吸させる会社','role'=>'専務取締役','star'=>'2'),
  array('corp'=>'株式会社きららぼ','head'=>'夢を形にする男','role'=>'代表取締役社長','star'=>'2'),
  array('corp'=>'陽光希株式会社','head'=>'輝く可能性に満ちあふれる会社','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'いちずリフォーム株式会社','head'=>'疲れた住まいを蘇らせるリフォーム会社','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'笑顔グループ','head'=>'南房総で“いい人生”を形つくれるところ','role'=>'理事長','star'=>'2'),
  array('corp'=>'テクニカルワークス','head'=>'四街道市のカスタムパーツ専門メーカー','role'=>'代表','star'=>'2'),
  array('corp'=>'メールセンター株式会社','head'=>'柏市にあるDM発送のスペシャリスト','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'株式会社柏斎苑','head'=>'「さよなら」を「ありがとう」に変える会社','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'アイコミュニケーションズ株式会社','head'=>'再生可能エネルギー普及を目指す風雲児！','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'THE BONDSビーチSPAリゾートグランピング千葉御宿','head'=>'御宿にたたずむ日本初のグランピングエリア','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'株式会社TuberVision','head'=>'管工業界の革命児','role'=>'代表','star'=>'3'),
  array('corp'=>'株式会社Shu Electricity and Railway Consultant','head'=>'松戸が誇る世界規模で活躍中の鉄道コンサルティング会社','role'=>'代表','star'=>'3'),
  array('corp'=>'WORKS ZERO','head'=>'野田市が育てた万能型ガラスフィルム職人','role'=>'代表','star'=>'3'),
  array('corp'=>'山田食品株式会社','head'=>'関東の麺処を支える老舗の製麺会社','role'=>'代表取締役社長','star'=>'3'),
  array('corp'=>'株式会社エフディーきむら','head'=>'千葉を代表する実力派フラワーショップ','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'司興業株式会社','head'=>'「当たり前」をつくる鎌ケ谷市のプロフェッショナル集団','role'=>'代表取締役','star'=>'2'),
  array('corp'=>'東部重工業株式会社','head'=>'浦安の「つかむ」専門の会社って？','role'=>'代表取締役社長','star'=>'3'),
  array('corp'=>'コストコホールセールジャパン株式会社','head'=>'“小売店”の概念を変える異次元の品揃え','role'=>'','star'=>'3'),
  array('corp'=>'ジャパンフーズ株式会社','head'=>'長生郡にある国内屈指の飲料製造会社とは','role'=>'代表取締役社長','star'=>'3'),
  array('corp'=>'エスポ化学株式会社','head'=>'千葉の最先端都市に在る日本有数の「ニオイ対策」特化型企業','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'株式会社ワラカド','head'=>'モットーは「笑門来福」6店舗を構えている千葉の若旦那','role'=>'代表','star'=>'3'),
  array('corp'=>'花澤林産興業株式会社','head'=>'5代続くあすみが丘の老舗不動産','role'=>'代表取締役','star'=>'3'),
  array('corp'=>'有限会社大川園','head'=>'“敷居が高い”お茶屋のイメージを変えるために','role'=>'代表','star'=>'3'),
  array('corp'=>'株式会社タルブ','head'=>'“千ブランド”認定。地元で人気の有名スイーツ店','role'=>'代表','star'=>'3'),
  array('corp'=>'学校法人増田学園 千葉聖心高等学校','head'=>'受け継がれる「聖心・努力・奉仕」の精神','role'=>'校長','star'=>'0'),
  array('corp'=>'有限会社かし熊','head'=>'「創業の地」と共に歩む覚悟と、経営者としての冷静な視点','role'=>'代表取締役','star'=>'0'),
  array('corp'=>'株式会社セーフティ・アラート','head'=>'安定を求めた創業の原点','role'=>'代表','star'=>'0'),
  array('corp'=>'社会福祉法人 翠燿会','head'=>'父の背中を追って診療所から始まった「奉仕」の心','role'=>'理事長','star'=>'0'),
  array('corp'=>'日触テクノファインケミカル株式会社','head'=>'危機を乗り越え、変革し続けた70年','role'=>'代表取締役社長','star'=>'0'),
  array('corp'=>'有限会社アシストハウス','head'=>'入社した直後、創業者が急逝して社長に就任','role'=>'代表取締役','star'=>'3'),
);

function cu_norm($s){ return preg_replace('/[\s　]+/u', '', (string)$s); }
function cu_setmeta($post_id, $key, $val){
  if (function_exists('update_field')) update_field($key, $val, $post_id); // ACF系が有効な場合も一応対応
  update_post_meta($post_id, $key, $val); // Smart Custom Fields はこれで反映
}

// タイトル→ID
$byTitle = array();
foreach (get_posts(array('post_type'=>'post','posts_per_page'=>-1,'post_status'=>'any')) as $p) {
  $byTitle[cu_norm($p->post_title)] = $p->ID;
}

header('Content-Type: text/html; charset=UTF-8');
echo '<meta charset="UTF-8"><body style="font-family:sans-serif;line-height:1.7;padding:20px">';
echo '<h2>' . ($apply ? '追記を実行' : '確認（未書き込み）') . '</h2>';
if (!$apply) echo '<p>実際に書き込むには、URLの末尾に <b>?apply=1</b> を付けて開いてください。</p>';
echo '<ol>';

$done = 0; $miss = array();
foreach ($DATA as $e) {
  $key = cu_norm($e['corp']);
  if (!isset($byTitle[$key])) { $miss[] = $e['corp']; continue; }
  $id = $byTitle[$key];
  echo '<li>' . esc_html($e['corp']) . ' ｜ 見出し:「' . esc_html(mb_strimwidth($e['head'],0,28,'…'))
     . '」／役職:' . esc_html($e['role'] !== '' ? $e['role'] : '(なし)') . '／星:' . esc_html($e['star']);
  if ($apply) {
    cu_setmeta($id, 'detail_head_copy', $e['head']);
    cu_setmeta($id, 'detail_head_role', $e['role']);
    cu_setmeta($id, 'detail_star', (string)$e['star']);
    echo ' → <b style="color:green">更新</b>';
    $done++;
  }
  echo '</li>';
}
echo '</ol>';
if ($miss) {
  echo '<p style="color:#c00"><b>該当投稿が見つからなかった会社（タイトル要確認）:</b><br>' . esc_html(implode(' / ', $miss)) . '</p>';
}
echo '<hr><p>' . ($apply ? ('<b>' . $done . ' 件を更新しました。</b> このファイルは削除してください。') : '確認のみ完了。') . '</p>';
echo '</body>';
