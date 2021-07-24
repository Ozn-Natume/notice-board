<!DOCTYPE html>
<html lang="jp">
<head>
  <meta charset="UTF-8">
  <title>Document</title>
  <link rel="stylesheet" href="m5-1.css">
  
</head>
<body>
<?php
// データベースへの接続
$dsn = 'mysql:dbname=********;host=localhot';
$user = '*******';
$password = '******';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
// データベース内にテーブルを作成
$sql = "CREATE TABLE IF NOT EXISTS pxbdata"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "time TIMESTAMP,"
    . "password char(32)"
    .");";
$stmt = $pdo->query($sql);

if(!empty($_POST["pass"])){//パスワード入力している場合
    $password=$_POST["pass"];//パスワードを取得
    //編集番号確認機能
    if(!empty($_POST["edit_number"])&& !empty($_POST["edit_message"])){//編集番号と編集の送信がある時
        $editnumber=$_POST["edit_number"];//編集番号を取得
        $id = $editnumber;
        $sql = 'SELECT * FROM pxbdata WHERE id=:id ';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            $readpassword=$row['password'];//元々のパスワードを取得
            if ($password == $readpassword){//パスワード認証
                $editnumber1=$row['id'];//編集番号を一時保存、78行と関連
                $editname=$row['name'];//
                $editcomment=$row['comment'];
            }else{
                echo "パスワードが間違っています。";
            }
        }
    }

    //削除機能
    if(!empty($_POST["delete_number"]) && !empty($_POST["delete_message"])){//削除番号と削除の送信がある時
        $deletenumber=$_POST["delete_number"];//削除番号を取得
        $id = $deletenumber;
        $sql = 'SELECT * FROM pxbdata WHERE id=:id ';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            $readpassword=$row['password'];//元々のパスワードを取得
            if ($password == $readpassword){//パスワード認証
                $sql = 'delete from pxbdata where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }else{
                echo "パスワードが間違っています。";
            }
        }
    }
}
?>
<div class="form">
    <h1> 簡易掲示板 </h1>
    <h2>あなたの好きな野菜を教えてください</h2>
    <div class="form-contents">
         <form action="" method="post">
            <div class="pass">   
            <p>パスワードを入力してください。</p> 
            <input  type="password" name="pass" placeholder="パスワード" value="<?php if(!empty($editnumber1)){echo $readpassword;}?>">
            </div>
            <div class="send">
                <p>名前とコメントを入力し、パスワードを決めて送信してね♪</p>
                <input type="text" name="name" placeholder="名前" value="<?php if(!empty($editnumber1)){echo $editname;}?>"><br>
                <input type="text" name="text" placeholder="コメント" value="<?php if(!empty($editnumber1)){echo $editcomment;}?>"><br>
                <input type="hidden" name="ed-number"value="<?php if(!empty($editnumber1)){echo $_POST["edit_number"];}?>">
                <input type="submit" name="send_message" value="送信"><br>
            </div>
            
            <div class="del">
                <p>削除する番号を指定し、パスワードを入力して削除♡</p>
                <input type="number" name="delete_number" placeholder="削除対象番号">
                <br>
                <input type="submit" name="delete_message"value="削除">
            </div>
            <div class="ed">
                <p>編集する番号を指定しパスワードを入力して編集♫</p>
                <input type="number" name="edit_number" placeholder="編集対象番号">
                <br>
                <input type="submit" name="edit_message"value="編集">
            </div>
            
         </form>
    </div>
   
</div>



<?php
//投稿
// 編集入力機能
if(!empty($_POST["send_message"])){//送信がある時
    if(!empty($_POST["name"])){//名前の入力がある時
        if(!empty($_POST["pass"])){//パスワードの入力がある時
            $name = trim($_POST['name']);//名前を取得
            $comment = trim($_POST['text']); //コメントを取得
            $password= trim($_POST["pass"]);//パスワードを取得
            $TIMESTAMP=new DateTime();//時間取得
            $TIMESTAMP=$TIMESTAMP->format("Y-m-d H:i:s");//時間の格式を決める
            // 編集機能
            if(!empty($_POST["ed-number"])){//編集番号がある時、
                $editnumber=$_POST["ed-number"];//編集番号を取得
                $id = $editnumber;
                $sql = 'UPDATE pxbdata SET name=:name,comment=:comment,time=:time, password=:password WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue('time',$TIMESTAMP, PDO::PARAM_STR);
                $stmt->execute();
                echo "$name"." のコメントを更新しました。";
            }else{// 入力機能、編集番号がない時、新しいコメントを加える
                $sql = $pdo -> prepare("INSERT INTO pxbdata (name, comment, time, password) VALUES (:name, :comment, :time, :password)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindValue('time',$TIMESTAMP, PDO::PARAM_STR);
                $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                $sql -> execute();
                echo "<div class=mess>";
                echo $name." のコメントを受け付けました。";
                echo "</div";
            }
        }else{//パスワードの入力していない場合、エラーを提示する
            echo "<div class=mess2>";
            echo "パスワードを入力してください";
            echo "</div";
            
        }
    }else{//名前の入力していない場合、エラーを提示する
        echo "<div class=mess2>";
        echo "名前を入力してください。";
        echo "</div";
        
    }
}
?> 
<div class="output-contents">
    <div class=head><p>みんなのコメント</p></div>
    <?php
        //表示機能
        $sql = 'SELECT * FROM pxbdata';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            echo "<div class = post>";
            echo $row['id'].'|';
            echo $row['name'].':';
            echo $row['comment'].'|';
            echo $row['time'].'<br>';
            echo "<hr>";
            echo "</div>";
        }
    ?>
</div>



</body>
</html>



