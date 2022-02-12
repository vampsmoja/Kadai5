<?php

// 変数の初期化　※nullで値を空にしておき、存在しない変数を読んだり、意図しない挙動を防ぐため
$pdo = null;
$stmt = null;
$res = null;
 
//ページのURLのパラメータ(IDの部分)を読み込む処理
//issetで変数の中身に値があれば変数$comment_idに取得した値(ID)を代入
if(isset($_GET['id'])){
    $comment_id = $_GET['id'];
}

//データベースの接続、PDOオブジェクトの作成、パラメータの指定、第1にデータベースのドライバからホスト名まで、第2にユーザー名、第3にパスワードですが指定してないので未入力
$pdo = new PDO('mysql:charset=UTF8;dbname=php-kadai2;host=localhost', 'root', "root");



$sql = ("SELECT * FROM comments WHERE comment_id = $comment_id;");//commentsテーブルから記事の全部のデータを選択、urlから取り込んだIDと同じcomment_idを持ったデータを取得。$sqlに代入。
$back_id = $pdo -> query($sql);
$id = $back_id -> fetch(PDO::FETCH_ASSOC);


//トランスザクションの開始、仮実行して予期せぬエラーでのDBの影響を防ぐため
$pdo->beginTransaction();

try{   
    $stmt = $pdo->prepare('DELETE FROM comments WHERE comment_id = :comment_id');//commentsテーブルにあるカラム名[comment_id]の中から同じIDを持つデータを消去。
    $stmt->execute(array(':comment_id' => $_GET["id"]));
    $res = $pdo->commit();//commitで本実行して、その処理を$resに代入。
}catch(Exception $e){
 // エラーが発生した時はロールバック
    $pdo->rollBack();
}

if( $res ) {// 削除に成功したらコメント一覧のページに戻る。
    header("Location: ./edit2.php?id=$id[article_id]");
    exit;
}

$pdo = null;//DBの接続終了。



?>