<?php

//エラー確認用
///ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);


// 変数の初期化　※nullで値を空にしておき、存在しない変数を読んだり、意図しない挙動を防ぐため
$error_message = array();//未入力の内容と配列
$pdo = null;
$stmt = null;
$res = null;



//データベースの接続、PDOオブジェクトの作成、パラメータの指定、第1にデータベースのドライバからホスト名まで、第2にユーザー名、第3にパスワードですが指定してないので未入力
//try{}内でデータベースに接続ができた時の動作、できなかった場合はPDOException(エラーの内容)を変数$eに入れてgetMessageでエラーの内容を取得
try{   
    $pdo = new PDO('mysql:charset=UTF8;dbname=php-kadai;host=localhost', 'kurihara', "");
} catch (PDOException $e){
    $error_message[] = $e->getMessage();
}


if( !empty($_POST['btn_submit'])) {//投稿した内容(変数)が空でなければ・・・
    if( empty($_POST['title']) ) {//入力したタイトルの内容が空だったら
		$error_message[] = 'タイトルは必須です。';//$error_message[]に「タイトルは必須です。」が加えられます。
	}

    if( empty($_POST['article']) ) {//入力した記事の内容が空だったら
		$error_message[] = '記事は必須です。';//$error_message[]に「記事は必須です。」が加えられます。
	}

    if( empty($error_message) ) {//$error_message)が空だったら以下の動作は行われる。
    
        $stmt = $pdo->prepare("INSERT INTO articles (title, article) 
        VALUES ( :title, :article)");//プリペアドステートメントの実行、DBのarticlesテーブルのそれぞれのカラムに登録の指定をする
        $title = $_POST['title'];//変数$title,$articleにPOSTで送られたデータが入る
        $article = $_POST['article'];
        $stmt->bindParam( ':title', $title, PDO::PARAM_STR);//':title'に変数$titleの内容が入るPDO::PARAM_STRで文字列に指定
        $stmt->bindParam( ':article', $article, PDO::PARAM_STR);

        $res = $stmt->execute();//DBへの登録の実行、bindParamなのでexecuteの時点でbindが実行。この処理を変数$resに入れる。

        //もし上の処理が失敗すれば$error_messageに「書き込みに失敗しました。」が入ります。
        if( $res ) {
            $error_message[] = '書き込みに失敗しました。';
        }

        $stmt = null;//プリペアドステートメントの終了。

    }


}

if( empty($error_message) ) {//エラーメッセージがなければ
	// 記事のデータを取得する
	$sql = $pdo->query("SELECT * FROM articles ORDER BY article_id DESC;");//articlesテーブルから記事の全部のデータを選択、各記事に紐づいた親IDの降順で取得。$sqlに代入。
    $message_array = array();
    $message_array = $sql->fetchAll();//$message_arrayに$sqlの中身を全て代入。
}

$pdo = null;//DBとの接続終了。


?>


<!--ここから下が入力フォームの部分-->


<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Laravel News.index</title>
</head>
<body>
<a href="http://localhost/">Laravel News</a>
<h1>さぁ、最新ニュースをシェアしましょう</h1>
<?php if( !empty($error_message) ): ?><!-- $error_messageの中身が空でなければ(「タイトルは必須です。」または「記事は必須です。」が入ってれば) -->
	<ul class="error_message">
        <?php $i = 0;?>
		<?php while(isset($error_message[$i]) ): ?><!--$error_messageの中身を$valueに入れていく-->
			<?php $value = $error_message[$i] ?>
            <li><?php echo $value; ?></li>
        <?php $i++ ?>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>     
<form method="post" onsubmit="return ask()"><!--フォームの作成とmethodで通信方式の指定、今回はpost。onsubmit属性で送信時に関数ask()を呼び出し、ok(true)で投稿処理がされる。-->
    <div>
        <label for="title">タイトル</label><!--タイトル部分のフォーム、view_nameの部分にタイトルで入力されたデータが入る-->
        <input id="title" type="text" maxlength="30" name="title" value=""><!--type属性で30字以内の1行のtextに指定、phpで受け取ったデータを引用するために名前をname属性に-->
    </div>
    <div>
        <label for="article">記事</label><!--記事のフォーム、messageに記事に入力されたデータが入る-->
        <textarea id="article" name="article" cols="50" rows="10"></textarea><!--textareaで複数行、10行50列に設定-->
    </div>
    <input type="submit" name="btn_submit" value="投稿"><!--投稿ボタン-->
    
</form>
<hr>

<section>
<?php if( !empty($message_array) ): ?><!--$message_arrayの中身が空でなければ-->
<?php $i=0;?>
<?php while( isset($message_array[$i])):?>
<?php    $value = $message_array[$i]; ?>
<?php      $i++; ?>
<!--$message_arrayから入力されたデータを取り出し$valueに入れる-->
<article>
    <div class="info">
        <h2><?php echo $value['title']; ?></h2><!--<h2>でview_name(タイトル)を出力-->
    </div>
    <p><?php echo nl2br($value['article']); ?></p><!--<ｐ>でmessage(記事)を出力-->
    <a href="http://localhost/edit2.php?id=<?php echo $value['article_id']?>">記事全文・コメントを見る</a><!--記事の詳細のリンク作成、$valueのユニークidのキーを指定して個別のページに飛ぶように設定-->
    <hr><!--下線部-->
</article>
<?php endwhile; ?>
<?php endif; ?>    
</section>
<script>
    ask = () => {//関数の設定
        return confirm('投稿してよろしいですか？');//確認ダイアログの出現
    }
</script>
</body>
</html>