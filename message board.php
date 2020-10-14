<?php
    
    //基本的なDB（データベース）への接続設定

    //DBに接続するために必要な変数をそれぞれ用意する
	$dsn = 'データベース名';
	$user = 'ユーザー名';
	$password = 'パスワード';
	
	/*PDOは、PHP Data Objects の省略のことで、PHPからデータベースに接続するために利用する
	dsnは、Data Source Name の省略のことで、文字列を表す時に利用する*/
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));   
	
	/*array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)とは、データベース操作で発生したエラーを
	警告として表示してくれる設定をするための要素である
	デフォルトでは、PDOのデータベース操作で発生したエラーは何も表示されない
	その場合、不具合の原因を見つけるのに時間がかかってしまうので、このオプションはつけておくべき*/
    
    /*CREATE文を使用してテーブルを作成 
    イメージとしては、情報を格納する「箱」を作成する感じ*/
    $sql = "CREATE TABLE IF NOT EXISTS public_html"
    ." ("
    //numは、INT AUTO_INCREMENT PRIMARY KEY を使うことで自動的に番号が割り当てられる
    . "num INT AUTO_INCREMENT PRIMARY KEY,"
    //（）内に記されている数値はDBに登録できる上限文字数
    . "name char(32),"
    . "comment TEXT,"
    . "date char(32),"
    . "password char(32)"
    .");";

    $stmt = $pdo->query($sql);

    /*　DROP文で作成したテーブルを破壊する（ここでは、コメントアウトとしておく）
    $sql = 'DROP TABLE public_html';
	$stmt = $pdo->query($sql);　*/


    //新規投稿

    /*条件分岐
    もし、投稿フォームの名前・コメント・パスワードが入力されている（空でない）なら、*/
    if((!empty($_POST["name"])) && (!empty($_POST["comment"])) && (!empty($_POST["password"]))){
        
        $password = $_POST["password"];   

        if(!empty($_POST["edit_post"])){
            
            $sql = 'SELECT * FROM public_html WHERE num=:num';
            $num = $_POST["edit_post"];
            //差し替えるパラメータを含めて記述したSQLを用意する
            $stmt = $pdo->prepare($sql);
            //その差し替えるパラメータの値を指定する
            $stmt->bindParam(':num', $num, PDO::PARAM_INT);
            //SQLを実行する
            $stmt->execute();
            $results = $stmt->fetchAll(); 
	        foreach ($results as $row){
		    //$rowの中にはテーブルのカラム名が入る
            $editpass = $row["password"];
	       }

            if($editpass==$password){

                $Name = $_POST["name"];
                $Comment = $_POST["comment"];

                //update文を使って編集処理を行う
	            $sql = 'update public_html set name=:name,comment=:comment where num=:num';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':num', $num, PDO::PARAM_STR);
	            $stmt->bindParam(':name', $Name, PDO::PARAM_STR);
	            $stmt->bindParam(':comment', $Comment, PDO::PARAM_STR);
	            $stmt->execute();
            }
        }else{

            /*作成したテーブルに、insert文を使用して投稿されるそれぞれのデータを記録する
            bindParamの引数（:name,:commentなど）は、自分がどんな名前のカラムを設定したかで変える必要がある
            numについては、テーブル作成の時点で自動で番号が振られるように設定しているから指示は必要ない*/

	    $sql = $pdo -> prepare("INSERT INTO public_html (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
        $sql -> bindParam(':name', $name, PDO::PARAM_STR);
	    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
        $sql -> bindParam(':date', $date, PDO::PARAM_STR);
        $sql -> bindParam(':password', $password, PDO::PARAM_STR);
            
        //phpで変数をそれぞれ定義して用意する

        //投稿フォームに入力された名前を変数で受け取る（格納）
        $name = $_POST["name"];
        //投稿フォームに入力されたコメントを変数で受け取る（格納）
        $comment = $_POST["comment"]; 
        //投稿フォームが送信された時間帯を記録する変数を用意する
        $date =date("y年m月d日 H時i分s秒");
        //投稿フォームに入力されたパスワードを変数で受け取る（格納）
	    $password = $_POST["password"];
            
        //sql文を実行する
        $sql -> execute();

        }   
    }

    /*削除処理
    もし、削除フォームの削除対象番号とパスワードが入力された（空でない）なら、*/
    if(!empty($_POST["delsub"]) && (!empty($_POST["delPassword"])) && (!empty($_POST["delnum"]))){
        
        //phpで変数をそれぞれ定義して用意する
        $num = $_POST["delnum"];
        
        //delete文を使って削除処理を行う
	    $sql = 'delete from public_html where num=:num AND password=:password';
	    $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':num', $num, PDO::PARAM_INT);
        $stmt->bindParam(':password',$_POST["delPassword"], PDO::PARAM_STR);
        
        //SQL文を実行する
        $stmt ->execute(); 
    }

    //投稿内容を投稿フォームにコピーする処理
     
    $editNumber = "";
    $editName = "";
    $editComment = "";

    if(!empty($_POST["edsub"]) && (!empty($_POST["ednum"]))){
        
        $num = $_POST["ednum"];
        $sql = 'SELECT * FROM public_html WHERE num=:num ';
        //差し替えるパラメータを含めて記述したSQLを用意する
        $stmt = $pdo->prepare($sql);
        //差し替えるパラメータの値を指定する
        $stmt->bindParam(':num', $num, PDO::PARAM_INT);
        //SQL文を実行する
        $stmt->execute();
                                  
        $results = $stmt->fetchAll(); 
	    foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		$editNumber = $row['num'];
		$editName = $row['name'];
		$editComment = $row['comment'];
	    }   
    }
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>mission_5</title>
    </head>
    <body>
        <h1>ひとこと掲示板</h1>
        
        <form action="" method="post">
            <h2>投稿フォーム</h2>
            <h3>『人生において、あなたが一番大切にしていること、または大切にしたいことは何ですか？』</h3>
            <br>
            <input type="hidden" name="edit_post" value="<?php echo $editNumber; ?>">
            <label>    名前    ：<input type="text" name="name" value="<?php echo $editName; ?>"></label>
            <br>
            <label>    投稿    ：<input type="text" name="comment" value="<?php echo $editComment; ?>"></label>
            <br>
            <label> パスワード ：<input type="password" name="password"></label>
            <input type="submit" name="submit">
        </form>

        <form action="" method="post">
            <h2>削除フォーム</h2>
            <label>削除対象番号：<input type="number" name="delnum"></label>
            <br>
            <label>パスワード：<input type="password" name="delPassword"></label>
            <input type="submit" name="delsub" value="削除">
        </form>

        <form action="" method="post">
            <h2>編集フォーム</h2>
            <label>編集対象番号：<input type="number" name="ednum"></label>
            <input type="submit" name="edsub" value="編集">
        </form>

        <h2>投稿</h2>
        
        <?php

            //入力されたデータをSELECT文で表示する
            $sql = 'SELECT * FROM public_html';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();

            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                echo $row['num'].',';
                echo $row['name'].',';
                echo $row['comment'].',';
                echo $row['date'].'<br>';
                
                echo "<hr>";
            }
               
        ?>
    </body>
</html>