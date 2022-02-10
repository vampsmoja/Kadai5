<?php

//エラー確認用
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);



// 変数の初期化　※nullで値を空にしておき、存在しない変数を読んだり、意図しない挙動を防ぐため
$pdo = null;
$stmt = null;
$res = null;
$error_message = array();//未入力の内容と配列    


//記事のページのURLのパラメータ(IDの部分)を読み込む処理
//issetで変数の中身に値があれば変数$article_idに取得した値(ID)を代入
if(isset($_GET['id'])){
    $article_id = $_GET['id'];
}

//データベースの接続、PDOオブジェクトの作成、パラメータの指定、第1にデータベースのドライバからホスト名まで、第2にユーザー名、第3にパスワードですが指定してないので未入力
//try{}内でデータベースに接続ができた時の動作、できなかった場合はPDOException(エラーの内容)を変数$eに入れてgetMessageでエラーの内容を取得
try{   
    $pdo = new PDO('mysql:charset=UTF8;dbname=php-kadai;host=localhost', 'kurihara', "");
} catch (PDOException $e){
    $error_message[] = $e->getMessage();
}


$sql = $pdo->query("SELECT * FROM articles ORDER BY article_id DESC;");//articlesテーブルから記事の全部のデータを選択、各記事に紐づいた親IDの降順で取得。$sqlに代入。
$message_array = array();
$message_array = $sql->fetchAll();//$message_arrayに$sqlの中身を全て代入。


$count=count($message_array);//$message_arrayの配列の数を数えて結果の値を$countに代入。

$i=0;
while($i<$count){//$iが$countの値を超えるまで
    $article = $message_array[$i];//$articleに$message_array[$i];を入れ続ける。
    $i++;
    if($article_id == $article['article_id']){//ページのIDと同じ親ID(article_id)を持ってる配列があればループを抜ける。
        break;
    }    
}   




if( !empty($_POST['btn_submit']) ) {   
    
    if( empty($_POST['comment']) ) {//入力した記事の内容が空だったら
        $error_message[] = 'コメントは必須です。';//$error_message[]に「コメントは必須です。」が加えられます。
    }
    if( empty($error_message) ) {//$error_message)が空だったら以下の動作は行われる。
         //下は記事に対するコメントの書き込み、保存
        $stmt = $pdo->prepare("INSERT INTO comments (comment, article_id)
        VALUES ( :comment, :article_id)");//プリペアドステートメントの実行、DBのcommentsテーブルのそれぞれのカラムに登録の指定をする
        $comment = $_POST['comment'];
        $send_article_id = $_POST['article_id'];
        $stmt->bindParam( ':comment', $comment, PDO::PARAM_STR);//':comment'に変数$commentの内容が入るPDO::PARAM_STRで文字列に指定
        $stmt->bindParam( ':article_id', $send_article_id, PDO::PARAM_INT);
        $stmt->execute();//DBへの登録の実行、bindParamなのでexecuteの時点でbindが実行。この処理を変数$resに入れる。

        $stmt = null;//プリペアドステートメントの終了。
    }
}






if( empty($error_message) ) {
	// メッセージのデータを取得する
    //commentsテーブルから記事の全部のデータを選択、同じ親IDを持ったデータを指定、各コメントが持っている子IDの降順で取得。$sqlに代入。
	$sql = $pdo->query("SELECT * FROM comments WHERE article_id = $article_id ORDER BY comment_id DESC;");
    $comment_array = array();
    $comment_array = $sql->fetchAll();//$message_arrayに$sqlの中身を全て代入。
}




$pdo = null;$pdo = null;//DBとの接続終了。



?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>edit.php</title>
</head>
<body>
<a href="http://localhost/">Laravel News</a><!--トップへのリンク-->
<section>
<article>
    <div class="info">
        <h2><?php echo $article['title']; ?></h2><!--でview_name(タイトル)を出力-->
    </div>
    <p><?php  echo $article['article']; ?></p><!--<ｐ>でmessage(記事)を出力-->
    <hr><!--下線部-->
</article> 
</section>
<?php if( !empty($error_message) ): ?><!-- $error_messageの中身が空でなければ(「タイトルは必須です。」または「記事は必須です。」が入ってれば) -->
    <div class="error_message"><?php echo $error_message[0]; ?></div>
<?php endif; ?>
<!--下はコメントの投稿ボタンの作成-->
<form method="post">
	<div>
		<label for="comment"></label>
		<textarea id="comment" name="comment"></textarea>
	</div>
	<input type="submit" name="btn_submit" value="コメント">
    <input id="article_id" type="hidden" name="article_id" value="<?php echo $article_id;?>">
</form>
<section>
<?php if( !empty($comment_array) ): ?><!--$message_arrayの中身が空でなければ-->
<?php $i=0;?>
<?php while( isset($comment_array[$i])):?><!--$comment_arrayの中身がある限りtrue無ければfalse、falseでループを抜ける。-->
<?php $value = $comment_array[$i]; ?><!--$valueに$comment_array[$i]を入れ続ける。-->
<?php $i++; ?>
<article>
    <p><?php echo $value['comment']; ?></p>
    <a href="http://localhost/delete.php?id=<?php echo $value['comment_id']?>" >コメント削除</a><!--コメント削除のリンク作成、$valueのcomment_idのキーを指定して個別のページに飛ぶように設定-->
    <hr>
</article>
<?php endwhile; ?>
<?php endif; ?>
</section>
</body>
</html>

