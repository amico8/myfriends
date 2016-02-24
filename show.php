<?php
// DB接続準備
$dsn = 'mysql:dbname=myfriends;host=localhost';
$user = 'root';
$password = '';
$dbh = new PDO($dsn,$user,$password);
$dbh->query('SET NAMES utf8');

// 削除処理
if (isset($_GET['action']) && !empty($_GET['action'])) {
  if ($_GET['action'] == 'delete') {
    $sql = 'DELETE FROM `friends` WHERE `friend_id` = ' . $_GET['friend_id'];
    // SQL実行
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    header('Location: index.php');
  }
}

$area_id = $_GET['area_id'];

// 都道府県名を表示するための処理
$sql = 'SELECT `area_name` FROM `areas` WHERE `area_id` = ' . $area_id;

// SQL実行
$stmt = $dbh->prepare($sql);
$stmt->execute();

// 実行結果を格納
$area = $stmt->fetch(PDO::FETCH_ASSOC);

// 友だちリストを表示する処理
$sql = 'SELECT * FROM `friends` WHERE `area_id` =' . $area_id;

// SQL実行
$stmt = $dbh->prepare($sql);
$stmt->execute();

// 取得データ格納用Array
$friends = array();

// 男女カウント用変数
$male = 0;
$female = 0;

while(1){
  // データ取得
  $rec = $stmt->fetch(PDO::FETCH_ASSOC);
  if($rec == false){
    break;
  }
  // データ格納
  $friends[]=$rec;

  if ($rec['gender'] == 1) {
    $male++;
  } else if($rec['gender'] == 2) {
    $female++;
  }
}

// 平均年齢取得
// SQL文
$sql = 'SELECT `gender`, TRUNCATE(AVG(`age`), 2) AS avgAge FROM `friends` WHERE `area_id` = '.$area_id.' GROUP BY `gender`';

// SQL実行
$stmt = $dbh->prepare($sql);
$stmt->execute();

// 取得データ格納用Array
$avgAge = array();

while(1){
  // データ取得
  $rec = $stmt->fetch(PDO::FETCH_ASSOC);
  if($rec == false){
    break;
  }
  // データ格納
  $avgAge[]=$rec;
}

//データベース切断
$dbh=null;


?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>myFriends</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript">
    function destroy(friend_id){
      if(confirm('削除しますか？')){
        // OKボタンをおした時
        location.href = 'show.php?action=delete&friend_id=' + friend_id;
        return true;
      } else {
        // キャンセルボタンをおした時
        return false;
      }
    }
    </script>
  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-facebook-square"></i> My friends</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
      <legend><?php echo $area['area_name']; ?>の友達</legend>
      <div class="well">
      男性：<?php echo $male; ?>名　女性：<?php echo $female; ?>名<br/>
      <?php if ($avgAge[0]['gender'] == 2) {
        echo '男性平均：-歳　';
        echo '女性平均：' . $avgAge[0]['avgAge'] . '歳';
      } else if (empty($avgAge[1]['gender'])) {
        echo '男性平均：' . $avgAge[0]['avgAge'] . '歳　';
        echo '女性平均：-歳';
      } else {
        echo '男性平均：' . $avgAge[0]['avgAge'] . '歳　';
        echo '女性平均：' . $avgAge[1]['avgAge'] . '歳';
      }
      ?>
      </div>
        <table class="table table-striped table-hover table-condensed">
          <thead>
            <tr>
              <th><div class="text-center">名前</div></th>
              <th><div class="text-center"></div></th>
            </tr>
          </thead>
          <tbody>
            <!-- id, 県名を表示 -->
            <?php foreach ($friends as $friend) { ?>
            <tr>
              <td><div class="text-center"><?php echo $friend['friend_name']; ?></div></td>
              <td>
                <div class="text-center">
                  <a href="edit.php?friend_id=<?php echo $friend['friend_id']; ?>"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                  <a href="#" onclick="destroy(<?php echo $friend['friend_id']; ?>);"><i class="fa fa-trash"></i></a>
                </div>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>

        <input type="button" class="btn btn-default" value="新規作成" onClick="location.href='new.php'">
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
